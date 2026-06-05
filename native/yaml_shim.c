/*
 * markdown-fight YAML front-matter shim.
 *
 * libyaml is an event-based parser. This walks its event stream once in C and
 * emits a single JSON string, so PHP makes ONE FFI call plus one json_decode —
 * no per-event boundary crossings (which would be catastrophically slow).
 *
 * yaml2json() returns a malloc'd, NUL-terminated JSON buffer (free via the
 * shim's md2html_free) on success, or NULL on anything it cannot faithfully
 * represent: a parse error, an anchor/alias, or a `<<` merge key. The PHP
 * caller treats NULL as empty front matter.
 *
 * The scalar type detectors started from the local php-ffi-exploration/yaml-ext
 * JSON-path experiment; the slower events-path strategy was intentionally not
 * lifted. The buffer here is hardened against allocation failure and the walker
 * adds the NULL-on-unsupported bail.
 */
#include <yaml.h>
#include <errno.h>
#include <stdlib.h>
#include <string.h>
#include <stdio.h>

#if defined(_WIN32)
#include <string.h>
#define strcasecmp _stricmp
#define strncasecmp _strnicmp
#define MDF_EXPORT __declspec(dllexport)
#else
#include <strings.h> /* strcasecmp */
#define MDF_EXPORT
#endif

/* ── Growable JSON buffer (allocation-failure aware) ───────────── */

typedef struct {
    char*  data;
    size_t len;
    size_t cap;
} strbuf;

static int sb_init(strbuf* sb, size_t cap) {
    if (cap < 64) cap = 64;
    sb->data = (char*)malloc(cap);
    sb->len = 0;
    sb->cap = sb->data ? cap : 0;
    return sb->data != NULL;
}
static int sb_ensure(strbuf* sb, size_t need) {
    if (sb->len + need + 1 > sb->cap) {
        size_t cap = sb->cap ? sb->cap : 64;
        while (cap < sb->len + need + 1) cap <<= 1;
        char* p = (char*)realloc(sb->data, cap);
        if (!p) return 0;
        sb->data = p;
        sb->cap = cap;
    }
    return 1;
}
static int sb_append(strbuf* sb, const char* s, size_t n) {
    if (!sb_ensure(sb, n)) return 0;
    memcpy(sb->data + sb->len, s, n);
    sb->len += n;
    sb->data[sb->len] = '\0';
    return 1;
}
static int sb_appendc(strbuf* sb, char c) { return sb_append(sb, &c, 1); }
static int sb_append_str(strbuf* sb, const char* s) { return sb_append(sb, s, strlen(s)); }

/* Append a JSON-escaped string */
static int sb_append_json_string(strbuf* sb, const char* s, size_t len) {
    if (!sb_appendc(sb, '"')) return 0;
    for (size_t i = 0; i < len; i++) {
        unsigned char c = (unsigned char)s[i];
        int ok = 1;
        switch (c) {
            case '"':  ok = sb_append_str(sb, "\\\""); break;
            case '\\': ok = sb_append_str(sb, "\\\\"); break;
            case '\n': ok = sb_append_str(sb, "\\n");  break;
            case '\r': ok = sb_append_str(sb, "\\r");  break;
            case '\t': ok = sb_append_str(sb, "\\t");  break;
            default:
                if (c < 0x20) {
                    char buf[8];
                    snprintf(buf, sizeof(buf), "\\u%04x", c);
                    ok = sb_append_str(sb, buf);
                } else {
                    ok = sb_appendc(sb, (char)c);
                }
        }
        if (!ok) return 0;
    }
    return sb_appendc(sb, '"');
}

/* ── Detect YAML scalar types (bool, int, float, null) ───────────
 * Verbatim from yaml-ext/ffi/yaml_shim.c:69-166. */

static int is_null(const char *s, size_t len) {
    return (len == 1 && s[0] == '~') ||
           (len == 4 && (strcasecmp(s, "null") == 0)) ||
           len == 0;
}

static int is_bool_true(const char *s, size_t len) {
    return (len == 4 && strcasecmp(s, "true") == 0) ||
           (len == 3 && strcasecmp(s, "yes") == 0) ||
           (len == 2 && strcasecmp(s, "on") == 0);
}

static int is_bool_false(const char *s, size_t len) {
    return (len == 5 && strcasecmp(s, "false") == 0) ||
           (len == 2 && strcasecmp(s, "no") == 0) ||
           (len == 3 && strcasecmp(s, "off") == 0);
}

static int is_integer(const char *s, size_t len) {
    if (len == 0) return 0;
    size_t i = 0;
    if (s[0] == '-' || s[0] == '+') i++;
    if (i >= len) return 0;
    /* hex */
    if (len > i + 2 && s[i] == '0' && (s[i+1] == 'x' || s[i+1] == 'X')) {
        for (i += 2; i < len; i++) {
            if (!((s[i] >= '0' && s[i] <= '9') ||
                  (s[i] >= 'a' && s[i] <= 'f') ||
                  (s[i] >= 'A' && s[i] <= 'F'))) return 0;
        }
        return 1;
    }
    /* octal */
    if (len > i + 1 && s[i] == '0' && s[i+1] >= '0' && s[i+1] <= '7') {
        for (i += 1; i < len; i++) {
            if (s[i] < '0' || s[i] > '7') return 0;
        }
        return 1;
    }
    for (; i < len; i++) {
        if (s[i] < '0' || s[i] > '9') return 0;
    }
    return 1;
}

static int is_float(const char *s, size_t len) {
    if (len == 0) return 0;
    if (len == 4 && strcasecmp(s, ".inf") == 0) return 1;
    if (len == 5 && strcasecmp(s, "+.inf") == 0) return 1;
    if (len == 5 && strcasecmp(s, "-.inf") == 0) return 1;
    if (len == 4 && strcasecmp(s, ".nan") == 0) return 1;
    if (len == 5 && strcasecmp(s, "+.nan") == 0) return 1;

    int has_dot = 0, has_e = 0;
    size_t i = 0;
    if (s[0] == '-' || s[0] == '+') i++;
    for (; i < len; i++) {
        if (s[i] == '.') { has_dot++; }
        else if (s[i] == 'e' || s[i] == 'E') {
            has_e++;
            if (i + 1 < len && (s[i+1] == '+' || s[i+1] == '-')) i++;
        }
        else if (s[i] < '0' || s[i] > '9') return 0;
    }
    return (has_dot == 1 || has_e == 1) && has_dot <= 1 && has_e <= 1;
}

static int sb_append_long_long(strbuf *sb, long long n) {
    char buf[64];
    int written = snprintf(buf, sizeof(buf), "%lld", n);
    if (written <= 0 || (size_t)written >= sizeof(buf)) return 0;
    return sb_append(sb, buf, (size_t)written);
}

static int sb_append_yaml_integer(strbuf *sb, const char *val, size_t len) {
    char *copy = (char*)malloc(len + 1);
    if (!copy) return 0;
    memcpy(copy, val, len);
    copy[len] = '\0';

    size_t i = (copy[0] == '-' || copy[0] == '+') ? 1 : 0;
    int base = 10;
    if (len > i + 2 && copy[i] == '0' && (copy[i + 1] == 'x' || copy[i + 1] == 'X')) {
        base = 16;
    } else if (len > i + 1 && copy[i] == '0' && copy[i + 1] >= '0' && copy[i + 1] <= '7') {
        base = 8;
    }

    errno = 0;
    char *end = NULL;
    long long n = strtoll(copy, &end, base);
    int ok = 0;

    if (errno == 0 && end == copy + len) {
        ok = sb_append_long_long(sb, n);
    } else {
        /* Keep huge or otherwise unrepresentable integers usable. */
        ok = sb_append_json_string(sb, val, len);
    }

    free(copy);
    return ok;
}

static int sb_append_yaml_float(strbuf *sb, const char *val, size_t len) {
    if (strcasecmp(val, ".inf") == 0 || strcasecmp(val, "+.inf") == 0) {
        return sb_append_str(sb, "1e999");
    }
    if (strcasecmp(val, "-.inf") == 0) {
        return sb_append_str(sb, "-1e999");
    }
    if (strcasecmp(val, ".nan") == 0 || strcasecmp(val, "+.nan") == 0) {
        return sb_append_str(sb, "null");
    }

    size_t start = (val[0] == '+') ? 1 : 0;
    if (val[0] == '-' && len > 1 && val[1] == '.') {
        return sb_append_str(sb, "-0") && sb_append(sb, val + 1, len - 1);
    }
    if (start < len && val[start] == '.') {
        return sb_appendc(sb, '0') && sb_append(sb, val + start, len - start);
    }
    if (val[len - 1] == '.') {
        return sb_append(sb, val + start, len - start) && sb_appendc(sb, '0');
    }
    if (start > 0) {
        return sb_append(sb, val + start, len - start);
    }

    return sb_append(sb, val, len);
}

/* Append a YAML scalar as a JSON value (with type detection). */
static int sb_append_yaml_scalar(strbuf *sb, const char *val, size_t len,
                                  yaml_scalar_style_t style)
{
    /* Quoted scalars are always strings */
    if (style == YAML_SINGLE_QUOTED_SCALAR_STYLE ||
        style == YAML_DOUBLE_QUOTED_SCALAR_STYLE) {
        return sb_append_json_string(sb, val, len);
    }

    if (is_null(val, len))        return sb_append_str(sb, "null");
    if (is_bool_true(val, len))   return sb_append_str(sb, "true");
    if (is_bool_false(val, len))  return sb_append_str(sb, "false");
    if (is_integer(val, len))     return sb_append_yaml_integer(sb, val, len);
    if (is_float(val, len))       return sb_append_yaml_float(sb, val, len);

    return sb_append_json_string(sb, val, len);
}

/* ── YAML → JSON ───────────────────────────────────────────────── */

MDF_EXPORT char* yaml2json(const char* yaml_str, size_t length, size_t* out_len) {
    if (out_len) *out_len = 0;

    yaml_parser_t parser;
    yaml_event_t  event;
    if (!yaml_parser_initialize(&parser)) return NULL;
    yaml_parser_set_input_string(&parser, (const unsigned char*)yaml_str, length);

    strbuf sb;
    if (!sb_init(&sb, length * 2)) { yaml_parser_delete(&parser); return NULL; }

    #define MAX_DEPTH 128
    int container_stack[MAX_DEPTH]; /* 0=map, 1=seq */
    int need_comma[MAX_DEPTH];
    int is_key[MAX_DEPTH];          /* in a map: alternates key/value */
    int depth = 0, done = 0, failed = 0;
    memset(need_comma, 0, sizeof(need_comma));
    memset(is_key, 0, sizeof(is_key));

    while (!done) {
        if (!yaml_parser_parse(&parser, &event)) { failed = 1; break; }

        switch (event.type) {
        case YAML_STREAM_START_EVENT:
        case YAML_DOCUMENT_START_EVENT:
            break;

        case YAML_ALIAS_EVENT:
            /* Anchors/aliases: refuse so PHP returns empty front matter. */
            failed = 1;
            break;

        case YAML_MAPPING_START_EVENT:
            /* A comma before a nested container is only correct when the parent
             * is a sequence; in a map the key + ':' was already written. */
            if (depth > 0 && container_stack[depth] == 1 && need_comma[depth]) {
                if (!sb_appendc(&sb, ',')) failed = 1;
            }
            if (depth < MAX_DEPTH - 1) {
                depth++;
                container_stack[depth] = 0; /* map */
                need_comma[depth] = 0;
                is_key[depth] = 1;
            }
            if (!sb_appendc(&sb, '{')) failed = 1;
            break;

        case YAML_MAPPING_END_EVENT:
            if (!sb_appendc(&sb, '}')) failed = 1;
            if (depth > 0) depth--;
            if (depth > 0 && container_stack[depth] == 0) is_key[depth] = 1;
            need_comma[depth] = 1;
            break;

        case YAML_SEQUENCE_START_EVENT:
            if (depth > 0 && container_stack[depth] == 1 && need_comma[depth]) {
                if (!sb_appendc(&sb, ',')) failed = 1;
            }
            if (depth < MAX_DEPTH - 1) {
                depth++;
                container_stack[depth] = 1; /* seq */
                need_comma[depth] = 0;
                is_key[depth] = 0;
            }
            if (!sb_appendc(&sb, '[')) failed = 1;
            break;

        case YAML_SEQUENCE_END_EVENT:
            if (!sb_appendc(&sb, ']')) failed = 1;
            if (depth > 0) depth--;
            if (depth > 0 && container_stack[depth] == 0) is_key[depth] = 1;
            need_comma[depth] = 1;
            break;

        case YAML_SCALAR_EVENT: {
            const char* val = (const char*)event.data.scalar.value;
            size_t vlen = event.data.scalar.length;
            yaml_scalar_style_t style = event.data.scalar.style;

            if (depth > 0 && container_stack[depth] == 0) {
                /* In a mapping */
                if (is_key[depth]) {
                    /* `<<` merge key: refuse so PHP returns empty front matter. */
                    if (vlen == 2 && val[0] == '<' && val[1] == '<') failed = 1;
                    if (need_comma[depth]) { if (!sb_appendc(&sb, ',')) failed = 1; }
                    if (!sb_append_json_string(&sb, val, vlen)) failed = 1;
                    if (!sb_appendc(&sb, ':')) failed = 1;
                    is_key[depth] = 0;
                } else {
                    if (!sb_append_yaml_scalar(&sb, val, vlen, style)) failed = 1;
                    is_key[depth] = 1;
                    need_comma[depth] = 1;
                }
            } else if (depth > 0 && container_stack[depth] == 1) {
                /* In a sequence */
                if (need_comma[depth]) { if (!sb_appendc(&sb, ',')) failed = 1; }
                if (!sb_append_yaml_scalar(&sb, val, vlen, style)) failed = 1;
                need_comma[depth] = 1;
            } else {
                /* Top-level scalar */
                if (!sb_append_yaml_scalar(&sb, val, vlen, style)) failed = 1;
            }
            break;
        }

        case YAML_DOCUMENT_END_EVENT:
        case YAML_STREAM_END_EVENT:
            done = 1;
            break;

        default:
            break;
        }

        yaml_event_delete(&event);
        if (failed) break;
    }

    yaml_parser_delete(&parser);

    if (failed || sb.data == NULL) { free(sb.data); return NULL; }

    if (out_len) *out_len = sb.len;
    return sb.data;
}

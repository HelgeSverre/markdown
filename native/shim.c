/*
 * markdown-fight FFI shim.
 *
 * md4c's native API renders through a callback (process_output called many
 * times). Invoking a PHP closure as a C callback per-chunk would murder our
 * FFI performance. So this shim buffers everything in C into one growable
 * membuf and hands PHP a single flat NUL-terminated char* — one FFI call in,
 * one pointer out. PHP copies it with FFI::string and frees it. Zero callbacks
 * cross the FFI boundary.
 */
#include <stdlib.h>
#include <string.h>
#include <stdint.h>
#include <stdio.h>
#if !defined(_WIN32)
#include <pthread.h>
#define MDF_THREADS 1   /* POSIX: real OS thread pool for the batch path */
#endif
#include "md4c/md4c-html.h"

/* Make sure the public entry points are exported from a Windows DLL. */
#if defined(_WIN32)
#define MDF_EXPORT __declspec(dllexport)
#else
#define MDF_EXPORT
#endif

typedef struct {
    char*  data;
    size_t size;
    size_t cap;
} membuf;

static void membuf_append(const MD_CHAR* text, MD_SIZE size, void* userdata) {
    membuf* b = (membuf*)userdata;
    if (b->size + (size_t)size + 1 > b->cap) {
        size_t newcap = b->cap ? b->cap : 8192;
        while (newcap < b->size + (size_t)size + 1) newcap <<= 1;
        b->data = (char*)realloc(b->data, newcap);
        b->cap = newcap;
    }
    memcpy(b->data + b->size, text, size);
    b->size += size;
}

static int starts_with(const char* s, size_t n, const char* prefix) {
    size_t plen = strlen(prefix);
    return n >= plen && memcmp(s, prefix, plen) == 0;
}

static int looks_like_autolink_text(const char* s, size_t n) {
    while (n > 0 && (*s == ' ' || *s == '\t' || *s == '\n' || *s == '\r')) {
        s++;
        n--;
    }
    while (n > 0 && (s[n - 1] == ' ' || s[n - 1] == '\t' || s[n - 1] == '\n' || s[n - 1] == '\r')) {
        n--;
    }

    return starts_with(s, n, "http://")
        || starts_with(s, n, "https://")
        || starts_with(s, n, "www.");
}

static int nested_anchor_is_generated_autolink(const char* s, size_t n, size_t inner_start) {
    size_t close = inner_start;
    while (close + 3 < n) {
        if (s[close] == '<' && s[close + 1] == '/' && s[close + 2] == 'a' && s[close + 3] == '>') {
            return looks_like_autolink_text(s + inner_start, close - inner_start);
        }
        close++;
    }

    return 0;
}

/*
 * Collapse anchors nested directly inside other anchors.
 *
 * md4c's GFM permissive-autolink will re-wrap the *text* of an explicit
 * [text](url) link when that text is itself a bare URL, emitting invalid
 * <a href="OUTER"><a href="INNER">text</a></a>. CommonMark/GFM (and league)
 * never autolink inside link text. We fix it with a single in-place pass:
 * any <a ...> seen while already inside an anchor is dropped (along with its
 * matching </a>), keeping the outermost href and the visible text intact.
 *
 * Safe because md4c only ever emits real anchor tags as literal "<a"/"</a>"
 * — any "<a" in document content is HTML-escaped (&lt;a) before it reaches us.
 * Output length is always <= input length (we only delete tags), so this is
 * done in place. Returns the new length.
 */
static size_t collapse_nested_anchors(char* s, size_t n) {
    size_t w = 0;          /* write cursor */
    size_t i = 0;          /* read cursor  */
    int    depth = 0;      /* anchor depth in the OUTPUT */
    int    suppressed = 0; /* dropped generated opens whose </a> we must also drop */

    /* Until a tag is actually dropped, w == i and every "copy" would be a
     * self-write, so we only move bytes once something has been deleted.
     * Ordinary text — the vast majority, since nesting only happens for the
     * GFM [url](url) case — is skipped in bulk with memchr, not byte by byte. */
    while (i < n) {
        if (s[i] != '<') {
            const char* lt = (const char*)memchr(s + i, '<', n - i);
            size_t run = lt ? (size_t)(lt - (s + i)) : (n - i);
            if (w != i) memmove(s + w, s + i, run);
            w += run; i += run;
            continue;
        }
        /* closing </a> */
        if (i + 3 < n && s[i+1] == '/' && s[i+2] == 'a' && s[i+3] == '>') {
            if (suppressed > 0) {            /* matches a dropped open: drop it too */
                suppressed--;
            } else {
                if (w != i) memmove(s + w, s + i, 4);
                w += 4;
                if (depth > 0) depth--;
            }
            i += 4;
            continue;
        }
        /* opening <a ...> (md4c emits <a href="...">) */
        if (i + 2 < n && s[i+1] == 'a' &&
            (s[i+2] == ' ' || s[i+2] == '>' || s[i+2] == '\t' || s[i+2] == '\n')) {
            size_t j = i + 2;
            while (j < n && s[j] != '>') j++;
            if (j >= n) {                    /* unterminated tag: copy the rest verbatim */
                size_t run = n - i;
                if (w != i) memmove(s + w, s + i, run);
                w += run; i += run;
                break;
            }
            if (depth >= 1 && nested_anchor_is_generated_autolink(s, n, j + 1)) {
                /* Generated nested autolink: drop it, remember its close. */
                suppressed++;
            } else {                         /* top-level open: keep it */
                size_t run = j - i + 1;
                if (w != i) memmove(s + w, s + i, run);
                w += run;
                depth++;
            }
            i = j + 1;
            continue;
        }
        /* a '<' that is neither <a...> nor </a> (e.g. <p>, <code>): keep it */
        if (w != i) s[w] = s[i];
        w++; i++;
    }
    s[w] = '\0';
    return w;
}

/*
 * Parse `input` (length `input_len`) markdown -> HTML.
 * Returns a malloc'd, NUL-terminated buffer and writes the byte length
 * (excluding the NUL) to *out_len. Caller MUST free via md2html_free().
 * Returns NULL on failure.
 */
MDF_EXPORT char* md2html(const char* input, size_t input_len, size_t* out_len,
              unsigned int parser_flags, unsigned int renderer_flags) {
    membuf b = {0, 0, 0};
    /* Seed the output buffer from the input size so the realloc-doubling in
     * membuf_append rarely fires: GFM HTML output is a small multiple of the
     * markdown, so one up-front malloc usually holds the whole render. */
    size_t seed = input_len + (input_len >> 1) + 64;
    b.data = (char*)malloc(seed);
    if (b.data) b.cap = seed;
    int rc = md_html(input, (MD_SIZE)input_len, membuf_append, &b,
                     parser_flags, renderer_flags);
    if (rc != 0) {
        free(b.data);
        if (out_len) *out_len = 0;
        return NULL;
    }
    /* ensure NUL terminator (membuf already reserved room for it) */
    if (b.cap == 0) { b.data = (char*)malloc(1); b.cap = 1; }
    b.data[b.size] = '\0';
    b.size = collapse_nested_anchors(b.data, b.size);
    if (out_len) *out_len = b.size;
    return b.data;
}

MDF_EXPORT void md2html_free(char* p) {
    free(p);
}

/* Expose the GitHub dialect flag value so PHP doesn't hardcode it. */
MDF_EXPORT unsigned int md2html_dialect_github(void) {
    return MD_DIALECT_GITHUB;
}

/* ------------------------------------------------------------------------- *
 * Heading anchoring + table of contents (for Parser::parse()).
 *
 * md4c-html emits bare "<h1>".."<h6>" with no id. This pass rewrites the
 * rendered HTML once in C — injecting GitHub-style id="slug" into every heading
 * and emitting a TOC — instead of the PHP regex+per-heading pass it replaces.
 * It mirrors src/HeadingAnchors.php byte-for-byte (verified by a parity test).
 * ------------------------------------------------------------------------- */

/* Small open-addressing string->int map for O(n) slug de-duplication. */
typedef struct { char* key; size_t klen; int val; } hm_slot;
typedef struct { hm_slot* slots; size_t cap; size_t len; } hashmap;

static uint64_t fnv1a(const char* s, size_t n) {
    uint64_t h = 1469598103934665603ULL;
    for (size_t i = 0; i < n; i++) { h ^= (unsigned char)s[i]; h *= 1099511628211ULL; }
    return h;
}
static void hm_init(hashmap* m) { m->cap = 64; m->len = 0; m->slots = (hm_slot*)calloc(m->cap, sizeof(hm_slot)); }
static void hm_free(hashmap* m) { for (size_t i = 0; i < m->cap; i++) free(m->slots[i].key); free(m->slots); }
static hm_slot* hm_slot_for(hm_slot* slots, size_t cap, const char* key, size_t klen, uint64_t h) {
    size_t mask = cap - 1, idx = (size_t)h & mask;
    for (;;) {
        hm_slot* s = &slots[idx];
        if (s->key == NULL) return s;
        if (s->klen == klen && memcmp(s->key, key, klen) == 0) return s;
        idx = (idx + 1) & mask;
    }
}
static void hm_resize(hashmap* m) {
    size_t newcap = m->cap * 2;
    hm_slot* ns = (hm_slot*)calloc(newcap, sizeof(hm_slot));
    for (size_t i = 0; i < m->cap; i++) {
        if (m->slots[i].key) {
            hm_slot* s = hm_slot_for(ns, newcap, m->slots[i].key, m->slots[i].klen, fnv1a(m->slots[i].key, m->slots[i].klen));
            *s = m->slots[i];
        }
    }
    free(m->slots); m->slots = ns; m->cap = newcap;
}
static hm_slot* hm_get(hashmap* m, const char* key, size_t klen) {
    hm_slot* s = hm_slot_for(m->slots, m->cap, key, klen, fnv1a(key, klen));
    return s->key ? s : NULL;
}
static void hm_put(hashmap* m, const char* key, size_t klen, int val) {
    if ((m->len + 1) * 10 >= m->cap * 7) hm_resize(m);
    hm_slot* s = hm_slot_for(m->slots, m->cap, key, klen, fnv1a(key, klen));
    if (s->key == NULL) { s->key = (char*)malloc(klen ? klen : 1); memcpy(s->key, key, klen); s->klen = klen; s->val = val; m->len++; }
    else { s->val = val; }
}

/* Mirror of HeadingAnchors::slugify. Returns a malloc'd slug, length in *out. */
static char* anchor_slugify(const char* inner, size_t len, size_t* out) {
    char* o = (char*)malloc(len + 1);
    size_t w = 0, i = 0;
    int prev_hyphen = 1;
    while (i < len) {
        char c = inner[i];
        if (c == '<') { while (i < len && inner[i] != '>') i++; if (i < len) i++; continue; }
        if (c == '&') {
            while (i < len && inner[i] != ';') i++;
            if (i < len) i++;
            if (!prev_hyphen) { o[w++] = '-'; prev_hyphen = 1; }
            continue;
        }
        unsigned char b = (unsigned char)c;
        if (b >= 65 && b <= 90) { o[w++] = (char)(b + 32); prev_hyphen = 0; }
        else if ((b >= 97 && b <= 122) || (b >= 48 && b <= 57)) { o[w++] = c; prev_hyphen = 0; }
        else if (!prev_hyphen) { o[w++] = '-'; prev_hyphen = 1; }
        i++;
    }
    while (w > 0 && o[w - 1] == '-') w--;
    if (w == 0) { free(o); o = (char*)malloc(8); memcpy(o, "section", 7); w = 7; }
    o[w] = '\0';
    *out = w;
    return o;
}

/* GitHub-style de-dup, mirror of HeadingAnchors::unique. Returns malloc'd slug. */
static char* anchor_unique(hashmap* m, const char* base, size_t blen, size_t* out) {
    if (hm_get(m, base, blen) == NULL) {
        hm_put(m, base, blen, 0);
        char* s = (char*)malloc(blen + 1);
        memcpy(s, base, blen); s[blen] = '\0';
        *out = blen;
        return s;
    }
    for (;;) {
        hm_slot* bs = hm_get(m, base, blen);
        int v = ++bs->val;
        char num[16];
        int nl = snprintf(num, sizeof num, "%d", v);
        size_t clen = blen + 1 + (size_t)nl;
        char* cand = (char*)malloc(clen + 1);
        memcpy(cand, base, blen); cand[blen] = '-'; memcpy(cand + blen + 1, num, (size_t)nl); cand[clen] = '\0';
        if (hm_get(m, cand, clen) == NULL) { hm_put(m, cand, clen, 0); *out = clen; return cand; }
        free(cand);
    }
}

static int is_trim_byte(char c) { return c == ' ' || c == '\t' || c == '\n' || c == '\r' || c == '\0' || c == '\x0B'; }

static int utf8_encode(unsigned int cp, char* dst) {
    if (cp <= 0x7F) { dst[0] = (char)cp; return 1; }
    if (cp <= 0x7FF) { dst[0] = (char)(0xC0 | (cp >> 6)); dst[1] = (char)(0x80 | (cp & 0x3F)); return 2; }
    if (cp <= 0xFFFF) { dst[0] = (char)(0xE0 | (cp >> 12)); dst[1] = (char)(0x80 | ((cp >> 6) & 0x3F)); dst[2] = (char)(0x80 | (cp & 0x3F)); return 3; }
    if (cp <= 0x10FFFF) { dst[0] = (char)(0xF0 | (cp >> 18)); dst[1] = (char)(0x80 | ((cp >> 12) & 0x3F)); dst[2] = (char)(0x80 | ((cp >> 6) & 0x3F)); dst[3] = (char)(0x80 | (cp & 0x3F)); return 4; }
    return 0;
}

/* Decode one entity NAME (between & and ;). Returns bytes written, or -1. md4c
 * only emits &amp;/&lt;/&gt; in text; we cover the named set + numeric refs so
 * the TOC text matches PHP's html_entity_decode for everything md4c produces. */
static int decode_entity(const char* name, size_t len, char* dst) {
    if (len == 0) return -1;
    if (name[0] == '#') {
        unsigned int cp = 0;
        if (len >= 2 && (name[1] == 'x' || name[1] == 'X')) {
            if (len == 2) return -1;
            for (size_t i = 2; i < len; i++) {
                char c = name[i]; int d;
                if (c >= '0' && c <= '9') d = c - '0';
                else if (c >= 'a' && c <= 'f') d = c - 'a' + 10;
                else if (c >= 'A' && c <= 'F') d = c - 'A' + 10;
                else return -1;
                cp = cp * 16 + (unsigned)d;
                if (cp > 0x10FFFF) return -1;
            }
        } else {
            if (len == 1) return -1;
            for (size_t i = 1; i < len; i++) {
                char c = name[i];
                if (c < '0' || c > '9') return -1;
                cp = cp * 10 + (unsigned)(c - '0');
                if (cp > 0x10FFFF) return -1;
            }
        }
        int w = utf8_encode(cp, dst);
        return w == 0 ? -1 : w;
    }
    if (len == 3 && memcmp(name, "amp", 3) == 0) { dst[0] = '&'; return 1; }
    if (len == 2 && memcmp(name, "lt", 2) == 0) { dst[0] = '<'; return 1; }
    if (len == 2 && memcmp(name, "gt", 2) == 0) { dst[0] = '>'; return 1; }
    if (len == 4 && memcmp(name, "quot", 4) == 0) { dst[0] = '"'; return 1; }
    if (len == 4 && memcmp(name, "apos", 4) == 0) { dst[0] = '\''; return 1; }
    return -1;
}

/* Mirror of HeadingAnchors::plainText: strip tags, decode entities, trim. */
static char* anchor_text(const char* inner, size_t len, size_t* out) {
    char* o = (char*)malloc(len + 1);
    size_t w = 0, i = 0;
    while (i < len) {
        char c = inner[i];
        if (c == '<') { while (i < len && inner[i] != '>') i++; if (i < len) i++; continue; }
        if (c == '&') {
            size_t j = i + 1;
            while (j < len && inner[j] != ';' && (j - i) <= 12) j++;
            if (j < len && inner[j] == ';') {
                char buf[8];
                int dw = decode_entity(inner + i + 1, j - (i + 1), buf);
                if (dw > 0) { memcpy(o + w, buf, (size_t)dw); w += (size_t)dw; i = j + 1; continue; }
            }
            o[w++] = c; i++;
            continue;
        }
        o[w++] = c; i++;
    }
    size_t s = 0, e = w;
    while (s < e && is_trim_byte(o[s])) s++;
    while (e > s && is_trim_byte(o[e - 1])) e--;
    if (s > 0) memmove(o, o + s, e - s);
    *out = e - s;
    o[*out] = '\0';
    return o;
}

static void append_u32le(membuf* b, uint32_t v) {
    unsigned char t[4] = { (unsigned char)v, (unsigned char)(v >> 8), (unsigned char)(v >> 16), (unsigned char)(v >> 24) };
    membuf_append((const char*)t, 4, b);
}

/*
 * Anchor headings in `html` and build a table of contents.
 *
 * Returns rewritten HTML (malloc'd, NUL-terminated; *out_len set). The TOC is
 * allocated into *toc_out as a little-endian, length-prefixed blob — one record
 * per heading: [u8 level][u32 slug_len][slug][u32 text_len][text] — with its
 * byte length in *toc_len_out. Caller frees BOTH the return value and *toc_out
 * via md2html_free(). Never returns NULL.
 */
MDF_EXPORT char* md2html_anchor(const char* html, size_t html_len, size_t* out_len,
                                char** toc_out, size_t* toc_len_out) {
    membuf out = {0, 0, 0};
    membuf toc = {0, 0, 0};
    /* Anchoring only injects id="slug" into headings, so output tracks input
     * size closely — seed it to avoid realloc churn on large documents. */
    size_t out_seed = html_len + (html_len >> 4) + 64;
    out.data = (char*)malloc(out_seed);
    if (out.data) out.cap = out_seed;
    hashmap map;
    hm_init(&map);

    size_t i = 0, run_start = 0;
    while (i < html_len) {
        if (html[i] == '<' && i + 3 < html_len && html[i + 1] == 'h'
            && html[i + 2] >= '1' && html[i + 2] <= '6' && html[i + 3] == '>') {
            char lvl = html[i + 2];
            size_t inner_start = i + 4, j = inner_start;
            int found = 0;
            while (j + 4 < html_len) {
                if (html[j] == '<' && html[j + 1] == '/' && html[j + 2] == 'h'
                    && html[j + 3] == lvl && html[j + 4] == '>') { found = 1; break; }
                j++;
            }
            if (found) {
                if (i > run_start) membuf_append(html + run_start, (MD_SIZE)(i - run_start), &out);

                const char* inner = html + inner_start;
                size_t inner_len = j - inner_start, base_len, slug_len, text_len;
                char* base = anchor_slugify(inner, inner_len, &base_len);
                char* slug = anchor_unique(&map, base, base_len, &slug_len);
                free(base);
                char* text = anchor_text(inner, inner_len, &text_len);

                char open[8] = {'<', 'h', lvl, ' ', 'i', 'd', '=', '"'};
                membuf_append(open, 8, &out);
                if (slug_len) membuf_append(slug, (MD_SIZE)slug_len, &out);
                membuf_append("\">", 2, &out);
                if (inner_len) membuf_append(inner, (MD_SIZE)inner_len, &out);
                char close[4] = {'<', '/', 'h', lvl};
                membuf_append(close, 4, &out);
                membuf_append(">", 1, &out);

                unsigned char lv = (unsigned char)(lvl - '0');
                membuf_append((const char*)&lv, 1, &toc);
                append_u32le(&toc, (uint32_t)slug_len);
                if (slug_len) membuf_append(slug, (MD_SIZE)slug_len, &toc);
                append_u32le(&toc, (uint32_t)text_len);
                if (text_len) membuf_append(text, (MD_SIZE)text_len, &toc);

                free(slug);
                free(text);
                i = j + 5;
                run_start = i;
                continue;
            }
            /* unmatched <hN>: leave it in the run, like the PHP regex does */
        }
        i++;
    }
    if (html_len > run_start) membuf_append(html + run_start, (MD_SIZE)(html_len - run_start), &out);

    hm_free(&map);

    if (out.cap == 0) { out.data = (char*)malloc(1); out.cap = 1; }
    out.data[out.size] = '\0';
    if (out_len) *out_len = out.size;

    if (toc.cap == 0) { toc.data = (char*)malloc(1); toc.cap = 1; }
    toc.data[toc.size] = '\0';
    if (toc_out) *toc_out = toc.data;
    if (toc_len_out) *toc_len_out = toc.size;

    return out.data;
}

/* ------------------------------------------------------------------------- *
 * Multi-core batch rendering.
 *
 * md4c keeps no global/static parser state (it's fully reentrant), so we can
 * fan a batch of documents out across a pthread pool with zero locking on the
 * parse itself. Each worker renders its assigned docs into per-doc membufs;
 * the main thread then concatenates them in order into a single malloc'd
 * buffer and fills out_offsets[i]..out_offsets[i+1] so PHP can substr-slice.
 * ------------------------------------------------------------------------- */

typedef struct {
    const char*  packed;
    const size_t* in_offsets;   /* n+1 entries */
    size_t        n;
    membuf*       results;      /* n per-doc buffers */
    int*          ok;           /* n status flags, 1 = success */
    unsigned int  parser_flags;
    unsigned int  renderer_flags;
    /* striped work assignment */
    int           thread_index;
    int           thread_count;
} batch_ctx;

static void batch_render_one(batch_ctx* c, size_t i) {
    membuf* b = &c->results[i];
    b->data = NULL; b->size = 0; b->cap = 0;
    const char* doc = c->packed + c->in_offsets[i];
    size_t doc_len = c->in_offsets[i + 1] - c->in_offsets[i];
    /* Seed per-doc output from the doc size (same rationale as md2html). */
    size_t seed = doc_len + (doc_len >> 1) + 64;
    b->data = (char*)malloc(seed);
    if (b->data) b->cap = seed;
    int rc = md_html(doc, (MD_SIZE)doc_len, membuf_append, b,
                     c->parser_flags, c->renderer_flags);
    if (rc == 0 && b->size > 0) {
        if (b->size + 1 > b->cap) {  /* ensure room for the NUL collapse writes */
            b->data = (char*)realloc(b->data, b->size + 1);
            b->cap = b->size + 1;
        }
        b->data[b->size] = '\0';
        b->size = collapse_nested_anchors(b->data, b->size);
    }
    c->ok[i] = (rc == 0) ? 1 : 0;
}

#if defined(MDF_THREADS)
static void* batch_worker(void* arg) {
    batch_ctx* c = (batch_ctx*)arg;
    /* Strided assignment keeps load balanced for uneven doc sizes. */
    for (size_t i = (size_t)c->thread_index; i < c->n; i += (size_t)c->thread_count) {
        batch_render_one(c, i);
    }
    return NULL;
}
#endif

/*
 * Parse n documents (concatenated in `packed`, delimited by in_offsets[n+1])
 * across `threads` workers. On success returns one malloc'd buffer holding all
 * HTML outputs concatenated, and fills out_offsets[n+1] with the byte ranges.
 * Caller frees the returned buffer via md2html_free().
 * Returns NULL on any failure (allocation, or any doc failing to parse).
 * threads<=1 runs sequentially in-thread.
 */
MDF_EXPORT char* md2html_batch(const char* packed, const size_t* in_offsets, size_t n,
                    size_t* out_offsets, unsigned int parser_flags,
                    unsigned int renderer_flags, int threads) {
    if (out_offsets == NULL) return NULL;

    /* Empty batch: single allocation so PHP always gets a freeable pointer. */
    if (n == 0) {
        out_offsets[0] = 0;
        char* empty = (char*)malloc(1);
        if (empty) empty[0] = '\0';
        return empty;
    }

    membuf* results = (membuf*)calloc(n, sizeof(membuf));
    int*    ok      = (int*)calloc(n, sizeof(int));
    if (results == NULL || ok == NULL) {
        free(results); free(ok);
        return NULL;
    }

    int use_threads = 0;
#if defined(MDF_THREADS)
    use_threads = (threads > 1);
#endif

    if (!use_threads) {
        /* Sequential (also the only path on platforms without pthreads). */
        batch_ctx c = {0};
        c.packed = packed; c.in_offsets = in_offsets; c.n = n;
        c.results = results; c.ok = ok;
        c.parser_flags = parser_flags; c.renderer_flags = renderer_flags;
        for (size_t i = 0; i < n; i++) batch_render_one(&c, i);
    }
#if defined(MDF_THREADS)
    else {
        /* Never spawn more threads than documents. */
        if ((size_t)threads > n) threads = (int)n;

        pthread_t* tids = (pthread_t*)calloc((size_t)threads, sizeof(pthread_t));
        batch_ctx* ctxs = (batch_ctx*)calloc((size_t)threads, sizeof(batch_ctx));
        if (tids == NULL || ctxs == NULL) {
            free(tids); free(ctxs);
            for (size_t i = 0; i < n; i++) free(results[i].data);
            free(results); free(ok);
            return NULL;
        }

        int spawned = 0;
        for (int t = 0; t < threads; t++) {
            ctxs[t].packed = packed; ctxs[t].in_offsets = in_offsets; ctxs[t].n = n;
            ctxs[t].results = results; ctxs[t].ok = ok;
            ctxs[t].parser_flags = parser_flags; ctxs[t].renderer_flags = renderer_flags;
            ctxs[t].thread_index = t; ctxs[t].thread_count = threads;
            if (pthread_create(&tids[t], NULL, batch_worker, &ctxs[t]) == 0) {
                spawned++;
            } else {
                /* Couldn't spawn this worker: run its stripe inline. */
                batch_worker(&ctxs[t]);
            }
        }
        for (int t = 0; t < spawned; t++) {
            pthread_join(tids[t], NULL);
        }
        free(tids); free(ctxs);
    }
#endif

    /* Verify every doc parsed and tally the total output size. */
    size_t total = 0;
    for (size_t i = 0; i < n; i++) {
        if (!ok[i]) {
            for (size_t j = 0; j < n; j++) free(results[j].data);
            free(results); free(ok);
            return NULL;
        }
        total += results[i].size;
    }

    /* One flat buffer (+1 for a trailing NUL) for all outputs. */
    char* out = (char*)malloc(total + 1);
    if (out == NULL) {
        for (size_t i = 0; i < n; i++) free(results[i].data);
        free(results); free(ok);
        return NULL;
    }

    size_t pos = 0;
    out_offsets[0] = 0;
    for (size_t i = 0; i < n; i++) {
        if (results[i].size > 0) {
            memcpy(out + pos, results[i].data, results[i].size);
            pos += results[i].size;
        }
        out_offsets[i + 1] = pos;
        free(results[i].data);
    }
    out[total] = '\0';

    free(results);
    free(ok);
    return out;
}

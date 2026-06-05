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
#include <pthread.h>
#include "md4c/md4c-html.h"

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
    int    depth = 0;      /* anchor depth in the OUTPUT (0 or 1) */
    int    suppressed = 0; /* dropped inner opens whose </a> we must also drop */

    while (i < n) {
        /* closing </a> */
        if (s[i] == '<' && i + 3 < n && s[i+1] == '/' && s[i+2] == 'a' && s[i+3] == '>') {
            if (suppressed > 0) {            /* matches a dropped open: drop it too */
                suppressed--;
                i += 4;
            } else {
                s[w++] = '<'; s[w++] = '/'; s[w++] = 'a'; s[w++] = '>';
                if (depth > 0) depth--;
                i += 4;
            }
            continue;
        }
        /* opening <a ...> (md4c emits <a href="...">) */
        if (s[i] == '<' && i + 2 < n && s[i+1] == 'a' &&
            (s[i+2] == ' ' || s[i+2] == '>' || s[i+2] == '\t' || s[i+2] == '\n')) {
            size_t j = i + 2;
            while (j < n && s[j] != '>') j++;
            if (j >= n) {                    /* unterminated tag: copy the rest verbatim */
                while (i < n) s[w++] = s[i++];
                break;
            }
            if (depth >= 1) {                /* nested open: drop it, remember its close */
                suppressed++;
            } else {                         /* top-level open: keep it */
                for (size_t k = i; k <= j; k++) s[w++] = s[k];
                depth++;
            }
            i = j + 1;
            continue;
        }
        s[w++] = s[i++];
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
char* md2html(const char* input, size_t input_len, size_t* out_len,
              unsigned int parser_flags, unsigned int renderer_flags) {
    membuf b = {0, 0, 0};
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

void md2html_free(char* p) {
    free(p);
}

/* Expose the GitHub dialect flag value so PHP doesn't hardcode it. */
unsigned int md2html_dialect_github(void) {
    return MD_DIALECT_GITHUB;
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

static void* batch_worker(void* arg) {
    batch_ctx* c = (batch_ctx*)arg;
    /* Strided assignment keeps load balanced for uneven doc sizes. */
    for (size_t i = (size_t)c->thread_index; i < c->n; i += (size_t)c->thread_count) {
        batch_render_one(c, i);
    }
    return NULL;
}

/*
 * Parse n documents (concatenated in `packed`, delimited by in_offsets[n+1])
 * across `threads` workers. On success returns one malloc'd buffer holding all
 * HTML outputs concatenated, and fills out_offsets[n+1] with the byte ranges.
 * Caller frees the returned buffer via md2html_free().
 * Returns NULL on any failure (allocation, or any doc failing to parse).
 * threads<=1 runs sequentially in-thread.
 */
char* md2html_batch(const char* packed, const size_t* in_offsets, size_t n,
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

    if (threads <= 1) {
        batch_ctx c = {0};
        c.packed = packed; c.in_offsets = in_offsets; c.n = n;
        c.results = results; c.ok = ok;
        c.parser_flags = parser_flags; c.renderer_flags = renderer_flags;
        for (size_t i = 0; i < n; i++) batch_render_one(&c, i);
    } else {
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

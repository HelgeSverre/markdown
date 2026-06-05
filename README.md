# helgesverre/markdown

**A deliberately over-engineered Markdown parser that beats `tempest/markdown` and `league/commonmark` by refusing to parse Markdown in PHP at all — it binds straight to [md4c](https://github.com/mity/md4c) (C) over FFI.**

> ### ⚠️ Read this first
>
> **This entire repository was built by [Claude Code](https://claude.com/claude-code) (Opus 4.8) in a single "ultracode" multi-agent session** — one prompt kicked off a fleet of agents that wrote the C shim, the FFI bindings, the benchmark harness, the test suite, and ran adversarial audits on their own output. A human asked it to "beat the Tempest Markdown parser using every dirty trick in the book," and this is what came back.
>
> **It is a benchmark stunt. Do not use it for anything serious.** It ships a native binary you compile yourself, it's "PHP calling C" rather than a real PHP parser, and the only reason it exists is to see how far the floor could be pushed. If you need a Markdown parser for real work, use [`league/commonmark`](https://commonmark.thephpleague.com/) — it's pure PHP, excellent, and you don't have to ship a `.dylib`. The numbers below are real and reproducible; the *premise* is a joke. Both things are true.

---

## What it scores

Reproducible on this machine (PHP 8.5.5, Apple arm64) via `composer bench`. Each parser runs in its own process, instances reused, warmed up, timed over a ~1 s wall-clock budget. Full tables in [`results/RESULTS.md`](results/RESULTS.md).

**Real Tempest docs corpus** (252.7 KB — the exact thing the Tempest blog benchmarked):

| Parser | mean ms | MB/s | peak MB | vs this |
|---|--:|--:|--:|--:|
| **helgesverre/markdown** (FFI→md4c) | **1.05** | **246.3** | 4.0 | — |
| league/commonmark (GFM) | 23.50 | 11.0 | 10.0 | **22× slower** |
| tempest/markdown | 41.59 | 6.2 | 6.0 | **40× slower** |

It holds **~170–250 MB/s flat from 2 KB to 8 MB** — throughput doesn't sag as documents grow, because md4c's working set stays tiny while pure-PHP parsers build an AST that balloons. On an **8 MB** document: this parser does **50 ms at 39 MB peak**; `league-gfm` takes **3,806 ms at 375 MB**.

Median across the whole corpus: **~13× faster than tempest, ~59× faster than league-gfm.**

---

## The contenders

| Parser | What it is |
|---|---|
| **`tempest/markdown`** | The pure-PHP Markdown parser from the [Tempest framework](https://tempestphp.com), with a bundled syntax highlighter and heading-anchor generator. Its [blog post](https://tempestphp.com/blog/tempest-markdown) reported ~10.9 ms / 6.6 MB vs league's ~57 ms / 21 MB on the Tempest docs. |
| **`league/commonmark`** | The de-facto standard PHP Markdown library. We benchmark its strict `CommonMarkConverter` and its `GithubFlavoredMarkdownConverter`. |
| **`helgesverre/markdown`** | This repo. PHP 8.5 → FFI → md4c. The challenger. |

The Tempest pitch is "we're ~5× faster than league." Fine — so the obvious follow-up was *where's the actual floor?* This is the floor.

---

## The dirty tricks (and whether they landed)

### 1. FFI → md4c — the whole ballgame ✅
md4c is a SAX-style C parser: no AST, streaming callbacks, used in production by Qt and others. We bind it directly from PHP. PHP never touches a parse tree — it hands md4c a `(char*, len)` and gets finished HTML back.

### 2. A flat-ABI C shim so ZERO callbacks cross FFI ✅
md4c's native API calls *you* for every block and inline span. Routing those into PHP closures through FFI would be death by a thousand context switches. So [`native/shim.c`](native/shim.c) buffers the whole document **inside C** (a `realloc`-backed membuf) and exposes one fat function:

```c
char* md2html(const char* input, size_t input_len, size_t* out_len,
              unsigned int parser_flags, unsigned int renderer_flags);
void  md2html_free(char* p);
unsigned int md2html_dialect_github(void);   // 0x0F0C: autolinks|tables|strikethrough|tasklists
```

One FFI call in, one pointer out, one `free` after. The boundary is crossed twice per document, never per token.

### 3. `FFI::load` + `opcache.preload` scope warming ✅
Naïve FFI re-parses the C header and `dlopen`s the library on *every request*. [`bench/preload.php`](bench/preload.php) runs `FFI::load()` against a header carrying `#define FFI_SCOPE "MD4C"` at preload time, so every request binds via `FFI::scope("MD4C")` — no per-request `cdef`, no per-request `dlopen`. `FfiParser` falls back to `FFI::cdef` automatically when preload isn't active.

### 4. A pthreads multi-core batch path ✅
[`md2html_batch`](native/shim.c) spawns an OS thread pool (`min(cores, batch_size)`), strides documents across workers, and concatenates results in order — md4c is fully reentrant, so this needs no locking on the parse itself. [`FfiBatchParser`](src/FfiBatchParser.php) packs N documents into one buffer + offset table and makes **one** FFI call for the whole batch. Measured **~2× speedup** over the sequential path on 500 small docs (`composer examples` → `03-batch-multicore`).

### 5. An in-C anchor-collapse correctness pass ✅
md4c's permissive autolinking re-wraps the *text* of an explicit `[url](url)` link, emitting invalid nested `<a><a>`. A single in-place pass in the shim collapses anchors nested inside anchors, so output now matches `league-gfm` **exactly** on anchor count. Costs ~12% throughput — the honest price of correctness, paid inside the timed path.

**What did *not* help: fibers and streams.** Parsing one document is CPU-bound and synchronous — there's no I/O to overlap, so cooperative fibers buy nothing; you can't yield through a tight C loop. Real multi-document throughput needs *real* parallelism, which is why the batch path uses OS threads in C, not PHP fibers.

---

## Is this a fair fight?

The methodology is honest like-for-like steady-state benchmarking (same input, instances reused, output emptiness rejected, same JIT for everyone). But there are real asymmetries, disclosed because credibility beats hype:

1. **It's "PHP calling C."** That's the point, but read it as such. This isn't a faster *PHP* parser; it's PHP getting out of md4c's way.
2. **`tempest` does strictly more work** — heading slugs + a syntax highlighter — so its output is 64% larger (474 KB vs 289 KB) and part of its slowness isn't pure parsing. The `vs league-gfm` comparison *is* apples-to-apples (same GFM feature set, structurally identical HTML).
3. **The memory column flatters us.** Output lives on the C heap, which PHP's `memory_get_peak_usage()` can't see. Measured with `/usr/bin/time -l` (which *does* count it), the real-RSS win on a 1 MB doc is **~2.3×**, not the ~8× the Zend-only column implies. Still a win — just not a cartoonish one.
4. **The slowest rows rest on few samples** (8 MB × league ≈ 3 iterations). The trend is unambiguous; those specific point-estimates are low-confidence.

This parser is also **as CommonMark/GFM-correct as `league`**: visible text matches on every corpus file, anchor counts match exactly after the collapse pass, and all four GFM extensions render correctly. As a bonus it's *more* correct than `tempest`, which leaves list-items as literal paragraph text (`A list:\n- one` → `<p>A list:\n- one</p>`), fails setext headings, and even throws a hard exception on the CommonMark spec's front matter.

---

## Install & build

Requires **PHP 8.5+** with `ext-ffi`, plus a C compiler (`cc`/`clang`/`gcc`).

```bash
composer install        # PHP deps (the contenders + phpbench + phpunit)
composer build          # compile the native md4c shim for your platform
```

`composer build` runs [`native/build.sh`](native/build.sh), which produces `libmd4cshim.dylib` (macOS), `libmd4cshim.so` (Linux), or `md4cshim.dll` (Windows/mingw) and regenerates the FFI scope header with the right absolute path. `FfiParser::libPath()` resolves the correct artifact per OS at runtime.

## Use it as a library

```php
use HelgeSverre\Markdown\FfiParser;

$html = (new FfiParser())->toHtml("# Hello\n\n- a\n- b\n");   // GFM dialect on

// Multi-core batch: one FFI call, pthread pool, ordered output:
use HelgeSverre\Markdown\FfiBatchParser;
$htmls = (new FfiBatchParser())->toHtmlBatch($arrayOfMarkdownStrings);
```

## Composer scripts

| Command | What it does |
|---|---|
| `composer build` | Compile the native shim for this platform (`.dylib`/`.so`/`.dll`) |
| `composer test` | Run the PHPUnit suite (`tests/`) |
| `composer check` | Run the CI correctness gate (render parity + GFM + speed sanity) |
| `composer bench` | Full head-to-head benchmark across the corpus → `results/` |
| `composer bench:phpbench` | phpbench parity run (the Tempest blog's methodology) |
| `composer examples` | Run every `examples/*.php` acid-test in order |

## Examples

Runnable, real-world acid tests in [`examples/`](examples/):

```bash
php examples/01-basic.php                 # markdown string → HTML
php examples/02-render-file.php [file.md] # render a real .md file → styled HTML page
php examples/03-batch-multicore.php       # 500 docs through the pthread batch path
php examples/04-compare-parsers.php       # this vs league vs tempest, head to head
```

## Tests

```bash
composer test
```

The suite (35 tests, ~300 assertions) covers CommonMark + all four GFM extensions, the nested-anchor fix, visible-text parity against `league/commonmark`, batch-vs-sequential equivalence, and a robustness battery: empty input, 1 MB of `#`, deeply nested structures, embedded NUL bytes, invalid UTF-8, and a 50k-iteration leak check. CI ([`.github/workflows/ci.yml`](.github/workflows/ci.yml)) builds and runs it on **Linux and macOS**.

---

## Caveats & honest take

- **Portability is on you.** The native artifact is per-platform; the C is portable, the binary isn't. `composer build` handles each OS, but you ship a `.so`/`.dylib`/`.dll` per target — that's the FFI tax.
- **It's a stunt with a real core.** md4c is mature, fuzzed, and widely deployed; the shim is small and audited (zero leaks, zero segfaults on hostile input, binary-safe); the output is as correct as league's GFM. *If* you have a PHP service rendering a lot of Markdown and *can* ship a native artifact, this is a legitimate ~13–59× speedup. Most people can't and shouldn't — use `league/commonmark`.
- **An AI wrote all of it.** Including this sentence. Review accordingly before trusting any of it.

The brief was to find the floor with every dirty trick available. The floor is about 200 MB/s, and PHP can reach it — it just has to call C to get there.

---

*Built with [Claude Code](https://claude.com/claude-code) · md4c © Martin Mitáš (MIT) · this repo MIT*

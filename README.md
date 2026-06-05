# helgesverre/markdown

A fast PHP Markdown parser that binds to the [md4c](https://github.com/mity/md4c) C library over FFI.

> **What this is:** a Claude Code "ultracode" experiment — built by an agent fleet in a single session — whose goal was to beat the `tempest/markdown` and `league/commonmark` benchmarks by any means necessary. It works: ~13–59× faster on the same input. But it's PHP calling C, not a faster PHP parser. The numbers are real and reproducible; for production work, [use a real PHP parser](#use-a-real-parser-instead).

## Requirements

- **PHP 8.5+** with `ext-ffi`
- A C compiler (`cc`/`clang`/`gcc`) **only if** you build from source — prebuilt binaries ship for the four supported platforms.

## Install

```bash
composer install
```

That's it. Prebuilt shared libraries ship in [`lib/`](lib/) and the right one is picked at runtime, so the supported platforms need no compiler:

| Platform | Artifact |
|---|---|
| macOS (Apple Silicon + Intel) | `lib/darwin/libmd4cshim.dylib` (universal) |
| Linux x86-64 (glibc) | `lib/linux-x86_64/libmd4cshim.so` |
| Linux aarch64 (glibc) | `lib/linux-aarch64/libmd4cshim.so` |
| Windows x64 | `lib/windows-x86_64/md4cshim.dll` |

`FfiParser::libPath()` resolves the artifact for your OS and architecture, checking in order:

1. `$MARKDOWN_FFI_LIB` (explicit override)
2. the shipped `lib/<platform>/` binary
3. a dev build in `native/`

## Usage

```php
use HelgeSverre\Markdown\FfiParser;

$html = (new FfiParser())->toHtml("# Hello\n\n- a\n- b\n");   // GFM dialect
```

Render many documents in one call — packed into a single buffer and parsed across an OS thread pool in C, returned in order:

```php
use HelgeSverre\Markdown\FfiBatchParser;

$htmls = (new FfiBatchParser())->toHtmlBatch($arrayOfMarkdownStrings);
```

## Build from source (optional)

Most people never need this — `composer install` already ships a binary for your platform. Build only for an unshipped target (musl/Alpine, FreeBSD, …) or to hack on the C.

```bash
composer build       # compile the shim for THIS platform → native/
composer build:all   # cross-compile all four shipped libraries → lib/
```

| Script | Tooling required | Output |
|---|---|---|
| `composer build` (`native/build.sh`) | a C compiler: `cc`, `clang`, or `gcc` | `libmd4cshim.{dylib,so,dll}` for the current platform, plus a regenerated FFI scope header |
| `composer build:all` (`native/build-all.sh`) | **`zig`** (Linux x86_64/aarch64 and Windows x64 targets, via `zig cc`) and **`clang`** (macOS universal `arm64`+`x86_64` slice) | `lib/darwin/`, `lib/linux-x86_64/`, `lib/linux-aarch64/`, `lib/windows-x86_64/` |

The C shim is Windows-safe — the batch path falls back to single-threaded where pthreads is absent.

## Composer scripts

| Command | What it does |
|---|---|
| `composer build` | Compile the native shim for this platform |
| `composer build:all` | Cross-compile the shipped libraries for all platforms (needs zig) |
| `composer test` | Run the PHPUnit suite |
| `composer check` | CI correctness gate (render parity + GFM + speed sanity) |
| `composer bench` | Full head-to-head benchmark across the corpus → `results/` |
| `composer bench:phpbench` | phpbench parity run (the Tempest blog's methodology) |
| `composer examples` | Run every `examples/*.php` script in order |

## Examples

```bash
php examples/01-basic.php                  # markdown string → HTML
php examples/02-render-file.php [file.md]  # render a real .md file → styled HTML page
php examples/03-batch-multicore.php        # 500 docs through the pthread batch path
php examples/04-compare-parsers.php        # this vs league vs tempest, head to head
```

---

## Benchmarks

Reproducible on this machine (PHP 8.5.5, Apple arm64) via `composer bench`. Each parser runs in its own process, instances reused, warmed up, timed over a ~1 s wall-clock budget. Full tables in [`results/RESULTS.md`](results/RESULTS.md).

**Real Tempest docs corpus** — 252.7 KB, the same input the [Tempest blog post](https://tempestphp.com/blog/tempest-markdown) benchmarked:

| Parser | mean ms | MB/s | peak MB | vs this |
|---|--:|--:|--:|--:|
| **helgesverre/markdown** (FFI→md4c) | **0.94** | **275.9** | 6.0 | — |
| league/commonmark (GFM) | 24.31 | 10.7 | 10.0 | ~26× slower |
| tempest/markdown | 40.71 | 6.4 | 8.0 | ~43× slower |

Throughput holds ~170–250 MB/s flat from 2 KB to 8 MB, because md4c's working set stays small while pure-PHP parsers build an AST that grows with the document. On an **8 MB** document this parser does **50 ms at 39 MB peak**; `league-gfm` takes **3,806 ms at 375 MB**.

Median across the whole corpus: **~13× faster than tempest, ~59× faster than league-gfm.**

The Tempest pitch is "we're ~5× faster than league." The question this repo answers is how much faster you can go if PHP stops parsing Markdown itself.

---

## How it beats the benchmarks

The whole approach is to not parse Markdown in PHP at all. md4c does the work in C; PHP just moves bytes in and out. Five things keep the FFI boundary cheap.

### 1. FFI → md4c

md4c is a *SAX-style* parser. Rather than building a document tree (an AST) in memory, it scans the input once and fires a callback for each piece of structure as it goes — "heading starts", "text", "heading ends", "list item starts" — then forgets it. (The name comes from [SAX](https://en.wikipedia.org/wiki/Simple_API_for_XML), the event-based XML parser that popularized the approach.) The payoff is a tiny, constant working set and streaming output, which is why md4c stays fast on huge documents where tree-building parsers balloon. It's used in production by Qt and others.

The parser is bound directly from PHP — PHP never touches a parse tree. It hands md4c a `(char*, len)` and gets finished HTML back.

### 2. A flat-ABI C shim so no callbacks cross FFI

md4c's native API calls *you* for every block and inline span. Routing those callbacks into PHP closures through FFI would mean a context switch per token. Instead, [`native/shim.c`](native/shim.c) buffers the whole document inside C (a `realloc`-backed membuf) and exposes one function:

```c
char* md2html(const char* input, size_t input_len, size_t* out_len,
              unsigned int parser_flags, unsigned int renderer_flags);
void  md2html_free(char* p);
unsigned int md2html_dialect_github(void);   // 0x0F0C: autolinks|tables|strikethrough|tasklists
```

One FFI call in, one pointer out, one `free` after. The boundary is crossed twice per document, never per token.

### 3. `FFI::load` + `opcache.preload` scope warming

Naïve FFI re-parses the C header and `dlopen`s the library on every request. [`bench/preload.php`](bench/preload.php) runs `FFI::load()` against a header carrying `#define FFI_SCOPE "MD4C"` at preload time, so each request binds via `FFI::scope("MD4C")` — no per-request `cdef`, no per-request `dlopen`. [`FfiParser`](src/FfiParser.php) falls back to `FFI::cdef` automatically when preload isn't active.

### 4. A pthreads multi-core batch path

[`md2html_batch`](native/shim.c) spawns an OS thread pool (`min(cores, batch_size)`), strides documents across workers, and concatenates results in order — md4c is reentrant, so the parse itself needs no locking. [`FfiBatchParser`](src/FfiBatchParser.php) packs N documents into one buffer plus an offset table and makes a single FFI call for the whole batch. Measured ~2× over the sequential path on 500 small docs (`composer examples` → `03-batch-multicore`).

### 5. An in-C anchor-collapse correctness pass

md4c's permissive autolinking re-wraps the *text* of an explicit `[url](url)` link, emitting invalid nested `<a><a>`. A single in-place pass in the shim collapses anchors nested inside anchors, so output matches `league-gfm` exactly on anchor count. It costs ~12% throughput, paid inside the timed path.

**What didn't help: fibers and streams.** Parsing one document is CPU-bound and synchronous — there's no I/O to overlap, so cooperative fibers buy nothing, and you can't yield through a tight C loop. Real multi-document throughput needs real parallelism, which is why the batch path uses OS threads in C rather than PHP fibers.

---

## Caveats

The methodology is honest like-for-like steady-state benchmarking: same input, instances reused, empty output rejected, same JIT for everyone. The asymmetries, stated plainly:

- **It's PHP calling C.** That's the point, but read the result as such — not a faster *PHP* parser, just PHP getting out of md4c's way.
- **tempest does strictly more work** (heading slugs + a syntax highlighter), so its output is 64% larger (474 KB vs 289 KB) and part of its time isn't parsing. The apples-to-apples comparison is the `league-gfm` one — same GFM feature set, structurally identical HTML.
- **The memory column undercounts the win.** Output lives on the C heap, which PHP's `memory_get_peak_usage()` can't see. Measured with `/usr/bin/time -l` (which does count it), the real-RSS win on a 1 MB doc is ~2.3×, not the ~8× the Zend-only column suggests.
- **The slowest rows rest on few samples** (8 MB × league ≈ 3 iterations). The trend is clear; those specific point estimates are low-confidence.

On correctness, this parser matches `league` on GFM: visible text matches on every corpus file, anchor counts match exactly after the collapse pass, and all four GFM extensions render correctly. It's also *more* robust than `tempest`, which leaves some list items as literal paragraph text (`A list:\n- one` → `<p>A list:\n- one</p>`), fails setext headings, and — because it treats every leading `---` as mandatory YAML front matter, scans unbounded for a closing `---`, and never accepts the valid YAML `...` terminator — throws a hard exception on any document that simply opens with a `---` thematic break or uses `...`-closed front matter. That last case is exactly why it errors out on the CommonMark spec (md4c and `league` both render it fine).

## Tests

```bash
composer test
```

Five test files cover CommonMark plus all four GFM extensions, the nested-anchor collapse, visible-text parity against `league/commonmark`, batch-vs-sequential equivalence, the shipped-library guard, and a robustness battery: empty input, 1 MB of `#`, deeply nested structures, embedded NUL bytes, invalid UTF-8, and a leak check. CI ([`.github/workflows/ci.yml`](.github/workflows/ci.yml)) builds and runs them on Linux and macOS.

---

## Use a real parser instead

This repo is a benchmark experiment, not a Markdown library you should depend on. For real work, reach for one of these — both are pure PHP, need no native artifact, and are the libraries this project benchmarks against:

- **[`league/commonmark`](https://commonmark.thephpleague.com/)** — the de-facto standard PHP Markdown library. Strict CommonMark via `CommonMarkConverter`, GitHub-flavored Markdown via `GithubFlavoredMarkdownConverter`. Mature, extensible, widely used. If you're unsure, use this.
- **[`tempest/markdown`](https://tempestphp.com/blog/tempest-markdown)** — the Markdown parser from the [Tempest framework](https://tempestphp.com), with a bundled syntax highlighter and heading-anchor generator. A good fit if you're already in the Tempest ecosystem or want highlighting and slugs out of the box.

---

*Built with [Claude Code](https://claude.com/claude-code) · md4c © Martin Mitáš (MIT) · this repo MIT*

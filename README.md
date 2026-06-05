# helgesverre/markdown

[![CI](https://github.com/HelgeSverre/markdown/actions/workflows/ci.yml/badge.svg)](https://github.com/HelgeSverre/markdown/actions/workflows/ci.yml)

A fast PHP Markdown parser that binds to the [md4c](https://github.com/mity/md4c) C library over FFI.

> **What this is:** it started as a Claude Code "ultracode" experiment — an agent fleet in a single session, seeing how far it could beat the `tempest/markdown` and `league/commonmark` benchmarks. It worked (~13–59× faster on the same input), and it's grown into a real, tested GFM parser with front matter, heading anchors, and a table of contents. The one honest caveat: it's PHP calling C, so you ship a small native binary per platform. If that's a dealbreaker, a [pure-PHP parser](#alternatives) is the better fit.

## Install

```bash
composer require helgesverre/markdown
```

Needs **PHP 8.5+** with the **FFI** extension. FFI ships with PHP — on the CLI it's on by default; for web/FPM set `ffi.enable=1` (or `preload`) in `php.ini`. **No C compiler required**: a prebuilt shared library for your platform is bundled and selected at runtime.

| Platform                      | Min glibc                      | Artifact                                   |
| ----------------------------- | ------------------------------ | ------------------------------------------ |
| macOS (Apple Silicon + Intel) | —                              | `lib/darwin/libmd4cshim.dylib` (universal) |
| Linux x86-64                  | 2.14 (≈ any distro since 2011) | `lib/linux-x86_64/libmd4cshim.so`          |
| Linux aarch64                 | 2.17                           | `lib/linux-aarch64/libmd4cshim.so`         |
| Windows x64                   | —                              | `lib/windows-x86_64/md4cshim.dll`          |

> Verified clean-room: `composer require` on a stock `php:8.5-cli` Linux container loads the bundled `.so` and renders correctly — no system packages beyond the FFI extension. Every binary is exercised on its real platform in CI.

`Library::path()` (in `HelgeSverre\Markdown\Ffi`) resolves the artifact for your OS and architecture, checking in order:

1. `$MARKDOWN_FFI_LIB` (explicit override)
2. the bundled `lib/<platform>/` binary
3. a dev build in `native/` (for unshipped targets — `composer build`)

Developing this repo instead of consuming it? Clone and run `composer install`.

## Usage

### Render Markdown to HTML

```php
use HelgeSverre\Markdown\Markdown;

$html  = Markdown::toHtml("# Hello\n\n- a\n- b\n");        // one document → GFM HTML
$htmls = Markdown::toHtmlBatch($arrayOfMarkdownStrings);  // many, across CPU cores in C
```

The `Markdown` facade reuses a single parser instance under the hood. For explicit lifecycle control, construct [`Parser`](src/Parser.php) / [`BatchParser`](src/BatchParser.php) directly:

```php
use HelgeSverre\Markdown\Parser;

$parser = new Parser();
$html   = $parser->toHtml("# Hello\n");
```

`toHtmlBatch` packs all documents into a single buffer and parses them across an OS thread pool in C, returning HTML in order. `toHtml()` is the fast path — raw Markdown in, HTML out, nothing else.

### Parse a document: front matter + table of contents

`parse()` does the document-level work md4c alone doesn't: it strips YAML front matter, adds GitHub-style `id` anchors to headings, and builds a table of contents. It returns a [`ParsedMarkdown`](src/Data/ParsedMarkdown.php), which casts to its HTML string.

```php
use HelgeSverre\Markdown\Markdown;

$doc = <<<MD
---
title: Hello World
tags: [php, markdown]
---
# Introduction

## Getting started
MD;

$result = Markdown::parse($doc);

$result->html;         // <h1 id="introduction">Introduction</h1>\n<h2 id="getting-started">…
$result->frontmatter;  // ['title' => 'Hello World', 'tags' => ['php', 'markdown']]
$result->toc;          // [['level' => 1, 'text' => 'Introduction', 'slug' => 'introduction'], …]
(string) $result;      // same as $result->html
```

Front matter is parsed in PHP with [`symfony/yaml`](https://symfony.com/doc/current/components/yaml.html) (a dependency, installed automatically); malformed front matter degrades to `[]` rather than throwing. Heading anchoring and the table of contents run in **C** — a single `md2html_anchor` pass over the rendered HTML injects GitHub-style slugs (lower-cased, ASCII-folded, de-duplicated: `intro`, `intro-1`, …) and collects the TOC, making `parse()` 1.6–3× faster than the equivalent PHP pass. All of it runs **only** on `parse()` — the `toHtml()` fast path is untouched.

### Options: dialect, safe HTML, XHTML

The parser constructors take a few options (defaults shown):

```php
use HelgeSverre\Markdown\Parser;
use HelgeSverre\Markdown\Data\Dialect;

new Parser(
    dialect: Dialect::GitHub,   // ::GitHub (tables, strikethrough, task lists, autolinks) or ::CommonMark (strict)
    safe: false,                // true → strip raw HTML (md4c MD_FLAG_NOHTML), for rendering untrusted input
    xhtml: false,               // true → self-closing void tags (<br />, <hr />)
);
```

`BatchParser` takes the same options. The `Markdown` facade uses the defaults; for a different dialect or safe mode, construct a `Parser`/`BatchParser` yourself.

## Build from source (optional)

Most people never need this — `composer install` already ships a binary for your platform. Build only for an unshipped target (musl/Alpine, FreeBSD, …) or to hack on the C.

```bash
composer build       # compile the shim for THIS platform → native/
composer build:all   # cross-compile every shipped library → lib/
```

| Script                                       | Tooling required                                                                                                                | Output                                                                                     |
| -------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------ |
| `composer build` (`native/build.sh`)         | a C compiler: `cc`, `clang`, or `gcc`                                                                                           | `libmd4cshim.{dylib,so,dll}` for the current platform, plus a regenerated FFI scope header |
| `composer build:all` (`native/build-all.sh`) | **`zig`** (Linux x86_64/aarch64 and Windows x64 targets, via `zig cc`) and **`clang`** (macOS universal `arm64`+`x86_64` slice) | `lib/darwin/`, `lib/linux-x86_64/`, `lib/linux-aarch64/`, `lib/windows-x86_64/`            |

The C shim is Windows-safe — the batch path falls back to single-threaded where pthreads is absent.

## Composer scripts

| Command              | What it does                                                                |
| -------------------- | --------------------------------------------------------------------------- |
| `composer build`     | Compile the native shim for this platform                                   |
| `composer build:all` | Cross-compile the shipped libraries for all platforms (needs zig)           |
| `composer test`      | Run the PHPUnit suite                                                       |
| `composer check`     | CI correctness gate (render parity + GFM + speed sanity)                    |
| `composer bench`     | Full PHPBench suite (HTML throughput + front matter) → `results/RESULTS.md` |
| `composer examples`  | Run every `examples/*.php` script in order                                  |

## Examples

```bash
php examples/01-basic.php                  # markdown string → HTML
php examples/02-render-file.php [file.md]  # render a real .md file → styled HTML page
php examples/03-batch-multicore.php        # 500 docs through the pthread batch path
php examples/04-compare-parsers.php        # this vs league vs tempest, head to head
php examples/05-parse-document.php         # front matter + heading anchors + TOC + options
```

---

## Benchmarks

Reproducible on this machine (PHP 8.5.5, Apple arm64) via `composer bench`, which runs the whole [PHPBench](https://phpbench.readthedocs.io) suite. Every parser runs with identical flags (opcache + tracing JIT + `ffi.enable` + the FFI preload), instances reused, 2 warmup + 50 revolutions × 10 iterations. Full tables in [`results/RESULTS.md`](results/RESULTS.md). (The 1 MB and 8 MB scaling tiers aren't committed — run `composer corpus` to regenerate them; the benchmark skips any corpus file that's absent.)

**Real Tempest docs corpus** — 252.7 KB, the same input the [Tempest blog post](https://tempestphp.com/blog/tempest-markdown) benchmarked:

| Parser                              |  mean ms |      MB/s | peak MB |     vs this |
| ----------------------------------- | -------: | --------: | ------: | ----------: |
| **helgesverre/markdown** (FFI→md4c) | **0.97** | **266.8** |     6.0 |           — |
| league/commonmark (GFM)             |    25.52 |      10.1 |    10.0 | ~26× slower |
| tempest/markdown                    |    42.41 |       6.1 |     8.0 | ~44× slower |

Throughput holds ~160–270 MB/s flat from 2 KB to 8 MB, because md4c's working set stays small while pure-PHP parsers build an AST that grows with the document. On an **8 MB** document this parser does **51 ms at 41 MB peak**; `league-gfm` takes **3,804 ms at 375 MB**.

Median across the whole corpus: **~13× faster than tempest, ~59× faster than league-gfm.**

The Tempest pitch is "we're ~5× faster than league." The question this repo answers is how much faster you can go if PHP stops parsing Markdown itself.

### Front-matter extraction

`composer bench` also covers front-matter extraction as its own group — isolating one feature, pulling the YAML front matter off a document, compared against raw `symfony/yaml` (the floor) and the other parsers:

| Approach                                          | mean µs | renders body? | vs floor |
| ------------------------------------------------- | ------: | :-----------: | -------: |
| symfony/yaml (raw floor)                          |     ~44 |      no       |    1.00× |
| **helgesverre/markdown** (`FrontMatter::extract`) |     ~48 |      no       |    0.92× |
| league/commonmark (`FrontMatterParser`)           |     ~51 |      no       |    0.88× |
| tempest/markdown (`parse()`)                      |    ~431 |      yes      |    0.10× |

Our dedicated extractor lands within a hair of the raw `symfony/yaml` floor — it adds only a cheap split on top of the same YAML parse. Parsers with no front-matter-only API (tempest) must render the whole document just to read its header, so they pay ~10×. (Front matter never touches the C shim — md4c has no YAML; it's a PHP-side concern.)

---

## How it beats the benchmarks

The whole approach is to not parse Markdown in PHP at all. md4c does the work in C; PHP just moves bytes in and out. A handful of tricks keep the FFI boundary cheap.

### 1. FFI → md4c

md4c is a _SAX-style_ parser. Rather than building a document tree (an AST) in memory, it scans the input once and fires a callback for each piece of structure as it goes — "heading starts", "text", "heading ends", "list item starts" — then forgets it. (The name comes from [SAX](https://en.wikipedia.org/wiki/Simple_API_for_XML), the event-based XML parser that popularized the approach.) The payoff is a tiny, constant working set and streaming output, which is why md4c stays fast on huge documents where tree-building parsers balloon. It's used in production by Qt and others.

The parser is bound directly from PHP — PHP never touches a parse tree. It hands md4c a `(char*, len)` and gets finished HTML back.

### 2. A flat-ABI C shim so no callbacks cross FFI

md4c's native API calls _you_ for every block and inline span. Routing those callbacks into PHP closures through FFI would mean a context switch per token. Instead, [`native/shim.c`](native/shim.c) buffers the whole document inside C (a `realloc`-backed membuf) and exposes a small, flat C surface — the hot path is a single function:

```c
char* md2html(const char* input, size_t input_len, size_t* out_len,
              unsigned int parser_flags, unsigned int renderer_flags);
void  md2html_free(char* p);
unsigned int md2html_dialect_github(void);   // 0x0F0C: autolinks|tables|strikethrough|tasklists
```

One FFI call in, one pointer out, one `free` after. The boundary is crossed twice per document, never per token.

### 3. `FFI::load` + `opcache.preload` scope warming

Naïve FFI re-parses the C header and `dlopen`s the library on every request. [`bench/preload.php`](bench/preload.php) runs `FFI::load()` against a header carrying `#define FFI_SCOPE "MD4C"` at preload time, so each request binds via `FFI::scope("MD4C")` — no per-request `cdef`, no per-request `dlopen`. [`Ffi\Library`](src/Ffi/Library.php) falls back to `FFI::cdef` automatically when preload isn't active.

### 4. A pthreads multi-core batch path

[`md2html_batch`](native/shim.c) spawns an OS thread pool (`min(cores, batch_size)`), strides documents across workers, and concatenates results in order — md4c is reentrant, so the parse itself needs no locking. [`BatchParser`](src/BatchParser.php) packs N documents into one buffer plus an offset table and makes a single FFI call for the whole batch. Measured ~2× over the sequential path on 500 small docs (`composer examples` → `03-batch-multicore`).

### 5. An in-C anchor-collapse correctness pass

md4c's permissive autolinking re-wraps the _text_ of an explicit `[url](url)` link, emitting invalid nested `<a><a>`. A single in-place pass in the shim collapses anchors nested inside anchors, so output matches `league-gfm` exactly on anchor count. It costs ~12% throughput, paid inside the timed path.

**What didn't help: fibers and streams.** Parsing one document is CPU-bound and synchronous — there's no I/O to overlap, so cooperative fibers buy nothing, and you can't yield through a tight C loop. Real multi-document throughput needs real parallelism, which is why the batch path uses OS threads in C rather than PHP fibers.

---

## Caveats

The methodology is honest like-for-like steady-state benchmarking: same input, instances reused, identical PHP flags for every parser (PHPBench — 2 warmup, 50 revolutions × 10 iterations). The asymmetries, stated plainly:

- **It's PHP calling C.** That's the point, but read the result as such — not a faster _PHP_ parser, just PHP getting out of md4c's way.
- **tempest does strictly more work** (heading slugs + a syntax highlighter), so its output is 64% larger (474 KB vs 289 KB) and part of its time isn't parsing. The apples-to-apples comparison is the `league-gfm` one — same GFM feature set, structurally identical HTML.
- **The memory column undercounts the win.** Output lives on the C heap, which PHP's `memory_get_peak_usage()` can't see. Measured with `/usr/bin/time -l` (which does count it), the real-RSS win on a 1 MB doc is ~2.3×, not the ~8× the Zend-only column suggests.
- **The slowest rows rest on few samples** (8 MB × league ≈ 3 iterations). The trend is clear; those specific point estimates are low-confidence.

On correctness, this parser matches `league` on GFM: visible text matches on every corpus file, anchor counts match exactly after the collapse pass, and the GFM extensions (tables, strikethrough, task lists, autolinks) render correctly. It's also _more_ robust than `tempest`, which leaves some list items as literal paragraph text (`A list:\n- one` → `<p>A list:\n- one</p>`), fails setext headings, and — because it treats every leading `---` as mandatory YAML front matter, scans unbounded for a closing `---`, and never accepts the valid YAML `...` terminator — throws a hard exception on any document that simply opens with a `---` thematic break or uses `...`-closed front matter. That last case is exactly why it errors out on the CommonMark spec (md4c and `league` both render it fine).

## Tests

```bash
composer test
```

The suite in [`tests/`](tests/) covers CommonMark and the GFM extensions, the dialect/safe/XHTML options, the nested-anchor collapse, `parse()` (front matter, heading slugs + de-dup, table of contents), visible-text parity against `league/commonmark`, batch-vs-sequential equivalence, the shipped-library guard, and a robustness battery: empty input, megabytes of `#`, many identical headings (linear-time de-dup), deeply nested structures, embedded NUL bytes, invalid UTF-8, and a leak check. CI ([`.github/workflows/ci.yml`](.github/workflows/ci.yml)) builds and runs them on Linux and macOS.

---

## Alternatives

It began as a benchmark experiment, and the tradeoff it makes is real: it's PHP calling C, so you ship a native binary per platform. If you'd rather not — you want pure PHP with no native artifact, or you're already in the Tempest ecosystem — these are excellent, and they're what this project benchmarks against:

- **[`league/commonmark`](https://commonmark.thephpleague.com/)** — the de-facto standard PHP Markdown library. Strict CommonMark via `CommonMarkConverter`, GitHub-flavored Markdown via `GithubFlavoredMarkdownConverter`. Mature, extensible, widely used. If you're unsure, use this.
- **[`tempest/markdown`](https://tempestphp.com/blog/tempest-markdown)** — the Markdown parser from the [Tempest framework](https://tempestphp.com), with a bundled syntax highlighter and heading-anchor generator. A good fit if you're already in the Tempest ecosystem or want highlighting and slugs out of the box.

---

_Built with [Claude Code](https://claude.com/claude-code) · md4c © Martin Mitáš (MIT) · this repo MIT_

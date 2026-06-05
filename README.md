# helgesverre/markdown

[![CI](https://github.com/HelgeSverre/markdown/actions/workflows/ci.yml/badge.svg)](https://github.com/HelgeSverre/markdown/actions/workflows/ci.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/markdown.svg)](https://packagist.org/packages/helgesverre/markdown)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/markdown.svg)](https://packagist.org/packages/helgesverre/markdown)
[![License](https://img.shields.io/packagist/l/helgesverre/markdown.svg)](https://packagist.org/packages/helgesverre/markdown)

A fast PHP Markdown parser backed by [md4c](https://github.com/mity/md4c) through PHP FFI.

It renders GitHub-flavored Markdown, supports front matter and heading TOCs, and ships prebuilt native libraries so normal installs do not need a C compiler.

## Install

```bash
composer require helgesverre/markdown
```

Requirements:

- PHP 8.5+
- `ext-ffi`
- `ffi.enable=1` for web/FPM use, or an opcache preload setup

Bundled native artifacts are selected at runtime:

| Platform | Artifact |
| --- | --- |
| macOS Apple Silicon + Intel | `lib/darwin/libmd4cshim.dylib` |
| Linux x86-64 | `lib/linux-x86_64/libmd4cshim.so` |
| Linux aarch64 | `lib/linux-aarch64/libmd4cshim.so` |
| Windows x64 | `lib/windows-x86_64/md4cshim.dll` |

`HelgeSverre\Markdown\Ffi\Library::path()` resolves libraries in this order:

1. `$MARKDOWN_FFI_LIB`
2. the bundled `lib/<platform>/` binary
3. a local `native/` build

## Usage

### Render HTML

```php
use HelgeSverre\Markdown\Markdown;

$html = Markdown::toHtml("# Hello\n\n- a\n- b\n");

$htmls = Markdown::toHtmlBatch([
    "# One\n",
    "# Two\n",
]);
```

`toHtml()` is the fast path: Markdown in, HTML out. `toHtmlBatch()` packs many documents into one native call and renders them across a C thread pool where pthreads are available.

For explicit lifecycle and options, construct the parser directly:

```php
use HelgeSverre\Markdown\Data\Dialect;
use HelgeSverre\Markdown\Parser;

$parser = new Parser(
    dialect: Dialect::GitHub,
    safe: false,
    xhtml: false,
);

$html = $parser->toHtml("# Hello\n");
```

### Parse Documents

`parse()` strips YAML front matter, renders the body, injects GitHub-style heading ids, and returns a `ParsedMarkdown` value with HTML, front matter, and TOC data.

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

$result->html;
$result->frontmatter; // ['title' => 'Hello World', 'tags' => ['php', 'markdown']]
$result->toc;         // [['level' => 1, 'text' => 'Introduction', 'slug' => 'introduction'], ...]
(string) $result;     // same as $result->html
```

Malformed front matter degrades to an empty array. Heading ids are lower-cased, ASCII-folded, and de-duplicated with suffixes like `intro-1`.

Front matter is decoded by a vendored [libyaml](https://github.com/yaml/libyaml) FFI path (parsed to JSON in C, then `json_decode`d) — no pure-PHP YAML parser is involved. Inputs libyaml's walker does not support — anchors/aliases and `<<` merge keys — degrade to an empty array, the same as malformed YAML.

> **Date scalars are strings.** A bare `date: 2026-06-05` in front matter is returned as the string `"2026-06-05"` (matching PECL `yaml`, spyc, and dallgoot). This differs from `symfony/yaml`'s default, which resolves it to an integer Unix timestamp. Quote or post-process if you need a different type.

### Options

```php
use HelgeSverre\Markdown\Data\Dialect;
use HelgeSverre\Markdown\Parser;

new Parser(
    dialect: Dialect::GitHub, // or Dialect::CommonMark
    safe: true,               // strip raw HTML
    xhtml: true,              // emit <br /> / <hr />
);
```

`BatchParser` accepts the same options. The `Markdown` facade uses the defaults.

## Benchmarks

Run the full suite with:

```bash
composer bench
```

Fresh run from this checkout: PHP 8.5.5, Darwin arm64, PHPBench, opcache + tracing JIT + FFI preload. Full generated tables live in [`results/RESULTS.md`](results/RESULTS.md), with machine-readable rows in [`results/results.json`](results/results.json). The default corpus caps at ~256 KB (realistic document sizes plus two real-world corpora); the 1 MB and 8 MB scaling tiers are opt-in via `composer bench:stress` (run `composer corpus` first to generate them).

### HTML Throughput Snapshot

`toHtml()` (render only) against the default corpus:

| Corpus | helgesverre/markdown | league/commonmark GFM | tempest/markdown |
| --- | ---: | ---: | ---: |
| `doc-128kb.md` (135 KB) | **0.71 ms** / 195 MB/s | 40.21 ms / 3.4 MB/s | 10.78 ms / 12.8 MB/s |
| `commonmark-spec.md` (165 KB) | **0.86 ms** / 197 MB/s | 27.73 ms / 6.1 MB/s | — (threw) |
| `tempest-docs.md` (252 KB) | **0.83 ms** / 313 MB/s | 23.74 ms / 10.9 MB/s | 24.22 ms / 10.7 MB/s |

On the 252 KB Tempest docs corpus, the render fast path measured about 29x faster than `league/commonmark` GFM and about 29x faster than `tempest/markdown`. The full `parse()` pipeline (front matter + render + heading anchors + TOC) is benchmarked too — on that corpus it runs in ~1.13 ms (229 MB/s), still ~21x faster than either.

### Front Matter

`extract()` pulls the YAML front matter without rendering the body (vendored libyaml in C → JSON → `json_decode`):

| Approach | Mean | Renders body? |
| --- | ---: | :---: |
| `helgesverre/markdown` extract only | **6.39 us** | no |
| `helgesverre/markdown` full parse | 32.04 us | yes |
| `symfony/yaml` floor | 334.67 us | no |
| `league/commonmark` front matter only | 380.96 us | no |
| `tempest/markdown` lex (no render) | 455.11 us | no |
| `tempest/markdown` full parse | 1,012.87 us | yes |

Front-matter extraction measured about 52x faster than the `symfony/yaml` floor and about 60x faster than `league/commonmark`'s dedicated front-matter parser. (`tempest/markdown` has no dedicated front-matter API — `lex()` is its cheapest path, full `parse()` its idiomatic one.)

Memory numbers in the benchmark output need context: this parser renders into a short-lived C heap buffer before copying HTML back into PHP, so PHP's memory metrics undercount part of its transient native allocation. Pure-PHP parsers keep their work on the Zend heap.

## How It Works

The hot path is one FFI call into a small C shim around md4c:

```c
char* md2html(const char* input, size_t input_len, size_t* out_len,
              unsigned int parser_flags, unsigned int renderer_flags);
void  md2html_free(char* p);
```

md4c renders through callbacks internally, but those callbacks stay in C. PHP passes a byte string in, receives one allocated HTML buffer back, copies it with `FFI::string()`, and frees it.

Front matter uses the same one-call shape: `yaml2json()` walks [libyaml](https://github.com/yaml/libyaml)'s event stream into a single JSON string in C, which PHP `json_decode`s — no per-node FFI crossings. libyaml is vendored and statically linked into the shim, so the shipped binaries carry no external runtime dependency.

For production, `bench/preload.php` can warm an `FFI::load()` scope through opcache preload. Without preload, the library falls back to `FFI::cdef()` automatically.

The shim also includes a small correctness pass for md4c's permissive autolinks: explicit links whose text is itself an autolinkable URL can otherwise become invalid nested anchors. The pass collapses that generated shape while preserving user-supplied raw nested anchors.

## Build From Source

Most users do not need this. Build from source when hacking on the C shim or targeting an unshipped platform.

```bash
composer build       # current platform -> native/
composer build:all   # all shipped platforms -> lib/
```

`composer build` needs a local C compiler. `composer build:all` uses `clang` for the macOS universal binary and `zig cc` for Linux and Windows cross-builds.

## Scripts

| Command | What it does |
| --- | --- |
| `composer test` | Run PHPUnit |
| `composer check` | Run the CI correctness smoke gate |
| `composer bench` | Run PHPBench and regenerate `results/` |
| `composer bench:stress` | Run the throughput bench against the 1 MB / 8 MB tiers |
| `composer examples` | Run every example script |
| `composer build` | Build the native shim for this platform |
| `composer build:all` | Cross-build shipped libraries |
| `composer format:check` | Check formatting with Mago |
| `composer lint` | Run Mago lint |

## Tests

```bash
composer test
```

The suite covers GFM rendering, dialect/safe/XHTML options, generated anchor collapse without raw HTML rewrites, document parsing, front matter, heading slugs and TOCs, structural parity against `league/commonmark`, batch-vs-sequential output, shipped-library binding, hostile inputs, embedded NUL bytes, and leak checks.

CI runs the shipped binaries on Linux and macOS, keeps an experimental Windows shipped-binary job, and also builds the Linux shim from source.

## Alternatives

- [`league/commonmark`](https://commonmark.thephpleague.com/) is the mature pure-PHP default. If you want extensibility and no native artifact, start there.
- [`tempest/markdown`](https://tempestphp.com/blog/tempest-markdown) is a good fit inside the Tempest ecosystem, especially if you want its bundled syntax highlighting and heading behavior.

## License

MIT. Bundled under their own MIT licenses: [md4c](https://github.com/mity/md4c) (Martin Mitáš) for Markdown parsing and [libyaml](https://github.com/yaml/libyaml) (Kirill Simonov et al.) for front-matter YAML — see [`THIRD_PARTY.md`](THIRD_PARTY.md).

# Consolidate benchmarking onto PHPBench

**Date:** 2026-06-05
**Status:** Approved (design), pending implementation plan
**Author:** Helge Sverre (with Claude Code)

## Problem

The repo has three separate benchmark entry points, only one of which uses PHPBench:

| Command | Runs | Engine |
|---|---|---|
| `composer bench` | `php bench/run.php` | bespoke `proc_open` orchestrator |
| `composer bench:frontmatter` | `php bench/frontmatter.php` | bespoke standalone script |
| `composer bench:phpbench` | `phpbench run bench/MarkdownBench.php` | PHPBench |

Each custom script has its own ad-hoc output, timing loop, and progress chatter. The
goal is to make **PHPBench the single measurement engine** and eliminate the scattered
bespoke scripts, while preserving the showcase-quality `results/RESULTS.md`.

## Decisions (locked)

1. **One clean formatter.** PHPBench owns *all* measurement. A single script
   (`bench/format.php`) reads PHPBench's XML dump and emits the polished `RESULTS.md`.
   This is the only custom script that survives.
2. **One command.** `composer bench` runs the entire PHPBench suite (groups
   `throughput` + `frontmatter`), then runs the formatter. `bench:frontmatter` and
   `bench:phpbench` are removed.
3. **Uniform flags.** A single executor / one global `runner.php_config` applies
   identical PHP flags to every parser (`opcache.enable_cli`, `jit=tracing`,
   `jit_buffer_size`, `ffi.enable`, `opcache.preload=bench/preload.php`). The preload
   only warms *our* FFI handle; for the pure-PHP parsers it is inert. Narrative becomes
   "same env for everyone — our parser wins on merit + a legitimately-preloaded handle,"
   which is harder to accuse of cheating than asymmetric flags.

## End state

```
composer bench
  → vendor/bin/phpbench run --dump-file=results/raw.xml --progress=none
  → php bench/format.php results/raw.xml
      → results/RESULTS.md    (HTML-throughput section + front-matter section)
      → results/results.json  (flat machine-readable rows, kept for parity)
```

A subset can still be run directly, e.g. `vendor/bin/phpbench run --group=frontmatter`.

## File changes

### New

- **`phpbench.json`** (repo root) — single source of benchmark config:
  - `runner.bootstrap`: `vendor/autoload.php`
  - `runner.path`: `bench`
  - `runner.file_pattern`: `*Bench.php`
  - `runner.php_config` (uniform, all parsers):
    `opcache.enable_cli=1`, `opcache.jit=tracing`, `opcache.jit_buffer_size=64M`,
    `ffi.enable=1`, `opcache.preload=bench/preload.php`
  - default cadence (may also be set per-class via attributes):
    `@Warmup(2) @Revs(50) @Iterations(10) @RetryThreshold(2.0)`

- **`bench/format.php`** — the only custom script. Parses `results/raw.xml`, computes
  derived columns PHPBench does not emit natively, writes `RESULTS.md` + `results.json`.

### Refactor

- **`bench/MarkdownBench.php` → `bench/ThroughputBench.php`** (group `throughput`):
  - One subject per parser: `benchHelgesverre`, `benchTempest`, `benchLeagueGfm`,
    `benchLeagueStrict`.
  - `setUp()` builds the parser callables by `require`-ing the existing
    `bench/parsers.php` registry (instances constructed once, outside timing).
  - Param provider `provideCorpus()` reads `corpus/manifest.json` and yields one param
    set per **existing** file: `{ path, bytes, label }`. Absent tiers (uncommitted
    1 MB / 8 MB) are skipped, matching `run.php` behavior.

- **`bench/frontmatter.php` → `bench/FrontMatterBench.php`** (group `frontmatter`):
  - One subject per extraction approach: `benchSymfonyYaml`, `benchOursExtract`,
    `benchLeagueFrontmatter`, `benchTempestFull`, `benchOursFull`.
  - Same baked blog-post document as today (built in `setUp()`).
  - The "renders body?" asymmetry and per-row notes live in a descriptor map in
    `format.php`, keyed by subject name.

### Delete (the bespoke measurement engine)

- `bench/run.php`
- `bench/once.php`
- `bench/frontmatter.php`
- `bench/MarkdownBench.php` (superseded by `ThroughputBench.php`)

### Keep untouched

- `bench/parsers.php` — reused by `ThroughputBench`.
- `bench/preload.php` — now the uniform preload for all parsers.
- `bench/ci_check.php` — CI correctness gate (parity + GFM + speed sanity), not a
  benchmark.
- `bench/which-lib.php`, `bench/smoke_ffi.php`, etc. — out of scope.

## Corpus loading (the one tricky mechanic)

The corpus ranges from ~2 KB to ~8 MB. File **contents must not** be serialized into
PHPBench's generated remote script (it `var_export`s params; 8 MB would bloat badly).

Approach: the param provider yields `{ path, bytes, label }` (not contents). The subject
memoizes the file read on first call into an instance cache keyed by path. Because
PHPBench runs `@Warmup` revs before the timed revs within each iteration-process, the
read lands in warmup and is amortized **out of** the measured revs. The memoized doc is
reused across the timed revs (cache hit).

## The formatter (`bench/format.php`)

Input: `results/raw.xml` (PHPBench dump — confirmed to carry subject name, parameter set
incl. our `bytes`/`label`, `revs`, and per-variant stats incl. `mode`).

Per corpus (grouped by param `label`), compute:

- **ops/sec** = `1e6 / mode_µs`
- **MB/s** = `bytes / (mode_µs / 1e6) / 1e6`
- **mean ms**, **peak MB**
- **vs tempest**, **vs league-gfm** ratios
- 🏆 winner marker (highest ops/sec, no error)
- one-line prose headline (median speedups across corpora)

Maps:
- subject name → friendly parser id (e.g. `benchHelgesverre` → `helgesverre/markdown`).
- front-matter subject → `{ label, rendersBody, note }` descriptor.

Output: `results/RESULTS.md` (structure matches today — HTML-throughput tables per
corpus, then the front-matter table + notes) and `results/results.json` (flat rows for
parity with the current artifact).

## composer.json

```jsonc
"scripts": {
  "bench": [
    "phpbench run --dump-file=results/raw.xml --progress=none",
    "@php bench/format.php results/raw.xml"
  ]
  // bench:frontmatter and bench:phpbench removed
}
```
Update `scripts-descriptions` accordingly.

## Methodology footnote changes (honest disclosure in RESULTS.md)

- **Uniform flags**: "identical flags for every parser" replaces "ours gets warm-FFI,
  contenders run plain."
- **Fixed cadence**: warmup + fixed revs/iterations + retry-threshold (variance-gated)
  replaces the adaptive ~1 s wall-clock budget. Each iteration is a fresh process, so
  JIT warms across revs within each iteration (warmup revs discarded).
- **C-heap memory caveat unchanged** (kept verbatim): our FFI parser renders onto the C
  heap (md4c `malloc`), invisible to PHP's `peak_mb`, so its memory number is
  real-RSS-favorable. Pure-PHP parsers are fully accounted.

## README & CI

- README lines 124–126: collapse the three-row command table to a single `composer
  bench` row.
- README methodology prose (~line 142 "~1 s wall-clock budget"; ~line 214 "same JIT for
  everyone / asymmetries"): update to uniform-flags + fixed-cadence wording.
- CI (`.github/workflows/ci.yml`): never invoked the three bench commands (it runs
  `composer test`, `php bench/ci_check.php`, `composer build`, `php bench/which-lib.php`).
  Expect **zero** CI changes; verify during implementation.

## Risks to verify during implementation (not blockers)

1. **Memory column source.** Confirm the dump XML carries `mem_peak` per variant. If not,
   source it differently or drop the column (it is heavily caveated anyway).
2. **Preload in PHPBench child.** Confirm `opcache.preload` via `runner.php_config`
   actually warms the FFI handle in PHPBench's per-iteration child process — a quick run
   showing our parser at expected speed confirms it.
3. **Warmup-amortized read.** Confirm the memoized corpus read truly lands in warmup
   (instance persists across revs within one iteration-process) so the timed revs are
   pure parse.

## Out of scope

- `bench/ci_check.php` and the CI correctness gate (`composer check`).
- The native build pipeline, parser source under `src/`.
- Any change to parser behavior or output correctness.

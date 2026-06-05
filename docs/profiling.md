# Profiling recipes

Runnable recipes for finding where this library spends its time. **Almost all of it is
inside md4c, behind one FFI call per document** — so the useful tools are _native_ profilers
(they see the C stack), not PHP profilers (which see the FFI call as one opaque box).

## Step 0 — build the profiling lib (once)

```sh
composer profile:build          # = bash native/build.sh profile
```

This produces `native/libmd4cshim.prof.{dylib,so}` built with `-O2 -g -fno-omit-frame-pointer`
(no `-flto`, no strip — so symbols and stack frames survive) **without touching** the shipped
`-O3 -flto` lib or the preload header. On macOS it also emits a `.dSYM` so md4c's _static_
functions resolve to names + source lines. None of these artifacts are committed.

## The target

[`bench/profile_target.php`](../bench/profile_target.php) is a steady-state hot loop:
construct once → read corpus once → render in a tight loop, so everything you sample is the
render itself.

```sh
php -d opcache.preload= -d ffi.enable=1 bench/profile_target.php [corpus] [iters] [mode]
#   corpus  file under corpus/ (default commonmark-spec.md) or an absolute path
#   iters   loop count — LARGE under samply/perf, SMALL (~5) under Valgrind/DHAT
#   mode    html (default) | parse | batch
```

> **Always run it with `-d opcache.preload=` (empty)** and `MARKDOWN_FFI_LIB` pointing at the
> profiling lib. The preload fast path binds the _optimized_ shipped lib via `FFI::scope`,
> which would shadow your profiling build. The script prints a warning if the scope is bound.

---

## macOS (dev loop)

Valgrind has no working Apple-Silicon port, so macOS is for **fast iteration and relative
trends**, not authoritative numbers (no turbo/governor/affinity control). Use a Linux box for
the deep dive and real figures.

### samply — flame graph in the browser (CPU)

```sh
MARKDOWN_FFI_LIB="$PWD/native/libmd4cshim.prof.dylib" \
  samply record -- php -d opcache.preload= -d ffi.enable=1 \
    bench/profile_target.php commonmark-spec.md 5000 html
```

Opens the Firefox Profiler; md4c frames (`md_parse`, `md_analyze_inlines`, `membuf_append`,
`collapse_nested_anchors`, …) symbolicate from the dylib + `.dSYM`. `brew install samply` or
`cargo install samply`. (samply symbolicates at view time — a `--save-only` `.json.gz` won't
contain the names until you `samply load` it.)

### sample — zero-dependency text call tree (CPU)

Always present on macOS, no install, no code-signing for your own process:

```sh
MARKDOWN_FFI_LIB="$PWD/native/libmd4cshim.prof.dylib" \
  php -d opcache.preload= -d ffi.enable=1 bench/profile_target.php commonmark-spec.md 8000 html &
sample $! 3 -file /tmp/md-sample.txt    # sample the running pid for 3s
grep -E 'membuf_append|md_analyze|collapse_nested|md_parse' /tmp/md-sample.txt
```

### Instruments — Allocations (the malloc/realloc story)

```sh
xcrun xctrace record --template 'Allocations' --launch -- \
  $(which php) -d opcache.preload= -d ffi.enable=1 bench/profile_target.php commonmark-spec.md 2000 html
```

Filter Recorded Types to `malloc`/`realloc` to see the `membuf` growth and `md2html_anchor`
churn. (The "Time Profiler" template is the heavier GUI alternative to `sample`/`samply`.)

---

## Linux (authoritative numbers + deep dive)

Pin a core and quiet the machine first: `cpupower frequency-set -g performance`, disable turbo,
and prefix runs with `taskset -c <core>`.

### perf + FlameGraph (CPU)

```sh
MARKDOWN_FFI_LIB="$PWD/native/libmd4cshim.prof.so" \
  perf record -F 999 --call-graph dwarf -- \
    php -d opcache.preload= -d ffi.enable=1 bench/profile_target.php commonmark-spec.md 5000 html
perf script | stackcollapse-perf.pl | flamegraph.pl > /tmp/md.svg
```

For a mixed PHP→FFI→md4c graph, add `-d opcache.jit=tracing -d opcache.jit_debug=48` and
`perf inject --jit` before `perf script` (fiddly — the native-only graph already isolates the
hot C functions, so don't over-invest).

### DHAT — the realloc-churn answer (allocations) ⭐

Best tool to confirm/quantify the `membuf` realloc-doubling. Use a **small** iter count.

```sh
MARKDOWN_FFI_LIB="$PWD/native/libmd4cshim.prof.so" \
  valgrind --tool=dhat --dhat-out-file=/tmp/dhat.out \
    php -d opcache.preload= -d ffi.enable=1 bench/profile_target.php commonmark-spec.md 5 html
# open /tmp/dhat.out in https://nnethercote.github.io/dh_view/dh_view.html
```

Look for `membuf_append` ranked by total bytes / "excessive turnover" — that tells you whether
the doubling over-allocates and what initial `membuf` capacity would cut the reallocs.

### heaptrack — faster allocation flame graph (allocations)

```sh
MARKDOWN_FFI_LIB="$PWD/native/libmd4cshim.prof.so" \
  heaptrack php -d opcache.preload= -d ffi.enable=1 bench/profile_target.php commonmark-spec.md 200 html
heaptrack_gui heaptrack.php.*.zst   # or heaptrack_print
```

### Callgrind — deterministic instruction count (CPU, low-noise CI metric)

```sh
MARKDOWN_FFI_LIB="$PWD/native/libmd4cshim.prof.so" \
  valgrind --tool=callgrind --callgrind-out-file=/tmp/cg.out \
    php -d opcache.preload= -d ffi.enable=1 bench/profile_target.php commonmark-spec.md 5 html
callgrind_annotate /tmp/cg.out | head -40   # or open in KCachegrind/QCachegrind
```

Instruction counts don't flake on noisy CI, so the `Ir` total makes a good before/after
regression gate.

---

## What to look at first

From a quick `sample` run on the macOS dev box, the hottest frames on the CommonMark spec
corpus were, in order: **`membuf_append`** (the output-buffer realloc-doubling),
`md_analyze_marks` / `md_analyze_inlines` (md4c inline parsing), the block-content processors,
and `collapse_nested_anchors`. So the first two questions worth answering with the tools above:

1. **Is `membuf_append` realloc-bound?** → DHAT. If so, seed the buffer at a fraction of input
   size (md4c HTML output is typically a small multiple of the markdown) to cut reallocs.
2. **Is `collapse_nested_anchors` a meaningful slice, or noise?** → perf/samply flame graph.
   It's a full extra pass over the output; if it's hot, it may be foldable into the render.

# helgesverre/markdown — benchmark results

_Generated 2026-06-05 14:58:23 · PHP 8.5.5 · Darwin arm64 · measured with PHPBench_

## Methodology

- One measurement engine: [PHPBench](https://phpbench.readthedocs.io). Run the whole suite with `composer bench`.
- Every parser runs with **identical PHP flags** (`opcache.enable_cli`, tracing JIT, `ffi.enable`,
  `opcache.preload=bench/preload.php`). The preload only warms _our_ FFI handle; for the pure-PHP parsers it is inert.
  Same env for everyone — our parser wins on merit plus a legitimately-preloaded handle.
- Cadence: **2 warmup, 50 revolutions × 10 iterations**, retry threshold 2.0 (PHPBench re-runs iterations until variance
  settles). Each iteration runs in its own process; reported time is the `mode` µs/rev.
- Parser instances are constructed **once** (in `setUp`/the registry), outside the timed revolutions. Corpus documents
  are read during warmup, not inside the measured revs.

> **Memory caveat (honest):** `helgesverre/markdown` renders its HTML onto the **C heap** (md4c `malloc`), which PHP's
> memory metrics do **not** count — so its `peak MB` is real-RSS-favorable (it undercounts the transient,
> immediately-freed C output buffer). Pure-PHP parsers keep all work on the Zend heap, so their `peak MB` is a complete
> accounting. The `peak MB` column also includes PHPBench's own per-process runner overhead, so read it as directional,
> not absolute.

## HTML throughput

### commonmark-spec.md (201.3 KB)

| Parser                      | ops/sec |   MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
| --------------------------- | ------: | -----: | ------: | ------: | ---------: | ------------: | --------------------- |
| **helgesverre/markdown** 🏆 |     982 | 202.46 |  1.0180 |    3.22 |          — |        27.79× |
| **league-strict**           |      38 |   7.83 | 26.3218 |  113.75 |          — |         1.07× |
| **league-gfm**              |      35 |   7.29 | 28.2902 |  113.91 |          — |         1.00× |
| **tempest**                 |       — |      — |       — |       — |          — |             — | ⚠️ threw during parse |

### tempest-docs.md (252.7 KB)

| Parser                      | ops/sec |   MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
| --------------------------- | ------: | -----: | ------: | ------: | ---------: | ------------: |
| **helgesverre/markdown** 🏆 |     998 | 258.30 |  1.0018 |    3.22 |     43.46× |        25.78× |
| **league-strict**           |      50 |  12.94 | 19.9933 |   85.45 |      2.18× |         1.29× |
| **league-gfm**              |      39 |  10.02 | 25.8228 |   86.23 |      1.69× |         1.00× |
| **tempest**                 |      23 |   5.94 | 43.5383 |    4.21 |      1.00× |         0.59× |

### doc-2kb.md (3.6 KB)

| Parser                      | ops/sec |   MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
| --------------------------- | ------: | -----: | ------: | ------: | ---------: | ------------: |
| **helgesverre/markdown** 🏆 |  45,371 | 168.23 |  0.0220 |    3.22 |     33.26× |       120.42× |
| **tempest**                 |   1,364 |   5.06 |  0.7331 |    3.22 |      1.00× |         3.62× |
| **league-strict**           |     488 |   1.81 |  2.0508 |    6.75 |      0.36× |         1.29× |
| **league-gfm**              |     377 |   1.40 |  2.6542 |    8.22 |      0.28× |         1.00× |

### doc-16kb.md (17.2 KB)

| Parser                      | ops/sec |   MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
| --------------------------- | ------: | -----: | ------: | ------: | ---------: | ------------: |
| **helgesverre/markdown** 🏆 |   9,817 | 173.02 |  0.1019 |    3.22 |     17.83× |        63.84× |
| **tempest**                 |     551 |   9.70 |  1.8164 |    3.22 |      1.00× |         3.58× |
| **league-strict**           |     190 |   3.35 |  5.2674 |   25.31 |      0.34× |         1.23× |
| **league-gfm**              |     154 |   2.71 |  6.5033 |   29.99 |      0.28× |         1.00× |

### doc-128kb.md (128.5 KB)

| Parser                      | ops/sec |   MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
| --------------------------- | ------: | -----: | ------: | ------: | ---------: | ------------: |
| **helgesverre/markdown** 🏆 |   1,323 | 174.00 |  0.7560 |    3.22 |     13.40× |        57.88× |
| **tempest**                 |      99 |  12.98 | 10.1315 |    3.24 |      1.00× |         4.32× |
| **league-strict**           |      29 |   3.76 | 34.9510 |  175.88 |      0.29× |         1.25× |
| **league-gfm**              |      23 |   3.01 | 43.7592 |  209.96 |      0.23× |         1.00× |

## Front-matter extraction

| Approach                             |  mean µs | ops/sec | renders body? | vs fastest |
| ------------------------------------ | -------: | ------: | :-----------: | ---------: |
| helgesverre/markdown (extract)       |   341.18 |   2,931 |      no       |      1.00× |
| symfony/yaml (floor)                 |   342.16 |   2,923 |      no       |      1.00× |
| league/commonmark (frontmatter-only) |   377.58 |   2,648 |      no       |      0.90× |
| helgesverre/markdown (full parse)    |   390.86 |   2,558 |      yes      |      0.87× |
| tempest/markdown (full parse)        | 1,032.72 |     968 |      yes      |      0.33× |

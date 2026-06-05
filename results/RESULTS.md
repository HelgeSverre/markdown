# helgesverre/markdown — benchmark results

_Generated 2026-06-05 19:58:41 · PHP 8.5.5 · Darwin arm64 · measured with PHPBench_

## Methodology

- One measurement engine: [PHPBench](https://phpbench.readthedocs.io). Run the whole suite with `composer bench`.
- Every parser runs with **identical PHP flags** (`opcache.enable_cli`, tracing JIT, `ffi.enable`, `opcache.preload=bench/preload.php`). The preload only warms *our* FFI handle; for the pure-PHP parsers it is inert. Same env for everyone — our parser wins on merit plus a legitimately-preloaded handle.
- Cadence: front matter runs **2 warmup, 50 revolutions × 10 iterations** for µs-scale precision; HTML throughput runs a lighter **1 warmup, 10 revolutions × 5 iterations** (the pure-PHP parsers cost tens of ms per parse on the larger tiers, so more samples buy no accuracy). Retry threshold 2.0; each iteration runs in its own process; reported time is the `mode` µs/rev.
- Parser instances are constructed **once** (in `setUp`/the registry), outside the timed revolutions. Corpus documents are read during warmup, not inside the measured revs.

> **Memory caveat (honest):** `helgesverre/markdown` renders its HTML onto the **C heap** (md4c `malloc`), which PHP's memory metrics do **not** count — so its `peak MB` is real-RSS-favorable (it undercounts the transient, immediately-freed C output buffer). Pure-PHP parsers keep all work on the Zend heap, so their `peak MB` is a complete accounting. The `peak MB` column also includes PHPBench's own per-process runner overhead, so read it as directional, not absolute.

## HTML throughput

### doc-2kb.md  (3.8 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 47,449 | 184.53 | 0.0211 | 3.22 | 80.93× | 254.17× |
| **helgesverre/markdown (parse)** | 17,581 | 68.37 | 0.0569 | 3.22 | 29.99× | 94.18× |
| **tempest** | 586 | 2.28 | 1.7057 | 3.22 | 1.00× | 3.14× |
| **league-strict** | 203 | 0.79 | 4.9357 | 3.58 | 0.35× | 1.09× |
| **league-gfm** | 187 | 0.73 | 5.3568 | 3.68 | 0.32× | 1.00× |

### doc-16kb.md  (18.1 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 10,448 | 193.58 | 0.0957 | 3.22 | 29.40× | 90.23× |
| **helgesverre/markdown (parse)** | 5,257 | 97.40 | 0.1902 | 3.22 | 14.79× | 45.40× |
| **tempest** | 355 | 6.58 | 2.8140 | 3.22 | 1.00× | 3.07× |
| **league-strict** | 139 | 2.57 | 7.2050 | 7.08 | 0.39× | 1.20× |
| **league-gfm** | 116 | 2.15 | 8.6356 | 8.43 | 0.33× | 1.00× |

### doc-128kb.md  (135.1 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,416 | 195.85 | 0.7062 | 3.22 | 15.50× | 59.67× |
| **helgesverre/markdown (parse)** | 924 | 127.76 | 1.0825 | 3.22 | 10.11× | 38.92× |
| **tempest** | 91 | 12.64 | 10.9447 | 3.22 | 1.00× | 3.85× |
| **league-strict** | 29 | 4.04 | 34.2362 | 38.61 | 0.32× | 1.23× |
| **league-gfm** | 24 | 3.28 | 42.1356 | 47.92 | 0.26× | 1.00× |

### commonmark-spec.md  (165.3 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,158 | 196.04 | 0.8636 | 3.22 | — | 33.32× |
| **helgesverre/markdown (parse)** | 761 | 128.79 | 1.3146 | 3.22 | — | 21.89× |
| **league-strict** | 38 | 6.41 | 26.4102 | 26.96 | — | 1.09× |
| **league-gfm** | 35 | 5.88 | 28.7789 | 27.11 | — | 1.00× |
| **tempest** | — | — | — | — | — | — |  ⚠️ threw during parse

### tempest-docs.md  (252.0 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,194 | 308.28 | 0.8372 | 3.22 | 50.46× | 31.34× |
| **helgesverre/markdown (parse)** | 896 | 231.26 | 1.1160 | 3.22 | 37.86× | 23.51× |
| **league-strict** | 47 | 12.25 | 21.0672 | 21.03 | 2.01× | 1.25× |
| **league-gfm** | 38 | 9.84 | 26.2360 | 21.34 | 1.61× | 1.00× |
| **tempest** | 24 | 6.11 | 42.2475 | 3.76 | 1.00× | 0.62× |

## Front-matter extraction

| Approach | mean µs | ops/sec | renders body? | vs fastest |
|---|--:|--:|:--:|--:|
| helgesverre/markdown (extract) | 8.84 | 113,115 | no | 1.00× |
| helgesverre/markdown (full parse) | 31.86 | 31,392 | yes | 0.28× |
| symfony/yaml (floor) | 307.81 | 3,249 | no | 0.03× |
| league/commonmark (frontmatter-only) | 344.33 | 2,904 | no | 0.03× |
| tempest/markdown (lex, no render) | 402.79 | 2,483 | no | 0.02× |
| tempest/markdown (full parse) | 939.14 | 1,065 | yes | 0.01× |


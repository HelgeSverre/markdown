# helgesverre/markdown — benchmark results

_Generated 2026-06-05 19:49:04 · PHP 8.5.5 · Darwin arm64 · measured with PHPBench_

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
| **helgesverre/markdown** 🏆 | 46,330 | 180.18 | 0.0216 | 3.04 | 81.41× | 267.13× |
| **helgesverre/markdown (parse)** | 16,530 | 64.28 | 0.0605 | 3.04 | 29.04× | 95.31× |
| **tempest** | 569 | 2.21 | 1.7571 | 3.04 | 1.00× | 3.28× |
| **league-strict** | 203 | 0.79 | 4.9346 | 3.18 | 0.36× | 1.17× |
| **league-gfm** | 173 | 0.67 | 5.7658 | 3.47 | 0.30× | 1.00× |

### doc-16kb.md  (18.1 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 10,411 | 192.89 | 0.0961 | 3.04 | 29.36× | 84.49× |
| **helgesverre/markdown (parse)** | 5,110 | 94.67 | 0.1957 | 3.04 | 14.41× | 41.46× |
| **tempest** | 355 | 6.57 | 2.8200 | 3.04 | 1.00× | 2.88× |
| **league-strict** | 139 | 2.57 | 7.2084 | 6.89 | 0.39× | 1.13× |
| **league-gfm** | 123 | 2.28 | 8.1149 | 8.27 | 0.35× | 1.00× |

### doc-128kb.md  (135.1 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,412 | 195.23 | 0.7084 | 3.04 | 15.22× | 56.76× |
| **helgesverre/markdown (parse)** | 917 | 126.83 | 1.0905 | 3.04 | 9.88× | 36.87× |
| **tempest** | 93 | 12.83 | 10.7794 | 3.04 | 1.00× | 3.73× |
| **league-strict** | 27 | 3.78 | 36.5965 | 38.43 | 0.29× | 1.10× |
| **league-gfm** | 25 | 3.44 | 40.2116 | 47.74 | 0.27× | 1.00× |

### commonmark-spec.md  (165.3 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,161 | 196.62 | 0.8611 | 3.04 | — | 32.21× |
| **helgesverre/markdown (parse)** | 948 | 160.51 | 1.0548 | 3.04 | — | 26.29× |
| **league-strict** | 37 | 6.24 | 27.1325 | 26.78 | — | 1.02× |
| **league-gfm** | 36 | 6.10 | 27.7339 | 26.93 | — | 1.00× |
| **tempest** | — | — | — | — | — | — |  ⚠️ threw during parse

### tempest-docs.md  (252.0 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,212 | 312.72 | 0.8253 | 3.04 | 29.34× | 28.76× |
| **helgesverre/markdown (parse)** | 888 | 229.19 | 1.1261 | 3.04 | 21.50× | 21.08× |
| **league-strict** | 47 | 12.07 | 21.3784 | 20.85 | 1.13× | 1.11× |
| **league-gfm** | 42 | 10.87 | 23.7362 | 21.14 | 1.02× | 1.00× |
| **tempest** | 41 | 10.66 | 24.2155 | 3.59 | 1.00× | 0.98× |

## Front-matter extraction

| Approach | mean µs | ops/sec | renders body? | vs fastest |
|---|--:|--:|:--:|--:|
| helgesverre/markdown (extract) | 6.39 | 156,529 | no | 1.00× |
| helgesverre/markdown (full parse) | 32.04 | 31,209 | yes | 0.20× |
| symfony/yaml (floor) | 334.67 | 2,988 | no | 0.02× |
| league/commonmark (frontmatter-only) | 380.96 | 2,625 | no | 0.02× |
| tempest/markdown (lex, no render) | 455.11 | 2,197 | no | 0.01× |
| tempest/markdown (full parse) | 1,012.87 | 987 | yes | 0.01× |


# helgesverre/markdown — benchmark results

_Generated 2026-06-05 17:55:11 · PHP 8.5.5 · Darwin arm64 · measured with PHPBench_

## Methodology

- One measurement engine: [PHPBench](https://phpbench.readthedocs.io). Run the whole suite with `composer bench`.
- Every parser runs with **identical PHP flags** (`opcache.enable_cli`, tracing JIT, `ffi.enable`, `opcache.preload=bench/preload.php`). The preload only warms *our* FFI handle; for the pure-PHP parsers it is inert. Same env for everyone — our parser wins on merit plus a legitimately-preloaded handle.
- Cadence: **2 warmup, 50 revolutions × 10 iterations**, retry threshold 2.0 (PHPBench re-runs iterations until variance settles). Each iteration runs in its own process; reported time is the `mode` µs/rev.
- Parser instances are constructed **once** (in `setUp`/the registry), outside the timed revolutions. Corpus documents are read during warmup, not inside the measured revs.

> **Memory caveat (honest):** `helgesverre/markdown` renders its HTML onto the **C heap** (md4c `malloc`), which PHP's memory metrics do **not** count — so its `peak MB` is real-RSS-favorable (it undercounts the transient, immediately-freed C output buffer). Pure-PHP parsers keep all work on the Zend heap, so their `peak MB` is a complete accounting. The `peak MB` column also includes PHPBench's own per-process runner overhead, so read it as directional, not absolute.

## HTML throughput

### commonmark-spec.md  (165.3 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,045 | 177.01 | 0.9565 | 3.22 | — | 28.34× |
| **league-strict** | 39 | 6.67 | 25.3823 | 113.63 | — | 1.07× |
| **league-gfm** | 37 | 6.25 | 27.1032 | 113.79 | — | 1.00× |
| **tempest** | — | — | — | — | — | — |  ⚠️ threw during parse

### tempest-docs.md  (252.0 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,109 | 286.15 | 0.9019 | 3.22 | 47.95× | 26.39× |
| **league-strict** | 51 | 13.20 | 19.5524 | 85.85 | 2.21× | 1.22× |
| **league-gfm** | 42 | 10.84 | 23.8030 | 86.63 | 1.82× | 1.00× |
| **tempest** | 23 | 5.97 | 43.2510 | 4.22 | 1.00× | 0.55× |

### doc-2kb.md  (3.8 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 47,131 | 183.29 | 0.0212 | 3.22 | 38.46× | 113.90× |
| **tempest** | 1,225 | 4.77 | 0.8160 | 3.22 | 1.00× | 2.96× |
| **league-strict** | 515 | 2.00 | 1.9418 | 6.84 | 0.42× | 1.24× |
| **league-gfm** | 414 | 1.61 | 2.4166 | 8.06 | 0.34× | 1.00× |

### doc-16kb.md  (18.1 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 9,971 | 184.73 | 0.1003 | 3.22 | 16.81× | 64.95× |
| **tempest** | 593 | 10.99 | 1.6862 | 3.22 | 1.00× | 3.86× |
| **league-strict** | 193 | 3.57 | 5.1897 | 25.39 | 0.32× | 1.26× |
| **league-gfm** | 154 | 2.84 | 6.5139 | 29.99 | 0.26× | 1.00× |

### doc-128kb.md  (135.1 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,345 | 186.00 | 0.7436 | 3.22 | 13.75× | 56.54× |
| **tempest** | 98 | 13.53 | 10.2251 | 3.22 | 1.00× | 4.11× |
| **league-strict** | 29 | 4.05 | 34.1398 | 176.44 | 0.30× | 1.23× |
| **league-gfm** | 24 | 3.29 | 42.0421 | 209.98 | 0.24× | 1.00× |

### doc-1mb.md  (1.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 168 | 176.55 | 5.9466 | 4.23 | 14.21× | 58.45× |
| **tempest** | 12 | 12.42 | 84.4999 | 9.89 | 1.00× | 4.11× |
| **league-strict** | 4 | 3.77 | 278.4909 | 1,382.97 | 0.30× | 1.25× |
| **league-gfm** | 3 | 3.02 | 347.6060 | 1,652.72 | 0.24× | 1.00× |

### doc-8mb.md  (8.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 21 | 172.98 | 48.5036 | 23.10 | 13.32× | — |
| **tempest** | 2 | 12.98 | 646.1485 | 64.42 | 1.00× | — |
| **league-gfm** | — | — | — | — | — | — |  ⚠️ threw during parse
| **league-strict** | — | — | — | — | — | — |  ⚠️ threw during parse

## Front-matter extraction

| Approach | mean µs | ops/sec | renders body? | vs fastest |
|---|--:|--:|:--:|--:|
| symfony/yaml (floor) | 352.25 | 2,839 | no | 1.00× |
| helgesverre/markdown (extract) | 362.96 | 2,755 | no | 0.97× |
| helgesverre/markdown (full parse) | 372.75 | 2,683 | yes | 0.95× |
| league/commonmark (frontmatter-only) | 400.97 | 2,494 | no | 0.88× |
| tempest/markdown (full parse) | 1,014.39 | 986 | yes | 0.35× |

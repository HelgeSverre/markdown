# helgesverre/markdown — benchmark results

_Generated 2026-06-05 13:35:37 · PHP 8.5.5 · Darwin arm64_

> **helgesverre/markdown** wins on throughput · helgesverre/markdown is ~13.0× faster than tempest · ~59.6× faster than league-gfm (median across corpora).

## Methodology

- Each (parser × corpus) combo runs in its **own php process** for a clean per-parser `memory_get_peak_usage(true)`.
- Warmup: ≥5 iterations. Timed: fixed **~1.0s wall-clock budget** (min 20 iters), measured with `hrtime(true)`.
- `helgesverre/markdown` is launched with warm-FFI flags: `opcache.enable_cli=1`, `opcache.preload=bench/preload.php`, `ffi.enable=1`. All parsers get `opcache.enable_cli=1` + tracing JIT for fairness.
- Parser/converter instances are constructed **once**, outside the timed loop (steady-state comparison).

> **Memory caveat (honest):** `helgesverre/markdown` renders its HTML onto the **C heap** (md4c `malloc`), which PHP's `peak_mb` does **not** count — so `helgesverre/markdown`'s memory number is real-RSS-favorable (it undercounts the transient, immediately-freed C output buffer). Pure-PHP parsers keep all work on the Zend heap, so their `peak_mb` is a complete accounting. Read the memory column with that asymmetry in mind.

## commonmark-spec.md  (201.3 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,017 | 209.67 | 0.9830 | 6.00 | 229,558 | — | 30.30× |
| **league-strict** | 38 | 7.82 | 26.3412 | 10.00 | 229,668 | — | 1.13× |
| **league-gfm** | 34 | 6.92 | 29.7814 | 10.00 | 229,668 | — | 1.00× |
| **tempest** | — | — | — | — | — | — | — |  ⚠️ warmup parse threw: Could not parse FrontMatter: Unable to parse at line 6 (near

## tempest-docs.md  (252.7 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,031 | 266.79 | 0.9700 | 6.00 | 289,014 | 43.72× | 26.31× |
| **league-strict** | 51 | 13.11 | 19.7438 | 10.00 | 288,948 | 2.15× | 1.29× |
| **league-gfm** | 39 | 10.14 | 25.5191 | 10.00 | 289,154 | 1.66× | 1.00× |
| **tempest** | 24 | 6.10 | 42.4069 | 8.00 | 474,774 | 1.00× | 0.60× |

## doc-2kb.md  (3.6 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 46,133 | 171.06 | 0.0217 | 6.00 | 6,162 | 12.78× | 59.88× |
| **tempest** | 3,609 | 13.38 | 0.2771 | 6.00 | 5,489 | 1.00× | 4.68× |
| **league-strict** | 954 | 3.54 | 1.0478 | 6.00 | 4,724 | 0.26× | 1.24× |
| **league-gfm** | 770 | 2.86 | 1.2981 | 6.00 | 5,782 | 0.21× | 1.00× |

## doc-16kb.md  (17.2 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 9,871 | 173.97 | 0.1013 | 6.00 | 29,881 | 12.65× | 59.43× |
| **tempest** | 780 | 13.75 | 1.2814 | 6.00 | 25,851 | 1.00× | 4.70× |
| **league-strict** | 207 | 3.65 | 4.8296 | 6.00 | 22,683 | 0.27× | 1.25× |
| **league-gfm** | 166 | 2.93 | 6.0204 | 6.00 | 27,973 | 0.21× | 1.00× |

## doc-128kb.md  (128.5 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,331 | 175.07 | 0.7513 | 6.00 | 223,548 | 14.15× | 59.58× |
| **tempest** | 94 | 12.38 | 10.6279 | 6.00 | 195,478 | 1.00× | 4.21× |
| **league-strict** | 27 | 3.60 | 36.5709 | 12.00 | 169,550 | 0.29× | 1.22× |
| **league-gfm** | 22 | 2.94 | 44.7664 | 12.00 | 209,225 | 0.24× | 1.00× |

## doc-1mb.md  (1.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 165 | 172.93 | 6.0713 | 10.00 | 1,781,670 | 13.15× | 64.39× |
| **tempest** | 13 | 13.15 | 79.8339 | 14.00 | 1,569,920 | 1.00× | 4.90× |
| **league-strict** | 3 | 3.33 | 314.9757 | 60.00 | 1,352,552 | 0.25× | 1.24× |
| **league-gfm** | 3 | 2.69 | 390.9954 | 66.00 | 1,667,836 | 0.20× | 1.00× |

## doc-8mb.md  (8.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 19 | 162.98 | 51.4770 | 41.14 | 14,218,315 | 12.87× | 73.86× |
| **tempest** | 2 | 12.66 | 662.6678 | 79.95 | 12,522,210 | 1.00× | 5.74× |
| **league-strict** | 0 | 2.61 | 3,209.6459 | 320.97 | 10,803,357 | 0.21× | 1.19× |
| **league-gfm** | 0 | 2.21 | 3,804.1194 | 375.44 | 13,312,404 | 0.17× | 1.00× |


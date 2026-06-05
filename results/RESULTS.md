# helgesverre/markdown — benchmark results

_Generated 2026-06-05 12:42:37 · PHP 8.5.5 · Darwin arm64_

> **helgesverre/markdown** wins on throughput · helgesverre/markdown is ~13.4× faster than tempest · ~59.9× faster than league-gfm (median across corpora).

## Methodology

- Each (parser × corpus) combo runs in its **own php process** for a clean per-parser `memory_get_peak_usage(true)`.
- Warmup: ≥5 iterations. Timed: fixed **~1.0s wall-clock budget** (min 20 iters), measured with `hrtime(true)`.
- `helgesverre/markdown` is launched with warm-FFI flags: `opcache.enable_cli=1`, `opcache.preload=bench/preload.php`, `ffi.enable=1`. All parsers get `opcache.enable_cli=1` + tracing JIT for fairness.
- Parser/converter instances are constructed **once**, outside the timed loop (steady-state comparison).

> **Memory caveat (honest):** `helgesverre/markdown` renders its HTML onto the **C heap** (md4c `malloc`), which PHP's `peak_mb` does **not** count — so `helgesverre/markdown`'s memory number is real-RSS-favorable (it undercounts the transient, immediately-freed C output buffer). Pure-PHP parsers keep all work on the Zend heap, so their `peak_mb` is a complete accounting. Read the memory column with that asymmetry in mind.

## commonmark-spec.md  (201.3 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,050 | 216.39 | 0.9525 | 6.00 | 229,558 | — | 29.69× |
| **league-strict** | 37 | 7.59 | 27.1525 | 10.00 | 229,668 | — | 1.04× |
| **league-gfm** | 35 | 7.29 | 28.2772 | 10.00 | 229,668 | — | 1.00× |
| **tempest** | — | — | — | — | — | — | — |  ⚠️ warmup parse threw: Could not parse FrontMatter: Unable to parse at line 6 (near

## tempest-docs.md  (252.7 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,066 | 275.87 | 0.9380 | 6.00 | 289,014 | 43.40× | 25.91× |
| **league-strict** | 52 | 13.46 | 19.2312 | 10.00 | 288,948 | 2.12× | 1.26× |
| **league-gfm** | 41 | 10.65 | 24.3062 | 10.00 | 289,154 | 1.67× | 1.00× |
| **tempest** | 25 | 6.36 | 40.7056 | 8.00 | 474,774 | 1.00× | 0.60× |

## doc-2kb.md  (3.6 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 47,249 | 175.20 | 0.0212 | 6.00 | 6,162 | 12.76× | 58.79× |
| **tempest** | 3,704 | 13.74 | 0.2700 | 6.00 | 5,489 | 1.00× | 4.61× |
| **league-strict** | 984 | 3.65 | 1.0159 | 6.00 | 4,724 | 0.27× | 1.22× |
| **league-gfm** | 804 | 2.98 | 1.2443 | 6.00 | 5,782 | 0.22× | 1.00× |

## doc-16kb.md  (17.2 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 10,240 | 180.47 | 0.0977 | 6.00 | 29,881 | 13.11× | 61.61× |
| **tempest** | 781 | 13.76 | 1.2807 | 6.00 | 25,851 | 1.00× | 4.70× |
| **league-strict** | 212 | 3.73 | 4.7200 | 6.00 | 22,683 | 0.27× | 1.27× |
| **league-gfm** | 166 | 2.93 | 6.0164 | 6.00 | 27,973 | 0.21× | 1.00× |

## doc-128kb.md  (128.5 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 1,361 | 179.08 | 0.7345 | 6.00 | 223,548 | 13.73× | 59.87× |
| **tempest** | 99 | 13.05 | 10.0823 | 6.00 | 195,478 | 1.00× | 4.36× |
| **league-strict** | 27 | 3.51 | 37.4269 | 12.00 | 169,550 | 0.27× | 1.17× |
| **league-gfm** | 23 | 2.99 | 43.9718 | 12.00 | 209,225 | 0.23× | 1.00× |

## doc-1mb.md  (1.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 166 | 174.17 | 6.0280 | 10.00 | 1,781,670 | 14.08× | 64.73× |
| **tempest** | 12 | 12.37 | 84.8538 | 14.00 | 1,569,920 | 1.00× | 4.60× |
| **league-strict** | 3 | 3.35 | 313.5694 | 60.00 | 1,352,552 | 0.27× | 1.24× |
| **league-gfm** | 3 | 2.69 | 390.2215 | 66.00 | 1,667,836 | 0.22× | 1.00× |

## doc-8mb.md  (8.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **helgesverre/markdown** 🏆 | 20 | 166.22 | 50.4756 | 41.14 | 14,218,315 | 12.77× | 75.62× |
| **tempest** | 2 | 13.01 | 644.9020 | 79.95 | 12,522,210 | 1.00× | 5.92× |
| **league-strict** | 0 | 2.71 | 3,097.9627 | 320.97 | 10,803,357 | 0.21× | 1.23× |
| **league-gfm** | 0 | 2.20 | 3,819.5857 | 375.44 | 13,312,404 | 0.17× | 1.00× |


# Markdown Fight — Results

_Generated 2026-06-05 11:47:40 · PHP 8.5.5 · Darwin arm64_

> **fight** wins on throughput · fight is ~16.9× faster than tempest · ~71.8× faster than league-gfm (median across corpora).

## Methodology

- Each (parser × corpus) combo runs in its **own php process** for a clean per-parser `memory_get_peak_usage(true)`.
- Warmup: ≥5 iterations. Timed: fixed **~1.0s wall-clock budget** (min 20 iters), measured with `hrtime(true)`.
- `fight` is launched with warm-FFI flags: `opcache.enable_cli=1`, `opcache.preload=bench/preload.php`, `ffi.enable=1`. All parsers get `opcache.enable_cli=1` + tracing JIT for fairness.
- Parser/converter instances are constructed **once**, outside the timed loop (steady-state comparison).

> **Memory caveat (honest):** `fight` renders its HTML onto the **C heap** (md4c `malloc`), which PHP's `peak_mb` does **not** count — so `fight`'s memory number is real-RSS-favorable (it undercounts the transient, immediately-freed C output buffer). Pure-PHP parsers keep all work on the Zend heap, so their `peak_mb` is a complete accounting. Read the memory column with that asymmetry in mind.

## commonmark-spec.md  (201.3 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 1,189 | 245.02 | 0.8412 | 4.00 | 229,558 | — | 35.11× |
| **league-strict** | 36 | 7.41 | 27.8090 | 10.00 | 229,668 | — | 1.06× |
| **league-gfm** | 34 | 6.98 | 29.5372 | 10.00 | 229,668 | — | 1.00× |
| **tempest** | — | — | — | — | — | — | — |  ⚠️ warmup parse threw: Could not parse FrontMatter: Unable to parse at line 6 (near

## tempest-docs.md  (252.7 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 1,302 | 336.79 | 0.7683 | 4.00 | 289,130 | 55.23× | 32.56× |
| **league-strict** | 49 | 12.72 | 20.3427 | 10.00 | 288,948 | 2.09× | 1.23× |
| **league-gfm** | 40 | 10.35 | 25.0129 | 10.00 | 289,154 | 1.70× | 1.00× |
| **tempest** | 24 | 6.10 | 42.4353 | 6.00 | 474,774 | 1.00× | 0.59× |

## doc-2kb.md  (3.6 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 56,671 | 210.13 | 0.0176 | 4.00 | 6,162 | 15.42× | 71.81× |
| **tempest** | 3,675 | 13.63 | 0.2721 | 4.00 | 5,489 | 1.00× | 4.66× |
| **league-strict** | 945 | 3.50 | 1.0585 | 6.00 | 4,724 | 0.26× | 1.20× |
| **league-gfm** | 789 | 2.93 | 1.2672 | 6.00 | 5,782 | 0.21× | 1.00× |

## doc-16kb.md  (17.2 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 12,129 | 213.76 | 0.0824 | 4.00 | 29,881 | 16.06× | 86.39× |
| **tempest** | 755 | 13.31 | 1.3240 | 4.00 | 25,851 | 1.00× | 5.38× |
| **league-strict** | 177 | 3.13 | 5.6382 | 6.00 | 22,683 | 0.23× | 1.26× |
| **league-gfm** | 140 | 2.47 | 7.1224 | 6.00 | 27,973 | 0.19× | 1.00× |

## doc-128kb.md  (128.5 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 1,489 | 195.80 | 0.6718 | 4.00 | 223,548 | 17.58× | 69.86× |
| **tempest** | 85 | 11.14 | 11.8088 | 4.00 | 195,478 | 1.00× | 3.97× |
| **league-strict** | 26 | 3.44 | 38.2019 | 12.00 | 169,550 | 0.31× | 1.23× |
| **league-gfm** | 21 | 2.80 | 46.9334 | 10.00 | 209,225 | 0.25× | 1.00× |

## doc-1mb.md  (1.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 201 | 211.50 | 4.9640 | 8.00 | 1,781,670 | 17.59× | 80.77× |
| **tempest** | 11 | 12.02 | 87.3345 | 14.00 | 1,569,920 | 1.00× | 4.59× |
| **league-strict** | 3 | 3.03 | 346.1824 | 58.00 | 1,352,552 | 0.25× | 1.16× |
| **league-gfm** | 2 | 2.62 | 401.0217 | 64.00 | 1,667,836 | 0.22× | 1.00× |

## doc-8mb.md  (8.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 24 | 201.10 | 41.7209 | 39.14 | 14,218,315 | 16.13× | 91.84× |
| **tempest** | 1 | 12.47 | 673.0483 | 77.95 | 12,522,210 | 1.00× | 5.69× |
| **league-strict** | 0 | 2.60 | 3,230.3698 | 318.97 | 10,803,357 | 0.21× | 1.19× |
| **league-gfm** | 0 | 2.19 | 3,836.0713 | 375.44 | 13,312,404 | 0.18× | 1.00× |


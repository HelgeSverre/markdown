# Markdown Fight — Results

_Generated 2026-06-05 12:10:47 · PHP 8.5.5 · Darwin arm64_

> **fight** wins on throughput · fight is ~13.2× faster than tempest · ~59.4× faster than league-gfm (median across corpora).

## Methodology

- Each (parser × corpus) combo runs in its **own php process** for a clean per-parser `memory_get_peak_usage(true)`.
- Warmup: ≥5 iterations. Timed: fixed **~1.0s wall-clock budget** (min 20 iters), measured with `hrtime(true)`.
- `fight` is launched with warm-FFI flags: `opcache.enable_cli=1`, `opcache.preload=bench/preload.php`, `ffi.enable=1`. All parsers get `opcache.enable_cli=1` + tracing JIT for fairness.
- Parser/converter instances are constructed **once**, outside the timed loop (steady-state comparison).

> **Memory caveat (honest):** `fight` renders its HTML onto the **C heap** (md4c `malloc`), which PHP's `peak_mb` does **not** count — so `fight`'s memory number is real-RSS-favorable (it undercounts the transient, immediately-freed C output buffer). Pure-PHP parsers keep all work on the Zend heap, so their `peak_mb` is a complete accounting. Read the memory column with that asymmetry in mind.

## commonmark-spec.md  (201.3 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 1,054 | 217.28 | 0.9486 | 4.00 | 229,558 | — | 29.31× |
| **league-strict** | 38 | 7.93 | 25.9906 | 10.00 | 229,668 | — | 1.07× |
| **league-gfm** | 36 | 7.41 | 27.8075 | 10.00 | 229,668 | — | 1.00× |
| **tempest** | — | — | — | — | — | — | — |  ⚠️ warmup parse threw: Could not parse FrontMatter: Unable to parse at line 6 (near

## tempest-docs.md  (252.7 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 952 | 246.34 | 1.0504 | 4.00 | 289,014 | 39.59× | 22.37× |
| **league-strict** | 50 | 12.90 | 20.0540 | 10.00 | 288,948 | 2.07× | 1.17× |
| **league-gfm** | 43 | 11.01 | 23.4951 | 10.00 | 289,154 | 1.77× | 1.00× |
| **tempest** | 24 | 6.22 | 41.5910 | 6.00 | 474,774 | 1.00× | 0.56× |

## doc-2kb.md  (3.6 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 45,576 | 168.99 | 0.0219 | 4.00 | 6,162 | 12.50× | 59.43× |
| **tempest** | 3,645 | 13.52 | 0.2743 | 4.00 | 5,489 | 1.00× | 4.75× |
| **league-strict** | 982 | 3.64 | 1.0180 | 6.00 | 4,724 | 0.27× | 1.28× |
| **league-gfm** | 767 | 2.84 | 1.3039 | 6.00 | 5,782 | 0.21× | 1.00× |

## doc-16kb.md  (17.2 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 10,136 | 178.64 | 0.0987 | 4.00 | 29,881 | 13.05× | 62.78× |
| **tempest** | 777 | 13.69 | 1.2874 | 4.00 | 25,851 | 1.00× | 4.81× |
| **league-strict** | 206 | 3.63 | 4.8489 | 6.00 | 22,683 | 0.27× | 1.28× |
| **league-gfm** | 161 | 2.85 | 6.1935 | 6.00 | 27,973 | 0.21× | 1.00× |

## doc-128kb.md  (128.5 KB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 1,352 | 177.81 | 0.7397 | 4.00 | 223,548 | 13.37× | 58.73× |
| **tempest** | 101 | 13.30 | 9.8916 | 4.00 | 195,478 | 1.00× | 4.39× |
| **league-strict** | 28 | 3.68 | 35.7673 | 12.00 | 169,550 | 0.28× | 1.21× |
| **league-gfm** | 23 | 3.03 | 43.4481 | 10.00 | 209,225 | 0.23× | 1.00× |

## doc-1mb.md  (1.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 169 | 177.75 | 5.9065 | 8.00 | 1,781,670 | 13.41× | 65.04× |
| **tempest** | 13 | 13.25 | 79.2104 | 14.00 | 1,569,920 | 1.00× | 4.85× |
| **league-strict** | 3 | 3.35 | 313.6816 | 58.00 | 1,352,552 | 0.25× | 1.22× |
| **league-gfm** | 3 | 2.73 | 384.1542 | 64.00 | 1,667,836 | 0.21× | 1.00× |

## doc-8mb.md  (8.00 MB)

| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |
|---|--:|--:|--:|--:|--:|--:|--:|
| **fight** 🏆 | 20 | 167.05 | 50.2250 | 39.14 | 14,218,315 | 12.75× | 75.70× |
| **tempest** | 2 | 13.10 | 640.4663 | 77.95 | 12,522,210 | 1.00× | 5.94× |
| **league-strict** | 0 | 2.72 | 3,088.4604 | 318.97 | 10,803,357 | 0.21× | 1.23× |
| **league-gfm** | 0 | 2.20 | 3,806.5739 | 375.44 | 13,312,404 | 0.17× | 1.00× |


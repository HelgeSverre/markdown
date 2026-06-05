# Profile — helgesverre/markdown (FFI→md4c)

**Generated:** 2026-06-05 18:55 · **Host:** macOS arm64, PHP 8.5.5
**Corpus:** commonmark-spec.md (169,307 bytes) · **Mode:** html · **Sampler:** macOS `sample`, 5s @ 1ms
**Throughput** (separate timed pass): 878.8 µs/op · 261 MB/s out

> Native self-time. Parked/idle threads (PHP runtime workers waiting in the
> kernel — `__workq_kernreturn` etc.) are **excluded**: 8358 idle samples vs 4258 on-CPU.
> Percentages are share of on-CPU samples. This is leaf/self time, not inclusive.

## Where the time goes — by component

| Component | Self samples | % on-CPU |
|---|--:|--:|
| md4c+shim | 3514 | 82.5% |
| libc (mem/str) | 744 | 17.5% |

## Hottest functions

| # | Function | Component | Self | % on-CPU |
|--:|---|---|--:|--:|
| 1 | `md_parse` | md4c+shim | 1081 | 25.4% |
| 2 | `render_html_escaped` | md4c+shim | 712 | 16.7% |
| 3 | `md_analyze_inlines` | md4c+shim | 404 | 9.5% |
| 4 | `_platform_memmove` | libc (mem/str) | 360 | 8.5% |
| 5 | `_platform_memchr` | libc (mem/str) | 342 | 8.0% |
| 6 | `md_process_verbatim_block_contents` | md4c+shim | 254 | 6.0% |
| 7 | `membuf_append` | md4c+shim | 230 | 5.4% |
| 8 | `md_analyze_marks` | md4c+shim | 203 | 4.8% |
| 9 | `collapse_nested_anchors` | md4c+shim | 147 | 3.5% |
| 10 | `md_process_normal_block_contents` | md4c+shim | 71 | 1.7% |
| 11 | `md_build_attribute` | md4c+shim | 68 | 1.6% |
| 12 | `text_callback` | md4c+shim | 62 | 1.5% |
| 13 | `DYLD-STUB$$memcpy` | md4c+shim | 49 | 1.2% |
| 14 | `md_end_current_block` | md4c+shim | 46 | 1.1% |
| 15 | `_platform_strchr` | libc (mem/str) | 42 | 1.0% |
| 16 | `md_add_line_into_current_block` | md4c+shim | 42 | 1.0% |
| 17 | `md_is_container_mark` | md4c+shim | 26 | 0.6% |
| 18 | `DYLD-STUB$$memchr` | md4c+shim | 25 | 0.6% |
| 19 | `enter_block_callback` | md4c+shim | 21 | 0.5% |
| 20 | `leave_block_callback` | md4c+shim | 14 | 0.3% |
| 21 | `DYLD-STUB$$strchr` | md4c+shim | 12 | 0.3% |
| 22 | `leave_span_callback` | md4c+shim | 12 | 0.3% |
| 23 | `enter_span_callback` | md4c+shim | 10 | 0.2% |
| 24 | `md_is_link_reference` | md4c+shim | 10 | 0.2% |
| 25 | `md_start_new_block` | md4c+shim | 10 | 0.2% |

---
_Raw sample: `results/profile.sample.txt`. Interactive flamegraph: `composer profile:flamegraph`. Recipes & Linux tools: `docs/profiling.md`._

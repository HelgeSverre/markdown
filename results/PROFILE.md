# Profile — helgesverre/markdown (FFI→md4c)

**Generated:** 2026-06-05 15:37 · **Host:** macOS arm64, PHP 8.5.5
**Corpus:** commonmark-spec.md (169,307 bytes) · **Mode:** html · **Sampler:** macOS `sample`, 2s @ 1ms
**Throughput** (separate timed pass): 1246.0 µs/op · 184 MB/s out

> Native self-time. Parked/idle threads (PHP runtime workers waiting in the
> kernel — `__workq_kernreturn` etc.) are **excluded**: 3418 idle samples vs 1687 on-CPU.
> Percentages are share of on-CPU samples. This is leaf/self time, not inclusive.

## Where the time goes — by component

| Component | Self samples | % on-CPU |
|---|--:|--:|
| md4c+shim | 1556 | 92.2% |
| libc (mem/str) | 131 | 7.8% |

## Hottest functions

| # | Function | Component | Self | % on-CPU |
|--:|---|---|--:|--:|
| 1 | `md_parse` | md4c+shim | 470 | 27.9% |
| 2 | `collapse_nested_anchors` | md4c+shim | 277 | 16.4% |
| 3 | `render_html_escaped` | md4c+shim | 273 | 16.2% |
| 4 | `md_analyze_inlines` | md4c+shim | 135 | 8.0% |
| 5 | `_platform_memmove` | libc (mem/str) | 112 | 6.6% |
| 6 | `md_process_verbatim_block_contents` | md4c+shim | 80 | 4.7% |
| 7 | `membuf_append` | md4c+shim | 80 | 4.7% |
| 8 | `md_analyze_marks` | md4c+shim | 68 | 4.0% |
| 9 | `md_process_normal_block_contents` | md4c+shim | 41 | 2.4% |
| 10 | `text_callback` | md4c+shim | 22 | 1.3% |
| 11 | `md_build_attribute` | md4c+shim | 19 | 1.1% |
| 12 | `md_add_line_into_current_block` | md4c+shim | 18 | 1.1% |
| 13 | `md_end_current_block` | md4c+shim | 15 | 0.9% |
| 14 | `DYLD-STUB$$memcpy` | md4c+shim | 13 | 0.8% |
| 15 | `md_is_container_mark` | md4c+shim | 11 | 0.7% |
| 16 | `_platform_strchr` | libc (mem/str) | 10 | 0.6% |
| 17 | `_platform_memchr` | libc (mem/str) | 9 | 0.5% |
| 18 | `enter_block_callback` | md4c+shim | 6 | 0.4% |
| 19 | `enter_span_callback` | md4c+shim | 6 | 0.4% |
| 20 | `md_is_link_reference` | md4c+shim | 6 | 0.4% |
| 21 | `md_start_new_block` | md4c+shim | 6 | 0.4% |
| 22 | `leave_block_callback` | md4c+shim | 5 | 0.3% |
| 23 | `md_is_link_destination` | md4c+shim | 5 | 0.3% |

---
_Raw sample: `results/profile.sample.txt`. Interactive flamegraph: `composer profile:flamegraph`. Recipes & Linux tools: `docs/profiling.md`._

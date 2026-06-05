# Synthetic Markdown Document (2048 byte target)

Generated feature-rich markdown for parser benchmarking. Contains headings, emphasis, lists, tables, code, blockquotes, task lists, strikethrough, images and thematic breaks.

---

## Section 1: the streaming buffer

This is paragraph **number 1** describing a *streaming* buffer. It runs in `O(n)` time and handles [external links](https://example.com/page/43) alongside ~~deprecated~~ APIs. We measured a **91% speedup** over the baseline implementation, with a tail latency of *37.3ms* per document.

> Blockquote 1: "Performance is a feature." The buffer processed 43 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #43

### Unordered features

- First item with `inline code` and a [link](http://x.test/1)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the buffer
  - Another nested point at 91% coverage
    - Deeply nested leaf node 43
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/1.png)

### Ordered steps

1. Allocate the arena (43 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<buffer>` tags
4. Free the arena in one shot

### Task list (run 1)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 91% target on doc 43
- [ ] Publish the results

### Benchmark table 1

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 805 | 5.2 | yes |
| league GFM | 22 | 47 | yes |
| tempest | 22 | 43 | partial |

Here is a fenced code block in `diff`:

```diff
# block 1: streaming buffer
run --threads 43 --target 91
```

A final paragraph with an autolink <https://autolink.test/1> and a footnote-ish aside. Inline `code spans` survive 43 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 2: the vectorized pipeline

This is paragraph **number 2** describing a *vectorized* pipeline. It runs in `O(n)` time and handles [external links](https://example.com/page/71) alongside ~~deprecated~~ APIs. We measured a **82% speedup** over the baseline implementation, with a tail latency of *55.1ms* per document.

> Blockquote 2: "Performance is a feature." The pipeline processed 71 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #71

### Unordered features

- First item with `inline code` and a [link](http://x.test/2)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the pipeline
  - Another nested point at 82% coverage
    - Deeply nested leaf node 71
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/2.png)

### Ordered steps

1. Allocate the arena (71 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<pipeline>` tags
4. Free the arena in one shot

### Task list (run 2)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 82% target on doc 71
- [ ] Publish the results

### Benchmark table 2

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 648 | 4 | yes |
| league GFM | 52 | 31 | yes |
| tempest | 28 | 42 | partial |

Here is a fenced code block in `bash`:

```bash
# block 2: vectorized pipeline
run --threads 71 --target 82
```

A final paragraph with an autolink <https://autolink.test/2> and a footnote-ish aside. Inline `code spans` survive 71 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---

# Synthetic Markdown Document (16384 byte target)

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
## Section 3: the zero-copy parser

This is paragraph **number 3** describing a *zero-copy* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/88) alongside ~~deprecated~~ APIs. We measured a **83% speedup** over the baseline implementation, with a tail latency of *61.8ms* per document.

> Blockquote 3: "Performance is a feature." The parser processed 88 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #88

### Unordered features

- First item with `inline code` and a [link](http://x.test/3)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 83% coverage
    - Deeply nested leaf node 88
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/3.png)

### Ordered steps

1. Allocate the arena (88 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 3)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 83% target on doc 88
- [ ] Publish the results

### Benchmark table 3

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 607 | 3 | yes |
| league GFM | 50 | 86 | yes |
| tempest | 28 | 58 | partial |

Here is a fenced code block in `rust`:

```rust
# block 3: zero-copy parser
run --threads 88 --target 83
```

A final paragraph with an autolink <https://autolink.test/3> and a footnote-ish aside. Inline `code spans` survive 88 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 4: the cache-warm parser

This is paragraph **number 4** describing a *cache-warm* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/65) alongside ~~deprecated~~ APIs. We measured a **78% speedup** over the baseline implementation, with a tail latency of *31.7ms* per document.

> Blockquote 4: "Performance is a feature." The parser processed 65 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #65

### Unordered features

- First item with `inline code` and a [link](http://x.test/4)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 78% coverage
    - Deeply nested leaf node 65
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/4.png)

### Ordered steps

1. Allocate the arena (65 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 4)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 78% target on doc 65
- [ ] Publish the results

### Benchmark table 4

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 688 | 2.6 | yes |
| league GFM | 61 | 90 | yes |
| tempest | 43 | 107 | partial |

Here is a fenced code block in `diff`:

```diff
# block 4: cache-warm parser
run --threads 65 --target 78
```

A final paragraph with an autolink <https://autolink.test/4> and a footnote-ish aside. Inline `code spans` survive 65 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 5: the branchless arena

This is paragraph **number 5** describing a *branchless* arena. It runs in `O(n)` time and handles [external links](https://example.com/page/3) alongside ~~deprecated~~ APIs. We measured a **15% speedup** over the baseline implementation, with a tail latency of *20ms* per document.

> Blockquote 5: "Performance is a feature." The arena processed 3 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #3

### Unordered features

- First item with `inline code` and a [link](http://x.test/5)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the arena
  - Another nested point at 15% coverage
    - Deeply nested leaf node 3
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/5.png)

### Ordered steps

1. Allocate the arena (3 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<arena>` tags
4. Free the arena in one shot

### Task list (run 5)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 15% target on doc 3
- [ ] Publish the results

### Benchmark table 5

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 830 | 5.9 | yes |
| league GFM | 47 | 90 | yes |
| tempest | 24 | 73 | partial |

Here is a fenced code block in `json`:

```json
{"doc": 5, "speedup": 15, "latency_ms": 20}
```

A final paragraph with an autolink <https://autolink.test/5> and a footnote-ish aside. Inline `code spans` survive 3 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 6: the compiled codepath

This is paragraph **number 6** describing a *compiled* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/73) alongside ~~deprecated~~ APIs. We measured a **80% speedup** over the baseline implementation, with a tail latency of *6.7ms* per document.

> Blockquote 6: "Performance is a feature." The codepath processed 73 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #73

### Unordered features

- First item with `inline code` and a [link](http://x.test/6)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 80% coverage
    - Deeply nested leaf node 73
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/6.png)

### Ordered steps

1. Allocate the arena (73 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 6)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 80% target on doc 73
- [ ] Publish the results

### Benchmark table 6

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 898 | 4.1 | yes |
| league GFM | 27 | 34 | yes |
| tempest | 58 | 43 | partial |

Here is a fenced code block in `rust`:

```rust
# block 6: compiled codepath
run --threads 73 --target 80
```

A final paragraph with an autolink <https://autolink.test/6> and a footnote-ish aside. Inline `code spans` survive 73 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 7: the cache-warm codepath

This is paragraph **number 7** describing a *cache-warm* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/80) alongside ~~deprecated~~ APIs. We measured a **72% speedup** over the baseline implementation, with a tail latency of *3.4ms* per document.

> Blockquote 7: "Performance is a feature." The codepath processed 80 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #80

### Unordered features

- First item with `inline code` and a [link](http://x.test/7)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 72% coverage
    - Deeply nested leaf node 80
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/7.png)

### Ordered steps

1. Allocate the arena (80 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 7)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 72% target on doc 80
- [ ] Publish the results

### Benchmark table 7

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 565 | 2.2 | yes |
| league GFM | 58 | 42 | yes |
| tempest | 39 | 72 | partial |

Here is a fenced code block in `rust`:

```rust
# block 7: cache-warm codepath
run --threads 80 --target 72
```

A final paragraph with an autolink <https://autolink.test/7> and a footnote-ish aside. Inline `code spans` survive 80 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 8: the branchless arena

This is paragraph **number 8** describing a *branchless* arena. It runs in `O(n)` time and handles [external links](https://example.com/page/85) alongside ~~deprecated~~ APIs. We measured a **22% speedup** over the baseline implementation, with a tail latency of *23.2ms* per document.

> Blockquote 8: "Performance is a feature." The arena processed 85 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #85

### Unordered features

- First item with `inline code` and a [link](http://x.test/8)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the arena
  - Another nested point at 22% coverage
    - Deeply nested leaf node 85
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/8.png)

### Ordered steps

1. Allocate the arena (85 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<arena>` tags
4. Free the arena in one shot

### Task list (run 8)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 22% target on doc 85
- [ ] Publish the results

### Benchmark table 8

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 486 | 2.8 | yes |
| league GFM | 23 | 30 | yes |
| tempest | 30 | 50 | partial |

Here is a fenced code block in `sql`:

```sql
# block 8: branchless arena
run --threads 85 --target 22
```

A final paragraph with an autolink <https://autolink.test/8> and a footnote-ish aside. Inline `code spans` survive 85 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 9: the zero-copy pipeline

This is paragraph **number 9** describing a *zero-copy* pipeline. It runs in `O(n)` time and handles [external links](https://example.com/page/79) alongside ~~deprecated~~ APIs. We measured a **56% speedup** over the baseline implementation, with a tail latency of *94.8ms* per document.

> Blockquote 9: "Performance is a feature." The pipeline processed 79 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #79

### Unordered features

- First item with `inline code` and a [link](http://x.test/9)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the pipeline
  - Another nested point at 56% coverage
    - Deeply nested leaf node 79
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/9.png)

### Ordered steps

1. Allocate the arena (79 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<pipeline>` tags
4. Free the arena in one shot

### Task list (run 9)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 56% target on doc 79
- [ ] Publish the results

### Benchmark table 9

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 541 | 2.5 | yes |
| league GFM | 34 | 84 | yes |
| tempest | 54 | 70 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 9 */
if (!out) return -1; /* 56% of runs */
```

A final paragraph with an autolink <https://autolink.test/9> and a footnote-ish aside. Inline `code spans` survive 79 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 10: the native pipeline

This is paragraph **number 10** describing a *native* pipeline. It runs in `O(n)` time and handles [external links](https://example.com/page/95) alongside ~~deprecated~~ APIs. We measured a **61% speedup** over the baseline implementation, with a tail latency of *51.9ms* per document.

> Blockquote 10: "Performance is a feature." The pipeline processed 95 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #95

### Unordered features

- First item with `inline code` and a [link](http://x.test/10)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the pipeline
  - Another nested point at 61% coverage
    - Deeply nested leaf node 95
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/10.png)

### Ordered steps

1. Allocate the arena (95 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<pipeline>` tags
4. Free the arena in one shot

### Task list (run 10)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 61% target on doc 95
- [ ] Publish the results

### Benchmark table 10

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 462 | 3.7 | yes |
| league GFM | 52 | 33 | yes |
| tempest | 44 | 107 | partial |

Here is a fenced code block in `diff`:

```diff
# block 10: native pipeline
run --threads 95 --target 61
```

A final paragraph with an autolink <https://autolink.test/10> and a footnote-ish aside. Inline `code spans` survive 95 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---

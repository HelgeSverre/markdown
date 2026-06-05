# Synthetic Markdown Document (131072 byte target)

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
## Section 11: the lock-free arena

This is paragraph **number 11** describing a *lock-free* arena. It runs in `O(n)` time and handles [external links](https://example.com/page/48) alongside ~~deprecated~~ APIs. We measured a **47% speedup** over the baseline implementation, with a tail latency of *92ms* per document.

> Blockquote 11: "Performance is a feature." The arena processed 48 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #48

### Unordered features

- First item with `inline code` and a [link](http://x.test/11)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the arena
  - Another nested point at 47% coverage
    - Deeply nested leaf node 48
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/11.png)

### Ordered steps

1. Allocate the arena (48 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<arena>` tags
4. Free the arena in one shot

### Task list (run 11)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 47% target on doc 48
- [ ] Publish the results

### Benchmark table 11

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 728 | 2 | yes |
| league GFM | 38 | 31 | yes |
| tempest | 52 | 103 | partial |

Here is a fenced code block in `diff`:

```diff
# block 11: lock-free arena
run --threads 48 --target 47
```

A final paragraph with an autolink <https://autolink.test/11> and a footnote-ish aside. Inline `code spans` survive 48 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 12: the allocator-aware dispatcher

This is paragraph **number 12** describing a *allocator-aware* dispatcher. It runs in `O(n)` time and handles [external links](https://example.com/page/38) alongside ~~deprecated~~ APIs. We measured a **73% speedup** over the baseline implementation, with a tail latency of *96.6ms* per document.

> Blockquote 12: "Performance is a feature." The dispatcher processed 38 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #38

### Unordered features

- First item with `inline code` and a [link](http://x.test/12)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the dispatcher
  - Another nested point at 73% coverage
    - Deeply nested leaf node 38
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/12.png)

### Ordered steps

1. Allocate the arena (38 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<dispatcher>` tags
4. Free the arena in one shot

### Task list (run 12)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 73% target on doc 38
- [ ] Publish the results

### Benchmark table 12

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 578 | 5.5 | yes |
| league GFM | 33 | 38 | yes |
| tempest | 67 | 68 | partial |

Here is a fenced code block in `diff`:

```diff
# block 12: allocator-aware dispatcher
run --threads 38 --target 73
```

A final paragraph with an autolink <https://autolink.test/12> and a footnote-ish aside. Inline `code spans` survive 38 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 13: the lock-free pipeline

This is paragraph **number 13** describing a *lock-free* pipeline. It runs in `O(n)` time and handles [external links](https://example.com/page/80) alongside ~~deprecated~~ APIs. We measured a **55% speedup** over the baseline implementation, with a tail latency of *70.8ms* per document.

> Blockquote 13: "Performance is a feature." The pipeline processed 80 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #80

### Unordered features

- First item with `inline code` and a [link](http://x.test/13)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the pipeline
  - Another nested point at 55% coverage
    - Deeply nested leaf node 80
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/13.png)

### Ordered steps

1. Allocate the arena (80 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<pipeline>` tags
4. Free the arena in one shot

### Task list (run 13)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 55% target on doc 80
- [ ] Publish the results

### Benchmark table 13

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 806 | 3.5 | yes |
| league GFM | 58 | 80 | yes |
| tempest | 53 | 43 | partial |

Here is a fenced code block in `diff`:

```diff
# block 13: lock-free pipeline
run --threads 80 --target 55
```

A final paragraph with an autolink <https://autolink.test/13> and a footnote-ish aside. Inline `code spans` survive 80 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 14: the branchless shim

This is paragraph **number 14** describing a *branchless* shim. It runs in `O(n)` time and handles [external links](https://example.com/page/15) alongside ~~deprecated~~ APIs. We measured a **80% speedup** over the baseline implementation, with a tail latency of *60.8ms* per document.

> Blockquote 14: "Performance is a feature." The shim processed 15 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #15

### Unordered features

- First item with `inline code` and a [link](http://x.test/14)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the shim
  - Another nested point at 80% coverage
    - Deeply nested leaf node 15
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/14.png)

### Ordered steps

1. Allocate the arena (15 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<shim>` tags
4. Free the arena in one shot

### Task list (run 14)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 80% target on doc 15
- [ ] Publish the results

### Benchmark table 14

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 720 | 2.4 | yes |
| league GFM | 71 | 58 | yes |
| tempest | 34 | 58 | partial |

Here is a fenced code block in `php`:

```php
$html = (new MarkdownFight\Parser())->render($input); // doc 14
assert(strlen($html) > 15);
```

A final paragraph with an autolink <https://autolink.test/14> and a footnote-ish aside. Inline `code spans` survive 15 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 15: the compiled parser

This is paragraph **number 15** describing a *compiled* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/56) alongside ~~deprecated~~ APIs. We measured a **35% speedup** over the baseline implementation, with a tail latency of *21.3ms* per document.

> Blockquote 15: "Performance is a feature." The parser processed 56 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #56

### Unordered features

- First item with `inline code` and a [link](http://x.test/15)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 35% coverage
    - Deeply nested leaf node 56
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/15.png)

### Ordered steps

1. Allocate the arena (56 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 15)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 35% target on doc 56
- [ ] Publish the results

### Benchmark table 15

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 706 | 5.6 | yes |
| league GFM | 33 | 68 | yes |
| tempest | 39 | 85 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 15 */
if (!out) return -1; /* 35% of runs */
```

A final paragraph with an autolink <https://autolink.test/15> and a footnote-ish aside. Inline `code spans` survive 56 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 16: the blazing buffer

This is paragraph **number 16** describing a *blazing* buffer. It runs in `O(n)` time and handles [external links](https://example.com/page/24) alongside ~~deprecated~~ APIs. We measured a **28% speedup** over the baseline implementation, with a tail latency of *29.7ms* per document.

> Blockquote 16: "Performance is a feature." The buffer processed 24 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #24

### Unordered features

- First item with `inline code` and a [link](http://x.test/16)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the buffer
  - Another nested point at 28% coverage
    - Deeply nested leaf node 24
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/16.png)

### Ordered steps

1. Allocate the arena (24 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<buffer>` tags
4. Free the arena in one shot

### Task list (run 16)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 28% target on doc 24
- [ ] Publish the results

### Benchmark table 16

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 567 | 3.7 | yes |
| league GFM | 61 | 78 | yes |
| tempest | 21 | 86 | partial |

Here is a fenced code block in `sql`:

```sql
# block 16: blazing buffer
run --threads 24 --target 28
```

A final paragraph with an autolink <https://autolink.test/16> and a footnote-ish aside. Inline `code spans` survive 24 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 17: the branchless parser

This is paragraph **number 17** describing a *branchless* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/45) alongside ~~deprecated~~ APIs. We measured a **65% speedup** over the baseline implementation, with a tail latency of *52.7ms* per document.

> Blockquote 17: "Performance is a feature." The parser processed 45 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #45

### Unordered features

- First item with `inline code` and a [link](http://x.test/17)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 65% coverage
    - Deeply nested leaf node 45
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/17.png)

### Ordered steps

1. Allocate the arena (45 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 17)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 65% target on doc 45
- [ ] Publish the results

### Benchmark table 17

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 782 | 5.9 | yes |
| league GFM | 60 | 89 | yes |
| tempest | 20 | 104 | partial |

Here is a fenced code block in `json`:

```json
{"doc": 17, "speedup": 65, "latency_ms": 52.7}
```

A final paragraph with an autolink <https://autolink.test/17> and a footnote-ish aside. Inline `code spans` survive 45 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 18: the compiled dispatcher

This is paragraph **number 18** describing a *compiled* dispatcher. It runs in `O(n)` time and handles [external links](https://example.com/page/25) alongside ~~deprecated~~ APIs. We measured a **17% speedup** over the baseline implementation, with a tail latency of *60.6ms* per document.

> Blockquote 18: "Performance is a feature." The dispatcher processed 25 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #25

### Unordered features

- First item with `inline code` and a [link](http://x.test/18)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the dispatcher
  - Another nested point at 17% coverage
    - Deeply nested leaf node 25
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/18.png)

### Ordered steps

1. Allocate the arena (25 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<dispatcher>` tags
4. Free the arena in one shot

### Task list (run 18)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 17% target on doc 25
- [ ] Publish the results

### Benchmark table 18

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 504 | 5.3 | yes |
| league GFM | 50 | 64 | yes |
| tempest | 62 | 72 | partial |

Here is a fenced code block in `rust`:

```rust
# block 18: compiled dispatcher
run --threads 25 --target 17
```

A final paragraph with an autolink <https://autolink.test/18> and a footnote-ish aside. Inline `code spans` survive 25 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 19: the native codepath

This is paragraph **number 19** describing a *native* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/24) alongside ~~deprecated~~ APIs. We measured a **74% speedup** over the baseline implementation, with a tail latency of *66.9ms* per document.

> Blockquote 19: "Performance is a feature." The codepath processed 24 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #24

### Unordered features

- First item with `inline code` and a [link](http://x.test/19)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 74% coverage
    - Deeply nested leaf node 24
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/19.png)

### Ordered steps

1. Allocate the arena (24 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 19)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 74% target on doc 24
- [ ] Publish the results

### Benchmark table 19

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 484 | 4 | yes |
| league GFM | 33 | 84 | yes |
| tempest | 26 | 80 | partial |

Here is a fenced code block in `sql`:

```sql
# block 19: native codepath
run --threads 24 --target 74
```

A final paragraph with an autolink <https://autolink.test/19> and a footnote-ish aside. Inline `code spans` survive 24 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 20: the compiled buffer

This is paragraph **number 20** describing a *compiled* buffer. It runs in `O(n)` time and handles [external links](https://example.com/page/9) alongside ~~deprecated~~ APIs. We measured a **41% speedup** over the baseline implementation, with a tail latency of *99.1ms* per document.

> Blockquote 20: "Performance is a feature." The buffer processed 9 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #9

### Unordered features

- First item with `inline code` and a [link](http://x.test/20)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the buffer
  - Another nested point at 41% coverage
    - Deeply nested leaf node 9
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/20.png)

### Ordered steps

1. Allocate the arena (9 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<buffer>` tags
4. Free the arena in one shot

### Task list (run 20)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 41% target on doc 9
- [ ] Publish the results

### Benchmark table 20

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 650 | 3.8 | yes |
| league GFM | 33 | 48 | yes |
| tempest | 55 | 107 | partial |

Here is a fenced code block in `rust`:

```rust
# block 20: compiled buffer
run --threads 9 --target 41
```

A final paragraph with an autolink <https://autolink.test/20> and a footnote-ish aside. Inline `code spans` survive 9 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 21: the allocator-aware dispatcher

This is paragraph **number 21** describing a *allocator-aware* dispatcher. It runs in `O(n)` time and handles [external links](https://example.com/page/24) alongside ~~deprecated~~ APIs. We measured a **58% speedup** over the baseline implementation, with a tail latency of *80.3ms* per document.

> Blockquote 21: "Performance is a feature." The dispatcher processed 24 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #24

### Unordered features

- First item with `inline code` and a [link](http://x.test/21)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the dispatcher
  - Another nested point at 58% coverage
    - Deeply nested leaf node 24
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/21.png)

### Ordered steps

1. Allocate the arena (24 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<dispatcher>` tags
4. Free the arena in one shot

### Task list (run 21)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 58% target on doc 24
- [ ] Publish the results

### Benchmark table 21

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 419 | 4.1 | yes |
| league GFM | 42 | 35 | yes |
| tempest | 52 | 72 | partial |

Here is a fenced code block in `go`:

```go
# block 21: allocator-aware dispatcher
run --threads 24 --target 58
```

A final paragraph with an autolink <https://autolink.test/21> and a footnote-ish aside. Inline `code spans` survive 24 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 22: the compiled renderer

This is paragraph **number 22** describing a *compiled* renderer. It runs in `O(n)` time and handles [external links](https://example.com/page/34) alongside ~~deprecated~~ APIs. We measured a **30% speedup** over the baseline implementation, with a tail latency of *63ms* per document.

> Blockquote 22: "Performance is a feature." The renderer processed 34 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #34

### Unordered features

- First item with `inline code` and a [link](http://x.test/22)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the renderer
  - Another nested point at 30% coverage
    - Deeply nested leaf node 34
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/22.png)

### Ordered steps

1. Allocate the arena (34 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<renderer>` tags
4. Free the arena in one shot

### Task list (run 22)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 30% target on doc 34
- [ ] Publish the results

### Benchmark table 22

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 746 | 5 | yes |
| league GFM | 44 | 51 | yes |
| tempest | 49 | 59 | partial |

Here is a fenced code block in `json`:

```json
{"doc": 22, "speedup": 30, "latency_ms": 63}
```

A final paragraph with an autolink <https://autolink.test/22> and a footnote-ish aside. Inline `code spans` survive 34 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 23: the lock-free arena

This is paragraph **number 23** describing a *lock-free* arena. It runs in `O(n)` time and handles [external links](https://example.com/page/14) alongside ~~deprecated~~ APIs. We measured a **55% speedup** over the baseline implementation, with a tail latency of *76.1ms* per document.

> Blockquote 23: "Performance is a feature." The arena processed 14 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #14

### Unordered features

- First item with `inline code` and a [link](http://x.test/23)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the arena
  - Another nested point at 55% coverage
    - Deeply nested leaf node 14
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/23.png)

### Ordered steps

1. Allocate the arena (14 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<arena>` tags
4. Free the arena in one shot

### Task list (run 23)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 55% target on doc 14
- [ ] Publish the results

### Benchmark table 23

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 428 | 4.8 | yes |
| league GFM | 74 | 31 | yes |
| tempest | 27 | 42 | partial |

Here is a fenced code block in `json`:

```json
{"doc": 23, "speedup": 55, "latency_ms": 76.1}
```

A final paragraph with an autolink <https://autolink.test/23> and a footnote-ish aside. Inline `code spans` survive 14 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 24: the native parser

This is paragraph **number 24** describing a *native* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/79) alongside ~~deprecated~~ APIs. We measured a **34% speedup** over the baseline implementation, with a tail latency of *43ms* per document.

> Blockquote 24: "Performance is a feature." The parser processed 79 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #79

### Unordered features

- First item with `inline code` and a [link](http://x.test/24)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 34% coverage
    - Deeply nested leaf node 79
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/24.png)

### Ordered steps

1. Allocate the arena (79 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 24)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 34% target on doc 79
- [ ] Publish the results

### Benchmark table 24

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 830 | 5.2 | yes |
| league GFM | 57 | 47 | yes |
| tempest | 55 | 104 | partial |

Here is a fenced code block in `sql`:

```sql
# block 24: native parser
run --threads 79 --target 34
```

A final paragraph with an autolink <https://autolink.test/24> and a footnote-ish aside. Inline `code spans` survive 79 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 25: the streaming renderer

This is paragraph **number 25** describing a *streaming* renderer. It runs in `O(n)` time and handles [external links](https://example.com/page/2) alongside ~~deprecated~~ APIs. We measured a **98% speedup** over the baseline implementation, with a tail latency of *85.9ms* per document.

> Blockquote 25: "Performance is a feature." The renderer processed 2 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #2

### Unordered features

- First item with `inline code` and a [link](http://x.test/25)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the renderer
  - Another nested point at 98% coverage
    - Deeply nested leaf node 2
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/25.png)

### Ordered steps

1. Allocate the arena (2 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<renderer>` tags
4. Free the arena in one shot

### Task list (run 25)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 98% target on doc 2
- [ ] Publish the results

### Benchmark table 25

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 597 | 2.3 | yes |
| league GFM | 68 | 36 | yes |
| tempest | 35 | 67 | partial |

Here is a fenced code block in `json`:

```json
{"doc": 25, "speedup": 98, "latency_ms": 85.9}
```

A final paragraph with an autolink <https://autolink.test/25> and a footnote-ish aside. Inline `code spans` survive 2 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 26: the lock-free parser

This is paragraph **number 26** describing a *lock-free* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/66) alongside ~~deprecated~~ APIs. We measured a **58% speedup** over the baseline implementation, with a tail latency of *95.1ms* per document.

> Blockquote 26: "Performance is a feature." The parser processed 66 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #66

### Unordered features

- First item with `inline code` and a [link](http://x.test/26)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 58% coverage
    - Deeply nested leaf node 66
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/26.png)

### Ordered steps

1. Allocate the arena (66 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 26)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 58% target on doc 66
- [ ] Publish the results

### Benchmark table 26

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 598 | 2.6 | yes |
| league GFM | 79 | 31 | yes |
| tempest | 30 | 67 | partial |

Here is a fenced code block in `bash`:

```bash
# block 26: lock-free parser
run --threads 66 --target 58
```

A final paragraph with an autolink <https://autolink.test/26> and a footnote-ish aside. Inline `code spans` survive 66 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 27: the blazing shim

This is paragraph **number 27** describing a *blazing* shim. It runs in `O(n)` time and handles [external links](https://example.com/page/46) alongside ~~deprecated~~ APIs. We measured a **77% speedup** over the baseline implementation, with a tail latency of *65.3ms* per document.

> Blockquote 27: "Performance is a feature." The shim processed 46 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #46

### Unordered features

- First item with `inline code` and a [link](http://x.test/27)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the shim
  - Another nested point at 77% coverage
    - Deeply nested leaf node 46
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/27.png)

### Ordered steps

1. Allocate the arena (46 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<shim>` tags
4. Free the arena in one shot

### Task list (run 27)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 77% target on doc 46
- [ ] Publish the results

### Benchmark table 27

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 445 | 2.3 | yes |
| league GFM | 43 | 69 | yes |
| tempest | 37 | 58 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 27 */
if (!out) return -1; /* 77% of runs */
```

A final paragraph with an autolink <https://autolink.test/27> and a footnote-ish aside. Inline `code spans` survive 46 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 28: the allocator-aware shim

This is paragraph **number 28** describing a *allocator-aware* shim. It runs in `O(n)` time and handles [external links](https://example.com/page/53) alongside ~~deprecated~~ APIs. We measured a **18% speedup** over the baseline implementation, with a tail latency of *26.9ms* per document.

> Blockquote 28: "Performance is a feature." The shim processed 53 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #53

### Unordered features

- First item with `inline code` and a [link](http://x.test/28)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the shim
  - Another nested point at 18% coverage
    - Deeply nested leaf node 53
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/28.png)

### Ordered steps

1. Allocate the arena (53 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<shim>` tags
4. Free the arena in one shot

### Task list (run 28)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 18% target on doc 53
- [ ] Publish the results

### Benchmark table 28

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 762 | 4.8 | yes |
| league GFM | 60 | 39 | yes |
| tempest | 66 | 98 | partial |

Here is a fenced code block in `bash`:

```bash
# block 28: allocator-aware shim
run --threads 53 --target 18
```

A final paragraph with an autolink <https://autolink.test/28> and a footnote-ish aside. Inline `code spans` survive 53 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 29: the zero-copy codepath

This is paragraph **number 29** describing a *zero-copy* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/71) alongside ~~deprecated~~ APIs. We measured a **29% speedup** over the baseline implementation, with a tail latency of *96.5ms* per document.

> Blockquote 29: "Performance is a feature." The codepath processed 71 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #71

### Unordered features

- First item with `inline code` and a [link](http://x.test/29)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 29% coverage
    - Deeply nested leaf node 71
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/29.png)

### Ordered steps

1. Allocate the arena (71 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 29)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 29% target on doc 71
- [ ] Publish the results

### Benchmark table 29

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 784 | 5.7 | yes |
| league GFM | 27 | 36 | yes |
| tempest | 51 | 80 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 29 */
if (!out) return -1; /* 29% of runs */
```

A final paragraph with an autolink <https://autolink.test/29> and a footnote-ish aside. Inline `code spans` survive 71 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 30: the zero-copy parser

This is paragraph **number 30** describing a *zero-copy* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/3) alongside ~~deprecated~~ APIs. We measured a **29% speedup** over the baseline implementation, with a tail latency of *91.4ms* per document.

> Blockquote 30: "Performance is a feature." The parser processed 3 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #3

### Unordered features

- First item with `inline code` and a [link](http://x.test/30)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 29% coverage
    - Deeply nested leaf node 3
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/30.png)

### Ordered steps

1. Allocate the arena (3 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 30)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 29% target on doc 3
- [ ] Publish the results

### Benchmark table 30

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 476 | 5.1 | yes |
| league GFM | 20 | 30 | yes |
| tempest | 57 | 75 | partial |

Here is a fenced code block in `sql`:

```sql
# block 30: zero-copy parser
run --threads 3 --target 29
```

A final paragraph with an autolink <https://autolink.test/30> and a footnote-ish aside. Inline `code spans` survive 3 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 31: the lock-free tokenizer

This is paragraph **number 31** describing a *lock-free* tokenizer. It runs in `O(n)` time and handles [external links](https://example.com/page/75) alongside ~~deprecated~~ APIs. We measured a **20% speedup** over the baseline implementation, with a tail latency of *49.6ms* per document.

> Blockquote 31: "Performance is a feature." The tokenizer processed 75 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #75

### Unordered features

- First item with `inline code` and a [link](http://x.test/31)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the tokenizer
  - Another nested point at 20% coverage
    - Deeply nested leaf node 75
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/31.png)

### Ordered steps

1. Allocate the arena (75 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<tokenizer>` tags
4. Free the arena in one shot

### Task list (run 31)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 20% target on doc 75
- [ ] Publish the results

### Benchmark table 31

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 493 | 5.7 | yes |
| league GFM | 29 | 53 | yes |
| tempest | 62 | 69 | partial |

Here is a fenced code block in `sql`:

```sql
# block 31: lock-free tokenizer
run --threads 75 --target 20
```

A final paragraph with an autolink <https://autolink.test/31> and a footnote-ish aside. Inline `code spans` survive 75 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 32: the zero-copy tokenizer

This is paragraph **number 32** describing a *zero-copy* tokenizer. It runs in `O(n)` time and handles [external links](https://example.com/page/30) alongside ~~deprecated~~ APIs. We measured a **41% speedup** over the baseline implementation, with a tail latency of *61ms* per document.

> Blockquote 32: "Performance is a feature." The tokenizer processed 30 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #30

### Unordered features

- First item with `inline code` and a [link](http://x.test/32)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the tokenizer
  - Another nested point at 41% coverage
    - Deeply nested leaf node 30
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/32.png)

### Ordered steps

1. Allocate the arena (30 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<tokenizer>` tags
4. Free the arena in one shot

### Task list (run 32)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 41% target on doc 30
- [ ] Publish the results

### Benchmark table 32

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 669 | 5.8 | yes |
| league GFM | 41 | 45 | yes |
| tempest | 69 | 74 | partial |

Here is a fenced code block in `diff`:

```diff
# block 32: zero-copy tokenizer
run --threads 30 --target 41
```

A final paragraph with an autolink <https://autolink.test/32> and a footnote-ish aside. Inline `code spans` survive 30 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 33: the lock-free corpus

This is paragraph **number 33** describing a *lock-free* corpus. It runs in `O(n)` time and handles [external links](https://example.com/page/76) alongside ~~deprecated~~ APIs. We measured a **41% speedup** over the baseline implementation, with a tail latency of *82.4ms* per document.

> Blockquote 33: "Performance is a feature." The corpus processed 76 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #76

### Unordered features

- First item with `inline code` and a [link](http://x.test/33)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the corpus
  - Another nested point at 41% coverage
    - Deeply nested leaf node 76
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/33.png)

### Ordered steps

1. Allocate the arena (76 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<corpus>` tags
4. Free the arena in one shot

### Task list (run 33)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 41% target on doc 76
- [ ] Publish the results

### Benchmark table 33

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 708 | 5.8 | yes |
| league GFM | 68 | 45 | yes |
| tempest | 38 | 76 | partial |

Here is a fenced code block in `rust`:

```rust
# block 33: lock-free corpus
run --threads 76 --target 41
```

A final paragraph with an autolink <https://autolink.test/33> and a footnote-ish aside. Inline `code spans` survive 76 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 34: the branchless pipeline

This is paragraph **number 34** describing a *branchless* pipeline. It runs in `O(n)` time and handles [external links](https://example.com/page/42) alongside ~~deprecated~~ APIs. We measured a **97% speedup** over the baseline implementation, with a tail latency of *88.4ms* per document.

> Blockquote 34: "Performance is a feature." The pipeline processed 42 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #42

### Unordered features

- First item with `inline code` and a [link](http://x.test/34)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the pipeline
  - Another nested point at 97% coverage
    - Deeply nested leaf node 42
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/34.png)

### Ordered steps

1. Allocate the arena (42 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<pipeline>` tags
4. Free the arena in one shot

### Task list (run 34)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 97% target on doc 42
- [ ] Publish the results

### Benchmark table 34

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 400 | 4.9 | yes |
| league GFM | 48 | 81 | yes |
| tempest | 44 | 97 | partial |

Here is a fenced code block in `go`:

```go
# block 34: branchless pipeline
run --threads 42 --target 97
```

A final paragraph with an autolink <https://autolink.test/34> and a footnote-ish aside. Inline `code spans` survive 42 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 35: the vectorized tokenizer

This is paragraph **number 35** describing a *vectorized* tokenizer. It runs in `O(n)` time and handles [external links](https://example.com/page/71) alongside ~~deprecated~~ APIs. We measured a **95% speedup** over the baseline implementation, with a tail latency of *29.1ms* per document.

> Blockquote 35: "Performance is a feature." The tokenizer processed 71 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #71

### Unordered features

- First item with `inline code` and a [link](http://x.test/35)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the tokenizer
  - Another nested point at 95% coverage
    - Deeply nested leaf node 71
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/35.png)

### Ordered steps

1. Allocate the arena (71 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<tokenizer>` tags
4. Free the arena in one shot

### Task list (run 35)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 95% target on doc 71
- [ ] Publish the results

### Benchmark table 35

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 483 | 2.2 | yes |
| league GFM | 20 | 31 | yes |
| tempest | 22 | 58 | partial |

Here is a fenced code block in `rust`:

```rust
# block 35: vectorized tokenizer
run --threads 71 --target 95
```

A final paragraph with an autolink <https://autolink.test/35> and a footnote-ish aside. Inline `code spans` survive 71 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 36: the lock-free parser

This is paragraph **number 36** describing a *lock-free* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/17) alongside ~~deprecated~~ APIs. We measured a **36% speedup** over the baseline implementation, with a tail latency of *69.2ms* per document.

> Blockquote 36: "Performance is a feature." The parser processed 17 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #17

### Unordered features

- First item with `inline code` and a [link](http://x.test/36)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 36% coverage
    - Deeply nested leaf node 17
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/36.png)

### Ordered steps

1. Allocate the arena (17 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 36)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 36% target on doc 17
- [ ] Publish the results

### Benchmark table 36

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 711 | 3.4 | yes |
| league GFM | 39 | 83 | yes |
| tempest | 21 | 41 | partial |

Here is a fenced code block in `php`:

```php
$html = (new MarkdownFight\Parser())->render($input); // doc 36
assert(strlen($html) > 17);
```

A final paragraph with an autolink <https://autolink.test/36> and a footnote-ish aside. Inline `code spans` survive 17 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 37: the lock-free arena

This is paragraph **number 37** describing a *lock-free* arena. It runs in `O(n)` time and handles [external links](https://example.com/page/68) alongside ~~deprecated~~ APIs. We measured a **52% speedup** over the baseline implementation, with a tail latency of *42.5ms* per document.

> Blockquote 37: "Performance is a feature." The arena processed 68 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #68

### Unordered features

- First item with `inline code` and a [link](http://x.test/37)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the arena
  - Another nested point at 52% coverage
    - Deeply nested leaf node 68
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/37.png)

### Ordered steps

1. Allocate the arena (68 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<arena>` tags
4. Free the arena in one shot

### Task list (run 37)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 52% target on doc 68
- [ ] Publish the results

### Benchmark table 37

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 514 | 4.5 | yes |
| league GFM | 41 | 52 | yes |
| tempest | 27 | 91 | partial |

Here is a fenced code block in `php`:

```php
$html = (new MarkdownFight\Parser())->render($input); // doc 37
assert(strlen($html) > 68);
```

A final paragraph with an autolink <https://autolink.test/37> and a footnote-ish aside. Inline `code spans` survive 68 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 38: the lock-free parser

This is paragraph **number 38** describing a *lock-free* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/64) alongside ~~deprecated~~ APIs. We measured a **39% speedup** over the baseline implementation, with a tail latency of *78.1ms* per document.

> Blockquote 38: "Performance is a feature." The parser processed 64 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #64

### Unordered features

- First item with `inline code` and a [link](http://x.test/38)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 39% coverage
    - Deeply nested leaf node 64
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/38.png)

### Ordered steps

1. Allocate the arena (64 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 38)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 39% target on doc 64
- [ ] Publish the results

### Benchmark table 38

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 449 | 4.5 | yes |
| league GFM | 32 | 83 | yes |
| tempest | 17 | 107 | partial |

Here is a fenced code block in `sql`:

```sql
# block 38: lock-free parser
run --threads 64 --target 39
```

A final paragraph with an autolink <https://autolink.test/38> and a footnote-ish aside. Inline `code spans` survive 64 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 39: the compiled parser

This is paragraph **number 39** describing a *compiled* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/76) alongside ~~deprecated~~ APIs. We measured a **13% speedup** over the baseline implementation, with a tail latency of *11.2ms* per document.

> Blockquote 39: "Performance is a feature." The parser processed 76 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #76

### Unordered features

- First item with `inline code` and a [link](http://x.test/39)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 13% coverage
    - Deeply nested leaf node 76
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/39.png)

### Ordered steps

1. Allocate the arena (76 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 39)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 13% target on doc 76
- [ ] Publish the results

### Benchmark table 39

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 639 | 5.9 | yes |
| league GFM | 80 | 90 | yes |
| tempest | 48 | 48 | partial |

Here is a fenced code block in `go`:

```go
# block 39: compiled parser
run --threads 76 --target 13
```

A final paragraph with an autolink <https://autolink.test/39> and a footnote-ish aside. Inline `code spans` survive 76 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 40: the vectorized parser

This is paragraph **number 40** describing a *vectorized* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/8) alongside ~~deprecated~~ APIs. We measured a **64% speedup** over the baseline implementation, with a tail latency of *68.9ms* per document.

> Blockquote 40: "Performance is a feature." The parser processed 8 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #8

### Unordered features

- First item with `inline code` and a [link](http://x.test/40)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 64% coverage
    - Deeply nested leaf node 8
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/40.png)

### Ordered steps

1. Allocate the arena (8 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 40)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 64% target on doc 8
- [ ] Publish the results

### Benchmark table 40

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 517 | 3.8 | yes |
| league GFM | 71 | 84 | yes |
| tempest | 17 | 87 | partial |

Here is a fenced code block in `php`:

```php
$html = (new MarkdownFight\Parser())->render($input); // doc 40
assert(strlen($html) > 8);
```

A final paragraph with an autolink <https://autolink.test/40> and a footnote-ish aside. Inline `code spans` survive 8 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 41: the blazing dispatcher

This is paragraph **number 41** describing a *blazing* dispatcher. It runs in `O(n)` time and handles [external links](https://example.com/page/22) alongside ~~deprecated~~ APIs. We measured a **78% speedup** over the baseline implementation, with a tail latency of *66.8ms* per document.

> Blockquote 41: "Performance is a feature." The dispatcher processed 22 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #22

### Unordered features

- First item with `inline code` and a [link](http://x.test/41)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the dispatcher
  - Another nested point at 78% coverage
    - Deeply nested leaf node 22
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/41.png)

### Ordered steps

1. Allocate the arena (22 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<dispatcher>` tags
4. Free the arena in one shot

### Task list (run 41)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 78% target on doc 22
- [ ] Publish the results

### Benchmark table 41

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 879 | 4.6 | yes |
| league GFM | 29 | 81 | yes |
| tempest | 24 | 47 | partial |

Here is a fenced code block in `go`:

```go
# block 41: blazing dispatcher
run --threads 22 --target 78
```

A final paragraph with an autolink <https://autolink.test/41> and a footnote-ish aside. Inline `code spans` survive 22 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 42: the streaming corpus

This is paragraph **number 42** describing a *streaming* corpus. It runs in `O(n)` time and handles [external links](https://example.com/page/17) alongside ~~deprecated~~ APIs. We measured a **85% speedup** over the baseline implementation, with a tail latency of *47.8ms* per document.

> Blockquote 42: "Performance is a feature." The corpus processed 17 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #17

### Unordered features

- First item with `inline code` and a [link](http://x.test/42)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the corpus
  - Another nested point at 85% coverage
    - Deeply nested leaf node 17
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/42.png)

### Ordered steps

1. Allocate the arena (17 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<corpus>` tags
4. Free the arena in one shot

### Task list (run 42)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 85% target on doc 17
- [ ] Publish the results

### Benchmark table 42

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 486 | 3.9 | yes |
| league GFM | 44 | 71 | yes |
| tempest | 53 | 85 | partial |

Here is a fenced code block in `go`:

```go
# block 42: streaming corpus
run --threads 17 --target 85
```

A final paragraph with an autolink <https://autolink.test/42> and a footnote-ish aside. Inline `code spans` survive 17 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 43: the blazing buffer

This is paragraph **number 43** describing a *blazing* buffer. It runs in `O(n)` time and handles [external links](https://example.com/page/6) alongside ~~deprecated~~ APIs. We measured a **13% speedup** over the baseline implementation, with a tail latency of *60ms* per document.

> Blockquote 43: "Performance is a feature." The buffer processed 6 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #6

### Unordered features

- First item with `inline code` and a [link](http://x.test/43)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the buffer
  - Another nested point at 13% coverage
    - Deeply nested leaf node 6
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/43.png)

### Ordered steps

1. Allocate the arena (6 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<buffer>` tags
4. Free the arena in one shot

### Task list (run 43)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 13% target on doc 6
- [ ] Publish the results

### Benchmark table 43

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 571 | 3.9 | yes |
| league GFM | 64 | 66 | yes |
| tempest | 37 | 74 | partial |

Here is a fenced code block in `rust`:

```rust
# block 43: blazing buffer
run --threads 6 --target 13
```

A final paragraph with an autolink <https://autolink.test/43> and a footnote-ish aside. Inline `code spans` survive 6 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 44: the streaming arena

This is paragraph **number 44** describing a *streaming* arena. It runs in `O(n)` time and handles [external links](https://example.com/page/80) alongside ~~deprecated~~ APIs. We measured a **44% speedup** over the baseline implementation, with a tail latency of *86.1ms* per document.

> Blockquote 44: "Performance is a feature." The arena processed 80 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #80

### Unordered features

- First item with `inline code` and a [link](http://x.test/44)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the arena
  - Another nested point at 44% coverage
    - Deeply nested leaf node 80
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/44.png)

### Ordered steps

1. Allocate the arena (80 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<arena>` tags
4. Free the arena in one shot

### Task list (run 44)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 44% target on doc 80
- [ ] Publish the results

### Benchmark table 44

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 570 | 3.8 | yes |
| league GFM | 72 | 67 | yes |
| tempest | 68 | 73 | partial |

Here is a fenced code block in `php`:

```php
$html = (new MarkdownFight\Parser())->render($input); // doc 44
assert(strlen($html) > 80);
```

A final paragraph with an autolink <https://autolink.test/44> and a footnote-ish aside. Inline `code spans` survive 80 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 45: the zero-copy shim

This is paragraph **number 45** describing a *zero-copy* shim. It runs in `O(n)` time and handles [external links](https://example.com/page/63) alongside ~~deprecated~~ APIs. We measured a **28% speedup** over the baseline implementation, with a tail latency of *19ms* per document.

> Blockquote 45: "Performance is a feature." The shim processed 63 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #63

### Unordered features

- First item with `inline code` and a [link](http://x.test/45)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the shim
  - Another nested point at 28% coverage
    - Deeply nested leaf node 63
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/45.png)

### Ordered steps

1. Allocate the arena (63 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<shim>` tags
4. Free the arena in one shot

### Task list (run 45)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 28% target on doc 63
- [ ] Publish the results

### Benchmark table 45

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 812 | 5.6 | yes |
| league GFM | 67 | 58 | yes |
| tempest | 52 | 53 | partial |

Here is a fenced code block in `go`:

```go
# block 45: zero-copy shim
run --threads 63 --target 28
```

A final paragraph with an autolink <https://autolink.test/45> and a footnote-ish aside. Inline `code spans` survive 63 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 46: the blazing codepath

This is paragraph **number 46** describing a *blazing* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/92) alongside ~~deprecated~~ APIs. We measured a **99% speedup** over the baseline implementation, with a tail latency of *20.6ms* per document.

> Blockquote 46: "Performance is a feature." The codepath processed 92 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #92

### Unordered features

- First item with `inline code` and a [link](http://x.test/46)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 99% coverage
    - Deeply nested leaf node 92
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/46.png)

### Ordered steps

1. Allocate the arena (92 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 46)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 99% target on doc 92
- [ ] Publish the results

### Benchmark table 46

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 723 | 5.1 | yes |
| league GFM | 41 | 68 | yes |
| tempest | 38 | 66 | partial |

Here is a fenced code block in `bash`:

```bash
# block 46: blazing codepath
run --threads 92 --target 99
```

A final paragraph with an autolink <https://autolink.test/46> and a footnote-ish aside. Inline `code spans` survive 92 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 47: the native dispatcher

This is paragraph **number 47** describing a *native* dispatcher. It runs in `O(n)` time and handles [external links](https://example.com/page/12) alongside ~~deprecated~~ APIs. We measured a **72% speedup** over the baseline implementation, with a tail latency of *26.8ms* per document.

> Blockquote 47: "Performance is a feature." The dispatcher processed 12 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #12

### Unordered features

- First item with `inline code` and a [link](http://x.test/47)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the dispatcher
  - Another nested point at 72% coverage
    - Deeply nested leaf node 12
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/47.png)

### Ordered steps

1. Allocate the arena (12 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<dispatcher>` tags
4. Free the arena in one shot

### Task list (run 47)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 72% target on doc 12
- [ ] Publish the results

### Benchmark table 47

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 876 | 4 | yes |
| league GFM | 40 | 89 | yes |
| tempest | 31 | 77 | partial |

Here is a fenced code block in `bash`:

```bash
# block 47: native dispatcher
run --threads 12 --target 72
```

A final paragraph with an autolink <https://autolink.test/47> and a footnote-ish aside. Inline `code spans` survive 12 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 48: the blazing renderer

This is paragraph **number 48** describing a *blazing* renderer. It runs in `O(n)` time and handles [external links](https://example.com/page/74) alongside ~~deprecated~~ APIs. We measured a **14% speedup** over the baseline implementation, with a tail latency of *90.8ms* per document.

> Blockquote 48: "Performance is a feature." The renderer processed 74 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #74

### Unordered features

- First item with `inline code` and a [link](http://x.test/48)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the renderer
  - Another nested point at 14% coverage
    - Deeply nested leaf node 74
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/48.png)

### Ordered steps

1. Allocate the arena (74 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<renderer>` tags
4. Free the arena in one shot

### Task list (run 48)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 14% target on doc 74
- [ ] Publish the results

### Benchmark table 48

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 731 | 2.8 | yes |
| league GFM | 59 | 80 | yes |
| tempest | 64 | 100 | partial |

Here is a fenced code block in `rust`:

```rust
# block 48: blazing renderer
run --threads 74 --target 14
```

A final paragraph with an autolink <https://autolink.test/48> and a footnote-ish aside. Inline `code spans` survive 74 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 49: the native codepath

This is paragraph **number 49** describing a *native* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/12) alongside ~~deprecated~~ APIs. We measured a **65% speedup** over the baseline implementation, with a tail latency of *98.6ms* per document.

> Blockquote 49: "Performance is a feature." The codepath processed 12 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #12

### Unordered features

- First item with `inline code` and a [link](http://x.test/49)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 65% coverage
    - Deeply nested leaf node 12
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/49.png)

### Ordered steps

1. Allocate the arena (12 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 49)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 65% target on doc 12
- [ ] Publish the results

### Benchmark table 49

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 621 | 4.3 | yes |
| league GFM | 63 | 53 | yes |
| tempest | 46 | 50 | partial |

Here is a fenced code block in `json`:

```json
{"doc": 49, "speedup": 65, "latency_ms": 98.6}
```

A final paragraph with an autolink <https://autolink.test/49> and a footnote-ish aside. Inline `code spans` survive 12 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 50: the lock-free buffer

This is paragraph **number 50** describing a *lock-free* buffer. It runs in `O(n)` time and handles [external links](https://example.com/page/74) alongside ~~deprecated~~ APIs. We measured a **36% speedup** over the baseline implementation, with a tail latency of *29.8ms* per document.

> Blockquote 50: "Performance is a feature." The buffer processed 74 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #74

### Unordered features

- First item with `inline code` and a [link](http://x.test/50)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the buffer
  - Another nested point at 36% coverage
    - Deeply nested leaf node 74
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/50.png)

### Ordered steps

1. Allocate the arena (74 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<buffer>` tags
4. Free the arena in one shot

### Task list (run 50)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 36% target on doc 74
- [ ] Publish the results

### Benchmark table 50

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 644 | 4.7 | yes |
| league GFM | 41 | 78 | yes |
| tempest | 68 | 60 | partial |

Here is a fenced code block in `go`:

```go
# block 50: lock-free buffer
run --threads 74 --target 36
```

A final paragraph with an autolink <https://autolink.test/50> and a footnote-ish aside. Inline `code spans` survive 74 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 51: the streaming tokenizer

This is paragraph **number 51** describing a *streaming* tokenizer. It runs in `O(n)` time and handles [external links](https://example.com/page/8) alongside ~~deprecated~~ APIs. We measured a **97% speedup** over the baseline implementation, with a tail latency of *37.7ms* per document.

> Blockquote 51: "Performance is a feature." The tokenizer processed 8 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #8

### Unordered features

- First item with `inline code` and a [link](http://x.test/51)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the tokenizer
  - Another nested point at 97% coverage
    - Deeply nested leaf node 8
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/51.png)

### Ordered steps

1. Allocate the arena (8 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<tokenizer>` tags
4. Free the arena in one shot

### Task list (run 51)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 97% target on doc 8
- [ ] Publish the results

### Benchmark table 51

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 878 | 5.5 | yes |
| league GFM | 53 | 75 | yes |
| tempest | 29 | 107 | partial |

Here is a fenced code block in `sql`:

```sql
# block 51: streaming tokenizer
run --threads 8 --target 97
```

A final paragraph with an autolink <https://autolink.test/51> and a footnote-ish aside. Inline `code spans` survive 8 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 52: the blazing dispatcher

This is paragraph **number 52** describing a *blazing* dispatcher. It runs in `O(n)` time and handles [external links](https://example.com/page/64) alongside ~~deprecated~~ APIs. We measured a **58% speedup** over the baseline implementation, with a tail latency of *77.9ms* per document.

> Blockquote 52: "Performance is a feature." The dispatcher processed 64 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #64

### Unordered features

- First item with `inline code` and a [link](http://x.test/52)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the dispatcher
  - Another nested point at 58% coverage
    - Deeply nested leaf node 64
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/52.png)

### Ordered steps

1. Allocate the arena (64 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<dispatcher>` tags
4. Free the arena in one shot

### Task list (run 52)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 58% target on doc 64
- [ ] Publish the results

### Benchmark table 52

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 526 | 3.5 | yes |
| league GFM | 72 | 58 | yes |
| tempest | 21 | 67 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 52 */
if (!out) return -1; /* 58% of runs */
```

A final paragraph with an autolink <https://autolink.test/52> and a footnote-ish aside. Inline `code spans` survive 64 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 53: the allocator-aware parser

This is paragraph **number 53** describing a *allocator-aware* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/51) alongside ~~deprecated~~ APIs. We measured a **65% speedup** over the baseline implementation, with a tail latency of *97.8ms* per document.

> Blockquote 53: "Performance is a feature." The parser processed 51 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #51

### Unordered features

- First item with `inline code` and a [link](http://x.test/53)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 65% coverage
    - Deeply nested leaf node 51
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/53.png)

### Ordered steps

1. Allocate the arena (51 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 53)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 65% target on doc 51
- [ ] Publish the results

### Benchmark table 53

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 874 | 2.3 | yes |
| league GFM | 63 | 56 | yes |
| tempest | 51 | 104 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 53 */
if (!out) return -1; /* 65% of runs */
```

A final paragraph with an autolink <https://autolink.test/53> and a footnote-ish aside. Inline `code spans` survive 51 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 54: the native codepath

This is paragraph **number 54** describing a *native* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/25) alongside ~~deprecated~~ APIs. We measured a **98% speedup** over the baseline implementation, with a tail latency of *5.9ms* per document.

> Blockquote 54: "Performance is a feature." The codepath processed 25 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #25

### Unordered features

- First item with `inline code` and a [link](http://x.test/54)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 98% coverage
    - Deeply nested leaf node 25
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/54.png)

### Ordered steps

1. Allocate the arena (25 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 54)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 98% target on doc 25
- [ ] Publish the results

### Benchmark table 54

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 496 | 2.3 | yes |
| league GFM | 52 | 76 | yes |
| tempest | 69 | 84 | partial |

Here is a fenced code block in `diff`:

```diff
# block 54: native codepath
run --threads 25 --target 98
```

A final paragraph with an autolink <https://autolink.test/54> and a footnote-ish aside. Inline `code spans` survive 25 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 55: the compiled tokenizer

This is paragraph **number 55** describing a *compiled* tokenizer. It runs in `O(n)` time and handles [external links](https://example.com/page/31) alongside ~~deprecated~~ APIs. We measured a **88% speedup** over the baseline implementation, with a tail latency of *40.4ms* per document.

> Blockquote 55: "Performance is a feature." The tokenizer processed 31 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #31

### Unordered features

- First item with `inline code` and a [link](http://x.test/55)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the tokenizer
  - Another nested point at 88% coverage
    - Deeply nested leaf node 31
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/55.png)

### Ordered steps

1. Allocate the arena (31 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<tokenizer>` tags
4. Free the arena in one shot

### Task list (run 55)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 88% target on doc 31
- [ ] Publish the results

### Benchmark table 55

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 900 | 5.2 | yes |
| league GFM | 56 | 63 | yes |
| tempest | 22 | 67 | partial |

Here is a fenced code block in `rust`:

```rust
# block 55: compiled tokenizer
run --threads 31 --target 88
```

A final paragraph with an autolink <https://autolink.test/55> and a footnote-ish aside. Inline `code spans` survive 31 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 56: the native buffer

This is paragraph **number 56** describing a *native* buffer. It runs in `O(n)` time and handles [external links](https://example.com/page/88) alongside ~~deprecated~~ APIs. We measured a **27% speedup** over the baseline implementation, with a tail latency of *2.1ms* per document.

> Blockquote 56: "Performance is a feature." The buffer processed 88 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #88

### Unordered features

- First item with `inline code` and a [link](http://x.test/56)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the buffer
  - Another nested point at 27% coverage
    - Deeply nested leaf node 88
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/56.png)

### Ordered steps

1. Allocate the arena (88 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<buffer>` tags
4. Free the arena in one shot

### Task list (run 56)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 27% target on doc 88
- [ ] Publish the results

### Benchmark table 56

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 703 | 2.8 | yes |
| league GFM | 68 | 90 | yes |
| tempest | 68 | 73 | partial |

Here is a fenced code block in `php`:

```php
$html = (new MarkdownFight\Parser())->render($input); // doc 56
assert(strlen($html) > 88);
```

A final paragraph with an autolink <https://autolink.test/56> and a footnote-ish aside. Inline `code spans` survive 88 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 57: the branchless shim

This is paragraph **number 57** describing a *branchless* shim. It runs in `O(n)` time and handles [external links](https://example.com/page/64) alongside ~~deprecated~~ APIs. We measured a **78% speedup** over the baseline implementation, with a tail latency of *59.2ms* per document.

> Blockquote 57: "Performance is a feature." The shim processed 64 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #64

### Unordered features

- First item with `inline code` and a [link](http://x.test/57)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the shim
  - Another nested point at 78% coverage
    - Deeply nested leaf node 64
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/57.png)

### Ordered steps

1. Allocate the arena (64 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<shim>` tags
4. Free the arena in one shot

### Task list (run 57)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 78% target on doc 64
- [ ] Publish the results

### Benchmark table 57

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 747 | 2.4 | yes |
| league GFM | 50 | 73 | yes |
| tempest | 45 | 76 | partial |

Here is a fenced code block in `diff`:

```diff
# block 57: branchless shim
run --threads 64 --target 78
```

A final paragraph with an autolink <https://autolink.test/57> and a footnote-ish aside. Inline `code spans` survive 64 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 58: the lock-free codepath

This is paragraph **number 58** describing a *lock-free* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/34) alongside ~~deprecated~~ APIs. We measured a **23% speedup** over the baseline implementation, with a tail latency of *18.1ms* per document.

> Blockquote 58: "Performance is a feature." The codepath processed 34 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #34

### Unordered features

- First item with `inline code` and a [link](http://x.test/58)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 23% coverage
    - Deeply nested leaf node 34
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/58.png)

### Ordered steps

1. Allocate the arena (34 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 58)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 23% target on doc 34
- [ ] Publish the results

### Benchmark table 58

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 754 | 4.8 | yes |
| league GFM | 43 | 46 | yes |
| tempest | 40 | 40 | partial |

Here is a fenced code block in `json`:

```json
{"doc": 58, "speedup": 23, "latency_ms": 18.1}
```

A final paragraph with an autolink <https://autolink.test/58> and a footnote-ish aside. Inline `code spans` survive 34 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 59: the zero-copy renderer

This is paragraph **number 59** describing a *zero-copy* renderer. It runs in `O(n)` time and handles [external links](https://example.com/page/97) alongside ~~deprecated~~ APIs. We measured a **26% speedup** over the baseline implementation, with a tail latency of *24.7ms* per document.

> Blockquote 59: "Performance is a feature." The renderer processed 97 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #97

### Unordered features

- First item with `inline code` and a [link](http://x.test/59)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the renderer
  - Another nested point at 26% coverage
    - Deeply nested leaf node 97
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/59.png)

### Ordered steps

1. Allocate the arena (97 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<renderer>` tags
4. Free the arena in one shot

### Task list (run 59)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 26% target on doc 97
- [ ] Publish the results

### Benchmark table 59

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 850 | 6 | yes |
| league GFM | 45 | 71 | yes |
| tempest | 31 | 79 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 59 */
if (!out) return -1; /* 26% of runs */
```

A final paragraph with an autolink <https://autolink.test/59> and a footnote-ish aside. Inline `code spans` survive 97 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 60: the blazing renderer

This is paragraph **number 60** describing a *blazing* renderer. It runs in `O(n)` time and handles [external links](https://example.com/page/26) alongside ~~deprecated~~ APIs. We measured a **38% speedup** over the baseline implementation, with a tail latency of *4.2ms* per document.

> Blockquote 60: "Performance is a feature." The renderer processed 26 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #26

### Unordered features

- First item with `inline code` and a [link](http://x.test/60)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the renderer
  - Another nested point at 38% coverage
    - Deeply nested leaf node 26
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/60.png)

### Ordered steps

1. Allocate the arena (26 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<renderer>` tags
4. Free the arena in one shot

### Task list (run 60)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 38% target on doc 26
- [ ] Publish the results

### Benchmark table 60

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 588 | 4.1 | yes |
| league GFM | 73 | 34 | yes |
| tempest | 70 | 101 | partial |

Here is a fenced code block in `go`:

```go
# block 60: blazing renderer
run --threads 26 --target 38
```

A final paragraph with an autolink <https://autolink.test/60> and a footnote-ish aside. Inline `code spans` survive 26 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 61: the native renderer

This is paragraph **number 61** describing a *native* renderer. It runs in `O(n)` time and handles [external links](https://example.com/page/48) alongside ~~deprecated~~ APIs. We measured a **62% speedup** over the baseline implementation, with a tail latency of *90.3ms* per document.

> Blockquote 61: "Performance is a feature." The renderer processed 48 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #48

### Unordered features

- First item with `inline code` and a [link](http://x.test/61)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the renderer
  - Another nested point at 62% coverage
    - Deeply nested leaf node 48
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/61.png)

### Ordered steps

1. Allocate the arena (48 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<renderer>` tags
4. Free the arena in one shot

### Task list (run 61)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 62% target on doc 48
- [ ] Publish the results

### Benchmark table 61

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 637 | 5 | yes |
| league GFM | 62 | 74 | yes |
| tempest | 18 | 84 | partial |

Here is a fenced code block in `sql`:

```sql
# block 61: native renderer
run --threads 48 --target 62
```

A final paragraph with an autolink <https://autolink.test/61> and a footnote-ish aside. Inline `code spans` survive 48 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 62: the allocator-aware shim

This is paragraph **number 62** describing a *allocator-aware* shim. It runs in `O(n)` time and handles [external links](https://example.com/page/8) alongside ~~deprecated~~ APIs. We measured a **19% speedup** over the baseline implementation, with a tail latency of *46.8ms* per document.

> Blockquote 62: "Performance is a feature." The shim processed 8 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #8

### Unordered features

- First item with `inline code` and a [link](http://x.test/62)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the shim
  - Another nested point at 19% coverage
    - Deeply nested leaf node 8
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/62.png)

### Ordered steps

1. Allocate the arena (8 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<shim>` tags
4. Free the arena in one shot

### Task list (run 62)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 19% target on doc 8
- [ ] Publish the results

### Benchmark table 62

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 631 | 5.2 | yes |
| league GFM | 43 | 70 | yes |
| tempest | 39 | 86 | partial |

Here is a fenced code block in `diff`:

```diff
# block 62: allocator-aware shim
run --threads 8 --target 19
```

A final paragraph with an autolink <https://autolink.test/62> and a footnote-ish aside. Inline `code spans` survive 8 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 63: the cache-warm shim

This is paragraph **number 63** describing a *cache-warm* shim. It runs in `O(n)` time and handles [external links](https://example.com/page/73) alongside ~~deprecated~~ APIs. We measured a **93% speedup** over the baseline implementation, with a tail latency of *59.6ms* per document.

> Blockquote 63: "Performance is a feature." The shim processed 73 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #73

### Unordered features

- First item with `inline code` and a [link](http://x.test/63)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the shim
  - Another nested point at 93% coverage
    - Deeply nested leaf node 73
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/63.png)

### Ordered steps

1. Allocate the arena (73 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<shim>` tags
4. Free the arena in one shot

### Task list (run 63)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 93% target on doc 73
- [ ] Publish the results

### Benchmark table 63

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 592 | 4.9 | yes |
| league GFM | 71 | 65 | yes |
| tempest | 61 | 104 | partial |

Here is a fenced code block in `sql`:

```sql
# block 63: cache-warm shim
run --threads 73 --target 93
```

A final paragraph with an autolink <https://autolink.test/63> and a footnote-ish aside. Inline `code spans` survive 73 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 64: the blazing dispatcher

This is paragraph **number 64** describing a *blazing* dispatcher. It runs in `O(n)` time and handles [external links](https://example.com/page/91) alongside ~~deprecated~~ APIs. We measured a **73% speedup** over the baseline implementation, with a tail latency of *96.3ms* per document.

> Blockquote 64: "Performance is a feature." The dispatcher processed 91 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #91

### Unordered features

- First item with `inline code` and a [link](http://x.test/64)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the dispatcher
  - Another nested point at 73% coverage
    - Deeply nested leaf node 91
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/64.png)

### Ordered steps

1. Allocate the arena (91 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<dispatcher>` tags
4. Free the arena in one shot

### Task list (run 64)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 73% target on doc 91
- [ ] Publish the results

### Benchmark table 64

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 821 | 5.8 | yes |
| league GFM | 28 | 73 | yes |
| tempest | 35 | 91 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 64 */
if (!out) return -1; /* 73% of runs */
```

A final paragraph with an autolink <https://autolink.test/64> and a footnote-ish aside. Inline `code spans` survive 91 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 65: the lock-free buffer

This is paragraph **number 65** describing a *lock-free* buffer. It runs in `O(n)` time and handles [external links](https://example.com/page/52) alongside ~~deprecated~~ APIs. We measured a **91% speedup** over the baseline implementation, with a tail latency of *34.5ms* per document.

> Blockquote 65: "Performance is a feature." The buffer processed 52 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #52

### Unordered features

- First item with `inline code` and a [link](http://x.test/65)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the buffer
  - Another nested point at 91% coverage
    - Deeply nested leaf node 52
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/65.png)

### Ordered steps

1. Allocate the arena (52 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<buffer>` tags
4. Free the arena in one shot

### Task list (run 65)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 91% target on doc 52
- [ ] Publish the results

### Benchmark table 65

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 637 | 3.7 | yes |
| league GFM | 56 | 44 | yes |
| tempest | 42 | 43 | partial |

Here is a fenced code block in `php`:

```php
$html = (new MarkdownFight\Parser())->render($input); // doc 65
assert(strlen($html) > 52);
```

A final paragraph with an autolink <https://autolink.test/65> and a footnote-ish aside. Inline `code spans` survive 52 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 66: the allocator-aware tokenizer

This is paragraph **number 66** describing a *allocator-aware* tokenizer. It runs in `O(n)` time and handles [external links](https://example.com/page/15) alongside ~~deprecated~~ APIs. We measured a **79% speedup** over the baseline implementation, with a tail latency of *87.8ms* per document.

> Blockquote 66: "Performance is a feature." The tokenizer processed 15 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #15

### Unordered features

- First item with `inline code` and a [link](http://x.test/66)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the tokenizer
  - Another nested point at 79% coverage
    - Deeply nested leaf node 15
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/66.png)

### Ordered steps

1. Allocate the arena (15 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<tokenizer>` tags
4. Free the arena in one shot

### Task list (run 66)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 79% target on doc 15
- [ ] Publish the results

### Benchmark table 66

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 466 | 5.5 | yes |
| league GFM | 32 | 85 | yes |
| tempest | 36 | 70 | partial |

Here is a fenced code block in `diff`:

```diff
# block 66: allocator-aware tokenizer
run --threads 15 --target 79
```

A final paragraph with an autolink <https://autolink.test/66> and a footnote-ish aside. Inline `code spans` survive 15 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 67: the compiled shim

This is paragraph **number 67** describing a *compiled* shim. It runs in `O(n)` time and handles [external links](https://example.com/page/19) alongside ~~deprecated~~ APIs. We measured a **66% speedup** over the baseline implementation, with a tail latency of *69.3ms* per document.

> Blockquote 67: "Performance is a feature." The shim processed 19 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #19

### Unordered features

- First item with `inline code` and a [link](http://x.test/67)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the shim
  - Another nested point at 66% coverage
    - Deeply nested leaf node 19
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/67.png)

### Ordered steps

1. Allocate the arena (19 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<shim>` tags
4. Free the arena in one shot

### Task list (run 67)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 66% target on doc 19
- [ ] Publish the results

### Benchmark table 67

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 401 | 5.6 | yes |
| league GFM | 23 | 51 | yes |
| tempest | 18 | 89 | partial |

Here is a fenced code block in `json`:

```json
{"doc": 67, "speedup": 66, "latency_ms": 69.3}
```

A final paragraph with an autolink <https://autolink.test/67> and a footnote-ish aside. Inline `code spans` survive 19 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 68: the compiled codepath

This is paragraph **number 68** describing a *compiled* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/82) alongside ~~deprecated~~ APIs. We measured a **14% speedup** over the baseline implementation, with a tail latency of *79.2ms* per document.

> Blockquote 68: "Performance is a feature." The codepath processed 82 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #82

### Unordered features

- First item with `inline code` and a [link](http://x.test/68)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 14% coverage
    - Deeply nested leaf node 82
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/68.png)

### Ordered steps

1. Allocate the arena (82 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 68)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 14% target on doc 82
- [ ] Publish the results

### Benchmark table 68

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 587 | 5.1 | yes |
| league GFM | 69 | 85 | yes |
| tempest | 58 | 66 | partial |

Here is a fenced code block in `php`:

```php
$html = (new MarkdownFight\Parser())->render($input); // doc 68
assert(strlen($html) > 82);
```

A final paragraph with an autolink <https://autolink.test/68> and a footnote-ish aside. Inline `code spans` survive 82 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 69: the streaming corpus

This is paragraph **number 69** describing a *streaming* corpus. It runs in `O(n)` time and handles [external links](https://example.com/page/75) alongside ~~deprecated~~ APIs. We measured a **76% speedup** over the baseline implementation, with a tail latency of *92.8ms* per document.

> Blockquote 69: "Performance is a feature." The corpus processed 75 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #75

### Unordered features

- First item with `inline code` and a [link](http://x.test/69)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the corpus
  - Another nested point at 76% coverage
    - Deeply nested leaf node 75
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/69.png)

### Ordered steps

1. Allocate the arena (75 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<corpus>` tags
4. Free the arena in one shot

### Task list (run 69)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 76% target on doc 75
- [ ] Publish the results

### Benchmark table 69

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 767 | 2.3 | yes |
| league GFM | 55 | 85 | yes |
| tempest | 30 | 89 | partial |

Here is a fenced code block in `json`:

```json
{"doc": 69, "speedup": 76, "latency_ms": 92.8}
```

A final paragraph with an autolink <https://autolink.test/69> and a footnote-ish aside. Inline `code spans` survive 75 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 70: the compiled tokenizer

This is paragraph **number 70** describing a *compiled* tokenizer. It runs in `O(n)` time and handles [external links](https://example.com/page/9) alongside ~~deprecated~~ APIs. We measured a **11% speedup** over the baseline implementation, with a tail latency of *38ms* per document.

> Blockquote 70: "Performance is a feature." The tokenizer processed 9 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #9

### Unordered features

- First item with `inline code` and a [link](http://x.test/70)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the tokenizer
  - Another nested point at 11% coverage
    - Deeply nested leaf node 9
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/70.png)

### Ordered steps

1. Allocate the arena (9 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<tokenizer>` tags
4. Free the arena in one shot

### Task list (run 70)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 11% target on doc 9
- [ ] Publish the results

### Benchmark table 70

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 595 | 5.2 | yes |
| league GFM | 47 | 69 | yes |
| tempest | 28 | 102 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 70 */
if (!out) return -1; /* 11% of runs */
```

A final paragraph with an autolink <https://autolink.test/70> and a footnote-ish aside. Inline `code spans` survive 9 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 71: the blazing arena

This is paragraph **number 71** describing a *blazing* arena. It runs in `O(n)` time and handles [external links](https://example.com/page/13) alongside ~~deprecated~~ APIs. We measured a **51% speedup** over the baseline implementation, with a tail latency of *20.3ms* per document.

> Blockquote 71: "Performance is a feature." The arena processed 13 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #13

### Unordered features

- First item with `inline code` and a [link](http://x.test/71)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the arena
  - Another nested point at 51% coverage
    - Deeply nested leaf node 13
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/71.png)

### Ordered steps

1. Allocate the arena (13 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<arena>` tags
4. Free the arena in one shot

### Task list (run 71)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 51% target on doc 13
- [ ] Publish the results

### Benchmark table 71

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 485 | 5.9 | yes |
| league GFM | 80 | 51 | yes |
| tempest | 58 | 101 | partial |

Here is a fenced code block in `c`:

```c
char *out = md2html(input, len, &out_len, flags, rflags); /* 71 */
if (!out) return -1; /* 51% of runs */
```

A final paragraph with an autolink <https://autolink.test/71> and a footnote-ish aside. Inline `code spans` survive 13 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 72: the compiled renderer

This is paragraph **number 72** describing a *compiled* renderer. It runs in `O(n)` time and handles [external links](https://example.com/page/54) alongside ~~deprecated~~ APIs. We measured a **36% speedup** over the baseline implementation, with a tail latency of *61.4ms* per document.

> Blockquote 72: "Performance is a feature." The renderer processed 54 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #54

### Unordered features

- First item with `inline code` and a [link](http://x.test/72)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the renderer
  - Another nested point at 36% coverage
    - Deeply nested leaf node 54
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/72.png)

### Ordered steps

1. Allocate the arena (54 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<renderer>` tags
4. Free the arena in one shot

### Task list (run 72)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 36% target on doc 54
- [ ] Publish the results

### Benchmark table 72

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 615 | 3.3 | yes |
| league GFM | 50 | 54 | yes |
| tempest | 25 | 96 | partial |

Here is a fenced code block in `rust`:

```rust
# block 72: compiled renderer
run --threads 54 --target 36
```

A final paragraph with an autolink <https://autolink.test/72> and a footnote-ish aside. Inline `code spans` survive 54 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 73: the zero-copy arena

This is paragraph **number 73** describing a *zero-copy* arena. It runs in `O(n)` time and handles [external links](https://example.com/page/74) alongside ~~deprecated~~ APIs. We measured a **98% speedup** over the baseline implementation, with a tail latency of *33.7ms* per document.

> Blockquote 73: "Performance is a feature." The arena processed 74 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #74

### Unordered features

- First item with `inline code` and a [link](http://x.test/73)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the arena
  - Another nested point at 98% coverage
    - Deeply nested leaf node 74
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/73.png)

### Ordered steps

1. Allocate the arena (74 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<arena>` tags
4. Free the arena in one shot

### Task list (run 73)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 98% target on doc 74
- [ ] Publish the results

### Benchmark table 73

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 549 | 6 | yes |
| league GFM | 58 | 51 | yes |
| tempest | 63 | 73 | partial |

Here is a fenced code block in `diff`:

```diff
# block 73: zero-copy arena
run --threads 74 --target 98
```

A final paragraph with an autolink <https://autolink.test/73> and a footnote-ish aside. Inline `code spans` survive 74 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 74: the compiled parser

This is paragraph **number 74** describing a *compiled* parser. It runs in `O(n)` time and handles [external links](https://example.com/page/18) alongside ~~deprecated~~ APIs. We measured a **69% speedup** over the baseline implementation, with a tail latency of *62.5ms* per document.

> Blockquote 74: "Performance is a feature." The parser processed 18 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #18

### Unordered features

- First item with `inline code` and a [link](http://x.test/74)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the parser
  - Another nested point at 69% coverage
    - Deeply nested leaf node 18
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/74.png)

### Ordered steps

1. Allocate the arena (18 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<parser>` tags
4. Free the arena in one shot

### Task list (run 74)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 69% target on doc 18
- [ ] Publish the results

### Benchmark table 74

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 475 | 3.4 | yes |
| league GFM | 70 | 69 | yes |
| tempest | 50 | 93 | partial |

Here is a fenced code block in `rust`:

```rust
# block 74: compiled parser
run --threads 18 --target 69
```

A final paragraph with an autolink <https://autolink.test/74> and a footnote-ish aside. Inline `code spans` survive 18 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---
## Section 75: the zero-copy codepath

This is paragraph **number 75** describing a *zero-copy* codepath. It runs in `O(n)` time and handles [external links](https://example.com/page/65) alongside ~~deprecated~~ APIs. We measured a **70% speedup** over the baseline implementation, with a tail latency of *53.3ms* per document.

> Blockquote 75: "Performance is a feature." The codepath processed 65 thousand nodes without a single heap allocation in the hot loop.
>
> — anonymous benchmark, run #65

### Unordered features

- First item with `inline code` and a [link](http://x.test/75)
- Second item is **bold** and *italic* combined into ***both***
- Third item with nested detail:
  - Nested point about the codepath
  - Another nested point at 70% coverage
    - Deeply nested leaf node 65
- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/75.png)

### Ordered steps

1. Allocate the arena (65 KB)
2. Tokenize the input stream
3. Render to HTML
   1. Escape entities
   2. Emit `<codepath>` tags
4. Free the arena in one shot

### Task list (run 75)

- [x] Compile the shared library
- [x] Wire up FFI bindings
- [ ] Beat the 70% target on doc 65
- [ ] Publish the results

### Benchmark table 75

| Engine | Throughput (MB/s) | Memory (MB) | Correct |
|:-------|------------------:|:-----------:|:-------:|
| md4c (FFI) | 867 | 2.2 | yes |
| league GFM | 67 | 48 | yes |
| tempest | 46 | 73 | partial |

Here is a fenced code block in `bash`:

```bash
# block 75: zero-copy codepath
run --threads 65 --target 70
```

A final paragraph with an autolink <https://autolink.test/75> and a footnote-ish aside. Inline `code spans` survive 65 round-trips. Some &amp; entity &copy; handling and a backslash escape \* here.

---

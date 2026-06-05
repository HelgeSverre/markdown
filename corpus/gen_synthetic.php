<?php
/**
 * Synthetic markdown corpus generator.
 *
 * Emits a feature-rich ~2KB markdown block repeatedly until a target byte size
 * is reached. Each block varies its numbers/words so the output is NOT
 * byte-identical repetition (defeats naive caching and is more realistic for
 * a parser benchmark). Every GFM feature the harness cares about appears:
 * headings, paragraphs with bold, italic, inline code and links,
 * bullet + ordered + nested lists, GFM tables, fenced code blocks,
 * blockquotes, ~~strikethrough~~, task lists, thematic breaks (hr), images.
 *
 * Usage: php gen_synthetic.php <target_bytes> <out_file>
 */

if ($argc < 3) {
    fwrite(STDERR, "usage: php gen_synthetic.php <target_bytes> <out_file>\n");
    exit(1);
}

$target = (int) $argv[1];
$out    = $argv[2];

// Deterministic but varied: seed so reruns are reproducible.
mt_srand(0xC0FFEE);

$adjectives = ['blazing', 'native', 'zero-copy', 'compiled', 'streaming', 'lock-free', 'vectorized', 'cache-warm', 'allocator-aware', 'branchless'];
$nouns      = ['parser', 'renderer', 'tokenizer', 'buffer', 'arena', 'pipeline', 'codepath', 'dispatcher', 'shim', 'corpus'];
$langs      = ['php', 'c', 'rust', 'go', 'json', 'bash', 'sql', 'diff'];

function pick(array $a): string { return $a[mt_rand(0, count($a) - 1)]; }

/** Build one ~2KB feature-rich block, parameterised by an index $i. */
function block(int $i): string {
    global $adjectives, $nouns, $langs;
    $adj  = pick($adjectives);
    $noun = pick($nouns);
    $lang = pick($langs);
    $n    = mt_rand(2, 97);
    $pct  = mt_rand(11, 99);
    $ms   = mt_rand(1, 999) / 10;

    $b = [];
    $b[] = "## Section {$i}: the {$adj} {$noun}";
    $b[] = "";
    $b[] = "This is paragraph **number {$i}** describing a *{$adj}* {$noun}. "
         . "It runs in `O(n)` time and handles [external links](https://example.com/page/{$n}) "
         . "alongside ~~deprecated~~ APIs. We measured a **{$pct}% speedup** over the "
         . "baseline implementation, with a tail latency of *{$ms}ms* per document.";
    $b[] = "";
    $b[] = "> Blockquote {$i}: \"Performance is a feature.\" The {$noun} processed "
         . "{$n} thousand nodes without a single heap allocation in the hot loop.";
    $b[] = ">";
    $b[] = "> — anonymous benchmark, run #{$n}";
    $b[] = "";
    $b[] = "### Unordered features";
    $b[] = "";
    $b[] = "- First item with `inline code` and a [link](http://x.test/{$i})";
    $b[] = "- Second item is **bold** and *italic* combined into ***both***";
    $b[] = "- Third item with nested detail:";
    $b[] = "  - Nested point about the {$noun}";
    $b[] = "  - Another nested point at {$pct}% coverage";
    $b[] = "    - Deeply nested leaf node {$n}";
    $b[] = "- Fourth item with ~~struck text~~ and an ![inline image](https://img.test/{$i}.png)";
    $b[] = "";
    $b[] = "### Ordered steps";
    $b[] = "";
    $b[] = "1. Allocate the arena ({$n} KB)";
    $b[] = "2. Tokenize the input stream";
    $b[] = "3. Render to HTML";
    $b[] = "   1. Escape entities";
    $b[] = "   2. Emit `<{$noun}>` tags";
    $b[] = "4. Free the arena in one shot";
    $b[] = "";
    $b[] = "### Task list (run {$i})";
    $b[] = "";
    $b[] = "- [x] Compile the shared library";
    $b[] = "- [x] Wire up FFI bindings";
    $b[] = "- [ ] Beat the {$pct}% target on doc {$n}";
    $b[] = "- [ ] Publish the results";
    $b[] = "";
    $b[] = "### Benchmark table {$i}";
    $b[] = "";
    $b[] = "| Engine | Throughput (MB/s) | Memory (MB) | Correct |";
    $b[] = "|:-------|------------------:|:-----------:|:-------:|";
    $b[] = "| md4c (FFI) | " . mt_rand(400, 900) . " | " . (mt_rand(20, 60) / 10) . " | yes |";
    $b[] = "| league GFM | " . mt_rand(20, 80) . " | " . mt_rand(30, 90) . " | yes |";
    $b[] = "| tempest | " . mt_rand(15, 70) . " | " . mt_rand(40, 110) . " | partial |";
    $b[] = "";
    $b[] = "Here is a fenced code block in `{$lang}`:";
    $b[] = "";
    $b[] = "```{$lang}";
    if ($lang === 'php') {
        $b[] = "\$html = (new MarkdownFight\\Parser())->render(\$input); // doc {$i}";
        $b[] = "assert(strlen(\$html) > {$n});";
    } elseif ($lang === 'c') {
        $b[] = "char *out = md2html(input, len, &out_len, flags, rflags); /* {$i} */";
        $b[] = "if (!out) return -1; /* {$pct}% of runs */";
    } elseif ($lang === 'json') {
        $b[] = "{\"doc\": {$i}, \"speedup\": {$pct}, \"latency_ms\": {$ms}}";
    } else {
        $b[] = "# block {$i}: {$adj} {$noun}";
        $b[] = "run --threads {$n} --target {$pct}";
    }
    $b[] = "```";
    $b[] = "";
    $b[] = "A final paragraph with an autolink <https://autolink.test/{$i}> and a "
         . "footnote-ish aside. Inline `code spans` survive {$n} round-trips. "
         . "Some &amp; entity &copy; handling and a backslash escape \\* here.";
    $b[] = "";
    $b[] = "---";
    $b[] = "";

    return implode("\n", $b);
}

$fh = fopen($out, 'wb');
if (!$fh) { fwrite(STDERR, "cannot open {$out}\n"); exit(1); }

// Document preamble (counts toward size).
$header = "# Synthetic Markdown Document ({$target} byte target)\n\n"
        . "Generated feature-rich markdown for parser benchmarking. "
        . "Contains headings, emphasis, lists, tables, code, blockquotes, "
        . "task lists, strikethrough, images and thematic breaks.\n\n---\n\n";
fwrite($fh, $header);
$written = strlen($header);

$i = 1;
while ($written < $target) {
    $chunk = block($i);
    fwrite($fh, $chunk);
    $written += strlen($chunk);
    $i++;
}
fclose($fh);

clearstatcache();
$actual = filesize($out);
fwrite(STDOUT, "{$out}: {$actual} bytes ({$i} blocks)\n");

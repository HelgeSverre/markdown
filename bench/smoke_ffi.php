<?php

declare(strict_types=1);

/**
 * Smoke + leak + batch test for the FFI parser stack.
 *
 * Exit code 0 = all assertions passed. Any failure exits non-zero.
 * Designed to run BOTH plain and under opcache.preload so the FFI::scope()
 * fast path is exercised. Run with `-d error_reporting=E_ALL` to surface any
 * deprecation notices (there must be none).
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use HelgeSverre\Markdown\FfiBatchParser;
use HelgeSverre\Markdown\FfiParser;

function ok(string $label): void
{
    echo "  ok: {$label}\n";
}

function fail(string $label): never
{
    fwrite(STDERR, "  FAIL: {$label}\n");
    exit(1);
}

/* Did we land on the preloaded scope or the cdef fallback? Report it. */
$scopeAvailable = false;
try {
    FFI::scope('MD4C');
    $scopeAvailable = true;
} catch (Throwable) {
    $scopeAvailable = false;
}
echo 'FFI::scope("MD4C") available: ' . ($scopeAvailable ? 'YES (preloaded fast path)' : 'no (cdef fallback)') . "\n";

/* ----- [1/3] basic correctness ----- */
echo "[1/3] FfiParser correctness\n";
$p = new FfiParser();

$p->name() === 'helgesverre/markdown (FFI→md4c)' || fail('name()');
ok('name() = ' . $p->name());

$html = $p->toHtml("# Hi\n\n- a\n- b");
str_contains($html, '<h1>Hi</h1>') || fail('expected <h1>Hi</h1>, got: ' . $html);
ok('<h1>Hi</h1>');
str_contains($html, '<li>a</li>') || fail('expected <li>a</li>, got: ' . $html);
ok('<li>a</li>');
str_contains($html, '<li>b</li>') || fail('expected <li>b</li>');
ok('<li>b</li>');

/* GFM extensions: tables + strikethrough + tasklists must be on. */
$gfm = $p->toHtml("| a | b |\n|---|---|\n| 1 | 2 |\n\n~~gone~~\n\n- [x] done");
str_contains($gfm, '<table>') || fail('GFM tables off');
ok('GFM <table>');
str_contains($gfm, '<del>gone</del>') || fail('GFM strikethrough off (expected <del>)');
ok('GFM <del>gone</del>');
str_contains($gfm, 'type="checkbox"') || fail('GFM tasklists off');
ok('GFM tasklist checkbox');

/* CommonMark correctness: a list interrupts a paragraph (tempest fails this). */
$interrupt = $p->toHtml("A list:\n- one\n- two");
str_contains($interrupt, '<li>one</li>') && str_contains($interrupt, '<li>two</li>') || fail('list-interrupts-paragraph failed: ' . $interrupt);
ok('list interrupts paragraph (CommonMark-correct)');

/* Empty input must not crash and must return a freeable buffer. */
$p->toHtml('') === '' || fail('empty input should produce empty string');
ok('empty input -> empty string');

/* ----- [2/3] 50k-iteration leak sanity ----- */
echo "[2/3] 50,000-iteration leak loop\n";
$doc =
    "# Heading\n\nSome **bold** and *italic* text with `code` and a [link](https://example.com).\n\n"
    . "- item one\n- item two\n- item three\n\n"
    . "| col a | col b |\n|-------|-------|\n| 1 | 2 |\n| 3 | 4 |\n\n"
    . "> a blockquote\n\n~~struck~~ and a www.example.com autolink.\n";

/* Warm up so allocator arenas settle before we snapshot. */
for ($i = 0; $i < 1000; $i++) {
    $p->toHtml($doc);
}
$startReal = memory_get_usage(true);
$startUsed = memory_get_usage(false);

$iters = 50_000;
for ($i = 0; $i < $iters; $i++) {
    $out = $p->toHtml($doc);
}
unset($out);

$endReal = memory_get_usage(true);
$endUsed = memory_get_usage(false);

printf("  iterations:       %d\n", $iters);
printf("  memory_get_usage(true):  start=%d  end=%d  delta=%+d bytes\n", $startReal, $endReal, $endReal - $startReal);
printf("  memory_get_usage(false): start=%d  end=%d  delta=%+d bytes\n", $startUsed, $endUsed, $endUsed - $startUsed);

/* Real (OS-allocated) memory must not grow across the loop. */
if ($endReal > $startReal) {
    fail(sprintf('memory grew by %d bytes over %d iterations -> leak', $endReal - $startReal, $iters));
}
ok('no real-memory growth over 50k iterations (no leak)');

/* ----- [3/3] batch parity ----- */
echo "[3/3] FfiBatchParser parity (100 docs)\n";
$bp = new FfiBatchParser();
echo '  batch native symbol present: ' . (new ReflectionProperty($bp, 'hasBatch')->getValue($bp) ? 'YES' : 'no (PHP fallback)') . "\n";

$docs = [];
for ($i = 0; $i < 100; $i++) {
    $docs[] = "# Doc {$i}\n\n- a{$i}\n- b{$i}\n\n**bold {$i}** and `code{$i}` ~~old{$i}~~\n\n| x | y |\n|---|---|\n| {$i} | " . ($i * 2) . " |\n";
}

$batchOut = $bp->toHtmlBatch($docs);
count($batchOut) === 100 || fail('batch returned ' . count($batchOut) . ' results, expected 100');
ok('batch returned 100 results');

$mismatch = 0;
foreach ($docs as $i => $d) {
    $seq = $p->toHtml($d);
    if ($batchOut[$i] !== $seq) {
        $mismatch++;
        if ($mismatch <= 3) {
            fwrite(STDERR, "  MISMATCH doc {$i}:\n   batch: " . var_export($batchOut[$i], true) . "\n   seq:   " . var_export($seq, true) . "\n");
        }
    }
}
$mismatch === 0 || fail("{$mismatch} batch outputs differ from sequential");
ok('all 100 batch outputs match sequential per-doc results');

/* Edge cases for batch. */
$bp->toHtmlBatch([]) === [] || fail('empty batch should return []');
ok('empty batch -> []');
$one = $bp->toHtmlBatch(['# Solo']);
count($one) === 1 && str_contains($one[0], '<h1>Solo</h1>') || fail('single-doc batch');
ok('single-doc batch');

echo "\nALL SMOKE TESTS PASSED\n";
exit(0);

<?php

declare(strict_types=1);

/**
 * Render a whole crowd of documents at once across an OS thread pool, in a
 * single FFI call — then compare against rendering them one-by-one.
 *
 *   php examples/03-batch-multicore.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use HelgeSverre\Markdown\BatchParser;
use HelgeSverre\Markdown\Parser;

$root = dirname(__DIR__);

// Build a realistic batch from the synthetic corpus, padded out to 500 docs.
$files = glob($root . '/corpus/synthetic/*.md') ?: [];
if ($files === []) {
    fwrite(STDERR, "No corpus found. Generate it first (see corpus/gen_synthetic.php).\n");
    exit(1);
}

// The realistic batch case is rendering MANY SMALL documents (think 500 forum
// comments or chat messages), so seed from the small corpus tiers only.
$seed = [];
foreach ($files as $f) {
    if (filesize($f) > (32 * 1024)) {
        continue;
    }

    $seed[] = (string) file_get_contents($f);
}
if ($seed === []) {
    $seed[] = (string) file_get_contents($files[0]);
}

$docs = [];
while (count($docs) < 500) {
    $docs[] = $seed[count($docs) % count($seed)];
}
$totalBytes = array_sum(array_map('strlen', $docs));

// Sequential: one FFI call per document.
$single = new Parser();
$t = hrtime(true);
foreach ($docs as $d) {
    $single->toHtml($d);
}
$seqMs = (hrtime(true) - $t) / 1e6;

// Batch: pack all docs, one FFI call, pthread pool fans them across cores.
$batch = new BatchParser();
$t = hrtime(true);
$out = $batch->toHtmlBatch($docs);
$batchMs = (hrtime(true) - $t) / 1e6;

printf("Batch of %d documents, %.1f MB total markdown\n\n", count($docs), $totalBytes / 1e6);
printf("  %-26s %9.2f ms   %7.0f MB/s\n", 'sequential (per-doc FFI)', $seqMs, ($totalBytes / 1e6) / ($seqMs / 1000));
printf("  %-26s %9.2f ms   %7.0f MB/s   %.2fx\n", 'batch (pthread pool)', $batchMs, ($totalBytes / 1e6) / ($batchMs / 1000), $seqMs / $batchMs);
printf("\nProduced %d HTML docs, %.1f MB of output.\n", count($out), array_sum(array_map('strlen', $out)) / 1e6);

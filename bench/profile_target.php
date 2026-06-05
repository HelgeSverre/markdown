<?php

declare(strict_types=1);

/**
 * bench/profile_target.php — a single, long-running hot loop for NATIVE profilers.
 *
 * Almost all of this library's time is spent inside md4c, behind one FFI call
 * per document. PHP-level profilers can't see in there; native profilers
 * (samply, perf, Valgrind/DHAT, heaptrack) attach to the PHP process and sample
 * the C stack. This script is their target: construct once, read the corpus
 * once, then hammer the hot path in a tight loop so everything you sample is the
 * render itself — not autoload, FFI binding, or file I/O.
 *
 *   php -d opcache.preload= -d ffi.enable=1 bench/profile_target.php [corpus] [iters] [mode]
 *
 *     corpus  path under corpus/ (default: commonmark-spec.md), or an absolute path
 *     iters   loop count (default 2000). LARGE under samply/perf (more samples);
 *             SMALL (e.g. 5) under Valgrind/DHAT, which run ~50x slower.
 *     mode    html (default) | parse | batch
 *
 * IMPORTANT: run with `-d opcache.preload=` (empty) so FFI::scope('MD4C') is NOT
 * bound and the FFI binding falls back to FFI::cdef + Library::path(), which honors the
 * MARKDOWN_FFI_LIB override that points at the libmd4cshim.prof.* profiling
 * build. With the preload active, the optimized shipped lib is loaded instead
 * and your symbols/frames will be poor. The banner below warns if that happens.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use HelgeSverre\Markdown\BatchParser;
use HelgeSverre\Markdown\Ffi\Library;
use HelgeSverre\Markdown\Parser;

$corpus = $argv[1] ?? 'commonmark-spec.md';
$iters = max(1, (int) ($argv[2] ?? 2000));
$mode = $argv[3] ?? 'html';

$path = str_starts_with($corpus, '/') ? $corpus : dirname(__DIR__) . '/corpus/' . $corpus;
if (! is_file($path)) {
    fwrite(STDERR, "corpus not found: {$path}\n");
    exit(1);
}
$doc = (string) file_get_contents($path);

// Warn loudly if the preloaded scope is shadowing the profiling lib.
$scoped = true;
try {
    FFI::scope('MD4C');
} catch (Throwable) {
    $scoped = false;
}

$lib = Library::path();
fwrite(STDERR, sprintf(
    "profile_target: mode=%s iters=%d corpus=%s (%d bytes)\n  lib=%s\n  FFI::scope bound=%s%s\n",
    $mode,
    $iters,
    $corpus,
    strlen($doc),
    $lib,
    $scoped ? 'yes' : 'no',
    $scoped ? '  <-- WARNING: preload is shadowing MARKDOWN_FFI_LIB; re-run with -d opcache.preload=' : '',
));

$parser = new Parser();
$batch = $mode === 'batch' ? new BatchParser() : null;

// Batch mode fans a handful of copies through the pthread pool each iteration.
$docs = $mode === 'batch' ? array_fill(0, 8, $doc) : [];

// A few warmup reps so the JIT has compiled the glue before the timed loop.
for ($i = 0; $i < 3; $i++) {
    match ($mode) {
        'parse' => $parser->parse($doc),
        'batch' => $batch->toHtmlBatch($docs),
        default => $parser->toHtml($doc),
    };
}

$bytes = 0;
$t0 = hrtime(true);
for ($i = 0; $i < $iters; $i++) {
    switch ($mode) {
        case 'parse':
            $bytes += strlen($parser->parse($doc)->html);
            break;
        case 'batch':
            foreach ($batch->toHtmlBatch($docs) as $h) {
                $bytes += strlen($h);
            }

            break;
        default:
            $bytes += strlen($parser->toHtml($doc));
    }
}
$ns = hrtime(true) - $t0;

$mbps = $bytes > 0 ? ($bytes / ($ns / 1e9)) / 1e6 : 0.0;
fwrite(STDERR, sprintf(
    "done: %d iters in %.3f s  |  %.1f µs/op  |  %.0f MB/s out\n",
    $iters,
    $ns / 1e9,
    ($ns / $iters) / 1000,
    $mbps,
));

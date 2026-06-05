<?php
/**
 * bench/once.php — single (parser, corpus-file) measurement, one process.
 *
 * Usage:
 *   php bench/once.php <parserId> <corpusFilePath>
 *
 * For the 'fight' parser the orchestrator launches php with the warm-FFI
 * flags (opcache.enable_cli, opcache.preload=bench/preload.php, ffi.enable).
 * For every parser it keeps opcache.enable_cli=1 for fairness.
 *
 * What it does:
 *   1. require vendor/autoload
 *   2. load the corpus file into memory (read once, outside timing)
 *   3. build the parser registry (instances constructed ONCE)
 *   4. warm up (>= 5 iterations) so JIT/opcache/branch-predictors settle
 *   5. time a fixed WALL-CLOCK budget (~1.0s, min 20 iterations) with
 *      hrtime(true) (monotonic nanoseconds)
 *   6. print ONE line of JSON to stdout with the full measurement
 *
 * Running each combo in its OWN process gives a clean per-parser
 * memory_get_peak_usage(true): no other parser's allocations contaminate it.
 *
 * ----------------------------------------------------------------------------
 * MEMORY CAVEAT (honest disclosure, do not delete):
 *   Our FFI 'fight' parser allocates its rendered HTML on the C heap (md4c
 *   malloc), which PHP's memory_get_peak_usage(true) does NOT count. PHP only
 *   sees the FFI::string() copy into a PHP string. So 'fight's peak_mb is
 *   real-RSS-favorable: it undercounts the transient C-heap output buffer
 *   (which is freed right after each parse). Pure-PHP parsers (tempest,
 *   league) keep ALL their work on the PHP/Zend heap, so their peak_mb is a
 *   complete accounting. Compare with that asymmetry in mind.
 * ----------------------------------------------------------------------------
 */

declare(strict_types=1);

/** Emit a JSON error line and exit non-fatally (orchestrator records it). */
function bench_fail(string $parserId, string $corpusPath, string $message): never
{
    $label = $corpusPath !== '' ? basename($corpusPath) : '(none)';
    fwrite(STDOUT, json_encode([
        'parserId' => $parserId,
        'corpus'   => $label,
        'corpus_path' => $corpusPath,
        'error'    => $message,
    ], JSON_UNESCAPED_SLASHES) . "\n");
    exit(0); // exit 0: the orchestrator parses stdout; a row-level error is not fatal.
}

$parserId   = $argv[1] ?? '';
$corpusPath = $argv[2] ?? '';

if ($parserId === '' || $corpusPath === '') {
    bench_fail($parserId, $corpusPath, 'usage: php bench/once.php <parserId> <corpusFilePath>');
}

$root = dirname(__DIR__);

$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    bench_fail($parserId, $corpusPath, "vendor/autoload.php not found at {$autoload}");
}
require $autoload;

if (!is_file($corpusPath)) {
    bench_fail($parserId, $corpusPath, "corpus file not found: {$corpusPath}");
}
$md = file_get_contents($corpusPath);
if ($md === false) {
    bench_fail($parserId, $corpusPath, "could not read corpus file: {$corpusPath}");
}
$bytes = strlen($md);
$label = basename($corpusPath);

// Build the registry (instances constructed ONCE, here, outside timing).
try {
    /** @var array<string, callable(string):string> $parsers */
    $parsers = require $root . '/bench/parsers.php';
} catch (\Throwable $e) {
    bench_fail($parserId, $corpusPath, 'parser registry build failed: ' . $e->getMessage());
}

if (!isset($parsers[$parserId])) {
    bench_fail($parserId, $corpusPath, "unknown parserId '{$parserId}'; known: " . implode(',', array_keys($parsers)));
}
$parse = $parsers[$parserId];

// ---- Warm up ---------------------------------------------------------------
// >= 5 iterations: let opcache/JIT compile hot paths and the FFI trampoline
// settle. We also grab one output here to prove the parser actually rendered.
// Adaptive: if a single parse is already expensive (huge corpus × slow parser)
// we stop warming early after $warmupCeilingNs of cumulative warmup work — we
// still always do at least 2 warmups (one to render, one to confirm a hot path)
// so the timed numbers stay representative without a runaway child.
$warmupIters       = 5;
$warmupCeilingNs   = 2_000_000_000; // ~2s total spent on warmup, at most
$outBytes = 0;
try {
    $firstHtml = '';
    $singleParseNs = 0;
    $warmStart = hrtime(true);
    for ($i = 0; $i < $warmupIters; $i++) {
        $w0 = hrtime(true);
        $firstHtml = $parse($md);
        $singleParseNs = hrtime(true) - $w0; // cost of the last (hottest) warmup parse
        if ($i >= 1 && (hrtime(true) - $warmStart) >= $warmupCeilingNs) {
            break; // already warm enough; don't let huge inputs run away
        }
    }
    $outBytes = strlen($firstHtml);
    if ($outBytes === 0 && $bytes > 0) {
        bench_fail($parserId, $corpusPath, 'parser produced empty output for non-empty input');
    }
    unset($firstHtml);
} catch (\Throwable $e) {
    bench_fail($parserId, $corpusPath, 'warmup parse threw: ' . $e->getMessage());
}

// ---- Timed run: fixed wall-clock budget ------------------------------------
// Run as many iterations as fit in ~1.0s, but never fewer than 20 — UNLESS a
// single parse is already slow (huge corpus × slow parser), in which case we
// cap the forced minimum so the child cannot blow past the orchestrator's
// timeout. We use the warmup-measured single-parse cost to bound it: never
// force more than ~$minWorkNs of guaranteed work.
$budgetNs   = 1_000_000_000; // 1.0 second target
$minWorkNs  = 3_000_000_000; // hard ceiling on the "forced minimum" phase (~3s)
$minIters   = 20;
if ($singleParseNs > 0) {
    // If 20 parses would exceed the ceiling, reduce the forced minimum
    // (but always do at least 3 timed iterations for a usable mean).
    $affordable = (int) max(3, intdiv($minWorkNs, $singleParseNs));
    $minIters   = min($minIters, $affordable);
}

$iters = 0;
$start = hrtime(true);
$elapsed = 0;
try {
    // Phase 1: honour the minimum iteration count regardless of clock.
    for (; $iters < $minIters; $iters++) {
        $parse($md);
    }
    $elapsed = hrtime(true) - $start;
    // Phase 2: keep going until the wall-clock budget is spent.
    while ($elapsed < $budgetNs) {
        $parse($md);
        $iters++;
        $elapsed = hrtime(true) - $start;
    }
} catch (\Throwable $e) {
    bench_fail($parserId, $corpusPath, 'timed parse threw: ' . $e->getMessage());
}
if ($elapsed <= 0) {
    $elapsed = hrtime(true) - $start;
}

$elapsedSec = $elapsed / 1e9;
$opsPerSec  = $elapsedSec > 0 ? $iters / $elapsedSec : 0.0;
$mbPerSec   = $elapsedSec > 0 ? ($bytes * $iters) / $elapsedSec / 1e6 : 0.0;
$meanMs     = $iters > 0 ? ($elapsed / $iters) / 1e6 : 0.0;
$peakMb     = memory_get_peak_usage(true) / 1048576;

$row = [
    'parserId'    => $parserId,
    'corpus'      => $label,
    'corpus_path' => $corpusPath,
    'bytes'       => $bytes,
    'iters'       => $iters,
    'elapsed_s'   => round($elapsedSec, 6),
    'ops_per_sec' => round($opsPerSec, 3),
    'mb_per_sec'  => round($mbPerSec, 4),
    'mean_ms'     => round($meanMs, 6),
    'out_bytes'   => $outBytes,
    'peak_mb'     => round($peakMb, 4),
    'php'         => PHP_VERSION,
    'jit'         => function_exists('opcache_get_status'),
    'error'       => null,
];

fwrite(STDOUT, json_encode($row, JSON_UNESCAPED_SLASHES) . "\n");
exit(0);

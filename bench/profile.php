<?php

declare(strict_types=1);

/**
 * bench/profile.php — one-command native profiling + report (macOS).
 *
 * `composer profile` runs this. It:
 *   1. ensures the symbol-rich profiling lib exists (builds it if missing),
 *   2. does a short timed pass to capture throughput (µs/op, MB/s),
 *   3. drives bench/profile_target.php's hot loop under macOS's built-in
 *      `sample` profiler, which symbolicates md4c/shim frames inline,
 *   4. parses the self-time table and writes results/PROFILE.md (+ prints a
 *      summary), mirroring how `composer bench` writes results/RESULTS.md.
 *
 *   php bench/profile.php [corpus] [mode] [seconds]
 *     corpus   file under corpus/ (default commonmark-spec.md) or absolute path
 *     mode     html (default) | parse | batch
 *     seconds  sampling window (default 5)
 *
 * macOS only: `sample` is a Darwin tool. On Linux use perf/DHAT/heaptrack —
 * see docs/profiling.md. For an interactive flamegraph: `composer profile:flamegraph`.
 */

$root = dirname(__DIR__);
$corpus = $argv[1] ?? 'commonmark-spec.md';
$mode = $argv[2] ?? 'html';
$seconds = max(1, (int) ($argv[3] ?? 5));

$die = static function (string $msg): never {
    fwrite(STDERR, "profile: {$msg}\n");
    exit(1);
};

// --- 0. platform + tooling -------------------------------------------------
if (PHP_OS_FAMILY !== 'Darwin') {
    $die("`sample` is macOS-only. On Linux use perf/DHAT/heaptrack — see docs/profiling.md.");
}
exec('command -v sample 2>/dev/null', $_o, $rc);
if ($rc !== 0) {
    $die('macOS `sample` not found on PATH (it normally ships at /usr/bin/sample).');
}

// --- 1. ensure the profiling lib (build on demand) -------------------------
$profLib = $root . '/native/libmd4cshim.prof.dylib';
if (! is_file($profLib)) {
    fwrite(STDERR, "profile: profiling lib missing — building it (composer profile:build)…\n");
    exec('bash ' . escapeshellarg($root . '/native/build.sh') . ' profile 2>&1', $_b, $rcb);
    if ($rcb !== 0 || ! is_file($profLib)) {
        $die('failed to build the profiling lib; run `composer profile:build` and inspect the output.');
    }
}

$corpusPath = str_starts_with($corpus, '/') ? $corpus : $root . '/corpus/' . $corpus;
is_file($corpusPath) || $die("corpus not found: {$corpusPath}");

// Child env: point FFI at the profiling lib (the empty opcache.preload in the
// command keeps FFI::scope unbound so the cdef fallback honors this override).
$childEnv = getenv();
$childEnv['MARKDOWN_FFI_LIB'] = $profLib;

$php = PHP_BINARY;
$target = $root . '/bench/profile_target.php';
$baseArgs = ['-d', 'opcache.preload=', '-d', 'ffi.enable=1', $target, $corpus];

// --- 2. short timed pass for throughput ------------------------------------
$timing = run_capture([$php, ...$baseArgs, '500', $mode], $root, $childEnv);
$usPerOp = preg_match('/([\d.]+)\s*µs\/op/u', $timing, $m) ? (float) $m[1] : null;
$mbps = preg_match('/([\d.]+)\s*MB\/s/u', $timing, $m) ? (float) $m[1] : null;
$bytes = preg_match('/\((\d+) bytes\)/', $timing, $m) ? (int) $m[1] : 0;
if (str_contains($timing, 'bound=yes')) {
    $die('FFI::scope is bound (opcache.preload active) — it is shadowing the profiling lib. This should not happen via this script; check your php.ini.');
}

// --- 3. sampling pass ------------------------------------------------------
$sampleOut = $root . '/results/profile.sample.txt';
fwrite(STDERR, "profile: sampling {$mode} on {$corpus} for {$seconds}s…\n");

// Launch the hot loop with a huge iteration count so it outlives the sampler;
// we terminate it once `sample` returns.
$descriptors = [['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']];
$proc = proc_open([$php, ...$baseArgs, '5000000', $mode], $descriptors, $pipes, $root, $childEnv);
is_resource($proc) || $die('could not launch the profile target.');
fclose($pipes[0]);

$pid = proc_get_status($proc)['pid'];
usleep(600_000); // let it warm up and reach steady state before sampling
exec(sprintf('sample %d %d -file %s 2>/dev/null', $pid, $seconds, escapeshellarg($sampleOut)));

proc_terminate($proc);
proc_close($proc);

is_file($sampleOut) || $die('sample produced no output.');

// --- 4. parse the self-time ("top of stack") table -------------------------
$rows = parse_sample_self_time(file_get_contents($sampleOut));
$rows === [] && $die('could not parse any self-time frames from the sample output.');

$onCpu = 0;
$idle = 0;
$byComponent = [];
foreach ($rows as $r) {
    if ($r['component'] === 'idle/wait') {
        $idle += $r['count'];

        continue;
    }

    $onCpu += $r['count'];
    $byComponent[$r['component']] = ($byComponent[$r['component']] ?? 0) + $r['count'];
}
arsort($byComponent);
$onCpu > 0 || $die('all samples were in idle/parked threads — try a longer window or a larger corpus.');

// --- 5. report -------------------------------------------------------------
$pct = static fn (int $n): string => sprintf('%.1f%%', 100 * $n / $onCpu);

$md = "# Profile — helgesverre/markdown (FFI→md4c)\n\n";
$md .= sprintf(
    "**Generated:** %s · **Host:** macOS %s, PHP %s\n",
    date('Y-m-d H:i'),
    php_uname('m'),
    PHP_VERSION,
);
$md .= sprintf(
    "**Corpus:** %s (%s bytes) · **Mode:** %s · **Sampler:** macOS `sample`, %ds @ 1ms\n",
    $corpus,
    number_format($bytes),
    $mode,
    $seconds,
);
if ($usPerOp !== null) {
    $md .= sprintf("**Throughput** (separate timed pass): %.1f µs/op · %.0f MB/s out\n", $usPerOp, $mbps ?? 0);
}

$md .= "\n> Native self-time. Parked/idle threads (PHP runtime workers waiting in the\n";
$md .= "> kernel — `__workq_kernreturn` etc.) are **excluded**: {$idle} idle samples vs {$onCpu} on-CPU.\n";
$md .= "> Percentages are share of on-CPU samples. This is leaf/self time, not inclusive.\n\n";

$md .= "## Where the time goes — by component\n\n";
$md .= "| Component | Self samples | % on-CPU |\n|---|--:|--:|\n";
foreach ($byComponent as $component => $n) {
    $md .= sprintf("| %s | %d | %s |\n", $component, $n, $pct($n));
}

$md .= "\n## Hottest functions\n\n";
$md .= "| # | Function | Component | Self | % on-CPU |\n|--:|---|---|--:|--:|\n";
$rank = 0;
foreach ($rows as $r) {
    if ($r['component'] === 'idle/wait') {
        continue;
    }

    $rank++;
    $md .= sprintf("| %d | `%s` | %s | %d | %s |\n", $rank, $r['symbol'], $r['component'], $r['count'], $pct($r['count']));
    if ($rank >= 25) {
        break;
    }
}

$md .= "\n---\n";
$md .= "_Raw sample: `results/profile.sample.txt`. ";
$md .= "Interactive flamegraph: `composer profile:flamegraph`. Recipes & Linux tools: `docs/profiling.md`._\n";

$reportPath = $root . '/results/PROFILE.md';
file_put_contents($reportPath, $md);

// --- 6. stdout summary -----------------------------------------------------
echo "\nProfile written to results/PROFILE.md\n";
if ($usPerOp !== null) {
    echo sprintf("  %s / %s · %.1f µs/op · %.0f MB/s · %d on-CPU samples (%d idle)\n\n", $corpus, $mode, $usPerOp, $mbps ?? 0, $onCpu, $idle);
}
echo "  Top frames (self time):\n";
$rank = 0;
foreach ($rows as $r) {
    if ($r['component'] === 'idle/wait') {
        continue;
    }

    $rank++;
    echo sprintf("    %2d. %-34s %-12s %s\n", $rank, $r['symbol'], $r['component'], $pct($r['count']));
    if ($rank >= 10) {
        break;
    }
}
echo "\n";

// ---------------------------------------------------------------------------

/** Run a command to completion, returning merged stdout+stderr. */
function run_capture(array $cmd, string $cwd, array $env): string
{
    $proc = proc_open($cmd, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, $cwd, $env);
    if (! is_resource($proc)) {
        return '';
    }

    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);

    return $out;
}

/**
 * Parse the "Sort by top of stack, same collapsed" section of `sample` output.
 * Each line: `        <symbol>  (in <lib>)        <count>`.
 *
 * @return list<array{symbol: string, lib: string, count: int, component: string}>
 */
function parse_sample_self_time(string $text): array
{
    $lines = explode("\n", $text);
    $rows = [];
    $inSection = false;
    foreach ($lines as $line) {
        if (str_contains($line, 'Sort by top of stack')) {
            $inSection = true;

            continue;
        }
        if (! $inSection) {
            continue;
        }
        if (trim($line) === '' || str_contains($line, 'Binary Images')) {
            break;
        }
        if (! preg_match('/^\s+(.+?)\s+\(in (.+?)\)\s+(\d+)\s*$/', $line, $m)) {
            continue;
        }

        $rows[] = [
            'symbol' => $m[1],
            'lib' => $m[2],
            'count' => (int) $m[3],
            'component' => classify_lib($m[2]),
        ];
    }

    usort($rows, static fn (array $a, array $b): int => $b['count'] <=> $a['count']);

    return $rows;
}

/** Bucket a Mach-O image name into a human component for the report. */
function classify_lib(string $lib): string
{
    $l = strtolower($lib);

    return match (true) {
        str_contains($l, 'libmd4cshim') => 'md4c+shim',
        str_contains($l, 'libsystem_kernel'), str_contains($l, 'libsystem_pthread') => 'idle/wait',
        str_contains($l, 'libsystem_malloc') => 'libc (alloc)',
        str_contains($l, 'libsystem_platform'), str_contains($l, 'libsystem_c') => 'libc (mem/str)',
        str_contains($l, 'php'), str_contains($l, 'herd') => 'php runtime',
        default => $lib,
    };
}

<?php
/**
 * bench/run.php — orchestrator.
 *
 * Reads corpus/manifest.json. For each corpus file × each parserId it spawns
 * bench/once.php in a SEPARATE php process (so each combo gets a clean
 * per-parser peak-memory reading). Collects the one-line JSON each child
 * prints, then writes:
 *   results/results.json  — array of every measurement
 *   results/RESULTS.md    — grouped, sorted tables + headline
 *
 * Process flags:
 *   - 'fight' is launched WITH the warm-FFI flags so the FFI handle is
 *     preloaded via opcache and the JIT is hot:
 *        -d opcache.enable_cli=1
 *        -d opcache.preload=<root>/bench/preload.php
 *        -d ffi.enable=1
 *   - every other parser is launched plain BUT still with
 *        -d opcache.enable_cli=1
 *     for fairness (everyone gets opcache + JIT).
 *
 * Robustness: if a combo errors (non-JSON output, crash, timeout, or a
 * row-level 'error' field), we record the error in that row and keep going.
 * One bad combo never aborts the whole run.
 *
 * Usage:
 *   php bench/run.php                 # uses corpus/manifest.json
 *   php bench/run.php <manifest.json> # override manifest path
 *
 * MEMORY CAVEAT carried through to RESULTS.md: 'fight' renders onto the C heap
 * (md4c malloc), invisible to PHP's peak_mb — its memory number is
 * real-RSS-favorable. See note printed in RESULTS.md.
 */

declare(strict_types=1);

$root = dirname(__DIR__);

// ---- Configuration ---------------------------------------------------------
$parserIds = ['fight', 'tempest', 'league-gfm', 'league-strict'];

$manifestPath = $argv[1] ?? ($root . '/corpus/manifest.json');
$onceScript   = $root . '/bench/once.php';
$preload      = $root . '/bench/preload.php';
$resultsDir   = $root . '/results';
$resultsJson  = $resultsDir . '/results.json';
$resultsMd    = $resultsDir . '/RESULTS.md';

$phpBin = PHP_BINARY;

// Per-child wall-clock guard. once.php caps warmup (~2s) and the forced-min
// timed phase (~3s) adaptively, so a child should finish well under this even
// for the 8MB corpus × slowest parser. Generous margin to avoid false timeouts.
$childTimeoutSec = 120;

// ---- Resolve the corpus file list from the manifest ------------------------
/**
 * Accepts several manifest shapes so we are robust to whatever the corpus
 * agent produced:
 *   - { "files": ["a.md", "b.md"] }
 *   - { "files": [ {"path":"a.md","label":"A"}, ... ] }
 *   - { "corpus": [ ... ] }   (same item shapes)
 *   - [ "a.md", {"path":"b.md"}, ... ]   (bare array)
 * Relative paths are resolved against the manifest's directory.
 *
 * @return list<array{path:string,label:string}>
 */
function load_corpus(string $manifestPath): array
{
    if (!is_file($manifestPath)) {
        fwrite(STDERR, "WARN: manifest not found at {$manifestPath}\n");
        return [];
    }
    $raw = file_get_contents($manifestPath);
    if ($raw === false) {
        fwrite(STDERR, "WARN: could not read manifest {$manifestPath}\n");
        return [];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        fwrite(STDERR, "WARN: manifest is not valid JSON\n");
        return [];
    }

    $items = $data['files'] ?? $data['corpus'] ?? (array_is_list($data) ? $data : []);
    if (!is_array($items)) {
        return [];
    }

    $base = dirname($manifestPath);
    $out  = [];
    foreach ($items as $item) {
        if (is_string($item)) {
            $path = $item;
            $label = basename($item);
        } elseif (is_array($item)) {
            $path  = (string) ($item['path'] ?? $item['file'] ?? $item['name'] ?? '');
            $label = (string) ($item['label'] ?? $item['name'] ?? ($path !== '' ? basename($path) : ''));
        } else {
            continue;
        }
        if ($path === '') {
            continue;
        }
        // Resolve relative paths against the manifest directory.
        if (!str_starts_with($path, '/')) {
            $resolved = $base . '/' . $path;
            $path = is_file($resolved) ? $resolved : $path;
        }
        if ($label === '') {
            $label = basename($path);
        }
        $out[] = ['path' => $path, 'label' => $label];
    }
    return $out;
}

/**
 * Spawn one once.php child via proc_open, capture stdout/stderr, enforce a
 * wall-clock timeout. Returns the decoded JSON row (or an error row).
 *
 * @return array<string,mixed>
 */
function run_combo(
    string $phpBin,
    string $onceScript,
    string $parserId,
    array $corpus,
    string $preload,
    int $timeoutSec
): array {
    $isFight = ($parserId === 'fight');

    $cmd = [$phpBin];
    // Everyone gets opcache+JIT for fairness.
    $cmd[] = '-d';
    $cmd[] = 'opcache.enable_cli=1';
    $cmd[] = '-d';
    $cmd[] = 'opcache.jit_buffer_size=64M';
    $cmd[] = '-d';
    $cmd[] = 'opcache.jit=tracing';
    if ($isFight) {
        // Warm-FFI flags: preload the FFI handle, enable ffi.
        $cmd[] = '-d';
        $cmd[] = 'opcache.preload=' . $preload;
        $cmd[] = '-d';
        $cmd[] = 'ffi.enable=1';
    }
    $cmd[] = $onceScript;
    $cmd[] = $parserId;
    $cmd[] = $corpus['path'];

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $errBase = [
        'parserId'    => $parserId,
        'corpus'      => $corpus['label'],
        'corpus_path' => $corpus['path'],
        'bytes'       => is_file($corpus['path']) ? (int) filesize($corpus['path']) : 0,
        'iters'       => 0,
        'ops_per_sec' => 0.0,
        'mb_per_sec'  => 0.0,
        'mean_ms'     => 0.0,
        'out_bytes'   => 0,
        'peak_mb'     => 0.0,
    ];

    $proc = @proc_open($cmd, $descriptors, $pipes, dirname($onceScript, 2));
    if (!is_resource($proc)) {
        return $errBase + ['error' => 'proc_open failed (could not spawn php child)'];
    }

    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $stdout = '';
    $stderr = '';
    $deadline = microtime(true) + $timeoutSec;
    $killed = false;

    while (true) {
        $status = proc_get_status($proc);
        $chunk = stream_get_contents($pipes[1]);
        if ($chunk !== false) {
            $stdout .= $chunk;
        }
        $echunk = stream_get_contents($pipes[2]);
        if ($echunk !== false) {
            $stderr .= $echunk;
        }
        if (!$status['running']) {
            break;
        }
        if (microtime(true) > $deadline) {
            proc_terminate($proc, 9);
            $killed = true;
            break;
        }
        usleep(2000);
    }

    // Drain anything left in the pipes.
    $stdout .= (string) stream_get_contents($pipes[1]);
    $stderr .= (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($proc);

    if ($killed) {
        return $errBase + ['error' => "child timed out after {$timeoutSec}s"];
    }

    // once.php prints exactly one JSON line on stdout. Take the last non-empty.
    $lines = array_values(array_filter(array_map('trim', explode("\n", $stdout)), static fn ($l) => $l !== ''));
    $jsonLine = end($lines) ?: '';
    $row = json_decode($jsonLine, true);

    if (!is_array($row)) {
        $detail = $stderr !== '' ? trim($stderr) : ('non-JSON output: ' . substr($stdout, 0, 240));
        if ($exit !== 0 && $detail === '') {
            $detail = "child exited with code {$exit}";
        }
        return $errBase + ['error' => $detail];
    }

    // Normalise: ensure every expected key exists.
    return $row + $errBase + ['error' => $row['error'] ?? null];
}

// ---- Formatting helpers ----------------------------------------------------
function fmt_num($v, int $decimals = 0): string
{
    if (!is_numeric($v)) {
        return (string) $v;
    }
    return number_format((float) $v, $decimals);
}

function fmt_bytes(int $b): string
{
    if ($b >= 1_048_576) {
        return number_format($b / 1_048_576, 2) . ' MB';
    }
    if ($b >= 1024) {
        return number_format($b / 1024, 1) . ' KB';
    }
    return $b . ' B';
}

// ---- Main ------------------------------------------------------------------
@mkdir($resultsDir, 0775, true);

$corpusFiles = load_corpus($manifestPath);
if ($corpusFiles === []) {
    fwrite(STDERR, "No corpus files resolved from manifest ({$manifestPath}). Nothing to run.\n");
    // Still write empty result artifacts so downstream steps don't choke.
    file_put_contents($resultsJson, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    file_put_contents($resultsMd, "# Markdown Fight — Results\n\nNo corpus files were found in `{$manifestPath}`.\n");
    exit(0);
}

$all = [];
foreach ($corpusFiles as $corpus) {
    foreach ($parserIds as $parserId) {
        fwrite(STDERR, sprintf("running %-14s  %s ... ", $parserId, $corpus['label']));
        $row = run_combo($phpBin, $onceScript, $parserId, $corpus, $preload, $childTimeoutSec);
        if (!empty($row['error'])) {
            fwrite(STDERR, 'ERROR: ' . $row['error'] . "\n");
        } else {
            fwrite(STDERR, sprintf(
                "%s ops/s, %s MB/s, %s ms, peak %s MB\n",
                fmt_num($row['ops_per_sec'], 0),
                fmt_num($row['mb_per_sec'], 1),
                fmt_num($row['mean_ms'], 3),
                fmt_num($row['peak_mb'], 2)
            ));
        }
        $all[] = $row;
    }
}

// ---- results.json ----------------------------------------------------------
file_put_contents($resultsJson, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

// ---- RESULTS.md ------------------------------------------------------------
// Group by corpus, sort rows by ops/sec descending (winner first).
$byCorpus = [];
foreach ($all as $row) {
    $byCorpus[$row['corpus']][] = $row;
}

$md = [];
$md[] = '# Markdown Fight — Results';
$md[] = '';
$md[] = '_Generated ' . date('Y-m-d H:i:s') . ' · PHP ' . PHP_VERSION . ' · ' . php_uname('s') . ' ' . php_uname('m') . '_';
$md[] = '';

// Headline: overall winner by aggregate (geomean-ish) MB/s across corpora,
// plus 'fight' speedups vs the two contenders on the largest corpus.
$headline = build_headline($byCorpus);
$md[] = '> ' . $headline;
$md[] = '';

// Methodology + memory caveat.
$md[] = '## Methodology';
$md[] = '';
$md[] = '- Each (parser × corpus) combo runs in its **own php process** for a clean per-parser `memory_get_peak_usage(true)`.';
$md[] = '- Warmup: ≥5 iterations. Timed: fixed **~1.0s wall-clock budget** (min 20 iters), measured with `hrtime(true)`.';
$md[] = '- `fight` is launched with warm-FFI flags: `opcache.enable_cli=1`, `opcache.preload=bench/preload.php`, `ffi.enable=1`. All parsers get `opcache.enable_cli=1` + tracing JIT for fairness.';
$md[] = '- Parser/converter instances are constructed **once**, outside the timed loop (steady-state comparison).';
$md[] = '';
$md[] = '> **Memory caveat (honest):** `fight` renders its HTML onto the **C heap** (md4c `malloc`), which PHP\'s `peak_mb` does **not** count — so `fight`\'s memory number is real-RSS-favorable (it undercounts the transient, immediately-freed C output buffer). Pure-PHP parsers keep all work on the Zend heap, so their `peak_mb` is a complete accounting. Read the memory column with that asymmetry in mind.';
$md[] = '';

foreach ($byCorpus as $corpusLabel => $rows) {
    // Sort: winners (no error, higher ops/sec) first; errored rows sink.
    usort($rows, static function ($a, $b) {
        $ae = !empty($a['error']);
        $be = !empty($b['error']);
        if ($ae !== $be) {
            return $ae <=> $be; // non-errored first
        }
        return ($b['ops_per_sec'] ?? 0) <=> ($a['ops_per_sec'] ?? 0);
    });

    $bytes = 0;
    foreach ($rows as $r) {
        if (!empty($r['bytes'])) {
            $bytes = (int) $r['bytes'];
            break;
        }
    }

    // Reference numbers for speedup columns.
    $tempestOps = null;
    $leagueGfmOps = null;
    foreach ($rows as $r) {
        if ($r['parserId'] === 'tempest' && empty($r['error'])) {
            $tempestOps = (float) $r['ops_per_sec'];
        }
        if ($r['parserId'] === 'league-gfm' && empty($r['error'])) {
            $leagueGfmOps = (float) $r['ops_per_sec'];
        }
    }

    $md[] = '## ' . $corpusLabel . '  (' . fmt_bytes($bytes) . ')';
    $md[] = '';
    $md[] = '| Parser | ops/sec | MB/s | mean ms | peak MB | out bytes | vs tempest | vs league-gfm |';
    $md[] = '|---|--:|--:|--:|--:|--:|--:|--:|';

    foreach ($rows as $i => $r) {
        $name = $r['parserId'];
        $marker = ($i === 0 && empty($r['error'])) ? ' 🏆' : '';

        if (!empty($r['error'])) {
            $err = str_replace(['|', "\n"], [' ', ' '], (string) $r['error']);
            $err = substr($err, 0, 80);
            $md[] = "| **{$name}** | — | — | — | — | — | — | — |  ⚠️ {$err}";
            continue;
        }

        $ops = (float) $r['ops_per_sec'];
        $vsTempest = ($tempestOps && $tempestOps > 0) ? number_format($ops / $tempestOps, 2) . '×' : '—';
        $vsLeague  = ($leagueGfmOps && $leagueGfmOps > 0) ? number_format($ops / $leagueGfmOps, 2) . '×' : '—';

        // Only annotate speedup for fight (per spec), dash for others to keep focus.
        if ($name !== 'fight') {
            $vsTempest = ($name === 'tempest') ? '1.00×' : $vsTempest;
            $vsLeague  = ($name === 'league-gfm') ? '1.00×' : $vsLeague;
        }

        $md[] = sprintf(
            '| **%s**%s | %s | %s | %s | %s | %s | %s | %s |',
            $name,
            $marker,
            fmt_num($r['ops_per_sec'], 0),
            fmt_num($r['mb_per_sec'], 2),
            fmt_num($r['mean_ms'], 4),
            fmt_num($r['peak_mb'], 2),
            fmt_num($r['out_bytes'], 0),
            $vsTempest,
            $vsLeague
        );
    }
    $md[] = '';
}

file_put_contents($resultsMd, implode("\n", $md) . "\n");

fwrite(STDERR, "\nWrote:\n  {$resultsJson}\n  {$resultsMd}\n");
exit(0);

/**
 * Build the one-line headline. Picks 'fight's median speedup vs each contender
 * across corpora (using ops/sec), and names the overall throughput winner.
 *
 * @param array<string, list<array<string,mixed>>> $byCorpus
 */
function build_headline(array $byCorpus): string
{
    $fightVsTempest = [];
    $fightVsLeague  = [];
    $winnerCounts   = [];

    foreach ($byCorpus as $rows) {
        $ops = [];
        foreach ($rows as $r) {
            if (empty($r['error']) && isset($r['ops_per_sec'])) {
                $ops[$r['parserId']] = (float) $r['ops_per_sec'];
            }
        }
        if ($ops === []) {
            continue;
        }
        arsort($ops);
        $winner = array_key_first($ops);
        $winnerCounts[$winner] = ($winnerCounts[$winner] ?? 0) + 1;

        if (isset($ops['fight'], $ops['tempest']) && $ops['tempest'] > 0) {
            $fightVsTempest[] = $ops['fight'] / $ops['tempest'];
        }
        if (isset($ops['fight'], $ops['league-gfm']) && $ops['league-gfm'] > 0) {
            $fightVsLeague[] = $ops['fight'] / $ops['league-gfm'];
        }
    }

    $median = static function (array $xs): float {
        if ($xs === []) {
            return 0.0;
        }
        sort($xs);
        $n = count($xs);
        $mid = intdiv($n, 2);
        return $n % 2 ? $xs[$mid] : ($xs[$mid - 1] + $xs[$mid]) / 2;
    };

    if ($winnerCounts === []) {
        return 'No successful measurements — every combo errored. See per-row error notes below.';
    }

    arsort($winnerCounts);
    $overallWinner = array_key_first($winnerCounts);

    $vt = $median($fightVsTempest);
    $vl = $median($fightVsLeague);

    $parts = [];
    $parts[] = '**' . $overallWinner . '** wins on throughput';
    if ($vt > 0) {
        $parts[] = sprintf('fight is ~%.1f× faster than tempest', $vt);
    }
    if ($vl > 0) {
        $parts[] = sprintf('~%.1f× faster than league-gfm', $vl);
    }
    return implode(' · ', $parts) . ' (median across corpora).';
}

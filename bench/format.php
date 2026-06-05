<?php

declare(strict_types=1);

/**
 * bench/format.php — the only custom benchmark script.
 *
 * PHPBench owns all measurement. This reads PHPBench's XML dump and turns it
 * into the showcase artifacts:
 *   results/RESULTS.md   — HTML-throughput tables per corpus + front-matter table
 *   results/results.json — flat machine-readable rows
 *
 * PHPBench does not emit ops/sec, MB/s, speedup ratios, a winner marker, or a
 * prose headline — this computes them from the raw `mode` time (µs/rev) and the
 * `bytes` param the throughput benchmark attaches to each corpus document.
 *
 * Usage:
 *   php bench/format.php [results/raw.xml]
 *
 * MEMORY CAVEAT (carried into RESULTS.md): our FFI parser renders onto the C
 * heap (md4c malloc), invisible to PHP's memory metrics — so its peak MB is
 * real-RSS-favorable. Pure-PHP parsers are fully accounted. The peak MB column
 * also includes PHPBench's own per-process runner overhead, so treat memory as
 * directional, not absolute.
 */

$root = dirname(__DIR__);
$xmlPath = $argv[1] ?? $root . '/results/raw.xml';
$resultsDir = $root . '/results';
$resultsJson = $resultsDir . '/results.json';
$resultsMd = $resultsDir . '/RESULTS.md';

if (! is_file($xmlPath)) {
    fwrite(STDERR, "format: dump not found at {$xmlPath} (run `composer bench` to produce it)\n");
    exit(1);
}

$xml = @simplexml_load_file($xmlPath);
if ($xml === false) {
    fwrite(STDERR, "format: could not parse XML at {$xmlPath}\n");
    exit(1);
}

// ---- Identity maps ---------------------------------------------------------
/** Throughput subject -> friendly parser id. */
$THROUGHPUT_IDS = [
    'benchHelgesverre' => 'helgesverre/markdown',
    'benchTempest' => 'tempest',
    'benchLeagueGfm' => 'league-gfm',
    'benchLeagueStrict' => 'league-strict',
];

/** Front-matter subject -> {label, rendersBody, note}. */
$FRONTMATTER_DESC = [
    'benchSymfonyYaml' => ['label' => 'symfony/yaml (floor)', 'rendersBody' => false, 'note' => 'raw YAML only — no Markdown involved'],
    'benchOursExtract' => ['label' => 'helgesverre/markdown (extract)', 'rendersBody' => false, 'note' => 'dedicated extractor: regex split + symfony/yaml'],
    'benchLeagueFrontmatter' => ['label' => 'league/commonmark (frontmatter-only)', 'rendersBody' => false, 'note' => 'FrontMatterParser — skips rendering the body'],
    'benchTempestFull' => ['label' => 'tempest/markdown (full parse)', 'rendersBody' => true, 'note' => 'no front-matter-only API — renders the whole document'],
    'benchOursFull' => ['label' => 'helgesverre/markdown (full parse)', 'rendersBody' => true, 'note' => 'parse(): front matter + HTML + table of contents'],
];

// ---- XML helpers -----------------------------------------------------------
/** Read a named <value> out of an <env> sub-block (php, uname, ...). */
function env_value(SimpleXMLElement $xml, string $block, string $name, string $default): string
{
    foreach ($xml->xpath("//env/{$block}/value[@name='{$name}']") ?: [] as $v) {
        return (string) $v;
    }
    return $default;
}

/**
 * Pull one variant down to the numbers we report.
 *
 * @return array{mode_us:float, peak_mb:float, params:array<string,string>, errored:bool}
 */
function read_variant(SimpleXMLElement $variant): array
{
    // A variant that threw during parse carries an <errors> block and no stats.
    $errored = isset($variant->errors);

    // stats are time per rev, in microseconds (output-time-unit=microseconds).
    $mode = 0.0;
    if (isset($variant->stats)) {
        $attr = $variant->stats->attributes();
        $mode = (float) ($attr['mode'] ?? $attr['mean'] ?? 0);
    }

    // Memory: peak over the variant's iterations (bytes -> MB).
    $peakBytes = 0;
    foreach ($variant->iteration as $it) {
        $peakBytes = max($peakBytes, (int) $it->attributes()['mem-peak']);
    }

    // Parameters (path/bytes/label for throughput; empty for front matter).
    $params = [];
    if (isset($variant->{'parameter-set'})) {
        foreach ($variant->{'parameter-set'}->parameter as $p) {
            $a = $p->attributes();
            $params[(string) $a['name']] = (string) $a['value'];
        }
    }

    return [
        'mode_us' => $mode,
        'peak_mb' => $peakBytes / 1_048_576,
        'params' => $params,
        'errored' => $errored,
    ];
}

// ---- Formatting helpers (ported from the old run.php) ----------------------
function fmt_num($v, int $decimals = 0): string
{
    if (! is_numeric($v)) {
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

// ---- Walk the suite --------------------------------------------------------
/** @var list<array<string,mixed>> $throughput */
$throughput = [];
/** @var list<array<string,mixed>> $frontmatter */
$frontmatter = [];

foreach ($xml->xpath('//benchmark') ?: [] as $benchmark) {
    $class = (string) $benchmark->attributes()['class'];
    $isThroughput = str_contains($class, 'ThroughputBench');
    $isFrontmatter = str_contains($class, 'FrontMatterBench');

    foreach ($benchmark->subject as $subject) {
        $name = (string) $subject->attributes()['name'];

        foreach ($subject->variant as $variant) {
            $v = read_variant($variant);
            $modeUs = $v['mode_us'];
            $opsPerSec = $modeUs > 0 ? 1e6 / $modeUs : 0.0;

            if ($isThroughput && isset($THROUGHPUT_IDS[$name])) {
                $bytes = (int) ($v['params']['bytes'] ?? 0);
                $label = $v['params']['label'] ?? '(unknown)';
                // MB/s = bytes / (µs per rev): bytes/µs == MB/s.
                $mbPerSec = $modeUs > 0 ? $bytes / $modeUs : 0.0;
                $throughput[] = [
                    'group' => 'throughput',
                    'parserId' => $THROUGHPUT_IDS[$name],
                    'corpus' => $label,
                    'bytes' => $bytes,
                    'ops_per_sec' => $opsPerSec,
                    'mb_per_sec' => $mbPerSec,
                    'mean_ms' => $modeUs / 1000,
                    'peak_mb' => $v['peak_mb'],
                    'error' => $v['errored'],
                ];
            } elseif ($isFrontmatter && isset($FRONTMATTER_DESC[$name])) {
                $d = $FRONTMATTER_DESC[$name];
                $frontmatter[] = [
                    'group' => 'frontmatter',
                    'subject' => $name,
                    'label' => $d['label'],
                    'mean_us' => $modeUs,
                    'ops_per_sec' => $opsPerSec,
                    'rendersBody' => $d['rendersBody'],
                    'note' => $d['note'],
                ];
            }
        }
    }
}

// ---- results.json ----------------------------------------------------------
@mkdir($resultsDir, 0o775, true);
file_put_contents(
    $resultsJson,
    json_encode(
        ['throughput' => $throughput, 'frontmatter' => $frontmatter],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
    )
        . "\n",
);

// ---- RESULTS.md ------------------------------------------------------------
$phpVer = env_value($xml, 'php', 'version', PHP_VERSION);
$os = env_value($xml, 'uname', 'os', php_uname('s'));
$machine = env_value($xml, 'uname', 'machine', php_uname('m'));

// Group throughput rows by corpus.
$byCorpus = [];
foreach ($throughput as $row) {
    $byCorpus[$row['corpus']][] = $row;
}

$md = [];
$md[] = '# helgesverre/markdown — benchmark results';
$md[] = '';
$md[] = '_Generated ' . date('Y-m-d H:i:s') . ' · PHP ' . $phpVer . ' · ' . $os . ' ' . $machine . ' · measured with PHPBench_';
$md[] = '';
$md[] = '## Methodology';
$md[] = '';
$md[] = '- One measurement engine: [PHPBench](https://phpbench.readthedocs.io). Run the whole suite with `composer bench`.';
$md[] = '- Every parser runs with **identical PHP flags** (`opcache.enable_cli`, tracing JIT, `ffi.enable`, `opcache.preload=bench/preload.php`). The preload only warms *our* FFI handle; for the pure-PHP parsers it is inert. Same env for everyone — our parser wins on merit plus a legitimately-preloaded handle.';
$md[] = '- Cadence: **2 warmup, 50 revolutions × 10 iterations**, retry threshold 2.0 (PHPBench re-runs iterations until variance settles). Each iteration runs in its own process; reported time is the `mode` µs/rev.';
$md[] = '- Parser instances are constructed **once** (in `setUp`/the registry), outside the timed revolutions. Corpus documents are read during warmup, not inside the measured revs.';
$md[] = '';
$md[] = '> **Memory caveat (honest):** `helgesverre/markdown` renders its HTML onto the **C heap** (md4c `malloc`), which PHP\'s memory metrics do **not** count — so its `peak MB` is real-RSS-favorable (it undercounts the transient, immediately-freed C output buffer). Pure-PHP parsers keep all work on the Zend heap, so their `peak MB` is a complete accounting. The `peak MB` column also includes PHPBench\'s own per-process runner overhead, so read it as directional, not absolute.';
$md[] = '';

// ---- HTML throughput -------------------------------------------------------
$md[] = '## HTML throughput';
$md[] = '';

if ($byCorpus === []) {
    $md[] = '_No throughput measurements found in the dump._';
    $md[] = '';
}

foreach ($byCorpus as $corpusLabel => $rows) {
    // Successful runs first (fastest first); errored rows sink to the bottom.
    usort($rows, static function ($a, $b) {
        $ae = ! empty($a['error']);
        $be = ! empty($b['error']);
        if ($ae !== $be) {
            return $ae <=> $be;
        }
        return ($b['ops_per_sec'] ?? 0) <=> ($a['ops_per_sec'] ?? 0);
    });

    $bytes = (int) ($rows[0]['bytes'] ?? 0);

    // Speedup baselines come only from successful runs.
    $tempestOps = null;
    $leagueGfmOps = null;
    foreach ($rows as $r) {
        if (! empty($r['error'])) {
            continue;
        }
        if ($r['parserId'] === 'tempest') {
            $tempestOps = (float) $r['ops_per_sec'];
        }
        if ($r['parserId'] === 'league-gfm') {
            $leagueGfmOps = (float) $r['ops_per_sec'];
        }
    }

    $md[] = '### ' . $corpusLabel . '  (' . fmt_bytes($bytes) . ')';
    $md[] = '';
    $md[] = '| Parser | ops/sec | MB/s | mean ms | peak MB | vs tempest | vs league-gfm |';
    $md[] = '|---|--:|--:|--:|--:|--:|--:|';

    foreach ($rows as $i => $r) {
        $name = $r['parserId'];

        if (! empty($r['error'])) {
            $md[] = sprintf('| **%s** | — | — | — | — | — | — |  ⚠️ threw during parse', $name);
            continue;
        }

        $marker = $i === 0 ? ' 🏆' : '';
        $ops = (float) $r['ops_per_sec'];

        $vsTempest = $tempestOps && $tempestOps > 0 ? number_format($ops / $tempestOps, 2) . '×' : '—';
        $vsLeague = $leagueGfmOps && $leagueGfmOps > 0 ? number_format($ops / $leagueGfmOps, 2) . '×' : '—';
        if ($name === 'tempest') {
            $vsTempest = '1.00×';
        }
        if ($name === 'league-gfm') {
            $vsLeague = '1.00×';
        }

        $md[] = sprintf(
            '| **%s**%s | %s | %s | %s | %s | %s | %s |',
            $name,
            $marker,
            fmt_num($r['ops_per_sec'], 0),
            fmt_num($r['mb_per_sec'], 2),
            fmt_num($r['mean_ms'], 4),
            fmt_num($r['peak_mb'], 2),
            $vsTempest,
            $vsLeague,
        );
    }
    $md[] = '';
}

// ---- Front-matter extraction -----------------------------------------------
$md[] = '## Front-matter extraction';
$md[] = '';

if ($frontmatter === []) {
    $md[] = '_No front-matter measurements found in the dump._';
    $md[] = '';
} else {
    usort($frontmatter, static fn ($a, $b) => ($b['ops_per_sec'] ?? 0) <=> ($a['ops_per_sec'] ?? 0));
    $fastest = (float) ($frontmatter[0]['ops_per_sec'] ?? 0);

    $md[] = '| Approach | mean µs | ops/sec | renders body? | vs fastest |';
    $md[] = '|---|--:|--:|:--:|--:|';
    foreach ($frontmatter as $r) {
        $md[] = sprintf(
            '| %s | %s | %s | %s | %s |',
            $r['label'],
            fmt_num($r['mean_us'], 2),
            fmt_num($r['ops_per_sec'], 0),
            $r['rendersBody'] ? 'yes' : 'no',
            $fastest > 0 ? number_format($r['ops_per_sec'] / $fastest, 2) . '×' : '—',
        );
    }
    $md[] = '';
}

file_put_contents($resultsMd, implode("\n", $md) . "\n");

fwrite(STDERR, "Wrote:\n  {$resultsJson}\n  {$resultsMd}\n");
exit(0);

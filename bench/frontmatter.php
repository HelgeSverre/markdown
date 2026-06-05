<?php

/**
 * bench/frontmatter.php — front-matter extraction head-to-head.
 *
 * Everything else in this repo benchmarks Markdown -> HTML. This one isolates a
 * single feature: pulling the YAML front-matter array out of a document. It
 * answers "how do we compare on front matter alone?" against the other parsers
 * and against raw symfony/yaml (the floor — just parsing the YAML, no Markdown).
 *
 * The honest asymmetry, surfaced in the "renders body?" column: tempest has no
 * front-matter-only API, so getting its frontmatter means rendering the whole
 * document. helgesverre/markdown and league both expose a dedicated extractor
 * that skips rendering — that's the comparison that matters for "front matter
 * alone." The full-parse rows are included for reference.
 *
 * Usage:  composer bench:frontmatter   (or: php bench/frontmatter.php)
 */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use HelgeSverre\Markdown\FfiParser;
use HelgeSverre\Markdown\FrontMatter;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser as LeagueFrontMatterParser;
use Symfony\Component\Yaml\Yaml;
use Tempest\Markdown\Markdown as TempestMarkdown;

// ---- The document under test -----------------------------------------------
// A realistic blog-post header (scalars, an inline list, a nested map) followed
// by enough Markdown body that "render the whole doc" is real work, not a stub.
$yaml = <<<'YAML'
    title: "Benchmarking Markdown Front Matter"
    date: 2026-06-05
    draft: false
    tags: [php, markdown, ffi, performance]
    author:
      name: Helge Sverre
      url: https://helgesverre.com
    description: How fast can you pull metadata off a Markdown document?
    YAML;

$body = str_repeat(
    "## Section\n\nSome **bold** and _italic_ prose with a [link](https://example.com) "
    . "and `inline code`, plus a list:\n\n- one\n- two\n- three\n\n"
    . "```php\n\$x = 1 + 2;\n```\n\n",
    6,
);

$doc = "---\n{$yaml}\n---\n# " . "Front Matter Speed\n\n" . $body;

// ---- Contenders: each returns the parsed front-matter array -----------------
// rendersBody = true means the approach also renders the Markdown body to get
// at the front matter (no dedicated extractor), so it pays the full parse cost.
$contenders = [];

$contenders['symfony/yaml (floor)'] = [
    'rendersBody' => false,
    'note' => 'raw YAML only — no Markdown involved',
    'run' => static fn (): array => (array) Yaml::parse($yaml),
];

$contenders['helgesverre/markdown (extract)'] = [
    'rendersBody' => false,
    'note' => 'dedicated extractor: regex split + symfony/yaml',
    'run' => static fn (): array => FrontMatter::extract($doc)[0],
];

if (class_exists(LeagueFrontMatterParser::class)) {
    $leagueFm = new LeagueFrontMatterParser(new SymfonyYamlFrontMatterParser());
    $contenders['league/commonmark (frontmatter-only)'] = [
        'rendersBody' => false,
        'note' => 'FrontMatterParser — skips rendering the body',
        'run' => static fn (): array => (array) $leagueFm->parse($doc)->getFrontMatter(),
    ];
}

if (class_exists(TempestMarkdown::class)) {
    $tempest = new TempestMarkdown();
    $contenders['tempest/markdown (full parse)'] = [
        'rendersBody' => true,
        'note' => 'no front-matter-only API — renders the whole document',
        'run' => static fn (): array => $tempest->parse($doc)->frontmatter,
    ];
}

$ours = new FfiParser();
$contenders['helgesverre/markdown (full parse)'] = [
    'rendersBody' => true,
    'note' => 'parse(): front matter + HTML + table of contents',
    'run' => static fn (): array => $ours->parse($doc)->frontmatter,
];

// ---- Timing harness (mirrors bench/once.php: warmup, ~1s budget) ------------
function bench(callable $fn, float $budgetSec = 1.0, int $minIters = 50): array
{
    // Warmup.
    for ($i = 0; $i < 20; $i++) {
        $fn();
    }

    $iters = 0;
    $start = hrtime(true);
    $deadline = $start + (int) ($budgetSec * 1e9);
    do {
        $fn();
        $iters++;
    } while ($iters < $minIters || hrtime(true) < $deadline);
    $elapsedNs = hrtime(true) - $start;

    $meanUs = ($elapsedNs / $iters) / 1e3;

    return ['iters' => $iters, 'mean_us' => $meanUs, 'ops_per_sec' => 1e6 / $meanUs];
}

// ---- Run --------------------------------------------------------------------
fwrite(STDERR, 'doc: ' . strlen($doc) . ' bytes (yaml header ' . strlen($yaml) . " bytes)\n\n");

$rows = [];
foreach ($contenders as $name => $c) {
    $sample = $c['run']();
    if (! isset($sample['title']) || $sample['title'] === '') {
        fwrite(STDERR, "WARN: {$name} did not return the expected front matter; skipping.\n");
        continue;
    }
    fwrite(STDERR, sprintf('timing %-42s ... ', $name));
    $r = bench($c['run']);
    $rows[] = ['name' => $name] + $r + ['rendersBody' => $c['rendersBody'], 'note' => $c['note']];
    fwrite(STDERR, sprintf("%s ops/s, %.2f µs\n", number_format($r['ops_per_sec'], 0), $r['mean_us']));
}

// Sort fastest first.
usort($rows, static fn ($a, $b) => $b['ops_per_sec'] <=> $a['ops_per_sec']);

$fastest = $rows[0]['ops_per_sec'] ?? 0.0;

echo "\n# Front-matter extraction — " . count($rows) . " contenders\n\n";
echo 'Document: ' . number_format(strlen($doc)) . ' bytes total, ' . number_format(strlen($yaml)) . " bytes of YAML.\n\n";
echo "| Approach | mean µs | ops/sec | renders body? | vs fastest |\n";
echo "|---|--:|--:|:--:|--:|\n";
foreach ($rows as $r) {
    printf(
        "| %s | %s | %s | %s | %s |\n",
        $r['name'],
        number_format($r['mean_us'], 2),
        number_format($r['ops_per_sec'], 0),
        $r['rendersBody'] ? 'yes' : 'no',
        $fastest > 0 ? number_format($r['ops_per_sec'] / $fastest, 2) . '×' : '—',
    );
}

echo "\nNotes:\n";
foreach ($rows as $r) {
    echo "  - {$r['name']}: {$r['note']}\n";
}
echo "\nTakeaway: for front matter *alone*, the dedicated extractors (helgesverre/markdown, league)\n";
echo "sit just above the raw symfony/yaml floor — they add only a cheap split on top of\n";
echo "the same YAML parse. Approaches with no front-matter-only API pay to render the\n";
echo "entire document just to read its header.\n";

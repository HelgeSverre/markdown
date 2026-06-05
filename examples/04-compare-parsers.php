<?php

declare(strict_types=1);

/**
 * Head-to-head on one document: helgesverre/markdown vs league/commonmark vs
 * tempest/markdown — timing, throughput, and output size, side by side.
 *
 *   php examples/04-compare-parsers.php [path/to/file.md | "inline markdown"]
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use HelgeSverre\Markdown\FfiParser;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Tempest\Markdown\Markdown as TempestMarkdown;

$root = dirname(__DIR__);
$arg = $argv[1] ?? $root . '/corpus/tempest-docs.md';
$markdown = is_file($arg) ? (string) file_get_contents($arg) : $arg;
$label = is_file($arg) ? basename($arg) : 'inline string';
$bytes = strlen($markdown);

// Construct each parser ONCE, outside the timed loop — same steady-state,
// instance-reuse methodology as the real benchmark (bench/run.php). Building a
// fresh parser every call would time construction, not parsing.
$fight = new FfiParser();
$league = new GithubFlavoredMarkdownConverter();
$tempest = new TempestMarkdown();

$contenders = [
    'helgesverre/markdown (FFI→md4c)' => static fn (): string => $fight->toHtml($markdown),
    'league/commonmark (GFM)' => static fn (): string => (string) $league->convert($markdown)->getContent(),
    'tempest/markdown' => static function () use ($tempest, $markdown): string {
        try {
            return $tempest->parse($markdown)->html;
        } catch (\Throwable $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    },
];

printf("Input: %s (%.1f KB)\n\n", $label, $bytes / 1024);
printf("%-34s %12s %12s %12s\n", 'parser', 'mean ms', 'MB/s', 'out bytes');
echo str_repeat('-', 72) . "\n";

$baseline = null;
foreach ($contenders as $name => $fn) {
    for ($i = 0; $i < 3; $i++) {
        $fn(); // warm up
    }

    $iters = $bytes > 200_000 ? 20 : 200;
    $out = '';
    $t = hrtime(true);
    for ($i = 0; $i < $iters; $i++) {
        $out = $fn();
    }
    $ms = (hrtime(true) - $t) / 1e6 / $iters;

    $errored = str_starts_with($out, 'ERROR:');
    $mbs = $errored ? 0.0 : $bytes / 1e6 / ($ms / 1000);
    printf("%-34s %12.3f %12.1f %12s\n", $name, $ms, $mbs, $errored ? 'errored' : (string) strlen($out));

    $baseline ??= $ms;
}

printf("\nFastest is the first row; the others are %s.\n", 'multiples slower (see mean ms)');
echo "Note: this is PHP calling C vs pure-PHP parsers — see the README for the honest framing.\n";

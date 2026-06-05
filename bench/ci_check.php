<?php

declare(strict_types=1);

/**
 * CI correctness gate: proves the FFI->md4c parser builds, binds, renders
 * correct CommonMark+GFM, matches league structurally, and is actually fast
 * (i.e. the native path is really engaged). Exits non-zero on any failure.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use HelgeSverre\Markdown\FfiBatchParser;
use HelgeSverre\Markdown\FfiParser;
use League\CommonMark\GithubFlavoredMarkdownConverter;

$fail = [];
$check = function (string $name, bool $cond) use (&$fail): void {
    echo ($cond ? '  ok    ' : '  FAIL  ') . $name . "\n";
    if (! $cond) {
        $fail[] = $name;
    }
};

$lib = FfiParser::libPath();
$ours = new FfiParser();
echo "parser : {$ours->name()}\n";
echo 'php    : ' . PHP_VERSION . ' on ' . PHP_OS_FAMILY . "\n";
echo "lib    : {$lib} (" . (is_file($lib) ? filesize($lib) . ' bytes' : 'MISSING!') . ")\n\n";

// 1. basic structure
$h = $ours->toHtml("# Hello\n\n- a\n- b\n");
$check('renders <h1> + <li>', str_contains($h, '<h1>Hello</h1>') && str_contains($h, '<li>a</li>'));

// 2. all four GFM extensions
$gfm = $ours->toHtml("| a | b |\n|---|---|\n| 1 | 2 |\n\n~~s~~\n\n- [x] done\n\nvisit www.example.com\n");
$check('gfm: table', str_contains($gfm, '<table>') && str_contains($gfm, '<th>a</th>'));
$check('gfm: strikethrough <del>', str_contains($gfm, '<del>s</del>'));
$check('gfm: task list checkbox', str_contains($gfm, 'type="checkbox"'));
$check('gfm: autolink', str_contains($gfm, 'href="http://www.example.com"'));

// 3. nested-anchor fix (explicit link whose text is itself a bare URL)
$na = $ours->toHtml("[https://x.com/a](https://x.com/a)\n");
$check('no nested <a><a>, content kept', ! preg_match('#<a[^>]*>\s*<a #', $na) && str_contains($na, 'x.com/a'));

// 4. structural parity with league GFM on a representative sample
$sample =
    "# Title\n\nA paragraph with **bold**, *italic*, `code` and a [link](http://e.com).\n\n"
    . "## Sub\n\n- one\n- two\n  - nested\n\n1. first\n2. second\n\n> a quote\n\n"
    . "| h1 | h2 |\n|----|----|\n| a  | b  |\n\n```php\n\$x = 1;\n```\n\n~~struck~~\n";
$lf = $ours->toHtml($sample);
$lg = (string) new GithubFlavoredMarkdownConverter()
    ->convert($sample)
    ->getContent();
$visible = static fn (string $s): string => trim((string) preg_replace('/\s+/', ' ', strip_tags($s)));
$check('visible text == league-gfm', $visible($lf) === $visible($lg));
// informational tag-count comparison (not gated — cosmetic md4c-vs-league diffs are fine)
foreach (['h1', 'h2', 'ul', 'ol', 'li', 'table', 'tr', 'th', 'td', 'strong', 'em', 'del', 'a', 'blockquote', 'pre'] as $t) {
    $cf = substr_count($lf, "<{$t}");
    $cl = substr_count($lg, "<{$t}");
    if ($cf !== $cl) {
        echo "  info  tag <{$t}>: helgesverre/markdown={$cf} league={$cl}\n";
    }
}

// 5. batch path parity (md2html_batch, pthread pool, anchor fix applied per doc)
$docs = ["# A\n", "| x |\n|---|\n| y |\n", "[https://a.com/p](https://a.com/p)\n"];
$batch = new FfiBatchParser()->toHtmlBatch($docs);
$batchOk = true;
foreach ($docs as $i => $d) {
    if ($batch[$i] === $ours->toHtml($d)) {
        continue;
    }

    $batchOk = false;
}
$check('batch == sequential', $batchOk);

// 6. speed sanity: helgesverre/markdown must be dramatically faster than league (FFI really engaged)
$med = str_repeat($sample, 200);
$t = hrtime(true);
for ($i = 0; $i < 50; $i++) {
    $ours->toHtml($med);
}
$oursNs = (hrtime(true) - $t) / 50;
$lgc = new GithubFlavoredMarkdownConverter();
$t = hrtime(true);
for ($i = 0; $i < 5; $i++) {
    $lgc->convert($med)->getContent();
}
$leagueNs = (hrtime(true) - $t) / 5;
$ratio = $leagueNs / $oursNs;
$check(sprintf('helgesverre/markdown >= 5x faster than league-gfm (measured %.1fx)', $ratio), $ratio >= 5.0);

echo "\n" . (empty($fail) ? "ALL CHECKS PASSED\n" : count($fail) . " CHECK(S) FAILED\n");
exit(empty($fail) ? 0 : 1);

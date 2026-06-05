<?php

declare(strict_types=1);

/**
 * Render a real Markdown file to a standalone, styled HTML page — the bread
 * and butter of any docs/blog renderer.
 *
 *   php examples/02-render-file.php [path/to/file.md]
 *
 * Defaults to the real Tempest docs corpus. Writes examples/output/<name>.html.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use HelgeSverre\Markdown\Parser;

$root = dirname(__DIR__);
$input = $argv[1] ?? $root . '/corpus/tempest-docs.md';

if (! is_file($input)) {
    fwrite(STDERR, "No such file: {$input}\n");
    exit(1);
}

$markdown = (string) file_get_contents($input);

$start = hrtime(true);
$body = new Parser()->toHtml($markdown);
$ms = (hrtime(true) - $start) / 1e6;

$outDir = $root . '/examples/output';
if (! is_dir($outDir)) {
    mkdir($outDir, 0o777, true);
}
$outFile = $outDir . '/' . pathinfo($input, PATHINFO_FILENAME) . '.html';

$title = htmlspecialchars(basename($input), ENT_QUOTES);
$css =
    'body{max-width:48rem;margin:3rem auto;padding:0 1rem;font:16px/1.65 system-ui,-apple-system,sans-serif;color:#1a1a1a}'
    . 'pre{background:#0d1117;color:#e6edf3;padding:1rem;border-radius:8px;overflow:auto}'
    . 'code{background:#f0f0f0;padding:.1em .3em;border-radius:4px;font-size:.9em}pre code{background:none;padding:0}'
    . 'table{border-collapse:collapse}th,td{border:1px solid #d0d0d0;padding:.4em .8em}'
    . 'blockquote{border-left:4px solid #ddd;margin:0;padding-left:1rem;color:#555}'
    . 'img{max-width:100%}h1,h2,h3{line-height:1.25}';

$page =
    "<!doctype html>\n<html lang=\"en\"><head><meta charset=\"utf-8\">"
    . '<meta name="viewport" content="width=device-width,initial-scale=1">'
    . "<title>{$title}</title><style>{$css}</style></head><body>\n{$body}\n</body></html>\n";

file_put_contents($outFile, $page);

$bytes = strlen($markdown);
printf("Rendered %s (%.1f KB) in %.2f ms  →  %.1f MB/s\n", basename($input), $bytes / 1024, $ms, ($bytes / 1e6) / ($ms / 1000));
printf("Wrote %s (%.1f KB of HTML)\n", $outFile, strlen($page) / 1024);
echo "Open it in a browser to see the rendered page.\n";

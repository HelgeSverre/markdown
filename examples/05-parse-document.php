<?php

declare(strict_types=1);

/**
 * Document-aware parsing: front matter, heading anchors, and a table of
 * contents in one call — plus the dialect / safe-HTML / XHTML options.
 *
 *   php examples/05-parse-document.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use HelgeSverre\Markdown\Data\Dialect;
use HelgeSverre\Markdown\Markdown;
use HelgeSverre\Markdown\Parser;

$doc = <<<'MD'
    ---
    title: Getting Started
    author: Helge Sverre
    tags: [php, markdown, ffi]
    ---
    # Introduction

    Welcome to **helgesverre/markdown**. Bare links autolink: www.example.com.

    ## Installation

    Run `composer require helgesverre/markdown`.

    ## Usage

    ### Basic usage

    Call `Markdown::toHtml()` for the fast path.

    ### Basic usage

    A duplicate heading — watch the slug get de-duplicated.
    MD;

$result = Markdown::parse($doc);

echo "=== Front matter (parsed from the YAML block) ===\n";
foreach ($result->frontmatter as $key => $value) {
    printf("  %-8s %s\n", $key . ':', is_array($value) ? implode(', ', $value) : $value);
}

echo "\n=== Table of contents (level · text · slug) ===\n";
foreach ($result->toc as $entry) {
    printf("  %s- %s  →  #%s\n", str_repeat('  ', $entry['level'] - 1), $entry['text'], $entry['slug']);
}

echo "\n=== Rendered HTML (headings carry id=\"…\"; front matter stripped) ===\n";
echo $result->html, "\n";

echo "\n=== Constructor options ===\n";
// safe: strip raw HTML from untrusted input (md4c MD_FLAG_NOHTML)
echo '  safe:   ', new Parser(safe: true)->toHtml('<b>raw</b> <i>html</i> stays text'), "\n";
// CommonMark dialect: GFM extensions off (no <del>, no autolink)
echo '  strict: ', new Parser(Dialect::CommonMark)->toHtml('~~strike~~ and www.x.com'), "\n";
// XHTML: self-closing void tags
echo '  xhtml:  ', new Parser(xhtml: true)->toHtml('over the line' . "\n\n---\n"), "\n";

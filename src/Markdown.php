<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

use HelgeSverre\Markdown\Data\ParsedMarkdown;

/**
 * Convenience facade for the common case.
 *
 *   Markdown::toHtml("# Hi");
 *
 * The underlying parser is created once and reused, so repeated calls don't
 * re-bind the library. For explicit lifecycle control, use Parser /
 * BatchParser directly.
 */
final class Markdown
{
    private static ?Parser $parser = null;

    private static ?BatchParser $batch = null;

    /** Render one Markdown string to GFM HTML. */
    public static function toHtml(string $markdown): string
    {
        return (self::$parser ??= new Parser())->toHtml($markdown);
    }

    /**
     * Render many Markdown documents at once across the native thread pool.
     *
     * @param  array<int, string>  $documents
     * @return array<int, string>  HTML, index-aligned with $documents
     */
    public static function toHtmlBatch(array $documents): array
    {
        return (self::$batch ??= new BatchParser())->toHtmlBatch($documents);
    }

    /**
     * Parse a full document: split front matter, anchor headings, and return a
     * ParsedMarkdown ({@see ParsedMarkdown::$html}, ::$frontmatter, ::$toc).
     */
    public static function parse(string $markdown): ParsedMarkdown
    {
        return (self::$parser ??= new Parser())->parse($markdown);
    }
}

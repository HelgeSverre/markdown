<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

/**
 * Convenience facade for the common case.
 *
 *   Markdown::toHtml("# Hi");
 *
 * The underlying FFI parser is created once and reused, so repeated calls don't
 * re-bind the library. For explicit lifecycle control, use FfiParser /
 * FfiBatchParser directly.
 */
final class Markdown
{
    private static ?FfiParser $parser = null;

    private static ?FfiBatchParser $batch = null;

    /** Render one Markdown string to GFM HTML. */
    public static function toHtml(string $markdown): string
    {
        return (self::$parser ??= new FfiParser())->toHtml($markdown);
    }

    /**
     * Render many Markdown documents at once across the native thread pool.
     *
     * @param  array<int, string>  $documents
     * @return array<int, string>  HTML, index-aligned with $documents
     */
    public static function toHtmlBatch(array $documents): array
    {
        return (self::$batch ??= new FfiBatchParser())->toHtmlBatch($documents);
    }

    /**
     * Parse a full document: split front matter, anchor headings, and return a
     * ParsedMarkdown ({@see ParsedMarkdown::$html}, ::$frontmatter, ::$toc).
     */
    public static function parse(string $markdown): ParsedMarkdown
    {
        return (self::$parser ??= new FfiParser())->parse($markdown);
    }
}

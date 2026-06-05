<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

use Stringable;

/**
 * The result of FfiParser::parse() — rendered HTML plus the document metadata
 * md4c alone doesn't give you: parsed front matter and a heading table of
 * contents. Casting to string yields the HTML, so it drops into templates.
 */
final class ParsedMarkdown implements Stringable
{
    /**
     * @param  string  $html  Rendered HTML, with id="slug" anchors on headings.
     * @param  array<string, mixed>  $frontmatter  Parsed YAML front matter ([] if none).
     * @param  list<array{level: int, text: string, slug: string}>  $toc  Headings in document order.
     */
    public function __construct(
        public readonly string $html,
        public readonly array $frontmatter = [],
        public readonly array $toc = [],
    ) {}

    public function __toString(): string
    {
        return $this->html;
    }
}

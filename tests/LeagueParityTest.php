<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\FfiParser;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The honest correctness bar: our md4c output must be semantically equivalent
 * to league/commonmark's GFM output. We compare *visible text* (tags stripped)
 * so cosmetic md4c-vs-league differences (e.g. <hr> vs <hr />) don't matter,
 * but dropped or mangled content would.
 */
final class LeagueParityTest extends TestCase
{
    public static function samples(): iterable
    {
        yield 'headings + emphasis' => ["# A\n\n**b**, *i*, and `c`.\n"];
        yield 'nested lists' => ["- a\n- b\n  - nested\n\n1. one\n2. two\n"];
        yield 'list interrupting paragraph' => ["text:\n- a\n- b\n"];
        yield 'gfm table' => ["| h1 | h2 |\n|----|----|\n| a  | b  |\n"];
        yield 'blockquote + fenced code' => ["> quote\n\n```php\n\$x = 1;\n```\n"];
        yield 'gfm inline' => ["~~s~~ and a [link](http://example.com).\n"];
        yield 'autolink' => ["visit www.example.com or https://x.com today\n"];
        yield 'explicit link with url text' => ["[https://x.com/a](https://x.com/a)\n"];
    }

    #[Test]
    #[DataProvider('samples')]
    public function visible_text_matches_league_gfm(string $markdown): void
    {
        $fight = (new FfiParser())->toHtml($markdown);
        $league = (string) (new GithubFlavoredMarkdownConverter())->convert($markdown)->getContent();

        $this->assertSame($this->visibleText($league), $this->visibleText($fight));
    }

    #[Test]
    public function anchor_count_matches_league_on_a_link_heavy_document(): void
    {
        $markdown = "[a](http://a.com) and [b](http://b.com), bare https://c.com, "
            . "and [https://d.com/x](https://d.com/x).\n";

        $fight = (new FfiParser())->toHtml($markdown);
        $league = (string) (new GithubFlavoredMarkdownConverter())->convert($markdown)->getContent();

        $this->assertSame(substr_count($league, '<a '), substr_count($fight, '<a '));
    }

    private function visibleText(string $html): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));
    }
}

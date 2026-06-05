<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\Parser;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The honest correctness bar: our md4c output must be semantically equivalent
 * to league/commonmark's GFM output. The broad sample compares visible text so
 * cosmetic md4c-vs-league differences don't matter; focused tests below also
 * compare links, tables, and fenced-code structure so tag-level regressions
 * don't hide behind strip_tags().
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
        $ours = new Parser()->toHtml($markdown);
        $league = (string) new GithubFlavoredMarkdownConverter()
            ->convert($markdown)
            ->getContent();

        $this->assertSame($this->visibleText($league), $this->visibleText($ours));
    }

    #[Test]
    public function anchor_count_matches_league_on_a_link_heavy_document(): void
    {
        $markdown = '[a](http://a.com) and [b](http://b.com), bare https://c.com, ' . "and [https://d.com/x](https://d.com/x).\n";

        $ours = new Parser()->toHtml($markdown);
        $league = (string) new GithubFlavoredMarkdownConverter()
            ->convert($markdown)
            ->getContent();

        $this->assertSame(substr_count($league, '<a '), substr_count($ours, '<a '));
    }

    #[Test]
    public function link_targets_and_text_match_league_on_a_link_heavy_document(): void
    {
        $markdown = '[a](http://a.com) and [b](http://b.com), bare https://c.com, ' . "and [https://d.com/x](https://d.com/x).\n";

        $ours = new Parser()->toHtml($markdown);
        $league = (string) new GithubFlavoredMarkdownConverter()
            ->convert($markdown)
            ->getContent();

        $this->assertSame($this->links($league), $this->links($ours));
    }

    #[Test]
    public function table_structure_matches_league_gfm(): void
    {
        $markdown = "| h1 | h2 |\n|----|----|\n| a  | b  |\n| c  | d  |\n";

        $ours = new Parser()->toHtml($markdown);
        $league = (string) new GithubFlavoredMarkdownConverter()
            ->convert($markdown)
            ->getContent();

        $this->assertSame($this->tagCounts($league, ['table', 'thead', 'tbody', 'tr', 'th', 'td']), $this->tagCounts($ours, ['table', 'thead', 'tbody', 'tr', 'th', 'td']));
    }

    #[Test]
    public function fenced_code_structure_matches_league_gfm(): void
    {
        $markdown = "```php\n\$x = 1;\n```\n";

        $ours = new Parser()->toHtml($markdown);
        $league = (string) new GithubFlavoredMarkdownConverter()
            ->convert($markdown)
            ->getContent();

        $this->assertSame($this->tagCounts($league, ['pre', 'code']), $this->tagCounts($ours, ['pre', 'code']));
        $this->assertSame($this->visibleText($league), $this->visibleText($ours));
    }

    private function visibleText(string $html): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));
    }

    /**
     * @return list<array{href: string, text: string}>
     */
    private function links(string $html): array
    {
        preg_match_all('#<a\b[^>]*\bhref="([^"]*)"[^>]*>(.*?)</a>#s', $html, $matches, PREG_SET_ORDER);

        return array_map(
            fn (array $match): array => [
                'href' => html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5),
                'text' => $this->visibleText($match[2]),
            ],
            $matches,
        );
    }

    /**
     * @param list<string> $tags
     * @return array<string, int>
     */
    private function tagCounts(string $html, array $tags): array
    {
        $counts = [];
        foreach ($tags as $tag) {
            $counts[$tag] = substr_count($html, "<{$tag}");
        }

        return $counts;
    }
}

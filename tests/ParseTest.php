<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\Data\Dialect;
use HelgeSverre\Markdown\Data\ParsedMarkdown;
use HelgeSverre\Markdown\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Covers the document-aware parse() path: front matter, heading anchors, and
 * the table of contents — plus the dialect/safe/xhtml constructor options.
 */
final class ParseTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    #[Test]
    public function parse_extracts_front_matter_and_renders_the_body(): void
    {
        $result = $this->parser->parse("---\ntitle: Hello\ntags: [a, b]\n---\n# Body\n");

        $this->assertInstanceOf(ParsedMarkdown::class, $result);
        $this->assertSame('Hello', $result->frontmatter['title']);
        $this->assertSame(['a', 'b'], $result->frontmatter['tags']);
        $this->assertStringContainsString('<h1 id="body">Body</h1>', $result->html);
        $this->assertStringNotContainsString('---', $result->html);
    }

    #[Test]
    public function parse_without_front_matter_returns_empty_metadata(): void
    {
        $result = $this->parser->parse("# Just A Heading\n\ntext\n");

        $this->assertSame([], $result->frontmatter);
        $this->assertStringContainsString('<h1 id="just-a-heading">Just A Heading</h1>', $result->html);
    }

    #[Test]
    public function parse_builds_a_table_of_contents_with_dedup(): void
    {
        $result = $this->parser->parse("# Title\n\n## Notes\n\n## Notes\n");

        $this->assertSame(
            [
                ['level' => 1, 'text' => 'Title', 'slug' => 'title'],
                ['level' => 2, 'text' => 'Notes', 'slug' => 'notes'],
                ['level' => 2, 'text' => 'Notes', 'slug' => 'notes-1'],
            ],
            $result->toc,
        );
    }

    #[Test]
    public function parsed_markdown_stringifies_to_html(): void
    {
        $result = $this->parser->parse("# Hi\n");

        $this->assertSame($result->html, (string) $result);
    }

    #[Test]
    public function malformed_front_matter_is_treated_as_no_front_matter(): void
    {
        // Unbalanced YAML — tempest/markdown throws here; we degrade to [].
        $result = $this->parser->parse("---\n: : :\nnope\n---\n# H\n");

        $this->assertSame([], $result->frontmatter);
        $this->assertStringContainsString('<h1 id="h">H</h1>', $result->html);
    }

    #[Test]
    public function it_slugifies_headings_the_way_github_does(): void
    {
        $body = "# Hello World\n\n## Hello World\n\n### Café & Crème\n\n# 100% Done!\n";

        $slugs = array_column($this->parser->parse($body)->toc, 'slug');

        $this->assertSame(['hello-world', 'hello-world-1', 'caf-cr-me', '100-done'], $slugs);
    }

    #[Test]
    public function to_html_is_unchanged_by_the_anchor_feature(): void
    {
        // The fast path must stay pure: no id attributes, no front-matter strip.
        $this->assertSame('<h1>X</h1>', trim($this->parser->toHtml("# X\n")));
    }

    #[Test]
    public function commonmark_dialect_disables_gfm_extensions(): void
    {
        $parser = new Parser(Dialect::CommonMark);

        $this->assertStringNotContainsString('<del>', $parser->toHtml("~~x~~\n"));
        $this->assertStringNotContainsString('<table>', $parser->toHtml("| a | b |\n|---|---|\n| 1 | 2 |\n"));
    }

    #[Test]
    public function safe_mode_neutralizes_raw_html(): void
    {
        $parser = new Parser(safe: true);
        $html = $parser->toHtml("<script>alert(1)</script>\n");

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    #[Test]
    public function xhtml_mode_emits_self_closing_tags(): void
    {
        $parser = new Parser(xhtml: true);

        $this->assertStringContainsString('<hr />', $parser->toHtml("***\n"));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function slugEdgeCases(): iterable
    {
        yield 'only punctuation falls back to "section"' => ['!!!', 'section'];
        yield 'trailing punctuation is trimmed' => ['Sub-Section!', 'sub-section'];
        yield 'separator runs collapse to one hyphen' => ['a   ---   b', 'a-b'];
        yield 'non-ascii letters are folded out' => ['Café Crème', 'caf-cr-me'];
    }

    #[Test]
    #[DataProvider('slugEdgeCases')]
    public function it_slugifies_heading_edge_cases(string $heading, string $expectedSlug): void
    {
        $toc = $this->parser->parse("# {$heading}\n")->toc;

        $this->assertSame($expectedSlug, $toc[0]['slug']);
    }

    #[Test]
    public function toc_text_strips_tags_and_decodes_entities_but_the_slug_uses_neither(): void
    {
        $toc = $this->parser->parse("# A <em>B</em> & C\n")->toc;

        $this->assertSame('A B & C', $toc[0]['text']);
        $this->assertSame('a-b-c', $toc[0]['slug']);
    }

    #[Test]
    public function many_identical_headings_dedupe_in_linear_time(): void
    {
        // Guards the native hashmap de-dup: thousands of identical headings must
        // produce sequential suffixes quickly, not blow up super-linearly.
        $start = hrtime(true);
        $toc = $this->parser->parse(str_repeat("## Dup\n\n", 20_000))->toc;
        $elapsedMs = (hrtime(true) - $start) / 1e6;

        $this->assertCount(20_000, $toc);
        $this->assertSame('dup', $toc[0]['slug']);
        $this->assertSame('dup-1', $toc[1]['slug']);
        $this->assertSame('dup-19999', $toc[19_999]['slug']);
        $this->assertLessThan(2000, $elapsedMs, 'native de-dup is not linear');
    }
}

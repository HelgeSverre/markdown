<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\Parser;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Targeted coverage for the in-shim anchor-collapse pass — the riskiest code in
 * the project and the hot path we optimized.
 *
 * Subtlety worth recording: a plain `[url](url)` does NOT make md4c emit nested
 * anchors. The bug only fires when the link *text* contains `www.`, because then
 * md4c's www-autolinker re-wraps the text inside the explicit link, producing
 * `<a href="…"><a href="…">…</a></a>`. Every "collapse works" assertion below
 * uses that real trigger, so it actually fails if the pass is disabled — unlike
 * a plain-`[url](url)` assertion, which passes whether or not collapse runs.
 */
final class AnchorCollapseTest extends TestCase
{
    private const TRIGGER = '[https://www.youtube.com/watch?v=abc](https://www.youtube.com/watch?v=abc)';

    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    #[Test]
    public function it_flattens_a_generated_nested_autolink_to_a_single_anchor(): void
    {
        $html = $this->parser->toHtml(self::TRIGGER . "\n");

        $this->assertSame(
            "<p><a href=\"https://www.youtube.com/watch?v=abc\">https://www.youtube.com/watch?v=abc</a></p>\n",
            $html,
        );
    }

    #[Test]
    public function it_flattens_the_trigger_inside_a_list_item(): void
    {
        // The exact shape that appears in the tempest-docs corpus.
        $html = $this->parser->toHtml('- ' . self::TRIGGER . "\n");

        $this->assertSame(1, substr_count($html, '<a '), 'exactly one anchor survives');
        $this->assertNoNestedAnchor($html);
    }

    #[Test]
    public function it_flattens_many_triggers_and_matches_league_anchor_count(): void
    {
        $doc = "# Links\n\n";
        for ($i = 0; $i < 25; $i++) {
            $doc .= "- [https://www.site{$i}.com/watch?v={$i}](https://www.site{$i}.com/watch?v={$i})\n";
        }
        $doc .= "\nAnd a normal [link](https://plain.example/p) plus bare https://www.bare.com/x.\n";

        $html = $this->parser->toHtml($doc);
        $league = $this->league($doc);

        $this->assertNoNestedAnchor($html);
        $this->assertSame(substr_count($html, '<a '), substr_count($html, '</a>'), 'balanced anchors');
        $this->assertSame(
            substr_count($league, '<a '),
            substr_count($html, '<a '),
            'anchor count must match league after collapse',
        );
    }

    #[Test]
    public function it_flattens_triggers_in_varied_block_contexts(): void
    {
        $doc = '> ' . self::TRIGGER . "\n\n" . "| a | b |\n|---|---|\n| " . self::TRIGGER . " | x |\n\n" . '1. ' . self::TRIGGER . "\n";

        $html = $this->parser->toHtml($doc);
        $league = $this->league($doc);

        $this->assertNoNestedAnchor($html);
        $this->assertSame(substr_count($league, '<a '), substr_count($html, '<a '));
    }

    #[Test]
    public function a_plain_url_link_is_already_flat_and_left_intact(): void
    {
        // Documents the boundary: no `www.` in the text => md4c never nests, so
        // there is nothing for the pass to do. (Guards against a future "trigger"
        // that quietly stops triggering.)
        $html = $this->parser->toHtml("[https://stitcher.io/blog/x](https://stitcher.io/blog/x)\n");

        $this->assertSame(
            "<p><a href=\"https://stitcher.io/blog/x\">https://stitcher.io/blog/x</a></p>\n",
            $html,
        );
    }

    #[Test]
    public function it_leaves_user_supplied_raw_nested_anchors_untouched(): void
    {
        // The pass must only flatten md4c's GENERATED autolink nesting, never
        // raw HTML the author wrote. (A too-aggressive collapse would fail here.)
        $html = $this->parser->toHtml("<a href=\"outer\"><a href=\"inner\">x</a></a>\n");

        $this->assertSame("<p><a href=\"outer\"><a href=\"inner\">x</a></a></p>\n", $html);
    }

    private function assertNoNestedAnchor(string $html): void
    {
        $this->assertSame(
            0,
            preg_match('#<a\b[^>]*>(?:(?!</a>).)*?<a\b#s', $html),
            'a generated <a> was left nested inside another <a>',
        );
    }

    private function league(string $markdown): string
    {
        return (string) new GithubFlavoredMarkdownConverter()
            ->convert($markdown)
            ->getContent();
    }
}

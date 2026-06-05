<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\Ffi\Library;
use HelgeSverre\Markdown\Parser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    #[Test]
    public function it_renders_basic_commonmark(): void
    {
        $html = $this->parser->toHtml("# Title\n\nA paragraph.\n");

        $this->assertStringContainsString('<h1>Title</h1>', $html);
        $this->assertStringContainsString('<p>A paragraph.</p>', $html);
    }

    #[Test]
    public function it_renders_a_list_that_interrupts_a_paragraph(): void
    {
        // The exact CommonMark case tempest/markdown renders incorrectly.
        $html = $this->parser->toHtml("A list:\n- one\n- two\n");

        $this->assertStringContainsString('<li>one</li>', $html);
        $this->assertStringContainsString('<li>two</li>', $html);
    }

    #[Test]
    public function it_renders_gfm_tables(): void
    {
        $html = $this->parser->toHtml("| a | b |\n|---|---|\n| 1 | 2 |\n");

        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('<th>a</th>', $html);
        $this->assertStringContainsString('<td>1</td>', $html);
    }

    #[Test]
    public function it_uses_semantic_del_for_strikethrough(): void
    {
        $this->assertStringContainsString('<del>gone</del>', $this->parser->toHtml("~~gone~~\n"));
    }

    #[Test]
    public function it_renders_task_lists(): void
    {
        $html = $this->parser->toHtml("- [x] done\n- [ ] todo\n");

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('checked', $html);
    }

    #[Test]
    public function it_autolinks_bare_urls(): void
    {
        $this->assertStringContainsString(
            'href="http://www.example.com"',
            $this->parser->toHtml("see www.example.com\n"),
        );
    }

    #[Test]
    public function it_collapses_anchors_nested_inside_anchors(): void
    {
        // md4c would otherwise emit <a><a>...</a></a> when the link text is a
        // bare autolinkable URL. The C shim's collapse pass fixes that.
        $html = $this->parser->toHtml("[https://x.com/a](https://x.com/a)\n");

        $this->assertSame("<p><a href=\"https://x.com/a\">https://x.com/a</a></p>\n", $html);
        $this->assertStringNotContainsString('<a href="https://x.com/a"><a ', $html);
        $this->assertSame(1, substr_count($html, '<a '), 'exactly one anchor');
    }

    #[Test]
    public function it_does_not_rewrite_user_supplied_raw_nested_anchors(): void
    {
        $html = $this->parser->toHtml("<a href=\"outer\"><a href=\"inner\">x</a></a>\n");

        $this->assertSame("<p><a href=\"outer\"><a href=\"inner\">x</a></a></p>\n", $html);
    }

    #[Test]
    public function it_returns_an_empty_string_for_empty_input(): void
    {
        $this->assertSame('', trim($this->parser->toHtml('')));
    }

    #[Test]
    public function it_preserves_multibyte_text(): void
    {
        $this->assertStringContainsString('café 日本語 🎉', $this->parser->toHtml("# café 日本語 🎉\n"));
    }

    #[Test]
    public function it_resolves_a_library_path_that_exists(): void
    {
        $this->assertFileExists(Library::path());
    }
}

<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\Markdown;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MarkdownFacadeTest extends TestCase
{
    #[Test]
    public function to_html_renders_gfm(): void
    {
        $html = Markdown::toHtml("# Hi\n\n~~x~~\n");

        $this->assertStringContainsString('<h1>Hi</h1>', $html);
        $this->assertStringContainsString('<del>x</del>', $html);
    }

    #[Test]
    public function to_html_batch_renders_in_order(): void
    {
        $out = Markdown::toHtmlBatch(["# A\n", "# B\n", "# C\n"]);

        $this->assertCount(3, $out);
        $this->assertStringContainsString('<h1>A</h1>', $out[0]);
        $this->assertStringContainsString('<h1>B</h1>', $out[1]);
        $this->assertStringContainsString('<h1>C</h1>', $out[2]);
    }

    #[Test]
    public function repeated_calls_are_stable(): void
    {
        $this->assertSame(Markdown::toHtml("# X\n"), Markdown::toHtml("# X\n"));
    }
}

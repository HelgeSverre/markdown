<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\HeadingAnchors;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Pure-PHP unit tests for the slug/TOC logic — no FFI involved.
 */
final class HeadingAnchorsTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function slugCases(): array
    {
        return [
            'plain words' => ['Hello World', 'hello-world'],
            'punctuation dropped' => ['Sub-Section!', 'sub-section'],
            'leading/trailing trimmed' => ['  spaced  ', 'spaced'],
            'digits kept' => ['Step 2: Go', 'step-2-go'],
            'non-ascii folded out' => ['Café Crème', 'caf-cr-me'],
            'empty becomes section' => ['', 'section'],
            'only punctuation' => ['!!!', 'section'],
            'collapses runs' => ['a   ---   b', 'a-b'],
        ];
    }

    #[Test]
    #[DataProvider('slugCases')]
    public function it_slugifies_like_the_c_pass(string $input, string $expected): void
    {
        $this->assertSame($expected, HeadingAnchors::slugify($input));
    }

    #[Test]
    public function it_injects_ids_and_dedupes(): void
    {
        $html = "<h1>Intro</h1>\n<h2>Setup</h2>\n<h2>Setup</h2>\n";
        [$out, $toc] = HeadingAnchors::process($html);

        $this->assertStringContainsString('<h1 id="intro">Intro</h1>', $out);
        $this->assertStringContainsString('<h2 id="setup">Setup</h2>', $out);
        $this->assertStringContainsString('<h2 id="setup-1">Setup</h2>', $out);

        $this->assertSame(
            [
                ['level' => 1, 'text' => 'Intro', 'slug' => 'intro'],
                ['level' => 2, 'text' => 'Setup', 'slug' => 'setup'],
                ['level' => 2, 'text' => 'Setup', 'slug' => 'setup-1'],
            ],
            $toc,
        );
    }

    #[Test]
    public function many_identical_headings_dedupe_in_linear_time(): void
    {
        // Guards against super-linear dedup: thousands of identical headings
        // must process quickly and produce sequential suffixes.
        $html = str_repeat("<h2>Dup</h2>\n", 20_000);

        $start = hrtime(true);
        [$out, $toc] = HeadingAnchors::process($html);
        $elapsedMs = (hrtime(true) - $start) / 1e6;

        $this->assertCount(20_000, $toc);
        $this->assertSame('dup', $toc[0]['slug']);
        $this->assertSame('dup-1', $toc[1]['slug']);
        $this->assertSame('dup-19999', $toc[19_999]['slug']);
        $this->assertStringContainsString('<h2 id="dup-19999">Dup</h2>', $out);
        $this->assertLessThan(2000, $elapsedMs, 'dedup is not linear');
    }

    #[Test]
    public function it_slugifies_from_visible_text_only(): void
    {
        // Tags and entities inside the heading must not leak into the slug.
        $this->assertSame('a-b', HeadingAnchors::slugify('A <em>B</em>'));
        $this->assertSame('a-b', HeadingAnchors::slugify('A &amp; B'));
    }
}

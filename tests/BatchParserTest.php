<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\BatchParser;
use HelgeSverre\Markdown\Parser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class BatchParserTest extends TestCase
{
    #[Test]
    public function batch_output_matches_sequential(): void
    {
        $docs = [
            "# One\n\n- a\n- b\n",
            "| h |\n|---|\n| v |\n",
            "[https://a.com/x](https://a.com/x)\n", // exercises the anchor-collapse fix
            "~~struck~~ and `code`\n",
            '', // empty doc in the middle
            "final **doc**\n",
        ];

        $single = new Parser();
        $expected = array_map(static fn (string $d): string => $single->toHtml($d), $docs);
        $actual = new BatchParser()->toHtmlBatch($docs);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function empty_batch_returns_empty_array(): void
    {
        $this->assertSame([], new BatchParser()->toHtmlBatch([]));
    }

    #[Test]
    public function it_preserves_order_across_many_documents(): void
    {
        $docs = [];
        for ($i = 0; $i < 250; $i++) {
            $docs[] = "# Doc {$i}\n\nbody {$i}\n";
        }

        $out = new BatchParser()->toHtmlBatch($docs);

        $this->assertCount(250, $out);
        foreach ($out as $i => $html) {
            $this->assertStringContainsString("<h1>Doc {$i}</h1>", $html);
        }
    }

    #[Test]
    public function single_document_batch_works(): void
    {
        $out = new BatchParser()->toHtmlBatch(["# Only\n"]);

        $this->assertCount(1, $out);
        $this->assertStringContainsString('<h1>Only</h1>', $out[0]);
    }

    #[Test]
    public function it_falls_back_to_sequential_when_the_batch_symbol_is_unavailable(): void
    {
        $docs = [
            "# One\n\n- a\n- b\n",
            '', // empty doc in the middle
            "[https://a.com/x](https://a.com/x)\n", // exercises the anchor-collapse fix
            "final **doc**\n",
        ];

        $batch = new BatchParser();

        // Simulate a build whose shim predates md2html_batch by forcing the
        // fallback flag; the output must still match per-doc sequential renders.
        new ReflectionProperty(BatchParser::class, 'batchAvailable')->setValue($batch, false);

        $single = new Parser();
        $expected = array_map(static fn (string $d): string => $single->toHtml($d), $docs);

        $this->assertSame($expected, $batch->toHtmlBatch($docs));
    }

    #[Test]
    public function batch_matches_sequential_on_real_corpus_documents(): void
    {
        $docs = [];
        foreach (['commonmark-spec.md', 'tempest-docs.md', 'synthetic/doc-2kb.md', 'synthetic/doc-16kb.md', 'synthetic/doc-128kb.md'] as $rel) {
            $path = dirname(__DIR__) . '/corpus/' . $rel;
            if (is_file($path)) {
                $docs[] = (string) file_get_contents($path);
            }
        }
        $docs[] = ''; // an empty document wedged among the large ones

        $this->assertGreaterThan(1, count($docs), 'expected at least one corpus file present');

        $single = new Parser();
        $expected = array_map(static fn (string $d): string => $single->toHtml($d), $docs);

        // Packs hundreds of KB into one buffer across the pthread pool; every
        // slice must match the document rendered on its own.
        $this->assertSame($expected, new BatchParser()->toHtmlBatch($docs));
    }

    #[Test]
    public function batch_matches_sequential_on_a_large_varied_set(): void
    {
        mt_srand(0x1234);
        $docs = [];
        for ($i = 0; $i < 300; $i++) {
            $docs[] = $this->variedDoc($i);
        }

        $single = new Parser();
        $expected = array_map(static fn (string $d): string => $single->toHtml($d), $docs);

        $this->assertSame($expected, new BatchParser()->toHtmlBatch($docs));
    }

    /** A document whose size/content varies by index: empty, multibyte, link-heavy, large, tables, nesting. */
    private function variedDoc(int $i): string
    {
        return match ($i % 6) {
            0 => '', // empty doc interleaved with real ones
            1 => "# café 日本語 🎉 {$i}\n\nmultibyte **bold**, *em*, and `code`\n",
            2 => "[https://x.com/{$i}](https://x.com/{$i}) and bare www.example.com/{$i}\n", // collapse trigger
            3 => str_repeat("para **{$i}** with [l](http://a.test/{$i}) ~~s~~\n\n", mt_rand(1, 40)), // larger
            4 => "| a | b |\n|---|---|\n| {$i} | " . str_repeat('x', mt_rand(0, 50)) . " |\n",
            default => "- one {$i}\n- two\n  - nested\n\n> quote {$i}\n",
        };
    }
}

<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\FfiBatchParser;
use HelgeSverre\Markdown\FfiParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FfiBatchParserTest extends TestCase
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

        $single = new FfiParser();
        $expected = array_map(static fn (string $d): string => $single->toHtml($d), $docs);
        $actual = new FfiBatchParser()->toHtmlBatch($docs);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function empty_batch_returns_empty_array(): void
    {
        $this->assertSame([], new FfiBatchParser()->toHtmlBatch([]));
    }

    #[Test]
    public function it_preserves_order_across_many_documents(): void
    {
        $docs = [];
        for ($i = 0; $i < 250; $i++) {
            $docs[] = "# Doc {$i}\n\nbody {$i}\n";
        }

        $out = new FfiBatchParser()->toHtmlBatch($docs);

        $this->assertCount(250, $out);
        foreach ($out as $i => $html) {
            $this->assertStringContainsString("<h1>Doc {$i}</h1>", $html);
        }
    }

    #[Test]
    public function single_document_batch_works(): void
    {
        $out = new FfiBatchParser()->toHtmlBatch(["# Only\n"]);

        $this->assertCount(1, $out);
        $this->assertStringContainsString('<h1>Only</h1>', $out[0]);
    }
}

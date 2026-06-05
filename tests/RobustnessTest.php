<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FFI means a C bug becomes a PHP segfault. These tests throw hostile input at
 * the parser and assert it always comes back with a string — never a crash,
 * never a truncation, never an unbounded leak.
 */
final class RobustnessTest extends TestCase
{
    public static function hostileInputs(): iterable
    {
        yield 'empty' => [''];
        yield 'one megabyte of hashes' => [str_repeat('#', 1_000_000)];
        yield 'deep blockquote nesting' => [str_repeat('> ', 5_000) . "deep\n"];
        yield 'deep bracket nesting' => [str_repeat('[', 10_000) . 'x' . str_repeat(']', 10_000) . "\n"];
        yield 'embedded nul bytes' => ["before\x00after\n"];
        yield 'invalid utf-8' => ["\xff\xfe bad \xc3\x28 bytes\n"];
        yield 'no trailing newline' => ['# heading with no newline'];
        yield 'one very long line' => [str_repeat('word ', 200_000)];
        yield 'raw html block' => ["<div><span>raw</span></div>\n"];
        // Larger samples that specifically hammer the in-shim collapse pass and
        // the input-size buffer seeding at megabyte scale.
        // `https://www.` in the link text is what actually makes md4c nest
        // anchors — a plain [url](url) does not (see AnchorCollapseTest).
        yield 'megabyte of collapse triggers' => [str_repeat('[https://www.x.com/a](https://www.x.com/a) ', 50_000)];
        yield 'megabyte of mixed gfm' => [str_repeat("# H\n\n- a\n- b\n\n| x | y |\n|---|---|\n| 1 | 2 |\n\n~~s~~ [l](http://a.test/b) www.x.io\n\n", 15_000)];
        yield 'autolink soup' => [str_repeat('www.a.com https://b.com/x http://c.org/y ', 30_000)];
    }

    #[Test]
    #[DataProvider('hostileInputs')]
    public function it_never_crashes_and_keeps_anchors_well_formed(string $input): void
    {
        $html = new Parser()->toHtml($input);

        $this->assertIsString($html);
        // The in-place collapse pass must never corrupt the tag stream, even on
        // adversarial input: every opening <a has a matching close, and no
        // generated anchor is left nested inside another (the bug it fixes).
        $this->assertSame(
            substr_count($html, '<a '),
            substr_count($html, '</a>'),
            'unbalanced <a>/</a>',
        );
        // O(n) scan instead of a backtracking regex so it stays correct (and
        // fast) on megabyte-scale outputs where a lazy regex can blow the PCRE
        // backtrack limit and falsely report "no match".
        $this->assertFalse($this->hasNestedAnchor($html), 'generated <a> nested inside <a>');
    }

    /** True if any anchor opens while already inside another anchor. */
    private function hasNestedAnchor(string $html): bool
    {
        $depth = 0;
        $pos = 0;
        while (true) {
            $open = strpos($html, '<a ', $pos);
            $close = strpos($html, '</a>', $pos);
            if ($open === false && $close === false) {
                return false;
            }
            if ($open !== false && ($close === false || $open < $close)) {
                if ($depth >= 1) {
                    return true; // an <a opened before the previous one closed
                }
                $depth++;
                $pos = $open + 3;
            } else {
                if ($depth > 0) {
                    $depth--;
                }
                $pos = $close + 4;
            }
        }
    }

    #[Test]
    public function embedded_nul_bytes_are_not_truncated(): void
    {
        // PHP's strlen() is binary-safe, so the (ptr, len) FFI path passes the
        // entire input through md4c — nothing is cut at the first NUL.
        $html = new Parser()->toHtml("alpha\x00omega\n");

        $this->assertStringContainsString('alpha', $html);
        $this->assertStringContainsString('omega', $html);
    }

    #[Test]
    public function it_does_not_leak_php_memory_across_many_renders(): void
    {
        $parser = new Parser();
        $doc = "# H\n\n- a\n- b\n\n[x](http://y.com/z)\n\n| a |\n|---|\n| 1 |\n";

        // Warm the Zend allocator's bundle to steady state first.
        for ($i = 0; $i < 2_000; $i++) {
            $parser->toHtml($doc);
        }

        $before = memory_get_usage(true);
        for ($i = 0; $i < 50_000; $i++) {
            $parser->toHtml($doc);
        }
        $after = memory_get_usage(true);

        // A missing free() would balloon this; we tolerate one allocator chunk.
        $this->assertLessThanOrEqual(2 * 1024 * 1024, $after - $before);
    }
}

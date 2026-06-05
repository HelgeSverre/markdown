<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\FfiParser;
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
    }

    #[Test]
    #[DataProvider('hostileInputs')]
    public function it_never_crashes_on_hostile_input(string $input): void
    {
        $html = new FfiParser()->toHtml($input);

        $this->assertIsString($html);
    }

    #[Test]
    public function embedded_nul_bytes_are_not_truncated(): void
    {
        // PHP's strlen() is binary-safe, so the (ptr, len) FFI path passes the
        // entire input through md4c — nothing is cut at the first NUL.
        $html = new FfiParser()->toHtml("alpha\x00omega\n");

        $this->assertStringContainsString('alpha', $html);
        $this->assertStringContainsString('omega', $html);
    }

    #[Test]
    public function it_does_not_leak_php_memory_across_many_renders(): void
    {
        $parser = new FfiParser();
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

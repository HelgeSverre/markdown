<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\Parser;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Property-based fuzzing against the league oracle.
 *
 * A seeded generator builds hundreds of randomized GFM documents from the full
 * block + inline vocabulary (headings, lists, tables, blockquotes, fenced code,
 * links, autolinks, the [url](url) auto-link-collapse trigger, and escaped raw
 * HTML inside code). For every document four invariants must hold:
 *
 *   1. visible text matches league GFM   (semantic correctness)
 *   2. anchor count matches league       (the collapse pass is correct at scale)
 *   3. <a>/</a> stay balanced            (no corruption from the in-place pass)
 *   4. no generated <a> nested in an <a> (the bug the collapse pass fixes)
 *
 * The seed is fixed, so a failure is fully reproducible and prints the exact
 * offending document.
 */
final class FuzzParityTest extends TestCase
{
    /** Fixed so runs are deterministic and failures reproduce exactly. */
    private const SEED = 0xC0_FFEE;

    private const DOCUMENTS = 200;

    private const WORDS = [
        'alpha',
        'bravo',
        'charlie',
        'delta',
        'echo',
        'foxtrot',
        'tango',
        'lorem',
        'ipsum',
        'dolor',
        'sit',
        'amet',
        'parser',
        'markdown',
        'render',
        'token',
    ];

    private const URLS = [
        'https://example.com/a',
        'http://test.org/p/q',
        'https://foo.io/x',
        'http://a.example/path',
        'https://docs.site.net/page',
        // `https://www.` URLs: when one is used as a link's *text* (the [url](url)
        // case below) md4c's www-autolinker fires inside the link and emits the
        // nested <a><a> that the collapse pass must flatten. Without these the
        // collapse invariant would pass vacuously.
        'https://www.youtube.com/watch?v=abc',
        'https://www.example.org/path',
        'http://www.test.net/q',
        'https://www.site.io/a/b',
    ];

    private const AUTOLINKS = [
        'https://example.com',
        'http://test.org/p',
        'www.example.com',
        'www.site.io/x',
    ];

    #[Test]
    public function generated_documents_match_league_and_collapse_anchors_cleanly(): void
    {
        $parser = new Parser();
        $league = new GithubFlavoredMarkdownConverter();
        mt_srand(self::SEED);

        for ($n = 0; $n < self::DOCUMENTS; $n++) {
            $doc = $this->randomDocument();
            $ours = $parser->toHtml($doc);
            $ref = (string) $league->convert($doc)->getContent();
            $ctx = "document #{$n} (seed 0x" . dechex(self::SEED) . "):\n---\n{$doc}\n---";

            $this->assertSame(
                $this->visibleText($ref),
                $this->visibleText($ours),
                "visible text diverged from league — {$ctx}",
            );
            $this->assertSame(
                substr_count($ref, '<a '),
                substr_count($ours, '<a '),
                "anchor count diverged from league — {$ctx}",
            );
            $this->assertSame(
                substr_count($ours, '<a '),
                substr_count($ours, '</a>'),
                "unbalanced <a>/</a> — {$ctx}",
            );
            $this->assertSame(
                0,
                preg_match('#<a\b[^>]*>(?:(?!</a>).)*?<a\b#s', $ours),
                "generated <a> nested inside <a> — {$ctx}",
            );
        }
    }

    /** Sanity-check the invariant detector itself can fire (so #4 is not vacuous). */
    #[Test]
    public function the_nested_anchor_detector_actually_detects_nesting(): void
    {
        $nested = '<p><a href="o"><a href="i">x</a></a></p>';
        $flat = '<p><a href="o">x</a> <a href="i">y</a></p>';

        $this->assertSame(1, preg_match('#<a\b[^>]*>(?:(?!</a>).)*?<a\b#s', $nested));
        $this->assertSame(0, preg_match('#<a\b[^>]*>(?:(?!</a>).)*?<a\b#s', $flat));
    }

    private function randomDocument(): string
    {
        $blocks = [];
        $count = mt_rand(3, 12);
        for ($i = 0; $i < $count; $i++) {
            $blocks[] = $this->randomBlock();
        }

        return implode("\n\n", $blocks) . "\n";
    }

    private function randomBlock(): string
    {
        return match (mt_rand(0, 8)) {
            0 => str_repeat('#', mt_rand(1, 6)) . ' ' . $this->inline(),
            1 => $this->inline() . ' ' . $this->inline(),
            2 => $this->list('- '),
            3 => $this->list('1. '),
            4 => $this->table(),
            5 => '> ' . $this->inline(),
            6 => $this->fencedCode(),
            7 => '---',
            default => $this->taskList(),
        };
    }

    private function list(string $marker): string
    {
        $lines = [];
        $items = mt_rand(2, 5);
        for ($i = 0; $i < $items; $i++) {
            $lines[] = $marker . $this->inline();
            if ($this->chance(25)) {
                $lines[] = '  ' . $marker . $this->inline(); // one nested level
            }
        }

        return implode("\n", $lines);
    }

    private function taskList(): string
    {
        $lines = [];
        $items = mt_rand(2, 4);
        for ($i = 0; $i < $items; $i++) {
            $lines[] = '- [' . ($this->chance(50) ? 'x' : ' ') . '] ' . $this->inline();
        }

        return implode("\n", $lines);
    }

    private function table(): string
    {
        $cols = mt_rand(2, 3);
        $header = $delim = '|';
        for ($c = 0; $c < $cols; $c++) {
            $header .= ' ' . $this->cell() . ' |';
            $delim .= ' --- |';
        }
        $rows = [$header, $delim];
        $bodyRows = mt_rand(1, 4);
        for ($r = 0; $r < $bodyRows; $r++) {
            $row = '|';
            for ($c = 0; $c < $cols; $c++) {
                $row .= ' ' . $this->cell() . ' |';
            }
            $rows[] = $row;
        }

        return implode("\n", $rows);
    }

    private function fencedCode(): string
    {
        $lang = $this->chance(50) ? 'php' : '';
        $lines = [];
        $n = mt_rand(1, 3);
        for ($i = 0; $i < $n; $i++) {
            // Include literal HTML so escaping (and "escaped <a is not a tag")
            // is exercised; never a backtick fence inside.
            $lines[] = $this->pick(self::WORDS) . ' <a> </div> ' . $this->pick(self::WORDS);
        }

        return "```{$lang}\n" . implode("\n", $lines) . "\n```";
    }

    /** A short, pipe-free, newline-free inline run safe for a table cell. */
    private function cell(): string
    {
        return match (mt_rand(0, 3)) {
            0 => $this->pick(self::WORDS),
            1 => '**' . $this->pick(self::WORDS) . '**',
            2 => '`' . $this->pick(self::WORDS) . '`',
            default => '[' . $this->pick(self::WORDS) . '](' . $this->pick(self::URLS) . ')',
        };
    }

    private function inline(): string
    {
        $parts = [];
        $n = mt_rand(2, 6);
        for ($i = 0; $i < $n; $i++) {
            $parts[] = match (mt_rand(0, 7)) {
                0 => $this->pick(self::WORDS),
                1 => '**' . $this->pick(self::WORDS) . '**',
                2 => '*' . $this->pick(self::WORDS) . '*',
                3 => '`' . $this->pick(self::WORDS) . '`',
                4 => '~~' . $this->pick(self::WORDS) . '~~',
                5 => '[' . $this->pick(self::WORDS) . '](' . $this->pick(self::URLS) . ')',
                6 => $this->pick(self::AUTOLINKS), // bare autolink
                default => '[' . ($u = $this->pick(self::URLS)) . '](' . $u . ')', // collapse trigger
            };
        }

        return implode(' ', $parts);
    }

    private function pick(array $pool): string
    {
        return $pool[mt_rand(0, count($pool) - 1)];
    }

    private function chance(int $percent): bool
    {
        return mt_rand(1, 100) <= $percent;
    }

    private function visibleText(string $html): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));
    }
}

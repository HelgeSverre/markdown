<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\Parser;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Differential correctness on whole, real documents — not hand-picked snippets.
 *
 * Every committed corpus file is rendered by both this parser and league's GFM
 * converter; the visible text and the anchor balance (the project's guaranteed
 * parity bar) must match. This is the test most likely to catch a regression
 * that toy-sized unit snippets sail straight past.
 */
final class CorpusParityTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function corpusFiles(): iterable
    {
        yield 'commonmark-spec (~169 KB)' => ['commonmark-spec.md'];
        yield 'tempest-docs (~258 KB)' => ['tempest-docs.md'];
        yield 'synthetic 2 KB' => ['synthetic/doc-2kb.md'];
        yield 'synthetic 16 KB' => ['synthetic/doc-16kb.md'];
        yield 'synthetic 128 KB' => ['synthetic/doc-128kb.md'];
    }

    #[Test]
    #[DataProvider('corpusFiles')]
    public function visible_text_matches_league_on_whole_documents(string $relPath): void
    {
        $markdown = $this->loadCorpus($relPath);

        $ours = new Parser()->toHtml($markdown);
        $league = $this->league($markdown);

        $this->assertSame($this->visibleText($league), $this->visibleText($ours));
    }

    #[Test]
    #[DataProvider('corpusFiles')]
    public function anchor_count_matches_league_and_stays_balanced(string $relPath): void
    {
        $markdown = $this->loadCorpus($relPath);

        $ours = new Parser()->toHtml($markdown);
        $league = $this->league($markdown);

        // The in-shim collapse pass exists precisely so this holds at scale.
        $this->assertSame(
            substr_count($league, '<a '),
            substr_count($ours, '<a '),
            'anchor count diverged from league',
        );
        // Our own output must never leave an unbalanced anchor.
        $this->assertSame(
            substr_count($ours, '<a '),
            substr_count($ours, '</a>'),
            'unbalanced <a>/</a> in output',
        );
    }

    private function loadCorpus(string $relPath): string
    {
        $path = dirname(__DIR__) . '/corpus/' . $relPath;
        if (! is_file($path)) {
            $this->markTestSkipped("corpus file not present: {$relPath}");
        }

        return (string) file_get_contents($path);
    }

    private function league(string $markdown): string
    {
        return (string) new GithubFlavoredMarkdownConverter()
            ->convert($markdown)
            ->getContent();
    }

    private function visibleText(string $html): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));
    }
}

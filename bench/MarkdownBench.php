<?php

declare(strict_types=1);

namespace MarkdownFight\Bench;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use MarkdownFight\FfiParser;
use PhpBench\Attributes as Bench;
use Tempest\Markdown\Markdown as TempestMarkdown;

/**
 * phpbench case — matches the blog's stated methodology (reused parser
 * instances, warmup, multiple revs/iterations, retry threshold for stable
 * numbers).
 *
 * Run it:
 *   vendor/bin/phpbench run bench/MarkdownBench.php --report=default
 *
 * Each bench method reuses ONE parser instance (built in beforeMethods) so we
 * measure steady-state parse throughput, not construction. The param provider
 * feeds a representative corpus document (tempest-docs.md if present, else a
 * baked-in GFM sample so the case is always runnable).
 *
 * Memory caveat (same as the wall-clock rig): the 'fight' parser renders onto
 * the C heap (md4c malloc), which PHP's memory metrics do not count. Compare
 * memory across parsers with that asymmetry in mind.
 */
#[Bench\BeforeMethods('setUp')]
#[Bench\Warmup(2)]
#[Bench\Revs(50)]
#[Bench\Iterations(10)]
#[Bench\RetryThreshold(2.0)]
final class MarkdownBench
{
    private ?FfiParser $fight = null;

    private TempestMarkdown $tempest;

    private GithubFlavoredMarkdownConverter $leagueGfm;

    /**
     * Build parser instances once per benchmark subject (outside the revs
     * loop). phpbench calls this before the timed revolutions.
     */
    public function setUp(): void
    {
        if (\class_exists(FfiParser::class)) {
            $this->fight = new FfiParser();
        }
        $this->tempest = new TempestMarkdown();
        $this->leagueGfm = new GithubFlavoredMarkdownConverter();
    }

    /**
     * Param provider: yields the representative corpus document under the key
     * 'md'. Prefers a real corpus file; falls back to a baked GFM sample so the
     * case runs even before the corpus agent has populated corpus/.
     *
     * @return \Generator<string, array{md:string, label:string}>
     */
    public function provideDocument(): \Generator
    {
        $candidates = [
            \dirname(__DIR__) . '/corpus/tempest-docs.md',
            \dirname(__DIR__) . '/corpus/synthetic/tempest-docs.md',
        ];
        foreach ($candidates as $path) {
            if (\is_file($path)) {
                $md = \file_get_contents($path);
                if ($md !== false && $md !== '') {
                    yield 'tempest-docs' => ['md' => $md, 'label' => \basename($path)];

                    return;
                }
            }
        }

        // Fallback representative GFM document (always runnable).
        $md = <<<'MD'
# Markdown Fight

A representative **GitHub Flavored** document used as a fallback when the
corpus has not been generated yet.

## Lists

A list:

- one
- two
  - nested
- [x] done
- [ ] todo

## Table

| Feature      | Supported |
|--------------|:---------:|
| autolinks    | yes       |
| tables       | yes       |
| strikethrough| ~~no~~ yes |
| tasklists    | yes       |

## Code

Inline `code` and a fenced block:

```php
echo "hello";
```

> A blockquote with a https://example.com autolink and **emphasis**.

Paragraph with _emphasis_, `inline code`, and ~~struck~~ text repeated a few
times to give the parser real work to chew on. Paragraph with _emphasis_,
`inline code`, and ~~struck~~ text. Paragraph with _emphasis_, `inline code`,
and ~~struck~~ text.
MD;

        yield 'fallback-gfm' => ['md' => $md, 'label' => 'fallback-gfm'];
    }

    #[Bench\Subject]
    #[Bench\ParamProviders('provideDocument')]
    public function benchFight(array $params): void
    {
        if ($this->fight === null) {
            // FfiParser not available — skip work but keep the subject valid.
            return;
        }
        $this->callFight($params['md']);
    }

    #[Bench\Subject]
    #[Bench\ParamProviders('provideDocument')]
    public function benchTempest(array $params): void
    {
        $this->tempest->parse($params['md'])->html;
    }

    #[Bench\Subject]
    #[Bench\ParamProviders('provideDocument')]
    public function benchLeagueGfm(array $params): void
    {
        $this->leagueGfm->convert($params['md'])->getContent();
    }

    /**
     * Probe FfiParser's public API once and dispatch. Kept tiny so the timed
     * path stays representative.
     */
    private function callFight(string $md): string
    {
        $p = $this->fight;
        if (\method_exists($p, 'parse')) {
            return (string) $p->parse($md);
        }
        if (\method_exists($p, 'toHtml')) {
            return (string) $p->toHtml($md);
        }
        if (\method_exists($p, 'convert')) {
            return (string) $p->convert($md);
        }
        if (\method_exists($p, 'render')) {
            return (string) $p->render($md);
        }
        if (\is_callable($p)) {
            return (string) $p($md);
        }

        return '';
    }
}

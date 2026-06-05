<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Bench;

use HelgeSverre\Markdown\Parser;
use HelgeSverre\Markdown\FrontMatter;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser as LeagueFrontMatterParser;
use PhpBench\Attributes as Bench;
use Symfony\Component\Yaml\Yaml;
use Tempest\Markdown\Markdown as TempestMarkdown;
use Tempest\Markdown\Parser as TempestParser;
use Tempest\Markdown\Tokens\FrontMatterToken;

use function str_repeat;

/**
 * Front-matter extraction, head-to-head (group: frontmatter).
 *
 * Everything else benchmarks Markdown -> HTML. This isolates one feature:
 * pulling the YAML front-matter array out of a document. The honest asymmetry —
 * surfaced as the "renders body?" column in RESULTS.md — is that tempest has no
 * *dedicated* front-matter API: its idiomatic path (parse()) renders the whole
 * document, while its cheapest path (lex(), no render) still tokenizes it end to
 * end. helgesverre/markdown and league both expose dedicated extractors that
 * skip rendering. Both tempest paths are measured (benchTempestFull / Lex). The
 * descriptor map (label / rendersBody / note) lives in bench/format.php.
 *
 * Single baked document (no param provider): a realistic blog-post header
 * (scalars, an inline list, a nested map) followed by enough Markdown body that
 * "render the whole doc" is real work, not a stub.
 */
#[Bench\BeforeMethods('setUp')]
#[Bench\Warmup(2)]
#[Bench\Revs(50)]
#[Bench\Iterations(10)]
#[Bench\RetryThreshold(2.0)]
final class FrontMatterBench
{
    private string $yaml = '';

    private string $doc = '';

    private LeagueFrontMatterParser $leagueFm;

    private TempestMarkdown $tempest;

    private TempestParser $tempestParser;

    private Parser $ours;

    public function setUp(): void
    {
        $this->yaml = <<<'YAML'
            title: "Benchmarking Markdown Front Matter"
            date: 2026-06-05
            draft: false
            tags: [php, markdown, ffi, performance]
            author:
              name: Helge Sverre
              url: https://helgesverre.com
            description: How fast can you pull metadata off a Markdown document?
            YAML;

        $body = str_repeat(
            "## Section\n\nSome **bold** and _italic_ prose with a [link](https://example.com) "
            . "and `inline code`, plus a list:\n\n- one\n- two\n- three\n\n"
            . "```php\n\$x = 1 + 2;\n```\n\n",
            6,
        );

        $this->doc = "---\n{$this->yaml}\n---\n# " . "Front Matter Speed\n\n" . $body;

        $this->leagueFm = new LeagueFrontMatterParser(new SymfonyYamlFrontMatterParser());
        $this->tempest = new TempestMarkdown();
        $this->tempestParser = new TempestParser();
        $this->ours = new Parser();
    }

    /**
     * tempest's cheapest front-matter path: lex() tokenizes the document but
     * does not render it; the front matter is carried on FrontMatterToken->data.
     *
     * @return array<string, mixed>
     */
    private function tempestFrontMatterViaLex(): array
    {
        foreach ($this->tempestParser->lex($this->doc) as $token) {
            if ($token instanceof FrontMatterToken) {
                return $token->data;
            }
        }

        return [];
    }

    /** Raw YAML parse only — no Markdown involved. The floor. */
    #[Bench\Subject]
    #[Bench\Groups(['frontmatter'])]
    public function benchSymfonyYaml(): void
    {
        Yaml::parse($this->yaml);
    }

    /** Dedicated extractor: regex split + libyaml FFI, skips rendering. */
    #[Bench\Subject]
    #[Bench\Groups(['frontmatter'])]
    public function benchOursExtract(): void
    {
        FrontMatter::extract($this->doc);
    }

    /** league FrontMatterParser — skips rendering the body. */
    #[Bench\Subject]
    #[Bench\Groups(['frontmatter'])]
    public function benchLeagueFrontmatter(): void
    {
        $this->leagueFm->parse($this->doc)->getFrontMatter();
    }

    /** tempest's idiomatic path: parse() renders the whole document. */
    #[Bench\Subject]
    #[Bench\Groups(['frontmatter'])]
    public function benchTempestFull(): void
    {
        $this->tempest->parse($this->doc)->frontmatter;
    }

    /** tempest's cheapest path: lex() (no render), read FrontMatterToken->data. */
    #[Bench\Subject]
    #[Bench\Groups(['frontmatter'])]
    public function benchTempestLex(): void
    {
        $this->tempestFrontMatterViaLex();
    }

    /** Our full parse(): front matter + HTML + table of contents. */
    #[Bench\Subject]
    #[Bench\Groups(['frontmatter'])]
    public function benchOursFull(): void
    {
        $this->ours->parse($this->doc)->frontmatter;
    }
}

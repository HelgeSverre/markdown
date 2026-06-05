<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Bench;

use Generator;
use PhpBench\Attributes as Bench;

use function basename;
use function dirname;
use function file_get_contents;
use function getenv;
use function is_array;
use function is_file;
use function json_decode;
use function str_starts_with;

/**
 * Markdown -> HTML throughput, head-to-head (group: throughput).
 *
 * One subject per parser; each reuses a single pre-constructed instance from
 * bench/parsers.php (built once in setUp, outside the timed revs). The param
 * provider sweeps the full corpus from corpus/manifest.json.
 *
 * Corpus is passed by PATH (not contents): yielding the document text as a
 * param would serialize multi-MB strings into phpbench's generated remote
 * script and into the XML dump. Instead each subject memoizes the file read on
 * first call — which lands in the @Warmup revs, so the read is amortized OUT of
 * the measured revs. Absent tiers are skipped.
 *
 * The default corpus (corpus/manifest.json) caps at ~256KB — realistic document
 * sizes plus the two real corpora. The 1MB/8MB scaling tiers live in
 * corpus/stress-manifest.json; select it with MARKDOWN_BENCH_MANIFEST (see the
 * `bench:stress` composer script). Run `composer corpus` first to generate them.
 *
 * Throughput cadence (warmup 1, revs 10, iterations 5) is intentionally lighter
 * than the global phpbench.json default: pure-PHP parsers take tens of ms per
 * parse on the larger tiers, so 500 samples/cell would push the run into minutes
 * for no statistical gain (rstdev stays <2%). The front-matter bench keeps the
 * higher cadence for its µs-scale precision.
 *
 * MEMORY CAVEAT (honest, do not delete): our FFI parser renders HTML onto the C
 * heap (md4c malloc), which PHP's memory metrics do NOT count — only the
 * FFI::string() copy back into a PHP string is visible. So our mem-peak is
 * real-RSS-favorable (it undercounts the transient, immediately-freed C output
 * buffer). Pure-PHP parsers keep all work on the Zend heap, so their mem-peak is
 * a complete accounting. Read the memory column with that asymmetry in mind.
 */
#[Bench\BeforeMethods('setUp')]
#[Bench\Warmup(1)]
#[Bench\Revs(10)]
#[Bench\Iterations(5)]
#[Bench\RetryThreshold(2.0)]
final class ThroughputBench
{
    /** @var array<string, callable(string):string> */
    private array $parsers = [];

    /** @var array<string, string> memoized corpus reads, keyed by path */
    private array $docs = [];

    /**
     * Build the parser registry once (instances constructed outside timing).
     * parsers.php returns id => callable(string):string.
     */
    public function setUp(): void
    {
        $this->parsers = require dirname(__DIR__) . '/bench/parsers.php';
    }

    /**
     * Param provider: yields one set per EXISTING corpus file, as
     * {path, bytes, label}. Reads corpus/manifest.json; resolves relative paths
     * against corpus/; skips files that are not present.
     *
     * @return Generator<string, array{path:string, bytes:int, label:string}>
     */
    public function provideCorpus(): Generator
    {
        $corpusDir = dirname(__DIR__) . '/corpus';
        // Default to the ~256KB manifest; MARKDOWN_BENCH_MANIFEST selects another
        // (e.g. stress-manifest.json for the 1MB/8MB scaling tiers).
        $manifestName = getenv('MARKDOWN_BENCH_MANIFEST');
        if (! is_string($manifestName) || $manifestName === '') {
            $manifestName = 'manifest.json';
        }
        $manifestPath = $corpusDir . '/' . $manifestName;

        $items = [];
        if (is_file($manifestPath)) {
            $decoded = json_decode((string) file_get_contents($manifestPath), true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['path'])) {
                continue;
            }
            $path = (string) $item['path'];
            if (! str_starts_with($path, '/')) {
                $path = $corpusDir . '/' . $path;
            }
            if (! is_file($path)) {
                // Uncommitted tier (run `composer corpus` to regenerate) — skip.
                continue;
            }
            $label = basename($path);
            yield $label => [
                'path' => $path,
                'bytes' => (int) filesize($path),
                'label' => $label,
            ];
        }
    }

    /** Memoized corpus read; first hit lands in warmup, timed revs reuse it. */
    private function doc(string $path): string
    {
        return $this->docs[$path] ??= (string) file_get_contents($path);
    }

    #[Bench\Subject]
    #[Bench\Groups(['throughput'])]
    #[Bench\ParamProviders('provideCorpus')]
    public function benchHelgesverre(array $params): void
    {
        $this->parsers['helgesverre/markdown']($this->doc($params['path']));
    }

    /**
     * Full document pipeline: front matter (libyaml) + render + heading anchors
     * + TOC. The realistic "serve a docs page" workload — what you actually pay
     * in production — versus benchHelgesverre()'s render-only fast path.
     */
    #[Bench\Subject]
    #[Bench\Groups(['throughput'])]
    #[Bench\ParamProviders('provideCorpus')]
    public function benchHelgesverreParse(array $params): void
    {
        $this->parsers['helgesverre/markdown (parse)']($this->doc($params['path']));
    }

    #[Bench\Subject]
    #[Bench\Groups(['throughput'])]
    #[Bench\ParamProviders('provideCorpus')]
    public function benchTempest(array $params): void
    {
        $this->parsers['tempest']($this->doc($params['path']));
    }

    #[Bench\Subject]
    #[Bench\Groups(['throughput'])]
    #[Bench\ParamProviders('provideCorpus')]
    public function benchLeagueGfm(array $params): void
    {
        $this->parsers['league-gfm']($this->doc($params['path']));
    }

    #[Bench\Subject]
    #[Bench\Groups(['throughput'])]
    #[Bench\ParamProviders('provideCorpus')]
    public function benchLeagueStrict(array $params): void
    {
        $this->parsers['league-strict']($this->doc($params['path']));
    }
}

<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

use FFI;
use RuntimeException;
use Throwable;

/**
 * Batch markdown renderer that fans a set of documents out across a native
 * pthread pool in a single FFI call.
 *
 * The C side (md2html_batch) takes all documents concatenated into one packed
 * buffer plus an offset table, renders each across worker threads, and hands
 * back one flat HTML buffer plus an output offset table. PHP slices the result
 * with substr — one FFI call for the whole batch.
 *
 * If the native batch symbol is unavailable (e.g. an older lib), we fall back
 * to a pure-PHP sequential loop over FfiParser so callers always get an answer.
 */
final class FfiBatchParser implements MarkdownParser
{
    private const CDEF = <<<'C'
        char* md2html(const char* input, size_t input_len, size_t* out_len, unsigned int parser_flags, unsigned int renderer_flags);
        void md2html_free(char* p);
        unsigned int md2html_dialect_github(void);
        char* md2html_batch(const char* packed, const size_t* in_offsets, size_t n, size_t* out_offsets, unsigned int parser_flags, unsigned int renderer_flags, int threads);
        C;

    private FFI $ffi;

    private int $flags;

    private int $rendererFlags;

    private bool $hasBatch;

    private FfiParser $sequential;

    public function __construct(
        Dialect $dialect = Dialect::GitHub,
        bool $safe = false,
        bool $xhtml = false,
    ) {
        try {
            $this->ffi = FFI::scope('MD4C');
        } catch (Throwable) {
            $this->ffi = FFI::cdef(self::CDEF, FfiParser::libPath());
        }

        $this->flags = FfiParser::resolveParserFlags($this->ffi, $dialect, $safe);
        $this->rendererFlags = $xhtml ? FfiParser::MD_HTML_FLAG_XHTML : 0;

        // Probe for the batch symbol once. Accessing an undefined C function on
        // the binding throws, so this is a reliable availability check.
        try {
            $probe = $this->ffi->md2html_batch;
            unset($probe);
            $this->hasBatch = true;
        } catch (Throwable) {
            $this->hasBatch = false;
        }

        $this->sequential = new FfiParser($dialect, $safe, $xhtml);
    }

    public function toHtml(string $markdown): string
    {
        return $this->sequential->toHtml($markdown);
    }

    /**
     * Render many documents at once.
     *
     * @param array<int,string> $docs
     * @return array<int,string> HTML output, index-aligned with $docs.
     */
    public function toHtmlBatch(array $docs): array
    {
        $n = count($docs);
        if ($n === 0) {
            return [];
        }

        if (! $this->hasBatch) {
            return $this->sequentialBatch($docs);
        }

        // Re-index to a dense 0..n-1 list so offset math is straightforward.
        $list = array_values($docs);

        // Pack all docs into one contiguous buffer + an (n+1) offset table.
        $packed = '';
        $inOffsets = $this->ffi->new('size_t[' . ($n + 1) . ']');
        $inOffsets[0] = 0;
        $cursor = 0;
        foreach ($list as $i => $doc) {
            $packed .= $doc;
            $cursor += strlen($doc);
            $inOffsets[$i + 1] = $cursor;
        }

        $outOffsets = $this->ffi->new('size_t[' . ($n + 1) . ']');

        $threads = $this->workerCount($n);

        $ptr = $this->ffi->md2html_batch(
            $packed,
            $inOffsets,
            $n,
            $outOffsets,
            $this->flags,
            $this->rendererFlags,
            $threads,
        );

        if ($ptr === null) {
            throw new RuntimeException('md4c batch render failed.');
        }

        try {
            $total = $outOffsets[$n];
            // Scope-free static helper; no instance form, no 8.5 deprecation.
            $blob = FFI::string($ptr, $total);
        } finally {
            $this->ffi->md2html_free($ptr);
        }

        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $start = $outOffsets[$i];
            $len = $outOffsets[$i + 1] - $start;
            $out[$i] = $len > 0 ? substr($blob, $start, $len) : '';
        }

        return $out;
    }

    public function name(): string
    {
        return 'helgesverre/markdown (FFI→md4c, batch)';
    }

    /**
     * Pure-PHP sequential fallback: just loop the single-doc parser.
     *
     * @param array<int,string> $docs
     * @return array<int,string>
     */
    private function sequentialBatch(array $docs): array
    {
        $out = [];
        foreach (array_values($docs) as $i => $doc) {
            $out[$i] = $this->sequential->toHtml($doc);
        }

        return $out;
    }

    /** Pick a sane worker count: bounded by CPU count and the batch size. */
    private function workerCount(int $n): int
    {
        $cpus = 4;
        if (function_exists('shell_exec')) {
            // macOS: sysctl; Linux: nproc. Whichever answers first wins.
            $detected = (int) trim((string) @shell_exec('sysctl -n hw.ncpu 2>/dev/null || nproc 2>/dev/null'));
            if ($detected > 0) {
                $cpus = $detected;
            }
        }

        return max(1, min($cpus, $n));
    }
}

<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

use FFI;
use FFI\Exception as FFIException;
use HelgeSverre\Markdown\Data\Dialect;
use HelgeSverre\Markdown\Ffi\Library;
use RuntimeException;

/**
 * Batch markdown renderer that fans a set of documents out across a native
 * pthread pool in a single FFI call.
 *
 * The C side (md2html_batch) takes all documents concatenated into one packed
 * buffer plus an offset table, renders each across worker threads, and hands
 * back one flat HTML buffer plus an output offset table. PHP slices the result
 * with substr — one FFI call for the whole batch.
 *
 * If the native md2html_batch symbol isn't present (an older or hand-built shim
 * that predates the batch path), toHtmlBatch() transparently falls back to
 * rendering each document sequentially, so output is identical either way.
 */
final class BatchParser
{
    private FFI $ffi;

    private int $flags;

    private int $rendererFlags;

    private Parser $sequential;

    /** Tri-state cache of whether this build exposes md2html_batch (null = untried). */
    private ?bool $batchAvailable = null;

    public function __construct(
        Dialect $dialect = Dialect::GitHub,
        bool $safe = false,
        bool $xhtml = false,
    ) {
        $this->ffi = Library::bind();
        $this->flags = Library::parserFlags($this->ffi, $dialect, $safe);
        $this->rendererFlags = Library::rendererFlags($xhtml);
        $this->sequential = new Parser($dialect, $safe, $xhtml);
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

        // Re-index to a dense 0..n-1 list so offset math is straightforward.
        $list = array_values($docs);

        // A prior call already proved this build has no md2html_batch symbol:
        // skip the packing work and render sequentially.
        if ($this->batchAvailable === false) {
            return $this->renderEachSequentially($list);
        }

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

        try {
            $ptr = $this->ffi->md2html_batch(
                $packed,
                $inOffsets,
                $n,
                $outOffsets,
                $this->flags,
                $this->rendererFlags,
                $threads,
            );
        } catch (FFIException) {
            // The batch entry point isn't in this library. Remember it so later
            // calls skip straight to the sequential path, and fall back now.
            $this->batchAvailable = false;

            return $this->renderEachSequentially($list);
        }

        $this->batchAvailable = true;

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

    /**
     * Render each document on its own through the sequential parser,
     * index-aligned — the fallback when the native batch path is unavailable.
     *
     * @param  list<string>  $docs
     * @return array<int,string>
     */
    private function renderEachSequentially(array $docs): array
    {
        $out = [];
        foreach ($docs as $i => $doc) {
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

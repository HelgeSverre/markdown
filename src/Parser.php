<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

use FFI;
use HelgeSverre\Markdown\Data\Dialect;
use HelgeSverre\Markdown\Data\ParsedMarkdown;
use HelgeSverre\Markdown\Ffi\Library;
use RuntimeException;

/**
 * Markdown parser backed by md4c through FFI.
 *
 * One FFI call in, one pointer out: the C shim buffers the whole HTML document
 * in a single malloc'd, NUL-terminated buffer so no PHP callback ever crosses
 * the FFI boundary. We copy it once with FFI::string and always free the C
 * buffer in a finally block.
 *
 *   toHtml()  — the fast path: raw Markdown -> HTML, nothing else.
 *   parse()   — document-aware: strips front matter, anchors headings, and
 *               returns a ParsedMarkdown (html + frontmatter + toc).
 *
 * The shared library is resolved and bound by {@see Library} (the preloaded FFI
 * scope, or a cdef fallback).
 */
final class Parser
{
    private FFI $ffi;

    /** Cached parser flags (md4c MD_FLAG_* bitmask). */
    private int $flags;

    /** Cached HTML renderer flags (md4c MD_HTML_FLAG_* bitmask). */
    private int $rendererFlags;

    public function __construct(
        Dialect $dialect = Dialect::GitHub,
        bool $safe = false,
        bool $xhtml = false,
    ) {
        $this->ffi = Library::bind();
        $this->flags = Library::parserFlags($this->ffi, $dialect, $safe);
        $this->rendererFlags = Library::rendererFlags($xhtml);
    }

    /** Render Markdown to HTML. The fast path — no front matter, no anchors. */
    public function toHtml(string $markdown): string
    {
        // size_t out param so the shim can hand back the exact byte length.
        // FFI::new() is called on the *instance* (the static form is deprecated
        // in PHP 8.5 because it needs a type-resolution scope).
        $len = $this->ffi->new('size_t');

        // FFI::addr()/FFI::string() are scope-free static helpers: there is no
        // instance-call form for them ($ffi->addr is treated as a C-function
        // lookup and errors), and the static calls emit no 8.5 deprecation.
        $ptr = $this->ffi->md2html(
            $markdown,
            strlen($markdown),
            FFI::addr($len),
            $this->flags,
            $this->rendererFlags,
        );

        if ($ptr === null) {
            throw new RuntimeException('md4c failed to render the markdown input.');
        }

        try {
            return FFI::string($ptr, $len->cdata);
        } finally {
            $this->ffi->md2html_free($ptr);
        }
    }

    /**
     * Parse a full document: split front matter, render the body, then anchor
     * the headings and collect a table of contents.
     *
     * The render itself runs at md4c speed in C; the document-level work (front
     * matter, heading ids, TOC) happens in PHP. It only runs on this opt-in
     * path, never in the toHtml() fast path.
     */
    public function parse(string $markdown): ParsedMarkdown
    {
        [$frontmatter, $body] = FrontMatter::extract($markdown);

        // Heading ids + TOC are built in a single C pass over the rendered HTML.
        [$html, $toc] = $this->anchorHeadings($this->toHtml($body));

        return new ParsedMarkdown($html, $frontmatter, $toc);
    }

    /**
     * Inject heading ids and build the TOC in a single C pass over the rendered
     * HTML (md2html_anchor): strip-tag slug derivation, GitHub-style de-dup, and
     * the table of contents, all without a second pass in PHP.
     *
     * @return array{0: string, 1: list<array{level: int, text: string, slug: string}>}
     */
    private function anchorHeadings(string $html): array
    {
        $outLen = $this->ffi->new('size_t');
        $tocPtr = $this->ffi->new('char*');
        $tocLen = $this->ffi->new('size_t');

        $ptr = $this->ffi->md2html_anchor(
            $html,
            strlen($html),
            FFI::addr($outLen),
            FFI::addr($tocPtr),
            FFI::addr($tocLen),
        );

        if ($ptr === null) {
            throw new RuntimeException('md4c heading-anchor pass failed.');
        }

        try {
            $outHtml = FFI::string($ptr, $outLen->cdata);
            $tocBlob = $tocLen->cdata > 0 ? FFI::string($tocPtr, $tocLen->cdata) : '';
        } finally {
            // Both the HTML and the TOC blob are malloc'd by the shim.
            $this->ffi->md2html_free($ptr);
            $this->ffi->md2html_free($tocPtr);
        }

        return [$outHtml, self::decodeToc($tocBlob)];
    }

    /**
     * Decode the TOC blob: little-endian, length-prefixed records of
     * [u8 level][u32 slugLen][slug bytes][u32 textLen][text bytes].
     *
     * @return list<array{level: int, text: string, slug: string}>
     */
    private static function decodeToc(string $blob): array
    {
        $toc = [];
        $offset = 0;
        $n = strlen($blob);

        while ($offset < $n) {
            $level = ord($blob[$offset]);
            $offset += 1;
            $slugLen = unpack('V', substr($blob, $offset, 4))[1];
            $offset += 4;
            $slug = substr($blob, $offset, $slugLen);
            $offset += $slugLen;
            $textLen = unpack('V', substr($blob, $offset, 4))[1];
            $offset += 4;
            $text = substr($blob, $offset, $textLen);
            $offset += $textLen;

            $toc[] = ['level' => $level, 'text' => $text, 'slug' => $slug];
        }

        return $toc;
    }
}

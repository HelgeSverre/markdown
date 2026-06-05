<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

use FFI;
use RuntimeException;
use Throwable;

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
 */
final class FfiParser implements MarkdownParser
{
    /**
     * md4c flags we toggle from PHP — these mirror native/md4c/md4c.h. The
     * GitHub dialect composite is fetched from C (md2html_dialect_github) so we
     * never duplicate that value here.
     */
    public const MD_FLAG_NOHTML = 0x0060; // NOHTMLBLOCKS | NOHTMLSPANS

    public const MD_HTML_FLAG_XHTML = 0x0008;

    private const CDEF = <<<'C'
        char* md2html(const char* input, size_t input_len, size_t* out_len, unsigned int parser_flags, unsigned int renderer_flags);
        void md2html_free(char* p);
        unsigned int md2html_dialect_github(void);
        C;

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
        // Fast path: bind against the opcache-preloaded scope. This skips the
        // C-declaration parse entirely on every request.
        try {
            $this->ffi = FFI::scope('MD4C');
        } catch (Throwable) {
            // Fallback: parse the inline declarations and dlopen the lib now.
            $this->ffi = FFI::cdef(self::CDEF, self::libPath());
        }

        $this->flags = self::resolveParserFlags($this->ffi, $dialect, $safe);
        $this->rendererFlags = $xhtml ? self::MD_HTML_FLAG_XHTML : 0;
    }

    /** Resolve md4c parser flags for a dialect + safety choice. */
    public static function resolveParserFlags(FFI $ffi, Dialect $dialect, bool $safe): int
    {
        $flags = $dialect === Dialect::GitHub ? $ffi->md2html_dialect_github() : 0;
        if ($safe) {
            $flags |= self::MD_FLAG_NOHTML;
        }

        return $flags;
    }

    /**
     * Resolve the shared library for the current OS + architecture.
     *
     * Resolution order:
     *   1. $MARKDOWN_FFI_LIB env var (explicit override, for C development)
     *   2. the prebuilt library shipped in lib/<platform>/ (what users get)
     *   3. a local dev build in native/ (e.g. `composer build` on an unshipped
     *      platform such as musl/Alpine or FreeBSD)
     */
    public static function libPath(): string
    {
        $override = getenv('MARKDOWN_FFI_LIB');
        if (is_string($override) && $override !== '' && is_file($override)) {
            return $override;
        }

        $root = dirname(__DIR__);
        $arch = strtolower(php_uname('m'));
        $isArm = str_contains($arch, 'aarch64') || str_contains($arch, 'arm');

        $candidates = match (PHP_OS_FAMILY) {
            'Darwin' => [
                $root . '/lib/darwin/libmd4cshim.dylib', // universal: arm64 + x86_64
                $root . '/native/libmd4cshim.dylib',
            ],
            'Windows' => [
                $root . '/lib/windows-x86_64/md4cshim.dll',
                $root . '/native/md4cshim.dll',
            ],
            default => $isArm
                ? [
                    $root . '/lib/linux-aarch64/libmd4cshim.so',
                    $root . '/native/libmd4cshim.so',
                ]
                : [
                    $root . '/lib/linux-x86_64/libmd4cshim.so',
                    $root . '/native/libmd4cshim.so',
                ],
        };

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        // Nothing found: return the canonical dev path so the FFI failure names
        // a real, buildable location (run `composer build`).
        return $candidates[array_key_last($candidates)];
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
        [$html, $toc] = HeadingAnchors::process($this->toHtml($body));

        return new ParsedMarkdown($html, $frontmatter, $toc);
    }

    public function name(): string
    {
        return 'helgesverre/markdown (FFI→md4c)';
    }
}

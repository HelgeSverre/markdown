<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Ffi;

use FFI;
use HelgeSverre\Markdown\Data\Dialect;
use Throwable;

/**
 * Binds the md4c shim and resolves its shared library.
 *
 * Both Parser and BatchParser share one binding strategy: prefer the
 * opcache-preloaded FFI::scope('MD4C') fast path, falling back to FFI::cdef
 * against the inline declarations plus a runtime-resolved library path.
 *
 * Centralizing it here keeps the C signatures in a single PHP location — they
 * otherwise also live in native/md4cshim.h (the FFI::load scope header) and
 * build.sh's heredoc; change all of them together.
 */
final class Library
{
    /** md4c parser flag: NOHTMLBLOCKS | NOHTMLSPANS (strip raw HTML). */
    public const MD_FLAG_NOHTML = 0x0060;

    /** md4c HTML renderer flag: self-closing void tags (<br />, <hr />). */
    public const MD_HTML_FLAG_XHTML = 0x0008;

    /**
     * Every shim symbol, so one binding serves both the single-document and
     * batch paths. Mirrors native/md4cshim.h. Only used on the cdef fallback;
     * the preloaded scope already carries these declarations.
     */
    private const CDEF = <<<'C'
        char* md2html(const char* input, size_t input_len, size_t* out_len, unsigned int parser_flags, unsigned int renderer_flags);
        char* md2html_anchor(const char* html, size_t html_len, size_t* out_len, char** toc, size_t* toc_len);
        void md2html_free(char* p);
        unsigned int md2html_dialect_github(void);
        char* md2html_batch(const char* packed, const size_t* in_offsets, size_t n, size_t* out_offsets, unsigned int parser_flags, unsigned int renderer_flags, int threads);
        char* yaml2json(const char* yaml, size_t yaml_len, size_t* out_len);
        C;

    /**
     * Bind the md4c shim: the opcache-preloaded scope if present, otherwise a
     * fresh cdef + dlopen of the resolved library.
     */
    public static function bind(): FFI
    {
        // Fast path: bind against the opcache-preloaded scope. This skips the
        // C-declaration parse entirely on every request.
        try {
            return FFI::scope('MD4C');
        } catch (Throwable) {
            // Fallback: parse the inline declarations and dlopen the lib now.
            return FFI::cdef(self::CDEF, self::path());
        }
    }

    /** Resolve md4c parser flags for a dialect + safety choice. */
    public static function parserFlags(FFI $ffi, Dialect $dialect, bool $safe): int
    {
        // The GitHub dialect composite is fetched from C (md2html_dialect_github)
        // so we never duplicate that value in PHP.
        $flags = $dialect === Dialect::GitHub ? $ffi->md2html_dialect_github() : 0;
        if ($safe) {
            $flags |= self::MD_FLAG_NOHTML;
        }

        return $flags;
    }

    /** Resolve the HTML renderer flag bitmask. */
    public static function rendererFlags(bool $xhtml): int
    {
        return $xhtml ? self::MD_HTML_FLAG_XHTML : 0;
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
    public static function path(): string
    {
        $override = getenv('MARKDOWN_FFI_LIB');
        if (is_string($override) && $override !== '' && is_file($override)) {
            return $override;
        }

        $root = dirname(__DIR__, 2);
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
}

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
 */
final class FfiParser implements MarkdownParser
{
    private const CDEF = <<<'C'
        char* md2html(const char* input, size_t input_len, size_t* out_len, unsigned int parser_flags, unsigned int renderer_flags);
        void md2html_free(char* p);
        unsigned int md2html_dialect_github(void);
        C;

    private FFI $ffi;

    /** Cached GitHub-dialect parser flags (GFM extensions on). */
    private int $flags;

    public function __construct()
    {
        // Fast path: bind against the opcache-preloaded scope. This skips the
        // C-declaration parse entirely on every request.
        try {
            $this->ffi = FFI::scope('MD4C');
        } catch (Throwable) {
            // Fallback: parse the inline declarations and dlopen the lib now.
            $this->ffi = FFI::cdef(self::CDEF, self::libPath());
        }

        $this->flags = $this->ffi->md2html_dialect_github();
    }

    /** Resolve the compiled shared library for the current platform. */
    public static function libPath(): string
    {
        $dir = dirname(__DIR__) . '/native/';

        return match (PHP_OS_FAMILY) {
            'Darwin'  => $dir . 'libmd4cshim.dylib',
            'Windows' => $dir . 'md4cshim.dll',
            default   => $dir . 'libmd4cshim.so',
        };
    }

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
            0,
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

    public function name(): string
    {
        return 'markdown-fight (FFI->md4c)';
    }
}

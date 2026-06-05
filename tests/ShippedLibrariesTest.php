<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use FFI;
use HelgeSverre\Markdown\Ffi\Library;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The package ships prebuilt native libraries for every supported platform so
 * users don't need a C compiler. These tests guard that promise.
 */
final class ShippedLibrariesTest extends TestCase
{
    private const SHIPPED = [
        'lib/darwin/libmd4cshim.dylib',
        'lib/linux-x86_64/libmd4cshim.so',
        'lib/linux-aarch64/libmd4cshim.so',
        'lib/windows-x86_64/md4cshim.dll',
    ];

    #[Test]
    public function every_platform_library_is_shipped_and_non_trivial(): void
    {
        $root = dirname(__DIR__);
        foreach (self::SHIPPED as $rel) {
            $path = $root . '/' . $rel;
            $this->assertFileExists($path, "missing shipped library: {$rel}");
            $this->assertGreaterThan(50_000, filesize($path), "suspiciously small library: {$rel}");
        }
    }

    #[Test]
    public function libpath_resolves_to_an_existing_file_for_this_platform(): void
    {
        $this->assertFileExists(Library::path());
    }

    #[Test]
    public function the_resolved_library_actually_binds_and_exports_md2html(): void
    {
        // Bind the resolved library directly (no preload) and confirm the C
        // entry points are really callable — this is what FFI does at runtime.
        $ffi = FFI::cdef(
            'unsigned int md2html_dialect_github(void);',
            Library::path(),
        );

        $this->assertSame(0x0F0C, $ffi->md2html_dialect_github());
    }

    #[Test]
    public function the_resolved_library_actually_binds_and_exports_yaml2json(): void
    {
        $ffi = FFI::cdef(
            <<<'C'
                char* yaml2json(const char* yaml, size_t yaml_len, size_t* out_len);
                void md2html_free(char* p);
                C,
            Library::path(),
        );

        $len = $ffi->new('size_t');
        $ptr = $ffi->yaml2json("title: Hi\n", strlen("title: Hi\n"), FFI::addr($len));

        $this->assertNotNull($ptr);

        try {
            $this->assertSame('{"title":"Hi"}', FFI::string($ptr, $len->cdata));
        } finally {
            $ffi->md2html_free($ptr);
        }
    }
}

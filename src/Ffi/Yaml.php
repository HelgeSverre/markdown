<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Ffi;

use FFI;
use Throwable;

use function is_array;
use function json_decode;
use function strlen;

use const JSON_THROW_ON_ERROR;

/**
 * Front-matter YAML decoder: one FFI call into the vendored-libyaml shim
 * (yaml2json) plus one json_decode. Returns the decoded array, or an empty
 * array when the input cannot be decoded — a parse error, an anchor/alias, a
 * `<<` merge key, malformed JSON, or FFI being unavailable. There is no
 * symfony/yaml fallback: anything libyaml's walker declines is treated as
 * "no usable front matter", the same way malformed YAML already degrades.
 *
 * Shares the preloaded MD4C scope (and its md2html_free) with {@see Library}.
 */
final class Yaml
{
    private static ?FFI $ffi = null;

    /**
     * @return array<array-key, mixed>  empty array when the input can't be decoded
     */
    public static function decode(string $yaml): array
    {
        try {
            self::$ffi ??= Library::bind();
            $ffi = self::$ffi;

            $len = $ffi->new('size_t');
            $ptr = $ffi->yaml2json($yaml, strlen($yaml), FFI::addr($len));

            if ($ptr === null) {
                // C walker refused (alias / merge key / parse error).
                return [];
            }

            try {
                $json = FFI::string($ptr, $len->cdata);
            } finally {
                $ffi->md2html_free($ptr);
            }

            $decoded = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            // FFI disabled / lib missing / binding failure / JSON error.
            return [];
        }

        // Front matter is expected to be a map/sequence; a bare scalar is
        // "empty front matter".
        return is_array($decoded) ? $decoded : [];
    }
}

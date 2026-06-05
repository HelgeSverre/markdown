<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

/**
 * Splits a leading YAML front-matter block from a Markdown document.
 *
 * The block must be the very first thing in the document: a line of `---`, the
 * YAML body, and a closing line of `---`. This is a cheap regex split — no
 * Markdown is parsed — so extracting metadata never pays the rendering cost.
 *
 * md4c has no concept of front matter (it would render `---` as a thematic
 * break), so this runs in PHP before the document reaches the parser.
 *
 * The YAML body is decoded entirely by the vendored-libyaml FFI path
 * ({@see Ffi\Yaml::decode}): parsed to JSON in C, then json_decode'd. There is
 * no symfony/yaml fallback — anything that path declines (anchors/aliases, `<<`
 * merge keys, parse errors, or FFI being unavailable) degrades to an empty
 * array, the same as malformed YAML. Bare dates are kept as strings rather than
 * coerced to integer timestamps — see the README.
 */
final class FrontMatter
{
    /**
     * @return array{0: array<string, mixed>, 1: string}  [parsed front matter, remaining body]
     */
    public static function extract(string $markdown): array
    {
        // Front matter must open on the first byte: `---` then a newline.
        if (! preg_match('/^---\r?\n(.*?)\r?\n---[ \t]*(?:\r?\n|$)/s', $markdown, $m)) {
            return [[], $markdown];
        }

        $body = substr($markdown, strlen($m[0]));
        $yaml = $m[1];

        if (trim($yaml) === '') {
            return [[], $body];
        }

        return [Ffi\Yaml::decode($yaml), $body];
    }
}

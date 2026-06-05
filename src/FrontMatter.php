<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

use Symfony\Component\Yaml\Yaml;
use Throwable;

/**
 * Splits a leading YAML front-matter block from a Markdown document.
 *
 * The block must be the very first thing in the document: a line of `---`, the
 * YAML body, and a closing line of `---`. This is a cheap regex split — no
 * Markdown is parsed — so extracting metadata never pays the rendering cost.
 *
 * md4c has no concept of front matter (it would render `---` as a thematic
 * break), so this runs in PHP before the document reaches the parser.
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

        if (trim($yaml) === '' || ! class_exists(Yaml::class)) {
            return [[], $body];
        }

        try {
            $parsed = Yaml::parse($yaml);
        } catch (Throwable) {
            // Malformed YAML: treat the document as having no front matter
            // rather than throwing (tempest/markdown throws here; we don't).
            return [[], $body];
        }

        return [is_array($parsed) ? $parsed : [], $body];
    }
}

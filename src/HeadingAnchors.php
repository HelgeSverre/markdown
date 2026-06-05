<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

/**
 * Heading slugs and the table of contents for FfiParser::parse().
 *
 * md4c-html emits bare "<h1>".."<h6>" tags with no id attribute, so this pass
 * runs over the rendered HTML, derives a GitHub-style slug from each heading's
 * visible text, injects id="slug", and collects the TOC. It only runs on the
 * opt-in parse() path — never in the toHtml() fast path.
 *
 * Slug rules:
 *   - ASCII letters are lower-cased, digits kept;
 *   - every other byte run (spaces, punctuation, entities, non-ASCII) collapses
 *     to a single '-'; leading/trailing '-' are trimmed;
 *   - duplicates within a document get -1, -2, … suffixes;
 *   - an empty result becomes "section".
 *
 * Dedup is O(n) (hash lookups + a per-base counter), so a document with many
 * identical headings can't blow up — important given the parser's hostile-input
 * guarantees. Non-ASCII letters are dropped rather than transliterated.
 */
final class HeadingAnchors
{
    /**
     * Inject id="slug" into every <hN> and collect the TOC.
     *
     * @return array{0: string, 1: list<array{level: int, text: string, slug: string}>}
     */
    public static function process(string $html): array
    {
        $counts = [];
        $toc = [];

        $out = preg_replace_callback(
            '#<h([1-6])>(.*?)</h\1>#s',
            static function (array $m) use (&$counts, &$toc): string {
                $level = (int) $m[1];
                $inner = $m[2];
                $slug = self::unique(self::slugify($inner), $counts);
                $toc[] = ['level' => $level, 'text' => self::plainText($inner), 'slug' => $slug];

                return "<h{$level} id=\"{$slug}\">{$inner}</h{$level}>";
            },
            $html,
        );

        return [$out ?? $html, $toc];
    }

    /** Derive a slug from a heading's inner HTML (tags and entities skipped). */
    public static function slugify(string $inner): string
    {
        $out = '';
        $prevHyphen = true;
        $len = strlen($inner);

        for ($i = 0; $i < $len;) {
            $c = $inner[$i];

            if ($c === '<') { // skip a tag
                while ($i < $len && $inner[$i] !== '>') {
                    $i++;
                }
                if ($i < $len) {
                    $i++;
                }
                continue;
            }

            if ($c === '&') { // an entity counts as one separator
                while ($i < $len && $inner[$i] !== ';') {
                    $i++;
                }
                if ($i < $len) {
                    $i++;
                }
                if (! $prevHyphen) {
                    $out .= '-';
                    $prevHyphen = true;
                }
                continue;
            }

            $o = ord($c);
            if ($o >= 65 && $o <= 90) { // A-Z -> a-z
                $out .= chr($o + 32);
                $prevHyphen = false;
            } elseif ($o >= 97 && $o <= 122 || $o >= 48 && $o <= 57) { // a-z 0-9
                $out .= $c;
                $prevHyphen = false;
            } elseif (! $prevHyphen) { // any other byte: separator
                $out .= '-';
                $prevHyphen = true;
            }
            $i++;
        }

        $out = rtrim($out, '-');

        return $out === '' ? 'section' : $out;
    }

    /**
     * Make a slug unique within the document. O(1) amortized: $counts both
     * records every emitted slug (membership) and remembers the next suffix to
     * try per base, so N identical headings stay linear overall.
     *
     * @param  array<string, int>  $counts
     */
    private static function unique(string $base, array &$counts): string
    {
        if (! isset($counts[$base])) {
            $counts[$base] = 0;

            return $base;
        }

        do {
            $candidate = $base . '-' . ++$counts[$base];
        } while (isset($counts[$candidate]));

        $counts[$candidate] = 0;

        return $candidate;
    }

    /** Human-readable heading text for the TOC: tags stripped, entities decoded. */
    private static function plainText(string $inner): string
    {
        return trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

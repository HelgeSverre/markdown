<?php

use HelgeSverre\Markdown\Parser;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Tempest\Markdown\Markdown;

/**
 * bench/parsers.php — Uniform parser registry.
 *
 * Returns an associative array of  id => callable(string $md): string.
 * Every callable closes over a SINGLE, pre-constructed parser/converter
 * instance so the timed loop measures steady-state parse throughput, not
 * object construction. Construct once, here, outside any timed loop.
 *
 * Parsers:
 *   'helgesverre/markdown'         — our FFI -> md4c parser (HelgeSverre\Markdown\Parser), GFM on
 *   'tempest'       — tempest/markdown
 *   'league-gfm'    — league/commonmark GitHub Flavored
 *   'league-strict' — league/commonmark strict CommonMark
 *
 * MEMORY CAVEAT (read this before trusting peak_mb for 'helgesverre/markdown'):
 *   Our FFI parser allocates its HTML output on the C heap (md4c's malloc),
 *   which PHP's memory_get_peak_usage(true) does NOT account for. PHP only
 *   sees the FFI::string() copy back into a PHP string. So 'helgesverre/markdown's reported
 *   peak_mb is real-RSS-favorable — it undercounts the transient C-heap
 *   buffer. We note this honestly rather than hide it; the C buffer is freed
 *   immediately after each parse (md2html_free), so steady-state RSS stays
 *   flat, but a single-parse peak as seen by the OS would be slightly higher
 *   than PHP's number suggests.
 *
 * This file is pure: it constructs instances and returns callables. It does
 * NOT require the autoloader itself (the caller does that first).
 *
 * @return array<string, callable(string):string>
 */

return (static function (): array {
    $registry = [];

    // --- 'helgesverre/markdown' : our FFI -> md4c parser ----------------------------------
    // Construct one Parser and reuse it. The render method is resolved by
    // probing (ordered below) so the registry stays decoupled from the exact
    // public API.
    if (class_exists(Parser::class)) {
        $ours = new Parser();
        $call = null;
        // toHtml first: the throughput benchmark must measure the fast path,
        // not parse() (which also strips front matter and anchors headings).
        foreach (['toHtml', 'convert', 'render', 'html', 'parse'] as $m) {
            if (! method_exists($ours, $m)) {
                continue;
            }

            $call = $m;
            break;
        }
        if ($call !== null) {
            $registry['helgesverre/markdown'] = static function (string $md) use ($ours, $call): string {
                return (string) $ours->{$call}($md);
            };
        } elseif (is_callable($ours)) {
            $registry['helgesverre/markdown'] = static function (string $md) use ($ours): string {
                return (string) $ours($md);
            };
        } else {
            // Last resort: surface a clear error if the API is unexpected.
            $registry['helgesverre/markdown'] = static function (string $md): string {
                throw new RuntimeException(
                    'HelgeSverre\Markdown\Parser exists but exposes no known parse method (tried parse/toHtml/convert/render/html/__invoke).',
                );
            };
        }
    } else {
        $registry['helgesverre/markdown'] = static function (string $md): string {
            throw new RuntimeException('HelgeSverre\Markdown\Parser not found — run `composer install` (and `composer build` on an unshipped platform).');
        };
    }

    // --- 'tempest' : tempest/markdown --------------------------------------
    $tempest = new Markdown();
    $registry['tempest'] = static function (string $md) use ($tempest): string {
        return (string) $tempest->parse($md)->html;
    };

    // --- 'league-gfm' : GitHub Flavored CommonMark -------------------------
    $leagueGfm = new GithubFlavoredMarkdownConverter();
    $registry['league-gfm'] = static function (string $md) use ($leagueGfm): string {
        return (string) $leagueGfm->convert($md)->getContent();
    };

    // --- 'league-strict' : strict CommonMark -------------------------------
    $leagueStrict = new CommonMarkConverter();
    $registry['league-strict'] = static function (string $md) use ($leagueStrict): string {
        return (string) $leagueStrict->convert($md)->getContent();
    };

    return $registry;
})();

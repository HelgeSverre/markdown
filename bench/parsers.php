<?php
/**
 * bench/parsers.php — Uniform parser registry.
 *
 * Returns an associative array of  id => callable(string $md): string.
 * Every callable closes over a SINGLE, pre-constructed parser/converter
 * instance so the timed loop measures steady-state parse throughput, not
 * object construction. Construct once, here, outside any timed loop.
 *
 * Parsers:
 *   'fight'         — our FFI -> md4c parser (MarkdownFight\FfiParser), GFM on
 *   'tempest'       — tempest/markdown
 *   'league-gfm'    — league/commonmark GitHub Flavored
 *   'league-strict' — league/commonmark strict CommonMark
 *
 * MEMORY CAVEAT (read this before trusting peak_mb for 'fight'):
 *   Our FFI parser allocates its HTML output on the C heap (md4c's malloc),
 *   which PHP's memory_get_peak_usage(true) does NOT account for. PHP only
 *   sees the FFI::string() copy back into a PHP string. So 'fight's reported
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

    // --- 'fight' : our FFI -> md4c parser ----------------------------------
    // MarkdownFight\FfiParser is written by the native agent. It exists at run
    // time. We construct exactly one instance and reuse it. We probe for the
    // method name so we are resilient to its exact public API (parse / toHtml
    // / convert / render / __invoke) without coupling tightly to one name.
    if (class_exists(\MarkdownFight\FfiParser::class)) {
        $fight = new \MarkdownFight\FfiParser();
        $call = null;
        foreach (['parse', 'toHtml', 'convert', 'render', 'html'] as $m) {
            if (method_exists($fight, $m)) {
                $call = $m;
                break;
            }
        }
        if ($call !== null) {
            $registry['fight'] = static function (string $md) use ($fight, $call): string {
                return (string) $fight->{$call}($md);
            };
        } elseif (is_callable($fight)) {
            $registry['fight'] = static function (string $md) use ($fight): string {
                return (string) $fight($md);
            };
        } else {
            // Last resort: surface a clear error if the API is unexpected.
            $registry['fight'] = static function (string $md): string {
                throw new \RuntimeException(
                    'MarkdownFight\FfiParser exists but exposes no known parse method '
                    . '(tried parse/toHtml/convert/render/html/__invoke).'
                );
            };
        }
    } else {
        $registry['fight'] = static function (string $md): string {
            throw new \RuntimeException('MarkdownFight\FfiParser class not found (native agent not done?).');
        };
    }

    // --- 'tempest' : tempest/markdown --------------------------------------
    $tempest = new \Tempest\Markdown\Markdown();
    $registry['tempest'] = static function (string $md) use ($tempest): string {
        return (string) $tempest->parse($md)->html;
    };

    // --- 'league-gfm' : GitHub Flavored CommonMark -------------------------
    $leagueGfm = new \League\CommonMark\GithubFlavoredMarkdownConverter();
    $registry['league-gfm'] = static function (string $md) use ($leagueGfm): string {
        return (string) $leagueGfm->convert($md)->getContent();
    };

    // --- 'league-strict' : strict CommonMark -------------------------------
    $leagueStrict = new \League\CommonMark\CommonMarkConverter();
    $registry['league-strict'] = static function (string $md) use ($leagueStrict): string {
        return (string) $leagueStrict->convert($md)->getContent();
    };

    return $registry;
})();

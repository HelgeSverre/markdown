<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Data;

/**
 * Which Markdown dialect md4c parses.
 *
 *   CommonMark — strict CommonMark, every extension off.
 *   GitHub     — GitHub Flavored Markdown: tables, strikethrough, task lists,
 *                and permissive autolinks (the default).
 */
enum Dialect
{
    case CommonMark;
    case GitHub;
}

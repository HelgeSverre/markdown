<?php

declare(strict_types=1);

namespace MarkdownFight;

interface MarkdownParser
{
    public function toHtml(string $markdown): string;

    public function name(): string;
}

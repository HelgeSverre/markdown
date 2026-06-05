<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown;

interface MarkdownParser
{
    public function toHtml(string $markdown): string;
}

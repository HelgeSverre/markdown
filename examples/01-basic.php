<?php

declare(strict_types=1);

/**
 * The simplest possible real use: a Markdown string in, HTML out.
 *
 *   php examples/01-basic.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use HelgeSverre\Markdown\Parser;

$markdown = <<<MD
    # Hello from helgesverre/markdown

    This is **bold**, *italic*, and `inline code`, plus a [link](https://example.com).

    A list that interrupts this paragraph:
    - one
    - two
      - nested
    - three with ~~strikethrough~~

    | Feature | Supported |
    |---------|:---------:|
    | Tables  | ✅ |
    | Tasks   | ✅ |

    - [x] task done
    - [ ] task todo

    > A blockquote, then a bare autolink: visit www.example.com

    ```php
    echo (new Parser())->toHtml('# hi');
    ```
    MD;

echo new Parser()->toHtml($markdown);

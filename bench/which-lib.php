<?php

declare(strict_types=1);

// Prints the native library FfiParser resolves to on this machine.
// A script file (not `php -r`) so it's free of cross-shell quoting issues
// with the namespace backslashes (PowerShell vs bash).

require dirname(__DIR__) . '/vendor/autoload.php';

echo HelgeSverre\Markdown\FfiParser::libPath(), PHP_EOL;

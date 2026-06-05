<?php

declare(strict_types=1);

/**
 * opcache.preload script.
 *
 * Binding the md4c shim once here (at preload time, in the parent process)
 * means every worker request can grab the already-parsed, already-dlopen'd
 * binding instantly via FFI::scope('MD4C') — no per-request C-declaration
 * parse, no per-request dlopen.
 *
 * Guarded so a double-preload (or a stray include) is a graceful no-op.
 */

if (! extension_loaded('FFI')) {
    return;
}

$header = dirname(__DIR__) . '/native/md4cshim.h';

try {
    // If the scope is already bound, FFI::scope() succeeds and we skip the load.
    FFI::scope('MD4C');
} catch (Throwable) {
    try {
        FFI::load($header);
    } catch (Throwable $e) {
        // Never fatal the preload over a missing/blocked lib; the runtime
        // FfiParser fallback (FFI::cdef) still works without the scope.
        fwrite(STDERR, 'preload: FFI::load failed: ' . $e->getMessage() . PHP_EOL);
    }
}

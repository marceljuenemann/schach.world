<?php

namespace SchachWorld;

defined('ABSPATH') || exit;

/**
 * PSR-4 autoloader for the SchachWorld namespace, mapped to this directory.
 *
 * We avoid Composer at runtime: bundled dependencies would need namespace
 * prefixing to avoid clashes with other plugins (see ADR 0001).
 */
spl_autoload_register(function (string $class): void {
    $prefix = __NAMESPACE__ . '\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

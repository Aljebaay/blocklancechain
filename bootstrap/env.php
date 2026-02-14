<?php
declare(strict_types=1);

// Backward-compatible shim: source of truth moved to laravel/bootstrap/env.php.
$laravelEnvBootstrap = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'env.php';
if (!is_file($laravelEnvBootstrap)) {
    throw new RuntimeException('Missing Laravel env bootstrap: ' . $laravelEnvBootstrap);
}
require_once $laravelEnvBootstrap;

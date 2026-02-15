<?php
declare(strict_types=1);

// Backward-compatible shim: source of truth is the project-root bootstrap/env.php.
// After restructure, legacy/ is a subdirectory of the Laravel project root,
// so we go two levels up from legacy/bootstrap/ to reach the root.
$rootEnvBootstrap = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'env.php';
if (!is_file($rootEnvBootstrap)) {
    throw new RuntimeException('Missing root env bootstrap: ' . $rootEnvBootstrap);
}
require_once $rootEnvBootstrap;

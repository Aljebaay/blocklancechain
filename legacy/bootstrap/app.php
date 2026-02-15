<?php
$basePath = dirname(__DIR__);

if (!defined('BLC_BASE_PATH')) {
    define('BLC_BASE_PATH', $basePath);
}

if (!defined('BLC_APP_PATH')) {
    define('BLC_APP_PATH', BLC_BASE_PATH . DIRECTORY_SEPARATOR . 'app');
}

if (!defined('BLC_PLATFORM_PATH')) {
    define('BLC_PLATFORM_PATH', BLC_APP_PATH . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform');
}

// After restructure, the project root (Laravel) is the parent of legacy/.
// The canonical env bootstrap lives at <root>/bootstrap/env.php.
$projectRoot = dirname(BLC_BASE_PATH);
$envBootstrap = $projectRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'env.php';
if (!is_file($envBootstrap)) {
    // Fallback: try the local legacy bootstrap/env.php shim
    $envBootstrap = BLC_BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'env.php';
}
if (is_file($envBootstrap)) {
    require_once $envBootstrap;
    if (function_exists('blc_load_env')) {
        blc_load_env($projectRoot);
    }
}

$sessionBootstrapCandidates = [
    BLC_BASE_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'session_bootstrap.php',
    BLC_PLATFORM_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'session_bootstrap.php',
];

if (!function_exists('blc_bootstrap_session')) {
    foreach ($sessionBootstrapCandidates as $candidate) {
        if (is_file($candidate)) {
            require_once $candidate;
            break;
        }
    }
}

if (!function_exists('blc_bootstrap_session')) {
    throw new RuntimeException('Session bootstrap file was not found in supported locations.');
}

blc_bootstrap_session();

$vendorAutoloadCandidates = [
    BLC_BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
    BLC_PLATFORM_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
];

foreach ($vendorAutoloadCandidates as $vendorAutoload) {
    if (is_file($vendorAutoload)) {
        require_once $vendorAutoload;
        break;
    }
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $prefixLength = strlen($prefix);

    if (strncmp($class, $prefix, $prefixLength) !== 0) {
        return;
    }

    $relativeClass = substr($class, $prefixLength);
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    $file = BLC_APP_PATH . DIRECTORY_SEPARATOR . $relativePath;

    if (is_file($file)) {
        require_once $file;
    }
});

return [
    'app' => require BLC_BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php',
    'db' => require BLC_BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'db.php',
];

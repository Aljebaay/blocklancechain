<?php

$basePath = dirname(__DIR__ , 4);
$envBootstrap = $basePath . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'env.php';
if (!is_file($envBootstrap)) {
    $envBootstrap = $basePath . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'env.php';
}
if (is_file($envBootstrap)) {
    require_once $envBootstrap;
    if (function_exists('blc_load_env')) {
        blc_load_env($basePath . DIRECTORY_SEPARATOR . 'laravel');
    }
}

$appDebug = getenv('APP_DEBUG');
$isDebug = $appDebug !== false && in_array(strtolower((string) $appDebug), ['1', 'true', 'yes', 'on'], true);
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('display_startup_errors', $isDebug ? '1' : '0');

$dbHost = getenv('DB_HOST');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
$dbName = getenv('DB_NAME');
$appUrl = getenv('APP_URL');

if (!defined('DB_HOST')) {
    @define('DB_HOST', $dbHost !== false ? (string) $dbHost : '');
}

if (!defined('DB_USER')) {
    @define('DB_USER', $dbUser !== false ? (string) $dbUser : '');
}

if (!defined('DB_PASS')) {
    @define('DB_PASS', $dbPass !== false ? (string) $dbPass : '');
}

if (!defined('DB_NAME')) {
    @define('DB_NAME', $dbName !== false ? (string) $dbName : '');
}

if (!defined('APP_URL')) {
    @define('APP_URL', $appUrl !== false ? rtrim((string) $appUrl, "/\\") : '');
}

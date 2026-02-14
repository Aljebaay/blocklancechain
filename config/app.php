<?php
declare(strict_types=1);

$basePath = dirname(__DIR__);

return [
    'environment' => getenv('APP_ENV') ?: 'production',
    'paths' => [
        'base' => $basePath,
        'app' => $basePath . DIRECTORY_SEPARATOR . 'app',
        'bootstrap' => $basePath . DIRECTORY_SEPARATOR . 'bootstrap',
        'config' => $basePath . DIRECTORY_SEPARATOR . 'config',
        'storage' => $basePath . DIRECTORY_SEPARATOR . 'storage',
        'sessions' => $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
        'logs' => $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs',
        'cache' => $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache',
    ],
    'endpoint_switch' => [
        'use_new_default' => true,
        'fallback_on_error' => true,
        'overrides' => [],
    ],
];

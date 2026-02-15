<?php
declare(strict_types=1);

return [
    'host' => getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : ''),
    'user' => getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : ''),
    'pass' => getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : ''),
    'name' => getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : ''),
];

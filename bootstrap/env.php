<?php
declare(strict_types=1);

if (!function_exists('blc_parse_env_value')) {
    function blc_parse_env_value(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $firstChar = $value[0];
        $lastChar = substr($value, -1);
        if (($firstChar === '"' || $firstChar === "'") && $lastChar === $firstChar) {
            $value = substr($value, 1, -1);
        }

        return str_replace(
            ['\\n', '\\r', '\\t'],
            ["\n", "\r", "\t"],
            $value
        );
    }
}

if (!function_exists('blc_load_env')) {
    function blc_load_env(?string $basePath = null): void
    {
        static $loaded = [];

        $basePath = $basePath === null ? dirname(__DIR__) : rtrim($basePath, "\\/");
        if ($basePath === '' || isset($loaded[$basePath])) {
            return;
        }

        $envFile = $basePath . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($envFile) || !is_readable($envFile)) {
            $loaded[$basePath] = true;
            return;
        }

        $lines = @file($envFile, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) {
            $loaded[$basePath] = true;
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                continue;
            }

            if (stripos($line, 'export ') === 0) {
                $line = trim(substr($line, 7));
            }

            $separatorPosition = strpos($line, '=');
            if ($separatorPosition === false) {
                continue;
            }

            $name = trim(substr($line, 0, $separatorPosition));
            if ($name === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
                continue;
            }

            if (getenv($name) !== false) {
                continue;
            }

            $value = blc_parse_env_value(substr($line, $separatorPosition + 1));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv($name . '=' . $value);
        }

        $loaded[$basePath] = true;
    }
}

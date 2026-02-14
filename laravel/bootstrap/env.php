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

        $envCandidates = [
            $basePath . DIRECTORY_SEPARATOR . '.env',
            dirname($basePath) . DIRECTORY_SEPARATOR . '.env',
            $basePath . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . '.env',
        ];

        $envFile = null;
        foreach ($envCandidates as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                $envFile = $candidate;
                break;
            }
        }

        if ($envFile === null) {
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

        // Normalize Laravel-style DB keys into legacy keys used by platform modules.
        if (getenv('DB_USER') === false && getenv('DB_USERNAME') !== false) {
            $v = (string) getenv('DB_USERNAME');
            $_ENV['DB_USER'] = $v;
            $_SERVER['DB_USER'] = $v;
            putenv('DB_USER=' . $v);
        }
        if (getenv('DB_PASS') === false && getenv('DB_PASSWORD') !== false) {
            $v = (string) getenv('DB_PASSWORD');
            $_ENV['DB_PASS'] = $v;
            $_SERVER['DB_PASS'] = $v;
            putenv('DB_PASS=' . $v);
        }
        if (getenv('DB_NAME') === false && getenv('DB_DATABASE') !== false) {
            $v = (string) getenv('DB_DATABASE');
            $_ENV['DB_NAME'] = $v;
            $_SERVER['DB_NAME'] = $v;
            putenv('DB_NAME=' . $v);
        }

        $loaded[$basePath] = true;
    }
}

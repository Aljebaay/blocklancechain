<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class LegacyScriptRunner
{
    private static ?array $rootEnvCache = null;

    /**
     * Execute a legacy PHP script in an isolated subprocess, preserving the original
     * superglobals and capturing status/body even when the legacy script calls exit/die.
     *
     * @return array|null ['status' => int, 'body' => string] or null on bootstrap failure
     */
    public static function run(Request $request, string $scriptPath, string $legacyUri, array $options = []): ?array
    {
        if (!is_file($scriptPath)) {
            return null;
        }

        $defaultStatus = isset($options['default_status']) ? (int) $options['default_status'] : 200;
        $timeout = isset($options['timeout']) ? (float) $options['timeout'] : 20.0;
        $extraEnv = isset($options['env']) && is_array($options['env']) ? $options['env'] : [];

        $server = array_merge($_SERVER, [
            'REQUEST_METHOD' => $request->method(),
            'REQUEST_URI' => $legacyUri . ($request->getQueryString() ? '?' . $request->getQueryString() : ''),
            'QUERY_STRING' => $request->getQueryString() ?: http_build_query($request->query()),
            'HTTP_HOST' => $request->getHost(),
            'HTTPS' => $request->isSecure() ? 'on' : 'off',
            'DOCUMENT_ROOT' => realpath(base_path('..' . DIRECTORY_SEPARATOR . 'public')) ?: null,
            'SCRIPT_NAME' => $legacyUri,
            'PHP_SELF' => $legacyUri,
            'SCRIPT_FILENAME' => $scriptPath,
        ]);

        // Build explicit DB values to hand to legacy layer (prefer LEGACY_* then DB_*).
        $legacyHost = env('LEGACY_DB_HOST', env('DB_HOST'));
        $legacyPort = env('LEGACY_DB_PORT', env('DB_PORT'));
        if ($legacyHost && $legacyPort && !str_contains((string) $legacyHost, ':')) {
            $legacyHost = $legacyHost . ':' . $legacyPort;
        }
        $legacyPortRaw = $legacyPort ?: getenv('DB_PORT');
        $legacyDb = env('LEGACY_DB_DATABASE', env('DB_DATABASE'));
        $legacyUser = env('LEGACY_DB_USERNAME', env('DB_USERNAME'));
        $legacyPass = env('LEGACY_DB_PASSWORD', env('DB_PASSWORD'));

        // Merge order: Laravel env, root env, caller extras, forced flags, explicit DB_* overrides.
        $env = array_merge($_ENV, self::loadRootEnv(), $extraEnv, [
            'LEGACY_SERVER' => json_encode($server),
            'LEGACY_GET' => json_encode($request->query->all()),
            'LEGACY_POST' => json_encode($request->request->all()),
            'LEGACY_SCRIPT' => $scriptPath,
            'LEGACY_CWD' => dirname($scriptPath),
            'LEGACY_STATUS_DEFAULT' => (string) $defaultStatus,
            'BLC_SKIP_INSTALL_CHECK' => 'true',
            'DB_HOST' => $legacyHost ?: getenv('DB_HOST'),
            'DB_PORT' => $legacyPortRaw ?: getenv('DB_PORT'),
            'DB_USER' => $legacyUser ?: getenv('DB_USER'),
            'DB_PASS' => $legacyPass ?: getenv('DB_PASS'),
            'DB_NAME' => $legacyDb ?: getenv('DB_NAME'),
        ]);

        $runner = tempnam(sys_get_temp_dir(), 'legacy_runner_');
        if ($runner === false) {
            return null;
        }

        $phpCode = <<<'PHP'
<?php
$server = json_decode(getenv('LEGACY_SERVER') ?: '[]', true) ?: [];
$get = json_decode(getenv('LEGACY_GET') ?: '[]', true) ?: [];
$post = json_decode(getenv('LEGACY_POST') ?: '[]', true) ?: [];
$script = getenv('LEGACY_SCRIPT');
$cwd = getenv('LEGACY_CWD') ?: null;
$defaultStatus = (int) (getenv('LEGACY_STATUS_DEFAULT') ?: 200);

if ($cwd && is_dir($cwd)) {
    chdir($cwd);
}

$_SERVER = array_merge($_SERVER, $server);
$_GET = $get;
$_POST = $post;
$_REQUEST = array_merge($_GET, $_POST);
http_response_code($defaultStatus);

ob_start();
register_shutdown_function(function (): void {
    $status = http_response_code();
    if ($status === false) {
        $status = 200;
    }
    $captured = ob_get_contents();
    if ($captured !== false) {
        ob_end_clean();
    }
    echo 'STATUS:' . $status . "\n";
    if ($captured !== false) {
        echo $captured;
    }
});

require $script;
PHP;

        file_put_contents($runner, $phpCode);

        $process = new Process([PHP_BINARY, $runner]);
        $process->setWorkingDirectory(dirname($scriptPath));
        $process->setEnv($env);
        $process->setTimeout($timeout);
        $process->run();

        $output = $process->getOutput();
        @unlink($runner);

        if ($output === '') {
            return null;
        }

        $markerPos = strrpos($output, 'STATUS:');
        if ($markerPos === false) {
            // Fallback: return raw output with default status when marker missing.
            return [
                'status' => $defaultStatus,
                'body' => $output,
            ];
        }
        $newlinePos = strpos($output, "\n", $markerPos);
        if ($newlinePos === false) {
            return [
                'status' => $defaultStatus,
                'body' => $output,
            ];
        }

        $statusLine = substr($output, $markerPos + strlen('STATUS:'), $newlinePos - ($markerPos + strlen('STATUS:')));
        $body = substr($output, $newlinePos + 1);
        $status = (int) trim($statusLine);

        return [
            'status' => $status > 0 ? $status : $defaultStatus,
            'body' => $body,
        ];
    }

    /**
     * Load root .env (project base, not Laravel/.env) to ensure legacy scripts
     * receive DB_* and related values when running via artisan serve.
     */
    private static function loadRootEnv(): array
    {
        if (self::$rootEnvCache !== null) {
            return self::$rootEnvCache;
        }

        $envPath = base_path('..' . DIRECTORY_SEPARATOR . '.env');
        $vars = [];
        if (is_file($envPath) && is_readable($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = ltrim($line);
                    if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                        continue;
                    }
                    if (stripos($line, 'export ') === 0) {
                        $line = trim(substr($line, 7));
                    }
                    $pos = strpos($line, '=');
                    if ($pos === false) {
                        continue;
                    }
                    $name = trim(substr($line, 0, $pos));
                    if ($name === '') {
                        continue;
                    }
                    $value = trim(substr($line, $pos + 1));
                    $value = trim($value, "\"'");
                    $vars[$name] = $value;
                }
            }
        }

        self::$rootEnvCache = $vars;
        return $vars;
    }
}

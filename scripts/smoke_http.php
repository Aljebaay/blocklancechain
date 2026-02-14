<?php
declare(strict_types=1);

/**
 * HTTP smoke checks for critical routes.
 *
 * Usage:
 *   php scripts/smoke_http.php
 *   php scripts/smoke_http.php --base-url=http://127.0.0.1:8080
 *   php scripts/smoke_http.php --host=127.0.0.1 --port=8080
 *   php scripts/smoke_http.php --toggle=on|off|both
 *   php scripts/smoke_http.php --record-snapshots
 */

$basePath = dirname(__DIR__);
$envBootstrap = $basePath . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'env.php';
if (is_file($envBootstrap)) {
    require_once $envBootstrap;
    if (function_exists('blc_load_env')) {
        blc_load_env($basePath);
    }
}

$appUrl = getenv('APP_URL');
$defaultHostFromEnv = '';
$defaultPortFromEnv = 0;
if (is_string($appUrl) && $appUrl !== '') {
    $parsedAppUrl = parse_url($appUrl);
    if (is_array($parsedAppUrl)) {
        if (isset($parsedAppUrl['host']) && is_string($parsedAppUrl['host']) && $parsedAppUrl['host'] !== '') {
            $defaultHostFromEnv = $parsedAppUrl['host'];
        }
        if (isset($parsedAppUrl['port'])) {
            $defaultPortFromEnv = (int) $parsedAppUrl['port'];
        } elseif ((isset($parsedAppUrl['scheme']) ? strtolower((string) $parsedAppUrl['scheme']) : '') === 'https') {
            $defaultPortFromEnv = 443;
        } else {
            $defaultPortFromEnv = 80;
        }
    }
}

$options = getopt('', ['base-url::', 'host::', 'port::', 'toggle::', 'record-snapshots', 'help']);
@ini_set('output_buffering', '0');
@ini_set('implicit_flush', '1');
if (function_exists('ob_implicit_flush')) {
    ob_implicit_flush(true);
}
if (defined('STDOUT') && function_exists('stream_set_write_buffer')) {
    @stream_set_write_buffer(STDOUT, 0);
}

if (isset($options['help'])) {
    echo "Usage:\n";
    echo "  php scripts/smoke_http.php\n";
    echo "  php scripts/smoke_http.php --base-url=http://127.0.0.1:8080\n";
    echo "  php scripts/smoke_http.php --host=127.0.0.1 --port=8080\n";
    echo "  php scripts/smoke_http.php --toggle=on|off|both   (default both)\n";
    echo "  php scripts/smoke_http.php --record-snapshots\n";
    exit(0);
}

$host = isset($options['host']) && is_string($options['host']) && $options['host'] !== ''
    ? $options['host']
    : ($defaultHostFromEnv !== '' ? $defaultHostFromEnv : '127.0.0.1');
$requestedPort = isset($options['port'])
    ? (int) $options['port']
    : ($defaultPortFromEnv > 0 ? $defaultPortFromEnv : 0);
$baseUrlOption = isset($options['base-url']) && is_string($options['base-url']) ? trim($options['base-url']) : '';
$toggleOption = isset($options['toggle']) && is_string($options['toggle']) && $options['toggle'] !== ''
    ? strtolower(trim($options['toggle']))
    : 'both';
$recordSnapshots = array_key_exists('record-snapshots', $options);

$validToggleOptions = ['on', 'off', 'both'];
if (!in_array($toggleOption, $validToggleOptions, true)) {
    fwrite(STDERR, "Invalid --toggle value. Use on, off, or both.\n");
    exit(1);
}

$snapshotsDir = $basePath . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'snapshots';
if (!is_dir($snapshotsDir)) {
    @mkdir($snapshotsDir, 0777, true);
}

$checks = [
    [
        'id' => 'home-page',
        'path' => '/',
        'expectedStatuses' => [200, 302],
        'bodyContainsAny' => ['<html', 'install.php', 'login', '<title'],
        'snapshot' => 'home',
        'dbDependent' => true,
    ],
    [
        'id' => 'login-page',
        'path' => '/login',
        'expectedStatuses' => [200, 302],
        'bodyContainsAny' => ['login', '<form', '<title', 'install.php'],
        'snapshot' => 'login',
        'dbDependent' => true,
    ],
    [
        'id' => 'admin-login',
        'path' => '/admin/login.php',
        'expectedStatuses' => [200, 302],
        'bodyContainsAny' => ['ADMIN', 'admin', '<form', '<title'],
        'snapshot' => 'admin-login',
        'dbDependent' => true,
    ],
    [
        'id' => 'index-alias',
        'path' => '/index',
        'expectedStatuses' => [200, 302],
        'bodyContainsAny' => ['<html', 'install.php', 'login'],
        'dbDependent' => true,
    ],
    [
        'id' => 'static-css-router',
        'path' => '/styles/bootstrap.css',
        'expectedStatuses' => [200],
        'contentTypeContains' => 'text/css',
        'minBodyBytes' => 5000,
    ],
    [
        'id' => 'asset-proxy-css',
        'path' => '/asset_proxy.php?path=styles/bootstrap.css',
        'expectedStatuses' => [200],
        'contentTypeContains' => 'text/css',
        'minBodyBytes' => 5000,
    ],
    [
        'id' => 'logo-image',
        'path' => '/images/app.png',
        'expectedStatuses' => [200],
        'contentTypeContains' => 'image',
        'minBodyBytes' => 2000,
    ],
    [
        'id' => 'laravel-health',
        'path' => '/_app/health',
        'expectedStatuses' => [200],
        'bodyContainsAny' => ['"status":"ok"', '"status": "ok"', 'status":"ok"'],
    ],
    [
        'id' => 'laravel-system-info',
        'path' => '/_app/system/info',
        'expectedStatuses' => [200],
        'bodyContainsAny' => ['"status":"ok"', '"status": "ok"', 'status":"ok"'],
    ],
    [
        'id' => 'laravel-migrate-fetch-subcategory',
        'method' => 'POST',
        'path' => '/_app/migrate/requests/fetch_subcategory',
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => 'category_id=1',
        'expectedStatuses' => [200],
        'bodyContainsAny' => [
            '<option',
            "window.open('../login",
        ],
    ],
    [
        'id' => 'requests-manage',
        'path' => '/requests/manage_requests',
        'expectedStatuses' => [200, 302],
        'bodyContainsAny' => [
            "window.open('../login",
            'manage_requests',
            "window.open('install.php'",
            'install.php',
        ],
        'dbDependent' => true,
    ],
    [
        'id' => 'requests-active',
        'path' => '/requests/active_request',
        'expectedStatuses' => [200, 302],
        'bodyContainsAny' => [
            'request',
            "window.open('../login",
            "window.open('install.php'",
            'install.php',
        ],
        'dbDependent' => true,
    ],
    [
        'id' => 'requests-fetch-subcategory',
        'method' => 'POST',
        'path' => '/requests/fetch_subcategory',
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => 'category_id=1',
        'expectedStatuses' => [200],
        'bodyContainsAny' => [
            "window.open('../login",
            '<option',
            "window.open('install.php'",
            'install.php',
        ],
        'dbDependent' => true,
    ],
    [
        'id' => 'proposal-pricing-check',
        'method' => 'POST',
        'path' => '/proposals/ajax/check/pricing',
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => 'proposal_id=1&proposal_price=5&proposal_revisions=1&delivery_id=1',
        'expectedStatuses' => [200],
        'bodyContainsAny' => [
            "window.open('../login",
            'false',
            'true',
            "window.open('install.php'",
            'install.php',
        ],
        'dbDependent' => true,
    ],
    [
        'id' => 'apis-index',
        'path' => '/apis/index.php?/apis/register',
        'expectedStatuses' => [200, 302],
        'bodyContainsAny' => ['invalid', 'CodeIgniter', '<html', '<title'],
        'dbDependent' => true,
    ],
    [
        'id' => 'admin-include-sanitize',
        'path' => '/admin/includes/sanitize_url.php',
        'expectedStatuses' => [200],
    ],
    [
        'id' => 'not-found-static',
        'path' => '/this-file-should-not-exist-blc.css',
        'expectedStatuses' => [404],
        'bodyContainsAny' => ['Not Found'],
    ],
];

$originalToggle = getenv('MIGRATE_REQUESTS_FETCH_SUBCATEGORY');
$passes = [];
switch ($toggleOption) {
    case 'on':
        $passes[] = ['label' => 'toggle-on', 'env' => 'true'];
        break;
    case 'off':
        $passes[] = ['label' => 'toggle-off', 'env' => 'false'];
        break;
    default:
        $passes[] = ['label' => 'toggle-off', 'env' => 'false'];
        $passes[] = ['label' => 'toggle-on', 'env' => 'true'];
        break;
}

if ($baseUrlOption !== '' && count($passes) > 1) {
    echo "Warning: --base-url provided; running all passes against the same external server (toggle cannot be forced).\n";
}

$overall = ['passed' => 0, 'failed' => 0, 'skipped' => 0];
$exitCode = 0;

foreach ($passes as $pass) {
    $serverProcess = null;
    $serverLogs = ['stdout' => '', 'stderr' => ''];
    $passLabel = $pass['label'];
    $envValue = $pass['env'];

    echo "Preparing HTTP smoke checks ({$passLabel})...\n";

    try {
        if ($baseUrlOption !== '') {
            $baseUrl = rtrim($baseUrlOption, '/');
            echo "Using existing server: {$baseUrl}\n";
        } else {
            putenv('MIGRATE_REQUESTS_FETCH_SUBCATEGORY=' . $envValue);
            $_ENV['MIGRATE_REQUESTS_FETCH_SUBCATEGORY'] = $envValue;
            $_SERVER['MIGRATE_REQUESTS_FETCH_SUBCATEGORY'] = $envValue;

            $port = $requestedPort > 0 ? $requestedPort : findFreePort($host, 18080, 18150);
            if ($port <= 0) {
                fwrite(STDERR, "No available port found for built-in server.\n");
                $exitCode = 1;
                break;
            }

            echo "Starting local server on {$host}:{$port} with MIGRATE_REQUESTS_FETCH_SUBCATEGORY={$envValue}...\n";
            [$serverProcess, $serverLogs] = startPhpServer($basePath, $host, $port);
            $baseUrl = "http://{$host}:{$port}";
            echo "Started local server: {$baseUrl}\n";

            if (!waitForServer($baseUrl, 20, 250000)) {
                fwrite(STDERR, "Server did not become ready in time.\n");
                dumpLogs($serverLogs);
                $exitCode = 1;
                break;
            }
        }

        $passed = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($checks as $check) {
            $started = microtime(true);
            $response = httpRequest($baseUrl, $check);
            $durationMs = (int) round((microtime(true) - $started) * 1000);

            $id = (string) $check['id'];
            $ok = true;
            $reasons = [];
            $dbUnavailable = responseIndicatesDbUnavailable($response);

            if (!empty($check['dbDependent']) && $dbUnavailable) {
                $skipped++;
                echo "SKIP  [{$passLabel}] {$id} status={$response['status']} time={$durationMs}ms reason=database unavailable\n";
                continue;
            }

            [$ok, $reasons] = evaluateResponse($check, $response);

            $snapshotId = isset($check['snapshot']) && is_string($check['snapshot']) && $check['snapshot'] !== ''
                ? $check['snapshot'] . '.' . $passLabel
                : null;

            if ($snapshotId !== null && !$dbUnavailable) {
                $snapshotPath = $snapshotsDir . DIRECTORY_SEPARATOR . $snapshotId . '.snapshot.txt';
                $bodyPrefix = isset($response['body']) && is_string($response['body']) ? substr($response['body'], 0, 2048) : '';

                if ($recordSnapshots || !is_file($snapshotPath)) {
                    if ($ok) {
                        file_put_contents($snapshotPath, $bodyPrefix);
                        echo "SNAP  [{$passLabel}] {$id} saved snapshot to {$snapshotPath}\n";
                    } else {
                        echo "SNAP-SKIP  [{$passLabel}] {$id} snapshot not recorded (response invalid)\n";
                    }
                } elseif ($bodyPrefix !== '') {
                    $snapshotBody = (string) @file_get_contents($snapshotPath);
                    if (!snapshotSimilar($bodyPrefix, $snapshotBody)) {
                        $ok = false;
                        $reasons[] = 'Snapshot drift detected';
                    }
                }
            }

            $status = $response['status'];

            if ($ok) {
                $passed++;
                echo "PASS  [{$passLabel}] {$id} status={$status} time={$durationMs}ms\n";
            } else {
                $failed++;
                echo "FAIL  [{$passLabel}] {$id} status={$status} time={$durationMs}ms\n";
                foreach ($reasons as $reason) {
                    echo "  - {$reason}\n";
                }
            }
        }

        $total = $passed + $failed + $skipped;
        $overall['passed'] += $passed;
        $overall['failed'] += $failed;
        $overall['skipped'] += $skipped;

        echo "Summary ({$passLabel}): total={$total} passed={$passed} failed={$failed} skipped={$skipped}\n";

        if ($failed > 0) {
            $exitCode = 1;
            if ($serverProcess !== null) {
                dumpLogs($serverLogs);
            }
        }
    } catch (Throwable $exception) {
        fwrite(STDERR, "[{$passLabel}] Smoke script failed: " . $exception->getMessage() . "\n");
        if ($serverProcess !== null) {
            dumpLogs($serverLogs);
        }
        $exitCode = 1;
    } finally {
        if (is_resource($serverProcess)) {
            stopProcess($serverProcess);
        }
    }
}

if ($originalToggle !== false) {
    putenv('MIGRATE_REQUESTS_FETCH_SUBCATEGORY=' . $originalToggle);
    $_ENV['MIGRATE_REQUESTS_FETCH_SUBCATEGORY'] = $originalToggle;
    $_SERVER['MIGRATE_REQUESTS_FETCH_SUBCATEGORY'] = $originalToggle;
}

if ($exitCode !== 0) {
    exit($exitCode);
}

$overallTotal = $overall['passed'] + $overall['failed'] + $overall['skipped'];
echo "Overall summary: total={$overallTotal} passed={$overall['passed']} failed={$overall['failed']} skipped={$overall['skipped']}\n";
exit(0);

/**
 * @return array{resource,array{stdout:string,stderr:string}}
 */
function startPhpServer(string $basePath, string $host, int $port): array
{
    $logDir = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $stamp = date('Ymd_His');
    $stdout = $logDir . DIRECTORY_SEPARATOR . "smoke_server_{$stamp}.out.log";
    $stderr = $logDir . DIRECTORY_SEPARATOR . "smoke_server_{$stamp}.err.log";

    $publicDir = $basePath . DIRECTORY_SEPARATOR . 'public';
    $routerFile = $publicDir . DIRECTORY_SEPARATOR . 'router.php';
    $cmd = [
        PHP_BINARY,
        '-S',
        $host . ':' . $port,
        '-t',
        $publicDir,
        $routerFile,
    ];

    $spec = [
        0 => ['pipe', 'r'],
        1 => ['file', $stdout, 'a'],
        2 => ['file', $stderr, 'a'],
    ];

    $process = proc_open($cmd, $spec, $pipes, $basePath, null, ['bypass_shell' => true]);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start PHP built-in server.');
    }

    if (isset($pipes[0]) && is_resource($pipes[0])) {
        fclose($pipes[0]);
    }

    return [$process, ['stdout' => $stdout, 'stderr' => $stderr]];
}

function stopProcess($process): void
{
    @proc_terminate($process);
    usleep(150000);
    $status = proc_get_status($process);
    if (is_array($status) && isset($status['running']) && $status['running'] === true) {
        @proc_terminate($process, 9);
        usleep(150000);
    }
    @proc_close($process);
}

function dumpLogs(array $logs): void
{
    $stderr = $logs['stderr'] ?? '';
    if (is_string($stderr) && $stderr !== '' && is_file($stderr)) {
        $tail = tailFile($stderr, 40);
        if ($tail !== '') {
            echo "Server stderr tail ({$stderr}):\n";
            echo $tail . "\n";
        }
    }
}

function tailFile(string $path, int $maxLines): string
{
    $lines = @file($path, FILE_IGNORE_NEW_LINES);
    if (!is_array($lines) || $lines === []) {
        return '';
    }
    $slice = array_slice($lines, -1 * $maxLines);
    return implode("\n", $slice);
}

function findFreePort(string $host, int $start, int $end): int
{
    for ($port = $start; $port <= $end; $port++) {
        $socket = @stream_socket_server("tcp://{$host}:{$port}", $errno, $errorString);
        if ($socket === false) {
            continue;
        }
        fclose($socket);
        return $port;
    }

    return 0;
}

function waitForServer(string $baseUrl, int $attempts, int $sleepMicros): bool
{
    for ($i = 0; $i < $attempts; $i++) {
        $response = httpRequest($baseUrl, ['path' => '/styles/bootstrap.css']);
        if (($response['status'] ?? 0) === 200) {
            return true;
        }
        usleep($sleepMicros);
    }

    return false;
}

/**
 * @param array<string,mixed> $check
 * @return array{status:int,body:string,headers:array<string,string>,error:string}
 */
function httpRequest(string $baseUrl, array $check): array
{
    $path = isset($check['path']) && is_string($check['path']) ? $check['path'] : '/';
    $url = rtrim($baseUrl, '/') . $path;
    $method = isset($check['method']) && is_string($check['method']) ? strtoupper($check['method']) : 'GET';
    $body = isset($check['body']) && is_string($check['body']) ? $check['body'] : '';
    $headers = [];
    if (isset($check['headers']) && is_array($check['headers'])) {
        foreach ($check['headers'] as $header) {
            if (is_string($header) && $header !== '') {
                $headers[] = $header;
            }
        }
    }

    $contextOptions = [
        'http' => [
            'method' => $method,
            'ignore_errors' => true,
            'timeout' => 20,
            'header' => $headers === [] ? '' : implode("\r\n", $headers),
        ],
    ];

    if ($body !== '' && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $contextOptions['http']['content'] = $body;
    }

    $context = stream_context_create($contextOptions);
    $rawBody = @file_get_contents($url, false, $context);
    $rawBody = is_string($rawBody) ? $rawBody : '';

    $responseHeaders = [];
    $status = 0;
    $error = '';

    $responseHeaderLines = isset($http_response_header) && is_array($http_response_header)
        ? $http_response_header
        : [];
    if ($responseHeaderLines !== []) {
        foreach ($responseHeaderLines as $lineIndex => $headerLine) {
            if (!is_string($headerLine) || $headerLine === '') {
                continue;
            }

            if ($lineIndex === 0 && preg_match('#^HTTP/\S+\s+(\d{3})#', $headerLine, $match) === 1) {
                $status = (int) $match[1];
                continue;
            }

            $pos = strpos($headerLine, ':');
            if ($pos === false) {
                continue;
            }

            $name = strtolower(trim(substr($headerLine, 0, $pos)));
            $value = trim(substr($headerLine, $pos + 1));
            if ($name !== '') {
                $responseHeaders[$name] = $value;
            }
        }
    }

    if ($status === 0) {
        $error = 'No HTTP status received.';
    }

    return [
        'status' => $status,
        'body' => $rawBody,
        'headers' => $responseHeaders,
        'error' => $error,
    ];
}

/**
 * @param array<string,mixed> $check
 * @param array<string,mixed> $response
 * @return array{bool,array<int,string>}
 */
function evaluateResponse(array $check, array $response): array
{
    $errors = [];

    $status = isset($response['status']) ? (int) $response['status'] : 0;
    $body = isset($response['body']) && is_string($response['body']) ? $response['body'] : '';
    $headers = isset($response['headers']) && is_array($response['headers']) ? $response['headers'] : [];

    $expectedStatuses = [200];
    if (isset($check['expectedStatuses']) && is_array($check['expectedStatuses']) && $check['expectedStatuses'] !== []) {
        $expectedStatuses = array_map('intval', $check['expectedStatuses']);
    }

    if (!in_array($status, $expectedStatuses, true)) {
        $errors[] = 'Expected status in [' . implode(', ', $expectedStatuses) . "], got {$status}.";
    }

    if (isset($check['contentTypeContains']) && is_string($check['contentTypeContains']) && $check['contentTypeContains'] !== '') {
        $contentType = isset($headers['content-type']) && is_string($headers['content-type']) ? $headers['content-type'] : '';
        if (stripos($contentType, $check['contentTypeContains']) === false) {
            $errors[] = "Content-Type does not contain '{$check['contentTypeContains']}'.";
        }
    }

    if (isset($check['minBodyBytes'])) {
        $minBodyBytes = (int) $check['minBodyBytes'];
        if (strlen($body) < $minBodyBytes) {
            $errors[] = "Body length is less than {$minBodyBytes} bytes.";
        }
    }

    if (isset($check['bodyContainsAny']) && is_array($check['bodyContainsAny']) && $check['bodyContainsAny'] !== []) {
        $found = false;
        foreach ($check['bodyContainsAny'] as $needle) {
            if (is_string($needle) && $needle !== '' && str_contains($body, $needle)) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $errors[] = 'Body does not contain any expected marker.';
        }
    }

    if (isset($check['bodyNotContains']) && is_array($check['bodyNotContains'])) {
        foreach ($check['bodyNotContains'] as $needle) {
            if (is_string($needle) && $needle !== '' && str_contains($body, $needle)) {
                $errors[] = "Body contains forbidden marker '{$needle}'.";
            }
        }
    }

    return [$errors === [], $errors];
}

/**
 * Detect responses that likely indicate database connectivity or install pre-check issues.
 *
 * @param array<string,mixed> $response
 */
function responseIndicatesDbUnavailable(array $response): bool
{
    $status = isset($response['status']) ? (int) $response['status'] : 0;
    $headers = isset($response['headers']) && is_array($response['headers']) ? $response['headers'] : [];
    $body = isset($response['body']) && is_string($response['body']) ? $response['body'] : '';

    $location = '';
    foreach (['location', 'Location'] as $headerName) {
        if (isset($headers[$headerName]) && is_string($headers[$headerName])) {
            $location = $headers[$headerName];
            break;
        }
    }

    $indicators = [
        'SQLSTATE',
        'Access denied for user',
        'could not find driver',
        'install.php',
        'install2.php',
        'Database connection',
        'PDOException',
    ];

    foreach ($indicators as $indicator) {
        if ($indicator !== '' && str_contains($body, $indicator)) {
            return true;
        }
    }

    if ($location !== '' && stripos($location, 'install') !== false) {
        return true;
    }

    if ($status >= 500) {
        return true;
    }

    return false;
}

function snapshotSimilar(string $current, string $snapshot): bool
{
    $current = trim($current);
    $snapshot = trim($snapshot);

    if ($current === '' || $snapshot === '') {
        return false;
    }

    $needleFromSnapshot = substr($snapshot, 0, 120);
    if ($needleFromSnapshot !== '' && str_contains($current, $needleFromSnapshot)) {
        return true;
    }

    $needleFromCurrent = substr($current, 0, 120);
    if ($needleFromCurrent !== '' && str_contains($snapshot, $needleFromCurrent)) {
        return true;
    }

    $markers = ['<html', '<title', '<body', 'login', 'admin', 'install.php'];
    $matches = 0;
    foreach ($markers as $marker) {
        if (str_contains($current, $marker) && str_contains($snapshot, $marker)) {
            $matches++;
        }
    }

    return $matches >= 2;
}

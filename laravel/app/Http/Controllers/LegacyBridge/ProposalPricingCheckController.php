<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\Process\Process;

class ProposalPricingCheckController extends Controller
{
    public function __invoke(Request $request)
    {
        $script = base_path('..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'proposals' . DIRECTORY_SEPARATOR . 'ajax' . DIRECTORY_SEPARATOR . 'check' . DIRECTORY_SEPARATOR . 'pricing.php');
        $result = $this->runLegacyScriptIsolated(
            $script,
            $request,
            '/proposals/ajax/check/pricing'
        );

        if ($result === null || $result['status'] !== 200 || $result['body'] === '') {
            return response('', 500);
        }

        $contentType = str_starts_with(ltrim($result['body']), '<script') ? 'text/html; charset=UTF-8' : 'application/json; charset=UTF-8';

        return response($result['body'], $result['status'], [
            'Content-Type' => $contentType,
        ]);
    }

    /**
     * Execute the legacy script in an isolated PHP process so exit/die cannot kill the bridge.
     */
    private function runLegacyScriptIsolated(string $scriptPath, Request $request, string $legacyUri): ?array
    {
        if (!is_file($scriptPath)) {
            return null;
        }

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

        $env = array_merge($_ENV, [
            'LEGACY_SERVER' => json_encode($server),
            'LEGACY_GET' => json_encode($request->query->all()),
            'LEGACY_POST' => json_encode($request->request->all()),
            'LEGACY_SCRIPT' => $scriptPath,
            'LEGACY_CWD' => dirname($scriptPath),
            'LEGACY_STATUS_DEFAULT' => '200',
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
        $process->setTimeout(20);
        $process->run();

        $output = $process->getOutput();
        @unlink($runner);

        if ($output === '') {
            $this->logRunnerFailure($scriptPath, $process);
            return null;
        }

        $markerPos = strrpos($output, 'STATUS:');
        if ($markerPos === false) {
            $this->logRunnerFailure($scriptPath, $process, $output);
            return null;
        }
        $newlinePos = strpos($output, "\n", $markerPos);
        if ($newlinePos === false) {
            $this->logRunnerFailure($scriptPath, $process, $output);
            return null;
        }

        $statusLine = substr($output, $markerPos + strlen('STATUS:'), $newlinePos - ($markerPos + strlen('STATUS:')));
        $body = substr($output, $newlinePos + 1);
        $status = (int) trim($statusLine);

        return [
            'status' => $status,
            'body' => $body,
        ];
    }

    private function logRunnerFailure(string $scriptPath, Process $process, string $output = ''): void
    {
        $logPath = base_path('storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'legacy_runner.log');
        $context = [
            'script' => $scriptPath,
            'exit_code' => $process->getExitCode(),
            'error_output' => $process->getErrorOutput(),
            'raw_output' => $output === '' ? $process->getOutput() : $output,
        ];
        @file_put_contents($logPath, '[' . date('c') . '] ' . json_encode($context) . PHP_EOL, FILE_APPEND);
    }
}

<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;

class ApisIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $script = base_path('legacy' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'apis' . DIRECTORY_SEPARATOR . 'index.php');
        $result = LegacyScriptRunner::run($request, $script, '/apis/index.php');

        if (!$this->isUsableResult($result)) {
            return response('', 500);
        }

        $status = (int) ($result['status'] ?? 200);
        $headers = $this->normalizeHeaders(is_array($result['headers'] ?? null) ? $result['headers'] : []);
        if (!$this->hasHeader($headers, 'Content-Type')) {
            $headers['Content-Type'] = 'text/html; charset=UTF-8';
        }

        return response((string) ($result['body'] ?? ''), $status > 0 ? $status : 200, $headers);
    }

    private function isUsableResult(?array $result): bool
    {
        if (!is_array($result)) {
            return false;
        }

        $status = (int) ($result['status'] ?? 0);
        $body = (string) ($result['body'] ?? '');
        if ($status <= 0) {
            return false;
        }

        if ($body !== '') {
            return true;
        }

        $headerLines = is_array($result['headers'] ?? null) ? $result['headers'] : [];
        return in_array($status, [301, 302, 303, 307, 308], true)
            && ($headerLines === [] || $this->hasHeaderLine($headerLines, 'Location'));
    }

    /**
     * @param array<int, mixed> $headerLines
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headerLines): array
    {
        $headers = [];
        foreach ($headerLines as $headerLine) {
            if (!is_string($headerLine) || $headerLine === '') {
                continue;
            }

            $pos = strpos($headerLine, ':');
            if ($pos === false) {
                continue;
            }

            $name = trim(substr($headerLine, 0, $pos));
            $value = trim(substr($headerLine, $pos + 1));
            if ($name === '' || $value === '') {
                continue;
            }

            if (strcasecmp($name, 'Content-Length') === 0 || strcasecmp($name, 'Transfer-Encoding') === 0) {
                continue;
            }

            $headers[$name] = $value;
        }

        return $headers;
    }

    /**
     * @param array<string, string> $headers
     */
    private function hasHeader(array $headers, string $name): bool
    {
        foreach ($headers as $headerName => $value) {
            if (strcasecmp($headerName, $name) === 0 && $value !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, mixed> $headerLines
     */
    private function hasHeaderLine(array $headerLines, string $name): bool
    {
        foreach ($headerLines as $headerLine) {
            if (!is_string($headerLine) || $headerLine === '') {
                continue;
            }

            $pos = strpos($headerLine, ':');
            if ($pos === false) {
                continue;
            }

            $headerName = trim(substr($headerLine, 0, $pos));
            $headerValue = trim(substr($headerLine, $pos + 1));
            if ($headerName !== '' && $headerValue !== '' && strcasecmp($headerName, $name) === 0) {
                return true;
            }
        }

        return false;
    }
}

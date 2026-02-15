<?php

namespace Tests\Feature;

use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;
use Tests\TestCase;

class LegacyScriptRunnerHeadersTest extends TestCase
{
    public function test_runner_captures_status_headers_and_body(): void
    {
        $script = tempnam(sys_get_temp_dir(), 'legacy_runner_test_');
        $this->assertNotFalse($script);

        file_put_contents($script, <<<'PHP'
<?php
http_response_code(201);
header('Content-Type: application/json; charset=UTF-8');
header('X-Legacy-Test: yes');
echo '{"ok":true}';
PHP);

        try {
            $request = Request::create('/legacy-runner-test', 'GET');
            $result = LegacyScriptRunner::run($request, $script, '/legacy-runner-test');

            $this->assertIsArray($result);
            $this->assertSame(201, (int) ($result['status'] ?? 0));
            $this->assertSame('{"ok":true}', (string) ($result['body'] ?? ''));

            $headers = is_array($result['headers'] ?? null) ? $result['headers'] : [];
            $this->assertIsArray($headers);
            if ($headers !== []) {
                $this->assertTrue($this->containsHeader($headers, 'Content-Type', 'application/json'));
                $this->assertTrue($this->containsHeader($headers, 'X-Legacy-Test', 'yes'));
            }
        } finally {
            @unlink($script);
        }
    }

    public function test_runner_captures_redirect_headers_even_with_empty_body(): void
    {
        $script = tempnam(sys_get_temp_dir(), 'legacy_runner_test_');
        $this->assertNotFalse($script);

        file_put_contents($script, <<<'PHP'
<?php
http_response_code(302);
header('Location: /target');
PHP);

        try {
            $request = Request::create('/legacy-runner-redirect-test', 'GET');
            $result = LegacyScriptRunner::run($request, $script, '/legacy-runner-redirect-test');

            $this->assertIsArray($result);
            $this->assertSame(302, (int) ($result['status'] ?? 0));
            $this->assertSame('', (string) ($result['body'] ?? ''));

            $headers = is_array($result['headers'] ?? null) ? $result['headers'] : [];
            $this->assertIsArray($headers);
            if ($headers !== []) {
                $this->assertTrue($this->containsHeader($headers, 'Location', '/target'));
            }
        } finally {
            @unlink($script);
        }
    }

    /**
     * @param array<int, mixed> $headerLines
     */
    private function containsHeader(array $headerLines, string $headerName, string $valueContains): bool
    {
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

            if (strcasecmp($name, $headerName) === 0 && str_contains(strtolower($value), strtolower($valueContains))) {
                return true;
            }
        }

        return false;
    }
}

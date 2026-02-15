<?php

namespace Tests\Feature;

use App\Http\Controllers\LegacyBridge\RequestsPauseRequestController;
use App\Support\LegacyWriteConnection;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class RequestsPauseRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        Mockery::close();
        parent::tearDown();
    }

    public function test_pauses_request_and_shows_alert()
    {
        $this->startNativeSession();
        $_SESSION['seller_user_name'] = 'alice';

        $connection = new class {
            public function __construct()
            {
                $this->selectResult = (object) ['seller_id' => 5];
                $this->updateResult = 1;
            }
            public function selectOne($query, $bindings = [])
            {
                return $this->selectResult;
            }
            public function update($query, $bindings = [])
            {
                return $this->updateResult;
            }
            public function transaction($callback)
            {
                return $callback($this);
            }
        };

        Mockery::mock('alias:' . LegacyWriteConnection::class)
            ->shouldReceive('connection')
            ->andReturn($connection);

        $controller = new RequestsPauseRequestController();
        $request = Request::create('/_app/migrate/requests/pause_request', 'GET', ['request_id' => 42]);

        $response = $controller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("alert('One request has been paused.", $response->getContent());
        $this->assertStringContainsString("window.open('manage_requests','_self')", $response->getContent());
    }

    public function test_redirects_without_alert_when_no_rows_updated()
    {
        $this->startNativeSession();
        $_SESSION['seller_user_name'] = 'alice';

        $connection = new class {
            public function selectOne($query, $bindings = [])
            {
                return (object) ['seller_id' => 5];
            }
            public function update($query, $bindings = [])
            {
                return 0;
            }
            public function transaction($callback)
            {
                return $callback($this);
            }
        };

        Mockery::mock('alias:' . LegacyWriteConnection::class)
            ->shouldReceive('connection')
            ->andReturn($connection);

        $controller = new RequestsPauseRequestController();
        $request = Request::create('/_app/migrate/requests/pause_request', 'GET', ['request_id' => 0]);

        $response = $controller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringNotContainsString('alert(', $response->getContent());
        $this->assertStringContainsString("window.open('manage_requests','_self')", $response->getContent());
    }

    public function test_login_redirect_when_not_authenticated()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $controller = new RequestsPauseRequestController();
        $request = Request::create('/_app/migrate/requests/pause_request', 'GET', ['request_id' => 1]);

        $response = $controller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("window.open('../login','_self')", $response->getContent());
    }

    private function startNativeSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $projectBase = base_path('legacy');
        $platformBase = $projectBase . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform';

        $candidatePaths = [
            $projectBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
            $platformBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
            $platformBase . DIRECTORY_SEPARATOR . '.sessions',
            rtrim(sys_get_temp_dir(), "\\/") . DIRECTORY_SEPARATOR . 'gig-zone_sessions',
        ];

        foreach ($candidatePaths as $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0777, true);
            }
            if (is_dir($path) && is_writable($path)) {
                session_save_path($path);
                break;
            }
        }

        @ini_set('session.use_strict_mode', '1');
        @ini_set('session.use_only_cookies', '1');
        @session_start();
    }
}

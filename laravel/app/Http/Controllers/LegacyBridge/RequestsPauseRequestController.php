<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestsPauseRequestController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->bootstrapLegacySession();

        $sellerUserName = $_SESSION['seller_user_name'] ?? null;
        if (!is_string($sellerUserName) || $sellerUserName === '') {
            return $this->loginRedirect();
        }

        $requestId = $this->sanitizeRequestId($request->query('request_id'));
        if ($requestId === null) {
            return $this->manageRedirect(false);
        }

        $connection = DB::connection('legacy_write');

        try {
            $sellerRow = $connection->selectOne(
                'select seller_id from sellers where seller_user_name = ? limit 1',
                [$sellerUserName]
            );
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        if (!$sellerRow || !isset($sellerRow->seller_id)) {
            return $this->loginRedirect();
        }

        $sellerId = (int) $sellerRow->seller_id;

        try {
            $affected = $connection->transaction(function () use ($connection, $requestId, $sellerId) {
                return $connection->update(
                    'update buyer_requests set request_status = ? where request_id = ? and seller_id = ?',
                    ['pause', $requestId, $sellerId]
                );
            });
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        return $this->manageRedirect($affected === 1);
    }

    private function bootstrapLegacySession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $projectBase = realpath(base_path('..')) ?: base_path('..');
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

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }

    private function sanitizeRequestId(mixed $input): ?int
    {
        if ($input === null) {
            return null;
        }
        if (is_numeric($input)) {
            $value = (int) $input;
            return $value >= 0 ? $value : null;
        }

        return null;
    }

    private function loginRedirect()
    {
        $body = "<script>window.open('../login','_self')</script>";

        return response($body, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function manageRedirect(bool $showAlert)
    {
        $body = '';
        if ($showAlert) {
            $body .= "<script>alert('One request has been paused.');</script>";
        }
        $body .= "<script>window.open('manage_requests','_self')</script>";

        return response($body, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}

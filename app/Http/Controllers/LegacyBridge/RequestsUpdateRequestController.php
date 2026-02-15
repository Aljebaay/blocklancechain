<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use App\Support\LegacyWriteConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestsUpdateRequestController extends Controller
{
    public function __invoke(Request $request)
    {
        if (filter_var(env('FORCE_LARAVEL_REQUESTS_MODULE_FAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 500);
        }

        $this->bootstrapLegacySession($request);
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $sellerUserName = $_SESSION['seller_user_name'] ?? null;
        if (!is_string($sellerUserName) || $sellerUserName === '') {
            return $this->loginRedirect();
        }

        $allowWrites = filter_var(env('SMOKE_ALLOW_WRITES', false), FILTER_VALIDATE_BOOLEAN);
        $requestId = $this->sanitizeRequestId($request->input('request_id'));
        if ($requestId === null) {
            return $this->manageRedirect();
        }

        $updateData = $this->extractUpdateData($request);
        if ($updateData === []) {
            return $this->manageRedirect();
        }

        // If writes are disabled, act as a no-op but keep legacy response shape.
        if (!$allowWrites) {
            return $this->manageRedirect();
        }

        try {
            $conn = LegacyWriteConnection::connection();
            $sellerRow = $conn->selectOne(
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
            $affected = $conn->transaction(function () use ($conn, $updateData, $requestId, $sellerId) {
                $sets = [];
                $values = [];
                foreach ($updateData as $column => $value) {
                    $sets[] = "{$column} = ?";
                    $values[] = $value;
                }
                $values[] = $requestId;
                $values[] = $sellerId;

                $sql = 'update buyer_requests set ' . implode(', ', $sets) . ' where request_id = ? and seller_id = ?';

                return $conn->update($sql, $values);
            });
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        // Legacy behavior: redirect regardless of affected count.
        return $this->manageRedirect();
    }

    private function extractUpdateData(Request $request): array
    {
        $columns = [
            'request_title' => 'request_title',
            'request_description' => 'request_description',
            'request_budget' => 'request_budget',
            'delivery_time' => 'delivery_time',
            'cat_id' => 'cat_id',
            'child_id' => 'child_id',
        ];

        $data = [];
        foreach ($columns as $column => $inputKey) {
            $value = $request->input($inputKey);
            if ($value === null || $value === '') {
                continue;
            }
            if (in_array($column, ['cat_id', 'child_id']) && !is_numeric($value)) {
                continue;
            }
            $data[$column] = $value;
        }

        return $data;
    }

    private function sanitizeRequestId($value): ?int
    {
        if ($value === null) {
            return null;
        }
        if (!is_numeric($value)) {
            return null;
        }
        $int = (int) $value;
        return $int >= 0 ? $int : null;
    }

    private function bootstrapLegacySession(Request $request): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && session_name() === 'PHPSESSID') {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $_SESSION = [];

        @ini_set('session.use_strict_mode', '1');
        @ini_set('session.use_only_cookies', '1');

        session_name('PHPSESSID');
        $legacyCookieId = $request->cookies->get('PHPSESSID');
        if (is_string($legacyCookieId) && $legacyCookieId !== '') {
            @session_id($legacyCookieId);
        } else {
            unset($_COOKIE['PHPSESSID']);
            session_id(session_create_id());
        }

        $legacyBootstrap = base_path('legacy' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'session_bootstrap.php');
        if (is_file($legacyBootstrap)) {
            require_once $legacyBootstrap;
            if (function_exists('blc_bootstrap_session')) {
                blc_bootstrap_session();
            }
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }

    private function loginRedirect()
    {
        $body = "<script>window.open('../login','_self')</script>";

        return response($body, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function manageRedirect()
    {
        $body = "<script>window.open('manage_requests','_self')</script>";

        return response($body, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}

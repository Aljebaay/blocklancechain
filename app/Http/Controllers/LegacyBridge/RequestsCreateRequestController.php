<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use App\Support\LegacyWriteConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestsCreateRequestController extends Controller
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

        // If writes are disabled, just redirect to manage without touching DB.
        if (!$allowWrites) {
            return $this->manageRedirect();
        }

        $input = $this->extractInput($request);
        if (!$input['isValid']) {
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
        $now = date('Y-m-d');

        try {
            $conn->transaction(function () use ($conn, $sellerId, $input, $now) {
                $conn->insert(
                    'insert into buyer_requests (seller_id, cat_id, child_id, request_title, request_description, request_file, delivery_time, request_budget, request_date, isS3, request_status) values (?,?,?,?,?,?,?,?,?,?,?)',
                    [
                        $sellerId,
                        $input['cat_id'],
                        $input['child_id'],
                        $input['request_title'],
                        $input['request_description'],
                        '',
                        $input['delivery_time'],
                        $input['request_budget'],
                        $now,
                        0,
                        'pending',
                    ]
                );
            });
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        return $this->manageRedirect();
    }

    private function extractInput(Request $request): array
    {
        $catId = $request->input('cat_id');
        $childId = $request->input('child_id');
        $title = $request->input('request_title');
        $description = $request->input('request_description');
        $delivery = $request->input('delivery_time');
        $budget = $request->input('request_budget');

        $valid =
            is_numeric($catId) &&
            is_numeric($childId) &&
            is_string($title) && $title !== '' &&
            is_string($description) && $description !== '' &&
            is_string($delivery) && $delivery !== '' &&
            (is_numeric($budget) || is_string($budget));

        return [
            'isValid' => $valid,
            'cat_id' => $valid ? (int) $catId : null,
            'child_id' => $valid ? (int) $childId : null,
            'request_title' => $valid ? $title : null,
            'request_description' => $valid ? $description : null,
            'delivery_time' => $valid ? $delivery : null,
            'request_budget' => $valid ? $budget : null,
        ];
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

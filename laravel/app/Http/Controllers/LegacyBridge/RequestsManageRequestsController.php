<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestsManageRequestsController extends Controller
{
    public function __invoke(Request $request)
    {
        if (filter_var(env('FORCE_LARAVEL_REQUESTS_MODULE_FAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 500);
        }

        $this->bootstrapLegacySession();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $sellerUserName = $_SESSION['seller_user_name'] ?? null;
        if (!is_string($sellerUserName) || $sellerUserName === '') {
            return $this->loginRedirect();
        }

        $legacy = DB::connection('legacy');

        try {
            $sellerRow = $legacy
                ->table('sellers')
                ->select('seller_id')
                ->where('seller_user_name', $sellerUserName)
                ->first();
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        if (!$sellerRow || !isset($sellerRow->seller_id)) {
            return $this->loginRedirect();
        }

        $sellerId = (int) $sellerRow->seller_id;

        try {
            $requests = $legacy
                ->table('buyer_requests')
                ->where('seller_id', $sellerId)
                ->orderByDesc('request_id')
                ->get();
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        $grouped = [
            'active' => [],
            'pause' => [],
            'pending' => [],
            'unapproved' => [],
        ];
        $requestIds = [];
        foreach ($requests as $row) {
            $status = $row->request_status ?? 'active';
            if (!isset($grouped[$status])) {
                $grouped[$status] = [];
            }
            $grouped[$status][] = $row;
            if (isset($row->request_id)) {
                $requestIds[] = (int) $row->request_id;
            }
        }

        $offerCounts = [];
        if ($requestIds !== []) {
            try {
                $offerCounts = $legacy
                    ->table('send_offers')
                    ->select('request_id', DB::raw('COUNT(*) as cnt'))
                    ->whereIn('request_id', $requestIds)
                    ->where('status', 'active')
                    ->groupBy('request_id')
                    ->pluck('cnt', 'request_id')
                    ->toArray();
            } catch (\Throwable $exception) {
                $offerCounts = [];
            }
        }

        $data = [
            'requests' => $grouped,
            'offerCounts' => $offerCounts,
        ];

        try {
            $html = view('requests.manage_requests', $data)->render();
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function bootstrapLegacySession(): void
    {
        $legacyBase = realpath(base_path('..'));
        $bootstrap = $legacyBase !== false
            ? $legacyBase . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'session_bootstrap.php'
            : null;

        if ($bootstrap && is_file($bootstrap) && !function_exists('blc_bootstrap_session')) {
            require_once $bootstrap;
        }

        if (function_exists('blc_bootstrap_session')) {
            blc_bootstrap_session();
        }
    }

    private function loginRedirect()
    {
        $body = "<script>window.open('../login','_self')</script>";
        return response($body, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SystemInfoController extends Controller
{
    public function index(): JsonResponse
    {
        $dbConnected = false;
        try {
            DB::connection('legacy')->getPdo();
            $dbConnected = true;
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        $payload = [
            'app' => 'Laravel Bridge',
            'status' => 'ok',
            'database_connected' => $dbConnected,
            'timestamp' => now()->toIso8601String(),
        ];

        if (app()->hasDebugModeEnabled() || app()->environment('local')) {
            $payload['php_version'] = PHP_VERSION;
            $payload['laravel_version'] = app()->version();
        }

        return response()->json($payload);
    }
}

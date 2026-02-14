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

        return response()->json([
            'app' => 'Laravel Bridge',
            'status' => 'ok',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connected' => $dbConnected,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

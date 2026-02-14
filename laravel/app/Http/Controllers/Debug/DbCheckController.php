<?php

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DbCheckController extends Controller
{
    public function __invoke()
    {
        $results = [];

        // Legacy read
        try {
            DB::connection('legacy')->getPdo();
            $results['legacy'] = 'ok';
        } catch (\Throwable $e) {
            $results['legacy'] = 'fail: ' . $e->getMessage();
        }

        // Legacy write
        try {
            DB::connection('legacy_write')->getPdo();
            $results['legacy_write'] = 'ok';
        } catch (\Throwable $e) {
            $results['legacy_write'] = 'fail: ' . $e->getMessage();
        }

        // Laravel default
        try {
            DB::connection()->getPdo();
            $results['default'] = 'ok';
        } catch (\Throwable $e) {
            $results['default'] = 'fail: ' . $e->getMessage();
        }

        return response()->json([
            'status' => 'ok',
            'connections' => $results,
        ]);
    }
}

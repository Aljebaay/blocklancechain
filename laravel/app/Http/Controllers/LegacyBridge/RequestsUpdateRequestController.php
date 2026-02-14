<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;

class RequestsUpdateRequestController extends Controller
{
    public function __invoke(Request $request)
    {
        if (filter_var(env('FORCE_LARAVEL_REQUESTS_MODULE_FAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 500);
        }

        $script = base_path('..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'requests' . DIRECTORY_SEPARATOR . 'update_request.php');
        $result = LegacyScriptRunner::run($request, $script, '/requests/update_request');

        if ($result === null || $result['status'] !== 200 || $result['body'] === '') {
            return response('', 500);
        }

        return response($result['body'], $result['status'], [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}

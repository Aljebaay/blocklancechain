<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;

class RequestsCreateRequestController extends Controller
{
    public function __invoke(Request $request)
    {
        if (filter_var(env('FORCE_LARAVEL_REQUESTS_MODULE_FAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 500);
        }

        $script = base_path('..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'requests' . DIRECTORY_SEPARATOR . 'create_request.php');
        $result = LegacyScriptRunner::run($request, $script, '/requests/create_request');

        if ($result === null || $result['status'] !== 200 || $result['body'] === '') {
            return response('', 500);
        }

        return response($result['body'], $result['status'], [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}

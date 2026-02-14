<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;

class ProposalSectionsController extends Controller
{
    public function __invoke(Request $request, string $path = '')
    {
        if (filter_var(env('FORCE_LARAVEL_PROPOSALS_FAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 500);
        }

        $clean = ltrim($path, '/');
        if ($clean === '' || str_contains($clean, '..')) {
            return response('', 500);
        }

        // Allow paths with or without .php extension
        $relative = $clean;
        if (!str_ends_with($relative, '.php')) {
            $relative .= '.php';
        }

        $script = base_path('..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'proposals' . DIRECTORY_SEPARATOR . 'sections' . DIRECTORY_SEPARATOR . $relative);
        if (!is_file($script)) {
            return response('', 500);
        }

        $legacyUri = '/proposals/sections/' . $clean;
        $result = LegacyScriptRunner::run($request, $script, $legacyUri);
        if (!$result || ($result['status'] ?? 500) !== 200 || ($result['body'] ?? '') === '') {
            return response('', 500);
        }

        return response($result['body'], (int) $result['status'], ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}

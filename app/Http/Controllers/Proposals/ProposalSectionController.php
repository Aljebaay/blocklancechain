<?php

namespace App\Http\Controllers\Proposals;

use App\Http\Controllers\Controller;
use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;

class ProposalSectionController extends Controller
{
    public function __invoke(Request $request, string $path = '')
    {
        if (!filter_var(env('MIGRATE_PROPOSALS', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 404);
        }

        $clean = ltrim($path, '/');
        if ($clean === '' || str_contains($clean, '..')) {
            return response('', 404);
        }

        $relative = $clean;
        if (!str_ends_with($relative, '.php')) {
            $relative .= '.php';
        }

        $legacyScript = base_path('legacy' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'proposals' . DIRECTORY_SEPARATOR . 'sections' . DIRECTORY_SEPARATOR . $relative);
        if (!is_file($legacyScript)) {
            return response('', 404);
        }

        $legacyUri = '/proposals/sections/' . $clean;
        $result = LegacyScriptRunner::run($request, $legacyScript, $legacyUri);
        if (!$result || ($result['body'] ?? '') === '') {
            return response('', 500);
        }

        return response()
            ->view('proposals.legacy', ['html' => $result['body']], (int) ($result['status'] ?? 200));
    }
}

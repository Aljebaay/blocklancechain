<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;

class ProposalViewController extends Controller
{
    public function __invoke(Request $request, string $username, string $slug = '')
    {
        if (filter_var(env('FORCE_LARAVEL_PROPOSALS_FAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 500);
        }

        $script = base_path('..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'proposals' . DIRECTORY_SEPARATOR . 'proposal.php');
        if (!is_file($script)) {
            return response('', 500);
        }

        $proposalUrl = $slug;

        // Mirror legacy variables
        $query = $request->query();
        $query['username'] = $username;
        $query['proposal_url'] = $proposalUrl;
        $request->query->replace($query);
        $request->server->set('QUERY_STRING', http_build_query($query));

        $legacyUri = '/proposals/' . $username . ($proposalUrl !== '' ? '/' . $proposalUrl : '');

        $result = LegacyScriptRunner::run($request, $script, $legacyUri);
        if (!$result || ($result['status'] ?? 500) !== 200 || ($result['body'] ?? '') === '') {
            return response('', 500);
        }

        return response($result['body'], (int) $result['status'], ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}

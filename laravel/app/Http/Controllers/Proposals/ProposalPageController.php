<?php

namespace App\Http\Controllers\Proposals;

use App\Http\Controllers\Controller;
use App\Services\Proposals\ProposalViewService;
use Illuminate\Http\Request;

class ProposalPageController extends Controller
{
    public function __construct(private ProposalViewService $service)
    {
    }

    public function __invoke(Request $request, string $username, string $slug = '')
    {
        if (!filter_var(env('MIGRATE_PROPOSALS', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 404);
        }

        // Best-effort legacy session mapping
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $viewerSellerId = null;
        $viewerUserName = $_SESSION['seller_user_name'] ?? null;
        if ($viewerUserName) {
            $viewerSellerId = $this->service->lookupSellerId($viewerUserName);
        }

        $data = $this->service->fetch($username, $slug, $viewerSellerId);
        if ($data === null) {
            // Fallback to runner if data missing to preserve legacy behavior
            return response('', 404);
        }

        return response()->view('proposals.show', $data);
    }
}

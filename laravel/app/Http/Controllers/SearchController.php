<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProposalService;
use App\Services\SiteSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SearchController - handles search functionality.
 * Replaces: app/Modules/Platform/search.php, search_load.php
 */
class SearchController extends Controller
{
    public function __construct(
        private readonly ProposalService $proposalService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show search results page.
     * Replaces: app/Modules/Platform/search.php
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'search', 'min_price', 'max_price', 'delivery_time',
            'seller_level', 'seller_country', 'online_sellers',
        ]);

        $proposals = $this->proposalService->getProposals('search', $filters);

        return view('search.index', [
            'proposals' => $proposals,
            'filters' => $filters,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * AJAX search results loader.
     * Replaces: app/Modules/Platform/search_load.php
     */
    public function load(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'min_price', 'max_price', 'delivery_time',
            'seller_level', 'seller_country', 'online_sellers',
        ]);

        $proposals = $this->proposalService->getProposals('search', $filters);

        return response()->json([
            'success' => true,
            'data' => $proposals->items(),
            'pagination' => [
                'current_page' => $proposals->currentPage(),
                'last_page' => $proposals->lastPage(),
                'total' => $proposals->total(),
            ],
        ]);
    }
}

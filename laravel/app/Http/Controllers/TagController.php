<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProposalService;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * TagController - handles tag-based proposal browsing.
 * Replaces: app/Modules/Platform/tags/tag.php
 */
class TagController extends Controller
{
    public function __construct(
        private readonly ProposalService $proposalService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show proposals for a given tag.
     * URL: /tags/{tag}
     */
    public function show(Request $request, string $tag): View
    {
        $filters = $request->only([
            'min_price', 'max_price', 'delivery_time',
            'seller_level', 'seller_country', 'online_sellers',
        ]);
        $filters['tag'] = $tag;

        $proposals = $this->proposalService->getProposals('tag', $filters);

        return view('tags.show', [
            'tag' => $tag,
            'proposals' => $proposals,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }
}

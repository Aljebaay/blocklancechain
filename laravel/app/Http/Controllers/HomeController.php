<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Proposal;
use App\Services\AuthService;
use App\Services\ProposalService;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HomeController - handles the main homepage and user dashboard.
 * Replaces: app/Modules/Platform/index.php, home.php, user_home.php
 */
class HomeController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly ProposalService $proposalService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show the homepage.
     * Replaces: app/Modules/Platform/index.php
     * Shows home.php for guests, user_home.php for logged-in users.
     */
    public function index(Request $request): View
    {
        $settings = $this->settingsService->getGeneralSettings();
        $seller = $this->authService->currentSeller();
        $categories = Category::with('children')->orderBy('cat_order')->get();

        if ($seller) {
            // Logged-in user dashboard
            return view('pages.user-home', [
                'settings' => $settings,
                'seller' => $seller,
                'categories' => $categories,
            ]);
        }

        // Public homepage
        $featuredProposals = Proposal::with(['seller', 'buyerReviews'])
            ->active()
            ->featured()
            ->limit(8)
            ->get();

        return view('pages.home', [
            'settings' => $settings,
            'categories' => $categories,
            'featuredProposals' => $featuredProposals,
        ]);
    }
}

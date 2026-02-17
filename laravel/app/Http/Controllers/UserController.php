<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Services\AuthService;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * UserController - handles user profiles and settings.
 * Replaces: app/Modules/Platform/user.php, settings.php, handler.php
 */
class UserController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show a user's public profile.
     * Replaces: app/Modules/Platform/user.php and handler.php (slug-based lookup)
     * URL: /{username}
     */
    public function profile(string $username): View
    {
        $seller = Seller::where('seller_user_name', $username)
            ->with(['proposals' => function ($query) {
                $query->where('proposal_status', 'active');
            }, 'buyerReviews'])
            ->firstOrFail();

        return view('users.profile', [
            'seller' => $seller,
            'currentSeller' => $this->authService->currentSeller(),
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show user settings page.
     * Replaces: app/Modules/Platform/settings.php
     */
    public function settings(): View
    {
        $seller = $this->authService->currentSeller();

        if (! $seller) {
            abort(403);
        }

        return view('users.settings', [
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Update user settings.
     */
    public function updateSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        $seller = $this->authService->currentSeller();

        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $validated = $request->validate([
            'seller_name' => ['nullable', 'string', 'max:255'],
            'seller_headline' => ['nullable', 'string', 'max:500'],
            'seller_about' => ['nullable', 'string', 'max:5000'],
            'seller_country' => ['nullable', 'string', 'max:100'],
            'seller_phone' => ['nullable', 'string', 'max:20'],
            'seller_language' => ['nullable', 'string', 'max:100'],
        ]);

        $seller->update($validated);

        return response()->json(['success' => true]);
    }
}

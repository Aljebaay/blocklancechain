<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BuyerRequest;
use App\Services\AuthService;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * BuyerRequestController - handles buyer requests module.
 * Replaces: app/Modules/Platform/requests/*.php
 */
class BuyerRequestController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show buyer requests listing.
     * Replaces: app/Modules/Platform/requests/buyer_requests.php
     */
    public function index(): View
    {
        $requests = BuyerRequest::with(['buyer', 'category'])
            ->where('request_status', 'active')
            ->orderByDesc('id')
            ->paginate(10);

        return view('requests.index', [
            'requests' => $requests,
            'currentSeller' => $this->authService->currentSeller(),
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show post request form.
     * Replaces: app/Modules/Platform/requests/post_request.php
     */
    public function create(): View
    {
        $seller = $this->authService->currentSeller();

        if (! $seller) {
            abort(403);
        }

        return view('requests.create', [
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show manage requests page.
     * Replaces: app/Modules/Platform/requests/manage_requests.php
     */
    public function manage(): View
    {
        $seller = $this->authService->currentSeller();

        if (! $seller) {
            abort(403);
        }

        $requests = BuyerRequest::where('buyer_id', $seller->seller_id)
            ->orderByDesc('id')
            ->paginate(10);

        return view('requests.manage', [
            'requests' => $requests,
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show offers for a request.
     * Replaces: app/Modules/Platform/requests/view_offers.php
     */
    public function viewOffers(int $requestId): View
    {
        $seller = $this->authService->currentSeller();

        if (! $seller) {
            abort(403);
        }

        $buyerRequest = BuyerRequest::with(['offers.seller'])
            ->where('buyer_id', $seller->seller_id)
            ->findOrFail($requestId);

        return view('requests.offers', [
            'buyerRequest' => $buyerRequest,
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }
}

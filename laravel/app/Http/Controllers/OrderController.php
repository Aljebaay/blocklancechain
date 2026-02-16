<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\AuthService;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * OrderController - handles order management.
 * Replaces: app/Modules/Platform/manage_orders/*.php
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show buying orders list.
     * Replaces: app/Modules/Platform/manage_orders/order_active_buying.php etc.
     */
    public function buyingOrders(Request $request, string $status = 'active'): View
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            abort(403);
        }

        $validStatuses = ['active', 'completed', 'cancelled', 'delivered', 'all'];
        if (!in_array($status, $validStatuses, true)) {
            abort(404);
        }

        $query = Order::with(['proposal', 'seller'])
            ->where('buyer_id', $seller->seller_id);

        if ($status !== 'all') {
            $query->where('order_status', $status);
        }

        $orders = $query->orderByDesc('order_id')->paginate(10);

        return view('orders.buying', [
            'orders' => $orders,
            'status' => $status,
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show selling orders list.
     * Replaces: app/Modules/Platform/manage_orders/order_active_selling.php etc.
     */
    public function sellingOrders(Request $request, string $status = 'active'): View
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            abort(403);
        }

        $validStatuses = ['active', 'completed', 'cancelled', 'delivered', 'all'];
        if (!in_array($status, $validStatuses, true)) {
            abort(404);
        }

        $query = Order::with(['proposal', 'buyer'])
            ->where('seller_id', $seller->seller_id);

        if ($status !== 'all') {
            $query->where('order_status', $status);
        }

        $orders = $query->orderByDesc('order_id')->paginate(10);

        return view('orders.selling', [
            'orders' => $orders,
            'status' => $status,
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show single order details.
     * Replaces: app/Modules/Platform/orderIncludes/orderDetails.php
     */
    public function show(int $orderId): View
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            abort(403);
        }

        $order = Order::with([
            'proposal', 'buyer', 'seller',
            'buyerReview', 'sellerReview', 'messages.sender',
        ])->findOrFail($orderId);

        // Verify the current user is either the buyer or seller
        if ($order->buyer_id !== $seller->seller_id && $order->seller_id !== $seller->seller_id) {
            abort(403);
        }

        return view('orders.show', [
            'order' => $order,
            'currentSeller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }
}

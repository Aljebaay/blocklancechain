<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UserManagementController - admin user management.
 * Replaces: app/Modules/Platform/admin/view_users.php, view_sellers.php, etc.
 */
class UserManagementController extends Controller
{
    /**
     * List all users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Seller::query();

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('seller_user_name', 'LIKE', "%{$search}%")
                    ->orWhere('seller_email', 'LIKE', "%{$search}%")
                    ->orWhere('seller_name', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('seller_status', $request->input('status'));
        }

        $users = $query->orderByDesc('seller_id')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Show a single user.
     */
    public function show(int $sellerId): JsonResponse
    {
        $seller = Seller::with(['proposals', 'buyerReviews'])
            ->find($sellerId);

        if (!$seller) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $seller,
        ]);
    }

    /**
     * Update user status (block/unblock/deactivate).
     */
    public function updateStatus(Request $request, int $sellerId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:online,offline,block-ban,deactivated'],
        ]);

        $updated = Seller::where('seller_id', $sellerId)
            ->update(['seller_status' => $validated['status']]);

        if (!$updated) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update user balance.
     * Replaces: app/Modules/Platform/admin/update_balance.php
     */
    public function updateBalance(Request $request, int $sellerId): JsonResponse
    {
        $validated = $request->validate([
            'balance' => ['required', 'numeric', 'min:0'],
        ]);

        $updated = Seller::where('seller_id', $sellerId)
            ->update(['seller_balance' => $validated['balance']]);

        if (!$updated) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        return response()->json(['success' => true]);
    }
}

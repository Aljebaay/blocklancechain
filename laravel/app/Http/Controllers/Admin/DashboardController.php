<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Proposal;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController - admin dashboard and statistics.
 * Replaces: app/Modules/Platform/admin/index.php
 */
class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => Seller::count(),
                'total_proposals' => Proposal::count(),
                'active_proposals' => Proposal::where('proposal_status', 'active')->count(),
                'total_orders' => Order::count(),
                'active_orders' => Order::where('order_status', 'active')->count(),
                'completed_orders' => Order::where('order_status', 'completed')->count(),
                'total_sales' => DB::table('sales')->sum('amount'),
            ],
        ]);
    }
}

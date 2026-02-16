<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Seller;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * UpdateSellerActivity middleware - tracks user activity for online status.
 * Replaces legacy activity tracking in the original application.
 */
class UpdateSellerActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = session('seller_user_name');

        if ($username) {
            Seller::where('seller_user_name', $username)
                ->update(['seller_activity' => now()->format('Y-m-d H:i:s')]);
        }

        return $next($request);
    }
}

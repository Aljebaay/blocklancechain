<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SellerAuthenticated middleware - ensures the user is logged in.
 * Replaces legacy session checks in includes/db.php.
 */
class SellerAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('seller_user_name')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            return redirect('/login');
        }

        return $next($request);
    }
}

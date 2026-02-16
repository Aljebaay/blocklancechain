<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminAuthenticated middleware - ensures the user is an admin.
 * Replaces legacy admin session check.
 */
class AdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('admin_email')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            return redirect('/admin/login');
        }

        return $next($request);
    }
}

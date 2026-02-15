<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds X-Handler header to all responses for migration parity verification.
 *
 * Used to distinguish whether a request was handled by Laravel or legacy code.
 * This header is safe to add — it has no effect on behavior or content.
 */
class AddHandlerHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Only set if not already provided (e.g. LegacyBridgeController sets 'legacy')
        if (!$response->headers->has('X-Handler')) {
            $response->headers->set('X-Handler', 'laravel');
        }

        return $response;
    }
}

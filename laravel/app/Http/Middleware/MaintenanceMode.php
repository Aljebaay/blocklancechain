<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SiteSettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MaintenanceMode middleware - checks database-driven maintenance mode.
 * Replaces: legacy maintenance mode check in includes/db.php.
 */
class MaintenanceMode
{
    public function __construct(
        private readonly SiteSettingsService $settingsService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Admin users bypass maintenance mode (matches legacy behavior)
        if (session()->has('admin_email')) {
            return $next($request);
        }

        if ($this->settingsService->isMaintenanceMode()) {
            return response()->view('errors.maintenance', [
                'settings' => $this->settingsService->getGeneralSettings(),
            ], 503);
        }

        return $next($request);
    }
}

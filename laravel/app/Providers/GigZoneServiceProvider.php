<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AuthService;
use App\Services\PriceService;
use App\Services\ProposalService;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * GigZoneServiceProvider - registers application-level services and view composers.
 */
class GigZoneServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(SiteSettingsService::class);
        $this->app->singleton(AuthService::class);
        $this->app->singleton(PriceService::class, function ($app) {
            return new PriceService($app->make(SiteSettingsService::class));
        });
        $this->app->singleton(ProposalService::class);
    }

    public function boot(): void
    {
        // Share common variables with all views (replaces legacy global variables)
        View::composer('*', function ($view) {
            $settingsService = app(SiteSettingsService::class);
            $authService = app(AuthService::class);

            $view->with([
                'siteUrl' => $settingsService->getSiteUrl(),
                'siteName' => $settingsService->getSiteName(),
                'isLoggedIn' => $authService->isLoggedIn(),
                'currentSellerGlobal' => $authService->currentSeller(),
            ]);
        });

        // Set timezone from database settings
        try {
            $timezone = app(SiteSettingsService::class)->getTimezone();
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        } catch (\Exception $e) {
            // Silently fail if DB is not connected yet (during migrations, etc.)
        }
    }
}

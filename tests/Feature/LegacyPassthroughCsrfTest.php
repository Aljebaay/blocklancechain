<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LegacyPassthroughCsrfTest extends TestCase
{
    public function test_legacy_catch_all_route_excludes_laravel_csrf_middleware(): void
    {
        $route = collect(Route::getRoutes())
            ->first(static fn ($route) => $route->uri() === '{path?}');

        $this->assertNotNull($route);
        $this->assertContains(ValidateCsrfToken::class, $route->excludedMiddleware());
    }

    public function test_migrate_orders_route_excludes_csrf_as_legacy_bridge(): void
    {
        $route = collect(Route::getRoutes())
            ->first(static fn ($route) => $route->uri() === '_app/migrate/orders/{file}');

        $this->assertNotNull($route);
        // All _app/migrate routes exclude CSRF — legacy PHP handles its own form validation
        $this->assertContains(ValidateCsrfToken::class, $route->excludedMiddleware());
    }
}

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
            ->first(static fn ($route) => $route->uri() === '{any?}');

        $this->assertNotNull($route);
        $this->assertContains(ValidateCsrfToken::class, $route->excludedMiddleware());
    }

    public function test_migrate_orders_route_keeps_laravel_csrf_middleware(): void
    {
        $route = collect(Route::getRoutes())
            ->first(static fn ($route) => $route->uri() === '_app/migrate/orders/{file}');

        $this->assertNotNull($route);
        $this->assertNotContains(ValidateCsrfToken::class, $route->excludedMiddleware());
    }
}

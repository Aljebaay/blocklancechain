<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'seller.auth' => \App\Http\Middleware\SellerAuthenticated::class,
            'admin.auth' => \App\Http\Middleware\AdminAuthenticated::class,
            'maintenance.db' => \App\Http\Middleware\MaintenanceMode::class,
        ]);

        // Append to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\UpdateSellerActivity::class,
            \App\Http\Middleware\MaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

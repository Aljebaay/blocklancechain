<?php

use App\Http\Middleware\AddHandlerHeader;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add X-Handler: laravel header to all responses for parity verification.
        $middleware->append(AddHandlerHeader::class);

        // CSRF exceptions are now handled per-route via ->withoutMiddleware()
        // in routes/web.php. The /_app/migrate/* aliases also have CSRF disabled
        // at the route group level.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

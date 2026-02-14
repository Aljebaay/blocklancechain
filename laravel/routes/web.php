<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::prefix('/_app')->group(function () {
    Route::get('/health', HealthController::class);

    Route::get('/debug/routes', function () {
        if (!app()->hasDebugModeEnabled() && !app()->environment('local')) {
            abort(403);
        }
        return response()->json([
            'routes' => collect(Route::getRoutes())->map(function ($route) {
                return [
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                ];
            })->values(),
        ]);
    });
});

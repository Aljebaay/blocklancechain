<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SystemInfoController;
use App\Http\Controllers\LegacyBridge\RequestsFetchSubcategoryController;
use App\Http\Controllers\LegacyBridge\ProposalPricingCheckController;
use App\Http\Controllers\LegacyBridge\ApisIndexController;
use App\Http\Controllers\LegacyBridge\RequestsPauseRequestController;
use App\Http\Controllers\LegacyBridge\RequestsActiveRequestController;
use App\Http\Controllers\LegacyBridge\ProposalPricingCheckController as LegacyProposalPricingCheckController;

Route::prefix('/_app')->group(function () {
    Route::get('/health', HealthController::class);
    Route::get('/system/info', [SystemInfoController::class, 'index']);
    Route::post('/migrate/requests/fetch_subcategory', RequestsFetchSubcategoryController::class);
    Route::post('/migrate/proposals/ajax/check/pricing', ProposalPricingCheckController::class);
    Route::post('/migrate/proposal/pricing_check', LegacyProposalPricingCheckController::class);
    Route::match(['get', 'post'], '/migrate/apis/index.php', ApisIndexController::class);
    Route::get('/migrate/requests/pause_request', RequestsPauseRequestController::class);
    Route::get('/migrate/requests/active_request', RequestsActiveRequestController::class);

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

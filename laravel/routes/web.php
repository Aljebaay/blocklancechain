<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SystemInfoController;
use App\Http\Controllers\LegacyBridge\RequestsFetchSubcategoryController;
use App\Http\Controllers\LegacyBridge\ProposalPricingCheckController;
use App\Http\Controllers\LegacyBridge\ApisIndexController;
use App\Http\Controllers\LegacyBridge\RequestsPauseRequestController;
use App\Http\Controllers\LegacyBridge\RequestsActiveRequestController;
use App\Http\Controllers\LegacyBridge\RequestsManageRequestsController;
use App\Http\Controllers\LegacyBridge\RequestsResumeRequestController;
use App\Http\Controllers\LegacyBridge\RequestsCreateRequestController;
use App\Http\Controllers\LegacyBridge\RequestsUpdateRequestController;
use App\Http\Controllers\LegacyBridge\ProposalPricingCheckController as LegacyProposalPricingCheckController;
use App\Http\Controllers\LegacyBridge\ProposalViewController;
use App\Http\Controllers\LegacyBridge\ProposalSectionsController;

// Local dev convenience: show a simple page when running `php artisan serve`.
Route::view('/', 'welcome');

Route::prefix('/_app')->group(function () {
    Route::get('/health', HealthController::class);
    Route::get('/system/info', [SystemInfoController::class, 'index']);
    Route::post('/migrate/requests/fetch_subcategory', RequestsFetchSubcategoryController::class);
    Route::get('/migrate/requests/manage_requests', RequestsManageRequestsController::class);
    Route::get('/migrate/requests/resume_request', RequestsResumeRequestController::class);
    Route::post('/migrate/requests/create_request', RequestsCreateRequestController::class);
    Route::post('/migrate/requests/update_request', RequestsUpdateRequestController::class);
    Route::post('/migrate/proposals/ajax/check/pricing', ProposalPricingCheckController::class);
    Route::post('/migrate/proposal/pricing_check', LegacyProposalPricingCheckController::class);
    Route::match(['get', 'post'], '/migrate/proposals/sections/{path}', ProposalSectionsController::class)->where('path', '.*');
    Route::match(['get', 'post'], '/migrate/proposals/{username}/{slug?}', ProposalViewController::class)->where('slug', '.*');
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

// Fallback: delegate all other requests to legacy public/router.php to mirror legacy routes when using `php artisan serve`.
Route::any('/{any}', function (string $any = null) {
    $path = '/' . ltrim($any ?? '', '/');
    // Keep Laravel-handled prefix untouched
    if (str_starts_with($path, '/_app')) {
        abort(404);
    }

    $router = base_path('..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'router.php');
    if (!is_file($router)) {
        abort(404);
    }

    // Align REQUEST_URI for legacy router
    $_SERVER['REQUEST_URI'] = $path === '' ? '/' : $path;
    $_SERVER['PHP_SELF'] = $path;
    $_SERVER['SCRIPT_NAME'] = $path;

    ob_start();
    $legacyStatus = 200;
    try {
        require $router;
    } catch (\Throwable $e) {
        ob_end_clean();
        abort(500);
    }
    $body = ob_get_clean();
    $status = http_response_code();
    if (!is_int($status) || $status <= 0) {
        $status = $legacyStatus;
    }

    return response($body, $status);
})->where('any', '.*');

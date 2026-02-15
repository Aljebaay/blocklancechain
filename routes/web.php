<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// ── Native Laravel Controllers (fully migrated) ─────────────────────────────
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SystemInfoController;
use App\Http\Controllers\LegacyBridge\RequestsFetchSubcategoryController;
use App\Http\Controllers\LegacyBridge\RequestsPauseRequestController;
use App\Http\Controllers\LegacyBridge\RequestsActiveRequestController;
use App\Http\Controllers\LegacyBridge\RequestsManageRequestsController;
use App\Http\Controllers\LegacyBridge\RequestsResumeRequestController;
use App\Http\Controllers\LegacyBridge\RequestsCreateRequestController;
use App\Http\Controllers\LegacyBridge\RequestsUpdateRequestController;
use App\Http\Controllers\LegacyBridge\ProposalPricingCheckController;
use App\Http\Controllers\LegacyBridge\ApisIndexController;
use App\Http\Controllers\LegacyBridge\OrdersBridgeController;
use App\Http\Controllers\Orders\CancelPaymentController;
use App\Http\Controllers\Proposals\ProposalPageController;
use App\Http\Controllers\Proposals\ProposalSectionController;
use App\Http\Controllers\Debug\DbCheckController;
use App\Http\Controllers\LegacyBridgeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The project is now fully routed through Laravel.
| - Native controllers handle migrated endpoints directly.
| - LegacyBridgeController handles everything else via LegacyScriptRunner
|   (subprocess isolation) until each endpoint is converted to native.
| - The /_app prefix routes remain as internal utilities.
|
*/

// ── Internal / utility routes ────────────────────────────────────────────────
Route::prefix('/_app')->group(function () {
    Route::get('/health', HealthController::class);
    Route::get('/system/info', [SystemInfoController::class, 'index']);

    Route::get('/debug/routes', function () {
        if (!app()->hasDebugModeEnabled() && !app()->environment('local')) {
            abort(403);
        }
        return response()->json([
            'routes' => collect(Route::getRoutes())->map(fn ($r) => [
                'uri'     => $r->uri(),
                'methods' => $r->methods(),
                'name'    => $r->getName(),
                'action'  => $r->getActionName(),
            ])->values(),
        ]);
    });
    Route::get('/debug/db', DbCheckController::class);
});

// ── Requests module (native Laravel) ─────────────────────────────────────────
Route::post('/requests/fetch_subcategory', RequestsFetchSubcategoryController::class)
    ->withoutMiddleware([ValidateCsrfToken::class]);

Route::get('/requests/manage_requests', RequestsManageRequestsController::class);

Route::get('/requests/active_request', RequestsActiveRequestController::class);

Route::get('/requests/pause_request', RequestsPauseRequestController::class);

Route::get('/requests/resume_request', RequestsResumeRequestController::class)
    ->withoutMiddleware([ValidateCsrfToken::class]);

Route::post('/requests/create_request', RequestsCreateRequestController::class)
    ->withoutMiddleware([ValidateCsrfToken::class]);

Route::post('/requests/update_request', RequestsUpdateRequestController::class)
    ->withoutMiddleware([ValidateCsrfToken::class]);

// ── Proposals module (native Laravel) ────────────────────────────────────────
Route::post('/proposals/ajax/check/pricing', ProposalPricingCheckController::class)
    ->withoutMiddleware([ValidateCsrfToken::class]);

Route::post('/proposal/pricing_check', ProposalPricingCheckController::class)
    ->withoutMiddleware([ValidateCsrfToken::class]);

Route::match(['get', 'post'], '/proposals/sections/{path}', ProposalSectionController::class)
    ->where('path', '.*')
    ->withoutMiddleware([ValidateCsrfToken::class]);

Route::match(['get', 'post'], '/proposals/{username}/{slug?}', ProposalPageController::class)
    ->where('slug', '.*')
    ->withoutMiddleware([ValidateCsrfToken::class]);

// ── Orders module (native + bridge) ─────────────────────────────────────────
Route::match(['get', 'post'], '/cancel_payment.php', CancelPaymentController::class)
    ->withoutMiddleware([ValidateCsrfToken::class]);

// APIs bridge
Route::match(['get', 'post'], '/apis/index.php', ApisIndexController::class)
    ->withoutMiddleware([ValidateCsrfToken::class]);

// ── Backward compatibility: /_app/migrate/* aliases ─────────────────────────
// Kept so existing smoke tests and references still work.
Route::prefix('/_app/migrate')->withoutMiddleware([ValidateCsrfToken::class])->group(function () {
    Route::post('/requests/fetch_subcategory', RequestsFetchSubcategoryController::class);
    Route::get('/requests/manage_requests', RequestsManageRequestsController::class);
    Route::get('/requests/active_request', RequestsActiveRequestController::class);
    Route::get('/requests/pause_request', RequestsPauseRequestController::class);
    Route::get('/requests/resume_request', RequestsResumeRequestController::class);
    Route::post('/requests/create_request', RequestsCreateRequestController::class);
    Route::post('/requests/update_request', RequestsUpdateRequestController::class);
    Route::post('/proposals/ajax/check/pricing', ProposalPricingCheckController::class);
    Route::post('/proposal/pricing_check', ProposalPricingCheckController::class);
    Route::match(['get', 'post'], '/proposals/sections/{path}', ProposalSectionController::class)->where('path', '.*');
    Route::match(['get', 'post'], '/proposals/{username}/{slug?}', ProposalPageController::class)->where('slug', '.*');
    Route::match(['get', 'post'], '/apis/index.php', ApisIndexController::class);
    Route::match(['get', 'post'], '/orders/cancel_payment.php', CancelPaymentController::class);
    Route::match(['get', 'post'], '/orders/{file}', OrdersBridgeController::class)->where('file', '.*');
});

// ── Catch-all: everything else via legacy bridge ────────────────────────────
// This route MUST be last. Delegates to LegacyBridgeController which runs the
// matching legacy PHP file inside legacy/public/ via LegacyScriptRunner.
Route::any('/{path?}', [LegacyBridgeController::class, 'handle'])
    ->where('path', '^(?!_app).*$')
    ->withoutMiddleware([ValidateCsrfToken::class]);

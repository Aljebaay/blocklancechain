<?php

declare(strict_types=1);

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
use App\Http\Controllers\LegacyBridge\OrdersBridgeController;
use App\Http\Controllers\Proposals\ProposalPageController;
use App\Http\Controllers\Proposals\ProposalSectionController;
use App\Http\Controllers\Debug\DbCheckController;
use App\Support\LegacyScriptRunner;

$legacyPassthrough = static function (string $path): void {
    // Keep Laravel-handled prefix untouched
    if (str_starts_with($path, '/_app')) {
        abort(404);
    }

    $router = base_path('..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'router.php');
    if (!is_file($router)) {
        abort(404);
    }

    $path = $path === '' ? '/' : $path;

    // Run legacy router in isolated process to capture output even if it calls exit/die.
    $result = LegacyScriptRunner::run(request(), $router, $path);
    $status = (int) ($result['status'] ?? 0);
    $body = (string) ($result['body'] ?? '');

    // If router returned nothing, try to serve direct target (php via runner, static via file_get_contents)
    if (($body === '' || $status === 0) && $path !== '/') {
        $docRoot = realpath(base_path('..' . DIRECTORY_SEPARATOR . 'public'));
        if ($docRoot !== false) {
            $direct = realpath($docRoot . $path);
            if ($direct !== false && is_file($direct)) {
                $ext = strtolower(pathinfo($direct, PATHINFO_EXTENSION));
                if ($ext === 'php') {
                    $directResult = LegacyScriptRunner::run(request(), $direct, $path);
                    if (is_array($directResult)) {
                        $body = (string) ($directResult['body'] ?? '');
                        $status = (int) ($directResult['status'] ?? 200);
                    }
                } else {
                    $content = @file_get_contents($direct);
                    if ($content !== false) {
                        $body = $content;
                        $status = 200;
                    }
                }
            }
        }
    }

    if ($body === '' || $status === 0) {
        abort(500);
    }

    if (!headers_sent()) {
        http_response_code($status);

        $mime = 'text/html; charset=UTF-8';
        $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        $map = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
        ];

        if ($ext !== '' && isset($map[$ext])) {
            $mime = $map[$ext];
        }

        header('Content-Type: ' . $mime);
    }

    echo $body;
    exit;
};

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

    // Native-viewed versions (Phase 14B)
    Route::match(['get', 'post'], '/migrate/proposals/sections/{path}', ProposalSectionController::class)
        ->where('path', '.*');
    Route::match(['get', 'post'], '/migrate/proposals/{username}/{slug?}', ProposalPageController::class)
        ->where('slug', '.*');

    Route::match(['get', 'post'], '/migrate/apis/index.php', ApisIndexController::class);
    Route::match(['get', 'post'], '/migrate/orders/{file}', OrdersBridgeController::class)
        ->where('file', '.*');

    Route::get('/migrate/requests/pause_request', RequestsPauseRequestController::class);
    Route::get('/migrate/requests/active_request', RequestsActiveRequestController::class);

    Route::get('/debug/routes', function () {
        if (!app()->hasDebugModeEnabled() && !app()->environment('local')) {
            abort(403);
        }

        return response()->json([
            'routes' => collect(Route::getRoutes())->map(static function ($route) {
                return [
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                ];
            })->values(),
        ]);
    });

    Route::get('/debug/db', DbCheckController::class);
});

// Catch-all fallback to legacy router.php for everything outside /_app
Route::any('/{any?}', function (?string $any = null) use ($legacyPassthrough): void {
    $path = '/' . ltrim($any ?? '', '/');
    $legacyPassthrough($path);
})->where('any', '.*');

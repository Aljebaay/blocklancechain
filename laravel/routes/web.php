<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LegacyAjaxController;
use App\Http\Controllers\LegacyEndpointController;
use App\Http\Controllers\LegacyComponentController;
use App\Http\Controllers\LegacyPageController;
use App\Http\Controllers\LegacyPostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes serve Blade templates that match legacy HTML output exactly.
| The Vue 3 SPA is available at /spa/* for future use.
|
*/

// =====================================================================
// Legacy POST Dispatch (parity with legacy register_login_forgot.php)
//
// Legacy forms use action="" which POSTs to the current page URL.
// register_login_forgot.php runs on every page via header.php, dispatching
// by the submit-button name:  register | login | forgot | access
//
// Because the modals appear in the shared layout, POST must be accepted
// on EVERY page that extends legacy.layout.  We route them all through
// LegacyPostController which inspects POST parameters to dispatch.
// =====================================================================
Route::post('/', [LegacyPostController::class, 'dispatchRootPost'])->name('legacy.post.root');
Route::post('/index', [LegacyPostController::class, 'dispatchRootPost'])->name('legacy.post.index');
Route::post('/login', [LegacyPostController::class, 'dispatchLoginPost'])->name('login.submit');
Route::post('/register', [LegacyPostController::class, 'dispatchRootPost'])->name('register.submit');
Route::post('/categories', [LegacyPostController::class, 'dispatchRootPost']);
Route::post('/categories/{catUrl}/{childUrl?}', [LegacyPostController::class, 'dispatchRootPost']);
Route::post('/blog', [LegacyPostController::class, 'dispatchRootPost']);
Route::post('/blog/{id}/{slug?}', [LegacyPostController::class, 'dispatchRootPost']);
Route::post('/tags/{tag}', [LegacyPostController::class, 'dispatchRootPost']);
Route::post('/pages/{slug}', [LegacyPostController::class, 'dispatchRootPost']);
Route::post('/proposals/{username}/{slug}', [LegacyPostController::class, 'dispatchRootPost']);

// =====================================================================
// Legacy AJAX Endpoints (parity with search_load.php, category_load.php,
// tag_load.php, featured_load.php)
//
// Called by sidebar filter JavaScript via $.ajax() POST.
// Returns HTML fragments (proposal cards + pagination).
// =====================================================================
Route::post('/search_load', [LegacyAjaxController::class, 'searchLoad'])->name('legacy.ajax.search');
Route::post('/category_load', [LegacyAjaxController::class, 'categoryLoad'])->name('legacy.ajax.category');
Route::post('/tag_load', [LegacyAjaxController::class, 'tagLoad'])->name('legacy.ajax.tag');
Route::post('/featured_load', [LegacyAjaxController::class, 'featuredLoad'])->name('legacy.ajax.featured');

// =====================================================================
// Legacy Component AJAX Endpoints (parity with includes/comp/*.php,
// includes/messagePopup.php, includes/notificationsPopup.php,
// includes/close_cookies_footer.php)
//
// Called by customjs.js / knowledge-bank.js via $.ajax() POST.
// Returns plain text counts, JSON data, or cookie responses.
// =====================================================================
Route::post('/includes/comp/c-favorites', [LegacyComponentController::class, 'cFavorites']);
Route::post('/includes/comp/c-messages-header', [LegacyComponentController::class, 'cMessagesHeader']);
Route::post('/includes/comp/c-messages-body', [LegacyComponentController::class, 'cMessagesBody']);
Route::post('/includes/comp/c-notifications-header', [LegacyComponentController::class, 'cNotificationsHeader']);
Route::post('/includes/comp/c-notifications-body', [LegacyComponentController::class, 'cNotificationsBody']);
Route::post('/includes/messagePopup', [LegacyComponentController::class, 'messagePopup']);
Route::post('/includes/notificationsPopup', [LegacyComponentController::class, 'notificationsPopup']);
Route::post('/includes/close_cookies_footer.php', [LegacyComponentController::class, 'closeCookiesFooter']);
Route::post('/search-knowledge', [LegacyComponentController::class, 'searchKnowledge']);

// =====================================================================
// Authentication GET routes + logout
// =====================================================================
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// =====================================================================
// Admin Authentication + static assets (when all requests go through index.php)
// Admin panel may request /admin/assets/... or root-relative /assets/ and /admin_images/
// Serves from Laravel public first, then legacy app/Modules/Platform/admin/ (so 500s become 404s).
// =====================================================================
$serveAdminAsset = function (string $publicSubPath, string $legacySubPath): \Symfony\Component\HttpFoundation\BinaryFileResponse {
    $base = realpath(public_path($publicSubPath));
    if ($base === false && $legacySubPath !== '') {
        $base = realpath(base_path($legacySubPath)) ?: null;
    } elseif ($base === false) {
        $base = null;
    }
    if ($base === null) {
        abort(404);
    }

    $path = (string) (request()->route('path') ?? '');
    $path = str_replace(['..', '\\'], ['', '/'], $path);
    $path = trim($path, '/');
    if ($path === '') {
        abort(404);
    }

    $parts = explode('/', $path);
    $fullPath = $base . \DIRECTORY_SEPARATOR . implode(\DIRECTORY_SEPARATOR, $parts);
    $resolved = realpath($fullPath);
    if ($resolved === false || !is_file($resolved)) {
        abort(404);
    }

    $baseReal = realpath($base);
    if ($baseReal === false || !str_starts_with($resolved, $baseReal)) {
        abort(404);
    }

    return response()->file($resolved);
};

Route::get('/admin/assets/{path}', fn () => $serveAdminAsset('admin/assets', '../app/Modules/Platform/admin/assets'))->where('path', '.*')->name('admin.assets');
Route::get('/assets/{path}', fn () => $serveAdminAsset('admin/assets', '../app/Modules/Platform/admin/assets'))->where('path', '.*')->name('assets');
Route::get('/admin_images/{path}', fn () => $serveAdminAsset('admin/admin_images', '../app/Modules/Platform/admin/admin_images'))->where('path', '.*')->name('admin_images');

Route::get('/admin/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::get('/admin/logout', [\App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])->name('admin.logout');

// =====================================================================
// Legacy Parity Pages (Blade SSR — matching legacy HTML exactly)
// =====================================================================
Route::get('/', [LegacyPageController::class, 'home'])->name('home');
Route::get('/index', [LegacyPageController::class, 'home']); // Legacy alias: index.php → home page
Route::get('/login', [LegacyPageController::class, 'showLogin'])->name('login');
Route::get('/register', [LegacyPageController::class, 'showRegister'])->name('register');
Route::get('/categories', [LegacyPageController::class, 'categoriesIndex'])->name('categories.index');
Route::get('/categories/{catUrl}/{childUrl?}', [LegacyPageController::class, 'categoriesShow'])->name('categories.show');
Route::match(['get', 'post'], '/search', [LegacyPageController::class, 'search'])->name('search');
Route::get('/blog', [LegacyPageController::class, 'blogIndex'])->name('blog.index');
Route::get('/blog/{id}/{slug?}', [LegacyPageController::class, 'blogPost'])->name('blog.post');
Route::get('/tags/{tag}', [LegacyPageController::class, 'tagsShow'])->name('tags.show');
Route::get('/pages/{slug}', [LegacyPageController::class, 'pageShow'])->name('pages.show');
Route::get('/proposals/{username}/{slug}', [LegacyPageController::class, 'proposalShow'])->name('proposals.show');

// =====================================================================
// Legacy Endpoint Routes (from config/endpoints.php — exact path parity)
// Every endpoint path is registered; existing parity routes are skipped.
// Served by LegacyEndpointController (runs legacy PHP handler).
// =====================================================================
$endpointsFile = base_path('../config/endpoints.php');
if (is_file($endpointsFile)) {
    $endpoints = require $endpointsFile;
    $skipUris = [
        '', 'index', 'login', 'register', 'categories', 'search', 'blog', 'tags', 'pages', 'proposals', 'logout',
        'admin/login', 'admin/logout',
        'search_load', 'category_load', 'tag_load', 'featured_load',
        'includes/comp/c-favorites', 'includes/comp/c-messages-header', 'includes/comp/c-messages-body',
        'includes/comp/c-notifications-header', 'includes/comp/c-notifications-body',
        'includes/messagePopup', 'includes/notificationsPopup', 'includes/close_cookies_footer',
        'search-knowledge',
    ];
    if (is_array($endpoints)) {
        foreach ($endpoints as $endpointId => $entry) {
            if (!is_array($entry) || !isset($entry['path']) || !is_string($entry['path'])) {
                continue;
            }
            $path = str_replace('\\', '/', $entry['path']);
            if (!str_ends_with($path, '.php')) {
                continue;
            }
            $uri = substr($path, 0, -4);
            if ($uri === '' || in_array($uri, $skipUris, true)) {
                continue;
            }
            Route::match(['get', 'post'], '/' . $uri, [LegacyEndpointController::class, 'dispatch'])
                ->name('legacy.' . str_replace('.', '_', $endpointId));
        }
    }
}

// =====================================================================
// Vue SPA Fallback (for routes not yet migrated to Blade parity)
// =====================================================================
Route::get('/spa/{any?}', function () {
    return view('app');
})->where('any', '.*')->name('spa');

// =====================================================================
// User Profile catch-all: /{username} (must be last before SPA fallback)
// Legacy: handler.php checks if slug is a seller username
// =====================================================================
Route::get('/{username}', [LegacyPageController::class, 'userProfile'])
    ->where('username', '[a-zA-Z0-9_-]+')
    ->name('user.profile');
Route::post('/{username}', [LegacyPostController::class, 'dispatchRootPost'])
    ->where('username', '[a-zA-Z0-9_-]+');

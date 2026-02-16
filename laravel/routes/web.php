<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
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
// Authentication GET routes + logout
// =====================================================================
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// =====================================================================
// Admin Authentication
// =====================================================================
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

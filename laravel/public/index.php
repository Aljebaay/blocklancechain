<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// =====================================================================
// Legacy Admin Panel Bypass
//
// The admin panel (admin/index.php) is a self-contained legacy PHP app
// with its own session management, DB layer, includes, header() calls,
// and exit() calls. It cannot run inside Laravel's request lifecycle.
//
// We intercept admin URLs here (before Laravel boots) and serve them
// directly from the legacy codebase. Exceptions that still go through
// Laravel: /admin/login, /admin/logout, /admin/assets.
// =====================================================================
$_blcUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$_blcUri = '/'.trim($_blcUri, '/');

if (
    str_starts_with($_blcUri, '/admin') &&
    ! str_starts_with($_blcUri, '/admin/assets/')
) {
    // login / logout → always go through Laravel (handles password verification + session sync)
    // But redirect .php variants to the clean URL
    if ($_blcUri === '/admin/login.php') {
        $_blcQs = ! empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
        header('Location: /admin/login'.$_blcQs, true, 302);
        exit;
    }
    if ($_blcUri === '/admin/logout.php') {
        $_blcQs = ! empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
        header('Location: /admin/logout'.$_blcQs, true, 302);
        exit;
    }
    if ($_blcUri === '/admin/login' || $_blcUri === '/admin/logout') {
        // Fall through to Laravel
    } else {
        $_blcAdminBase = realpath(__DIR__.'/../../app/Modules/Platform/admin');

        if ($_blcAdminBase !== false) {
            // /admin with no query string → redirect to /admin/index?dashboard
            if (($_blcUri === '/admin' || $_blcUri === '/admin/') && empty($_SERVER['QUERY_STRING'])) {
                header('Location: /admin/index?dashboard', true, 302);
                exit;
            }

            // /admin with query string (e.g. /admin?dashboard) → rewrite to index
            if ($_blcUri === '/admin' || $_blcUri === '/admin/') {
                chdir($_blcAdminBase);
                require $_blcAdminBase.'/index.php';
                exit;
            }

            // /admin/index or /admin/index?page → serve index.php
            if ($_blcUri === '/admin/index' || $_blcUri === '/admin/index.php') {
                chdir($_blcAdminBase);
                require $_blcAdminBase.'/index.php';
                exit;
            }

            // /admin_images/* → static image serving (handled by Laravel route below)
            if (str_starts_with($_blcUri, '/admin_images/')) {
                // Fall through to Laravel
            }
            // /admin/{subpage} → check for subpage.php or set $_GET param
            elseif (preg_match('#^/admin/(.+)$#', $_blcUri, $_blcMatch)) {
                $_blcSub = $_blcMatch[1];

                // Static file requests (css, js, images, fonts) — NOT .php
                if (preg_match('/\.(?:js|css|map|png|jpe?g|gif|svg|ico|woff2?|ttf|eot|webp|mp4|webm|pdf|json)$/i', $_blcSub)) {
                    $_blcSub = str_replace(['..', '\\'], ['', '/'], $_blcSub);
                    $_blcStatic = $_blcAdminBase.'/'.$_blcSub;
                    $_blcStaticResolved = realpath($_blcStatic);
                    if (
                        $_blcStaticResolved !== false &&
                        is_file($_blcStaticResolved) &&
                        str_starts_with($_blcStaticResolved, $_blcAdminBase)
                    ) {
                        $_blcExt = strtolower(pathinfo($_blcStaticResolved, PATHINFO_EXTENSION));
                        $_blcMime = [
                            'css' => 'text/css',
                            'js' => 'application/javascript',
                            'json' => 'application/json',
                            'png' => 'image/png',
                            'jpg' => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'gif' => 'image/gif',
                            'svg' => 'image/svg+xml',
                            'ico' => 'image/x-icon',
                            'woff2' => 'font/woff2',
                            'woff' => 'font/woff',
                            'ttf' => 'font/ttf',
                            'eot' => 'application/vnd.ms-fontobject',
                            'map' => 'application/json',
                        ][$_blcExt] ?? 'application/octet-stream';
                        header('Content-Type: '.$_blcMime);
                        header('Cache-Control: public, max-age=86400');
                        readfile($_blcStaticResolved);
                        exit;
                    }
                    http_response_code(404);
                    echo 'Not Found';
                    exit;
                }

                // Handle .php extension URLs: /admin/something.php → require something.php
                if (str_ends_with($_blcSub, '.php')) {
                    $_blcSubSafe = basename($_blcSub); // e.g. "proceed.php"
                    $_blcCandidate = $_blcAdminBase.'/'.$_blcSubSafe;
                    if (is_file($_blcCandidate)) {
                        chdir($_blcAdminBase);
                        require $_blcCandidate;
                        exit;
                    }
                }

                // PHP sub-page without extension: check if {subpage}.php exists
                $_blcSubSafe = basename($_blcSub);
                $_blcCandidate = $_blcAdminBase.'/'.$_blcSubSafe.'.php';
                if (is_file($_blcCandidate)) {
                    chdir($_blcAdminBase);
                    require $_blcCandidate;
                    exit;
                }

                // Otherwise set as $_GET param and serve index.php
                if (preg_match('/^[0-9a-zA-Z_-]+$/', $_blcSubSafe)) {
                    $_GET[$_blcSubSafe] = '';
                    $_REQUEST = array_merge($_REQUEST, $_GET);
                }

                chdir($_blcAdminBase);
                require $_blcAdminBase.'/index.php';
                exit;
            }
        }
    }
}

// Clean up bypass variables
unset($_blcUri, $_blcAdminBase, $_blcMatch, $_blcSub, $_blcStatic, $_blcStaticResolved, $_blcExt, $_blcMime, $_blcSubSafe, $_blcCandidate);

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());

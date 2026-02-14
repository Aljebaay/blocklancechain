# LARAVEL_BRIDGE

## Goal
Run a Laravel 12 app alongside the legacy PHP runtime without changing any existing routes or behaviors. All legacy traffic continues to use `public/router.php`; only the `/ _app` prefix is routed to Laravel.

## Routing Strategy
- Prefix reserved for Laravel: `/_app` (no collisions with legacy endpoints per BASELINE.md).
- Bridge lives in `public/router.php`:
  - If request path starts with `/_app`, router forwards to `laravel/public/index.php`.
  - Static assets under `/_app` are served directly from `laravel/public` when present; otherwise Laravel handles the request.
  - All other paths follow legacy routing unchanged.
- Legacy `public/` and rewrites remain untouched.

## Request Flow (built-in PHP server)
1. Dev server runs `php -S 127.0.0.1:8080 -t public public/router.php` (same as Phase 1).
2. `public/router.php` checks prefix:
   - `/ _app/*` ? Laravel bootstrap (`laravel/public/index.php`).
   - Anything else ? existing legacy flow (static passthrough, includes, rewrites, handler slug fallback, etc.).

## Laravel App Location
- Path: `laravel/` (standard Laravel 12 structure).
- Public dir: `laravel/public` (used by bridge when serving `/ _app` static assets).

## Laravel Endpoints (Phase 2)
- `GET /_app/health` ? `{ "status": "ok", "version": "<laravel version>" }`
- `GET /_app/debug/routes` (only in local/when APP_DEBUG=true) ? JSON route list

## Environment & DB
- `.env.example` prefilled for legacy DB connection (host 127.0.0.1, port 3307, db `gig-zone`, user `root`/`root`).
- Session/cache drivers set to `file` to avoid migrations. No legacy schema changes.

## Running Locally
- Legacy + bridge together (recommended):
  - `./scripts/smoke.sh` (starts built-in server and hits both legacy + `/ _app/health`).
- Laravel-only (optional):
  - `cd laravel && composer install`
  - `php artisan serve` (defaults to port 8000) — useful for isolated Laravel dev; not used in bridge path.

## Rollback
- Remove `/ _app` block from `public/router.php` and delete `laravel/` if needed; legacy continues unaffected.

## Notes
- No legacy routes or response shapes changed.
- `/ _app` prefix reserved; do not reuse for legacy features.

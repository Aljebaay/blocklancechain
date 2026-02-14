# LARAVEL_BRIDGE

## Goal
Run a Laravel 12 app alongside the legacy PHP runtime without changing existing routes or behaviors. Legacy traffic stays on `public/router.php`; only the `/_app` prefix goes to Laravel.

## Routing Strategy
- Prefix reserved for Laravel: `/_app` (no collisions per BASELINE.md).
- Bridge is in `public/router.php`:
  - If path starts with `/_app`, forward to `laravel/public/index.php`.
  - Static assets under `/_app` are served directly from `laravel/public` when present; otherwise Laravel handles the request.
  - All other paths follow legacy routing unchanged.

## Request Flow (dev server)
1. Run `php -S 127.0.0.1:8080 -t public public/router.php`.
2. Router checks prefix:
   - `/_app/*` ? Laravel bootstrap.
   - Everything else ? legacy flow (static passthrough, includes, rewrites, slug fallback).

## Laravel App Location
- Path: `laravel/` (standard Laravel 12 layout).
- Public dir: `laravel/public` (used for `/ _app` static assets and bootstrap).

## Laravel Endpoints
- `GET /_app/health` ? `{ "status": "ok", "version": "<laravel>" }`
- `GET /_app/system/info` ? bridge status + DB connectivity
- `POST /_app/migrate/requests/fetch_subcategory` ? HTML `<option>` list (read-only mirror)
- `GET /_app/debug/routes` (APP_DEBUG=true or APP_ENV=local) ? route list

## Environment & DB
- `.env.example` prefilled for legacy DB (host 127.0.0.1, port 3307, db `gig-zone`, user `root`/`root`).
- Session/cache drivers: file; no legacy schema changes.
- Toggle: `MIGRATE_REQUESTS_FETCH_SUBCATEGORY` (default false). When true, `/requests/fetch_subcategory` is delegated to Laravel first; on exception, router falls back to legacy handler.

## Adding New Migrated Endpoints Safely
1) Use `legacy` connection for legacy data (read-only unless approved later).
2) Add routes under `/_app` in `laravel/routes/web.php`.
3) Do not change legacy routes or schemas. If toggling a legacy path, add an env toggle and fallback in `public/router.php`.
4) Add smoke probes for new `/ _app` endpoints.

## Running
- Unified: `./scripts/smoke.sh` (built-in server 127.0.0.1:8080).
- Laravel-only: `cd laravel && php artisan serve --host=127.0.0.1 --port=8000`.

## Rollback
- Remove `/_app` delegation blocks and/or toggles; legacy continues unaffected.

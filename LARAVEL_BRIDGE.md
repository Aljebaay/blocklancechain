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
- `POST /_app/migrate/proposals/ajax/check/pricing` ? mirrors legacy pricing check
- `GET|POST /_app/migrate/apis/index.php` ? mirrors legacy APIs front controller
- `GET /_app/migrate/requests/pause_request` ? mirrors legacy pause_request (write)
- `GET /_app/debug/routes` (APP_DEBUG=true or APP_ENV=local) ? route list

## Environment & DB
- `.env.example` prefilled for legacy DB (host 127.0.0.1, port 3307, db `gig-zone`, user `root`/`root`).
- Session/cache drivers: file; no legacy schema changes.
- Toggles (default false) controlling legacy delegation with fallback on empty/non-200/error:
  - `MIGRATE_REQUESTS_FETCH_SUBCATEGORY` for `/requests/fetch_subcategory`
  - `MIGRATE_PROPOSAL_PRICING_CHECK` for `/proposals/ajax/check/pricing`
  - `MIGRATE_APIS_INDEX` for `/apis/index.php`
  - `MIGRATE_REQUESTS_PAUSE_REQUEST` for `/requests/pause_request`

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

## Toggle safety and fallback (Phase 7)
- /requests/fetch_subcategory is guarded by MIGRATE_REQUESTS_FETCH_SUBCATEGORY (default false).
- When enabled, router buffers Laravel response and only serves it if status==200 and body is non-empty; otherwise it falls back to legacy public/requests/fetch_subcategory.php.
- FORCE_LARAVEL_FETCH_SUBCATEGORY_FAIL=true forces the Laravel handler to throw, exercising the fallback path.
- Smoke: use --mode=legacy|laravel|both (default both). In laravel mode with --force-fallback (or env flag), the same probe must still pass via legacy output.

## Phase 8 toggle pattern
- /requests/active_request now guarded by MIGRATE_REQUESTS_ACTIVE_REQUEST (default false).
- Router buffers Laravel response (status==200 & body non-empty) before serving; otherwise falls back to legacy public/requests/active_request.php.
- Mirror route: GET /_app/migrate/requests/active_request (isolated runner executes legacy script for parity).
- Smoke: --mode=legacy|laravel|both covers toggle off/on; legacy markers must remain identical.

## Phase 8 pricing_check toggle
- Endpoint: /proposal/pricing_check (alias of /proposals/ajax/check/pricing) guarded by MIGRATE_PROPOSAL_PRICING_CHECK (default false).
- Router buffers Laravel response; serves only when status==200 and body non-empty; otherwise falls back to legacy public/proposals/ajax/check/pricing.php.
- Laravel route: POST /_app/migrate/proposal/pricing_check (isolated runner).
- Testing: smoke --mode=legacy|laravel|both; optional --force-fallback-pricing (or env FORCE_LARAVEL_PROPOSAL_PRICING_FAIL=true) forces Laravel failure to confirm fallback.

## Requests Module Toggle (Phase 9)
- Module toggle: MIGRATE_REQUESTS_MODULE governs all /requests/* endpoints by default.
- Endpoint overrides: setting MIGRATE_REQUESTS_<ENDPOINT>=false forces legacy even when module toggle is true; setting to true enables a single endpoint when module toggle is false.
- Router isolation: exact-path checks only; any Laravel error, non-200, or empty body triggers legacy fallback.
- Force simulation: FORCE_LARAVEL_REQUESTS_MODULE_FAIL=true makes Laravel handlers return 500 so fallback can be verified via smoke.
- Smoke: use --mode=legacy|laravel|both; SMOKE_ALLOW_WRITES=false by default skips write-dependent checks; --force-fallback-requests tests fallback path.


## Phase 12: Requests module default and overrides
- Default: MIGRATE_REQUESTS_MODULE=true (Requests served by Laravel).
- Overrides (deprecated): MIGRATE_REQUESTS_<ENDPOINT>; use false to force legacy, true to enable a single endpoint when module is off. Behavior unchanged; fallback still buffered on error/non-200/empty.
- Rollback: set module toggle to false or set endpoint override false; restart built-in server/supervisor accordingly.


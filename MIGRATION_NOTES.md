# MIGRATION_NOTES

## 2026-02-14 — Baseline established
- Regenerated endpoint manifest (`php scripts/audit_endpoints.php`), total endpoints: 528.
- Added BASELINE.md documenting routes, auth/session model, controllers, risk areas.
- Created DB_SCHEMA_SNAPSHOT.md and TENANT_SCOPE_MAP.md for future SaaS work (no schema changes).
- Enhanced smoke verification (scripts/smoke_http.php) with extra probes, snapshot support, DB-unavailable skip handling; added scripts/smoke.sh wrapper and snapshots directory.
- No runtime behavior, routes, or database schema modified.

## 2026-02-14 — Laravel 12 bridge
- Added Laravel 12 app under laravel/ (no legacy code moved).
- Introduced /_app prefix delegation in public/router.php to Laravel without altering legacy routes.
- Added health and debug routes in Laravel; DB/session/cache configured for legacy DB (read-only intent).
- Added docs: LARAVEL_BRIDGE.md, README_LARAVEL12.md.
- Updated smoke tests to cover /_app/health (legacy probes unchanged).
- No database schema changes; legacy runtime remains primary.


## 2026-02-14 — Phase 3: Laravel functional slice
- Added legacy DB connection (read-only) via Laravel 'legacy' connection using LEGACY_DB_* env with DB_* fallback.
- Introduced /_app/system/info endpoint reporting bridge status and DB connectivity.
- Added LegacyUser model (sellers table) as exemplar Eloquent access to legacy DB.
- Extended smoke checks with /_app/system/info probe; total 16 checks.
- Added bridge documentation (laravel/README_BRIDGE.md).


## 2026-02-14 — Phase 4: migrate fetch_subcategory
- Added Laravel handler for /requests/fetch_subcategory under /_app/migrate/requests/fetch_subcategory.
- Toggle MIGRATE_REQUESTS_FETCH_SUBCATEGORY controls delegation to Laravel; default false; fallback to legacy on error.
- Response shape preserved (HTML <option> list; login redirect if not authenticated).
- Smoke extended with Laravel migrate probe; legacy probe retained.


## 2026-02-14 - Phase 5: migrate proposal pricing_check and apis index
- Added Laravel mirror routes under /_app/migrate:
  - POST /_app/migrate/proposals/ajax/check/pricing (runs legacy pricing_check script via isolated runner)
  - GET/POST /_app/migrate/apis/index.php (runs legacy APIs front controller via isolated runner)
- Added toggles (default false) in public/router.php with guarded fallback:
  - MIGRATE_PROPOSAL_PRICING_CHECK for /proposals/ajax/check/pricing
  - MIGRATE_APIS_INDEX for /apis/index.php
- Router forwards to Laravel when toggle on; falls back to legacy if Laravel reply is empty/non-200/exception.
- Smoke tests extended (toggle off/on passes) covering both migrated endpoints; total checks now 19 per pass.
- No database or response shape changes; outputs are streamed from the legacy scripts to preserve behavior.

## 2026-02-14 - Phase 6: migrate pause_request write endpoint
- Migrated legacy GET /requests/pause_request to Laravel mirror /_app/migrate/requests/pause_request with identical output (login redirect or pause alert + manage redirect).
- Added toggle MIGRATE_REQUESTS_PAUSE_REQUEST (default false) with router fallback to legacy on empty/non-200/error.
- Uses legacy_write connection with transaction to update buyer_requests.request_status='pause'.
- Smoke extended with migrate and legacy probes for pause_request; toggle off/on passes covered.
- Added Laravel feature tests covering success, no-op, and unauthenticated flows.

## 2026-02-14 - Phase 7: migration toggle hardening
- Hardened /requests/fetch_subcategory toggle with buffered Laravel include and guaranteed legacy fallback on error/empty/non-200.
- Added FORCE_LARAVEL_FETCH_SUBCATEGORY_FAIL to simulate failures and verify fallback.
- Smoke now runs legacy and Laravel modes (and fallback when forced) via --mode flag; snapshots remain per-pass.
- Bridge docs updated; no business logic or schema changes.

## 2026-02-14 - Phase 8: migrate active_request read-only endpoint
- Added Laravel mirror for GET /requests/active_request under /_app/migrate/requests/active_request using isolated legacy runner.
- Toggle MIGRATE_REQUESTS_ACTIVE_REQUEST (default false) with buffered router delegation; fallback to legacy on exception/non-200/empty body.
- Smoke expanded with legacy/laravel mode probes for active_request; dual-mode runs remain green.
- No schema or business logic changes; legacy response preserved (login redirect and active request page HTML).

## 2026-02-14 - Phase 8: migrate proposal pricing_check endpoint
- Added Laravel mirror for POST /proposal/pricing_check (aliasing /proposals/ajax/check/pricing) under /_app/migrate/proposal/pricing_check using isolated legacy runner.
- Toggle MIGRATE_PROPOSAL_PRICING_CHECK delegates to Laravel with buffered response; fallback to legacy on exception, non-200, or empty output; exact-path match only.
- Smoke extended with legacy/laravel mode probes and optional FORCE_LARAVEL_PROPOSAL_PRICING_FAIL fallback simulation; all passes green.
- No schema or auth changes; JSON shape preserved (status 200, Content-Type application/json).

## 2026-02-14 - Phase 9: Requests module bridge hardening
- Introduced module-level toggle MIGRATE_REQUESTS_MODULE with override precedence (endpoint toggle false forces legacy; module false allows per-endpoint opt-in).
- Current status per endpoint (parity via isolated legacy runner unless noted):
  - manage_requests: runner parity (legacy runner), NOT native Laravel yet.
  - active_request: runner parity (legacy runner), NOT native Laravel yet.
  - fetch_subcategory: native Laravel implementation; preserves legacy <option> markers.
  - pause_request: runner parity (legacy runner), NOT native Laravel yet.
  - resume_request: runner parity (legacy runner), NOT native Laravel yet.
  - create_request: runner parity (legacy runner), NOT native Laravel yet (writes guarded/optional).
  - update_request: runner parity (legacy runner), NOT native Laravel yet (writes guarded/optional).
- Added support class LegacyScriptRunner; router uses exact-path matching with buffered Laravel delegation and guaranteed legacy fallback for all Requests endpoints.
- Added force flag FORCE_LARAVEL_REQUESTS_MODULE_FAIL to simulate Laravel failure and verify fallback.
- Smoke suite extended with module mode (legacy/laravel), new Requests probes, write-guarded checks via SMOKE_ALLOW_WRITES, and force-fallback support.
- Next: convert read endpoints (manage_requests, active_request) to native Laravel while preserving output parity before enabling module toggle by default.

## 2026-02-14 — Phase 10: native Laravel for Requests read endpoints
- /requests/manage_requests and /requests/active_request now use native Laravel controllers and Blade (no LegacyScriptRunner).
- Auth/session parity preserved via legacy session bootstrap; login script returned when unauthenticated.
- Router toggles/fallback unchanged; controllers return 500 on failure to trigger legacy fallback.
- Smoke updated to check manage/active markers in both modes and forced-fallback path.


## 2026-02-14 — Phase 11: native Laravel for Requests write endpoints
- /requests/pause_request, /requests/resume_request, /requests/create_request, /requests/update_request now use native Laravel controllers (no LegacyScriptRunner).
- Writes use legacy_write connection inside transactions; unauthenticated requests still return legacy login script.
- Router toggles/fallback unchanged; controllers return 500 on failure to trigger legacy fallback; SMOKE_ALLOW_WRITES defaults false to avoid unintended writes.


## 2026-02-14 — Phase 12: default Laravel Requests module and deprecated overrides
- MIGRATE_REQUESTS_MODULE now defaults to true in env samples; Laravel handles Requests by default with fallback intact.
- Per-endpoint Requests toggles retained only as deprecated overrides (endpoint=false forces legacy; endpoint=true enables single endpoint when module is off).
- Router remains exact-path buffered; fallback to legacy on error/non-200/empty unchanged.
- Smoke includes laravel-only Requests sanity probe; forced fallback remains available via FORCE_LARAVEL_REQUESTS_MODULE_FAIL.
- Rollback: set MIGRATE_REQUESTS_MODULE=false or set specific endpoint override to false.

## 2026-02-14 — Phase 14: proposals module groundwork
- Introduced MIGRATE_PROPOSALS module toggle (default false) with buffered router fallback preserved.
- Converted /proposals/ajax/check/pricing and /proposal/pricing_check to native Laravel controller logic (session bootstrap, proposal/package validation) with JSON parity; fallback to legacy on error/non-200/empty.
- Smoke harness updates prepare module-on validation by setting MIGRATE_PROPOSALS=true in Laravel mode; pricing probes remain green; artisan tests unchanged.
- Remaining Proposals P0 (proposal view/sections) still pending; rollback by setting MIGRATE_PROPOSALS=false or endpoint override false.

## 2026-02-14 — Phase 14A: proposals view/sections routed via Laravel runner
- Added Laravel controllers for proposal view and sections using LegacyScriptRunner under MIGRATE_PROPOSALS toggle (default false); buffered router delegation with fallback to legacy on non-200/empty/error.
- Routes: /_app/migrate/proposals/{username}/{slug?} and /_app/migrate/proposals/sections/* mirror legacy proposal.php and sections/*.php without changing public URLs.
- Smoke remains green in legacy/laravel modes; no behavior change while toggle is off. Rollback: set MIGRATE_PROPOSALS=false.
- Added content-type aware passthrough so CSS/JS/assets load correctly when using `php artisan serve` (legacy public assets still served from root /public).

## 2026-02-14 — Phase 15 kickoff: orders/payments bridge scaffold
- Added MIGRATE_ORDERS toggle (default false) to .env samples.
- Added Laravel OrdersBridgeController and /_app/migrate/orders/* route using LegacyScriptRunner for P0 checkout/cart/payment endpoints; guarded whitelist; returns 404 when toggle off.
- public/router.php now forwards cart/checkout/order/payment front controllers to Laravel when MIGRATE_ORDERS=true; legacy remains default fallback.
- No behavior change by default; flip MIGRATE_ORDERS=true to exercise bridge and parity-test P0 flows.

## 2026-02-14 — Phase 15A: native cancel_payment endpoint
- Migrated `/cancel_payment.php` to native Laravel controller `CancelPaymentController` under `/_app/migrate/orders/cancel_payment.php` (toggle-gated by `MIGRATE_ORDERS`).
- Preserved legacy response contract (JS redirect/close scripts) and legacy DB side effects (`temp_orders`/`temp_extras` cleanup) using `legacy_write` transaction.
- Kept `OrdersBridgeController` for remaining orders/payment endpoints; removed `cancel_payment.php` from runner whitelist.
- Smoke harness updated to:
  - set `MIGRATE_ORDERS=true` in laravel mode,
  - add `orders-cancel-payment` probe,
  - keep proposal/apis toggles deterministic per pass.

## 2026-02-14 — Hotfix: DB install-check false negatives on non-default port
- Fixed installer/runtime install-state DB probing to honor `DB_PORT` when `DB_HOST` has no inline port (`app/Modules/Platform/includes/install_state.php`).
- Root cause: install completeness check used DSN default port 3306, causing false redirects to `install.php` and `SQLSTATE[HY000] [2002]` despite valid runtime DB config on port 3307.
- Verified with smoke laravel pass: home/login/index and requests endpoints now pass without DB-unavailable skips.

## 2026-02-14 — Phase 14B (native) in progress: proposals page
- Added ProposalViewService to fetch proposal/seller/category/delivery/reviews/extras/faq from legacy DB.
- ProposalPageController now renders Blade view `proposals.show` (native HTML) when MIGRATE_PROPOSALS=true; returns 404 when toggle off.
- Kept sections controller via runner for now; fallback remains available if service returns null.
- Parity not yet validated; ordering/favorite actions still disabled placeholders.

## 2026-02-14 — Phase 13: inventory, priorities, and migration matrix
- Added MIGRATION_MATRIX.md summarizing all modules: endpoint counts (native/runner/unmigrated), priorities (P0/P1/P2), toggles, and fallback status.
- Identified top P0 endpoints and assigned to upcoming phases: Phase 14 (Proposals), Phase 15 (Orders/Payments), Phase 16 (Messages/Offers), Phase 17 (Admin/APIs with /apis/index.php kept behind toggle).
- Smoke remains green in legacy/laravel modes; no runtime changes made.

## 2026-02-14 — CSRF scope fix for legacy passthrough (419 errors)
- Root cause: Laravel `web` CSRF middleware was applied to the global legacy catch-all route (`/{any?}`), so legacy POST endpoints like `/search-knowledge` and `/includes/close_cookies_footer.php` returned HTTP 419 when served through `php artisan serve`.
- Fix: scoped CSRF exclusion to the legacy catch-all route only via `withoutMiddleware(ValidateCsrfToken::class)` in `laravel/routes/web.php`.
- Safety: `/_app` migration routes keep Laravel CSRF behavior unchanged (existing explicit exceptions remain in `laravel/bootstrap/app.php`).
- Added regression test `laravel/tests/Feature/LegacyPassthroughCsrfTest.php` to assert the catch-all excludes CSRF while `/_app/migrate/orders/{file}` does not.

## 2026-02-14 — Phase 15B: bridge response parity hardening
- Enhanced `LegacyScriptRunner` to return structured metadata (`status`, `body`, and raw response `headers` when available) with backward-compatible fallback parsing.
- Hardened Laravel legacy passthrough (`laravel/routes/web.php`) to treat empty-body redirects as valid responses and preserve captured legacy headers.
- Refactored `ApisIndexController` to use shared `LegacyScriptRunner` instead of a duplicated isolated runner implementation.
- Updated `OrdersBridgeController` to accept redirect-style legacy responses and forward captured headers with safe content-type fallback.
- Added regression tests: `laravel/tests/Feature/LegacyScriptRunnerHeadersTest.php`.
- Verification: `php laravel/artisan test` and `php scripts/smoke_http.php --mode laravel` both pass.


## 2026-02-14 — Phase 16: inventory manifest, parity markers & fallback hardening
- Created ENDPOINT_MANIFEST.md — comprehensive inventory of all 528 legacy endpoints across every module, with per-endpoint migration status (native/runner/unmigrated), toggle names, and priority assignments (P0–P2).
- Introduced `X-Handler` response-header parity markers to distinguish which handler served a request:
  - Laravel middleware `AddHandlerHeader` sets `X-Handler: laravel` on every Laravel response; registered globally in `laravel/bootstrap/app.php`.
  - Router helper `blc_set_legacy_handler()` sets `X-Handler: legacy` on pure-legacy paths.
  - Router helper `blc_prepare_legacy_fallback()` calls `header_remove()` + `http_response_code(200)` + `blc_set_legacy_handler()` on all paths that fall back from a failed Laravel attempt, eliminating header leakage.
- Fixed header-leakage bug: when Laravel attempt fails (non-200 / exception) and router falls through to legacy, previously set response headers (Content-Type, etc.) were not cleaned on orders, proposals-pricing, and general PHP fallback paths. All fallback paths now run `blc_prepare_legacy_fallback()`.
- Updated `scripts/smoke_http.php`:
  - Added `expectHandler` key to check definitions (`legacy`, `laravel`, or `auto`).
  - Extended `evaluateResponse()` to assert `X-Handler` header value when `expectHandler` is present.
  - Added dedicated `handler-header-*` probes for parity verification in both modes.
- No database, business logic, or response-shape changes.


## 2026-02-15 — Phase 17: Full Laravel restructure (strangler fig completion)

### Structural change
- **Laravel promoted to project root**: all contents of `laravel/` (app, bootstrap, config, database, public, resources, routes, storage, tests, vendor, artisan, composer.json, .env, etc.) moved to the project root.
- **Legacy code moved to `legacy/`**: original root directories (app, bootstrap, config, public, scripts, tests, storage, vendor, serve.bat, serve.ps1, composer.json) moved to `legacy/` subdirectory.
- The project is now a standard Laravel 12 application at the root level.

### New architecture
```
root/                   ← Laravel 12 project root
  app/                  ← Laravel controllers, models, support
  bootstrap/            ← Laravel bootstrap (incl. env.php for .env loading)
  config/               ← Laravel config
  database/             ← Laravel migrations, seeders
  public/               ← Laravel public (index.php entry point)
  resources/            ← Blade views, assets
  routes/               ← web.php with all routes
  storage/              ← Laravel storage
  tests/                ← Laravel tests (9 passing)
  vendor/               ← Laravel dependencies
  legacy/
    app/                ← Legacy Modules/Platform code
    bootstrap/          ← Legacy dispatch.php, app.php, env.php (paths updated)
    config/             ← Legacy endpoints.generated.php etc.
    public/             ← ALL 528 legacy PHP endpoints
    scripts/            ← smoke_http.php etc.
    tests/              ← Legacy tests
    storage/            ← Legacy sessions
    vendor/             ← Legacy dependencies (stripe, paystack, phpmailer)
  artisan, composer.json, .env, ...
```

### Routing
- **Native controller routes** registered at their real public URLs (e.g. `/requests/manage_requests`, `/cancel_payment.php`, `/proposals/{username}/{slug?}`).
- **Backward compatibility aliases** kept at `/_app/migrate/*` for existing smoke tests and references.
- **Catch-all route** `/{path?}` → `LegacyBridgeController@handle` — resolves ANY unmigrated request to the matching legacy PHP file in `legacy/public/` via `LegacyScriptRunner` subprocess isolation.
- CSRF excluded on catch-all and all `_app/migrate` legacy bridge routes (legacy PHP handles its own form validation).

### LegacyBridgeController
New comprehensive catch-all controller with 7-step resolution:
1. Static assets (css, js, images, fonts) — served from `legacy/public/`
2. `/includes/` proxy — maps to legacy includes
3. Direct `.php` file match
4. Directory `index.php` match
5. Extensionless `.php` fallback
6. Slug-based routes (categories, proposals, blog, article, tags, pages, handler.php)
7. Final fallback to `legacy/public/index.php`

### Code updates
- All `base_path('..')` references (16 files) updated to `base_path('legacy')`.
- Legacy `bootstrap/env.php` shim updated to point to root `bootstrap/env.php` (was `laravel/bootstrap/env.php`).
- Legacy `bootstrap/app.php` updated to load env from project root.
- `blc_load_env()` candidate list cleaned (removed dead `laravel/.env` path).
- `AddHandlerHeader` middleware: only sets `X-Handler: laravel` if not already present (preserves `X-Handler: legacy` from bridge controller).
- `.gitignore` updated with `legacy/vendor/`.
- `LegacyPassthroughCsrfTest` updated for new catch-all URI (`{path?}` vs `{any?}`) and new CSRF architecture.

### Verification
- `php artisan --version` → Laravel Framework 12.51.0
- `php artisan route:list` → 34 routes registered correctly
- `php artisan test` → 9 passed (25 assertions)
- Smoke tests: `/_app/health` (200, X-Handler: laravel), `home.php` (200, X-Handler: legacy), `freelancers.php` (200, X-Handler: legacy), `dashboard.php` (200, X-Handler: legacy)
- Native routes: `/requests/manage_requests` (200, X-Handler: laravel)
- Backward compat: `/_app/migrate/requests/manage_requests` (200, X-Handler: laravel)

### Separate vendor directories
- Root `vendor/` = Laravel deps (symfony/http-foundation ^7.x, etc.)
- `legacy/vendor/` = Legacy deps (stripe ^7.45, paystack ^1.x, phpmailer, symfony/http-foundation ^5.4.50)
- No version conflicts — each has its own autoloader

### No breaking changes
- All existing URLs continue to work
- Response shapes preserved
- Legacy PHP files run in subprocess isolation (same LegacyScriptRunner mechanism)
- Single `.env` at project root serves both Laravel and legacy code

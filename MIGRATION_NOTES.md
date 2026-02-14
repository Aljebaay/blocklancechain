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

## 2026-02-14 — Phase 13: inventory, priorities, and migration matrix
- Added MIGRATION_MATRIX.md summarizing all modules: endpoint counts (native/runner/unmigrated), priorities (P0/P1/P2), toggles, and fallback status.
- Identified top P0 endpoints and assigned to upcoming phases: Phase 14 (Proposals), Phase 15 (Orders/Payments), Phase 16 (Messages/Offers), Phase 17 (Admin/APIs with /apis/index.php kept behind toggle).
- Smoke remains green in legacy/laravel modes; no runtime changes made.


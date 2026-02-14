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


## 2026-02-14 â€” Phase 5: migrate proposal pricing_check and apis index
- Added Laravel mirror routes under /_app/migrate:
  - POST /_app/migrate/proposals/ajax/check/pricing (runs legacy pricing_check script via isolated runner)
  - GET/POST /_app/migrate/apis/index.php (runs legacy APIs front controller via isolated runner)
- Added toggles (default false) in public/router.php with guarded fallback:
  - MIGRATE_PROPOSAL_PRICING_CHECK for /proposals/ajax/check/pricing
  - MIGRATE_APIS_INDEX for /apis/index.php
- Router forwards to Laravel when toggle on; falls back to legacy if Laravel reply is empty/non-200/exception.
- Smoke tests extended (toggle off/on passes) covering both migrated endpoints; total checks now 19 per pass.
- No database or response shape changes; outputs are streamed from the legacy scripts to preserve behavior.

## 2026-02-14 â€” Phase 6: migrate pause_request write endpoint
- Migrated legacy GET /requests/pause_request to Laravel mirror /_app/migrate/requests/pause_request with identical output (login redirect or pause alert + manage redirect).
- Added toggle MIGRATE_REQUESTS_PAUSE_REQUEST (default false) with router fallback to legacy on empty/non-200/error.
- Uses legacy_write connection with transaction to update buyer_requests.request_status='pause'.
- Smoke extended with migrate and legacy probes for pause_request; toggle off/on passes covered.
- Added Laravel feature tests covering success, no-op, and unauthenticated flows.

## 2026-02-14 â€” Phase 7: migration toggle hardening
- Hardened /requests/fetch_subcategory toggle with buffered Laravel include and guaranteed legacy fallback on error/empty/non-200.
- Added FORCE_LARAVEL_FETCH_SUBCATEGORY_FAIL to simulate failures and verify fallback.
- Smoke now runs legacy and Laravel modes (and fallback when forced) via --mode flag; snapshots remain per-pass.
- Bridge docs updated; no business logic or schema changes.

## 2026-02-14 â€” Phase 8: migrate active_request read-only endpoint
- Added Laravel mirror for GET /requests/active_request under /_app/migrate/requests/active_request using isolated legacy runner.
- Toggle MIGRATE_REQUESTS_ACTIVE_REQUEST (default false) with buffered router delegation; fallback to legacy on exception/non-200/empty body.
- Smoke expanded with legacy/laravel mode probes for active_request; dual-mode runs remain green.
- No schema or business logic changes; legacy response preserved (login redirect and active request page HTML).

## 2026-02-14 â€” Phase 8: migrate proposal pricing_check endpoint
- Added Laravel mirror for POST /proposal/pricing_check (aliasing /proposals/ajax/check/pricing) under /_app/migrate/proposal/pricing_check using isolated legacy runner.
- Toggle MIGRATE_PROPOSAL_PRICING_CHECK delegates to Laravel with buffered response; fallback to legacy on exception, non-200, or empty output; exact-path match only.
- Smoke extended with legacy/laravel mode probes and optional FORCE_LARAVEL_PROPOSAL_PRICING_FAIL fallback simulation; all passes green.
- No schema or auth changes; JSON shape preserved (status 200, Content-Type application/json).

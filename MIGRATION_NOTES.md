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

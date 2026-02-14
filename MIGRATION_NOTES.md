# MIGRATION_NOTES

## 2026-02-14 — Baseline established
- Regenerated endpoint manifest (`php scripts/audit_endpoints.php`), total endpoints: 528.
- Added BASELINE.md documenting routes, auth/session model, controllers, risk areas.
- Created DB_SCHEMA_SNAPSHOT.md and TENANT_SCOPE_MAP.md for future SaaS work (no schema changes).
- Enhanced smoke verification (scripts/smoke_http.php) with extra probes, snapshot support, DB-unavailable skip handling; added scripts/smoke.sh wrapper and snapshots directory.
- No runtime behavior, routes, or database schema modified.

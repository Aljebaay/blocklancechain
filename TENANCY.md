# Tenancy Plan (Phase 18 – Required Gate)

## Strategy
- **Resolution:** Path-based prefix `/t/{tenant}/...` (locked). Keeps DNS simple and works with built-in server.
- **Flag:** `TENANT_MODE` (default `false`). When off, app behaves single-tenant.
- **Prefix config:** `TENANT_PATH_PREFIX` (default `/t`).

## Context Derivation
- Middleware reads first path segment after prefix as `tenant_key`.
- Tenant is loaded from `tenants` table (or view) using `tenant_key` (slug/code).
- On success: set `tenant()` context (request + container), tag logs/metrics.
- On failure: 404 with legacy-compatible “Not Found” (no stack trace) and optional rollback to single-tenant when flag off.

## Scoping Rules (initial)
- DB connections: reuse existing `legacy`/`legacy_write`, but queries must filter by tenant columns listed in `TENANT_SCOPE_MAP.md`.
- Storage/Files: subfolders per tenant when we later move uploads; **Phase 18 does not move files yet**.
- Caches/queues: tag with `tenant_key` when enabled.

## Provisioning (Phase 18 deliverable)
- Console command `tenant:create {key} {name}` seeds defaults (currencies/settings stubs) without touching legacy tables.
- Optional seed for demo data behind `--seed-demo` flag (off by default).

## Isolation Tests (gate)
- Automated feature tests:
  - Cross-tenant read denied (404/empty) for Requests/Proposals/Messages.
  - Cross-tenant write denied (no DB change).
  - Tenant missing → 404.
- Smoke unchanged; tenancy flag defaults off.

## Rollback
- Set `TENANT_MODE=false` to bypass resolution and operate as single-tenant.
- Keep path prefix unused when flag off; no route changes required.

## Next Steps After Phase 18
- Gradually scope modules (Requests → Proposals → Orders/Payments → Messages → Admin) using `tenant()` helper.
- Move uploads to tenant-aware paths after isolation proven.

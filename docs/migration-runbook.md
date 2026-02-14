## Migration Runbook

### Pre-Checks
1. Confirm branch state with `git status`.
2. Confirm PHP runtime with `php -v`.
3. Regenerate endpoint inventory with:
   - `php scripts/audit_endpoints.php`

### Endpoint Toggle Strategy
Switches live in `config/app.php`:
- `endpoint_switch.use_new_default`
- `endpoint_switch.fallback_on_error`
- `endpoint_switch.overrides`

Current recommendation:
1. Keep `use_new_default=true`.
2. Keep `overrides=[]` unless doing a temporary experiment.
3. Re-run smoke checks after any switch change.

### Manifest Workflow
1. Generate endpoint inventory:
   - `php scripts/audit_endpoints.php`
2. Keep module-specific overrides in:
   - `config/endpoints.php`

### Public Compat Stubs
Generate all compatibility stubs under `public/`:
- `php scripts/generate_compat_stubs.php`
- `php scripts/generate_compat_stubs.php --force` to overwrite

### Runtime Cutover
1. Generate or refresh public stubs.
2. Ensure routing files exist in `public/` (`.htaccess`, `router.php`).
3. Point web server document root to `public/`.
4. Run regression checklist and smoke flows.
5. Keep rollback path: previous deployment package + previous document root.

### Current State
- Source tree is under `app/Modules/Platform/`.
- `config/endpoints.generated.php` is generated from `app/Modules/Platform/`.
- `public/` contains compatibility stubs and extensionless routing behavior.

### Phase Roadmap (13–22) with Gates

**Phase 13 — Inventory & Parity Baseline (complete)**  
- Deliverables: regenerated manifest; `MIGRATION_MATRIX.md`; P0 list mapped to Phases 14–17.  
- Gate: Smoke `--mode=both` green; matrix committed.  
- Rollback: docs only.

**Phase 14 — Proposals P0 (in progress)**  
- Scope: Proposal view/sections + pricing_check native; toggle `MIGRATE_PROPOSALS` (default false).  
- Gates: Legacy vs Laravel parity via smoke/feature; forced-fallback passes; artisan tests green.  
- Rollback: Set module toggle false or endpoint override false.

**Phase 15 — Orders & Payments P0**  
- Scope: checkout/order flows + gateways shims under `MIGRATE_ORDERS`.  
- Gates: Smoke (writes behind `SMOKE_ALLOW_WRITES`), mocked gateway tests; fallback passes.  
- Rollback: Module toggle false.

**Phase 16 — Messaging/Offers P0**  
- Scope: conversations index/view/send, send_offer_modal, attachments under `MIGRATE_MESSAGES`.  
- Gates: Parity smoke/feature; forced-fallback; artisan green.  
- Rollback: Module toggle false or endpoint override.

**Phase 17 — Admin & APIs**  
- Scope: Admin login/dashboard + critical CRUD; incremental `/apis/index.php` controllers behind `MIGRATE_APIS_INDEX`.  
- Gates: Per-controller parity; smoke both modes; forced-fallback for APIs; artisan green.  
- Rollback: Toggle false per module/controller.

**Phase 18 — Multi-Tenant Core (required gate)**  
- Strategy: Path-based `/t/{tenant}/...` resolution.  
- Deliverables: TENANCY.md, tenant middleware, updated `TENANT_SCOPE_MAP.md`, provisioning + isolation tests.  
- Gates: Isolation tests pass; `TENANT_MODE` flag for rollback; smoke unaffected.

**Phase 19 — Fresh-Install Readiness**  
- Deliverables: INSTALL.md, installer artisan cmd, clean env samples, CI fresh-install job.  
- Gates: Fresh install on empty DB passes smoke + artisan; no schema drift; writes disabled by default.

**Phase 20 — Performance & Hardening**  
- Deliverables: N+1 fixes, safe caching, logging/metrics, fallback verification.  
- Gates: Smoke/feature green; basic load within baseline; zero new P0 errors.  
- Rollback: Disable caches via config flags.

**Phase 21 — Final Cutover (Laravel primary)**  
- Deliverables: Router defaults to Laravel; legacy behind emergency flag; toggles default true.  
- Gates: Full smoke with modules on; forced-fallback available; artisan + tenant isolation tests green.  
- Rollback: Flip emergency flag; module toggles false if needed.

**Phase 22 — Cleanup & Deprecation**  
- Deliverables: Deprecation of per-endpoint overrides; docs to Laravel-only; optional legacy archival.  
- Gates: Stabilization window completes with tests green.  
- Rollback: During window use emergency/overrides; afterwards via deploy rollback.

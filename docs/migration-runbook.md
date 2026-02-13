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

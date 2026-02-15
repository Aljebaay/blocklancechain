# AUDIT_REPORT.md — Bug & Error Audit

**Date:** 2026-02-15
**Branch:** `feature/laravel12-bridge`
**Auditor:** Automated (GitHub Copilot)

---

## Environment Baseline

| Item | Value |
|---|---|
| PHP | 8.2.28 (ZTS, Xdebug 3.4.2) |
| Laravel | 12.51.0 |
| Composer | Valid (root + laravel/) |
| Node/Vite | Vite 7 + TailwindCSS 4 |
| DB Engine | MySQL 8.0 (docker), SQLite (tests) |
| Test runner | PHPUnit 11.5.x via `php artisan test` |
| Formatter | Laravel Pint (installed, 28 style issues) |
| Static Analysis | PHPStan **not installed** |
| CI | None detected |

### How to run tests

```bash
cd laravel
php artisan config:clear
php artisan test
```

### How to run Pint (style check only)

```bash
cd laravel
vendor/bin/pint --test
```

### Known toggles / legacy bridge behavior that must NOT be broken

- `MIGRATE_REQUESTS_MODULE` — module-level toggle for requests bridge
- Per-endpoint overrides (`MIGRATE_REQUESTS_FETCH_SUBCATEGORY`, etc.)
- `MIGRATE_PROPOSALS` — proposals bridge toggle (default: false)
- `MIGRATE_ORDERS` — orders bridge toggle (default: false)
- `MIGRATE_PROPOSAL_PRICING_CHECK` — pricing check toggle
- `MIGRATE_APIS_INDEX` — APIs index toggle
- Catch-all fallback route `{any?}` → legacy `router.php` (CSRF excluded)
- `blc_require_laravel()` in-process bridge in `public/router.php`
- `LegacyScriptRunner` subprocess bridge for `/_app/migrate/*` routes

---

## Test Baseline (before fixes)

```
Tests:    9 passed (25 assertions)
Duration: 1.04s
```

All 9 tests pass. No failures.

---

## Prioritized Bug Triage

### BUG-001 — BLOCKER: `ob_end_clean()` without buffer guard in `router.php`

- **Where:** `public/router.php` lines 54 and 197
- **How to reproduce:** Start server via `php -S` or docker, hit any `/_app/migrate/*` endpoint. Check `laravel/storage/logs/laravel.log` — every request logs `ErrorException: ob_end_clean(): Failed to delete buffer. No buffer to delete`.
- **Why it is a bug:** `ob_start()` creates a buffer, but `require $laravelIndex` boots Laravel which may consume the buffer internally. The `finally` block calls `ob_end_clean()` unconditionally. When the buffer was already consumed, this triggers an `ErrorException` caught by Laravel's error handler and logged.
- **Proposed fix:** Guard with `if (ob_get_level() > 0) { ob_end_clean(); }` at both locations.
- **Risk:** None. This is purely defensive — when a buffer exists it behaves identically; when it doesn't, it avoids the error.

### BUG-002 — MEDIUM: Unused import `Str` in `ProposalViewService.php`

- **Where:** `laravel/app/Services/Proposals/ProposalViewService.php` line 6
- **How to reproduce:** `vendor/bin/pint --test` flags `no_unused_imports`.
- **Why it is a bug:** Dead import. Causes lint failure. Could mask future autoloader issues.
- **Proposed fix:** Remove the `use Illuminate\Support\Str;` line.

### BUG-003 — MEDIUM: `ExampleTest` uses absolute URL instead of path

- **Where:** `laravel/tests/Feature/ExampleTest.php` line 16
- **How to reproduce:** Test passes currently, but uses `$this->get('http://localhost/_app/health')` which is non-idiomatic. If `APP_URL` changes or a test middleware inspects the host, this could break.
- **Why it is a bug:** Laravel's test client `$this->get()` is designed for relative paths. Using an absolute URL works by accident (Symfony's `Request::create()` parses it), but couples the test to `localhost`.
- **Proposed fix:** Change to `$this->get('/_app/health')`.

### BUG-004 — LOW: Duplicate import alias in `web.php`

- **Where:** `laravel/routes/web.php` lines 9 and 17
- **How to reproduce:** `vendor/bin/pint --test` flags `ordered_imports`. Both `ProposalPricingCheckController` (direct) and `LegacyProposalPricingCheckController` (alias) point to the same class.
- **Why it is a bug:** Confusing — reader assumes two different controllers. Both routes (`/proposals/ajax/check/pricing` and `/proposal/pricing_check`) invoke the same controller.
- **Proposed fix:** Remove the aliased import; use the direct import name in both routes.
- **Risk:** None. Same class, just different import name.

### BUG-005 — LOW (documented risk): Dead controller files

- **Where:** `laravel/app/Http/Controllers/LegacyBridge/ProposalSectionsController.php` and `ProposalViewController.php`
- **How to reproduce:** No route references these classes. `grep -r ProposalSectionsController routes/` returns nothing.
- **Why it is a risk:** Dead code. Not causing errors, but could confuse future maintainers. The active route uses `Proposals\ProposalSectionController` and `Proposals\ProposalPageController` instead.
- **Proposed fix:** Document only. Do NOT delete (>300 lines policy, and they serve as reference for the migration).

### RISK-001 — Documented: CSRF not excluded for POST on proposals/orders `/_app` routes

- **Where:** `laravel/bootstrap/app.php` CSRF exception list
- **Impact:** POST requests to `/_app/migrate/proposals/sections/{path}`, `/_app/migrate/proposals/{username}/{slug?}`, and `/_app/migrate/orders/*` will be rejected with 419 if they lack a Laravel CSRF token.
- **Status:** Intentional — test `LegacyPassthroughCsrfTest::test_migrate_orders_route_keeps_laravel_csrf_middleware()` explicitly asserts this. `MIGRATE_PROPOSALS` and `MIGRATE_ORDERS` are off by default.
- **Action:** No fix. Document as risk for when these toggles are enabled.

### RISK-002 — Documented: Pint reports 28 style issues

- **Status:** All are cosmetic (`concat_space`, `single_quote`, `line_ending`, `ordered_imports`). None affect runtime behavior.
- **Action:** No mass-formatting per audit constraints. Individual fixes only where tied to a real bug.

---

## Fixed Bugs

### FIX-001: `ob_end_clean()` guard in `router.php`

- **Before:** `ErrorException` logged on every `/_app` bridge request
- **Patch:** Guard both `ob_end_clean()` calls with `ob_get_level() > 0`
- **Verification:** Start server, hit `/_app/health` endpoint, check logs — no more `ob_end_clean` errors
- **Rollback:** Revert the two lines back to unconditional `ob_end_clean()`

### FIX-002: Remove unused `Str` import in `ProposalViewService`

- **Before:** Pint flags `no_unused_imports`
- **Patch:** Remove `use Illuminate\Support\Str;`
- **Verification:** `vendor/bin/pint --test app/Services/` passes `no_unused_imports`
- **Rollback:** Re-add the import line

### FIX-003: `ExampleTest` — use relative path

- **Before:** `$this->get('http://localhost/_app/health')` — non-idiomatic
- **Patch:** `$this->get('/_app/health')`
- **Verification:** `php artisan test --filter=ExampleTest` still passes
- **Rollback:** Restore the `http://localhost` prefix

### FIX-004: Remove duplicate import alias in `web.php`

- **Before:** Same controller imported twice under two names
- **Patch:** Remove alias import, use direct class name in both routes
- **Verification:** `php artisan route:list` shows same routes. Tests pass.
- **Rollback:** Re-add the aliased import

---

## How to verify locally

```bash
cd laravel

# 1. Clear caches
php artisan config:clear
php artisan cache:clear

# 2. Run full test suite (should still be 9 passing)
php artisan test

# 3. Check routes are unchanged
php artisan route:list

# 4. Start server and check logs for ob_end_clean errors
# (via docker-compose or php artisan serve)
# Hit: http://localhost:8080/_app/health
# Then check: storage/logs/laravel.log — should NOT contain ob_end_clean errors

# 5. Verify Pint improvements (optional)
vendor/bin/pint --test app/Services/Proposals/ProposalViewService.php
```

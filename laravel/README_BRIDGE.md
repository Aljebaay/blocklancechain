# Laravel Bridge Reference

## Purpose
Document the bridge patterns for incremental Laravel adoption alongside the legacy platform. The bridge reserves the `/_app` prefix for new Laravel endpoints while keeping legacy routing untouched.

## Legacy DB Connection (read-only)
- Connection name: `legacy`
- Driver: mysql
- Config source: `LEGACY_DB_HOST/PORT/DATABASE/USERNAME/PASSWORD`; falls back to `DB_*` if the LEGACY_* vars are absent.
- Charset/collation: utf8mb4 / utf8mb4_unicode_ci
- No migrations run against legacy tables; usage is read-only in this phase.

## Bridge Prefix
- Reserved prefix: `/_app`
- Delegation handled in `public/router.php` before legacy rewrites.
- Static assets under `/_app` are served from `laravel/public`; everything else under the prefix is handled by Laravel.

## Current Laravel Endpoints
- `GET /_app/health` ? `{ status: "ok", version: <laravel> }`
- `GET /_app/system/info` ? system status JSON with DB connectivity flag
- `GET /_app/debug/routes` ? route list (only in local/APP_DEBUG=true)

## Adding New Migrated Endpoints Safely
1) Create controllers/models under `laravel/app/...` using `legacy` connection for legacy data (read-only unless explicitly approved for writes in later phases).
2) Add routes under the `/_app` prefix in `laravel/routes/web.php` (or API routes if we introduce them later).
3) Do not change legacy routes, router.php behavior, or legacy schemas.
4) Add smoke probes for any new critical `/ _app` endpoints to `scripts/smoke_http.php`.

## Running
- Unified (legacy + bridge): `./scripts/smoke.sh` (built-in server on 127.0.0.1:8080)
- Laravel-only (optional): `cd laravel && php artisan serve --host=127.0.0.1 --port=8000`

## Rollback
- Remove the `/_app` delegation block from `public/router.php` and delete/ignore new routes; legacy continues unchanged.

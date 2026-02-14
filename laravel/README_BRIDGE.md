# Laravel Bridge Reference

## Purpose
Document bridge patterns for incremental Laravel adoption alongside the legacy platform. The bridge reserves `/_app` for Laravel while keeping legacy routing untouched.

## Legacy DB Connection (read-only)
- Connection: `legacy` (mysql)
- Env: `LEGACY_DB_HOST/PORT/DATABASE/USERNAME/PASSWORD` (fallback to `DB_*`)
- Charset/collation: utf8mb4 / utf8mb4_unicode_ci
- No migrations against legacy tables in this phase.

## Current Laravel Endpoints
- `GET /_app/health`
- `GET /_app/system/info`
- `POST /_app/migrate/requests/fetch_subcategory`
- `GET /_app/debug/routes` (local/APP_DEBUG=true)

## Migration Toggles
- `MIGRATE_REQUESTS_FETCH_SUBCATEGORY` (default false)
  - When true, `public/router.php` delegates `/requests/fetch_subcategory` to Laravel first; on exception, it falls back to legacy.

## Adding New Migrated Endpoints Safely
1) Build controllers/models in `app/Http/Controllers/...` using `legacy` connection.
2) Add routes under the `/_app` prefix.
3) If mirroring a legacy URL, add an env toggle + fallback in `public/router.php` and keep legacy behavior unchanged.
4) Extend smoke checks for new `/ _app` endpoints.

## Running
- Unified: `./scripts/smoke.sh`
- Laravel only: `php artisan serve --host=127.0.0.1 --port=8000`

## Rollback
- Turn off toggles and/or remove delegation blocks; legacy continues unaffected.

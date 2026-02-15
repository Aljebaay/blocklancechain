# Laravel 12 Application — Developer Guide

## What is this?
This is now a standard Laravel 12 application at the project root. Legacy PHP code lives in the `legacy/` subdirectory and is served via `LegacyBridgeController` using subprocess isolation (`LegacyScriptRunner`).

## Prerequisites
- PHP 8.2+
- Composer
- Node 20+ (only for Vite/front-end builds)
- MySQL (default port 3307, database `gig-zone`)

## Setup
```bash
# From project root
composer install
cp .env.example .env   # already prefilled for legacy DB
php artisan key:generate

# Legacy dependencies (separate vendor)
cd legacy && composer install && cd ..
```

## Running
```bash
php artisan serve --host=127.0.0.1 --port=8000
```
All URLs work through Laravel's routing:
- Native Laravel endpoints serve directly
- Unmigrated endpoints route through `LegacyBridgeController` → `LegacyScriptRunner`

## Key URLs
- `/_app/health` — health check (JSON)
- `/_app/system/info` — bridge status and DB connectivity
- `/_app/debug/routes` — route list (APP_DEBUG=true only)
- `/home.php`, `/freelancers.php`, etc. — served via legacy bridge
- `/requests/manage_requests` — native Laravel controller

## Frontend (optional)
```bash
npm install
npm run dev   # or npm run build
```

## Environment
- Single `.env` at project root serves both Laravel and legacy code
- `DB_*` vars for Laravel, `LEGACY_DB_*` for legacy read-only connection
- `MIGRATE_*` toggles control per-endpoint migration behavior

## Project structure
```
root/               ← Laravel 12 (artisan, composer.json, .env)
  app/              ← Controllers, Models, Support (LegacyScriptRunner)
  routes/web.php    ← All routing (native + bridge + catch-all)
  legacy/           ← All original legacy code
    public/         ← 528 legacy PHP endpoints
    vendor/         ← Legacy dependencies (separate from root vendor/)
```

## How the legacy bridge works
1. Request hits Laravel's `routes/web.php`
2. If a native route matches, Laravel handles it directly
3. Otherwise, the catch-all `/{path?}` route invokes `LegacyBridgeController`
4. `LegacyBridgeController` resolves the request to a file in `legacy/public/`
5. `LegacyScriptRunner` executes the legacy PHP file in a subprocess
6. Response (body, headers, status) is captured and returned as a Laravel Response
7. `X-Handler` header distinguishes: `laravel` (native) vs `legacy` (bridge)

## Docs
- [MIGRATION_NOTES.md](MIGRATION_NOTES.md) — full migration history
- [BASELINE.md](BASELINE.md) — original project baseline
- [TENANCY.md](TENANCY.md) — multi-tenant roadmap

## Safety
- No legacy routes changed.
- No DB schema changes required.
- `/ _app` prefix reserved for Laravel features only.

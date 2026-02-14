# Laravel 12 Bridge - Developer Guide

## What is this?
A Laravel 12 app under `laravel/` runs side-by-side with the legacy platform. The legacy runtime stays primary; only the `/_app` prefix goes to Laravel via `public/router.php`.

## Prereqs
- PHP 8.2+
- Composer
- Node 20+ (only if you run Vite/Front-end builds)

## Setup
```bash
cd laravel
composer install
cp .env.example .env   # already prefilled for legacy DB
php artisan key:generate
```

## Running
### Legacy + Laravel together (recommended)
```bash
./scripts/smoke.sh    # from repo root; starts built-in server on 127.0.0.1:8080
```
- Access health: http://127.0.0.1:8080/_app/health

### Laravel only (optional)
```bash
cd laravel
php artisan serve --host=127.0.0.1 --port=8000
```
(Use `/health` via that server; bridge is not used in this mode.)

## Frontend (optional)
If you need Vite/Vue later:
```bash
cd laravel
npm install
npm run dev   # or npm run build
```

## Environment
- APP_URL: http://127.0.0.1:8080/_app
- Primary DB: matches legacy (DB_HOST=127.0.0.1, DB_PORT=3307, DB_DATABASE=gig-zone, DB_USERNAME=root, DB_PASSWORD=root)
- Legacy read-only DB: configure LEGACY_DB_HOST/PORT/DATABASE/USERNAME/PASSWORD with a dedicated **read-only** user; there is no fallback to DB_*.
- SESSION_DRIVER=file, CACHE_STORE=file, QUEUE_CONNECTION=sync (no migrations required)

## Endpoints (Phase 2)
- `GET /_app/health` ? `{ "status": "ok", "version": "<laravel version>" }`
- `GET /_app/debug/routes` ? route list (only when APP_DEBUG=true or APP_ENV=local)

## Safety
- No legacy routes changed.
- No DB schema changes required.
- `/ _app` prefix reserved for Laravel features only.

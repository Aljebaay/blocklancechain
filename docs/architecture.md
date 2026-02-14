## Architecture Overview

### Goal
Move from a flat PHP tree to a modular, team-friendly structure without breaking existing URLs or include paths.

### Runtime Layers
1. `bootstrap/app.php`
Loads session bootstrap, Composer autoload (if available), and project autoload for `App\*`.
2. `bootstrap/dispatch.php`
Resolves endpoint id to module handler.
3. `app/Modules/*`
Domain-first endpoint implementations.
4. `config/endpoints.php`
Endpoint manifest with generated handler map plus targeted domain overrides.

### Source Placement
- Full historical PHP tree lives under `app/Modules/Platform/`.
- Public entrypoints live under `public/` as compatibility stubs.
- Static assets are served from `app/Modules/Platform/` through `public/router.php` and `public/asset_proxy.php`.

### Endpoint Dispatch Contract
Each endpoint entry supports:
- `path`: original HTTP or filesystem path
- `handler`: new module handler (script path or class)

Dispatch sequence:
1. Read endpoint switches from `config/app.php`.
2. Execute `handler`.

### Domain Modules
- `app/Modules/Requests`
- `app/Modules/Proposals`
- `app/Modules/Conversations`
- `app/Modules/Orders`
- `app/Modules/Admin`
- `app/Modules/Shared`

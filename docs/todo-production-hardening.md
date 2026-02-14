# Production Hardening TODO

## Goal
Raise platform readiness to production-grade quality with measurable security and reliability improvements.

## Checklist
- [x] Harden language file loading and admin language editing path safety.
- [x] Replace risky SQL string interpolation in active requests endpoints with parameterized queries.
- [x] Upgrade vulnerable dependencies and remove abandoned package reliance where feasible.
- [x] Add automated quality checks in CI.
- [x] Expand regression checklist to cover newly hardened flows.
- [x] Run validation suite: PHP lint, smoke checks, composer audit.

## Completion Criteria
- Composer audit has no security vulnerability findings.
- Smoke checks pass.
- No direct file path traversal path for language file editing/loading.
- High-risk endpoints no longer interpolate SQL values directly.

## Residual Note
- One abandoned transitive package remains: `doctrine/annotations` via `mercadopago/dx-php` (no published security advisory in current audit output).

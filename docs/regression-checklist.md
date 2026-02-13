# Regression Checklist

Automated quick run:
- `composer smoke:http`

## Auth / Session
- `/login` loads and authenticates.
- `/logout` clears session.
- `/admin/login` authenticates admin.
- session timeout behavior remains unchanged.

## Requests Flow
- `/requests/manage_requests` renders.
- `/requests/pause_request?request_id=...` updates status and redirects.
- `/requests/fetch_subcategory` returns valid `<option>` list for category.
- `/requests/stripe_charge` starts payment flow without include/session errors.

## Proposal Pricing Flow
- pricing section renders from `proposals/sections/edit/pricing`.
- `/proposals/ajax/check/pricing` returns:
  - `false` when no changes
  - `true` when values differ

## Routing
- extensionless URL behavior matches previous behavior.
- root slug routing via `handler.php` works.
- nested rewrites (categories/proposals/blog/article/tags/pages) behave as before.

## Static Assets
- sample files from `styles/`, `js/`, `images/`, `fonts/` return `200`.

## Payments (Sandbox)
- Stripe, PayPal, Paystack entrypoints do not fail on bootstrap/include.

## Admin Smoke
- admin dashboard renders.
- representative CRUD pages load.

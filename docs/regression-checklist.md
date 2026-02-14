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
- `load_category_data` and `load_search_data` handle numeric/filter inputs without SQL errors.
- request search still returns expected results when `search` is empty and when it contains text.

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
- PayPal order creation returns valid order id JSON.
- PayPal capture endpoint returns success JSON for a valid sandbox order id.

## Admin Smoke
- admin dashboard renders.
- representative CRUD pages load.
- language settings page rejects invalid language identifiers and invalid file paths.
- language file save path is restricted to `languages/*.php` and updates remain valid PHP content.

## Runtime Language Safety
- runtime language loader resolves files only inside `app/Modules/Platform/languages`.
- fallback to English language file works when configured language file is missing/invalid.

## Homepage Copy
- hero heading and subheading match the latest approved copy.
- cards section headings/description are updated.
- trust boxes text is updated and still aligned correctly.
- featured link label uses language copy and routes to `/featured_proposals`.

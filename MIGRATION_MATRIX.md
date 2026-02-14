# Migration Matrix (Phase 13)

Source manifest: `config/endpoints.generated.php` (528 endpoints, regenerated 2026-02-14).

Status keys: **Native** (Laravel controllers/views, no runner), **Runner** (LegacyScriptRunner/bridge), **Unmigrated** (legacy runtime).
Priority: **P0** critical/top-traffic/auth/payment; **P1** core; **P2** long tail.

## Requests (19 endpoints)
- Native (7): manage_requests, active_request, pause_request, resume_request, create_request, update_request, fetch_subcategory.  
- Runner: 0  
- Unmigrated (12): buyer_requests, load_category_data, load_search_data, post_request, delete_request, insert_offer, offer_submit_order, load_category_data (api), load_search_data (api), buyer_requests assets, request_files/*, view_offers.  
- Toggles: MIGRATE_REQUESTS_MODULE (default true), per-endpoint overrides (deprecated) still honored.  
- Priority: P0 = post_request, buyer_requests, load_category_data, load_search_data (search/browse), view_offers, insert_offer; P1 = remaining; P2 = static assets.

## Proposals (72 endpoints)
- Native (3): proposal public page (`/proposals/{username}/{slug}`), `/proposals/ajax/check/pricing`, `/proposal/pricing_check`.  
- Runner (1): proposal sections passthrough (`/proposals/sections/*`).  
- Unmigrated: remaining proposal pages/coupons/files/edit flows.  
- Toggles: MIGRATE_PROPOSALS (default false), MIGRATE_PROPOSAL_PRICING_CHECK (compat toggle).  
- Priority: P0 = proposal view (handler/proposal.php), pricing_check (already bridged but not native), proposal sections/ajax; P1 = coupons/files; P2 = long-tail assets.

## Orders / Payments (53 endpoints: manage_orders 10 + orderIncludes 37 + paypal 6)
- Native (1): `/cancel_payment.php` (Laravel controller, legacy contract preserved).  
- Runner (16): cart/checkout/order/payment front controllers still bridged via `OrdersBridgeController`.  
- Unmigrated: remaining orders/checkout/payment flows (stripe_charge, paypal/*, paystack, mercadopago, dusupay, crypto, offer_submit_order, manage_orders pages).  
- Toggles: MIGRATE_ORDERS (default false).  
- Priority: P0 = checkout flows (stripe_charge, paypal/checkout, offer_submit_order, paystack/charge, mercadopago/dusupay/crypto), manage_orders main page; P1 = orderIncludes fragments; P2 = ancillary assets.

## Messages / Offers (26 conversations endpoints)
- Native: 0  
- Runner: 0  
- Unmigrated: conversations inbox, compose, attachments, view thread, send message.  
- Toggles: none yet.  
- Priority: P0 = conversations index/view/send; P1 = attachments; P2 = archives.

## Admin / APIs (232 endpoints: admin 231 + apis 1)
- Native: 0  
- Runner (1): /apis/index.php (bridge runner, toggle MIGRATE_APIS_INDEX default false).  
- Unmigrated: admin dashboards, CRUD, includes, login.  
- Toggles: MIGRATE_APIS_INDEX (default false).  
- Priority: P0 = admin/login.php, admin dashboard landing, key CRUD used by ops; P0 for /apis/index.php front controller (kept behind toggle, migrate incrementally); P1 = remaining admin pages; P2 = rarely used admin tools.

## Auth / Account (subset of root 104)
- Native: 0  
- Runner: 0  
- Unmigrated: login.php, register, forgot, session bootstrap dependencies.  
- Toggles: none.  
- Priority: P0 = login.php, register.php, forgot/password reset; P1 = account settings; P2 = profile niceties.

## Uploads / Files (shared across modules)
- Native: 0  
- Runner: 0  
- Unmigrated: upload handlers, asset proxies, proposal_files, request_files.  
- Toggles: none.  
- Priority: P0 = proposal_files, request_files upload/download; P1 = asset_proxy.php; P2 = misc file listings.

## Static / Pages (blog/article/categories/tags/pages) — 11 endpoints
- Native: 0  
- Runner: 0  
- Unmigrated: category routes, blog posts, article, tags, pages.  
- Toggles: none.  
- Priority: P1 = blog/article/pages; P2 = tags/categories static.

## P0 Assignment into Phases 14–17
- Phase 14 (Proposals): proposal view/handler, proposal sections ajax, pricing_check (convert from runner to native).  
- Phase 15 (Orders/Payments): stripe_charge, paypal checkout, paystack/mercadopago/dusupay/crypto charges, offer_submit_order, manage_orders landing.  
- Phase 16 (Messages/Offers): conversations index/view/send, send_offer_modal, insert_offer (requests → offers), attachments.  
- Phase 17 (Admin/APIs): /apis/index.php controllers migrated incrementally behind toggle; admin/login.php and admin dashboard + critical CRUD.

## Fallback status
- Router remains exact-path with buffered fallback; module toggles override deprecated endpoint toggles.  
- Legacy runtime intact for all unmigrated endpoints; forced-fallback flags verified for Requests (and available for Proposals via FORCE_LARAVEL_PROPOSALS_FAIL).
- Bridge response handling now treats redirect statuses as valid even with empty bodies, and preserves legacy headers when the runtime SAPI exposes them.

## Notes
- Counts per module derived from endpoint manifest grouping by first path segment; “root” endpoints (104) house auth/account and assorted platform pages—classified above where applicable.  
- Ambiguity defaults to conservative P0 for auth/payments/core browse/search.

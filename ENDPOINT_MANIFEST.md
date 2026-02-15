# ENDPOINT_MANIFEST.md

Generated: 2026-02-15  
Source: `config/endpoints.generated.php` (528 entries), manual directory scan of `public/`, `public/router.php` route map.

## Status Legend

| Key | Meaning |
|-----|---------|
| **native** | Fully implemented in Laravel controller + Blade/JSON, no LegacyScriptRunner |
| **runner** | Served via `LegacyScriptRunner` subprocess (parity via legacy code execution) |
| **unmigrated** | Legacy handler only; Laravel not involved |
| **router-rewrite** | Handled by `public/router.php` rewriting (categories, proposals slugs, blog, etc.) |

## Parity Markers

- `X-Handler: legacy` header — set by legacy path (to be added in Phase 1C).
- `X-Handler: laravel` header — set by Laravel controllers (to be added in Phase 1C).
- Markers are response headers only; they do not change content or behavior.

---

## Module: Requests (19 legacy endpoints)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | POST `/requests/fetch_subcategory` | `requests/fetch_subcategory.php` | **native** | `MIGRATE_REQUESTS_MODULE` / `MIGRATE_REQUESTS_FETCH_SUBCATEGORY` | HTML `<option>` list |
| 2 | GET `/requests/manage_requests` | `requests/manage_requests.php` | **native** | `MIGRATE_REQUESTS_MODULE` / `MIGRATE_REQUESTS_MANAGE_REQUESTS` | Blade view |
| 3 | GET `/requests/active_request` | `requests/active_request.php` | **native** | `MIGRATE_REQUESTS_MODULE` / `MIGRATE_REQUESTS_ACTIVE_REQUEST` | Blade view |
| 4 | GET `/requests/pause_request` | `requests/pause_request.php` | **native** | `MIGRATE_REQUESTS_MODULE` / `MIGRATE_REQUESTS_PAUSE_REQUEST` | Write: `buyer_requests.request_status` |
| 5 | GET `/requests/resume_request` | `requests/resume_request.php` | **native** | `MIGRATE_REQUESTS_MODULE` / `MIGRATE_REQUESTS_RESUME_REQUEST` | Write: `buyer_requests.request_status` |
| 6 | POST `/requests/create_request` | `requests/create_request.php` | **native** | `MIGRATE_REQUESTS_MODULE` / `MIGRATE_REQUESTS_CREATE_REQUEST` | Write |
| 7 | POST `/requests/update_request` | `requests/update_request.php` | **native** | `MIGRATE_REQUESTS_MODULE` / `MIGRATE_REQUESTS_UPDATE_REQUEST` | Write |
| 8 | GET `/requests/buyer_requests` | `requests/buyer_requests.php` | unmigrated | — | Browse/search |
| 9 | POST `/requests/load_category_data` | `requests/load_category_data.php` | unmigrated | — | AJAX |
| 10 | POST `/requests/load_search_data` | `requests/load_search_data.php` | unmigrated | — | AJAX |
| 11 | POST `/requests/post_request` | `requests/post_request.php` | unmigrated | — | Write |
| 12 | POST `/requests/delete_request` | `requests/delete_request.php` | unmigrated | — | Write |
| 13 | POST `/requests/insert_offer` | `requests/insert_offer.php` | unmigrated | — | Write |
| 14 | POST `/requests/offer_submit_order` | `requests/offer_submit_order.php` | unmigrated | — | Write/payment |
| 15 | GET `/requests/view_offers` | `requests/view_offers.php` | unmigrated | — | Read |
| 16 | POST `/requests/send_offer_modal` | `requests/send_offer_modal.php` | unmigrated | — | Modal HTML |
| 17 | POST `/requests/submit_proposal_details` | `requests/submit_proposal_details.php` | unmigrated | — | Write |
| 18 | POST `/requests/stripe_charge` | `requests/stripe_charge.php` | unmigrated | — | Payment |
| 19 | POST `/requests/crypto_charge` | `requests/crypto_charge.php` | unmigrated | — | Payment |
| 20 | POST `/requests/dusupay_charge` | `requests/dusupay_charge.php` | unmigrated | — | Payment |
| 21 | POST `/requests/mercadopago_charge` | `requests/mercadopago_charge.php` | unmigrated | — | Payment |
| 22 | POST `/requests/paystack_charge` | `requests/paystack_charge.php` | unmigrated | — | Payment |

**Module toggle:** `MIGRATE_REQUESTS_MODULE` (default `true`). Per-endpoint toggles deprecated but honored.  
**Forced fallback:** `FORCE_LARAVEL_REQUESTS_MODULE_FAIL`  
**Rollback:** `MIGRATE_REQUESTS_MODULE=false`

---

## Module: Proposals (72+ legacy endpoints)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | POST `/proposals/ajax/check/pricing` | `proposals/ajax/check/pricing.php` | **native** | `MIGRATE_PROPOSALS` / `MIGRATE_PROPOSAL_PRICING_CHECK` | JSON |
| 2 | POST `/proposal/pricing_check` | alias → pricing.php | **native** | `MIGRATE_PROPOSALS` / `MIGRATE_PROPOSAL_PRICING_CHECK` | JSON alias |
| 3 | GET `/proposals/{username}/{slug}` | `proposals/proposal.php` | **native** (Phase 14B) | `MIGRATE_PROPOSALS` | Blade `proposals.show` |
| 4 | GET `/proposals/sections/*` | `proposals/sections/*.php` | **runner** | `MIGRATE_PROPOSALS` | LegacyScriptRunner |
| 5 | GET `/proposals/view_proposals` | `proposals/view_proposals.php` | unmigrated | — | |
| 6 | GET `/proposals/create_proposal` | `proposals/create_proposal.php` | unmigrated | — | Write |
| 7 | GET `/proposals/edit_proposal` | `proposals/edit_proposal.php` | unmigrated | — | Write |
| 8 | POST `/proposals/save_package` | `proposals/save_package.php` | unmigrated | — | Write |
| 9 | POST `/proposals/save_pricing` | `proposals/ajax/save_pricing.php` | unmigrated | — | Write |
| 10 | POST `/proposals/save_delivery` | `proposals/ajax/save_delivery.php` | unmigrated | — | Write |
| 11 | POST `/proposals/save_proposal` | `proposals/ajax/save_proposal.php` | unmigrated | — | Write |
| 12 | POST `/proposals/upload_file` | `proposals/upload_file.php` | unmigrated | — | Write |
| 13 | POST `/proposals/insert_attribute` | `proposals/insert_attribute.php` | unmigrated | — | Write |
| 14 | POST `/proposals/delete_attribute` | `proposals/delete_attribute.php` | unmigrated | — | Write |
| 15 | POST `/proposals/save_attribute` | `proposals/save_attribute.php` | unmigrated | — | Write |
| 16 | POST `/proposals/insert_extra` | `proposals/ajax/insert_extra.php` | unmigrated | — | Write |
| 17 | POST `/proposals/delete_extra` | `proposals/ajax/delete_extra.php` | unmigrated | — | Write |
| 18 | POST `/proposals/edit_extra` | `proposals/ajax/edit_extra.php` | unmigrated | — | Write |
| 19 | POST `/proposals/insert_faq` | `proposals/ajax/insert_faq.php` | unmigrated | — | Write |
| 20 | POST `/proposals/delete_faq` | `proposals/ajax/delete_faq.php` | unmigrated | — | Write |
| 21 | POST `/proposals/edit_faq` | `proposals/ajax/edit_faq.php` | unmigrated | — | Write |
| 22 | POST `/proposals/convert_price` | `proposals/ajax/convert_price.php` | unmigrated | — | AJAX |
| 23 | GET `/proposals/download` | `proposals/download.php` | unmigrated | — | File download |
| 24 | GET `/proposals/featured_proposal` | `proposals/featured_proposal.php` | unmigrated | — | |
| 25 | GET `/proposals/view_coupons` | `proposals/view_coupons.php` | unmigrated | — | |
| 26 | POST `/proposals/create_coupon` | `proposals/create_coupon.php` | unmigrated | — | Write |
| 27 | GET `/proposals/view_referrals` | `proposals/view_referrals.php` | unmigrated | — | |
| 28 | POST `/proposals/crop_upload` | `proposals/crop_upload.php` | unmigrated | — | Write |
| 29 | GET `/proposals/activate_proposal` | `proposals/activate_proposal.php` | unmigrated | — | Write |
| 30 | GET `/proposals/pause_proposal` | `proposals/pause_proposal.php` | unmigrated | — | Write |
| 31 | GET `/proposals/delete_proposal` | `proposals/delete_proposal.php` | unmigrated | — | Write |
| 32 | POST `/proposals/submit_approval` | `proposals/submit_approval.php` | unmigrated | — | Write |
| 33 | POST `/proposals/fetch_subcategory` | `proposals/fetch_subcategory.php` | unmigrated | — | AJAX |
| 34 | GET `/proposals/load_packages` | `proposals/load_packages.php` | unmigrated | — | AJAX |
| 35 | POST `/proposals/sanitize_url` | `proposals/sanitize_url.php` | unmigrated | — | AJAX |
| 36 | GET `/proposals/couponsModals` | `proposals/couponsModals.php` | unmigrated | — | Modal |
| 37 | POST `/proposals/pay_featured_listing` | `proposals/pay_featured_listing.php` | unmigrated | — | Payment |
| 38 | POST `/proposals/stripe_listing_charge` | `proposals/stripe_listing_charge.php` | unmigrated | — | Payment |
| 39 | POST `/proposals/paystack_listing_charge` | `proposals/paystack_listing_charge.php` | unmigrated | — | Payment |
| 40 | POST `/proposals/crypto_charge` | `proposals/crypto_charge.php` | unmigrated | — | Payment |
| 41 | POST `/proposals/dusupay_charge` | `proposals/dusupay_charge.php` | unmigrated | — | Payment |
| 42 | POST `/proposals/mercadopago_charge` | `proposals/mercadopago_charge.php` | unmigrated | — | Payment |
| 43 | GET `/proposals/seller_vacation` | `proposals/seller_vacation.php` | unmigrated | — | |
| 44 | GET `/proposals/seller_vacation_modal` | `proposals/seller_vacation_modal.php` | unmigrated | — | Modal |
| 45 | POST `/proposals/ajax/check/attribute` | `proposals/ajax/check/attribute.php` | unmigrated | — | AJAX |
| 46 | POST `/proposals/ajax/check/delivery` | `proposals/ajax/check/delivery.php` | unmigrated | — | AJAX |
| 47 | POST `/proposals/ajax/check/description` | `proposals/ajax/check/description.php` | unmigrated | — | AJAX |
| 48 | POST `/proposals/ajax/check/overview` | `proposals/ajax/check/overview.php` | unmigrated | — | AJAX |
| 49 | POST `/proposals/ajax/check/delete_attribute` | `proposals/ajax/check/delete_attribute.php` | unmigrated | — | AJAX |
| 50 | POST `/proposals/ajax/check/delete_extra` | `proposals/ajax/check/delete_extra.php` | unmigrated | — | AJAX |
| 51 | POST `/proposals/ajax/check/delete_faq` | `proposals/ajax/check/delete_faq.php` | unmigrated | — | AJAX |
| 52 | POST `/proposals/ajax/check/edit_faq` | `proposals/ajax/check/edit_faq.php` | unmigrated | — | AJAX |
| 53 | POST `/proposals/ajax/check/insert_extra` | `proposals/ajax/check/insert_extra.php` | unmigrated | — | AJAX |
| 54 | POST `/proposals/ajax/check/insert_faq` | `proposals/ajax/check/insert_faq.php` | unmigrated | — | AJAX |
| 55 | POST `/proposals/ajax/check/update_attribute` | `proposals/ajax/check/update_attribute.php` | unmigrated | — | AJAX |
| 56 | POST `/proposals/ajax/edit_attribute` | `proposals/ajax/edit_attribute.php` | unmigrated | — | AJAX |
| 57 | POST `/proposals/ajax/insert_attribute` | `proposals/ajax/insert_attribute.php` | unmigrated | — | AJAX |
| 58 | POST `/proposals/ajax/delete_attribute` | `proposals/ajax/delete_attribute.php` | unmigrated | — | AJAX |
| 59 | POST `/proposals/ajax/upload_file` | `proposals/ajax/upload_file.php` | unmigrated | — | File upload |
| 60-72 | `/proposals/sections/create/*`, `/proposals/sections/edit/*` | `proposals/sections/*.php` | **runner** | `MIGRATE_PROPOSALS` | Create/edit wizard steps |

**Module toggle:** `MIGRATE_PROPOSALS` (default `false`).  
**Forced fallback:** `FORCE_LARAVEL_PROPOSALS_FAIL`  
**Rollback:** `MIGRATE_PROPOSALS=false`

---

## Module: Orders / Payments (53+ legacy endpoints)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | GET `/cancel_payment.php` | `cancel_payment.php` | **native** | `MIGRATE_ORDERS` | JS redirect/close |
| 2 | GET `/cart.php` | `cart.php` | **runner** | `MIGRATE_ORDERS` | OrdersBridgeController |
| 3 | POST `/cart_charge.php` | `cart_charge.php` | **runner** | `MIGRATE_ORDERS` | |
| 4 | POST `/cart_paystack_charge.php` | `cart_paystack_charge.php` | **runner** | `MIGRATE_ORDERS` | |
| 5 | POST `/cart_dusupay_charge.php` | `cart_dusupay_charge.php` | **runner** | `MIGRATE_ORDERS` | |
| 6 | POST `/cart_mercadopago_charge.php` | `cart_mercadopago_charge.php` | **runner** | `MIGRATE_ORDERS` | |
| 7 | POST `/cart_crypto_charge.php` | `cart_crypto_charge.php` | **runner** | `MIGRATE_ORDERS` | |
| 8 | GET `/checkout.php` | `checkout.php` | **runner** | `MIGRATE_ORDERS` | |
| 9 | POST `/checkout_charge.php` | `checkout_charge.php` | **runner** | `MIGRATE_ORDERS` | |
| 10 | GET `/order.php` | `order.php` | **runner** | `MIGRATE_ORDERS` | |
| 11 | GET `/order_details.php` | `order_details.php` | **runner** | `MIGRATE_ORDERS` | |
| 12 | POST `/paypal_charge.php` | `paypal_charge.php` | **runner** | `MIGRATE_ORDERS` | |
| 13 | GET `/paypal_order.php` | `paypal_order.php` | **runner** | `MIGRATE_ORDERS` | |
| 14 | GET `/paystack_order.php` | `paystack_order.php` | **runner** | `MIGRATE_ORDERS` | |
| 15 | GET `/mercadopago_order.php` | `mercadopago_order.php` | **runner** | `MIGRATE_ORDERS` | |
| 16 | GET `/dusupay_order.php` | `dusupay_order.php` | **runner** | `MIGRATE_ORDERS` | |
| 17 | GET `/crypto_order.php` | `crypto_order.php` | **runner** | `MIGRATE_ORDERS` | |
| 18 | GET `/cart_show.php` | `cart_show.php` | unmigrated | — | |
| 19 | GET `/cart_payment_options.php` | `cart_payment_options.php` | unmigrated | — | |
| 20 | GET `/buying_orders.php` | `buying_orders.php` | unmigrated | — | |
| 21 | GET `/selling_orders.php` | `selling_orders.php` | unmigrated | — | |
| 22 | GET `/buying_history.php` | `buying_history.php` | unmigrated | — | |
| 23 | GET `/selling_history.php` | `selling_history.php` | unmigrated | — | |
| 24 | GET `/purchases.php` | `purchases.php` | unmigrated | — | |
| 25 | GET `/revenue.php` | `revenue.php` | unmigrated | — | |
| 26 | GET `/checkoutPayMethods.php` | `checkoutPayMethods.php` | unmigrated | — | |
| 27 | POST `/stripe_order.php` | `stripe_order.php` | unmigrated | — | |
| 28 | POST `/stripe_webhook.php` | `stripe_webhook.php` | unmigrated | — | |
| 29 | POST `/paypal_adaptive.php` | `paypal_adaptive.php` | unmigrated | — | |
| 30 | POST `/paypal_capture.php` | `paypal_capture.php` | unmigrated | — | |
| 31 | GET `/crypto_ipn.php` | `crypto_ipn.php` | unmigrated | — | |
| 32 | GET `/crypto_return.php` | `crypto_return.php` | unmigrated | — | |
| 33 | GET `/crypto_charge.php` | `crypto_charge.php` | unmigrated | — | |
| 34 | GET `/dusupay_ipn.php` | `dusupay_ipn.php` | unmigrated | — | |
| 35 | GET `/dusupay_return.php` | `dusupay_return.php` | unmigrated | — | |
| 36 | GET `/dusupay_charge.php` | `dusupay_charge.php` | unmigrated | — | |
| 37 | POST `/paystack_charge.php` | `paystack_charge.php` | unmigrated | — | |
| 38 | POST `/mercadopago_charge.php` | `mercadopago_charge.php` | unmigrated | — | |
| 39-48 | `/manage_orders/*.php` | `manage_orders/*.php` | unmigrated | — | 10 order listing views |
| 49-53 | `/orderIncludes/**` | `orderIncludes/**/*.php` | unmigrated | — | ~37 include fragments |
| 54-59 | `/paypal/*.php` | `paypal/*.php` | unmigrated | — | 6 PayPal endpoints |

**Module toggle:** `MIGRATE_ORDERS` (default `false`).  
**Rollback:** `MIGRATE_ORDERS=false`

---

## Module: Messages / Conversations (16 endpoints)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | GET `/conversations/inbox` | `conversations/inbox.php` | unmigrated | — | |
| 2 | GET `/conversations/message` | `conversations/message.php` | unmigrated | — | |
| 3 | POST `/conversations/insert_inbox_message` | `conversations/insert_inbox_message.php` | unmigrated | — | Write |
| 4 | POST `/conversations/insert_offer` | `conversations/insert_offer.php` | unmigrated | — | Write |
| 5 | POST `/conversations/upload_file` | `conversations/upload_file.php` | unmigrated | — | Write |
| 6 | POST `/conversations/send_offer_modal` | `conversations/send_offer_modal.php` | unmigrated | — | Modal |
| 7 | POST `/conversations/accept_offer_modal` | `conversations/accept_offer_modal.php` | unmigrated | — | Modal |
| 8 | POST `/conversations/match_words` | `conversations/match_words.php` | unmigrated | — | AJAX |
| 9 | POST `/conversations/seller_typing_status` | `conversations/seller_typing_status.php` | unmigrated | — | AJAX |
| 10 | GET `/conversations/typeStatus` | `conversations/typeStatus.php` | unmigrated | — | AJAX |
| 11 | POST `/conversations/submit_proposal_details` | `conversations/submit_proposal_details.php` | unmigrated | — | Write |
| 12 | POST `/conversations/stripe_charge` | `conversations/stripe_charge.php` | unmigrated | — | Payment |
| 13 | POST `/conversations/crypto_charge` | `conversations/crypto_charge.php` | unmigrated | — | Payment |
| 14 | POST `/conversations/dusupay_charge` | `conversations/dusupay_charge.php` | unmigrated | — | Payment |
| 15 | POST `/conversations/mercadopago_charge` | `conversations/mercadopago_charge.php` | unmigrated | — | Payment |
| 16 | POST `/conversations/paystack_charge` | `conversations/paystack_charge.php` | unmigrated | — | Payment |

**Module toggle:** none yet (planned: `MIGRATE_MESSAGES`).  
**Rollback:** N/A (not started)

---

## Module: APIs (1 endpoint)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | GET/POST `/apis/index.php` | `apis/index.php` | **runner** | `MIGRATE_APIS_INDEX` | CodeIgniter-style front controller |

**Module toggle:** `MIGRATE_APIS_INDEX` (default `false`).

---

## Module: Auth / Account (root-level, ~20 endpoints)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | GET `/login` | `login.php` | unmigrated | — | P0 |
| 2 | GET `/logout` | `logout.php` | unmigrated | — | P0 |
| 3 | POST `/change_password` | `change_password.php` | unmigrated | — | Write |
| 4 | GET `/account_settings` | `account_settings.php` | unmigrated | — | |
| 5 | GET `/profile_settings` | `profile_settings.php` | unmigrated | — | |
| 6 | POST `/crop_upload` | `crop_upload.php` | unmigrated | — | Write |
| 7 | GET `/fb-config.php` | `fb-config.php` | unmigrated | — | OAuth |
| 8 | GET `/fb-callback.php` | `fb-callback.php` | unmigrated | — | OAuth |
| 9 | GET `/fb-register.php` | `fb-register.php` | unmigrated | — | OAuth |
| 10 | GET `/g-config.php` | `g-config.php` | unmigrated | — | OAuth |
| 11 | GET `/g-callback.php` | `g-callback.php` | unmigrated | — | OAuth |
| 12 | GET `/g-register.php` | `g-register.php` | unmigrated | — | OAuth |
| 13 | GET `/social-config.php` | `social-config.php` | unmigrated | — | OAuth |
| 14 | GET `/referral` | `referral.php` | unmigrated | — | |
| 15 | GET `/referral_modal` | `referral_modal.php` | unmigrated | — | |
| 16 | GET `/my_referrals` | `my_referrals.php` | unmigrated | — | |
| 17 | GET `/manage_contacts` | `manage_contacts.php` | unmigrated | — | |
| 18 | GET `/notifications` | `notifications.php` | unmigrated | — | |
| 19 | GET `/settings` | `settings.php` | unmigrated | — | |
| 20 | GET `/user.php` | `user.php` (handler) | unmigrated | — | Profile view |

**Module toggle:** none yet (planned: `MIGRATE_AUTH`).

---

## Module: Browse / Search / Home (~20 endpoints)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | GET `/` | `index.php` / `home.php` | unmigrated | — | P0 homepage |
| 2 | GET `/home` | `home.php` | unmigrated | — | |
| 3 | GET `/search` | `search.php` | unmigrated | — | |
| 4 | POST `/search_load` | `search_load.php` | unmigrated | — | AJAX |
| 5 | POST `/search-knowledge` | `search-knowledge.php` | unmigrated | — | |
| 6 | POST `/search_articles` | `search_articles.php` | unmigrated | — | |
| 7 | GET `/freelancers` | `freelancers.php` | unmigrated | — | |
| 8 | POST `/freelancer_load` | `freelancer_load.php` | unmigrated | — | AJAX |
| 9 | GET `/categories/{slug}` | `categories/category.php` | unmigrated | — | router-rewrite |
| 10 | POST `/category_load` | `category_load.php` | unmigrated | — | AJAX |
| 11 | GET `/featured_proposals` | `featured_proposals.php` | unmigrated | — | |
| 12 | POST `/featured_load` | `featured_load.php` | unmigrated | — | AJAX |
| 13 | GET `/random_proposals` | `random_proposals.php` | unmigrated | — | |
| 14 | POST `/random_load` | `random_load.php` | unmigrated | — | AJAX |
| 15 | GET `/top_proposals` | `top_proposals.php` | unmigrated | — | |
| 16 | POST `/top_load` | `top_load.php` | unmigrated | — | AJAX |
| 17 | POST `/change_currency` | `change_currency.php` | unmigrated | — | |
| 18 | POST `/change_language` | `change_language.php` | unmigrated | — | |
| 19 | POST `/change_qty` | `change_qty.php` | unmigrated | — | AJAX |
| 20 | GET `/how-it-works` | `how-it-works.php` | unmigrated | — | Static |
| 21 | GET `/knowledge_bank` | `knowledge_bank.php` | unmigrated | — | |
| 22 | GET `/start_selling` | `start_selling.php` | unmigrated | — | |
| 23 | GET `/terms_and_conditions` | `terms_and_conditions.php` | unmigrated | — | Static |
| 24 | GET `/maintenance` | `maintenance.php` | unmigrated | — | |
| 25 | GET `/user_home` | `user_home.php` | unmigrated | — | |
| 26 | GET `/dashboard` | `dashboard.php` | unmigrated | — | |

**Module toggle:** none yet.

---

## Module: Content Pages (blog, article, tags, pages, feedback)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | GET `/blog` | `blog/index.php` | unmigrated | — | |
| 2 | GET `/blog/{id}/{slug}` | `blog/post.php` | unmigrated | — | router-rewrite |
| 3 | GET `/article/{slug}` | `article/article.php` | unmigrated | — | router-rewrite |
| 4 | GET `/tags/{tag}` | `tags/tag.php` | unmigrated | — | router-rewrite |
| 5 | POST `/tag_load` | `tag_load.php` | unmigrated | — | AJAX |
| 6 | POST `/tag_sidebar` | `tag_sidebar.php` | unmigrated | — | AJAX |
| 7 | GET `/pages/{slug}` | `pages/index.php` | unmigrated | — | router-rewrite |
| 8 | GET `/feedback` | `feedback/index.php` | unmigrated | — | |
| 9 | GET `/feedback/idea` | `feedback/idea.php` | unmigrated | — | |
| 10 | POST `/feedback/post-idea` | `feedback/post-idea.php` | unmigrated | — | Write |
| 11 | GET `/feedback/my-feedback` | `feedback/my-feedback.php` | unmigrated | — | |
| 12 | GET `/customer_support` | `customer_support.php` | unmigrated | — | |
| 13 | GET `/support` | `support.php` | unmigrated | — | |
| 14 | GET `/ticket_support/view_tickets` | `ticket_support/view_tickets.php` | unmigrated | — | |
| 15 | GET `/ticket_support/view_conversation` | `ticket_support/view_conversation.php` | unmigrated | — | |

**Module toggle:** none yet.

---

## Module: Seller / Earnings (~10 endpoints)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | GET `/favorites` | `favorites.php` | unmigrated | — | |
| 2 | GET `/shopping_balance` | `shopping_balance.php` | unmigrated | — | |
| 3 | GET `/withdraw` | `withdraw.php` | unmigrated | — | |
| 4 | GET `/withdraw_manual` | `withdraw_manual.php` | unmigrated | — | |
| 5 | GET `/withdraw_wallet` | `withdraw_wallet.php` | unmigrated | — | |
| 6 | GET `/withdrawal_requests` | `withdrawal_requests.php` | unmigrated | — | |
| 7 | GET `/proposal_referrals` | `proposal_referrals.php` | unmigrated | — | |
| 8 | GET `/pages.php` | `pages.php` | unmigrated | — | |

---

## Module: Admin (231 endpoints in `admin/`)

All **unmigrated**. Toggle: none yet (planned: `MIGRATE_ADMIN`).

Key P0 endpoints:
- `/admin/login.php` — admin login
- `/admin/dashboard.php` — admin dashboard
- `/admin/index.php` — admin index
- `/admin/view_orders.php` — order management
- `/admin/view_proposals.php` — proposal management
- `/admin/view_sellers.php` — seller management
- `/admin/sales.php` — sales dashboard

---

## Module: Cron / System (~5 endpoints)

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | GET `/payouts_and_offers_cron.php` | `payouts_and_offers_cron.php` | unmigrated | — | Cron |
| 2 | GET `/view_earnings_cron.php` | `view_earnings_cron.php` | unmigrated | — | Cron |
| 3 | GET `/install.php` | `install.php` | unmigrated | — | Installer |
| 4 | GET `/install2.php` | `install2.php` | unmigrated | — | Installer |
| 5 | GET `/install3.php` | `install3.php` | unmigrated | — | Installer |

---

## Module: Utility / Proxies

| # | URL | File | Laravel Status | Toggle | Notes |
|---|-----|------|----------------|--------|-------|
| 1 | GET `/asset_proxy.php` | `asset_proxy.php` | unmigrated | — | Static asset proxy |
| 2 | GET `/includes_proxy.php` | `includes_proxy.php` | unmigrated | — | Includes proxy |
| 3 | GET `/handler.php` | `handler.php` | unmigrated | — | Catch-all slug handler |
| 4 | GET `/mobile_categories` | `mobile_categories.php` | unmigrated | — | |

---

## Module: Laravel Internal (`/_app/*`)

| # | URL | Laravel Status | Notes |
|---|-----|----------------|-------|
| 1 | GET `/_app/health` | **native** | Health check |
| 2 | GET `/_app/system/info` | **native** | System info |
| 3 | GET `/_app/debug/routes` | **native** | Debug (local only) |
| 4 | GET `/_app/debug/db` | **native** | Debug (local only) |

---

## Summary Counts

| Status | Count | % |
|--------|-------|---|
| native | 14 | 2.7% |
| runner | ~20 | 3.8% |
| unmigrated | ~494 | 93.5% |
| **Total** | **528** | 100% |

## Active Toggles

| Toggle | Default | Module |
|--------|---------|--------|
| `MIGRATE_REQUESTS_MODULE` | `true` | Requests |
| `MIGRATE_PROPOSALS` | `false` | Proposals |
| `MIGRATE_ORDERS` | `false` | Orders/Payments |
| `MIGRATE_APIS_INDEX` | `false` | APIs |
| `MIGRATE_PROPOSAL_PRICING_CHECK` | `false` | Proposals (compat) |
| `MIGRATE_REQUESTS_FETCH_SUBCATEGORY` | — | Requests (deprecated override) |
| `MIGRATE_REQUESTS_ACTIVE_REQUEST` | — | Requests (deprecated override) |
| `MIGRATE_REQUESTS_PAUSE_REQUEST` | — | Requests (deprecated override) |
| `MIGRATE_REQUESTS_RESUME_REQUEST` | — | Requests (deprecated override) |
| `MIGRATE_REQUESTS_CREATE_REQUEST` | — | Requests (deprecated override) |
| `MIGRATE_REQUESTS_UPDATE_REQUEST` | — | Requests (deprecated override) |
| `MIGRATE_REQUESTS_MANAGE_REQUESTS` | — | Requests (deprecated override) |

## Forced-Fallback Flags

| Flag | Tests |
|------|-------|
| `FORCE_LARAVEL_FETCH_SUBCATEGORY_FAIL` | fetch_subcategory fallback |
| `FORCE_LARAVEL_PROPOSAL_PRICING_FAIL` | pricing fallback |
| `FORCE_LARAVEL_PROPOSALS_FAIL` | proposals module fallback |
| `FORCE_LARAVEL_REQUESTS_MODULE_FAIL` | requests module fallback |

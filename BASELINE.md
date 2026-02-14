# Runtime Baseline (2026-02-14)

## Overview
- Endpoint manifest regenerated via `php scripts/audit_endpoints.php` on 2026-02-14.
- Total endpoints: 528 (config/endpoints.php, including overrides).
- Legacy runtime served from `app/Modules/Platform/` with public compat stubs under `public/` and dispatcher `bootstrap/dispatch.php`.

## Endpoint Categories (by id prefix)
- admin: 231
- proposals: 72
- orderIncludes: 37
- conversations: 26
- requests: 19
- paypal: 6; blog: 6
- feedback: 9; manage_orders: 10; ticket_support: 2; categories: 2; pages: 2
- Singletons (count =1 unless noted): account_settings, apis, article, buying_history, buying_orders, cancel_payment, cart, cart_charge, cart_crypto_charge, cart_dusupay_charge, cart_mercadopago_charge, cart_payment_options, cart_paystack_charge, cart_show, category_load, change_currency, change_language, change_password, change_qty, checkout, checkout_charge, checkoutpaymethods, crop_upload, crypto_charge, crypto_ipn, crypto_order, crypto_return, customer_support, dashboard, dusupay_charge, dusupay_ipn, dusupay_order, dusupay_return, favorites, fb-callback, fb-config, fb-register, featured_load, featured_proposals, freelancers, freelancer_load, g-callback, g-config, g-register, handler, home, how-it-works, index, install, install2, install3, knowledge_bank, login, logout, maintenance, manage_contacts, mercadopago_charge, mercadopago_order, mobile_categories, my_referrals, notifications, order, order_details, payouts_and_offers_cron, paypal_adaptive, paypal_capture, paypal_charge, paypal_order, paystack_charge, paystack_order, profile_settings, proposal_referrals, purchases, random_load, random_proposals, referral, referral_modal, revenue, router, search, search-knowledge, search_articles, search_load, selling_history, selling_orders, settings, shopping_balance, social-config, start_selling, stripe_config, stripe_order, stripe_webhook, support, tag_load, tag_sidebar, tags, terms_and_conditions, top_load, top_proposals, user, user_home, view_earnings_cron, withdraw, withdraw_manual, withdraw_wallet, withdrawal_requests.

## Routing Behavior (public/router.php)
- Static passthrough: serves files directly from `public/` if present.
- Asset proxy to legacy tree: matches common static extensions and streams files from `app/Modules/Platform/*` when paths exist, preserving content-type and length.
- Includes passthrough: `/includes/{name}` mapped to legacy include PHP under `app/Modules/Platform/includes/*` (enforces `.php` suffix, 404 otherwise).
- Directory index fallback: if URL maps to a directory containing `index.php`, that index is required with working directory changed.
- PHP stub fallback: if `{path}.php` exists under `public/`, it is required.
- Rewrites:
  - `/categories/{cat}/{child?}` -> sets `$_GET['cat_url']` and `cat_child_url`, then loads `public/categories/category.php`.
  - `/proposals/{username}/{slug...}` (excluding reserved `proposal_files|ajax|sections|coupons`) -> sets `username` and `proposal_url`, then loads `public/proposals/proposal.php`.
  - `/blog/{id}` (numeric) -> sets `id`, then loads `public/blog/post.php`.
  - `/article/{slug...}` -> sets `article_url`, then loads `public/article/article.php`.
  - `/tags/{slug...}` -> sets `tag`, then loads `public/tags/tag.php`.
  - `/pages/{slug...}` -> sets `slug`, then loads `public/pages/index.php`.
  - Single-segment slug (alnum, underscore, hyphen) -> sets `slug`, then loads `public/handler.php`.
- Default fallback: `public/index.php`.

## Endpoint Manifest Overrides (config/endpoints.php)
- Requests module overrides map to `app/Modules/Requests/*`: active_request, buyer_requests, crypto_charge, delete_request, dusupay_charge, fetch_subcategory, insert_offer, load_category_data, load_search_data, manage_requests, mercadopago_charge, offer_submit_order, pause_request, paystack_charge, post_request, send_offer_modal, stripe_charge, submit_proposal_details, view_offers.
- Proposals overrides: `proposals/sections/edit/pricing.php` ? `app/Modules/Proposals/Sections/Edit/pricing.php`; `proposals/ajax/check/pricing.php` ? `app/Modules/Proposals/Ajax/Check/pricing.php`.

## CodeIgniter API Controllers (app/Modules/Platform/apis/application/controllers)
- Apis: login, register, forgot, data($table,$id=""), deliver_order, add_buyer_rating, add_seller_rating, get_app_links.
- Call: index, view_call_status, incoming_call, ended_call, accept_call, decline_call, end_call.
- Messages: index, createInboxGroup, inboxGroupMessages, uploadFile, insertMessage, hideDeleteMessages.

## Directory / Module Layout
- Legacy front-end/back-end tree: `app/Modules/Platform/` (pages, assets, includes, admin, apis, libs, functions, images, styles, js, vendor, etc.).
- New modular slices: `app/Modules/Requests`, `app/Modules/Proposals`, `app/Modules/Conversations`, `app/Modules/Orders`, `app/Modules/Admin`, `app/Modules/Shared` (currently thin; overrides wired via manifest).
- Runtime bootstrap: `bootstrap/app.php` (env + session bootstrap + autoload), `bootstrap/dispatch.php` (dispatch by endpoint id), config files under `config/`.
- Public compat stubs: `public/*.php` generated via `scripts/generate_compat_stubs.php`, plus `public/router.php` for dev server.
- Scripts: tooling under `scripts/` (endpoint audit, compat stub generation, smoke tests, SQL).

## AUTHENTICATION AND SESSION MODEL
- Session bootstrap (`app/Modules/Platform/includes/session_bootstrap.php`):
  - Ensures session save path using writable directories (storage/sessions, .sessions fallback, temp) and creates missing dirs.
  - Enforces `session.use_strict_mode` and cookies only; sets SameSite=Lax, HttpOnly; `secure` flag auto-detected from HTTPS/forwarded proto.
  - Supports deferred regeneration when headers already sent; calls `blc_session_regenerate_id_safe()` wrapper.
- Login / registration (`includes/register_login_forgot.php`, `login.php`):
  - Registration validates inputs, hashes password, captures geo country via geoplugin, inserts seller + accounts, optional referral credit; regenerates session id on successful signup; may send verification email depending on settings.
  - Login validates credentials and uses `blc_session_regenerate_id_safe(true)` (seller login) to avoid fixation; supports email/username lookup; redirects to index on success; shows flash errors on failure.
  - Password reset uses `includes/password_reset.php` and `password_resets` table (selector + token hash, expiring tokens) for change_password flow.
- Admin auth (`admin/login.php`):
  - Starts session via same bootstrap, includes admin DB bootstrap; redirects to admin dashboard if `$_SESSION['admin_email']` set; stores `adminLanguage` default; standard form post handles login (logic in included scripts). Session uses same cookie hardening.
- CSRF:
  - User auth forms rely on server-side validation; admin critical actions use `admin_csrf_require/token` utilities (see security hardening notes) though login itself is form POST without explicit CSRF token.
- Cookies: PHP session cookie only; SameSite=Lax, HttpOnly, optional Secure; additional app cookies not set in these flows.

## CRITICAL RISK AREAS — DO NOT BREAK DURING MIGRATION
- Router rewrite semantics (category/proposal/blog/article/tag/page/slug fallbacks) and static passthrough must remain intact.
- Handler slug fallback (`public/router.php` slug -> handler.php) used for many content pages.
- Manifest-driven endpoint dispatch (config/endpoints.php) including overrides to new modules must preserve ids and paths.
- CodeIgniter API routing under `app/Modules/Platform/apis` (controllers + index.php bootstrap).
- Session bootstrap directory resolution and cookie flags; regeneration hooks in auth flows.
- Database schema assumptions (table/column names, language & settings tables, payment settings); see DB_SCHEMA_SNAPSHOT.md.

## Generation / Maintenance Commands
- Refresh endpoint manifest: `php scripts/audit_endpoints.php` (writes config/endpoints.generated.php).
- Regenerate public compat stubs: `php scripts/generate_compat_stubs.php` (optionally --force).
- Smoke checks: `composer smoke:http` or `./scripts/smoke.sh`.

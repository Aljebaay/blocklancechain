# DB_SCHEMA_SNAPSHOT (2026-02-14)

Source files:
- scripts/sql/gig-zone.sql (primary schema dump)
- scripts/sql/2026-02-14_homepage_copy_refresh.sql
- scripts/sql/2026-02-14_password_resets.sql
- config/db.php
- .env (connection values)

Connection defaults (.env):
- host: 127.0.0.1:3307
- database: gig-zone
- user: root
- password: root
- charset/collation: utf8mb4 / utf8mb4_unicode_ci

## Major Tables & Keys
- sellers (PK seller_id int) — user accounts; fields include email, username, pass hash, country, level, status, activity timestamps.
- seller_accounts (PK id, FK seller_id) — financial balances.
- seller_payment_settings (PK id, FK seller_id) — payout details.
- seller_reviews (PK review_id, FK seller_id, order_id).
- seller_levels (+ seller_levels_meta) — level configuration.
- buyers are implicit via sellers; buyer_requests (PK request_id, FK seller_id) — posted requests; related send_offers, messages_offers.
- proposals (PK proposal_id, FK seller_id, category_id, child_id) — gigs; related tables: proposal_packages, proposals_extras, proposal_modifications, proposals_faq, proposal_referrals, proposal_packages attributes, featured_proposals, top_proposals, recent_proposals.
- orders (PK order_id, FK seller_id, buyer_id, proposal_id) — core order record.
- order_extras (PK id, FK order_id), order_conversations (PK id, FK order_id, sender_id), order_tips (PK id, FK order_id, sender_id), orderIncludes (PK include_id, FK order_id) — per-order details and attachments.
- revenues (PK revenue_id, FK order_id, seller_id), payouts (PK payout_id, FK seller_id), withdrawals (PK id, FK seller_id) — money flow.
- payments: payment_settings (singleton), api_settings (S3, API keys), currency_converter_settings, currencies, site_currencies; gateway-specific orders: paypal_orders (via paypal order endpoints), dusupay_orders, mercadopago/paystack/stripe config stored in payment_settings; cart and cart_extras capture in-progress purchases.
- messaging: inbox_sellers (PK id, FK seller_id, receiver_id), inbox_messages (PK message_id, FK sender_id, receiver_id, order_id optional), archived_messages, starred_messages, unread_messages, hide_seller_messages, send_offers, messages_offers; support_conversations/support_tickets for support desk.
- content: categories (PK cat_id), categories_children (PK child_id, FK cat_id), cats_meta/child_cats_meta, slider, section_boxes, home_cards, home_section, posts + post_categories/meta/comments, pages + pages_meta, knowledge_bank, article_cat.
- localization: languages (PK id), languages_relation, languages templates; translation content in languages-related tables.
- settings: general_settings (site-wide config incl APP URL, logo, maintenance flag), smtp_settings, api_settings, payment_settings, announcement_bar, currency tables.
- admin: admins (PK admin_id), admin_logs (PK id, FK admin_id), admin_notifications, admin_rights.
- coupons: coupons (PK id), coupons_used (PK id, FK coupon_id, order_id, buyer_id).
- referrals: referrals (PK referral_id, seller_id, referred_id), proposal_referrals (PK id, FK proposal_id, seller_id).
- security/logging: spam_words, notifications, my_buyers, my_sellers, expenses, reports.

## Recent Patch Tables
- password_resets (added 2026-02-14):
  - id (PK, auto inc), seller_id (int, idx), email, selector (unique), token_hash, expires_at, used_at nullable, created_at default current_timestamp.
- Homepage copy refresh script updates data rows in home_section, home_cards, section_boxes for default language id.

## Relationships (high level)
- sellers 1:N proposals, buyer_requests, orders (as buyer/seller), conversations, reviews.
- proposals 1:N proposal_packages, proposals_extras, proposal_modifications, proposal_faq, featured/top/recent associations.
- orders reference proposals and sellers; order_extras/orderIncludes/order_conversations/order_tips reference orders; revenues and payouts reference orders and sellers.
- Messages: inbox_sellers/inbox_messages link sender/receiver seller_ids; send_offers/messages_offers connect requests and proposals.
- Categories: categories_children links to categories; proposals and buyer_requests reference category/child ids.
- Languages: many content tables include language_id (home_section, home_cards, section_boxes, posts, pages, categories meta) and rely on languages table.

## Charset/Collation
- Default engine: InnoDB
- Default charset: utf8mb4
- Default collation: utf8mb4_unicode_ci

## Notes
- No destructive changes made; snapshot is descriptive only.
- Password reset flow depends on password_resets table and `includes/password_reset.php` helper.
- Payment/order tables expect NO_AUTO_VALUE_ON_ZERO and timezone UTC at import time (per dump header).

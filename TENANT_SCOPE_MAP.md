# TENANT_SCOPE_MAP (preliminary, 2026-02-14)

Legend: T=tenant-scoped, G=global, U=unknown/needs review

## Tenant-Scoped (T)
- sellers, seller_accounts, seller_payment_settings, seller_reviews, seller_languages, seller_skills, seller_levels (behavioral per seller though config is global), seller_type_status
- proposals, proposal_packages, proposals_extras, proposal_modifications, proposal_faq, proposal_referrals, featured_proposals, top_proposals, recent_proposals, proposal_packages/attributes, temp_extras, temp_orders
- buyer_requests, send_offers, messages_offers
- orders, order_extras, orderIncludes, order_tips, order_conversations
- revenues, payouts, withdrawals, sales, purchases, cart, cart_extras
- inbox_sellers, inbox_messages, archived_messages, starred_messages, unread_messages, hide_seller_messages, my_buyers, my_sellers
- notifications, admin_notifications (content references actors), seller_payment_settings
- favorites, wishlist-like tables (favorites)
- reports (user-originated), contact_support/contact_support_meta
- referral tables: referrals, proposal_referrals

## Global (G)
- general_settings, payment_settings, api_settings, smtp_settings, currency_converter_settings, currencies, site_currencies
- languages, languages_relation, language files
- categories, categories_children, cats_meta, child_cats_meta
- posts, post_categories, post_categories_meta, posts_meta, post_comments, article_cat, knowledge_bank
- pages, pages_meta, terms, footer_links, home_section, home_cards, section_boxes, slider, home_section_slider, ideas
- plugins, admin_rights, admin_logs, admins
- spam_words, expenses, app_info, announcement_bar, enquiry_types
- currency_converter_settings, api_settings (S3, etc.)
- Payment gateway order tables (global provider config scope): dusupay_orders (present); paypal_orders / mercadopago_orders / paystack_orders / stripe_orders (not in current dump; if introduced, treat as global).

## Unknown / Needs Review (U)
- coupons, coupons_used (likely tenant-specific to buyer; confirm scoping in app logic)
- packages: package_attributes (used by proposal packages; probably T)
- skills_relation (bridges skills to sellers; likely T)
- home_section language-specific rows (data-global but may vary by tenant in future)
- support_tickets, support_conversations (currently cross-tenant; confirm access control)
- app_info, jwplayer/api keys storage (global)
- any CodeIgniter-specific cache/log tables under apis/application (not migrated yet)

## Notes
- Classification is inference-only; no schema changes made.
- Multi-tenancy will require enforcing tenant_id scoping on T tables and read filters on G tables where customization is per tenant.

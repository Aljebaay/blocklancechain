# BACKEND ALIGNMENT PREP

## Overview
Documents the current Laravel backend architecture for the legacy-parity frontend layer, identifies inline DB queries in Blade templates that should be moved to controllers/services in Phase 2, and provides a migration roadmap.

---

## Current Architecture

### Controllers

| Controller | Purpose | Methods |
|-----------|---------|---------|
| `LegacyPageController` | Serves all 16 legacy-parity GET pages | `home`, `showLogin`, `showRegister`, `categoriesIndex`, `categoriesShow`, `search`, `blogIndex`, `blogPost`, `tagsShow`, `pageShow`, `proposalShow`, `userProfile` |
| `LegacyPostController` | Dispatches all form POST submissions | `dispatchRootPost`, `dispatchLoginPost` + private handlers: `handleLogin`, `handleRegister`, `handleForgot`, `handleBlogComment` |
| `LegacyAjaxController` | Handles AJAX filter endpoints | `searchLoad`, `categoryLoad`, `tagLoad`, `featuredLoad` |
| `AdminAuthController` | Admin authentication | `showLogin`, `login`, `logout` |
| `AuthController` | User authentication (future SPA) | `showLogin`, `login`, `logout`, `showRegister`, `register` |

### Services

| Service | Purpose | Key Methods |
|---------|---------|-------------|
| `LegacyDataService` | Loads all shared template data | `loadGlobals`, `loadHeaderData`, `loadFooterData`, `loadHomeData`, `loadAuthHomeData`, `getImageUrl`, `getImageUrl2`, `showPrice`, `dynamicUrl` |

### Middleware

| Middleware | Purpose |
|-----------|---------|
| `SellerAuthenticated` | Guards seller-only routes |
| `AdminAuthenticated` | Guards admin routes |
| `MaintenanceMode` | DB-driven maintenance mode |
| `UpdateSellerActivity` | Updates seller_activity timestamp |

---

## Inline DB Queries in Blade Templates (Phase 2 Refactor Targets)

These templates contain `DB::table()` / `DB::select()` queries directly in `@php` blocks. Phase 2 should move these to controllers or view composers.

### High Priority (multiple queries per page load)

| Template | Query Count | Tables Accessed | Refactor Target |
|----------|-------------|----------------|-----------------|
| `proposal-card.blade.php` | 7 | `proposals`, `sellers`, `seller_levels_meta`, `buyer_reviews`, `favorites`, `instant_deliveries`, `proposal_videosettings` | Create `ProposalCardService::buildCardData()` |
| `user-profile.blade.php` | 8+ | `sellers`, `seller_levels_meta`, `proposals`, `buyer_reviews`, `languages_relation`, `seller_languages`, `skills_relation`, `seller_skills`, `proposal_packages` | Move to `LegacyPageController::userProfile()` |
| `home-auth.blade.php` | 3 per request row | `send_offers`, `sellers` | Move buyer_requests processing to `LegacyDataService::loadAuthHomeData()` |
| `search-results.blade.php` | 5 per proposal | `proposals`, `sellers`, `seller_levels_meta`, `buyer_reviews`, `proposal_packages` | Move to `LegacyAjaxController` |
| `category-results.blade.php` | 5 per proposal | Same as search-results | Move to `LegacyAjaxController` |
| `tag-results.blade.php` | 5 per proposal | Same as search-results | Move to `LegacyAjaxController` |

### Medium Priority (few queries)

| Template | Query Count | Tables Accessed | Refactor Target |
|----------|-------------|----------------|-----------------|
| `proposal.blade.php` | 15+ | `proposals`, `sellers`, `categories`, `cats_meta`, `categories_children`, `child_cats_meta`, `delivery_times`, `proposals_extras`, `proposals_faq`, `proposal_packages`, `buyer_reviews`, `orders`, `related proposals` | Create `ProposalShowService` |
| `blog-single.blade.php` | 3 | `posts`, `post_comments`, `sellers` | Move to `LegacyPageController::blogPost()` |
| `blog-sidebar.blade.php` | 1 | `blog` | Move to `LegacyPageController` |

### Low Priority (already mostly in controller/service)

| Template | Query Count | Notes |
|----------|-------------|-------|
| `categories-show.blade.php` | 2 | Category/subcategory meta queries, could move to controller |
| `home-guest.blade.php` | 0 | All data loaded via `LegacyDataService` |
| `login.blade.php` | 0 | All data from flash/session |
| `admin-login.blade.php` | 0 | Standalone HTML |

---

## Session Keys (Legacy Parity)

### Authentication Sessions

| Key | Type | Set By | Used By |
|-----|------|--------|---------|
| `seller_user_name` | string | `LegacyPostController::handleLogin` | Header, mobile menu, proposal card, all auth checks |
| `seller_id` | int | `LegacyPostController::handleLogin` | Favorites, offers, buyer requests |
| `seller_email` | string | `LegacyPostController::handleLogin` | Profile, settings |
| `admin_email` | string | `AdminAuthController::login` | Admin panel access |
| `admin_id` | int | `AdminAuthController::login` | Admin panel |

### Feature Sessions

| Key | Type | Set By | Used By |
|-----|------|--------|---------|
| `siteLanguage` | int | `LegacyDataService::loadGlobals` | All pages (language ID) |
| `siteCurrency` | int | Currency converter | `showPrice()` |
| `conversionRate` | float | Currency converter | `showPrice()` |
| `search_query` | string | `LegacyPostController::dispatchRootPost` | Search page, search sidebar |
| `cat_id` | int | `LegacyPageController::categoriesShow` | Category AJAX filter |
| `cat_child_id` | int | `LegacyPageController::categoriesShow` | Category AJAX filter |
| `tag` | string | `LegacyPageController::tagsShow` | Tag AJAX filter |

### Flash Keys

| Key | Type | Used By |
|-----|------|---------|
| `login_errors` | array | Login modal validation errors |
| `login_warning` | string | Login SweetAlert (wrong creds/blocked) |
| `register_errors` | array | Register modal validation errors |
| `form_data` | array | Register form repopulation |
| `forgot_errors` | array | Forgot password validation |
| `forgot_success` | string | Forgot password success message |
| `admin_login_error` | string | Admin login error |
| `admin_login_success` | bool | Admin login success SweetAlert |

---

## Database Tables Used by Frontend (Quick Reference)

**Core (every page):** `general_settings`, `languages`, `currencies`, `announcement_bar`, `footer_links`, `pages`, `pages_meta`

**Auth pages:** `sellers`, `cart`, `seller_accounts`, `proposals`, `notifications`, `inbox`

**Homepage:** `home_section`, `home_section_slider`, `home_cards`, `categories`, `cats_meta`, `section_boxes`, `slider`, `top_proposals`, `buyer_requests`, `send_offers`, `orders`, `recent_proposals`

**Listings (search/category/tag):** `proposals`, `sellers`, `seller_levels_meta`, `buyer_reviews`, `proposal_packages`, `favorites`, `instant_deliveries`, `proposal_videosettings`, `categories_children`, `child_cats_meta`, `delivery_times`

**Blog:** `posts`, `blog`, `post_comments`

**Profile:** `sellers`, `proposals`, `buyer_reviews`, `languages_relation`, `seller_languages`, `skills_relation`, `seller_skills`, `seller_levels_meta`, `proposal_packages`

**Admin:** `admins`

---

## Phase 2 Migration Roadmap

### Step 1: Extract Blade Queries → Services
Move all `DB::table()` calls from Blade templates into dedicated service methods. Start with `proposal-card.blade.php` (highest impact — used on every listing page).

### Step 2: Add Eloquent Models
Replace raw `DB::table()` calls with Eloquent models. Priority order:
1. `Proposal` model (used everywhere)
2. `Seller` model (used everywhere)
3. `BuyerReview` model (ratings on every card)
4. `Category` / `CategoryChild` models
5. Remaining models as needed

### Step 3: Implement Caching
Add query caching for:
- `general_settings` (changes rarely, queried on every request)
- `languages` / language strings (changes rarely)
- `categories` (nav bar on every page)
- `footer_links` / `pages` (footer on every page)

### Step 4: Replace Legacy Auth with Laravel Auth
- Migrate session-based auth to Laravel's `Auth` facade
- Use `sellers` table as the `User` model
- Implement proper password hashing migration
- Add CSRF to all remaining forms

### Step 5: API Layer
- Add REST API endpoints for SPA migration
- Reuse services from Step 1
- Implement proper API authentication (Sanctum/Passport)

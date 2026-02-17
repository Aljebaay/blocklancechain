# FRONTEND DATA CONTRACTS

## Overview
Documents the data fields required by each page/component from the backend, defaults, ordering/pagination rules, empty/error states.

---

## Global Data (LegacyDataService::loadGlobals)

All pages receive these fields via the layout:

| Field | Type | Source Table | Default | Notes |
|-------|------|-------------|---------|-------|
| `site_url` | string | config `app.url` | `''` | Base URL for all links |
| `site_name` | string | `general_settings` | `'GigZone'` | Used in titles, footers |
| `site_desc` | string | `general_settings` | `''` | Meta description |
| `site_keywords` | string | `general_settings` | `''` | Meta keywords |
| `site_author` | string | `general_settings` | `''` | Meta author |
| `site_title` | string | `general_settings` | site_name | Page `<title>` |
| `site_color` | string | `general_settings` | `'#1DBF73'` | Primary brand color |
| `site_hover_color` | string | `general_settings` | `''` | Hover state color |
| `site_border_color` | string | `general_settings` | `''` | Border color |
| `site_copyright` | string | `general_settings` | `''` | Footer copyright text |
| `site_logo_type` | string | `general_settings` | `'text'` | `'text'` or `'image'` |
| `site_logo_text` | string | `general_settings` | `'GigZone'` | Text logo content |
| `site_logo_image` | string | `general_settings` | `''` | Image logo URL |
| `site_favicon` | string | `general_settings` | `''` | Favicon URL |
| `site_mobile_logo` | string | `general_settings` | `''` | Mobile logo URL |
| `enable_mobile_logo` | int | `general_settings` | `0` | Show mobile logo |
| `siteLanguage` | int | session / `languages` | `1` | Active language ID |
| `lang_dir` | string | `languages` | `'left'` | `'left'` or `'right'` (RTL) |
| `lang` | array | language file | `[]` | All UI strings |
| `s_currency` | string | `currencies` | `'$'` | Currency symbol |
| `s_currency_name` | string | `currencies` | `''` | Currency name |
| `currency_position` | string | `general_settings` | `'left'` | Symbol position |
| `enable_converter` | int | `currency_converter_settings` | `0` | Show currency converter |
| `language_switcher` | int | `general_settings` | `0` | Show language switcher |
| `enable_google_translate` | int | `general_settings` | `0` | Show Google Translate |
| `google_analytics` | string | `general_settings` | `''` | GA tracking ID |
| `knowledge_bank` | string | `general_settings` | `'no'` | `'yes'`/`'no'` |
| `google_app_link` | string | `general_settings` | `''` | Google Play link |
| `apple_app_link` | string | `general_settings` | `''` | App Store link |
| `enable_social_login` | int | `general_settings` | `0` | Enable social auth |
| `enable_referrals` | int/string | `general_settings` | `0` | Enable referral system |
| `deviceType` | string | computed | `'computer'` | Device detection |
| `row_general_settings` | object | `general_settings` | `null` | Full settings row |
| `floatRight` | string | computed | `'float-left'` | RTL helper class |
| `textRight` | string | computed | `'text-left'` | RTL helper class |

## Header Data (LegacyDataService::loadHeaderData)

| Field | Type | Source Table | Default | Notes |
|-------|------|-------------|---------|-------|
| `enable_bar` | string | `announcement_bar` | `'0'` | Show announcement |
| `bg_color` | string | `announcement_bar` | `''` | Bar background |
| `text_color` | string | `announcement_bar` | `''` | Bar text color |
| `bar_text` | string | `announcement_bar` | `''` | Bar HTML content |
| `bar_last_updated` | string | `announcement_bar` | `''` | For cookie tracking |
| `seller_id` | int | `sellers` | `0` | Auth user (if logged in) |
| `seller_email` | string | `sellers` | `''` | Auth user email |
| `seller_verification` | string | `sellers` | `'ok'` | Email verification status |
| `seller_image` | string | `sellers` | `''` | Auth user avatar URL |
| `count_cart` | int | `cart` | `0` | Cart item count |
| `current_balance` | float | `seller_accounts` | `0` | Account balance |
| `count_active_proposals` | int | `proposals` | `0` | Active gig count |

## Footer Data (LegacyDataService::loadFooterData)

| Field | Type | Source Table | Default | Notes |
|-------|------|-------------|---------|-------|
| `footer_categories` | Collection | `footer_links` | `[]` | Category footer links |
| `footer_about` | Collection | `footer_links` | `[]` | About section links |
| `footer_follow` | Collection | `footer_links` | `[]` | Social media links |
| `footer_pages` | Collection | `pages` + `pages_meta` | `[]` | CMS page links |
| `all_languages` | Collection | `languages` | `[]` | For language switcher |
| `site_currencies` | Collection | `site_currencies` + `currencies` | `[]` | For currency converter |

---

## Page-Specific Data Contracts

### Homepage (Guest) - `loadHomeData`

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `section_heading` | string | `home_section` | `''` | Hero heading text |
| `section_short_heading` | string | `home_section` | `''` | Hero subheading |
| `slides` | Collection | `home_section_slider` | `[]` | Carousel slides |
| `home_cards` | Collection | `home_cards` | `[]` | Marketplace cards |
| `categories_row1` | Collection | `categories` (featured) | `[]` | First row (4 cats) |
| `categories_row2` | Collection | `categories` (featured) | `[]` | Second row (4 cats) |
| `section_boxes_first` | Collection | `section_boxes` | `[]` | First trust box |
| `section_boxes_rest` | Collection | `section_boxes` | `[]` | Remaining trust boxes |
| `featured_proposals` | Collection | `proposals` (featured+active) | `[]` | Featured gigs (max 10) |
| `featured_proposals_count` | int | `proposals` | `0` | Total featured count |

**SQL Dependencies:** `2026-02-14_homepage_copy_refresh.sql` populates `home_section`, `home_cards`, `section_boxes`

### Homepage (Auth) - `loadAuthHomeData`

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `auth_slides` | Collection | `slider` | `[]` | Auth slider (different table!) |
| `auth_featured_proposals` | Collection | `proposals` | `[]` | Featured gigs (max 8) |
| `auth_top_proposals` | Collection | `proposals` + `top_proposals` | `[]` | Top rated (max 8) |
| `auth_random_proposals` | Collection | `proposals` | `[]` | Random active (max 8) |
| `auth_buyer_requests` | Collection | `buyer_requests` | `[]` | Recent requests (max 5) |
| `sidebar_buy_again` | array | `orders` + `proposals` | `[]` | Completed order proposal IDs |
| `sidebar_recently_viewed` | array | `recent_proposals` | `[]` | Recently viewed IDs (max 4) |
| `login_seller_name` | string | `sellers` | `''` | Logged-in user name |
| `login_user_name` | string | `sellers` | `''` | Logged-in username |
| `login_seller_offers` | string | `sellers` | `'0'` | Offer quota flag |

### Login Page

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `login_errors` | array (flash) | session | `null` | Validation error list |
| `login_warning` | string (flash) | session | `null` | SweetAlert warning (wrong creds) |
| `login_success` | string (flash) | session | `null` | SweetAlert success (login ok) |
| `enable_social_login` | string | `general_settings` | `'no'` | Show social buttons |

### Categories Show

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `page_cat_title` | string | `cats_meta` | `''` | Category title |
| `page_cat_desc` | string | `cats_meta` | `''` | Category description |
| `active_cat_id` | int | `categories` | `null` | Active parent cat |
| `active_child_id` | int | `categories_children` | `null` | Active child cat |
| `cat_url` | string | URL param | `''` | Category URL slug |
| `cat_child_url` | string | URL param | `null` | Child category slug |

**AJAX Endpoint:** `POST /category_load` with params: `zAction`, filter arrays
**Response:** HTML fragments for proposals grid and pagination

### Search

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `search_query` | string | session | `''` | Current search query |

**AJAX Endpoint:** `POST search_load` with params: `zAction`, filter arrays
**Response:** HTML fragments for proposals grid and pagination

### Blog Index

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| (queries inline in blade) | | `posts` / `blog` | | Posts with pagination |

### Blog Post

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `post_id` | string | URL param | `''` | Blog post ID |

### Tags

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `tag` | string | URL param | `''` | Tag name (dashes replaced with spaces) |

**AJAX Endpoint:** `POST /tag_load` with params: `zAction`, filter arrays
**Response:** HTML fragments for proposals grid and pagination

### Static Page

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `page_title` | string | `pages_meta` | `''` | Page title |
| `page_content` | string | `pages_meta` | `''` | Page HTML content |

### Proposal Detail

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `proposal_id` | int | `proposals` | `0` | Proposal ID |
| `proposal_title` | string | `proposals` | `''` | Title |
| `proposal_desc` | string | `proposals` | `''` | HTML description |
| `proposal_price` | float | `proposals` | `0` | Base price |
| `proposal_img1`-`img4` | string | `proposals` | `''` | Gallery images |
| `proposal_seller_id` | int | `proposals` | `0` | Seller ID |
| `proposal_cat_id` | int | `proposals` | `0` | Category ID |
| `proposal_cat_title` | string | `cats_meta` | `''` | Category name |
| `proposal_child_title` | string | `child_cats_meta` | `''` | Subcategory name |
| `delivery_proposal_title` | string | `delivery_times` | `''` | Delivery time label |
| `proposal_order_queue` | int | `orders` | `0` | Active orders |
| `proposal_rating` | int | computed | `0` | Average rating (1-5) |
| `count_reviews` | int | `buyer_reviews` | `0` | Total reviews |
| `level_title` | string | `seller_levels_meta` | `''` | Seller level name |
| `count_extras` | int | `proposals_extras` | `0` | Extra services count |
| `count_faq` | int | `proposals_faq` | `0` | FAQ items count |

### User Profile

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `profile_username` | string | URL param | `''` | Seller username |

### Admin Login

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `admin_login_error` | string (flash) | session | `null` | Error message |

---

## Proposal Card Component

Used across: Homepage, Search, Categories, Tags, User Profile

| Field | Type | Source | Default | Notes |
|-------|------|--------|---------|-------|
| `proposal` | object | passed | required | Full proposal row |
| `proposal.proposal_id` | int | `proposals` | | |
| `proposal.proposal_title` | string | `proposals` | | |
| `proposal.proposal_price` | float | `proposals` | | Falls back to Basic package |
| `proposal.proposal_img1` | string | `proposals` | | |
| `proposal.proposal_seller_id` | int | `proposals` | | |
| `proposal.proposal_rating` | int | `proposals` | | |
| `proposal.proposal_url` | string | `proposals` | | |
| `proposal.proposal_enable_referrals` | string | `proposals` | `'no'` | |
| Seller info | | `sellers` | | Queried inside partial |
| Reviews | | `buyer_reviews` | | Aggregated inside partial |
| Favorites | | `favorites` | | Checked inside partial |
| Instant delivery | | `instant_deliveries` | | Checked inside partial |
| Video settings | | `proposal_videosettings` | | Checked inside partial |

---

## Empty State Rules

| Page/Component | Empty State | Legacy Behavior |
|---------------|-------------|-----------------|
| Homepage featured proposals | `featured_proposals_count == 0` | No "View More" link shown |
| Auth home featured | Empty collection | Shows `<p>` with frown icon |
| Auth home top proposals | Empty collection | Shows `<p>` with frown icon |
| Auth home random proposals | `total_active == 0` | Shows `<p>` with frown icon |
| Auth sidebar buy again | No completed orders | Shows `<p>` with frown icon |
| Auth sidebar recently viewed | No recent proposals | Shows `<p>` with frown icon |
| Search results | No matches | Empty grid |
| Category results | No matches | Empty grid |
| Tag results | No matches | Empty grid |
| Blog posts | No posts | Empty list |
| User profile proposals | No active proposals | "No proposals" message |

---

## Pagination Rules

| Page | Items per page | Legacy implementation |
|------|---------------|---------------------|
| Search | AJAX loaded | Server-rendered pagination links |
| Categories | AJAX loaded | Server-rendered pagination links |
| Tags | AJAX loaded | Server-rendered pagination links |
| Blog | 10 posts | PHP pagination with page param |
| Homepage featured | Max 10 | No pagination |
| Auth home sections | Max 8 each | No pagination |

---

## Error/Flash Message Rules

| Context | Legacy Flash Key | Laravel Flash Key | Display Method |
|---------|-----------------|-------------------|----------------|
| Login (validation) | `login2_errors` | `login_errors` | Alert danger list |
| Login (wrong creds) | inline swal | `login_warning` | SweetAlert warning |
| Login (blocked) | inline swal | `login_warning` | SweetAlert warning |
| Login (success) | inline swal | `login_success` | SweetAlert success + redirect |
| Register (validation) | `register_errors` | `register_errors` | Alert danger list in modal |
| Forgot (validation) | inline | `forgot_errors` | Alert danger |
| Admin login | session var | `admin_login_error` | Alert danger |
| Not available | `?not_available` | `?not_available` | Alert danger text |
| Email verification | `$seller_verification != 'ok'` | Same | Alert warning with resend button |

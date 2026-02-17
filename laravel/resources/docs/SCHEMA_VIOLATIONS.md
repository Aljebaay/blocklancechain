# SCHEMA VIOLATIONS LOG

## Overview
Tracks all database column reference violations found during parity checklist execution.
Each violation must be fixed immediately — no invented columns allowed.

---

## Violations Found

### 2026-02-16 — Initial Schema Verification

#### proposal-card.blade.php (C9)
- **Table `proposal_videosettings`**: Not in base install SQL (`gig-zone.sql`). Created dynamically by `admin/activate_plugin.php` when video plugin is enabled.
  - Columns used: `proposal_id`, `enable` — **VALID** (matches legacy usage pattern)
  - **Status**: ADDED to `database_schema_map.json` as plugin table
  - **No fix needed** — query is guarded with null check

#### proposal.blade.php (PG-12)
1. Column `buyer_id` referenced at line 156 as `$review->buyer_id`
   - Schema column: `review_buyer_id`
   - **FIXED**: Changed to `$review->review_buyer_id`

2. Column `id` used in `orderBy('id', 'DESC')` at line 152
   - Schema primary key: `review_id`
   - **FIXED**: Changed to `orderBy('review_id', 'DESC')`

3. Column `date` referenced at line 166 as `$review->date`
   - Schema column: `review_date`
   - **FIXED**: Changed to `$review->review_date`

#### user-profile.blade.php (PG-13)
1. Column `seller_member_since` referenced at line 39
   - Schema column: `seller_register_date`
   - **FIXED**: Changed to `$seller->seller_register_date`

2. Column `seller_cover` referenced at line 41
   - Schema column: `seller_cover_image`
   - **FIXED**: Changed to `$seller->seller_cover_image`

---

## Resolution Summary

| File | Column Used | Correct Column | Status |
|------|------------|----------------|--------|
| `proposal.blade.php:156` | `buyer_id` | `review_buyer_id` | FIXED |
| `proposal.blade.php:152` | `id` (orderBy) | `review_id` | FIXED |
| `proposal.blade.php:166` | `date` | `review_date` | FIXED |
| `user-profile.blade.php:39` | `seller_member_since` | `seller_register_date` | FIXED |
| `user-profile.blade.php:41` | `seller_cover` | `seller_cover_image` | FIXED |

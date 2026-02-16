# PARITY MATRIX

> Last updated: 2026-02-15T22:30:00.000Z
> Harness: `scripts/parity/run_parity.mjs` → `report/parity/index.html`
> Threshold: PASS ≤ 1% (target ≤ 0.5%)
> **Overall: 9/9 PASS (100%)**

## Phase 2 — All Tested Routes (ALL PASS)

| ID | Path | Method | Auth | Legacy Status | Laravel Status | Visual Diff % | Parity Status | Notes |
|----|------|--------|------|---------------|----------------|---------------|---------------|-------|
| home-guest | `/` | GET | guest | 200 | 200 | 0.01% | **PASS** | Blade SSR + legacy asset junctions |
| login | `/login` | GET | guest | 200 | 200 | 0.03% | **PASS** | Full modals, external stylesheet, knowledge bank hidden |
| register | `/register` | GET | guest | 200 | 200 | 0.01% | **PASS** | Legacy returns JS redirect to `index?not_available`; Laravel matches |
| categories-index | `/categories` | GET | guest | 302→200 | 302→200 | 0.01% | **PASS** | Both redirect to home page |
| search | `/search` | GET | guest | 200 | 200 | 0.48% | **PASS** | Full sidebar with filters, initial results rendered server-side |
| blog-index | `/blog` | GET | guest | 200 | 200 | 0.03% | **PASS** | Blog posts with sidebar categories |
| categories-show | `/categories/graphics-design` | GET | guest | 200 | 200 | 0.33% | **PASS** | Category page with sidebar filters, proposals, JS |
| blog-post | `/blog/1` | GET | guest | 200 | 200 | 0.02% | **PASS** | Single blog post with comments and sidebar |
| tags-show | `/tags/logo` | GET | guest | 200 | 200 | 0.35% | **PASS** | Tag page with sidebar filters and tagged proposals |

## Phase 2C — Additional Routes (Implemented, pending parity test with real data)

| ID | Path | Method | Auth | Parity Status | Notes |
|----|------|--------|------|---------------|-------|
| categories-child | `/categories/{url}/{child}` | GET | guest | IMPLEMENTED | Shares template with categories-show |
| proposal-show | `/proposals/{user}/{slug}` | GET | guest | IMPLEMENTED | Full proposal page with reviews, related, sidebar |
| pages | `/pages/{slug}` | GET | guest | IMPLEMENTED | CMS page with breadcrumb and content |
| user-profile | `/{username}` | GET | guest | IMPLEMENTED | User profile with proposals, skills, languages |
| index-alias | `/index` | GET | guest | IMPLEMENTED | Alias for home page (legacy compatibility) |

## Phase 2D — POST Flows (PENDING)

| ID | Path | Method | Auth | Parity Status | Notes |
|----|------|--------|------|---------------|-------|
| login-post | `/login` | POST | guest | PENDING | Session keys, redirect behavior, error messages |
| register-post | `/register` | POST | guest | PENDING | Validation, messages, redirects |
| search-post | `/search` | POST | guest | PARTIAL | Query stored in session |

## Legend
- **PASS**: Status matches, visual diff < 1%
- **CLOSE**: Status matches, visual diff 1-5%
- **FAIL**: Status mismatch OR visual diff > 5%
- **IMPLEMENTED**: Route and template created, pending automated parity test
- **PENDING**: Waiting for prerequisite to be completed
- **PARTIAL**: Partially implemented

## Screenshots
See `report/parity/<route-id>/` for full screenshots and diff images.

## Summary Statistics
- **Total tested routes**: 9
- **PASS**: 9 (100%)
- **FAIL**: 0
- **Average pixel diff**: 0.14%
- **Max pixel diff**: 0.48% (search page)
- **Min pixel diff**: 0.01% (home, register, categories-index)

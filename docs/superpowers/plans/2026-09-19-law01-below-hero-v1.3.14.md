# Law 01 Below-Hero Parity 1.3.14 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Finish the remaining Theme-owned Law01 presentation parity below the already-approved Hero and produce a verified 1.3.14 installable candidate.

**Architecture:** Inherit the verified 1.3.13 candidate. Change only Law01 presentation templates/CSS and source-backed WordPress consumers. Keep domain data with its owners and fail soft whenever the screenshot contains client facts that the Theme cannot legitimately source.

**Tech Stack:** WordPress classic PHP theme + theme.json, PHP 8.1+, WordPress 6.9+, CSS, Node/Playwright browser QA, GitHub Actions, deterministic package builder.

**Spec:** `docs/superpowers/specs/2026-09-19-law01-below-hero-parity.md`

## Global Constraints

- Hero and Header are frozen except for regression fixes proven by tests.
- SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.
- No fabricated people, business metrics, contact data, social data or client claims.
- No semantic service reordering by title/slug.
- No private provider storage reads.
- RED -> expected failure -> minimal GREEN -> full regression for behavior changes.
- WP >= 6.9 and PHP >= 8.1.
- No deploy/merge/release publication.

## Review Focus

- Empty/missing menu or Posts Page data must collapse without dead links.
- Service icon presentation must not change source order or route by title/slug.
- About-only mode must remain coherent and accessible when Team is absent.
- Footer social/contact columns must not duplicate source menus.
- Tablet/mobile layouts must not create horizontal overflow.

---

### Task 1: Trust strip reference parity

**Files:**
- Modify: `template-parts/homepage/law-01/hero.php`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify: `tests/offline/homepage-law-01-visual-parity-contract.php`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes existing four trust messages.
- Produces presentation-only shield / people / scales / handshake iconography and reference strip rhythm.

- [ ] RED: assert the four presentation icons and reference strip styling.
- [ ] Verify expected failure on current candidate.
- [ ] GREEN: replace only decorative icon SVGs/CSS; preserve messages and Hero.
- [ ] Run Law01 offline + browser regression.
- [ ] Commit.

### Task 2: Services reference closure

**Files:**
- Modify: `template-parts/homepage/law-01/services.php`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify: `tests/offline/homepage-law-01-visual-parity-contract.php`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes mapped Services Page + up to six direct child Pages in source order.
- Produces compact equal-height six-card reference layout.

- [ ] RED: assert preserved source order/no title routing, reference heading text, card CTA and icon ring.
- [ ] Verify expected failure for the heading/reference delta.
- [ ] GREEN: make the smallest markup/CSS refinement without changing the WordPress query/order.
- [ ] Run offline + Law01 browser regression.
- [ ] Commit.

### Task 3: About + Team reference closure

**Files:**
- Modify: `template-parts/homepage/law-01/profile.php`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify: `tests/offline/homepage-law-01-visual-parity-contract.php`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes mapped About Page + existing legitimate Team presentation inputs.
- Produces reference split when Team exists, About-only fail-soft otherwise, plus decorative legal watermark.

- [ ] RED: assert decorative watermark hook and retained no-stats/no-fake-team behavior.
- [ ] Verify expected failure.
- [ ] GREEN: add presentation-only watermark hook/CSS; retain Team and statistics boundaries.
- [ ] Run offline + browser regression.
- [ ] Commit.

### Task 4: Latest posts reference closure

**Files:**
- Modify: `template-parts/homepage/law-01/latest.php`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify: `tests/offline/homepage-law-01-visual-parity-contract.php`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes `homepage_latest_posts()` plus WordPress `page_for_posts`.
- Produces optional source-backed “Xem tất cả bài viết” link and clickable media.

- [ ] RED: assert Posts Page fail-soft and linked featured media.
- [ ] Verify expected failure.
- [ ] GREEN: add Posts Page lookup/link and media anchor; no fabricated archive URL.
- [ ] Run offline + browser regression.
- [ ] Commit.

### Task 5: Footer reference closure

**Files:**
- Modify: `template-parts/footer/site-footer.php`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify: `tests/offline/homepage-law-01-visual-parity-contract.php`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes existing `footer_context()` menu projections.
- Produces Law01 visual order Identity -> Contact -> Quick links -> Social and screenshot-aligned headings only when columns exist.

- [ ] RED: assert Law01 headings/order hooks and no placeholder columns.
- [ ] Verify expected failure.
- [ ] GREEN: conditional presentation labels + CSS order only.
- [ ] Run Footer + Law01 browser/a11y regressions.
- [ ] Commit.

### Task 6: Full exact-head regression and version promotion

**Files:**
- Modify version/release guards only after Tasks 1-5 are green.
- Add evidence document for this slice.

- [ ] Run full triggered workflow matrix on exact presentation head.
- [ ] Resolve any real regression via RED -> GREEN.
- [ ] Promote 1.3.13 -> 1.3.14 metadata and package guards with RED first.
- [ ] Run full exact 1.3.14 workflow matrix.
- [ ] Build deterministic installable package twice and verify identity/lint/runtime.
- [ ] Keep PR draft; do not deploy/merge/release.

# Law 01 Completion and 1.3.13 Delivery Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete the remaining Theme-owned Law 01 homepage presentation gaps by inheritance, preserve source ownership, verify the exact candidate, then produce an installable AZnet Theme 1.3.13 ZIP only after all safe slices are green.

**Architecture:** Keep the existing Law 01 Homepage Composer and WordPress-native content map. Add only small presentation consumers/markup needed to match the approved Tâm Đức reference more closely; missing authoritative Team/business data stays fail-soft/blocked. Version/package work happens only after presentation and regression gates are green.

**Tech Stack:** WordPress classic PHP theme + theme.json, PHP 8.1+, WordPress 6.9+, CSS, Node/Playwright browser QA, GitHub Actions, deterministic Python ZIP builder.

**Spec:** `docs/superpowers/specs/2026-09-19-law01-lstamduchn-inheritance-parity.md`

## Global Constraints

- SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.
- No direct reads of RootProfile/private option/meta/CPT/table storage.
- No inferred authoritative identity/routing from slug/title/Page ID/URL/heuristics.
- No fabricated Team people, credentials, business metrics, contact details, social profiles, testimonials, or customer statistics.
- Existing PASS behavior is inherited unless fresh evidence invalidates it.
- Feature/bugfix code uses RED -> fail for expected reason -> minimal GREEN -> regression.
- WordPress 6.9+ and PHP 8.1+ remain the support floor.
- Naming remains `AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.
- No new bundler/build stack.
- Release/deploy/takeover are separate gates; this plan may build a candidate package but must not deploy it.

## Review Focus

- Law 01 without a mapped Contact Page must omit the new consultation CTA cleanly rather than emit a dead link.
- Law 01 without Team members must not leave a misleading empty team panel or infer people from WordPress users/authors.
- Non-Law01 pages must retain the existing generic Header/Footer behavior.
- Mobile navigation must remain reachable and avoid duplicate desktop/mobile consultation actions.
- Candidate packaging must contain exact production bytes only and keep version metadata synchronized.

---

### Task 1: Law 01 Header brand + source-backed consultation CTA

**Files:**
- Modify: `template-parts/header/brand.php`
- Modify: `template-parts/header/site-header.php`
- Modify: `template-parts/header/mobile-panel.php`
- Create: `template-parts/header/law01-consultation.php`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify: `tests/offline/homepage-law-01-visual-parity-contract.php`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes: existing `homepage_composer_active()`, `homepage_law01_variant()`, `setting('homepage_contact_page')`, `homepage_page_reference()`.
- Produces: `.aznet-theme-site-header__brand-copy`, `.aznet-theme-site-header__brand-kicker`, `.aznet-theme-site-header__consultation`.

- [ ] Step 1: RED — assert Law01 brand supports a second presentation line and Header renders a consultation action from the mapped Contact Page, while generic Header keeps utility navigation.
- [ ] Step 2: Run the targeted offline contract and confirm failure is caused by missing Law01 consultation/brand markup.
- [ ] Step 3: GREEN — add the smallest fail-soft Law01-only markup; keep utility menu as the Hero quick-contact source and generic Header behavior unchanged.
- [ ] Step 4: Run targeted contract plus Homepage Law01 browser workflow; fix only regressions proven by fresh output.
- [ ] Step 5: Commit bounded Task 1.

### Task 2: Profile/Team fail-soft composition

**Files:**
- Modify: `template-parts/homepage/law-01/profile.php`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify: `tests/offline/homepage-law-01-visual-parity-contract.php`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes: `homepage_direct_published_children($team_id, 4)`.
- Produces: a two-column About+Team band only when legitimate mapped member inputs exist; otherwise an About-only composition with no inferred people.

- [ ] Step 1: RED — assert zero members removes the empty Team panel and gives About a full-width fail-soft state; populated mapped children still render up to four cards.
- [ ] Step 2: Run targeted test and confirm the empty-team assertion fails on current code.
- [ ] Step 3: GREEN — add a state class/conditional markup only; do not synthesize member data.
- [ ] Step 4: Run targeted contract + browser regression at desktop/tablet/mobile.
- [ ] Step 5: Commit bounded Task 2.

### Task 3: Law 01 Footer reference composition

**Files:**
- Modify: `template-parts/footer/site-footer.php`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify: `tests/offline/homepage-law-01-visual-parity-contract.php`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes: existing WordPress footer/contact/social/policy menu projections from `footer_context()`.
- Produces: `.aznet-theme-site-footer--law01-burgundy-gold` modifier with 3-column source-backed fallback and 4-column layout when social data exists.

- [ ] Step 1: RED — assert the Law01 footer modifier exists and empty menu projections collapse without placeholders.
- [ ] Step 2: Run targeted test and confirm failure is specific to the missing Law01 footer state.
- [ ] Step 3: GREEN — add modifier class and CSS only; do not create footer data in Theme.
- [ ] Step 4: Run Header/Footer + Law01 browser/a11y regressions.
- [ ] Step 5: Commit bounded Task 3.

### Task 4: Reference parity regression closure

**Files:**
- Modify only if a failing regression proves a necessary production change.
- Add evidence: `docs/evidence/LAW01_LSTAMDUCHN_COMPLETION_20260919.md`

**Interfaces:**
- Consumes: Tasks 1-3 exact branch head.
- Produces: fresh L1/L2/L4 evidence, plus explicit BLOCKED state for RootProfile Team authority and unsupported business metrics.

- [ ] Step 1: Run all triggered PR workflows and inspect any failure at the shallowest reproducible layer.
- [ ] Step 2: For each real regression, write/extend a failing regression assertion before fixing.
- [ ] Step 3: Re-run exact branch head until the complete triggered workflow set is green.
- [ ] Step 4: Record evidence, rollback, blocked external data, and the exact next version-promotion action.
- [ ] Step 5: Commit evidence.

### Task 5: Promote candidate metadata to 1.3.13

**Files:**
- Modify: `style.css`
- Modify: `functions.php`
- Modify: `tests/offline/y5-client-delivery-release-contract.php`
- Modify: `.github/workflows/y5-client-delivery-release.yml`
- Modify: `.github/workflows/x6-cross-surface-release.yml`
- Add evidence: `docs/evidence/V1_3_13_CLIENT_DELIVERY_20260919.md`

**Interfaces:**
- Consumes: exact green Task 4 code bytes.
- Produces: synchronized Theme metadata `1.3.13` and release/package guards accepting only the approved `1.3.12 -> 1.3.13` promotion boundary.

- [ ] Step 1: RED — change the release contract expectation to exactly 1.3.13 and run it against 1.3.12 metadata.
- [ ] Step 2: Confirm expected version mismatch failure.
- [ ] Step 3: GREEN — atomically promote `style.css` + `AZNET_THEME_VERSION`, update only required package/release guards.
- [ ] Step 4: Run version/release contracts and full PR regression.
- [ ] Step 5: Commit bounded version promotion.

### Task 6: Deterministic installable package and final handoff

**Files:**
- No production change unless packaging verification proves one.
- Candidate output: `aznet-theme-1.3.13.zip`

**Interfaces:**
- Consumes: exact Task 5 green candidate.
- Produces: deterministic installable ZIP, SHA-256, file count, PHP lint evidence, source/package identity evidence.

- [ ] Step 1: Build the package twice with `scripts/build-release-package.py --version 1.3.13`.
- [ ] Step 2: Verify byte-identical ZIPs and record SHA-256.
- [ ] Step 3: Verify required production paths, excluded development paths, PHP lint, and exact source/package comparison.
- [ ] Step 4: Run final whole-branch verification/review and resolve Critical/Important findings via RED -> GREEN.
- [ ] Step 5: Only after every completion gate above is green, materialize the final ZIP for the user. Do not deploy or publish the Theme.

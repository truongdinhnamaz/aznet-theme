# Law-site Production Readiness Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move AZnet Theme from the verified v1.1.0 technical candidate to a production-ready editorial/law-site pilot without changing Theme ownership or importing legal/domain semantics into the Theme.

**Architecture:** Keep AZnet Theme as presentation owner. WordPress remains owner of Posts/Pages/Menu/Media/query/editorial state; RootProfile/ConvertFlow/WooCommerce remain optional public-contract providers. The first production hardening slice improves generic editorial presentation, followed by real-site pilot verification and only then publication/deployment gates.

**Tech Stack:** WordPress 6.9+ hybrid PHP Theme + `theme.json`, PHP 8.1+, native WordPress template hierarchy/APIs, existing AZnet Theme semantic tokens/settings, GitHub Actions browser/runtime/package verification.

**Spec:** `docs/evidence/LAW_SITE_PRODUCTION_READINESS_AUDIT_20260908.md`

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Theme owns presentation only; WordPress/domain owners keep authoritative state.
- No private provider API/storage reads, no authoritative URL/slug/Page-ID heuristics.
- No Theme-generated SEO title/meta/canonical/schema/OG duplication; Rank Math or other SEO owner remains independent.
- No related-post ranking/query engine inside Theme.
- Article/listing assets must be surface-aware.
- Production feature/bugfix work uses RED -> intended failure -> minimal GREEN -> regression.
- Do not tag/GitHub Release/deploy without explicit owner approval.

---

### Task 1: Close R6 source state

**Files:**
- Modify: `docs/source/AZT-03-baseline-provenance.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Evidence: `docs/evidence/LAW_SITE_PRODUCTION_READINESS_AUDIT_20260908.md`

**Interfaces:**
- Consumes: canonical `main@4673202f67068294fe92d49d1ed66e371d0ecbb3`, Theme metadata `1.1.0`, final v1.1 candidate SHA `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.
- Produces: authoritative current state with R6 technical PASS and publication/deployment still gated.

- [ ] **Step 1: Update current-state source facts only**

Record canonical main/tree, Theme `1.1.0`, R6 PASS, deterministic candidate identity/SHA, no GitHub Release/tag claim, and remove stale wording that R6 is still exact next.

- [ ] **Step 2: Verify source-only diff**

Expected changed paths: the four `docs/source/**` files above plus evidence/plan files only; no PHP/CSS/JS/test/workflow change.

- [ ] **Step 3: Open source-state PR and stop at owner merge gate**

Do not merge without product-owner approval because AZT-03/AZT-04/EXEC-MAP are authoritative source/state documents.

---

### Task 2: Pilot identity cleanup

**Files:**
- No production Theme code change.
- Evidence: `docs/evidence/PILOT_SYSTEM_HEALTH_20260908.md`

**Interfaces:**
- Consumes: authenticated pilot System Health evidence showing active Theme `1.1.0`.
- Produces: one active canonical AZnet Theme install, inactive legacy duplicate folders removed only after verification.

- [ ] **Step 1: Smoke active frontend before destructive cleanup**

Check homepage, one Page and one Post while `1.1.0` is active. Confirm no obvious fatal/blank page and primary navigation works.

- [ ] **Step 2: Inspect inactive AZnet Theme cards**

Use WordPress Appearance -> Themes -> Theme Details. Delete only inactive legacy versions. If any inactive card also reports `1.1.0`, stop and capture its details before deletion.

- [ ] **Step 3: Verify one AZnet Theme remains active**

Return to Appearance -> Themes and confirm only the intended active AZnet Theme remains. Re-open System Health and confirm version `1.1.0` and primary menu continuity.

---

### Task 3: Editorial single-post contract RED

**Files:**
- Create: `tests/offline/editorial-single-post-contract.php`
- Modify later: `single.php`
- Create later: focused `template-parts/content/*` parts only when required by the contract.

**Interfaces:**
- Consumes: native WordPress Post/author/taxonomy/featured-image APIs and existing Theme content shell/tokens.
- Produces: deterministic static/contract requirements for article presentation without SEO/domain takeover.

- [ ] **Step 1: Write failing contract**

Require the single-post implementation to expose native author, published date, modified date when changed, categories/tags, featured image when present, content pagination and previous/next navigation; reject `WP_Query`, `get_posts`, direct option/meta/table access for related-content inference, and reject Theme-owned SEO meta/schema output.

- [ ] **Step 2: Run contract and confirm intended RED**

Expected failure: current `single.php` only renders title/content and lacks the required publication presentation.

---

### Task 4: Editorial single-post minimal GREEN

**Files:**
- Modify: `single.php`
- Create: `template-parts/content/content-single.php`
- Create: `template-parts/content/meta.php`
- Create: `template-parts/content/author.php`
- Create: `assets/css/components/article.css`
- Modify: `inc/theme/assets.php`

**Interfaces:**
- Consumes: native WordPress author/date/taxonomy/media/navigation APIs; existing RootProfile public presentation consumer only if already available without new contract.
- Produces: generic high-trust editorial Post presentation and singular-post scoped CSS.

- [ ] **Step 1: Implement semantic article composition**

Move single Post composition into `content-single.php`; render H1, native publication meta, optional featured image, content, pagination, taxonomies, post navigation and bounded author fallback.

- [ ] **Step 2: Keep RootProfile enhancement fail-soft**

Do not reconstruct identity. If the existing public RootProfile presentation path can enhance author presentation without new contract, use only that path; otherwise render WordPress-native author fallback.

- [ ] **Step 3: Add singular-post scoped CSS**

Style long-form reading measure, headings, lists, blockquotes, tables, media and focus states using existing semantic tokens. Enqueue only on `is_singular( 'post' )`.

- [ ] **Step 4: Run contract and retained static regression**

Expected: editorial contract GREEN; PHP lint and existing ownership/private-storage guards remain GREEN.

---

### Task 5: Editorial listing/search RED -> GREEN

**Files:**
- Create: `tests/offline/editorial-listing-contract.php`
- Modify: `template-parts/content/card.php`
- Modify only if required: `archive.php`, `search.php`, relevant scoped CSS.

**Interfaces:**
- Consumes: existing WordPress main query and native featured image/date/taxonomy APIs.
- Produces: readable scan cards for archives/search without replacing query ownership.

- [ ] **Step 1: Write failing card contract**

Require resilient long-title markup, optional native thumbnail/date presentation and excerpt, while rejecting custom query replacement.

- [ ] **Step 2: Confirm RED on current title+excerpt card**

- [ ] **Step 3: Implement minimal GREEN**

Add presentation-only thumbnail/date metadata and responsive long-title behavior without changing the main query.

- [ ] **Step 4: Run retained archive/search regression**

---

### Task 6: Browser/editorial quality matrix

**Files:**
- Create: a focused editorial browser workflow/harness following existing R3/R4/R5 browser patterns.

**Interfaces:**
- Consumes: final Task 4-5 Theme bytes.
- Produces: L3/L4 evidence for generic editorial surfaces.

- [ ] **Step 1: Build fixtures**

Include Vietnamese diacritics, very long legal title, long citations, nested headings, lists, blockquote, wide table, images and taxonomy labels.

- [ ] **Step 2: Run WordPress support-floor browser matrix**

Viewports: 1440, 1024, 390, 320. Routes: single Post, archive, search, no-results. Assert no horizontal overflow, usable table/media behavior, keyboard/focus, landmarks/headings, blocking axe critical/serious = 0 within Theme control, no browser/PHP error markers.

- [ ] **Step 3: Preserve Rank Math independence**

With Rank Math present in a compatibility run, verify Theme does not duplicate canonical/meta/schema ownership.

---

### Task 7: Real pilot law-site QA

**Files:**
- Evidence only unless a reproducible Theme-owned defect is found.

**Interfaces:**
- Consumes: active pilot stack and final candidate bytes.
- Produces: site-specific L3-L5 evidence; any failure is reduced to the shallowest reproducible Theme regression before code changes.

- [ ] **Step 1: Smoke actual pilot routes**

Homepage, representative long Post, Page, archive/category, search/no-results, 404, RootProfile surfaces that are actually public on the site.

- [ ] **Step 2: Check real environment**

Current observed pilot is WordPress 7.1 / PHP 8.4.24. Treat this as a current-stack pilot, not a replacement for support-floor 6.9/8.1 evidence.

- [ ] **Step 3: Record optional-provider status accurately**

Woo absent is not a blocker. RootProfile v1/v2 present but current-surface absent means no takeover claim. ConvertFlow remains unknown unless a public Theme-consumable capability becomes available.

---

### Task 8: Final candidate and release gate

**Files:**
- Reuse existing deterministic package builder/workflows.
- Evidence checkpoint for final editorial candidate.

**Interfaces:**
- Consumes: exact canonical main after approved production-hardening merges.
- Produces: deterministic installable candidate, SHA, file count and rollback reference.

- [ ] **Step 1: Run exact-main verification**

- [ ] **Step 2: Build candidate twice and require byte identity**

- [ ] **Step 3: Unzip/exact-compare/lint/clean-WordPress activation smoke**

- [ ] **Step 4: Stop before tag/GitHub Release/deployment**

Request explicit owner approval for publication and a separate explicit approval for production deployment.

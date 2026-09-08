# AZnet Theme — Law-site Production Readiness Audit

**Date:** 2026-09-08  
**Canonical repository:** `truongdinhnamaz/aznet-theme`  
**Audited live main:** `4673202f67068294fe92d49d1ed66e371d0ecbb3`  
**Live repo tree:** `dca0e3719063cdee22e3e535e16c61b24a8a6046`  
**Theme metadata:** `1.1.0`

## 1. Purpose

Assess whether the current AZnet Theme sources and implementation are sufficient to guide a production launch on a professional Vietnamese law/editorial website, without changing Theme ownership or importing domain logic from RootProfile, ConvertFlow, WooCommerce, Rank Math, or any other provider.

This is evidence/audit, not an authoritative product or architecture decision. AZT-05/00/01/02/03/04 remain authoritative by subject.

## 2. What is already strong

The source set has a coherent product constitution and ownership model:

- Theme is independently useful on clean WordPress.
- WordPress owns Post/Page/Menu/Media/query/editorial lifecycle.
- Theme owns presentation, design tokens, shell, generic templates, responsive/a11y behavior and its own presentation settings.
- Optional provider integration is public/versioned/capability-detected and fail-soft.
- Release quality is layered; PR-head evidence does not replace exact-main/package evidence.
- Deterministic packaging uses the canonical top-level directory `aznet-theme/`.

R0-R6 implementation has reached a technically strong v1.1 candidate. Final exact-main verification and the manually dispatched v1.1 release-candidate package both succeeded on `main@4673202f...`; the candidate package is `aznet-theme-1.1.0.zip` with SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.

## 3. Source-state drift that must be closed

The authoritative current-state documents are stale relative to live GitHub:

- AZT-03 v0.22 still records `main@484e896...`, Theme `1.0.0`, and R6 as exact next.
- AZT-04 v0.26 still records R6 as READY / EXACT NEXT and metadata `1.0.0`.
- AZT-EXEC-MAP v0.18 still records R6 as the only remaining v1.1 stream.
- SOURCE_MANIFEST still represents the R5 baseline and `1.0.0`.

Live GitHub has already completed R6 infrastructure, exact-main verification, workflow retirement, explicit owner-approved metadata promotion to `1.1.0`, and a fresh `1.1.0` release-candidate package. No tag/GitHub Release has been published yet.

**Classification:** L0 SOURCE/STATE DRIFT. This does not invalidate the verified implementation bytes, but it is not acceptable as the guiding current-state source for the next development/deployment slice.

## 4. Existing-install identity issue on the pilot website

Observed WordPress admin evidence shows multiple installed themes with the same display name `AZnet Theme`. The canonical v1.1 package itself has one top-level folder `aznet-theme/`, so multiple cards imply legacy installs under distinct stylesheet/folder identities.

This is a deployment/migration issue, not a reason to make Theme code delete other themes.

Safe rule for the pilot site:

1. prove the currently active Theme version through `AZnet Theme -> System Health`;
2. if active version is `1.1.0`, smoke the frontend and keep the canonical active install;
3. remove inactive legacy AZnet Theme directories only through WordPress core UI after rollback/backup confidence;
4. if active version is still `1.0.0`, do not delete anything; first capture the exact legacy stylesheet slug and perform a controlled cutover to canonical `aznet-theme`;
5. do not add automatic theme-deletion logic to the Theme Control Center.

**Current status:** BLOCKED on one site datum — active Theme version/identity evidence from the pilot WordPress admin.

## 5. Editorial/law-site presentation gap

The generic template milestone is technically valid, but the current single-post presentation is too minimal for a high-trust legal/editorial production site.

Current `single.php` renders the Post title and content only. It does not provide a polished Theme-owned presentation for native publication metadata such as author, published/modified date, categories/tags, featured media, post navigation, or a bounded author presentation fallback.

The generic listing card currently renders title + excerpt only. This is functional but does not yet provide a strong professional editorial scan experience for dense legal content.

This is a concrete presentation-quality invalidation for the pilot use case, not a transfer of legal/business semantics into the Theme. WordPress must remain owner of Post/taxonomy/author data, and RootProfile may enhance identity presentation only through its public contract.

## 6. Recommended production-readiness slices

### P0 — Source closure

- Reconcile AZT-03, AZT-04, AZT-EXEC-MAP and SOURCE_MANIFEST to live `main@4673202f...`, Theme `1.1.0`, R6 technical PASS and publication pending.
- Preserve AZT-05/01/02 unless a real rule/architecture change is approved.
- Record legacy stylesheet migration as a deployment concern, not automatic Theme ownership.

**Gate:** authoritative source merge approval.

### P1 — Pilot identity cleanup

- Prove active version/stylesheet on the pilot site.
- If canonical `1.1.0` is active, smoke and delete only inactive legacy duplicates through WordPress core UI.
- If legacy identity is active, controlled cutover to canonical `aznet-theme` with Theme Mod/menu/logo continuity verification before cleanup.

**Gate:** destructive deletion only after rollback confidence.

### P2 — Editorial single-post hardening

Theme-owned presentation only:

- semantic article header;
- native author + published/modified dates;
- category/tag presentation;
- featured image when present;
- readable long-form content measure/spacing;
- accessible tables, blockquotes, lists, headings and media containment;
- previous/next post navigation;
- WordPress-native author fallback, with RootProfile enhancement only through the existing public contract;
- no manual SEO title/meta/canonical/schema/OG generation in the Theme;
- no related-post ranking/query engine in the Theme.

Use RED -> minimal GREEN -> regression and keep article assets singular-post scoped.

### P3 — Editorial listing/search hardening

- improve native archive/search cards for long Vietnamese titles and excerpts;
- optional native featured image/date presentation where available;
- preserve WordPress query ownership;
- responsive 1440/1024/390/320 and keyboard/a11y checks.

### P4 — Real law-site pilot QA

Use realistic Vietnamese legal/editorial fixtures and the actual pilot plugin stack.

Minimum route set:

- homepage;
- long single Post;
- Post with long title and long taxonomy labels;
- Page;
- category/archive;
- search results + no results;
- 404;
- RootProfile profile/contact surfaces when public contract is available;
- Woo surfaces only if the site actually enables WooCommerce.

Quality checks:

- no Theme-caused PHP fatal/warning/uncaught;
- no horizontal overflow;
- keyboard/focus/landmarks/headings/labels;
- Vietnamese diacritics and long legal citations/tables;
- responsive media/table behavior;
- print readability for long-form article content if adopted as a Theme presentation requirement;
- Rank Math compatibility smoke without Theme duplicating SEO metadata;
- update-in-place, theme-switch and rollback continuity;
- measured asset/performance evidence on actual site content.

### P5 — Publication and deployment

Only after P0-P4 exit:

- owner approval for `v1.1.x`/next-version tag and GitHub Release as applicable;
- final deterministic package/SHA on exact release bytes;
- backup + rollback checkpoint;
- explicit production deployment approval;
- post-deploy smoke on the live domain.

## 7. Readiness assessment

**Core Theme engineering:** technically strong and close to product-grade after R0-R6.  
**Source readiness for next work:** not yet acceptable until P0 closes the current-state drift.  
**Law-site production readiness:** not yet PASS because the pilot identity issue is unresolved, current single-post presentation is underpowered for a high-trust editorial site, and no real-site P4 matrix has been completed.

## 8. Exact next

1. Capture the pilot site's `System Health` Theme version/identity evidence.
2. In parallel, reconcile source state through P0 on a docs-only branch/PR.
3. After P0 approval, execute P1 identity cleanup and then P2 editorial single-post hardening as the first production-quality implementation slice.

Do not tag/release/deploy until the site-specific pilot gates are satisfied.
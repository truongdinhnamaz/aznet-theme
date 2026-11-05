# Wellness 01 inner-page balance repair

> **For agentic workers:** Use superpowers:executing-plans for this bounded continuation of the existing QA plan.

**Goal:** Correct the mismatched title/content frames and cramped native columns reported by the owner, across the five existing inner Pages.

**Architecture:** Add a presentation stylesheet selected by the existing explicit `homepage_preset=wellness-01` setting inside the existing native Page asset loader. Standard/Wide Page headers and bodies share a frame, native paragraphs retain readable measures, and consecutive authored background Groups can occupy two desktop columns. Landing, Front Page, posts, WooCommerce and other presets retain their existing presentation. No Page identity inference or new persisted state.

**Tech Stack:** Existing PHP 8.1-compatible WordPress enqueue API, CSS, read-only browser geometry checks.

**Spec:** Owner feedback on the existing v0.7 preview: inner Pages have unbalanced presentation space. Continue the prior authorized repair workflow and visual language; this is an implementation correction, not a new product rule or redesign.

## Constraints and review focus

- Preserve WordPress content, media, templates, prices, links, editor policy and visibility. Edit the inactive draft only.
- No active-theme publication, merge, release, billing action or indexing write.
- Scope selection uses the explicit existing Theme presentation setting, never route, title, Page ID or business content.
- Preserve authored image ratios, negative-control button colors, mobile stacking and full-bleed Landing behavior.
- Check clipped elements, unequal title/body edges, undersized desktop columns, native card gaps and button contrast at 390/768/1440.

### Task 1: bounded repair and verification

**Files:** `inc/theme/assets.php`, new `assets/css/components/page-wellness-01.css`, `tests/browser/wellness01-inner-layout-probe.mjs`, `tests/offline/wellness01-page-assets-contract.php`, evidence under `docs/evidence/wellness01/`.

**Interfaces:** Existing `enqueue_page_assets(?string $version): void` additionally queues the content-hashed Wellness Page stylesheet only on its authorized native Page surface. The exported pure `probeWellnessInnerLayout()` consumes rendered DOM and returns geometry, image/button metrics and failures.

- [x] Capture RED on all five existing inner Pages: title frame1152px, content720px at1440; title/body left edges differ216px.
- [x] Verify loader guard RED, then add the minimal scoped enqueue and CSS. Use shared1120px Standard frame, existing Wide maximum, 24px native block rhythm and desktop pairs of consecutive background Groups; retain single-column small screens.
- [x] Synchronize only the new CSS and the exact existing loader insertion into the inactive draft.
- [x] Verify all five Pages at390/768/1440; visually inspect About, Programs, Pricing, Knowledge and Contact; retain authored content/media/URLs. Verify Front Page and other preset/Front/Woo/non-Page loader controls.
- [x] Run affected PHP contracts and syntax checks; review the bounded diff independently and prepare the existing draft branch for handoff. Existing v0.7 native editor evidence remains historical; do not claim it ran on changed PHP bytes.
- [x] Show the corrected preview and record results under `docs/evidence/wellness01/2026-10-30-v08-qa.md`; resulting commit identity is recorded in checkpoint v0.8 and the PR.
- [ ] Separate owner visual acceptance and release/merge/publication gate.

## v0.9 verification continuation

- [x] Repair the missing commit identity in checkpointv0.8 using Git/PR evidence, preserving its file identity.
- [x] Correct the historical native inventory omission; reuse the pinned release-package policy and check new-directory/uncommitted-file controls.
- [x] Execute a fresh seven-case native WP6.9/PHP8.1/MySQL8 Page HTML/assets smoke, including rendered fixture identity and exact CSS delivery; stop owned services.
- [x] Audit all235 draft text files and250 package paths. Align the identical Wellness admin-preview block; preserve the observed draft-name suffix.
- [x] Build and verify the250-file source QA ZIP against the native manifest; record separate raw-binary limitations.
- [ ] Remaining release boundary:11 draft binary GETs return403; owner publication/required-CI disposition is separate. No full draft-byte or current Actions PASS claim.

Evidence: `docs/evidence/wellness01/2026-10-30-v09-qa.md`. Production/UI source remainscab5cf81; only QA infrastructure/evidence changes in Git during this continuation.

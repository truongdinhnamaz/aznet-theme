# D-064 — AZnet Digital 01 Footer (2026-10-22)

## Ownership and baseline
- Canonical source `main@f9bdffed17d358a3a0667d4c92ce02955d955f2f`, AZnet WordPress running Theme 1.3.96. PR #503 is draft only.
- WordPress owns site logo, tagline, native navigation menus and editor-authored reusable wp_block Footer columns. Theme owns composition and CSS only.
- Verified existing AZnet Footer is sparse (logo, tagline, copyright). Registered footer menu positions `footer`, `footer-contact`, `footer-social`, `footer-policy` are **all unassigned**.
- Existing WordPress-native menu `Footer thiết kế website` ID 2578 has five authored links; `Main Menu chính` ID 1436 is assigned to header-utility. This branch **does not reassign WordPress menus or hardcode content**.
- RootProfile current public API organization route returned 404 `rootprofile_not_found`; do not infer official company facts from SEO JSON-LD or legacy Page HTML and write them to Theme.

## Implementation
- `template-parts/footer/site-footer.php`: corrects misplaced else-branch closing div. Under both menu-backed and WordPress wp_block-column paths, Footer main and bottom now remain direct siblings under Footer inner.
- `assets/css/components/site-footer.css`: scoped navy/orange Digital 01 + Professional preset footer presentation, responsive grid by actual populated source count, accessible focus and hover, 48rem one-column collapse, no made-up placeholder items. Other presets untouched.
- WordPress draft theme `aznet-theme-wpvibe-draft` created and exactly updated with these two GitHub source files; publication NOT performed. Fresh draft HTML shows the corrected Footer nesting. The WordPress menus stay unassigned; no dynamic menu content can appear until editor-owned assignments exist.

## TDD / QA
- RED: [workflow 39753409260](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39753409260) failed precisely `FAIL D-064: native menus: bottom/main escaped the Footer inner shell`.
- GREEN: [workflow 39753715874](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39753715874) passed PHP 8.1 and `tests/offline/d064-digital01-footer-contract.php` after minimal markup and scoped CSS changes.
- Retained full verification initially RED for the existing `professional-footer-mobile-spacing-contract.php` because it finds the last exact `@media (max-width: 48rem)` marker and expects legacy selectors after that marker. Re-scoped new CSS media as `@media screen and (max-width: 48rem)`, preserving effective CSS breakpoint but leaving the last exact marker for the retained contract. Exact corrected head `284446af5091587bac326d3ed13e2e31733ac24e` in CI.
- Real WordPress draft HTML after overwrite: Footer inner contains `main` and `bottom`, WordPress logo/tagline/copyright remain, no orphan menu columns. This proves a draft L3 read, **not** a responsive visual / source content / production L4 PASS.
- Header separate D-063 / PR #502 WP Rocket minified stale CSS remains independently blocked; do not claim addressed by Footer.

## Release gates / next
1. Inspect all exact-head CI results (especially V1 retained static) and verify responsive screenshots + a11y L4. No cross-layer PASS inference.
2. WordPress owner must configure actual Footer menu slots or reusable WordPress wp_block columns; the Theme must never fabricate contact, organization identity, social URLs or policies. Suggested use existing Menu 2578 for footer if owner approves and authoritative contact/social sources independently when available.
3. Preview actual populated Footer; verify linked destinations and mobile nav; preserve rollback to Theme 1.3.96 and existing menu assignments. No merge main, tag, GitHub Release or live publish without fresh owner release approval.

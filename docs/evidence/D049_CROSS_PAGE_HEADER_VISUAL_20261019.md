# D-049: Cross-page Header visual continuity, live Minh Nguyên pilot

Date: 2026-10-19. Repository: truongdinhnamaz/aznet-theme. Scope: Core presentation only.

## Real production evidence and root cause

- The user installed released Theme v1.3.89 and supplied separate incognito screenshots of https://minhnguyen.vn/ and https://minhnguyen.vn/giai-phap/.
- Homepage: native logo + MINH NGUYÊN brand text, full category label aligned above the Hero rail, nav on its right. The short seven-root native menu left an excessive white blank area because its card stretched to full Hero height.
- Inner page: brand text was hidden, category button narrower, main nav aligned near the right edge. CSS scoped category rail sizing and brand/nav continuity only to homepage-specific .aznet-theme-site-header--catalog-home-rail.
- Fresh WPVibe site_info and wp theme status reported active AZnet Theme v1.3.89. WordPress still owns Main menu and Menu danh mục (product-catalog location), with header_catalog_mode=homepage-rail. The unrelated WPVibe Draft was not modified.

## Root cause and bounded correction

- Opt-in catalog-only CSS used different Header grid tracks and justify-content between the homepage and inner pages. The same source had align-self:stretch on the Hero catalog, making a short menu occupy the entire Hero height.
- Reuse a single Core --aznet-theme-catalog-rail-width token and Header grid/shell on any catalog-enabled desktop page. Display WordPress native brand text alongside logo consistently and left-align the primary nav beside the catalog column.
- Retain the homepage-only informational category band (no second native menu), and retain the inner-page native details/summary dropdown. Give the dropdown popover the same width as its control.
- Use align-self:start for the native Hero rail, retaining maximum height and overflow scrolling for long WordPress menus. No Product terms, WooCommerce state, routing or other domain data is queried/duplicated by the Theme.

## TDD and browser evidence

- RED: test tests/offline/d049-cross-page-header-parity-contract.php, wired into scripts/verify-v1-core.sh. Head 74c7c63df27747b78645338fe823ce8976d0636b failed the exact intended assertion at V1 Core job 118045140899: missing shared Homepage/inner catalog width token.
- GREEN candidate: CSS commit c25e9b4c9c23a86954833452268489e3e309fe8e; browser fixture seeds WordPress custom_logo from a disposable native image; tests compare Header category left edge, first-column width, nav start and native brand text across Homepage and /shop/.
- Browser test asserts short collapsed seven-category menu fits its actual contents, dropdown panel matches control width and 1200/1101 desktop pages have no horizontal overflow. Existing 1440/1024/390/320 and keyboard/axe/no-JS checks retained.
- L4 WordPress/Playwright run 39248850445 completed SUCCESS on intermediate feature head 7cd67e4820726a5680563f7fea94732118da52da; artifacts include Standard/Commerce Homepage, inner closed/open dropdown and narrow desktop screenshots. This browser result is not a production deployment PASS.
- The former d049-rail-header-hero-visual-parity-contract.php checked a homepage-only CSS selector. It was strengthened to assert the new site-wide catalog selector instead; no visual assertion was removed. Exact final-head CI must independently PASS before merge/release.

## Rollback / gate

- Rollback is a revert of this bounded CSS/regression patch or setting header_catalog_mode=off. Native WordPress menu data stays unchanged.
- Current production still runs official 1.3.89. This PR does not upload a Theme package, edit WordPress options or publish the older WPVibe Draft.
- Merge/release, versioned package and new production installation remain approval-gated. New production L3/L4 must follow the actual deployment.

## Final visual review — label typography continuity

An independent inspection of the exact PR #474 Standard Header browser screenshots (industrial01-pilot-runtime artifact 12186200881, run 39250422688) confirmed the original three screenshot defects are corrected. One residual cosmetic mismatch was identified: the homepage catalog heading is uppercase, while the inner-page native dropdown label still used sentence case. The bounded follow-up scopes `font-weight`, `letter-spacing` and `text-transform: uppercase` only to the opt-in catalog dropdown summary on desktop. The existing browser comparison now verifies computed `text-transform` matches both surfaces. Native WordPress menu, control accessibility, compact/other preset without catalog and mobile behavior are unchanged. Final-head CI must be rerun before merge; this is still a feature-branch checkpoint, not production PASS.

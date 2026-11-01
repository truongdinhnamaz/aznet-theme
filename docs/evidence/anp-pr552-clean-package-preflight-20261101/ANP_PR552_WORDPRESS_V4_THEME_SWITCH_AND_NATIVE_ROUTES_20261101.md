# ANP PR #552 — WordPress 6.9 support-floor theme-switch and native route L3 (2026-11-01)

## Authority and unchanged source

- Canonical repository: `truongdinhnamaz/aznet-theme`.
- Candidate: Draft PR #552, head `fc6725654ab20767fecd17412416b2ad12a6c2bd`; main `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`; both revalidated before this evidence.
- Authoritative AZT-05 v1.0 (Git blob `85135c026504484962e2bb1f971ae51ed5e2205a`), AZT-00–04 and Source Governance remain unchanged. Release/rollback and explicit owner approval remain hard gates.
- Prior evidence checkpoint: `81d9a8050a2ffe2545d7269845133129b70ab0ba`.
- All execution occurred in a fresh **disposable WordPress Playground with SQLite**. No ANP hosting, public customer website, live WordPress database/files, Wellness 01, or separate plugin/domain-owner repository was touched.

## Exact-package setup retained, not repeated without reason

The V4 Blueprint (new evidence-only file `AZT_PR552_THEME_SWITCH_FRONTEND_RECEIPT_V4_20261101.json` at commit `be4214397ca9cc1df605a62e2910464dbb204e42`) includes the previously proven exact-package installation prerequisite. It reconstructs ZIP `aznet-theme/` from the pinned PR source inside a disposable environment, verifies persisted ZIP bytes, and uses WordPress core `Theme_Upgrader` for installation. The existing L0-L2/fixture and V2 real WordPress installation checks were not reopened as new product regressions.

Expected ZIP SHA-256: `44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f` (242 production files, 656000-byte deterministic ZIP); actual package and WordPress-installed payload byte integrity had previously been proved at the separate V2 checkpoint.

## New L3 validation and root-cause isolation

The earlier V3 probe [run f55a2bc6](https://agent.tinyfish.ai/runs/f55a2bc6-2da4-47eb-a486-7adab7cc1728) was **UNKNOWN** because the browser investigator could not read the machine JSON and was observing a default-theme page; it was not evidence of an AZnet Theme defect.

V4 changes *only the test harness*, splitting follow-up into four bounded WordPress `runPHP` steps:
1. Capture title/body/status SHA-256 digests of three synthetic WordPress Page/Post objects and pinned ZIP; publish a QA-only front page.
2. `switch_theme('twentytwentyfive')`; assert active default Theme.
3. `switch_theme('aznet-theme')`; assert AZnet Theme active again.
4. Verify the same Page/Post hashes and front-page reference persisted, inspect active plugins/debug-log presence, write the exact assertions to both a JSON file and **visible front-page content**, and provide clickable QA-only native routes.

This removes the V3 reporting visibility dependency. No Theme production file was modified.

## Fresh V4 observable test result

- **Execution evidence**: [WordPress Playground run 38af80b9](https://agent.tinyfish.ai/runs/38af80b9-020f-48e9-bb03-833c3f27a606), reported completion after 28 browser-agent steps. The agent observed the exact front-page marker `AZT_PR552_V4_PASS` with the machine JSON from the WordPress runtime:
  - `candidate_commit=fc6725654ab20767fecd17412416b2ad12a6c2bd`
  - `zip_exact_match=true`, `zip_sha256=44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f`
  - `wordpress_version=6.9.11`, `php_version=8.1.34`, database: Playground SQLite
  - `active_theme_after=aznet-theme`, `active_plugins=[]`
  - `switch_away_success=true`; `switch_back_success=true`
  - `page_post_content_unchanged=true`; `front_page_setting_stable=true`
  - `debug_log_available=false`; `theme_error_markers=[]`
  - `pass=true`
- **Three fresh, actual WordPress browser route observations** (not a mock DOM fixture):
  - `/azt-qa-article/`: heading `AZT QA Article`, `Native post` subheading, expected content, WordPress date/category and comment area visible.
  - `/?s=azt`: four synthetic Page/Post results visible (Home, Contact, QA verification, Article).
  - `/azt-pr552-missing-page-404/`: `Page not found` heading, search form and `Back to home` link visible.
- Prior V2 homepage/contact native rendering proof remains retained.
- **Important limitations:** The browser report does not establish independent HTTP status codes/final URLs or full keyboard/screen-reader WCAG compliance. `debug_log_available=false` means a missing log, **not** a verified zero-warning PHP log. SQLite sandbox continuity is **not** a byte-exact live ANP/theme-directory rollback or MySQL restoration receipt. V4 has not been certified on ANP or all Woo/provider surfaces.

## Acceptance disposition

| Scope | Status |
| --- | --- |
| L0-L2 deterministic exact ZIP and focused source contracts | PASS retained |
| L3 exact ZIP WordPress 6.9.11/PHP 8.1.34 install/activate (zero plugins) | PASS retained |
| L3 Page/Post content continuity through Theme switch away/back | **PASS new, disposable SQLite** |
| L3 actual WordPress Article/Search/404 render sample | **PASS new, browser-visible content only** |
| L3 PHP debug-log clean assertion, HTTP status and full regression | **UNKNOWN** |
| L4 genuine WordPress 390/768/1440 visual/a11y, not synthetic fixture | **UNKNOWN** |
| L5 provider-present real integration | **BLOCKED/UNKNOWN (separate track)** |
| L6 real ANP backup/preimage, full rollback and release approval | **BLOCKED** |

Existing live ANP contains at least three unrelated Wellness 01 text files absent from the 242-file clean ZIP. Full-directory overwrite is unsafe, even though the ZIP itself has integrity PASS.

Engineering Guardian: no authenticated shared coordination registry reachable from this chat, so **cross-chat collision protection is UNKNOWN / NOT ENFORCED**. No irreversible integration or production code changes were undertaken.

**EXACT NEXT:** Perform a narrowly instrumented **real WordPress L4 responsive/keyboard/accessibility pass at 390/768/1440** against this same pinned package in a disposable sandbox, with measured viewport widths and artifacts; do not rerun L0-L2. Separately retain live rollback/owner approval gates.

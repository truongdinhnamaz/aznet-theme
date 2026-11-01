# ANP PR #552 — Measured responsive L4 on actual isolated WordPress Industrial 01 (2026-11-01)

## Authority, source, non-goals
- Canonical repository: `truongdinhnamaz/aznet-theme`. Draft/open/unmerged PR #552 candidate `fc6725654ab20767fecd17412416b2ad12a6c2bd`; `main=9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`.
- Accepted AZT-05 v1.0 and AZT-00–04 govern Theme presentation ownership, WordPress-native content, QA distinctions and release gates. Evidence does not amend product policy.
- Package: exactly 242 file candidate ZIP SHA-256 `44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f` (previously verified 656000 bytes).
- All execution was in **disposable WordPress Playground / SQLite**, configured with the exact package and the Industrial 01 presentation preset. No ANP hosting or live WordPress/DB files, WooCommerce or provider internal storage, mixed WPVibe draft, Wellness 01, main, PR head, release/tag or production data were modified.
- The measured content is a synthetic **WordPress-owned Page** constructed in the isolated DB with 3 native `.wp-block-column` blocks; the DOM and CSS layout are rendered by the **real installed candidate WordPress Theme**. This is neither fake DOM HTML outside WordPress nor genuine ANP business content.

## New scoped L4 method
- Test-only [V5 Blueprint](https://github.com/truongdinhnamaz/aznet-theme/blob/865afc693289533450e505d38b1de133cfc356be/docs/evidence/anp-pr552-clean-package-preflight-20261101/AZT_PR552_WORDPRESS_IFRAME_RESPONSIVE_L4_V5_20261101.json) reinstalls the verified ZIP in a disposable WordPress instance, activates Industrial 01 through Theme-owned presentation settings, updates only isolated WordPress Page content, and installs a **disposable mu-plugin test endpoint** (not distributed Theme source or a provider dependency).
- Parent test page creates **three same-origin iframes** each loading the actual WordPress front-page URL. Each iframe sets explicit CSS width 390, 768 or 1440 pixels and reads `contentWindow.innerWidth`, document client/scroll widths, computed `gridTemplateColumns`, main/industrial wrapper/native content landmarks, H1 count and named card links.
- Browser viewports are independently instrumented in the embedded WordPress page itself. The `390/768/1440` widths are **actual child browsing contexts**, not a claim that the browser's *top-level device* was resized. Standard browser scrollbar reduced `document_client_width` by 15px while keeping the expected iframe `innerWidth`.

## Fresh execution: measured L4 PASS (bounded)

**Run:** [7ec587ba](https://agent.tinyfish.ai/runs/7ec587ba-f705-4494-b941-0ad358d4be45). Browser agent observed `AZT_PR552_L4_PASS` with the following actual WordPress frame data (not merely a script specification):

| Target / measured innerWidth | clientWidth | scrollWidth | overflow px | computed CSS grid columns | H1 | named native card links | Result |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | --- |
| 390 / 390 | 375 | 375 | 0 | **1** | 1 | 3/3 | PASS |
| 768 / 768 | 753 | 753 | 0 | **2** | 1 | 3/3 | PASS |
| 1440 / 1440 | 1425 | 1425 | 0 | **3** | 1 | 3/3 | PASS |

At all three widths, the expected `main#main`, `.aznet-theme-homepage--industrial-01`, and `[data-aznet-homepage-surface="front-page-content"]` existed and the native WordPress block grid used computed `display:grid`. Each frame had 3 native card links and 0 unnamed card links.

**PASS represents:** real WordPress-run exact candidate responsive grid and minimum inspected semantic/link assertions for one Industrial 01 native front-page sample.

**NOT established:** complete site-wide WordPress L4, top-level physical-device/screen-reader behavior, header catalog submenu keyboard/focus state, automated axe/WCAG audit, provider-present WooCategory/Product/Cart/Menu on real pilot content, exact HTTP status codes, PHP debug-log clean assertion, MySQL/migration/restore, ANP visual fidelity or production release readiness.

## Retained evidence and remaining gates

- L0–L2 exact package/5 focused contracts, synthetic Chromium fixture, 8/8 AX card links: **PASS retained**, not re-executed without invalidation.
- L3 exact ZIP WordPress 6.9.11/PHP 8.1.34/SQLite install/activate, Theme switch-away/back, Page/Post content continuity, native Article/Search/404 render sample: **PASS retained**, from [V2](ANP_PR552_WORDPRESS_69_PHP81_EXACT_PACKAGE_L3_20261101.md) and [V4](ANP_PR552_WORDPRESS_V4_THEME_SWITCH_AND_NATIVE_ROUTES_20261101.md).
- L4 responsive Industrial 01 **measured iframe WordPress sample**: **PASS scoped**; broader browser/a11y still UNKNOWN.
- L5 provider-specific real integration: **UNKNOWN/BLOCKED separately**. AZT-05 makes external provider optional for core release unless Theme itself owns the defect or a specific release makes it mandatory.
- L6 ANP release/restore remains **BLOCKED**. Live ANP contains three additional Wellness 01 text files outside the clean package; full-directory Theme overwrite would delete unrelated work. No authorized verified active Theme preimage+database restore point or separately explicit release approval exists.
- Engineering Guardian shared registry unavailable here: cross-chat collision protection **UNKNOWN/NOT ENFORCED**. No irreversible integration attempted.

**EXACT NEXT:** bounded **real WordPress header catalog submenu keyboard/focus/accessibility browser QA** against this same exact package (e.g. public WordPress menu fixture with nested items and valid Theme catalog setting), and/or full-width visual/browser spot-check where practical. Do not re-run previous PASS or treat this as complete WCAG/L6.

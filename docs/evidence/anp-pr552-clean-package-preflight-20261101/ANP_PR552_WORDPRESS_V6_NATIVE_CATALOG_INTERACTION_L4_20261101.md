# ANP PR #552 — Native WordPress catalog-menu interactive L4 (2026-11-01)

## Authority and unchanged candidate

- Canonical GitHub: `truongdinhnamaz/aznet-theme`.
- Candidate PR #552: open Draft, unmerged, commit `fc6725654ab20767fecd17412416b2ad12a6c2bd`. Main: `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`.
- Accepted AZT-05 v1.0 and AZT-00–04 source governance retain authority. No product ownership, contracts, release rules or production Theme code changed.
- Exact candidate package: `aznet-theme/` 242 production paths, deterministic ZIP SHA-256 `44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f`.
- No ANP hosting, ANP WordPress, WPVibe mixed draft, Wellness 01, WooCommerce/private state or separate provider repository touched.

## New scope and method

- Evidence-only [V6 Blueprint](AZT_PR552_WORDPRESS_NAV_ACCESSIBILITY_L4_V6_20261101.json), committed as docs-only QA on work branch. It reuses the exact-package isolated WordPress 6.9/PHP 8.1 setup as a prerequisite, creates **a genuine WordPress native menu with parent and child** using `wp_create_nav_menu` / `wp_update_nav_menu_item`, assigns it to the Theme's public `product-catalog` location, and chooses Theme-owned `header_catalog_mode=dropdown`. This respects WordPress native content ownership and avoids any WooCommerce/private taxonomy read.
- A disposable mu-plugin serves an independent QA receipt page. Three *same-origin iframes* of measured `innerWidth` 390/768/1440 load the actual rendered WordPress front page with the candidate Theme, menu markup, and `assets/js/catalog-navigation.js`.
- The in-frame script opens the appropriate visible native catalog disclosure, locates the **JS-generated real button**, verifies `aria-controls` refers to the actual submenu and the decorative icon is `aria-hidden=true`. It dispatches DOM click followed by a bubbling `KeyboardEvent('keydown', {key:'Escape'})` to test the real live event listener. It then checks submenu hidden state, accessible label and programmatic focus restoration. This is real WordPress DOM and installed JS, **not a separate mock DOM**, but it is **scripted events**, not physical keyboard, real screen reader or a full axe audit.

## Fresh observed result — bounded L4 PASS

- Browser run: [bf4c47da](https://agent.tinyfish.ai/runs/bf4c47da-4f08-48c3-8cf1-ce63480ccab3).
- Observed QA marker: `AZT_PR552_NAV_PASS`.
- For all three measured in-frame widths `390, 768, 1440`: `pass=true`; native catalog menu existed and selected menu was visible; `aria-controls` resolved to a real submenu.
- Initial `aria-expanded=false`, target `hidden=true`, icon `aria-hidden=true`; label `Mở danh mục con: Banh va lop QA`.
- On clicking the real button: `aria-expanded=true`, target `hidden=false`, label `Thu gọn danh mục con: Banh va lop QA`.
- On dispatching Escape: `aria-expanded=false`, target `hidden=true`, `document.activeElement===button` **true**.
- Every assertion PASS at all three widths.

## QA classification and preserved blockers

- Existing L0–L2 package/source contracts: PASS retained, not rerun without invalidation.
- L3 genuine isolated WordPress install/activate, Theme switching native Page/Post data continuity, Article/Search/404 browser samples: PASS retained (see V2/V4 receipts). PHP `debug.log` clean assertion remains UNKNOWN because V4 observed `debug_log_available=false`, not a complete log.
- L4 measured WordPress Industrial native content responsive grid 1/2/3 at 390/768/1440 with 0 overflow and 3/3 named card links: PASS retained (V5).
- **NEW L4:** WordPress native menu, dynamic JS disclosure, `aria-controls`, expanded state, Escape and focus restoration: PASS scoped on three iframe widths. Full keyboard-device, actual assistive technology, axe/WCAG and visual sitewide certification remain UNKNOWN.
- L5 provider-present (Woo/ConvertFlow/RootProfile) runtime: not established. Core AZT-05 requires zero-plugin independence; no optional provider can redefine the core acceptance contract.
- L6 deployment safety/rollback: BLOCKED. ANP active Theme has additional Wellness 01 files absent from the clean ZIP. No verified complete active Theme+DB preimage or independently approved production release. **Do not overwrite active Theme directory, delete Wellness, merge, tag, or publish.**
- Engineering Guardian shared registry is not connected to this session. Cross-chat conflict prevention is **UNKNOWN / NOT ENFORCED**.

**EXACT NEXT:** Capture HTTP response codes/final URLs and a verifiable Theme-scoped PHP log receipt from a disposable real WordPress run, without rerunning L0–L2 or disrupting the retained L3/L4 PASS. Then keep L5 provider-specific certification and L6 production rollback/deploy as separate hard gates.

# ANP PR #552 — Five exact-source L2 contracts + isolated Chromium markup QA (2026-11-01)

## Source and scope

- Canonical source: `truongdinhnamaz/aznet-theme`, Draft PR #552 exact head `fc6725654ab20767fecd17412416b2ad12a6c2bd`; canonical main `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`. Both revalidated before this bounded check; PR open/unmerged.
- Exact previously rebuilt, SHA-256 verified 242-entry candidate Theme ZIP: **`44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f`**. The source tree used here was previously verified against ZIP extraction and 242 candidate Git blob IDs; this check does not change the archive.
- Existing PASS retained: 164 PHP packaged file syntax checks, nine JS syntax checks, deterministic ZIP, separate five-file overlay/rollback fixture. They were **not rerun indiscriminately**.
- Work performed in isolated local fixture files only. **No access to ANP hosting/cPanel, no mutation of WordPress live or WPVibe draft, no deletion of Wellness 01 files, no source PR/main merge, no deploy.**

## Fresh focused L2 tests (real test source, fixture execution)

All five exact tests retrieved from the PR head have Git blob byte identities verified locally via `git hash-object`, then executed against its exact candidate source:

| Original PR test | Exact Git blob SHA | Fresh result |
|---|---|---|
| `tests/offline/catalog-navigation-accessibility-behavior.test.mjs` | `2d1b33136d8484ed3e1603e842b9a7a8029cab95` | PASS |
| `tests/offline/industrial01-native-front-page-presentation-contract.test.mjs` | `726fc397f78ea1a3a1a42b233a8167acb8fd6962` | PASS |
| `tests/offline/core-vertical-catalog-submenu-contract.php` | `fad3d65f92dd0534f376848f619513d4f81ac52d` | PASS |
| `tests/offline/industrial01-category-gallery-contract.php` | `4a5e20799125f3d41cb95142d5f9d2ec86d23d4c` | PASS |
| `tests/offline/r3-header-preset-contract.php` | `3dfd93db2593c96f730ce18dfdf3f037a62ceac2` | PASS |

Two tests (**category gallery** and **R3 Header**) were not part of the earlier three-test PASS and now have **fresh** execution evidence. The category test covers real term-image retained, classic and modern Woo placeholder removal, filtered public placeholder URL, missing-source URL fail-soft, provider absent/empty, order, count and no private storage reads. The Header test exercises preset, mobile-panel guard and Woo public assigned/unassigned account/cart URL fail-soft.

- Local test runtime: PHP **8.4.24**, Node.js **22.16.0**.
- Log SHA-256: **`384f2c7c596d970defc7def1c0203a8c80d3ab86206c5d6a2a5af090d4c17fbf`** (local execution record).
- These are focused **L1/L2 contract PASS**, not proof of the support-floor PHP 8.1, real WordPress/WooCommerce runtime, or production L5 integration.

## Isolated Chromium rendering and keyboard test (NOT real WordPress)

A local HTML fixture was generated using the **real PR #552 `template-parts/homepage/industrial-01/categories.php`** executed by PHP with the exact test's WordPress/WooCommerce mock functions. Eight test categories are provided (seven test images and one Woo placeholder term `Pin Lithium`). The heading, native `.wp-block-group > .wp-block-columns` composition and `wp_nav_menu` parent-child markup are fixture-authored. The **unmodified candidate CSS** `assets/css/tokens.css`, `assets/css/components/homepage-industrial-01.css`, `assets/css/components/catalog-navigation.css`, plus **unmodified candidate JS** `assets/js/catalog-navigation.js` were injected via Playwright into Chromium's isolated `page.set_content`, avoiding externally hosted pages. Decorative JPEGs were generated as local *test media*, **not** user/site imagery.

- Chromium **144.0.7559.96**; viewport test widths **390, 768, 1440px**.
- Responsive actual computed CSS grid columns for *WordPress-native block columns*: **1, 2, 3** respectively.
- Responsive actual computed CSS grid columns for eight-category gallery: **2, 2, 4** respectively.
- All **8/8 distinct source category links** were retained at each viewport; **7/7 synthetic source IMG assets loaded** and the `Pin Lithium` category used `--no-media` with no placeholder IMG.
- Child-category disclosure button: `aria-controls` targets the actual submenu; closed `aria-expanded=false` and `hidden=true`, click opens with `aria-expanded=true`, Escape closes and restores DOM focus to original button. Decorative submenu icon has `aria-hidden=true`. Checked at all 3 widths.
- `document.documentElement.scrollWidth <= window.innerWidth + 1`, eight category boxes within viewport, no JavaScript runtime exceptions at all three widths.
- JSON result SHA-256: **`0c49fd251dabdfbadda21c894d265abdf90fbe8cdf81bc7f6f4d8d818a93e6cb`**.
- Optional screenshot SHA-256 per fixture (local, *not website screenshots*):
  - 390px: `773261367e330420ed79071baff6f2c99a8a0aaa1f0c5ecfa90ab7af07080c90`
  - 768px: `9e054aaef801c50046e5e5020e79fbb7753ff5fe154a636ac9172d6364b4b8cc`
  - 1440px: `c65d7a667274456b09ba60a9a7c062b9491a5fee381595da17d2ac782e227347`

**Classification:** Chromium isolated **presentation/keyboard fixture PASS**; **NOT L3 WordPress runtime PASS**, **NOT live/published L4**, **NOT formal WCAG/axe audit**. The fixture is intentionally explicit about mocked provider output. The existing WPVibe preview 14-route and earlier public ANP measurements remain separate, non-equivalent evidence.

## Hard runtime constraint / gate discipline

The current runner has Chromium, PHP 8.4 and Node but no WordPress Core, no `mysqli`/PDO MySQL/SQLite extensions, no MySQL/SQLite server/client, Docker/Podman or WP-CLI. DNS to WordPress download endpoints is blocked. Local `file://` and loopback navigations in Chromium return `ERR_BLOCKED_BY_ADMINISTRATOR`, so source CSS/JS were injected into the browser's blank in-memory page instead of misrepresenting local HTTP as WordPress. This is a genuine **L3 environment blocker**; no WordPress 6.9/PHP 8.1 support-floor runtime was provisioned. The central NWQS promoted policy endpoint could not be retrieved; the fallback baseline was used provisionally without a version-synchronization claim.

## PASS, BLOCKED and exact NEXT

- **PASS L0/L1:** retain earlier exact-package and provenance checks, no rerun.
- **PASS targeted L2:** all five original candidate source tests, including the two freshly executed PHP tests.
- **PASS browser fixture only:** the exact candidate presentation code at three widths, no overflow in isolated rendering, actual submenu DOM attributes and focus in Chromium.
- **UNKNOWN/BLOCKED:** real WordPress 6.9+/PHP 8.1+ disposable L3, exact-package live-equivalent L4/axe, WooCommerce integration L5, and an independently tested production rollback. No attempt to touch ANP hosting as instructed. Also no full-directory live replacement because active ANP contains Wellness 01 files absent from canonical source.
- **Release:** PR #552 remains Draft; **no publication/merge/deploy authorization inferred**, no live changes.

**EXACT NEXT:** in a separately authorized **disposable WordPress environment** with core + database and minimum PHP support, install the SHA-pinned full candidate ZIP, run real L3 and L4/L5 contract checks on that exact package, and rehearse preservation/rollback of the seven out-of-source Wellness assets before seeking explicit publication approval. Do not rerun the five focused L2 tests without a source change.


## Independent late read-only check — WordPress preview H1 and Chrome accessibility tree

On **2026-11-01**, after revalidating PR #552 head `fc6725654ab20767fecd17412416b2ad12a6c2bd` and `main` `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`:

1. Authorized WordPress WPVibe **read-only** `get_page_html` of the **draft preview** route `/lien-he/` with CSS selector `h1` returned exactly `<h1 class="aznet-theme-page__title">Liên hệ</h1>`. Independent `selector=main` readback included that H1 inside `<main id="main" class="aznet-theme-main aznet-theme-main--page">`. It also showed native Contact Form 7 labels and `aria-required="true"` on required inputs. **PASS scoped actual-WordPress preview H1**. This corrects the **earlier TinyFish narrative that `/lien-he/` lacked an H1**; do not create a speculative Theme/content patch from the false positive.
2. WPVibe `selector=".aznet-theme-catalog-navigation"` returned a server-rendered mobile `<details class="aznet-theme-catalog-navigation ...">...` element with nested native-menu items; dynamic `.aznet-theme-catalog-navigation__submenu-toggle` was not in that HTML extraction. Since the toggle is **constructed by JS**, a selector result absent from server HTML does **not** prove dynamic `aria-expanded` missing. **Live/preview DOM state remains UNKNOWN**, pending a browser capability that captures raw post-JS attributes and focus.
3. The **existing exact-source candidate isolated Chromium fixture** (not WordPress) was additionally inspected through **Chrome DevTools Protocol `Accessibility.getFullAXTree`** at 390px. Exactly **8/8** synthetic Woo source category links exposed nonempty accessible names and were focusable. The disclosure was a non-ignored `button`, name `Mở danh mục con: Bánh và lốp xe nâng`, accessibility property `expanded=false`, focusable true; decorative media did not add separate image roles. **PASS scoped isolated AX-tree semantic checks**, not a published-site axe/WCAG certificate. Local JSON SHA-256 **`6826164080169191fb75a558c7b2c857aa38e1c4c6a2ef731d423d4bf76c1184`**.
4. **True local WordPress L3 remains BLOCKED**, independently checked: no installed WordPress Core, MySQL/SQLite driver/server, WP-CLI or Docker/Podman, and official WordPress download DNS fails. The connected WPVibe `AZnet Việt Nam` account reached its rolling 24-hour fair-use cap after the read-only H1 result; a secondary already-authorized account had **no connection to ANP**, and no new connection/credentials were requested. Do not escalate or bypass provider limits. Website live, mixed Wellness draft and PR #552 code were not modified.
5. Current promoted Nagavia Website Quality Standard endpoint was inaccessible in the available lookup; provisional safe baseline only, with **no central standard version claim**.

This update **does not supersede** previously verified full candidate ZIP L0/L1 and five exact-source L2 tests. Keep **L3 whole-package WordPress, L4 real post-JS browser, L5 integration, and L6 release as UNKNOWN/BLOCKED**. Exact safe next is an independent disposable WordPress environment with core+DB; do not reuse a synthetic fixture to claim L3. No ANP hosting/cPanel access or production release.

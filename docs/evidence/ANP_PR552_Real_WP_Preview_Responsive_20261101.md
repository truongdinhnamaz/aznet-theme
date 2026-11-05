# ANP Industrial 01 — Real WordPress preview, measured homepage regression (2026-11-01)

## Source and scope
Canonical repo `truongdinhnamaz/aznet-theme`, Draft PR #552. Active public ANP remains `aznet-theme` 1.3.98; no production publication or merge. Scope is **only** the native WordPress front-page service article `article[data-aznet-homepage-surface="front-page-content"]`; not the full 14-route audit. Website data/identity/routing and WooCommerce/ConvertFlow storage were not modified.

## Root cause
Fresh actual WPVibe rendered `#main` markup has `main#main > .aznet-theme-homepage--industrial-01 > article[data-aznet-homepage-surface="front-page-content"]`. Prior Theme CSS and Draft PR #550 read-only L4 probe incorrectly assumed `main#main > article`; the section-level width/background/shell selectors did not match. Product card Grid and CTA selectors were descendants and already worked, which allowed misleading partial visual results. Historical offline CSS fixtures also had the wrong DOM wrapper; old fixture PASS did **not** establish live-page layout correctness.

## TDD and Theme-only correction
- RED: Update the original native front-page Node contract to require the correct wrapper hierarchy; run against old CSS `f2af2aaa4e2314a000e055cf50af6c3ae90ca37f`, exit nonzero on missing wrapper selector.
- GREEN: change only **three** article/content shell selectors and the comment in `assets/css/components/homepage-industrial-01.css`, retain native WordPress blocks/links and all existing card/CTA logic. Test exit 0 on Node 22.16.0; `node --check` PASS. New CSS blob: `7c9e4e9142cc315cb1ce16740efbf0e8a3e75459`, test blob: `726fc397f78ea1a3a1a42b233a8167acb8fd6962`. Implementation commit `549b49083168933dc19a38250fac01cc0768ad5f`.
- Independent Chromium 144 **isolated nested DOM + actual WP Gutenberg column CSS replay**: old section background transparent, desktop 1440 article not full width; patched section light gray, full width and centered at 390/768/1440, Grid 1/2/3, no measured horizontal overflow, all three card gold borders 4px and navy CTAs. Isolated offline replay alone is **L2**, not WordPress runtime QA.

## New L4 — real WordPress preview browser computed styles, limited to this section

A temporary QA-only script was placed in the **isolated WPVibe draft** (never committed or published). It sequentially loaded real WPVibe preview homepages inside same-origin iframes at specified CSS widths and read each child frame's `window.innerWidth`, `document.documentElement.clientWidth`, computed Grid, card borders/CTAs and geometry. Only this native service article was measured.

| Measured property | 390 CSS px | 768 CSS px | 1440 CSS px |
| --- | --- | --- | --- |
| `window.innerWidth` | 390 | 768 | 1440 |
| `documentElement.clientWidth` (scrollbar-adjusted) | 375 | 753 | 1425 |
| Grid tracks | 1 | 2 | 3 |
| Cards | 3 | 3 | 3 |
| Computed top border on **each** card | 4px solid `rgb(216,165,79)` | Same | Same |
| Computed CTA background on **each** card | `rgb(23,59,84)` | Same | Same |
| Horizontal overflow of measured child frame | 0px | 0px | 0px |
| **Old** `articleFullWidth` | true | true | **false** |
| **Patched** `articleFullWidth` | **true** | **true** | **true** |
| Patched `shellCentered` | true | true | true |

- **Real WordPress RED:** [TinyFish run f1327985](https://agent.tinyfish.ai/runs/f1327985-2238-4944-85b3-23a97e7815be).
- **Real WordPress GREEN:** [TinyFish run 0b7d3a17](https://agent.tinyfish.ai/runs/0b7d3a17-5c76-49e2-9eb8-c7ea9ea06320).
- This disproves the earlier TinyFish visual claim that the gold borders were missing: computed style on all cards shows the correct accent at all three widths. **Do not interpret this as complete visual/a11y PASS of the entire site.**

## Exact preview write/readback and cleanup
- WPVibe edit only the three Theme CSS selectors/comment in the existing AZnet draft. Two-range WPVibe readback of all **1604 lines** exactly matched the GitHub CSS at candidate commit `549b490...` (byte-for-byte). WordPress preview enqueues this asset with SHA-256-based cache version `1.3.98-739b80341c9e`.
- Temporary diagnostic iframe script was **fully removed** via a bounded text deletion. Fresh readback of `front-page.php` exactly matches canonical Git blob `c86b216108d14c7dfad37a907ff66b552dd454a7`. No test marker was found in the rendered preview afterward.
- Separate Draft PR #550 read-only browser harness fixed to use the same actual nested DOM path, commit `2885311cacacb6fa5fa63516feca63d3e19fd8c3`. GitHub main remains untouched.

## QA classification and release gate
- **L1/L2 PASS:** source/local RED→GREEN contract; offline nested Chromium width/color regressions.
- **L3 PASS within target:** WordPress draft PHP-rendered article, CSS asset loaded and exact file readback.
- **L4 targeted PASS:** actual WP preview browser computed CSS at measured 390/768/1440 for native service article. This is **not** full-page screenshots/interaction/axe coverage.
- **L4 sitewide, L5 provider interaction, L6 publication: NOT PASS / UNKNOWN**. Prior WPVibe Lighthouse 100/100 on preview mobile/desktop still identified `label-content-name-mismatch` without selectors; no blanket accessibility PASS or zero-error claim.
- Fresh site-wide/DB backup, recovery verification, separate main merge approval and live publish authorization have not been established. No live release.
- Promoted NWQS central registry unavailable; apply only provisional safe baseline, no claims of global quality-policy synchronization.

**EXACT NEXT:** conduct bounded remaining browser interaction/a11y review on main routes using the corrected QA harness, preserve established PASS without rerun; verify fresh host/DB backup and rollback. Ask for explicit owner merge/publish approval only when release gates are satisfied. Then verify anonymous public DOM/screenshot after release. Access token and authenticated admin screenshot deliberately excluded.

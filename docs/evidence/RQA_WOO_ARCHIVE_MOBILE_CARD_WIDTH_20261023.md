# RQA — WooCommerce archive mobile-card width fix: RED → GREEN + live L4

**Date:** 2026-10-23, Asia/Bangkok. **Scope:** AZnet Theme-owned WooCommerce **presentation only**.  
**Canonical Theme repository:** `truongdinhnamaz/aznet-theme`. **Candidate:** [Draft PR #506](https://github.com/truongdinhnamaz/aznet-theme/pull/506), branch `work/rqa-woo-mobile-grid-20261023`.  
**Production:** Rèm Quốc Anh still runs AZnet Theme **1.3.92** (fresh WPVibe authenticated `site_info` read-back); no Theme file was modified or deployed by this slice.

## Exact baseline / RED

- Live `/rem-cuon/` in Chromium 390px: WordPress/WooCommerce server-rendered product archive `ul.products.columns-4` is a single CSS Grid column **354px** wide, but its first `li.product` is **169.90625px** wide, `float:left`, `clear:both`. Visually the card uses only half the available column.
- Installed WooCommerce 11.2.1 `woocommerce-smallscreen.css`: on mobile, `.woocommerce ul.products[class*=columns-] li.product` sets `width:48%;float:left;clear:both`. This more-specific selector outranks Theme's generic `.woocommerce ul.products li.product` width/float reset.
- [Original RED GitHub Actions #39964029501](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39964029501) failed the isolated real-browser cascade fixture at 390px (ratio **0.48**) and 768px (**0.4799**), but 1280px already passed (**1.0**). This is a correct expected failure, not a flaky runner error.

## Minimal Theme-owned patch

`assets/css/components/woocommerce-archive.css`:

```css
.woocommerce ul.products[class*="columns-"] li.product {
    float: none;
    clear: none;
    width: auto;
    margin: 0;
}
```

- No WooCommerce plugin files, prices, stock, taxonomy data, ConvertFlow private state, build stack, JS or template logic touched.
- A WooCommerce mobile `nth-child(2n)` float may remain as computed `float:right` due to `!important`; CSS Grid ignores floats for grid items and the card width is correct. The acceptance gate checks the actual 100%-column width and link integrity, **not** a blanket guarantee all computed float properties are none.

## L2 GREEN and live L4 browser-local verification

- [Fixture GREEN GitHub Actions #39964271277](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39964271277): card/grid-track ratio 1.0 at **390, 768, 1280**.
- [Actual RQA one-width browser-local overlay #39965021161](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39965021161): first card expands **169.90625px → 354px** at viewport 390px, with no WordPress or Theme-file write.
- [Actual RQA responsive matrix #39970836010](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39970836010), exact tested branch head `65ed5a5c069200bd718d8f35556cde3ca7c9a28e`, SUCCESS:
  - **360px**: one grid track 324px, before card 155.515625px, after **324px**. Three cards match their track and all three product links remain valid.
  - **768px**: three tracks 213.328px each, before first card 102.390625px, after **213.328125px**. Three cards match tracks, links valid.
  - **1024px**: four tracks 218px each, before and after first card 218px; no regression.
  - **1366px**: four tracks 302px each, before and after first card 302px; no regression.
  - All four scenarios HTTP 200, 3/3 product links from canonical site, all card/track ratios exactly 1.000 (displayed 3 decimals). The full run also reran fixture, one-width overlay and source-owner CLS trace successfully, with screenshots and JSON artifact.
- Interim runs `#39970552873` and `#39970560027` initially failed because their *test* incorrectly required `float/clear:none` on all mobile cards, including WooCommerce's even-child `!important` rule. The width/links were already correct for every viewport in those failed logs. The test gate was narrowed to its intended user-visible width invariant, and the float/clear values are retained in the output for review. The final exact-head run above succeeded.

## IMPORTANT — independent ConvertFlow CLS blocker

- Read-only [CLS run #39969458015](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39969458015) shows a **797px vertical product-grid jump** when ConvertFlow Core initializes `choiceguide-filter--enhanced` around 3–4s, collapsing the initially expanded filter panel. CLS current **0.67714**, browser CSS card-width candidate alone **0.72502** (not a controlled head-to-head comparison of general performance).
- Plugin `product-filter.js` adds enhancement state, plugin CSS hides the panel after enhancement; the lifecycle and first-paint semantics belong to ConvertFlow.
- Detailed owner handoff: [QA PR #505](https://github.com/truongdinhnamaz/aznet-theme/pull/505), file `docs/evidence/RQA_CONVERTFLOW_FILTER_FIRST_PAINT_CLS_20261023.md`. No Theme-owned CLS fix is claimed, no plugin source/private storage changed.

## QA gates, rollback and exact next

- **PASS:** L1/L2 CSS cascade RED/GREEN, live browser-local L4 four-width candidate geometry and product-link integrity. Narrow scope only.
- **UNKNOWN:** production post-deploy Theme L3/L4 because **no deployment happened**; whole-site mobile keyboard test, visual comparison, provider integration L5, release/acceptance L6.
- **Rollback candidate:** revert only the added `columns-*` rule on the work branch or restore prior `woocommerce-archive.css` from canonical v1.3.92 release. No production change exists yet to roll back. Keep backup Theme and hosting backup status independently verified at deployment gate.
- **EXACT NEXT:** obtain approval-gated Theme release/deployment plan for the **scoped CSS candidate only**, not the entire unreleased `main` 1.3.96. Produce an exact candidate package and staging/preview verification prior to touching RQA production, then run fresh production multi-width/keyboard regression after release. ConvertFlow filter CLS and quote/project accessibility issues remain provider-owned blockers and must not be patched by private Theme heuristics.


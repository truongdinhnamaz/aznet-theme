# RQA — ConvertFlow mobile filter CLS source attribution (2026-10-23)

**Site:** https://remquocanh.vn/rem-cuon/  
**Purpose:** live read-only L4 cause attribution, **not** a request to edit the ConvertFlow source in AZnet Theme.  
**Theme baseline:** live AZnet Theme 1.3.92; Theme PR #506 is an independently verified presentation-only mobile product-card width fix, not a CLS fix.

## Reproduced RED with browser

- [GitHub Action run #39961771437](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39961771437), artifact `rqa-live-l4-element-trace`: mobile Chromium 390 px with 4x CPU and slow network found archive CLS **0.67714**, dominated by one layout shift at roughly 4.3 seconds. Shift source included the WooCommerce `ul.products` and product cards.
- [GitHub Action run #39969458015](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39969458015), artifact `rqa-woo-mobile-grid` ID `12508880407`: more precise DOM and CSS/JS timing at mobile 390 px.
- **Current production (no CSS override):** product grid exists during HTML parsing. At ~2295 ms it has `x=18, y=1030, width=354 px`; 5 DOM mutation records in total, of which 4 are ordinary HTML insertion while parsing. At **3806 ms**, the only remaining filter mutation is adding class `choiceguide-filter--enhanced` to `#choiceguide-filter-auto-1001`. At that moment the grid moves from **y=1030 to y=233** while retaining width/height. The resulting ~797 px vertical movement generates a CLS event at 3820 ms worth **0.677119**. `DOMContentLoaded` follows at ~3926 ms.
- Source timing: `product-filter.css` finished around **1794 ms**; `product-filter.js` finished around **3797 ms**, immediately before its enhancement class was added. Thus this is not an image-resize effect and not a Theme CSS Grid width regression.
- **Browser-local CSS candidate for Theme card width:** card/grid-track ratio improves from 0.48 to 1.0, but the filter class toggles independently at ~3071 ms and the grid again moves **y=1030 to y=233**. Measured CLS **0.72502** in that second throttled lab load. **Do not claim the Theme card-width fix resolves CLS.** The difference between 0.677 and 0.725 is not a controlled causal A/B performance result.

## Source-owner attribution

Read-only inspection of installed ConvertFlow Core:

- `plugins/choiceguide/assets/js/product-filter.js`, around line **237**: `initInstance(instance)` adds `choiceguide-filter--enhanced` after the page initially renders.
- `plugins/choiceguide/assets/css/product-filter.css`, around lines **261–268**: on mobile (`max-width: 767px`) the filter panel is hidden by `.choiceguide-filter.choiceguide-filter--enhanced .choiceguide-filter__panel:not(.is-open) { display:none; }`.
- Initial HTML contains the entire filter panel above the WooCommerce product grid. It is laid out expanded **before** ConvertFlow JS adds the enhanced class, causing the ~797 px collapse seen in the browser trace.
- This front-end filter lifecycle belongs to **ConvertFlow**, not AZnet Theme. The Theme's role is a presentation consumer, and this project does not own ConvertFlow init logic or plugin-private content/state.

## Consequences and gates

**PASS** — L4 element-level origin verified through Chromium DOM mutation timing + layout-shift record and installed plugin source, with the two changes occurring together.  
**BLOCKED** — No plugin-side patch, fix, release, production deployment or L5 end-to-end validation. Do not claim CLS fixed.  
**POSSIBLE provider-owned remediation to evaluate separately:** establish the intended mobile collapsed state *before first paint*, while retaining keyboard access, touch actions, no-JS fallback and accessible filter content. This is a provider implementation/contract decision, not authorization for Theme-side direct CSS hide or JS override.  
**Theme PR #506** remains valid for independent mobile card-width CSS cascade correction. RED [#39964029501](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39964029501) fails exactly on 48% card; GREEN [#39964271277](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39964271277) passes 390/768/1280 px; live read-only CSS overlay [#39965021161](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39965021161) confirms 169.9 -> 354 px on the actual site.

## Exact next

1. Maintain Theme PR #506 as a bounded, release-gated **presentation fix only**. No Theme production publish until a reviewed release path and proper site backup/QA authorization.
2. Handoff this dated CLS evidence to ConvertFlow's owning project, requesting a *public, versioned* filter initialization/first-paint contract or a provider-side fix; do not import or modify ConvertFlow source in `aznet-theme`.
3. Continue independent Theme-owned browser responsiveness/keyboard regression for archive cards and document PASS by layer. Keep provider accessibility and FAQ placeholders BLOCKED until their owner exposes a supported editing/fix path.

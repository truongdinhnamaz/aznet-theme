# Woo L4 Browser / Visual / A11y Verification Evidence

Date: 2026-09-03

## Scope

Verification of the Theme-owned WooCommerce presentation surfaces against the production selector fix candidate in PR #12.

This checkpoint covers L4 only:
- real Chromium browser rendering;
- desktop 1440 x 1000;
- mobile 390 x 844;
- horizontal overflow;
- native Woo landmark/control visibility;
- keyboard-visible focus;
- axe critical/serious findings;
- manual screenshot review.

It does not claim L5 integration or L6 release PASS.

## Production fix candidate

- PR: #12 `fix: bind Woo product presentation to AZnet main shell`
- Branch: `fix/w2-product-runtime-selector`
- Candidate SHA: `2f04082b0c1b3a864c9e91434af94275796ad09b`
- Production delta: `assets/css/components/woocommerce-product.css` only
- Regression test delta: `tests/offline/w2-product-css-contract.php`

Root cause found by the first L4 run:

Woo Single Product markup is rendered inside `<main class="aznet-theme-main">`, while the W2 Product stylesheet had been scoped to `.site-main`. The dead selector prevented the Theme's product grid, float reset, price color and focus presentation from applying in real runtime. This produced a zero-height/overlapping desktop product composition and a serious mobile price color-contrast finding.

TDD fix:
- RED: W2 product CSS contract reported `missing: .aznet-theme-main`.
- GREEN: all product stylesheet shell selectors changed from `.site-main` to `.aznet-theme-main`; no layout values, Woo semantics, commerce logic or other production files were changed.
- Fresh exact-byte W2 CSS contract: PASS.

## L4 successor verification branch

- Draft PR: #13 `test: verify Woo L4 against product shell selector fix`
- Branch: `test/woo-l4-browser-product-fix`
- Verified test-infrastructure head: `bc6143b089acf3e6e4faf6f15435ad8f81a5fcad`
- Base: PR #12 candidate `2f04082b0c1b3a864c9e91434af94275796ad09b`

Delta from PR #12 is test-only:
- `.github/workflows/woo-l4-browser.yml`
- `tests/browser/woo-l4-browser.mjs`
- `tests/offline/woo-l4-browser-workflow-contract.php`
- `tests/runtime/woo-l3-fixtures.php`

No additional production Theme path was changed by the L4 verification branch.

## Runtime

GitHub Actions:
- Workflow: `Woo L4 Browser Visual A11y`
- Run: `33734871022`
- Job: `100582967332`
- Conclusion: SUCCESS

Runtime report:
- WordPress: 7.1
- PHP: 8.1.34
- WooCommerce: 11.0.1
- AZnet Theme: 0.1.0-alpha.7
- Node: v22.23.2
- Browser: Playwright Chromium 140.0.7339.16 / build v1187

## Automated browser results

All 10 cases PASS:

Desktop 1440 x 1000:
- Product: PASS
- Archive / Shop: PASS
- Cart: PASS
- Checkout: PASS
- My Account: PASS

Mobile 390 x 844:
- Product: PASS
- Archive / Shop: PASS
- Cart: PASS
- Checkout: PASS
- My Account: PASS

For every case:
- HTTP/browser surface rendered successfully;
- expected native Woo landmark/control was visible;
- horizontal overflow was `0px`;
- keyboard Tab reached an element inside `<main>` with a visible outline/box-shadow indicator;
- axe blocking violations (`critical`, `serious`) = 0.

The Checkout cases used the same browser context after a real public Woo `?add-to-cart=<PRODUCT_ID>` flow. Woo empty-cart redirect behavior was not disabled or bypassed.

## Axe evidence

Ten axe JSON reports were inspected independently after artifact download:
- `desktop-account.json`: blocking=0
- `desktop-archive.json`: blocking=0
- `desktop-cart.json`: blocking=0
- `desktop-checkout.json`: blocking=0
- `desktop-product.json`: blocking=0
- `mobile-account.json`: blocking=0
- `mobile-archive.json`: blocking=0
- `mobile-cart.json`: blocking=0
- `mobile-checkout.json`: blocking=0
- `mobile-product.json`: blocking=0

## Manual screenshot review

All 10 full-page screenshots were opened and visually reviewed after the successful run.

Desktop:
- Product: product wrapper now has normal height; image/summary/CTA are contained above the footer; the first-run footer overlap is gone; focused `View cart` control is visibly outlined.
- Archive: result/order controls and product card are contained; no overlap or clipping.
- Cart: product table and cart totals remain contained; checkout CTA is visible; no horizontal clipping.
- Checkout: billing/additional/order/payment sections remain visible and contained; no overlap or clipping. Large whitespace is fixture/content composition, not lost or overlapped controls.
- My Account: login form is contained and the focused username field has a clear visible outline.

Mobile:
- Product: single-column product composition stays within the 390px viewport; image, price, quantity, Add to cart and category remain visible; no footer overlap.
- Archive: ordering control and product card fit the viewport.
- Cart: native cart content reflows into a stacked mobile presentation; totals and checkout CTA fit the viewport.
- Checkout: fields, order summary, native no-payment-method fixture notice and Place order control remain contained without horizontal clipping.
- My Account: login form, controls and focus indicator remain contained and readable.

No screenshot showed the original Product overlap defect, horizontal page overflow, clipped primary controls or destructive visual collision.

## Artifact

- Artifact name: `woo-l4-browser-evidence`
- Artifact ID: `9885371896`
- GitHub digest: `sha256:540dfc5487052222116f9fd98ce977b5ec0e32daa01f9fb204e6120343d2fc10`
- Independently downloaded ZIP SHA-256: `540dfc5487052222116f9fd98ce977b5ec0e32daa01f9fb204e6120343d2fc10`
- Files uploaded: 25
- Screenshots: 10/10
- Axe reports: 10/10
- Browser summary: 10 cases, 10 PASS

## Result

PASS — Woo presentation L4 Browser / Visual / A11y for the PR #12 product selector fix candidate.

This PASS is specific to the isolated WordPress + WooCommerce runtime and the five tested Woo surfaces at the two approved viewports. It does not prove external-plugin coexistence/integration (L5), production deployment, or release completion (L6).

## Remaining gates

- PR #12 is not merged; merge to `main` requires explicit owner approval.
- PR #13 is test-only/draft and must not be merged automatically.
- L5 integration remains UNKNOWN / contract-gated.
- L6 completion/release remains UNKNOWN.
- E5-C RootProfile remains separately BLOCKED on the external current-surface contract.

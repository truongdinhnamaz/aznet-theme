# W5 Woo Checkout Shell — Local Verification

Date: 2026-09-03
Repository: `truongdinhnamaz/aznet-theme`
Branch: `work/w5-woo-checkout-shell`
Base checkpoint: W4 branch head `2453ea628bb28c2e85a2d34f4d022b690ae363a5`
Verified pre-evidence W5 head: `d2bd479223ff8c35f54bccd5e2fb96950e3e6bc3`

## Scope

W5 adds Theme-owned presentation for WooCommerce Checkout only, covering native classic Checkout and Woo Checkout Block presentation targets.

WooCommerce remains authoritative for billing/shipping fields and values, validation, shipping/tax/discount/total calculation, payment gateways, payment processing, order creation, notices and checkout endpoint state. ConvertFlow remains untouched.

No `woocommerce/` template override, JavaScript, custom AJAX, checkout-field mutation, payment gateway mutation, order creation logic or direct Woo storage access is introduced.

## TDD evidence

### Task 1 — checkout-only asset gate

RED observed before production helper code:

```text
missing checkout helper
```

Minimal GREEN added:

- `inc/theme/woocommerce-checkout.php`;
- exactly one bootstrap require;
- exactly one checkout-only enqueue block in `inc/theme/assets.php`.

GREEN/lint output:

```text
PASS: W5 checkout-only asset scope
No syntax errors detected in inc/theme/woocommerce-checkout.php
No syntax errors detected in inc/theme/assets.php
No syntax errors detected in inc/theme/bootstrap.php
```

### Task 2 — classic + Block Checkout CSS

RED observed before stylesheet creation:

```text
missing checkout CSS
```

The draft plan initially proposed a raw forbidden substring `order:`. Before production CSS was written, this was corrected in the actual contract to a CSS-property regex because raw `order:` would falsely match valid `border:` declarations. This was a test-design correction, not a production workaround.

Minimal GREEN added `assets/css/components/woocommerce-checkout.css` with:

- classic `form.checkout` two-column desktop intent;
- `#customer_details`, `#order_review_heading`, `#order_review`, `#payment` presentation;
- Woo Checkout Block component presentation targets;
- one-column mobile intent at 767px;
- Theme-token focus/control/card styling;
- no sticky CTA, hiding, forced horizontal scroll or CSS `order` property.

GREEN output:

```text
PASS: W5 checkout CSS contract
```

## Retained W4 regression / debugging evidence

Adding the W5 enqueue block invalidated the retained W4 asset harness because that harness directly required `assets.php` without loading the new Checkout helper.

Reproduced failure:

```text
PHP Fatal error: Uncaught Error: Call to undefined function AZnet\Theme\should_enqueue_woocommerce_checkout_assets()
```

Root cause: test-harness dependency drift. Production bootstrap already loads `woocommerce-checkout.php` before `assets.php`.

Fix: on the W5 branch only, `tests/offline/w4-cart-asset-scope-contract.php` adds exactly one require for `woocommerce-checkout.php` before `assets.php`. No defensive `function_exists()` or other production workaround was added.

## Fresh closure verification

Command:

```bash
bash scripts/verify-w5.sh
```

Fresh output:

```text
PASS: W5 checkout-only asset scope
PASS: W5 checkout CSS contract
PASS: W5 Checkout ownership / no-payment-order-behavior / no-override contract
PASS: W5 offline contracts
PASS: W4 cart-only asset scope
PASS: W4 Cart ownership / no-commerce-behavior / no-override contract
PASS: retained W4 invalidated regression subset
PASS: W5 changed/new production PHP lint 3/3
```

## Exact-byte provenance

The verifier workspace was checked against Git blob SHA values from `work/w5-woo-checkout-shell`. Relevant exact blobs:

- `inc/theme/woocommerce-checkout.php` `a1dd77670074a2270c485eda07325f23a43bd0c9`
- `inc/theme/assets.php` `574dba76dfab37fbf333b871565a9227b472acbb`
- `inc/theme/bootstrap.php` `cbb81e1ab7a1a0a1209fa4187f59f06ff6816fc8`
- `assets/css/components/woocommerce-checkout.css` `93be71ecf682634ebeadc5ac93798f96a3809446`
- W5 asset contract `7636b06342a4d0e19264309137745cdb7d938a5d`
- W5 CSS contract `4594c4ee256c3ed672a4ee4cc1bf617a3cd20197`
- W5 ownership contract `57c0d524bcfd7db0448dbf39c72b70a7eff7ba6e`
- retained W4 asset contract `9e35ef7d38aea3abc23caa8cd6cfa72c5bcf1763`
- retained W4 ownership contract `5c46719790a6763c7e3672c65b78c21f64f1ae21`
- retained Cart helper `ef99e7844c48bf1428f1041a83e7c825ef9525e7`
- retained Cart CSS `46fa2402f1a2fe1cbab8a09f0eb1f312c94b7752`
- `scripts/verify-w5.sh` `7ffe6be1eaae226067f6441fee1a6593dc00cf39`

## Provenance / delta

GitHub compare from W4 base `2453ea628bb28c2e85a2d34f4d022b690ae363a5` to the W5 pre-evidence head confirmed the W5 production delta is exactly:

- ADD `assets/css/components/woocommerce-checkout.css`
- ADD `inc/theme/woocommerce-checkout.php`
- MODIFY `inc/theme/assets.php` `+9/-0`
- MODIFY `inc/theme/bootstrap.php` `+1/-0`

The only retained earlier test changed is `tests/offline/w4-cart-asset-scope-contract.php` `+1/-0` to load the new helper dependency.

No Woo classifier, Cart helper/CSS, archive/product presentation, RootProfile integration, template or domain source changed.

## PASS by layer

- L0 Source/State: PASS for W5 scope, approved Checkout Design Packet, ownership and exact W4 base.
- L1 Static: PASS for W5 ownership/no-payment-order-behavior/no-override/no-JS boundary and changed/new PHP lint 3/3.
- L2 Contract/TDD: PASS for checkout-only asset behavior and classic+Block Checkout CSS contract, with affected W4 asset/ownership regression subset PASS.

## Not proven

- L3 real WordPress/WooCommerce runtime: NOT PROVEN.
- L4 browser/responsive/visual/a11y: NOT PROVEN.
- L5 Woo/ConvertFlow ecosystem integration: NOT PROVEN.
- L6 completion/release: NOT PROVEN.

## Source-document policy

No authoritative AZT source document was revised for this implementation progress. W5 follows the approved source/governance; progress is recorded only in code, tests, PR/evidence and this checkpoint.

## Next

Open a stacked W5 PR against W4. Do not merge automatically. The next new Woo surface (My Account) requires its own Design Packet approval before production code.

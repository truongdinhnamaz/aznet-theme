# W4 Woo Cart Shell — Local Verification

Date: 2026-09-03
Repository: `truongdinhnamaz/aznet-theme`
Branch: `work/w4-woo-cart-shell`
Base checkpoint: W3 head `2e387831dcb807d60bc879f4568f2422a36ac05c`
Verified pre-evidence W4 head: `43b7e98662e3ef1fb1b32d1816fa05ddbb32fe03`

## Scope

W4 adds Theme-owned presentation for WooCommerce Cart only, covering native classic Cart and Woo Cart Block markup.

WooCommerce remains owner of line items, quantity, coupon validity, price/subtotal/total, tax/shipping, stock validation, notices, cart mutations and checkout transition. ConvertFlow remains owner of Product Journey / Filter / Fit / Fast Conversion.

No `woocommerce/` template override, JavaScript, custom AJAX, price calculation, cart mutation, direct Woo storage access or ConvertFlow behavior is introduced.

## TDD evidence

### Task 1 — cart-only asset gate

RED observed before production code:

```text
missing cart helper
exit=1
```

Minimal GREEN:

- add `inc/theme/woocommerce-cart.php`;
- add exactly one bootstrap require;
- add one cart-only enqueue block in `inc/theme/assets.php`.

GREEN/lint:

```text
PASS: W4 cart-only asset scope
No syntax errors detected in inc/theme/woocommerce-cart.php
No syntax errors detected in inc/theme/assets.php
No syntax errors detected in inc/theme/bootstrap.php
```

### Task 2 — classic + Block Cart CSS

RED observed:

```text
missing cart CSS
exit=1
```

Minimal GREEN added `assets/css/components/woocommerce-cart.css` with classic Cart + Cart Block selectors, Theme tokens, mobile wrapping and visible focus treatment.

GREEN:

```text
PASS: W4 Cart CSS contract
```

## Regression/debugging evidence

Adding the Cart enqueue block invalidated the retained W3 archive asset harness because that harness directly required `assets.php` without loading the new Cart helper.

Observed failure:

```text
Call to undefined function AZnet\Theme\should_enqueue_woocommerce_cart_assets()
```

Root cause: test-harness dependency drift. Production bootstrap already loads `woocommerce-cart.php` before `assets.php`.

Fix: on the W4 branch only, `tests/offline/w3-archive-asset-scope-contract.php` adds one require for `woocommerce-cart.php` before `assets.php`. No defensive production workaround was added.

## Fresh closure verification

Command:

```bash
bash scripts/verify-w4.sh
```

Fresh output:

```text
PASS: W4 cart-only asset scope
PASS: W4 Cart CSS contract
PASS: W4 Cart ownership / no-commerce-behavior / no-override contract
PASS: W4 offline contracts
PASS: W3 archive-only asset scope
PASS: W3 archive ownership / no-query / no-override contract
PASS: retained W3 invalidated regression subset
PASS: W4 changed/new production PHP lint 3/3
```

The verifier uses the W4 candidate bytes reconstructed from the branch changes. The affected W3 asset/ownership subset is rerun because `assets.php` / `bootstrap.php` changed. Unchanged W1/E5 behavior is not rerun merely to log progress.

## Provenance / delta

GitHub compare from W3 base `2e387831dcb807d60bc879f4568f2422a36ac05c` to W4 pre-evidence head shows the production delta is exactly:

- ADD `assets/css/components/woocommerce-cart.css`
- ADD `inc/theme/woocommerce-cart.php`
- MODIFY `inc/theme/assets.php` `+9/-0`
- MODIFY `inc/theme/bootstrap.php` `+1/-0`

The only retained earlier test changed is `tests/offline/w3-archive-asset-scope-contract.php` `+1/-0` to load the new helper dependency.

No Woo classifier, Product/Archive presentation source, RootProfile source, Woo query/domain source or authoritative AZT document changed.

## PASS by layer

- L0 Source/State: PASS for W4 scope, ownership and exact W3 base.
- L1 Static: PASS for no-commerce-behavior/no-storage/no-JS/no-template-override boundary and changed/new PHP lint 3/3.
- L2 Contract/TDD: PASS for cart-only asset behavior and classic/Block Cart CSS contract, with affected W3 regression subset PASS.

## Not proven

- L3 real WordPress/WooCommerce runtime: NOT PROVEN.
- L4 browser/responsive/visual/a11y: NOT PROVEN.
- L5 Woo/ConvertFlow ecosystem integration: NOT PROVEN.
- L6 completion/release: NOT PROVEN.

## Source-document policy

No authoritative AZT source document was revised for this implementation progress. W4 follows the approved source/governance; progress is recorded only in code, tests, PR/evidence and this checkpoint.

## Next

Open a stacked W4 PR against W3. Do not merge automatically. The next new Woo surface requires its own Design Packet approval before production code.

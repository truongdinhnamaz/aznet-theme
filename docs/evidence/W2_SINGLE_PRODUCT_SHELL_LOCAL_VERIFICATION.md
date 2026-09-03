# W2 Single Product Shell — Local Verification

Date: 2026-09-03

## 1. Scope / ownership

W2 implements AZnet Theme presentation for WooCommerce Single Product only.

Theme-owned:
- product-only asset eligibility;
- Theme-owned Single Product CSS;
- desktop/mobile composition of native Woo markup;
- focus/readability presentation.

Not Theme-owned:
- product identity, price, stock, SKU, variation, purchasability;
- notices, add-to-cart/cart/order/checkout/account state;
- Product Journey/Filter/Fit/Fast Conversion.

W2 adds no `woocommerce/` template override, no JS, no sticky Add to Cart, no Woo storage/private access and no ConvertFlow behavior.

## 2. Baseline / branch

- W1 evidence head / W2 parent baseline: `8d29c08ea230cf3544849c48bb97fa1335c845f9`
- Branch: `work/w2-single-product-shell`
- Verified source head before this evidence commit: `006dfc65f86167e8fa75d2a8bc11467848c405f2`
- W2 Design Packet: `docs/superpowers/specs/2026-09-03-w2-single-product-shell-design.md`
- W2 plan: `docs/superpowers/plans/2026-09-03-w2-single-product-shell.md`
- Verification amendment: `docs/superpowers/plans/2026-09-03-w2-single-product-shell-verification-amendment.md`

## 3. TDD evidence

### Task 1 — Product-only asset gate

RED was observed before implementation: the contract failed because `inc/theme/woocommerce-product.php` / `should_enqueue_woocommerce_product_assets()` did not exist.

Minimal GREEN:
- add `inc/theme/woocommerce-product.php`;
- add one bootstrap require after the public Woo adapter;
- enqueue `aznet-theme-woocommerce-product` only when normalized W1 surface is `product`.

Fresh exact-byte contract result:

```text
PASS: W2 product-only asset scope
```

The contract covers `product` as true/enqueued and `archive|cart|checkout|account|null` as false/not-enqueued.

### Task 2 — Native Woo Single Product CSS

RED was observed before CSS creation:

```text
missing product CSS
```

Minimal GREEN adds `assets/css/components/woocommerce-product.css` scoped to native Single Product markup:
- desktop grid uses `minmax(0, 1.25fr) minmax(0, 1fr)`;
- mobile at `max-width: 767px` becomes one column;
- gallery/summary use `min-width: 0` and no float layout;
- tabs/related/upsells span the full grid;
- visible `:focus-visible` treatment uses Theme tokens;
- no sticky CTA or hidden commerce-information rule.

Fresh exact-byte contract result:

```text
PASS: W2 product CSS contract
```

### Task 3 — Ownership gate

The first draft of the static test produced a false positive because a naive `_woocommerce_` substring also matched the valid helper name `should_enqueue_woocommerce_product_assets()`. Investigation confirmed the occurrence was a function name, not a storage key. The test was corrected to reject quoted `_woocommerce_*` storage-key literals rather than a blind substring. Production code was not changed to satisfy the false test.

Fresh exact-byte result:

```text
PASS: W2 product ownership / no-override contract
```

## 4. Retained regression chain

The final W2 verifier is `scripts/verify-w2.sh` (Git blob `6b4e18c230f74527ea171d2a974cf26afa06313d`). Its retained chain delegates W1/E5-B checks to the already-established verifier instead of rerunning the same checks twice.

A reconstructed verification workspace was built from the exact Git blob bytes of the W2 production/test paths and retained W1/E5-B source/tests. Blob identity was checked for W2 helper, bootstrap, assets, CSS, all three W2 contracts, `verify-w1.sh` and `verify-e5b.sh` before execution.

Fresh verifier output:

```text
PASS: W2 product-only asset scope
PASS: W2 product CSS contract
PASS: W2 product ownership / no-override contract
PASS: W2 offline contracts
PASS: W1 WooCommerce absent capability contract
PASS: W1 WooCommerce normalized surface contract
PASS: W1 WooCommerce generic asset scope contract
PASS: W1 WooCommerce ownership / no-override static contract
PASS: W1 offline contracts
PASS: E5 current-surface consumer contract
PASS: E5 current-surface payload-to-model adapters
PASS: E5 dormant current-surface dispatcher
PASS: E5-B ownership / no-takeover static contract
PASS: E5-B offline contracts
PASS: production PHP lint 9/9
PASS: retained E5-B verifier
PASS: production PHP lint 9/9
PASS: retained W1 -> E5-B + production lint chain
```

The `9/9` lint count is the reconstructed exact-byte verification subset and is NOT represented as a new full-repository lint. W1 already had fresh full production PHP lint `23/23 PASS` before W2. W2 changes/adds only three PHP production paths (`inc/theme/woocommerce-product.php`, `inc/theme/assets.php`, `inc/theme/bootstrap.php`), and all three received fresh PHP lint PASS after the final minimal-diff correction. W1→W2 compare proves other production PHP paths are unchanged.

## 5. Exact production delta vs W1

GitHub compare `8d29c08...` → `work/w2-single-product-shell` after cleanup shows exactly four production paths:

- ADD `assets/css/components/woocommerce-product.css` — 105 lines;
- ADD `inc/theme/woocommerce-product.php` — 21 lines;
- MODIFY `inc/theme/assets.php` — +9 / -0;
- MODIFY `inc/theme/bootstrap.php` — +1 / -0.

There is no production file removal, no `woocommerce/` template directory and no temporary workflow in the final candidate tree.

Key Git blob identity:
- `inc/theme/woocommerce-product.php`: `6a8c378d715e72a240a59129a60a90c3ee422c59`
- `inc/theme/bootstrap.php`: `aa4b519515810dc84b3f888bd9cf5bf2d006c110`
- `inc/theme/assets.php`: `8f94fc21e2fd642387d25f1f4d35434f18e34421`
- `assets/css/components/woocommerce-product.css`: `e9392560d75dd555ef786eabec9546669685033f`
- `tests/offline/w2-product-asset-scope-contract.php`: `9abb2c2c6419352a06c3f1b14b328acb9ca96df8`
- `tests/offline/w2-product-css-contract.php`: `c666d1bce0c793a064e32fc02f53fbc0b92b7edb`
- `tests/offline/w2-product-ownership-static-contract.php`: `4000aa435ebbb9d02ff138379f94bf6d08b0e9b9`

## 6. Evidence depth

**PASS: W2 local L0–L2 only.**

Not proven here:
- L3 real WordPress/WooCommerce runtime;
- L4 desktop/tablet/mobile browser visual and accessibility behavior;
- L5 ConvertFlow/Woo coexistence or broader integration matrix;
- L6 release/package/merge readiness.

E5-C remains externally blocked and is unchanged by W2. W2 does not unlock E5-D, E6/E7 or Milestone F.

## 7. Recovery / next

Rollback: revert W2 branch commits; W1 capability/surface classifier and generic asset boundary remain the parent baseline.

Next Theme-owned slice after W2 local closure is **W3 — additional Woo surfaces**, starting with a separate Design Packet/approval. Recommended first W3 surface is Shop/Product Archive/Category/Tag before transactional Cart/Checkout/Account presentation.

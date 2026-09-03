# W3 Woo Archive Shell — Local Verification

Date: 2026-09-03
Repository: `truongdinhnamaz/aznet-theme`
Branch: `work/w3-woo-archive-shell`
Base checkpoint: W2 branch head `da611e972c2102a4b2c52b55880feb0cc85c7532`
Verified pre-evidence W3 head: `653a58045b9464cc3c77772a1d30750203e3c283`

## Scope

W3 adds Theme-owned presentation for WooCommerce Shop / Product Archive / Category / Tag surfaces only.

WooCommerce remains owner of catalogue query, taxonomy, ordering, product/price/stock/rating/purchasability/add-to-cart facts and behavior. ConvertFlow remains owner of Product Journey / Filter / Fit / Fast Conversion.

No `woocommerce/` template override, custom query, filter engine, JavaScript or domain store is introduced.

## TDD evidence

### Task 1 — archive-only asset gate

RED observed before production code:

- `w3-archive-asset-scope-contract.php` failed because `inc/theme/woocommerce-archive.php` did not exist.

Minimal GREEN:

- add `inc/theme/woocommerce-archive.php`;
- add exactly one bootstrap require;
- add one archive-only enqueue block in `inc/theme/assets.php`.

GREEN output:

```text
PASS: W3 archive-only asset scope
No syntax errors detected in inc/theme/woocommerce-archive.php
No syntax errors detected in inc/theme/assets.php
No syntax errors detected in inc/theme/bootstrap.php
```

### Task 2 — archive CSS

RED observed:

```text
missing archive CSS
```

Minimal GREEN added `assets/css/components/woocommerce-archive.css` with native Woo selectors and 4/3/2/1 responsive grid intent.

GREEN output:

```text
PASS: W3 archive CSS contract
```

## Regression/debugging evidence

Adding the archive enqueue block initially invalidated the retained W2 asset harness because that harness directly required `assets.php` without loading the new helper first.

Observed failure:

```text
Call to undefined function AZnet\Theme\should_enqueue_woocommerce_archive_assets()
```

Root cause: test harness dependency drift. Production bootstrap already loaded the archive helper before `assets.php`.

Fix: on the W3 branch only, `tests/offline/w2-product-asset-scope-contract.php` now requires `woocommerce-archive.php` before `assets.php`. No defensive production workaround was added.

Retained W2 invalidated subset then passed:

```text
PASS: W2 product-only asset scope
PASS: W2 product ownership / no-override contract
```

## Fresh closure verification

Command:

```bash
bash scripts/verify-w3.sh
```

Fresh output:

```text
PASS: W3 archive-only asset scope
PASS: W3 archive CSS contract
PASS: W3 archive ownership / no-query / no-override contract
PASS: W3 offline contracts
PASS: W2 product-only asset scope
PASS: W2 product ownership / no-override contract
PASS: retained W2 invalidated regression subset
PASS: W3 changed/new production PHP lint 3/3
```

The workspace files used by the verifier were checked against Git blob SHA values from the W3 branch. Relevant exact blobs included:

- `inc/theme/content-shell.php` `6871e207581652cb8f5af94a0e8a8081aea62323`
- `inc/theme/woocommerce-product.php` `6a8c378d715e72a240a59129a60a90c3ee422c59`
- `inc/theme/woocommerce-archive.php` `84ed63dd5a9c62b4d24fb29e260698f9c182099d`
- `inc/theme/assets.php` `4a361f8e0ea042d83e5491690089d468c8b6d4a1`
- `inc/theme/bootstrap.php` `bd5ea7d5550be6df5ac70215e935aaae4f2f9956`
- `assets/css/components/woocommerce-product.css` `e9392560d75dd555ef786eabec9546669685033f`
- `assets/css/components/woocommerce-archive.css` `87185bb7088cc7d986db27db644c5f9e400d046c`
- W3 asset test `243d873b9fa8d7d7efe13a7bd29a3693a9522fc3`
- W3 CSS test `e1ad9603c765af1c6b34f12902b0b0a803c07bc3`
- W3 ownership test `4e893cb0c6fc8ba8a5596eae24a0900e02e3ce35`
- retained W2 asset test `b284d5ea796d2414a06b1992e038d21898bb0585`
- retained W2 ownership test `4000aa435ebbb9d02ff138379f94bf6d08b0e9b9`
- `scripts/verify-w3.sh` `7a0313a94bbdbc31cba958bcc7d3e1093c2ac90e`

## Provenance / delta

GitHub compare from W2 base `da611e972c2102a4b2c52b55880feb0cc85c7532` to W3 pre-evidence head showed the W3 production delta is exactly:

- ADD `assets/css/components/woocommerce-archive.css`
- ADD `inc/theme/woocommerce-archive.php`
- MODIFY `inc/theme/assets.php` `+9/-0`
- MODIFY `inc/theme/bootstrap.php` `+1/-0`

The only retained earlier test changed is the W2 asset harness `+1/-0` to load the new helper dependency.

No Woo classifier, product helper/CSS, RootProfile consumer/model/dispatcher, template or domain source changed.

## PASS by layer

- L0 Source/State: PASS for W3 scope, ownership and exact W2 base.
- L1 Static: PASS for W3 ownership/no-query/no-override/no-JS boundary and changed/new PHP lint 3/3.
- L2 Contract/TDD: PASS for archive-only asset behavior and responsive CSS contract, with invalidated W2 asset/ownership regression subset PASS.

## Not proven

- L3 real WordPress/WooCommerce runtime: NOT PROVEN.
- L4 browser/responsive/visual/a11y: NOT PROVEN.
- L5 Woo/ConvertFlow ecosystem integration: NOT PROVEN.
- L6 completion/release: NOT PROVEN.

## Source-document policy

No authoritative AZT source document was revised for this implementation progress. W3 follows the already-approved source/governance; progress is recorded only in code, tests, PR/evidence and this checkpoint.

## Next

Open a stacked W3 PR against the W2 branch. Do not merge automatically. The next new Woo surface requires its own Design Packet approval before production code.

# W6 Woo My Account Shell — Local Verification

Date: 2026-09-03
Repository: `truongdinhnamaz/aznet-theme`
Branch: `work/w6-woo-account-shell`
Base checkpoint: W5 branch head `cd3bb43ce7c769941c98fa4e976977f2b9e03552`
Verified pre-evidence W6 head: `e4d2a7b0a1837b6c9335b042e8e701ef955aa8d3`

## Scope

W6 adds Theme-owned presentation for WooCommerce My Account surfaces only.

WooCommerce remains owner of login/register/logout, account identity, endpoint routing, orders, downloads, addresses, payment methods, account details, validation/nonces and all account mutations. ConvertFlow is untouched.

No `woocommerce/` template override, custom endpoint, JavaScript, auth/account-data logic or direct Woo storage access is introduced.

## TDD evidence

### Task 1 — account-only asset gate

RED observed before production code:

```text
missing account helper
EXIT=1
```

Minimal GREEN added `inc/theme/woocommerce-account.php`, exactly one bootstrap require and one account-only enqueue block.

GREEN output:

```text
PASS: W6 account-only asset scope
No syntax errors detected in inc/theme/woocommerce-account.php
No syntax errors detected in inc/theme/assets.php
No syntax errors detected in inc/theme/bootstrap.php
```

### Task 2 — account CSS

RED observed before stylesheet creation:

```text
missing account CSS
EXIT=1
```

Minimal GREEN added `assets/css/components/woocommerce-account.css` with native Woo My Account selectors, two-column desktop intent, one-column mobile intent, Theme tokens and visible focus.

GREEN output:

```text
PASS: W6 account CSS contract
```

## Regression / debugging evidence

W6 adds an Account helper call to `enqueue_assets()`. The retained W5 Checkout asset harness directly included `assets.php` without loading the new helper.

Observed failure before harness correction:

```text
PHP Fatal error: Call to undefined function AZnet\Theme\should_enqueue_woocommerce_account_assets()
```

Production bootstrap already loads `woocommerce-account.php` before `assets.php`, so the root cause was retained test-harness dependency drift, not a runtime bootstrap defect.

Correction: `tests/offline/w5-checkout-asset-scope-contract.php` adds exactly one `require` for `woocommerce-account.php` before `assets.php`. No production workaround was added.

## Fresh closure verification

Command:

```bash
bash scripts/verify-w6.sh
```

Fresh output on exact Git-blob-matched candidate bytes:

```text
PASS: W6 account-only asset scope
PASS: W6 account CSS contract
PASS: W6 My Account ownership / no-auth-data-endpoint / no-override contract
PASS: W6 offline contracts
PASS: W5 checkout-only asset scope
PASS: W5 Checkout ownership / no-payment-order-behavior / no-override contract
PASS: retained W5 invalidated regression subset
PASS: W6 changed/new production PHP lint 3/3
```

## Exact-byte provenance

Local verifier workspace Git blob hashes matched the W6 branch for all changed/new production and test/verifier paths used by the closure run:

- `inc/theme/assets.php` `fc2202ee6ed15e39c28116e386632fd2b44fe066`
- `inc/theme/bootstrap.php` `881d73b9e0bd8ed97df6317d8699e67759fd98d3`
- `inc/theme/woocommerce-account.php` `fa13df2b57bbda540b46bdcd6b0e592cc6afa6f9`
- `assets/css/components/woocommerce-account.css` `62032c8192d3088e96ab5754afaac59927ba260a`
- retained W5 asset harness `eda5f66e32eef0b02a12a53844fce851d7b7426c`
- W6 asset contract `c670222df76a27de914eca531e1dd1917df3fc15`
- W6 CSS contract `62ab4f6ef379c9fb014ee42ac31dce97e4c17502`
- W6 ownership contract `0faeec37532a59160b2f577504946e6a23f1536a`
- `scripts/verify-w6.sh` `891f36d10f5b7e04c057bf1f68475f0b38f30320`

Retained immutable W5 inputs also matched the branch:

- Checkout helper `a1dd77670074a2270c485eda07325f23a43bd0c9`
- Checkout CSS `93be71ecf682634ebeadc5ac93798f96a3809446`
- W5 ownership contract `57c0d524bcfd7db0448dbf39c72b70a7eff7ba6e`

## Provenance / delta

GitHub compare from W5 base `cd3bb43ce7c769941c98fa4e976977f2b9e03552` to W6 pre-evidence head showed the W6 production delta is exactly:

- ADD `assets/css/components/woocommerce-account.css`
- ADD `inc/theme/woocommerce-account.php`
- MODIFY `inc/theme/assets.php` `+9/-0`
- MODIFY `inc/theme/bootstrap.php` `+1/-0`

The only retained earlier test changed is the W5 Checkout asset harness `+1/-0` to load the new helper dependency.

## PASS by layer

- L0 Source/State: PASS for W6 approved Design Packet, W5 base and ownership boundary.
- L1 Static: PASS for no-auth/no-account-data/no-endpoint/no-private-storage/no-JS/no-template-override boundary and changed/new PHP lint 3/3.
- L2 Contract/TDD: PASS for account-only asset behavior, responsive CSS contract and affected retained W5 regression subset.

## Not proven

- L3 real WordPress/WooCommerce runtime: NOT PROVEN.
- L4 browser/responsive/visual/a11y: NOT PROVEN.
- L5 Woo/ecosystem integration: NOT PROVEN.
- L6 completion/release: NOT PROVEN.

## Source-document policy

No authoritative AZT source document was revised for this implementation progress. W6 follows the already-approved source/governance; progress is recorded in code, tests, PR/evidence only.

## Next

Open a stacked W6 PR against `work/w5-woo-checkout-shell`. Do not merge automatically. After W6 local L0-L2, the next work should be chosen from the remaining v1.0 Woo/runtime/integration gates rather than inventing another surface without source support.

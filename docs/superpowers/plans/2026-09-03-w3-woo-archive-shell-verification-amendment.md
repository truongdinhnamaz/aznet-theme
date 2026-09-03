# W3 Verification Amendment — Preserve Uninvalidated PASS

This amendment refines Task 3 of `2026-09-03-w3-woo-archive-shell.md` to follow the project rule: do not rerun already-PASS layers unless the W3 delta invalidates their evidence.

## Why the original retained chain is too broad

W3 changes only:
- a new archive helper;
- a new archive stylesheet;
- `inc/theme/assets.php`;
- `inc/theme/bootstrap.php`;
- W3 tests/evidence plus one retained W2 harness dependency include.

It does not change Woo surface classification, W1 generic-content logic, W2 product CSS/helper, RootProfile consumer/model/dispatcher code, or any E5 domain/presentation behavior.

Therefore a full `verify-w2.sh -> verify-w1.sh -> verify-e5b.sh` rerun would repeat behavior gates whose inputs are byte-identical.

## Required W3 closure regression subset

`verify-w3.sh` must run:
1. all three W3 contracts;
2. `w2-product-asset-scope-contract.php` because `assets.php` gained another helper dependency/enqueue block;
3. `w2-product-ownership-static-contract.php` because `assets.php` and `bootstrap.php` changed;
4. fresh PHP lint for W3 changed/new production PHP paths.

W3 ownership static contract must additionally preserve the bootstrap invariants that can affect W1/E5 evidence:
- Woo integration require exactly once;
- W2 Product helper require exactly once;
- W3 Archive helper require exactly once;
- dormant RootProfile dispatcher require exactly once;
- exactly two pre-existing `add_action()` registrations;
- no `add_filter()` registration;
- no `render_current_rootprofile_surface` wiring.

## Retained PASS reasoning

W1 classifier/generic behavior and E5 consumer/model/dispatcher behavior are not rerun when `compare` evidence shows their production inputs are unchanged. Their prior PASS remains valid; only bootstrap-facing invariants are re-evaluated by W3 ownership static contract.

W2 Product CSS contract is not rerun because `woocommerce-product.css` and `woocommerce-product.php` are unchanged. W2 asset/ownership contracts are rerun because their dependency surface was invalidated by W3.

This amendment changes verification efficiency only. It does not change W3 scope, acceptance, ownership, architecture, or any authoritative AZT source document.

# W6 Woo My Account — Retained Asset Regression Closure

Date: 2026-09-03
Repository: `truongdinhnamaz/aznet-theme`
Branch: `work/w6-woo-account-shell`
Base: W5 head `cd3bb43ce7c769941c98fa4e976977f2b9e03552`

## Why this supplemental checkpoint exists

The initial W6 closure verified W6 itself plus the immediately preceding W5 asset/ownership subset. Before integration, review of the shared `inc/theme/assets.php` dependency revealed that earlier W2-W4 asset contracts also directly include `assets.php`; therefore the W6 enqueue change invalidated their test harness dependencies as well.

This file supplements, rather than rewrites, `W6_WOO_ACCOUNT_SHELL_LOCAL_VERIFICATION.md`.

## Reproduction

On the W6 production tree, the retained asset harnesses failed only because they had not loaded helpers added by later presentation slices:

```text
W2: undefined should_enqueue_woocommerce_cart_assets()
W3: undefined should_enqueue_woocommerce_checkout_assets()
W4: undefined should_enqueue_woocommerce_account_assets()
```

Production bootstrap already loads the full helper chain before `assets.php`, so these were test-harness dependency drifts, not runtime bootstrap defects.

## Test-only corrections

No production source changed during this regression closure.

- W2 product asset harness now loads Cart, Checkout and Account helpers before `assets.php`.
- W3 archive asset harness now loads Checkout and Account helpers before `assets.php`.
- W4 Cart asset harness now loads Account helper before `assets.php`.
- W5 Checkout asset harness already loads Account helper from the initial W6 closure.
- `scripts/verify-w6.sh` now runs the retained W2→W5 asset-scope chain.

## Exact-byte provenance

The local verifier workspace matched current W6 branch Git blobs for the updated retained harnesses/verifier:

- W2 asset harness `f1b16e58894576c6e3f6391c455c8a0352a2fd6a`
- W3 asset harness `8e7950adae076eec7052219811a0c2b16e618902`
- W4 asset harness `f69f0f951b0476962a7a9695e864ab7620ed2bb2`
- W5 asset harness `eda5f66e32eef0b02a12a53844fce851d7b7426c`
- W6 verifier `c0e209179c77cd38c71f5158a4aca3fd855bbc96`

W6 production blobs remain unchanged from the initial closure:

- `inc/theme/assets.php` `fc2202ee6ed15e39c28116e386632fd2b44fe066`
- `inc/theme/bootstrap.php` `881d73b9e0bd8ed97df6317d8699e67759fd98d3`
- `inc/theme/woocommerce-account.php` `fa13df2b57bbda540b46bdcd6b0e592cc6afa6f9`
- `assets/css/components/woocommerce-account.css` `62032c8192d3088e96ab5754afaac59927ba260a`

## Fresh verification

Command:

```bash
bash scripts/verify-w6.sh
```

Fresh output:

```text
PASS: W6 account-only asset scope
PASS: W6 account CSS contract
PASS: W6 My Account ownership / no-auth-data-endpoint / no-override contract
PASS: W6 offline contracts
PASS: W2 product-only asset scope
PASS: W3 archive-only asset scope
PASS: W4 cart-only asset scope
PASS: W5 checkout-only asset scope
PASS: W5 Checkout ownership / no-payment-order-behavior / no-override contract
PASS: retained W2-W5 invalidated asset regression chain
PASS: W6 changed/new production PHP lint 3/3
```

## Status by layer

- L0 Source/State: PASS for W6 local scope and W5 base.
- L1 Static: PASS for W6 ownership boundary; no production change occurred during this supplemental closure.
- L2 Contract/TDD: PASS for W6 and the full invalidated retained Woo asset chain W2→W5.
- L3-L6: remain NOT PROVEN.

## Source-document policy

No authoritative AZT source document was changed. This is implementation evidence only.

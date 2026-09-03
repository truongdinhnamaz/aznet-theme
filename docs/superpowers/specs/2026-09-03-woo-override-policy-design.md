# WooCommerce Presentation Boundary Design

**Status:** Approved 2026-09-03

## Goal

Give AZnet Theme a v1.0 WooCommerce presentation shell without transferring commerce ownership out of WooCommerce or Product Journey ownership out of ConvertFlow.

## Approved O-005 policy

1. Use WooCommerce public hooks, Blocks APIs, conditional tags and CSS first.
2. A `woocommerce/` template override is an exception, not the default. Every override must record the reason, upstream Woo template/version, affected surface, regression coverage and rollback path.
3. v1.0 presentation scope: Single Product; Shop/Product Archive/Category/Tag; Cart; Checkout; My Account.
4. WooCommerce remains authoritative for product, price, stock, variation, cart, checkout, order and account state.
5. ConvertFlow remains owner of Product Journey, Filter/Fit/Fast Conversion and conversion semantics.
6. AZnet Theme owns layout/composition/styling only and consumes public Woo/ConvertFlow boundaries.
7. Assets are surface-aware and must not load globally when no Woo presentation surface needs them.
8. Woo/plugin absence must fail soft; Theme generic WordPress surfaces must continue to render.

## Parallel execution rule

Because E5-C is blocked by an external RootProfile contract, Workstream W may progress in parallel. W progress does not imply E, F, integration, release or takeover PASS. Milestone F remains locked by its own dependencies.

## W1 boundary

W1 establishes Woo capability detection and surface classification through public Woo APIs/conditional tags. It also prevents generic-content CSS from leaking onto recognized Woo surfaces. W1 deliberately does not create Product/Cart/Checkout layout, template overrides, commerce state, or ConvertFlow integration.

## W1 surface vocabulary

`product`, `archive`, `cart`, `checkout`, `account`, or `null`.

Recognition uses only Woo public runtime capability and conditional functions:
- capability: `WC()` exists;
- product: `is_product()`;
- archive: `is_shop()` or `is_product_taxonomy()`;
- cart: `is_cart()`;
- checkout: `is_checkout()`;
- account: `is_account_page()`.

Missing functions or absent WooCommerce return `null` rather than fatal.

## Acceptance

W1 passes when:
- Woo absent returns capability false and no Woo surface;
- each approved Woo surface classifies deterministically;
- generic-content asset eligibility remains true for generic WordPress surfaces but false for recognized Woo surfaces;
- no direct Woo option/meta/table/private-class access exists;
- no `woocommerce/` template override is introduced;
- retained E5-B regression and production PHP lint remain PASS.

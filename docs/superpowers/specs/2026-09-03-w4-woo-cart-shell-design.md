# W4 Woo Cart Design Packet

## Problem / job

Make WooCommerce Cart easy to review and act on across classic Cart markup and Woo Cart Block without moving commerce state or calculations into AZnet Theme.

## Owner boundary

AZnet Theme owns presentation only: spacing, grouping, table/card treatment, totals emphasis, form/control styling, responsive reflow and focus treatment.

WooCommerce remains authoritative for line items, quantity, coupon validity, price/subtotal/total, tax/shipping, stock validation, notices, cart mutations and transition to Checkout.

ConvertFlow remains authoritative for Product Journey / Filter / Fit / Fast Conversion. W4 does not implement or emulate those capabilities.

## Content / data contract

W4 consumes only the normalized W1 Theme adapter surface value `cart` plus WooCommerce native classic Cart and Cart Block markup.

Allowed presentation targets include `.woocommerce-cart-form`, `.shop_table`, `.cart_totals`, `.coupon`, `.checkout-button`, `.wp-block-woocommerce-cart`, `.wc-block-cart-items`, `.wc-block-components-quantity-selector` and `.wc-block-cart__submit-button`.

Forbidden: price math, cart mutation logic, custom AJAX, quantity interception, direct Woo storage/meta reads, private Woo internals, duplicate coupon state, ConvertFlow behavior, JavaScript or `woocommerce/` template overrides.

## Desktop intent

- Keep cart items visually primary and totals clearly separated.
- Preserve native Woo forms, labels, links and buttons.
- Classic Cart totals may be visually constrained/aligned to the end of the content region without changing Woo markup or calculation flow.
- Cart Block keeps native block structure; Theme only adds compatible spacing, borders, typography and focus styling.

## Mobile intent

- No forced horizontal scrolling for normal product names, quantity controls, coupon form or totals.
- Preserve table semantics; do not convert semantic tables into arbitrary div-like layout solely for styling.
- Inputs/buttons wrap or stack when needed and remain touch-friendly.
- Checkout CTA remains native and non-sticky in W4.

## Interaction states

- Native quantity, coupon, remove, update-cart and checkout controls remain the only behavior owners.
- Visible `:focus-visible` treatment uses Theme focus tokens.
- No custom JavaScript, auto-submit or AJAX interception.

## Accessibility

- Preserve Woo labels/table/block semantics and native notices.
- Do not hide totals, coupons, errors or update controls as a presentation shortcut.
- Maintain visible keyboard focus and sufficient control spacing.
- Do not create duplicate interactive controls.

## Performance

Add one stylesheet: `assets/css/components/woocommerce-cart.css`.

It loads only when `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface()` returns `cart`.

No JavaScript is added in W4.

## Acceptance — local L0-L2

1. A cart-only Theme asset gate exists and returns true only for normalized surface `cart`.
2. `aznet-theme-woocommerce-cart` loads only on Cart requests.
3. Cart stylesheet covers both classic Cart and Cart Block selectors, uses Theme tokens and includes responsive/focus intent without hiding or replacing native controls.
4. Static ownership gate rejects price calculation, cart mutation/AJAX logic, direct storage/private Woo access, ConvertFlow-specific code, JavaScript and template overrides.
5. Regression gates affected by `assets.php` / `bootstrap.php` remain green.
6. No claim is made for L3 runtime, L4 browser/a11y, L5 integration or L6 release.

## Non-goals

- Checkout or My Account presentation.
- Sticky checkout CTA.
- Custom quantity behavior.
- Custom coupon engine.
- Custom AJAX/cart API calls.
- Template override.
- Runtime/browser validation.

## Rollback

Revert W4-only helper, enqueue block, stylesheet, tests and verifier. W3 Archive presentation remains intact.

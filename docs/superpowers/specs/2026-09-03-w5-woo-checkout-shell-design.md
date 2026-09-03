# W5 Woo Checkout Shell — Design Packet

## Problem / job

Give WooCommerce Checkout a coherent AZnet Theme presentation for both classic Checkout and Woo Checkout Block while preserving WooCommerce as the sole owner of customer data, validation, payment, totals, order creation and checkout transitions.

## Owner boundary

AZnet Theme owns presentation only: page spacing, desktop/mobile composition, form/control styling, notices, focus treatment, readable order/payment hierarchy and visual treatment of native Woo Checkout markup.

WooCommerce remains authoritative for billing/shipping fields and values, validation, shipping methods, taxes, discounts, totals, payment gateways and ordering, payment processing, order creation, checkout endpoints, notices and checkout state.

ConvertFlow remains outside W5. W5 does not implement Product Journey, conversion-state logic or checkout orchestration.

## Content / data contract

W5 consumes only the normalized W1 Theme adapter surface value `checkout` plus WooCommerce native classic/Block markup already rendered on the request.

Allowed presentation targets include classic `form.checkout`, `#customer_details`, `#order_review_heading`, `#order_review`, `#payment`, Woo notices and native form controls, plus Woo Checkout Block wrappers/components.

Forbidden: field removal/reconstruction, payment-method reordering, gateway filtering, checkout validation hooks, order creation hooks, direct Woo storage/meta access, custom AJAX/fetch checkout behavior, price/total calculation, or a `woocommerce/` template override.

## Desktop intent

- Classic Checkout uses a two-column presentation where native customer details remain the primary left column and native order/payment review remains the right column.
- Preserve native DOM order and semantics; CSS may place the existing regions visually but must not mutate checkout data or callback order.
- Order review/payment receives clear grouping, border, spacing and hierarchy without hiding Woo notices or terms.
- Woo Checkout Block retains its native component structure; Theme applies spacing, typography, border/radius, control and focus presentation rather than replacing Block layout logic.

## Tablet / mobile intent

- Collapse classic Checkout to one column at the mobile breakpoint.
- Inputs, selects, buttons and Place Order controls remain usable without horizontal overflow.
- No sticky Place Order CTA in W5.
- No JavaScript dependency is added.

## Interaction states

- Preserve Woo field validation/error semantics and gateway behavior.
- Do not hide, disable, reorder or synthesize payment methods.
- `:focus-visible` uses Theme focus tokens.
- Native notices and errors remain visible.
- Order Received / checkout endpoints may receive fail-safe presentation when Woo classifies the request as `checkout`; Theme does not infer order state.

## Accessibility

- Preserve native labels, field grouping, headings, notices and button semantics.
- Maintain visible keyboard focus.
- Do not replace native inputs with custom controls.
- Do not use presentation CSS to conceal required checkout information.

## Performance

Add one stylesheet: `assets/css/components/woocommerce-checkout.css`.

It loads only when `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface()` returns `checkout`.

No JavaScript is added in W5.

## Acceptance — local L0-L2

1. A checkout-only Theme asset gate exists and returns true only for normalized surface `checkout`.
2. `aznet-theme-woocommerce-checkout` is enqueued only on checkout-classified requests.
3. Checkout stylesheet covers both classic Checkout and Woo Checkout Block presentation targets.
4. Classic responsive intent is two columns on desktop and one column on mobile; controls avoid forced horizontal overflow.
5. Static ownership gate rejects direct Woo storage access, price/total calculation, cart/order mutation, checkout field/payment filtering, validation/order hooks, JavaScript and `woocommerce/` template overrides.
6. Affected retained W4 asset/ownership regressions stay green after the new helper dependency is introduced.
7. No claim is made for L3 runtime, L4 browser/a11y, L5 integration or L6 release.

## Non-goals

- Custom checkout fields.
- Payment gateway ordering/filtering.
- Shipping/tax/discount/total calculation.
- Checkout validation or order creation.
- Sticky Place Order CTA.
- JavaScript/custom AJAX.
- Template override.
- Runtime/browser validation.

## Rollback

Revert W5-only helper, enqueue block, stylesheet, tests, verifier and retained W4 test-harness dependency include. W4 Cart presentation remains intact.

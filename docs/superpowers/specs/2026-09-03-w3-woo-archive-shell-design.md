# W3 Shop / Product Archive / Category / Tag Design Packet

## Problem / job

Help visitors scan the WooCommerce catalogue, compare visible product cards quickly, and enter a Single Product page without turning AZnet Theme into a catalogue/query/filter engine.

## Owner boundary

AZnet Theme owns presentation only: container, spacing, grid, card styling, image framing, typography, focus treatment, responsive reflow and placement styling for Woo-native archive controls.

WooCommerce remains authoritative for product query, catalogue membership, taxonomy, ordering semantics, result count, pagination, product links, images, title, price, rating, sale state, stock/purchasability and add-to-cart behavior.

ConvertFlow remains authoritative for Product Journey / Filter / Fit / Fast Conversion. W3 does not implement or emulate those capabilities.

## Content / data contract

W3 consumes only WooCommerce native archive markup and the normalized W1 Theme adapter surface value `archive`.

Allowed native presentation targets include Woo archive wrappers, result count, ordering form, product loop, product card link/image/title/price/rating and native add-to-cart markup.

Forbidden: custom `WP_Query`, direct Woo storage/meta reads, taxonomy reconstruction, custom product ordering logic, duplicate filter state, ConvertFlow-specific behavior or a `woocommerce/` template override.

## Desktop intent

- Use the Theme wide container and preserve native Woo heading/result/order controls.
- Product loop is a four-column grid on wide desktop.
- Cards keep native Woo semantic links and actions; Theme styles border/radius/spacing/image ratio/typography only.
- Product images use a consistent square presentation box without changing Woo image ownership.
- Price remains visually prominent but no price value is transformed or synthesized.

## Tablet / mobile intent

- Three columns for medium desktop/tablet where space allows.
- Two columns from 480px up to the mobile/tablet breakpoint.
- One column below 480px to avoid compressed names, prices and controls.
- No horizontal overflow and no JS-driven layout dependency.

## Interaction states

- Native product/card links and native add-to-cart controls remain independently focusable.
- Hover styling must not be required to understand state.
- `:focus-visible` uses Theme focus tokens.
- No nested whole-card click overlay that changes Woo semantics.
- No sticky archive controls in W3.

## Accessibility

- Preserve Woo/native list and heading semantics.
- Maintain visible keyboard focus.
- Do not hide price, rating, result count, ordering controls or native notices as a presentation shortcut.
- Do not create duplicate interactive targets.
- Respect reduced-motion by not requiring animation for comprehension.

## Performance

Add one stylesheet: `assets/css/components/woocommerce-archive.css`.

It must load only when `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface()` returns `archive`.

No JavaScript is added in W3.

## Acceptance — local L0-L2

1. An archive-only Theme asset gate exists and returns true only for normalized surface `archive`.
2. `aznet-theme-woocommerce-archive` is enqueued only on Shop/Product Archive/Category/Tag surfaces represented by W1 as `archive`.
3. Product CSS and generic-content CSS are not accidentally used as W3 archive assets.
4. Archive stylesheet expresses four/three/two/one-column responsive intent and uses Theme tokens.
5. Static ownership gate rejects direct storage access, custom query mutation, private Woo internals, ConvertFlow-specific code, JS and `woocommerce/` template overrides.
6. Retained W2/W1/E5-B contracts stay green.
7. No claim is made for L3 runtime, L4 browser/a11y, L5 integration or L6 release.

## Non-goals

- Cart, Checkout or My Account presentation.
- Custom filter/sidebar implementation.
- ConvertFlow coexistence behavior.
- Runtime/browser validation.
- Template override.
- Woo query/order/catalogue mutation.
- Sticky controls or JavaScript enhancements.

## Rollback

Revert W3-only helper, enqueue block, stylesheet, tests and verifier. W2 Single Product presentation remains intact.

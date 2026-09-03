# W6 Woo My Account Design Packet

## Problem / job

Make WooCommerce My Account surfaces feel like a first-class AZnet Theme surface without moving authentication, account data, orders, downloads, addresses, payment methods or endpoint routing into the Theme.

## Owner boundary

AZnet Theme owns presentation only: page spacing, navigation presentation, table/card treatment, form/control styling, notices, buttons, focus states and responsive reflow.

WooCommerce remains authoritative for login/register/logout, account identity, endpoint routing, orders, downloads, addresses, payment methods, account details, nonce/validation, mutations and all account data.

ConvertFlow is untouched.

## Content / data contract

W6 consumes only WooCommerce native My Account markup and the normalized W1 Theme adapter surface value `account`.

Allowed presentation targets include `.woocommerce-account`, `.woocommerce-MyAccount-navigation`, `.woocommerce-MyAccount-content`, native Woo tables, forms, notices and buttons.

Forbidden: custom account endpoints, direct user/order/meta queries, authentication interception, logout/login replacement, nonce/validation logic, direct Woo storage, private Woo internals, ConvertFlow-specific behavior or a `woocommerce/` template override.

## Desktop intent

- Use the Theme wide container.
- Present My Account navigation as a clear left rail and native account content as the main column.
- Native tables/forms/notices remain semantically intact.
- Current endpoint navigation state remains Woo-owned; Theme only styles existing classes.

## Tablet / mobile intent

- Collapse to one column at 767px and below.
- Navigation appears before content and may wrap/stack without JavaScript.
- Tables and forms must fit without forced horizontal scrolling introduced by Theme CSS.
- Buttons/inputs remain usable at narrow widths.

## Interaction states

- Existing Woo links, forms and buttons stay independently focusable.
- `:focus-visible` uses Theme focus tokens.
- No nested click overlays, no custom logout confirmation and no JS-driven account navigation.

## Accessibility

- Preserve native headings, links, lists, tables, labels and form semantics.
- Maintain visible keyboard focus.
- Do not hide notices, validation output or account navigation entries as a presentation shortcut.

## Performance

Add one stylesheet: `assets/css/components/woocommerce-account.css`.

It loads only when `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface()` returns `account`.

No JavaScript is added in W6.

## Acceptance — local L0-L2

1. An account-only Theme asset gate exists and returns true only for normalized surface `account`.
2. `aznet-theme-woocommerce-account` is enqueued only on My Account requests represented by W1 as `account`.
3. Account stylesheet covers native My Account navigation/content, tables/forms/notices/buttons, one-column mobile reflow and visible focus using Theme tokens.
4. Static ownership gate rejects direct account/order/user storage access, custom endpoint/auth mutation, private Woo internals, ConvertFlow-specific code, JavaScript and `woocommerce/` template overrides.
5. Retained W5 asset/ownership regression invalidated by `assets.php`/`bootstrap.php` stays green.
6. No claim is made for L3 runtime, L4 browser/a11y, L5 integration or L6 release.

## Non-goals

- Custom account endpoints.
- Replacing login/register/logout behavior.
- Order, download, address or payment-method business logic.
- JavaScript enhancements.
- Template overrides.
- Runtime/browser validation.

## Rollback

Revert W6-only helper, enqueue block, stylesheet, tests and verifier. W5 Checkout presentation remains intact.

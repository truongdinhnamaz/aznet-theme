# Woo L4 Browser / Visual / A11y Design

## Status

Approved by owner on 2026-09-03.

## Goal

Verify the already-L3-PASS Woo presentation surfaces in a real Chromium browser at desktop and mobile widths without changing AZnet Theme production behavior.

## Scope

Surfaces:
- Single Product
- Shop / Product Archive
- Cart with a real Woo cart session
- Checkout with a real Woo cart session
- My Account (logged-out native Woo form is sufficient for this slice)

Viewports:
- desktop: 1440 x 1000
- mobile: 390 x 844

## Ownership

AZnet Theme owns presentation only. WooCommerce remains authoritative for product, cart, checkout, account, stock, price, quantity, validation and navigation semantics. L4 must not replace or bypass Woo behavior to make tests pass.

## Verification requirements

For every surface and viewport:
1. Chromium renders the native Woo surface successfully.
2. No horizontal page overflow beyond a 1px rounding tolerance.
3. A surface-specific native Woo landmark/control is visible.
4. A meaningful native interactive control is visible and enabled where applicable.
5. Keyboard Tab reaches a focusable element inside the main content and the focused element has a visible focus indicator (outline or box-shadow).
6. Axe scan reports zero `critical` or `serious` violations.
7. A full-page screenshot is saved as evidence.

Checkout must be reached with a real Woo cart session created through the public add-to-cart HTTP/browser flow; the test must not disable Woo empty-cart redirect logic.

## Evidence

Upload:
- 10 full-page screenshots (5 surfaces x 2 viewports)
- per-surface axe JSON reports
- browser summary JSON
- WordPress/Woo/PHP/Theme runtime report
- WordPress server log

Screenshots must be reviewed after the run before L4 PASS is claimed.

## Constraints

- Test branch only; no production Theme file changes.
- No deployment to remquocanh.vn or another live site.
- No Woo private APIs/storage reads.
- No RootProfile or ConvertFlow code.
- No AZT source-document version bump.
- L4 PASS does not imply L5 integration or L6 release PASS.

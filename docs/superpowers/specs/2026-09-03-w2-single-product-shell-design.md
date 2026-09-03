# W2 Single Product Shell Design

**Status:** Approved Design Packet — 2026-09-03

## Goal

Give WooCommerce Single Product a Theme-owned presentation shell that is consistent with AZnet Theme tokens, responsive behavior and accessibility expectations while preserving WooCommerce as the owner of product facts and commerce behavior.

## Problem / Job

A Single Product request needs to feel like a first-class AZnet Theme surface without copying or replacing WooCommerce product logic. The user should see a clear product hierarchy, usable gallery/summary layout, readable product details and a stable mobile reflow while WooCommerce continues to own price, stock, variations, purchasability, notices, add-to-cart/cart state and reviews behavior.

## Owner Boundary

AZnet Theme owns:
- page-level Product presentation composition;
- spacing, typography, container rhythm and responsive layout;
- visual treatment of native Woo gallery, summary, tabs/details and reviews regions;
- focus/hover styling and surface-aware Theme assets.

WooCommerce owns:
- product identity and catalogue facts;
- title/price/stock/SKU/variation/purchasability truth;
- gallery/product forms and add-to-cart behavior;
- notices, tabs/reviews semantics and commerce state;
- all mutation and transaction behavior.

ConvertFlow remains owner of Product Journey, Filter, Fit, Fast Conversion and conversion semantics. W2 does not integrate or modify ConvertFlow.

## Allowed Data / Rendering Contract

W2 consumes only WooCommerce public runtime capability, public conditional tags already normalized by W1, native WooCommerce HTML/classes/hooks and WordPress Theme APIs.

Forbidden:
- direct Woo option/meta/table/private-class reads;
- product/cart/order state duplication in Theme settings or local stores;
- overriding or replacing Woo callbacks for title, price, stock, variation or add-to-cart logic;
- slug/title/Page-ID heuristics;
- a `woocommerce/` template override in W2.

## Desktop Intent

On Single Product only:
- keep the native WooCommerce product markup and callback order;
- present the primary product area as an approximately 56/44 two-column gallery/summary composition when viewport width permits;
- keep summary content visually grouped without introducing a new commerce card/state model;
- keep tabs/additional information/reviews below the primary two-column region at full content width;
- use AZnet Theme semantic tokens for spacing, border, text and focus treatment;
- do not add sticky Add to Cart in W2.

## Tablet / Mobile Intent

At narrower widths the primary product region becomes one column in native document order:
1. gallery;
2. title/price/summary;
3. variation/add-to-cart form;
4. meta and secondary summary content;
5. tabs/details/reviews.

The layout must avoid horizontal overflow, preserve Woo form controls and not move commerce actions by DOM manipulation.

## Interaction States

W2 styles only presentation states exposed by native Woo markup:
- links/buttons: hover and `:focus-visible`;
- form controls: visible focus treatment;
- disabled/unavailable Woo controls: retain Woo semantics and pointer/disabled behavior;
- notices/errors: W2 may improve spacing/readability but must not change message semantics or lifecycle.

No loading model, sticky CTA, custom variation resolver, modal or custom product state is introduced.

## Accessibility

- preserve native heading and landmark structure; do not inject duplicate H1;
- do not remove labels or accessible names from Woo controls;
- maintain keyboard access to gallery controls, links, forms and tabs supplied by Woo;
- add visible `:focus-visible` treatment using Theme tokens;
- respect `prefers-reduced-motion` by avoiding new required motion;
- CSS must not hide essential commerce information.

## Performance

Add one Theme-owned product stylesheet only when W1 normalized Woo surface equals `product`. It must not enqueue on archive, cart, checkout, account or non-Woo requests. No JS is added in W2.

## Implementation Shape

Preferred implementation is hooks/CSS/Blocks-first with no Woo template override:
- add a Theme helper deciding whether Product presentation assets should load from the normalized W1 surface;
- enqueue `assets/css/components/woocommerce-product.css` conditionally;
- style native Woo Single Product classes under a Theme/Woo Single Product scope;
- only add a small public-hook wrapper/class if CSS cannot establish the required container safely. The default path is no new wrapper.

## Acceptance — Local L0–L2

W2 local gate is PASS only when:
- Product stylesheet is enqueued on normalized `product` requests only;
- archive/cart/checkout/account/non-Woo requests do not enqueue it;
- CSS contains desktop two-column and mobile one-column product layout rules using Theme token family;
- no `woocommerce/` template override exists;
- no direct Woo storage/private access or commerce state duplication exists;
- W1 contracts remain PASS;
- retained E5-B verifier remains PASS;
- production PHP lint remains PASS.

## Not Yet Proven in W2 Local Gate

W2 local L0–L2 does not prove real WooCommerce runtime, browser visual parity, responsive/a11y behavior in an actual browser, ConvertFlow coexistence, integration or release readiness. Those remain for W5/W6.

## Rollback

Revert W2 commits. W1 capability/surface classifier and generic-content asset boundary remain intact.

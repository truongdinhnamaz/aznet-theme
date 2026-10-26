# Digital 01 Brand Light 2026 — Presentation Forward-port Design

Date: 2026-10-26. Status: Chosen visual direction; non-production candidate.

## Intent
Restore the AZnet.vn Digital 01 frontend to the existing approved Brand Light 2026 reference: white Header, light Hero, navy type, red CTAs, four service cards, value/benefit grid, responsive navigation. Preserve the site's role as a business website. WooCommerce booking and checkout remain separate commerce surfaces, not the visual owner of the Homepage.

## Sources and baseline
- Historical design/candidate: draft PR #495, exact head 979da5bb0dafd7990a47f55c896ec0677fe0bfea, evidence docs/evidence/DIGITAL01_AZNET_BRAND_LIGHT_20261022.md on that branch. Its isolated L3/L4 passed 37/37 workflows; not production evidence.
- Current canonical code source: main ee765af2dc0af5f11d65bf779ac2f244b43475a3, Theme 1.3.97; main differs materially from historical PR base.
- Live aznet.vn: active Theme 1.3.96; WordPress Front Page 2 is published; Page 43515 is a draft with native Gutenberg opt-in class aznet-digital01--brand-2026 and Landing Page template. Native menu 3493 is not the live menu.
- The approved historical screenshot is a reference, not permission to invent metrics or domain facts. The CSS laptop illustration is an approximation, not pixel-identical photography.

## Ownership and choices
- AZnet Theme owns only presentation CSS, scoped loading, and rendering of WordPress-native menu depth; it does not own WordPress Page content, logo, navigation items, Woo pricing/variations/cart/orders, ConvertFlow journeys, or RootProfile semantics.
- Keep site-specific WordPress data in WordPress. Use explicit Page opt-in class; do not detect brand/site by URL, title, ID, slug, or provider internals.
- Forward-port the already verified PR #495 visual candidate to current main on a new isolated branch, rather than merge the stale branch or roll the whole theme back to Flatsome.
- Keep both old/current presentation and Brand Light styles available without global impact. Draft preview first; production Front Page/menu/logo change, main merge and deployment are separate approval gates.

## Acceptance
- On an opted-in native Page only: white Header with correct logo/text fallback, responsive 1- or 2-level WordPress navigation, light Hero, navy typography, red action, services cards in responsive 4/2/1 columns, benefits grid and consistent CTA styling.
- On non-opted-in Page 2 and all other surfaces: unchanged behavior and CSS loading; no unrequested WooCommerce display/state change.
- L1 static and L2 contract regression, fresh L3 isolated WordPress, fresh L4 desktop/tablet/mobile plus keyboard/focus, and L5 boundary checks must be separately evidenced before production claims.
- WordPress Page 43515 must remain draft; existing published Page 2, active theme 1.3.96, and live menu assignments must remain unchanged.

## Rollback and hard gates
- Revert only this forward-port branch changes; a WPVibe draft Theme may be discarded only after inspecting whether it contains unsaved unrelated work. No force push, main merge, release, production deploy or global custom_logo/menu/front-page changes without explicit owner approval.
- The actual authenticated Page 43515 screenshot comparison remains UNKNOWN until a combined Page + Theme preview is observable; isolated WordPress CI is not a substitute.
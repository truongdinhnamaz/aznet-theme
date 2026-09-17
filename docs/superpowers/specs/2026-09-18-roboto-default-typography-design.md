# AZnet Theme Default Typography — Roboto Design

Date: 2026-09-18  
Status: Proposed for owner review  
Scope: AZnet Theme only

## 1. Goal

Make Roboto the default typography of AZnet Theme so frontend surfaces, WordPress editor rendering, and Law 01 use one consistent, common, highly readable type system.

This is a presentation-layer change only. It does not change content ownership, provider contracts, WordPress data, RootProfile, ConvertFlow, WooCommerce state, or provisioning semantics.

## 2. Decision

AZnet Theme will use Roboto as its default font family.

Primary family:

```css
Roboto, Arial, sans-serif
```

Both body/UI text and headings use the same family. Visual hierarchy comes from weight, size, line-height, spacing, color, and composition rather than a separate serif heading family.

The existing Law 01 hardcoded `Georgia, 'Times New Roman', serif` heading/slogan rules will be removed or replaced with Theme typography tokens.

## 3. Font delivery

Roboto will be self-hosted by AZnet Theme rather than loaded from Google Fonts or another external CDN.

Requirements:
- ship only the font files/weights actually required by the Theme;
- use `font-display: swap`;
- provide `Arial, sans-serif` fallback;
- no external font request is required for normal rendering;
- font assets remain Theme presentation assets and do not carry domain data.

Initial weight set:
- 400 Regular
- 500 Medium
- 600 SemiBold
- 700 Bold

If a Roboto distribution does not provide an actual 600 file in the selected source set, use the nearest supported weight deliberately rather than synthesizing a new font family.

## 4. Design tokens

Add Theme-owned typography tokens in `assets/css/tokens.css`:

```css
--aznet-theme-font-family-base: Roboto, Arial, sans-serif;
--aznet-theme-font-family-heading: var(--aznet-theme-font-family-base);
```

Existing size and line-height tokens remain authoritative.

The default frontend shell should inherit `--aznet-theme-font-family-base`. Headings should consume `--aznet-theme-font-family-heading`.

No product-specific namespace or provider-specific typography token is introduced.

## 5. theme.json and editor parity

`theme.json` will expose the same Theme typography family so Gutenberg/editor rendering matches the frontend.

The implementation must not create a second competing typography source. CSS tokens remain the Theme presentation primitive; `theme.json` references or mirrors that canonical family in the WordPress-native editor layer.

## 6. Law 01 refinement

Law 01 will inherit the Theme default font family.

Target presentation:
- body and lede: Roboto 400;
- navigation/UI/button labels: 500–600;
- section headings: 600–700;
- hero H1: 700;
- eyebrow/labels: 600–700 with controlled letter spacing;
- slogan: Roboto italic where italic is desired; no Georgia fallback;
- maintain current burgundy/gold/cream palette and approved layout.

This slice does not redesign Law 01 content structure again. It only normalizes typography and makes small spacing/weight adjustments required by the new metrics.

## 7. Asset scope and performance

Roboto assets must be loaded through the Theme asset system, not inline from an external service.

Acceptance:
- no Google Fonts request;
- no global ecosystem/provider asset coupling;
- no unnecessary weights;
- no layout-breaking FOIT;
- existing surface-aware CSS rules remain intact.

Because the typography is Theme-wide, the base font assets may be globally enqueued by AZnet Theme. Law 01 must not register its own duplicate font source.

## 8. Accessibility and language

Roboto must render Vietnamese diacritics correctly.

Typography must preserve:
- readable body size;
- sufficient line-height;
- no text clipping;
- no horizontal overflow caused by changed glyph metrics;
- focus styles and interactive controls unaffected;
- browser zoom and mobile reflow behavior.

## 9. Ownership boundary

Allowed:
- Theme typography tokens;
- Theme font assets;
- theme.json typography;
- Theme/Law 01 presentation CSS;
- related tests and source documentation.

Forbidden:
- editing provider/plugin data;
- embedding customer-specific identity in font rules;
- changing RootProfile/ConvertFlow/WooCommerce contracts;
- rerunning Quick Setup;
- using external provider storage to drive typography.

## 10. Verification

Implementation follows RED → GREEN → regression.

Required evidence:
1. L1 static contract: default typography tokens + no Law 01 Georgia/Times hardcode.
2. L2 contract tests: theme.json/editor parity and asset registration.
3. L3 WordPress runtime: Theme loads and Roboto assets resolve.
4. L4 browser:
   - desktop and mobile Law 01;
   - no horizontal overflow;
   - no critical axe violations;
   - Vietnamese text displays correctly;
   - computed body and heading font-family resolve to Roboto when asset loads.
5. Performance regression: no material deterioration from font delivery.
6. Existing Law 01, homepage, D-027, native runtime, lifecycle, and release regressions remain green.

## 11. Delivery / deployment boundary

Implementation, merge, and live publish remain separate gates.

The currently prepared WPVibe draft for PR #114 must not be overwritten with this new typography until the implementation candidate is verified and the owner explicitly approves preview/publish.

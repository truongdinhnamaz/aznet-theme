# AZnet Theme System Typography v1 — Design

Date: 2026-09-18
Status: Approved design, pending implementation-plan review
Scope: AZnet Theme only

## 1. Goal

Replace the current Roboto-specific typography implementation with a stable, common, system-font-based typography architecture for the entire AZnet Theme.

The objective is not only to choose a font family. The Theme must define a coherent typography system by semantic role so font family, size, weight, line-height, and letter-spacing are predictable across Header/Footer, Homepage, Page/Post, Archive/Search/404, forms, WooCommerce, and editor surfaces.

The Theme remains presentation owner only. This change does not alter domain data, provider contracts, provisioning semantics, WordPress content ownership, RootProfile, ConvertFlow, Nagavia, or WooCommerce business state.

## 2. Default font-family architecture

The Theme default family is the common system stack:

```css
system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif
```

Canonical family tokens:

```css
--aznet-theme-font-family-base:
    system-ui, -apple-system, BlinkMacSystemFont,
    "Segoe UI", Roboto, Helvetica, Arial, sans-serif;

--aznet-theme-font-family-heading:
    var(--aznet-theme-font-family-base);
```

Rules:
- body/UI and headings use the same default system family;
- components consume semantic family tokens instead of declaring local font stacks;
- no Theme surface hardcodes Georgia, Times New Roman, Roboto, Arial, Helvetica, or another family unless a future approved design explicitly creates a documented semantic exception;
- no external font CDN;
- no self-hosted default font binary is required;
- browser/OS-native system fonts are an intentional part of the design, so minor cross-platform glyph-metric differences are acceptable;
- editor and frontend resolve the same default family semantics.

## 3. Remove Roboto as a Theme dependency

The current Roboto-specific implementation is retired from the default Theme typography path.

Remove:
- Roboto `fontFace` entries from `theme.json`;
- Theme-owned Roboto WOFF2 files used only by the default typography system;
- Roboto-specific package assertions that require local font binaries.

Retain:
- generic content-aware asset versioning, because it is useful Theme infrastructure beyond fonts;
- cache-busting regression coverage at the asset layer where applicable.

No replacement webfont is introduced.

## 4. Semantic typography scale

Typography is selected by **display role**, not by template or customer surface.

### 4.1 Core roles

| Semantic role | Desktop / fluid size | Mobile behavior | Weight | Line-height | Primary use |
| --- | --- | --- | --- | --- | --- |
| Display / Hero | `clamp(2.75rem, 5vw, 4.5rem)` | fluid clamp | 700 | 1.05–1.10 | Homepage/landing hero headline |
| H1 | `2.5rem` | `2rem` | 700 | 1.15 | Page/Post primary title |
| H2 | `2rem` | `1.625rem` | 700 | 1.20 | Primary section heading |
| H3 | `1.5rem` | `1.25rem` | 600 | 1.30 | Card/secondary section heading |
| H4 | `1.25rem` | `1.125rem` | 600 | 1.35 | Tertiary heading |
| Lead | `1.125rem` | `1.0625rem` | 400 | 1.70 | Intro/lede/hero supporting copy |
| Body | `1rem` | `1rem` | 400 | 1.65 | Main prose and standard UI copy |
| Small body | `0.9375rem` | `0.9375rem` | 400 | 1.60 | Secondary copy |
| Navigation | `0.9375rem` | `1rem` | 500–600 | 1.35 | Header/footer navigation |
| Button / CTA | `0.9375rem` | `0.9375rem` | 600 | 1.20 | Buttons and primary action labels |
| Label / Eyebrow | `0.75–0.8125rem` | same semantic range | 600–700 | 1.30 | Section label, uppercase eyebrow |
| Metadata | `0.875rem` | `0.875rem` | 400–500 | 1.45 | Date/category/author/meta |
| Caption | `0.8125rem` | `0.8125rem` | 400 | 1.45 | Image/media caption |
| Form control | `1rem` | `1rem` | 400 | 1.40 | Input/select/textarea |
| Price | `1.25–1.5rem` | `1.125–1.375rem` | 700 | 1.20 | WooCommerce price presentation |

### 4.2 Body floor

Body text remains 16px-equivalent (`1rem`) on mobile and desktop.

The Theme must not shrink standard body/form text below that floor merely to fit a layout. Layout must reflow instead.

## 5. Canonical semantic tokens

Introduce explicit semantic tokens rather than relying only on generic `sm/base/lg/xl` scale names.

Minimum token set:

```css
--aznet-theme-text-display
--aznet-theme-text-h1
--aznet-theme-text-h2
--aznet-theme-text-h3
--aznet-theme-text-h4
--aznet-theme-text-lead
--aznet-theme-text-body
--aznet-theme-text-small
--aznet-theme-text-navigation
--aznet-theme-text-button
--aznet-theme-text-label
--aznet-theme-text-meta
--aznet-theme-text-caption
--aznet-theme-text-form
--aznet-theme-text-price
```

Add corresponding semantic weight and line-height tokens where a role needs them.

Example families:

```css
--aznet-theme-font-weight-regular: 400;
--aznet-theme-font-weight-medium: 500;
--aznet-theme-font-weight-semibold: 600;
--aznet-theme-font-weight-bold: 700;

--aznet-theme-line-height-display: 1.08;
--aznet-theme-line-height-h1: 1.15;
--aznet-theme-line-height-h2: 1.2;
--aznet-theme-line-height-h3: 1.3;
--aznet-theme-line-height-body: 1.65;
--aznet-theme-line-height-meta: 1.45;
```

The exact final token names may be adjusted to match existing naming conventions, but they must remain semantic, Theme-owned, and stable.

## 6. Legacy token compatibility

Existing public/consumed tokens such as:

```css
--aznet-theme-font-size-sm
--aznet-theme-font-size-base
--aznet-theme-font-size-lg
--aznet-theme-font-size-xl
```

must not be deleted abruptly if current Theme/plugin presentation consumers still rely on them.

Migration rule:
- semantic role tokens become the preferred path;
- old tokens remain aliases or compatibility tokens where required;
- removal of an old public token requires a separate compatibility/versioning decision.

## 7. theme.json and editor parity

`theme.json` remains the WordPress-native presentation bridge.

It must:
- stop registering Roboto `fontFace` assets;
- expose the system font stack as the default family;
- keep editor/frontend semantics aligned;
- map WordPress typography presets to Theme semantic tokens where WordPress supports that cleanly;
- avoid a second competing source of typography truth.

CSS custom properties remain the primary Theme presentation primitive. `theme.json` mirrors the same design contract for WordPress-native editor behavior.

## 8. Surface mapping

All Theme surfaces must map visible text to semantic typography roles.

### Header / Footer
- main navigation → Navigation;
- utility navigation → Navigation or Small body, depending on information hierarchy;
- footer body text → Small body;
- footer headings → H4;
- footer metadata/legal text → Metadata.

### Homepage
- hero headline → Display;
- section heading → H2;
- card title → H3 or H4 according to hierarchy;
- hero/supporting paragraph → Lead;
- eyebrow → Label;
- CTA → Button;
- trust/stat/support copy → Small body or Metadata.

### Page / Post
- document title → H1;
- article section heading → H2/H3/H4;
- body prose → Body;
- introductory excerpt/lead → Lead;
- author/date/category → Metadata;
- captions → Caption.

### Archive / Search / 404
- page heading → H1;
- card/result title → H3;
- result excerpt → Body/Small body;
- result metadata → Metadata;
- recovery CTA → Button.

### Forms
- labels → Label or Body depending on form semantics;
- controls → Form;
- helper/error text → Small body / Metadata as appropriate;
- button → Button.

### WooCommerce
- product page title → H1;
- product-card title → H3/H4;
- price → Price;
- product body/description → Body;
- variation labels → Label;
- checkout/cart form controls → Form;
- buttons → Button;
- product metadata → Metadata.

## 9. Hardcode removal policy

During migration, audit Theme presentation files for typography declarations that duplicate or contradict semantic tokens.

Replace or justify:
- local `font-family` stacks;
- arbitrary pixel/rem font sizes;
- local font weights outside 400/500/600/700;
- inconsistent line-height values;
- unnecessary negative letter-spacing;
- surface-specific typography that exists only for visual improvisation.

Expected default:
- `letter-spacing: normal`;
- negative tracking is not a Theme default;
- uppercase eyebrow/label may use wider tracking as a documented semantic characteristic.

A component may keep a local declaration only when:
1. it represents a real semantic exception, and
2. the exception is documented and tested.

## 10. Law 01 migration

Law 01 must stop being a typography owner.

Specifically:
- no Georgia/Times stack;
- no Roboto-specific dependency;
- no custom default heading family;
- hero H1 consumes Display;
- section H2 consumes H2;
- service/card heading consumes H3/H4;
- body/lede/eyebrow/CTA consume their corresponding semantic Theme roles.

Law 01 may retain its burgundy/gold palette, composition, spacing, and other approved presentation identity. Typography differentiation comes from semantic scale, weight, color, spacing, and composition—not a separate typeface.

## 11. Ownership boundary

Allowed:
- Theme typography tokens;
- Theme CSS/presentation mappings;
- `theme.json`;
- editor presentation;
- tests;
- removal of obsolete Theme font assets;
- authoritative Theme architecture/source updates.

Forbidden:
- changing provider/domain data;
- changing RootProfile/ConvertFlow/WooCommerce business semantics;
- storing typography choices in provider private storage;
- rerunning Quick Setup;
- adding an external font service as a workaround;
- introducing a parallel typography configuration owned by Law 01 or another surface.

## 12. Accessibility and responsive behavior

Required behavior:
- body/form text remains readable at 16px-equivalent baseline;
- browser zoom remains usable;
- no horizontal overflow caused by text metrics;
- long Vietnamese strings wrap correctly;
- Vietnamese diacritics display correctly using native system fonts;
- focus indicators remain unaffected;
- headings preserve logical hierarchy;
- buttons/controls do not rely on reduced text size to fit;
- no text clipping at 320px width.

## 13. Performance

Expected performance direction:
- no font CDN request;
- no WOFF2 request for the default Theme family;
- no font preload requirement;
- fewer Theme font bytes;
- no FOIT from custom default font assets.

The migration must not materially worsen retained performance metrics.

## 14. Migration strategy

Implementation is one Theme-wide bounded migration delivered through RED → GREEN → regression.

Sequence:
1. add failing semantic typography architecture/contracts;
2. introduce system family + semantic scale tokens;
3. update `theme.json`;
4. migrate shared primitives/shell;
5. migrate Homepage/Law 01;
6. migrate native Page/Post/Archive/Search/404/forms;
7. migrate WooCommerce presentation;
8. remove obsolete Roboto font assets and fontFace metadata;
9. retain required legacy token aliases;
10. run static, runtime, browser, accessibility, performance, and full regression gates.

Do not partially publish a mixed typography state to production.

## 15. Verification gates

### L1 — Static
Assert:
- system family token exists;
- semantic scale exists;
- no default Roboto fontFace;
- no Theme-owned Roboto WOFF2 dependency;
- no Georgia/Times typography remains;
- hardcoded family/weight/size exceptions are either removed or explicitly allowlisted;
- legacy token aliases remain where required.

### L2 — Contract/TDD
Add contracts for:
- semantic role coverage;
- theme.json/frontend parity;
- legacy token compatibility;
- Law 01 semantic mapping;
- no default custom font binary dependency.

### L3 — WordPress runtime
Verify:
- Theme activates on supported WordPress/PHP;
- frontend and editor render without warnings/fatals;
- no missing font requests;
- standard Theme surfaces render.

### L4 — Browser / Visual / A11y
Required viewports include:
- 1440×900;
- 1024×768/900;
- 390×844;
- 320×800.

Verify:
- computed font-family resolves to a system stack;
- expected semantic role size/weight/line-height;
- Vietnamese text renders correctly;
- no text clipping/overflow;
- no critical axe violations;
- editor/frontend hierarchy remains visually consistent.

### L5 — Integration
Retained provider/WooCommerce integration contracts remain green. Typography must not change provider ownership behavior.

### L6 — Completion / Release
Require exact-head retained CI success before merge. Merge and production deployment remain separate gates.

## 16. Source governance

Because this changes Theme-wide presentation architecture, update the authoritative Theme architecture/design-system source (AZT-02) and current-state/decision evidence as required by existing governance.

Do not duplicate ephemeral CI facts into architecture source.

## 17. Rollback

Before merge:
- close PR/delete feature branch if explicitly requested.

After merge, before production deploy:
- revert the typography merge commit.

After production deploy:
- restore the previous verified Theme package or deploy the revert package through the existing safe Theme replacement workflow.

Rollback must not mutate WordPress domain content or provider data.

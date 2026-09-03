# AZnet Theme Control Center — Design Specification

Status: Proposed for owner review
Date: 2026-09-03
Repository baseline: `main@540669b440e7d36c1f2a6f33bef2fec80cc60fff`
Scope: AZnet Theme only

## 1. Problem

AZnet Theme already owns and renders a meaningful presentation foundation: semantic design tokens, Header/Footer, generic Page/Post/Archive/Search/404 templates, and WooCommerce presentation shells for Product, Archive, Cart, Checkout, and My Account. Runtime and browser/a11y verification have shown that these surfaces can work.

The current product gap is usability. A user who installs the Theme does not yet receive a clear, centralized workflow for turning that foundation into a client-ready website. Important presentation decisions are distributed across WordPress-native screens, hard-coded defaults, and Theme implementation. As a result, the Theme can be technically capable without visibly accelerating a real deployment.

The approved direction is to learn from the usability pattern of mature commercial themes such as Flatsome without copying their code, branding, layouts, or builder model. AZnet Theme will provide a simple product-level control surface while preserving AZnet ownership boundaries.

## 2. Design decision

Build an **AZnet Theme Control Center** as the Theme-owned administration and onboarding layer.

The Control Center will make presentation configuration easy through:

1. one top-level `AZnet Theme` admin entry;
2. guided Quick Setup;
3. code-defined presentation Presets;
4. global Design controls mapped to semantic Theme tokens;
5. a bounded Header Composer;
6. a bounded Footer Composer;
7. generic content and WooCommerce presentation controls;
8. curated Gutenberg block Patterns rather than a proprietary page builder;
9. capability-aware Integration status;
10. portable Import/Export for Theme-owned presentation settings;
11. stable in-place Theme packaging so updates keep the `aznet-theme/` directory and preserve settings.

AZnet Theme will **not** build a proprietary page builder analogous to UX Builder.

## 3. Governing ownership

The design preserves the existing architecture rule:

> SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.

### Theme may own

- Control Center UI and onboarding presentation;
- Theme presentation settings;
- preset definitions;
- semantic token overrides;
- Header/Footer composition order and layout;
- generic Page/Post/Archive/Search/404 presentation options;
- Woo-compatible presentation options that do not change commerce truth;
- bundled Gutenberg pattern definitions;
- Theme settings import/export;
- capability-aware presentation of integration status;
- Theme package/update continuity.

### Theme must not own

- WordPress Post/Page/Menu/Media/query data;
- WooCommerce product, price, stock, variation, cart, checkout, order, payment, account, or authentication truth;
- ConvertFlow Journey semantics, resolver/state/validation/conversion/analytics;
- RootProfile identity, Entity UUID, Claim/Evidence/Readiness, authoritative profile semantics or routing;
- plugin admin/domain workflows;
- private provider storage, private classes, secrets, licensing, or intelligence capability.

The Control Center may guide the user to a WordPress or provider-owned screen, but it must not duplicate that owner's data model merely for convenience.

## 4. Product experience

### 4.1 Top-level navigation

Add one top-level WordPress admin menu: **AZnet Theme**.

The initial information architecture is:

- Overview
- Quick Setup
- Design
- Header
- Footer
- Content
- WooCommerce — shown only when the public Woo capability is available
- Patterns
- Integrations
- Tools

The UI must be understandable without reading technical documentation. It should use native WordPress admin conventions where possible and avoid introducing a separate visual language that users must learn.

### 4.2 Overview

Overview answers four questions immediately:

1. Is the Theme configured enough to use?
2. What is still missing?
3. Which integrations are currently available?
4. What should I do next?

It shows compact status cards such as:

- Logo: configured / missing
- Primary Menu: assigned / missing
- Design Preset: applied / custom
- Header: configured / default
- Footer: configured / default
- WooCommerce: available / unavailable
- External provider adapters: available only through their verified public capability probes

Each incomplete item links to the correct Control Center section or to the authoritative WordPress/provider screen.

### 4.3 Quick Setup

Quick Setup is a short guided checklist, not a site-content generator.

Recommended flow:

1. Choose a presentation preset.
2. Set or confirm the WordPress custom logo.
3. Confirm the WordPress Primary Menu assignment.
4. Choose Header layout.
5. Choose Footer layout.
6. Review global colors/typography.
7. If WooCommerce is available, choose the Woo presentation preset/options.
8. Open the front end for review.

The wizard must never silently create authoritative business/domain data. It must not create Woo products, RootProfile identities, ConvertFlow Journeys, or plugin-owned records.

For WordPress-native content such as Pages or Menus, the Control Center should prefer links and guided actions into WordPress-native workflows rather than maintaining duplicate copies.

## 5. Preset system

### 5.1 Initial presets

Ship four presentation presets:

- Business
- Commerce
- Expert / Personal Brand
- Education

These are **visual starting points**, not demo-site content packages.

A preset may define:

- semantic colors;
- typography stack and scale;
- container widths;
- spacing density;
- radius/shadow choices;
- button/card presentation;
- Header composition default;
- Footer composition default;
- generic content presentation defaults;
- Woo presentation defaults when Woo is available.

### 5.2 Preset semantics

Applying a preset is a deterministic write of Theme-owned presentation settings. The frontend must not depend on a remote preset service or on a continuing runtime relationship with the preset definition.

After applying a preset, the user may override individual settings. The Theme may retain `preset_id` as informational metadata, but stored settings are the effective source for rendering.

Presets must have accessible color/contrast baselines and must not import media attachment IDs, menu IDs, product data, profile data, or page content.

## 6. Settings architecture

### 6.1 Storage

Use WordPress public Theme Mod APIs for Theme-owned presentation configuration.

Store one structured Theme Mod, conceptually:

`aznet_theme_settings`

with a versioned schema:

```text
schema_version
preset
 design
 header
 footer
 content
 woocommerce
```

WordPress-owned settings remain WordPress-owned:

- `custom_logo` remains the native Custom Logo Theme Mod;
- nav menu locations remain native WordPress nav menu assignments;
- site title/tagline remain WordPress settings;
- Pages/Posts/Media remain WordPress content.

The Theme must access its structured settings through `get_theme_mod()` / `set_theme_mod()` and must not read/write the underlying `theme_mods_*` option directly.

### 6.2 Normalization and precedence

Render-time precedence:

1. code-defined safe Theme defaults;
2. normalized stored `aznet_theme_settings` values;
3. bounded surface-specific presentation override where the schema explicitly permits it.

Unknown, malformed, or future keys are ignored. Missing keys fall back to defaults. No invalid setting may cause a frontend fatal.

### 6.3 Sanitization

Use an explicit allow-list schema. Every field has a known type and permitted values/range.

Examples:

- colors: validated CSS color values from supported formats;
- enumerations: exact known values only;
- numeric layout values: bounded ranges;
- component IDs: registry-backed allow-list;
- URLs, when ever permitted for Theme-owned presentation metadata, must use WordPress URL sanitization and must not replace provider-owned CTA semantics.

All writes require capability checks and nonces.

## 7. Design controls

Design controls modify Theme semantic presentation, not page content.

### 7.1 Colors

Expose a bounded semantic set instead of dozens of element-specific colors:

- Brand / Primary
- Accent
- Text
- Muted Text
- Background
- Surface
- Border
- Link

The runtime maps these values to `--aznet-theme-*` CSS custom properties.

### 7.2 Typography

Initial version uses curated, dependency-light stacks rather than a proprietary font service.

Expose:

- Body font stack
- Heading font stack
- Base font size scale
- Heading scale
- Line-height density

Custom font upload and remote font marketplaces are out of scope for the first customer-ready track. They may be added later through a WordPress-compatible public capability if needed.

### 7.3 Layout and components

Expose bounded settings for:

- content container width;
- wide container width;
- spacing density;
- corner radius;
- shadow intensity;
- button shape/emphasis;
- card style.

Dynamic values should be emitted as a small sanitized CSS custom-property layer, not as duplicated per-component stylesheets.

## 8. Header Composer

The Header Composer is intentionally smaller than a full visual builder.

### 8.1 Model

Desktop header uses three zones:

- Left
- Center
- Right

Mobile header uses bounded zones appropriate to the current responsive Header implementation.

Registered Header components may include:

- Brand / Logo
- Primary Menu
- Search
- Woo Account — only when public Woo capability exists
- Woo Cart — only when public Woo capability exists
- Header Action Menu — a dedicated WordPress menu location, rather than Theme-owned CTA business data
- Mobile Menu trigger

### 8.2 Interaction

Users can:

- choose a layout preset;
- enable/disable registered components;
- move components between permitted zones;
- reorder components within a zone;
- choose bounded presentation variants such as standard/compact/centered where implemented.

The admin UI may provide drag/reorder assistance, but accessible Move Up / Move Down / Move Left / Move Right controls must exist so the composer is operable without drag-and-drop.

### 8.3 Rendering

The frontend renderer uses a public internal component registry owned by the Theme. Each component has:

- ID;
- label;
- allowed zones;
- availability callback;
- render callback.

Unavailable capability components fail soft and are omitted without breaking the Header.

No component may infer Woo auth/order/cart truth through URLs or private state. No component may infer RootProfile/ConvertFlow domain truth heuristically.

## 9. Footer Composer

Footer follows the same bounded composition principle.

Initial configurable building blocks are based primarily on native WordPress menu locations already registered by the Theme:

- Footer Menu
- Footer Contact Menu
- Footer Social Menu
- Footer Policy Menu
- Site identity/copyright presentation derived from public WordPress site information
- provider presentation slots only when a verified public provider contract exists

Users can choose:

- number of visual columns within supported layouts;
- component placement/order;
- spacing/background presentation variant.

The Theme must not duplicate authoritative organization/contact/social records into a parallel Theme store.

## 10. Generic content controls

Expose presentation-only controls for surfaces the Theme already owns:

- Page
- Post
- Archive
- Search
- 404

Initial controls should stay small and high-value:

- content width variant;
- archive grid/list presentation where supported;
- archive column count within tested responsive bounds;
- card presentation variant;
- featured-image presentation variant;
- content density.

Do not add custom query builders, search engines, content stores, or SEO/domain semantics.

## 11. WooCommerce presentation controls

The WooCommerce section appears only when Woo public capability is available.

It may configure presentation on the already-supported Theme surfaces:

- Single Product
- Product Archive / Shop / Product Taxonomy
- Cart
- Checkout
- My Account

Examples of allowed settings:

- product shell density/layout variant where compatible with native Woo markup;
- archive grid columns within responsive bounds;
- product card image ratio/presentation;
- sale badge presentation;
- price visual emphasis;
- Cart/Checkout surface density;
- My Account navigation presentation.

Forbidden:

- product/price/stock/variation storage;
- cart or checkout mutation;
- payment/order behavior;
- authentication behavior;
- custom commerce routing;
- ConvertFlow Product Journey/Filter/Fit/Fast Conversion semantics.

Prefer public Woo hooks/CSS/Blocks-compatible presentation; template overrides remain exceptional.

## 12. Gutenberg Patterns instead of a proprietary page builder

AZnet Theme will rely on the WordPress block editor for page content authoring.

Ship a curated AZnet pattern category with reusable presentation patterns. Initial target library may include approximately 10–12 patterns such as:

- Hero
- Hero + trust strip
- Feature grid
- Services grid
- Logo/client strip
- Stats
- Media + text
- Testimonial layout
- FAQ presentation
- CTA band
- Contact intro
- Blog/latest-posts presentation

Patterns are layout/presentation templates with safe placeholder content. They must not create a parallel content model or encode ConvertFlow Journey semantics.

Patterns should inherit Theme semantic tokens so applying a Design preset updates their visual language consistently.

## 13. Integrations screen

The Integrations screen is diagnostic and navigational, not a plugin control panel.

Each provider adapter may report only what its verified public capability probe allows.

Examples:

- WooCommerce: available/unavailable through the existing public Woo capability boundary;
- RootProfile: connected only when the required public/versioned current-surface contract is actually available;
- ConvertFlow: connected only through a verified public integration capability; otherwise the Theme must not inspect private classes/options/storage to guess.

For missing or incompatible providers, show a clear fail-soft state. Do not fabricate integration PASS from fixtures.

## 14. Import / Export

Tools may export/import **portable Theme-owned presentation settings only**.

Export format includes:

- format marker;
- schema version;
- Theme version metadata;
- portable `aznet_theme_settings` fields.

Do not export site-specific authoritative identifiers by default:

- media attachment IDs;
- menu term IDs;
- page/post IDs;
- product/order IDs;
- RootProfile entity IDs;
- ConvertFlow Journey state.

Import is transactional:

1. parse;
2. validate format/schema;
3. normalize/sanitize entire payload;
4. reject unsupported/incompatible payload without partial writes;
5. write Theme presentation settings once validation passes.

Provide a Theme-settings reset action with an explicit confirmation and nonce. Reset must not delete WordPress content or provider/domain data.

## 15. Implementation constraints

### 15.1 No new build stack by default

Keep the v0.x technical baseline: PHP + CSS + minimal vanilla JavaScript where interaction requires it. Do not introduce React, a bundler, or a proprietary component framework merely to create the Control Center.

### 15.2 Admin code isolation

Suggested ownership-oriented source structure:

```text
inc/admin/
  bootstrap.php
  control-center.php
  settings.php
  presets.php
  components.php
  import-export.php

assets/css/admin/
assets/js/admin/

inc/theme/
  settings.php
  dynamic-tokens.php
  header-components.php
  footer-components.php

patterns/
```

Exact filenames may be refined during implementation planning, but admin concerns must not be mixed into Woo/domain/provider adapters.

### 15.3 Asset scoping

Control Center admin CSS/JS loads only on AZnet Theme admin screens. Frontend dynamic settings emit only the minimal presentation layer required for the current Theme.

No global loading of plugin/provider bundles.

## 16. Failure and compatibility behavior

- Missing stored settings → safe Theme defaults.
- Malformed stored setting → ignore that value and use default.
- Unknown composer component → omit safely.
- Missing WordPress menu assignment → omit frontend menu; show actionable state in Control Center.
- Missing WooCommerce → Woo components/settings do not render and Theme remains functional.
- Missing provider contract → provider-owned component omitted; no private fallback read.
- Import schema mismatch → reject import with no partial mutation.
- Theme update → preserve Theme Mods and native WordPress assignments.
- Theme switch → domain/plugin data remains untouched; AZnet Theme settings may remain stored for reuse if the Theme is reactivated.

## 17. QA strategy

No QA layer may be inferred from another.

### L0 — Source / State

- approved design and ownership boundaries;
- exact baseline/branch;
- source-document impact identified before production implementation.

### L1 — Static

- PHP syntax;
- naming/prefix scans;
- capability/nonce checks;
- forbidden private storage/class reads;
- admin asset scope;
- no foreign product source.

### L2 — Contract / TDD

RED → GREEN contracts for:

- settings normalization;
- sanitization;
- default fallback;
- preset application;
- Header/Footer component registry and availability;
- import/export schema;
- no domain-key persistence;
- dynamic token generation;
- admin asset scoping.

### L3 — Runtime

Real WordPress runtime:

- activate from existing alpha package;
- open Control Center;
- save settings;
- apply preset;
- render frontend;
- Woo on/off smoke;
- provider absent fail-soft.

### L4 — Browser / Visual / A11y

- Control Center desktop usability;
- keyboard operation of composer/reorder controls;
- responsive frontend after settings changes;
- no overflow/destructive layout collision;
- visible focus;
- zero critical/serious a11y violations on agreed matrix;
- screenshot review for presets and key surfaces.

### L5 — Integration

Only when real public contracts exist:

- Woo present/absent;
- RootProfile present/absent/version mismatch;
- ConvertFlow present/absent/version mismatch;
- Theme switch/coexistence;
- no ownership leakage.

### L6 — Customer-ready delivery

- in-place upgrade from the previous pilot version using stable `aznet-theme/` directory;
- settings preservation;
- installable ZIP verification;
- rollback package/checkpoint;
- agreed pilot-site acceptance.

## 18. Delivery strategy — fastest safe route

Do not attempt the entire Control Center in one large branch. Deliver customer-usable value incrementally.

Recommended workstream:

### U0 — Control Center foundation

- top-level admin page;
- Overview;
- versioned Theme settings schema;
- capability/nonce/sanitization foundation;
- scoped admin assets.

**Customer value:** the Theme becomes visibly manageable instead of opaque.

### U1 — Design + Presets

- four presets;
- semantic colors;
- typography;
- width/spacing/radius/shadow/button/card controls;
- dynamic CSS token output.

**Customer value:** a new site can acquire a coherent visual identity quickly.

### U2 — Header + Footer Composer

- bounded component registry;
- zones/order/layout presets;
- accessibility-safe reorder UI;
- capability-aware Woo elements;
- WordPress menu ownership preserved.

**Customer value:** the most visible site shell becomes configurable without code.

### U3 — Content + Woo presentation options

- high-value generic surface controls;
- Woo surface presentation controls;
- no commerce/domain mutation.

**Customer value:** standard client pages and shops can be tuned without CSS edits.

### U4 — Gutenberg Pattern Library

- curated token-aware patterns;
- no proprietary page builder.

**Customer value:** common page sections can be assembled quickly in native WordPress.

### U5 — Integrations + Tools + Update continuity

- integration diagnostics through public capability probes;
- portable presentation Import/Export;
- reset;
- alpha update package with stable root directory;
- preservation tests from previous pilot package.

**Customer value:** repeatable deployment across client sites.

### U6 — Customer-ready QA and pilot closure

- L3/L4 full Control Center matrix;
- L5 only for actually available public integrations;
- update/rollback validation;
- pilot acceptance.

## 19. Versioning intent

Continue the existing Theme line. Do not create independent Theme directories.

The next pilot releases remain updates of the same `aznet-theme` installation, for example alpha.8 onward as slices become usable. Exact version bumps are implementation/release checkpoints, not separate products.

Every installable ZIP must contain a root directory exactly named:

`aznet-theme/`

## 20. Explicit non-goals for the customer-ready track

The following are intentionally excluded unless separately approved later:

- proprietary drag-and-drop page builder;
- Flatsome/UX Builder code, assets, copied layouts, branding, or UI clone;
- remote Template/Studio marketplace;
- custom Woo commerce engine;
- custom product filter/journey engine;
- custom profile/identity engine;
- SEO engine;
- form/conversion engine;
- custom updater/licensing server;
- font marketplace/custom font subsystem;
- arbitrary CSS/JS injection manager;
- plugin admin settings mirrored into Theme.

## 21. Source-governance impact

This specification represents a substantive Theme product/architecture addition, not an implementation progress log.

After owner approval of this written specification and before production implementation is treated as canonical, the accepted decision must be reconciled once into the appropriate authoritative AZnet Theme source topics (Product Charter/Ownership, Architecture, Roadmap/Decision Log as applicable). That source update must describe the enduring capability and ownership boundary only; subsequent U0–U6 progress belongs in GitHub/evidence and must not cause repeated source-document version churn.

## 22. Acceptance criteria for this design

The design is accepted when the owner confirms all of the following intent:

- AZnet Theme becomes easy to configure from one Control Center;
- the usability inspiration is ease-of-use, not copying Flatsome;
- native WordPress block editing/patterns replace the need for a proprietary page builder;
- Theme configuration remains presentation-only;
- Header/Footer composers are bounded component arrangers, not arbitrary builders;
- Theme does not duplicate WordPress/Woo/RootProfile/ConvertFlow authoritative data;
- incremental alpha updates remain the fastest delivery mechanism;
- customer-ready status still requires the appropriate runtime/browser/integration/release evidence rather than feature count alone.

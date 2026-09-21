# Curtain 01 Pilot — design packet

**Date:** 21/09/2026  
**Status:** Proposed / source-gated  
**Target product:** AZnet Theme  
**Pilot site:** `https://remquocanh.vn/`  
**UI label:** Rèm 01  
**Proposed machine keys:** `curtain-01` (presentation preset), `curtain-v1` (provisioning blueprint)

## 1. Goal

Create one reusable curtain/interior website presentation for AZnet Theme that can replace a Flatsome-based site without turning AZnet Theme into a page builder, commerce engine, product decision engine or second content store.

`remquocanh.vn` is the migration pilot, not the semantic owner of the template. The resulting Rèm 01 preset must be reusable for another curtain business after replacing WordPress/WooCommerce-owned content, media and mappings.

## 2. Problem to solve

A Flatsome site can contain three different classes of state:

1. WordPress/WooCommerce-owned data that should survive a theme switch naturally;
2. presentation assembled with Flatsome/UX Builder shortcodes or theme-specific components;
3. optional plugin/domain data that must remain owned by its provider.

A successful migration is therefore not "switch theme and restyle". The Theme must provide a destination presentation and a controlled migration path while preserving authoritative data and rollback.

## 3. Ownership boundary

### AZnet Theme owns

- Rèm 01 visual tokens and bounded preset selection;
- Header/Footer composition and responsive presentation;
- Homepage composition around the native Front Page content boundary;
- Page/Post/archive/search/404 presentation;
- WooCommerce presentation shell only;
- reusable cards/grids/gallery presentation primitives;
- explicit typed source references used only for presentation;
- a confirmed/idempotent WordPress-native starter blueprint when creating a new site.

### WordPress owns

- Page/Post/Menu/Media;
- native editorial content and publication state;
- native taxonomy/query state;
- synced/core-block Hero content;
- project/article content when represented as ordinary WordPress content.

### WooCommerce owns

- Product, variation, SKU, price, stock, catalogue, cart, checkout, order and account state.

### ConvertFlow owns when present

- Product Journey, suitability/Fit semantics, filter/decision logic, quote-vs-buy behavior, conversion state and analytics.

### RootProfile owns when present

- Person/Organization identity, authoritative contact/social/profile semantics and public-safe provider payloads.

## 4. Hard rules

- No Flatsome runtime dependency in Rèm 01.
- Do not implement a Flatsome/UX Builder shortcode interpreter in AZnet Theme.
- Do not copy Flatsome theme options or UX Builder serialized state into AZnet Theme as a new master store.
- Do not copy WooCommerce Product facts into Theme settings.
- Do not reconstruct ConvertFlow Fit/filter/Journey semantics in Theme.
- Do not read private plugin option/meta/table/class internals.
- Preset switching is presentation-only and must not create/rewrite/delete/publish WordPress content.
- Any starter provisioning remains a separate explicit confirmed operation.
- Existing-site migration must preserve a tested Flatsome rollback path until AZnet Theme browser/integration gates pass.

## 5. Proposed Rèm 01 information architecture

The visual template is generic; each slot fails soft when its mapped source is absent.

### Site shell

- compact utility/contact strip when a legitimate public source exists;
- brand/logo + primary navigation;
- optional search/account/cart actions through existing public WordPress/WooCommerce capabilities;
- sticky main Header only;
- premium Footer using WordPress-owned menus/contact sources.

### Homepage

1. Hero — one WordPress-owned synced Hero; Theme controls composition only.
2. Collection / solution gateway — explicit mapped WordPress/WooCommerce public source.
3. Featured product presentation — WooCommerce-native/public output only; absent Woo => section hidden.
4. Use-case / room / application presentation — ordinary WordPress content unless a public ConvertFlow projection is available; Theme must not invent Fit semantics.
5. Project showcase — ordinary WordPress-native content/media with exact explicit mapping.
6. About / craft / experience — mapped Page content.
7. Trust / process / service promise — mapped Page/core-block content; no fabricated statistics, credentials or guarantees.
8. Latest knowledge / project stories — mapped WordPress categories.
9. Contact / quotation CTA — presentation link to the authoritative owner surface; no lead/quotation state in Theme.

The native Front Page must retain exactly one `the_content()` boundary so public WordPress/provider filters remain intact.

## 6. Inner surfaces

Rèm 01 should reuse existing AZnet Theme capabilities instead of creating a parallel stack:

- Page Experience 2.0 for About/Contact/Collection landing pages;
- Editorial Post/Archive/Search surfaces for Knowledge and project stories;
- Header System 2.0;
- Footer System 2.0;
- WooCommerce Presentation 2.0 for Shop/Product/Cart/Checkout/Account;
- Theme design tokens and surface-aware assets;
- optional RootProfile/ConvertFlow public-contract integration only where already supported.

A Rèm-specific override is justified only when the generic destination cannot meet the visible acceptance criteria.

## 7. Visual direction

Initial reference direction: warm premium interior presentation rather than legal/editorial Burgundy.

- warm ivory / soft stone surfaces;
- charcoal text;
- restrained bronze or warm wood accent;
- large interior photography;
- generous whitespace and full-bleed media bands;
- product cards with quiet borders and strong image priority;
- minimal motion, no heavy slider dependency by default;
- Vietnamese long-title and mobile-first geometry retained.

Brand-specific colors, logo geometry and actual photography must be validated from the pilot before final acceptance. The template must not hardcode Rèm Quốc Anh identity.

## 8. Flatsome migration model for the pilot

### M0 — read-only inventory

Capture:

- active Theme/version and child-theme state;
- active plugins relevant to rendering;
- Front Page assignment;
- menus/widgets;
- Pages/Posts/Categories;
- WooCommerce products/categories and shop pages;
- media used by key surfaces;
- exact pages whose `post_content` contains Flatsome/UX Builder shortcodes;
- redirects/permalinks/canonical public URLs;
- current screenshot baselines at desktop/tablet/mobile.

No write occurs in M0.

### M1 — classify

Each current pilot surface is classified as:

- **KEEP** — WordPress/WooCommerce/provider data already portable;
- **MAP** — authoritative content stays in place; Rèm 01 receives an explicit typed reference;
- **REBUILD PRESENTATION** — UX Builder layout is replaced with native WordPress content + Theme presentation;
- **OWNER CONTRACT** — needs a public provider projection before Theme may render it;
- **RETIRE LATER** — Flatsome-only presentation removed only after destination + fallback + regression PASS.

### M2 — preview candidate

Build a non-production AZnet Theme candidate using pilot data/mappings.

Do not switch the live site yet. Do not rewrite authoritative product/contact/identity state.

### M3 — content portability

For pages whose visible content is trapped in UX Builder shortcodes:

- extract editorial copy/media as migration evidence;
- recreate the destination using ordinary WordPress content/Core blocks/Theme patterns;
- keep old content/Flatsome installation available as rollback until cutover closure;
- never make AZnet Theme dependent on Flatsome shortcodes.

### M4 — QA

Required minimum:

- desktop 1440;
- tablet 1024;
- mobile 390;
- narrow 320 where risk exists;
- no horizontal overflow;
- keyboard/focus/landmarks/heading checks;
- product/archive/cart/checkout/account runtime when Woo is present;
- Flatsome absent from runtime candidate;
- ConvertFlow/RootProfile absent/present paths only at accepted public contracts;
- URL/permalink continuity;
- visible content parity for the accepted pilot scope.

### M5 — production cutover

Production activation is a separate explicit owner gate.

Preflight must verify:

- fresh backup/rollback;
- exact AZnet Theme package identity;
- current live Theme and plugin state;
- no unresolved UX Builder-only critical page;
- browser matrix PASS on the candidate;
- rollback path to the prior Flatsome Theme is available.

## 9. Proposed provisioning blueprint

`curtain-v1` may be added to the existing provisioning blueprint registry for greenfield sites after source approval.

It may create/map only ordinary WordPress-native objects through the existing confirmed change-plan workflow. Suggested roles:

- home;
- about;
- collections;
- projects;
- knowledge;
- contact;
- optional service/process page;
- primary menu.

Commerce catalogue content is not starter-owned by Theme. The blueprint must not seed fake Products, prices, stock, reviews, ratings, customer logos, project claims, years of experience or contact facts.

## 10. Implementation shape after source approval

Prefer the smallest extension of existing systems:

- add `curtain-01` to the existing Homepage preset allow-list;
- add a scoped Rèm 01 homepage renderer and CSS loaded only on that preset;
- reuse existing Hero Library rather than create another Hero store;
- add only typed source mappings actually required by the accepted design;
- add `curtain-v1` to the existing provisioning registry only if greenfield setup is included in the first release;
- reuse Header/Footer/Woo/Page/Editorial systems before adding Rèm-specific primitives;
- RED -> minimal GREEN -> retained regression for each bounded slice.

Do not first generalize the entire Theme into a new builder/DSL. A reusable registry abstraction is warranted only where the second real blueprint exposes concrete duplication.

## 11. Evidence plan

### C0 — source/design gate

- D-034 accepted in AZT-04;
- design packet approved;
- pilot inventory still allowed to remain read-only.

### C1 — pilot inventory

- M0 inventory evidence;
- Flatsome dependency matrix;
- screenshots;
- exact migration acceptance list.

### C2 — Rèm 01 shell

- RED/GREEN preset contract;
- Theme-owned visual tokens and asset scope;
- no domain/private storage reads.

### C3 — Homepage

- RED/GREEN composition;
- exactly one native `the_content()` boundary;
- explicit typed mappings;
- absent/missing source fail-soft.

### C4 — Woo / inner-page coexistence

- existing Woo/Page/Post systems retained;
- Rèm-specific regression only where necessary.

### C5 — migration candidate runtime/browser

- real WordPress runtime;
- pilot-data preview;
- responsive/a11y;
- no Flatsome runtime dependency.

### C6 — integration / release candidate

- Woo/public provider matrix as applicable;
- package/SHA;
- exact-byte verification;
- rollback evidence.

### C7 — production cutover

- explicit owner approval only after C1-C6 PASS.

## 12. Current evidence / blocker

At the start of this design slice, public retrieval of `https://remquocanh.vn/` returns an upstream fetch failure in the available web path, and the site is not among the currently connected WPVibe sites. Therefore the current live information architecture, exact Flatsome/UX Builder shortcode footprint and active plugin/theme versions are **UNKNOWN** here.

This does not block the repo-side source/design work. It does block any claim that Rèm 01 already matches the live pilot or that a Flatsome cutover is safe.

## 13. Rollback

Before live cutover, rollback is simply: do not activate/publish the candidate.

After a later approved cutover, rollback must restore the previously verified Flatsome Theme and captured Theme presentation settings without deleting WordPress/WooCommerce/provider data.

## 14. Exact next

Obtain owner approval for the D-034 source decision. After source merge, perform the read-only pilot inventory before production implementation is allowed to claim Rèm Quốc Anh parity or migration readiness.

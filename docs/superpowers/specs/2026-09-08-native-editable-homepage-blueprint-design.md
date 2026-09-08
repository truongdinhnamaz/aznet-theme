# AZnet Theme — Native Editable Homepage Blueprint Design

**Date:** 2026-09-08  
**Status:** Design approved in chat; awaiting written-spec review before implementation  
**Canonical base:** `main@08b74209013fc7c71d2a6d98ea0e457ccfd336d4`  
**Product:** AZnet Theme  
**Pilot:** Tâm Đức Hà Nội  

## 1. Problem and concrete P4 invalidation

The real pilot proves that AZnet Theme's current Homepage implementation is technically correct as a native WordPress shell but is not sufficient as a production-ready Homepage experience.

Current `front-page.php` owns only the Theme shell and renders the selected WordPress Page body through `the_content()`. On the pilot, that Page body is effectively empty, so the visible result is Header -> large blank content area -> Footer.

This does **not** invalidate the existing ownership boundary or the technical F Homepage shell evidence. It concretely invalidates the assumption that a valid shell alone is enough to call the Homepage presentation complete for a real professional-services/editorial site.

## 2. Decision

Implement a **Native Editable Homepage Blueprint** as a Theme-supplied full-page WordPress Block Pattern.

The Theme will provide the presentation composition. The user will explicitly insert that pattern into the existing static Front Page using the WordPress Page editor. Once inserted, the blocks become normal WordPress Page content and remain editable without a proprietary builder.

The Theme will **not** auto-create, auto-publish, overwrite, replace or silently mutate Page content on activation, update or settings save.

## 3. Ownership boundary

The existing architecture remains authoritative:

- **WordPress owns:** Page content, Page identity, menu state, media, Posts, categories/tags, and Query Block semantics.
- **AZnet Theme owns:** block-pattern composition, visual hierarchy, responsive presentation, typography, spacing, card treatment, section treatment and editor/frontend presentation support.
- **RootProfile owns:** identity/profile semantics; the Homepage blueprint must not infer or copy identity data.
- **ConvertFlow owns:** Journey/resolver/state/conversion semantics; the Homepage blueprint must not read its private state or require it.
- **Rank Math / SEO plugins own:** SEO metadata/schema/canonical/OG generation. The Theme must not duplicate them.

No provider contract, domain ownership or data-source authority changes are introduced by this design.

## 4. User workflow

### 4.1 Existing static Front Page

When a static Front Page exists, the Control Center Overview will provide a clear Homepage Setup card with:

1. a WordPress-native link to edit the current Front Page;
2. short guidance: open Block Inserter -> Patterns -> `AZnet — Pages` -> `Homepage — Professional Services`;
3. a statement that inserting the pattern writes normal editable WordPress blocks into that Page;
4. no one-click content mutation from the Theme.

### 4.2 No static Front Page configured

If WordPress is configured to show latest posts or there is no usable static Front Page, the Control Center will link to the native WordPress Reading/Page configuration surface and explain that the blueprint is intended for a static Page.

The Theme will not create the Page automatically.

## 5. Pattern identity and scope

Add a native pattern category:

`aznet-theme-pages` -> `AZnet — Pages`

Add one full-page pattern for this slice:

`aznet-theme/homepage-professional-services`

Display title:

`Homepage — Professional Services`

The pattern must be generic enough for legal, consulting, education, agency and expert-service sites. Tâm Đức Hà Nội is the pilot, not a domain fork. No legal-specific semantics, claims, service taxonomy or regulatory text may be hard-coded into the Theme.

## 6. Homepage composition

The first production blueprint will contain these native sections in this order.

### 6.1 Hero

- one page-level H1;
- short eyebrow;
- concise supporting paragraph;
- primary CTA and secondary CTA using core Button blocks;
- default CTA targets are in-page anchors `#services` and `#contact`, with corresponding target sections in the same pattern;
- no hard-coded external URL, Page ID or provider route;
- starter text must be clearly editable and must not assert unverified business facts.

### 6.2 Trust / decision-support strip

Three compact columns for user-replaceable values such as process clarity, professional responsibility or support expectations.

This section must avoid fabricated testimonials, certifications, client counts, ratings or other unverified evidence.

### 6.3 Services / expertise overview

A responsive card grid built from core Group/Columns blocks and carrying the `services` anchor target.

The pattern provides presentation placeholders only. The Theme does not define authoritative legal services, product taxonomy or business-domain categories.

### 6.4 Authority / about section

A two-column editorial section for organization/expert introduction and an optional CTA.

It is Page content, not RootProfile identity projection. A future RootProfile enhancement may only consume an accepted public contract and is not part of this slice.

### 6.5 Process section

Three concise steps presented as editable native blocks. The copy must be generic and replaceable, not a Theme-owned business workflow engine.

### 6.6 Latest articles

Use a native core Query block for recent Posts, consistent with the existing `content-article-grid` ownership model:

- WordPress owns query semantics;
- no custom `WP_Query` in the Theme blueprint;
- featured image, title, date and excerpt are presentation only;
- no related-post ranking or recommendation engine.

### 6.7 FAQ

Use native Details/Heading/Paragraph blocks with replaceable starter content.

The Theme does not create legal advice, structured FAQ schema or domain answers.

### 6.8 Final CTA / contact handoff

A final call-to-action section carrying the `contact` anchor target, with native Buttons and editable contact-oriented copy.

No contact form engine, lead state, conversion tracking or private provider integration is introduced.

## 7. Content portability

The full-page pattern must be composed from WordPress core blocks and normal serialized Page content.

After insertion, the Page must remain meaningful if AZnet Theme is later switched away. Therefore:

- do not persist a Theme-private Page schema;
- do not require a Theme custom post type;
- do not store section content inside Theme Mods;
- do not rely on runtime-only `wp:pattern` references for essential stored Page content;
- do not embed assets whose continued existence depends on a specific Theme directory path;
- use WordPress Media Library content only when the user chooses/replaces media.

Theme-specific CSS classes are allowed as presentation hooks, but loss of those styles after a Theme switch must not destroy the underlying WordPress content structure.

## 8. Presentation implementation

Add a small dedicated Homepage component stylesheet, expected path:

`assets/css/components/homepage.css`

Frontend loading must be surface-aware:

- enqueue only for `is_front_page()`;
- selectors must be scoped under a blueprint root class such as `.aznet-theme-homepage-blueprint`;
- no JavaScript is required for the initial blueprint;
- existing design tokens and `theme.json` presets remain the visual source;
- no new bundler/build stack.

Editor parity will use `enqueue_block_editor_assets` to load the same small scoped Homepage stylesheet in the Block Editor. Because every selector is rooted under `.aznet-theme-homepage-blueprint`, the stylesheet remains inert for editor content that does not contain the blueprint. This editor asset does not create a second content model.

## 9. `front-page.php` behavior

The existing `front-page.php` content boundary remains intact:

Header -> one Theme-owned `main#main` -> WordPress Loop -> `the_content()` -> Footer.

Do not hard-code the new Homepage sections into `front-page.php`.

If the Page remains empty, the Theme continues to fail soft rather than secretly injecting business content. The Control Center setup guidance is the discoverability mechanism.

## 10. Control Center behavior

The existing Overview/Patterns guidance may be refined into a Homepage-specific card.

Allowed behavior:

- read WordPress-native Front Page configuration through WordPress APIs;
- show status and edit/configuration links;
- explain where to insert the Theme pattern.

Forbidden behavior:

- auto-create Page;
- auto-set `page_on_front`;
- auto-overwrite `post_content`;
- auto-publish content;
- create a parallel Homepage settings/content store.

No new Theme setting is required for this feature.

## 11. Accessibility and responsive requirements

The blueprint must pass at minimum:

- exactly one H1 in the Page blueprint;
- coherent H1 -> H2 -> H3 hierarchy;
- keyboard-reachable links/buttons;
- visible focus treatment inherited from Theme primitives;
- axe critical/serious findings = 0 in the tested blueprint scope;
- no horizontal overflow at 1440, 1024, 390 and 320 px viewport widths;
- media contained within viewport;
- Query cards reflow cleanly;
- long Vietnamese titles and diacritics wrap safely;
- landmarks remain owned by the Theme shell, without nested document-level `main` elements.

## 12. TDD and verification plan

Implementation must use RED -> minimal GREEN -> regression.

### 12.1 Offline pattern contract — RED first

Add a contract that fails before the full-page pattern exists and then protects:

- expected pattern slug/title/category;
- one H1 and required section markers;
- matching `services` / `contact` in-page anchor targets;
- native core Query block for recent Posts;
- native FAQ/details presentation;
- no provider-private APIs/storage;
- no custom query engine;
- no hard-coded Page/Post IDs;
- no SEO/schema generation;
- no auto-content mutation logic.

### 12.2 Asset-scope contract — RED first

Protect that Homepage CSS is frontend-enqueued only for the Front Page, is editor-enqueued through the explicit editor hook, and remains root-class scoped.

### 12.3 Control Center guidance contract

Protect the Homepage Setup guidance and native edit/configuration links without adding a content-write action.

### 12.4 Runtime/browser GREEN gate

On disposable clean WordPress:

1. register the Theme pattern;
2. create a static Page from the registered pattern content;
3. set it as Front Page through the test fixture;
4. create representative Posts so the native Query block renders real cards;
5. test 1440/1024/390/320;
6. verify Header/Main/Footer, one `main#main`, one H1, expected sections, Query cards and FAQ;
7. verify overflow, keyboard/focus and axe;
8. scan runtime logs for Theme-caused PHP fatal/warning/parse/uncaught markers.

The fixture may write Page content because the test owns its disposable WordPress environment. Production Theme code may not do so.

### 12.5 Retained regression

Run reusable v1 core verification and retained Homepage/Pattern/Header/Control Center regressions. Woo regressions remain retained even though the Tâm Đức pilot has WooCommerce absent, because the Theme is a generic product.

## 13. Pilot acceptance

After verified implementation and owner-approved merge/package replacement on Tâm Đức Hà Nội:

1. open the existing Front Page in WordPress editor;
2. insert `Homepage — Professional Services` once;
3. replace starter copy with site-authoritative content;
4. replace/add Media Library assets as desired;
5. publish using WordPress;
6. run the P4 real-site Homepage matrix on desktop/tablet/mobile;
7. continue P4 through long Post, Page, archive/search/404, RootProfile/Rank Math compatibility and measured performance.

The pilot content edit is a WordPress content operation, not Theme-owned authoritative domain mutation.

## 14. Rollback

Code rollback:

- revert the bounded Theme commit/PR; existing Page content remains WordPress-owned.

Pilot content rollback:

- use WordPress Page revisions or restore the pre-insertion Page revision.

Theme update rollback remains the existing stable-folder/package recovery path. No external provider data is changed by this feature.

## 15. Exit gate

This Homepage hardening slice is PASS only when:

- offline ownership/pattern/asset contracts are GREEN after proven RED;
- full production PHP lint is GREEN;
- clean WordPress runtime/browser/a11y matrix is GREEN at required viewports;
- retained core regressions are GREEN;
- exact candidate package is deterministic and installable;
- real Tâm Đức Homepage is populated through native Page blocks and visually passes P4 Homepage review;
- no tag, GitHub Release or final production-deployment claim is made without the separate P5 owner gate.

## 16. Exact implementation boundary

Expected production touch points are bounded to:

- `patterns/homepage-professional-services.php`;
- `inc/theme/patterns.php` for the new Pages category if needed;
- `assets/css/components/homepage.css`;
- `inc/theme/assets.php` for Front Page/editor asset scope;
- `inc/admin/control-center.php` for non-mutating Homepage setup guidance;
- tests/workflow/evidence needed to prove the slice.

`front-page.php` should remain unchanged unless a test exposes a concrete Theme-owned defect in its existing shell/content boundary.

No RootProfile, ConvertFlow, WooCommerce extension, Rank Math or other product repository/source is modified.
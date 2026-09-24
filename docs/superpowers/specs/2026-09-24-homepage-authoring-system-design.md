# AZnet Theme — Preset-Isolated Homepage Authoring System Design

**Date:** 2026-09-24  
**Status:** Design approved in chat; written-spec review required before implementation  
**Canonical base:** `main@9b93fd76f7d3238734bec58e247bf98d61851618`  
**Product:** AZnet Theme  
**Scope:** Homepage Composer authoring architecture, preset-isolated Content Maps, Control Center authoring bridge, legacy compatibility and migration  
**Pilot:** `lstamduchn.vn` using `law-01`

## 1. Problem

AZnet Theme already has a WordPress-native Homepage Composer, Law 01 and Curtain 01 presentation presets, typed WordPress source references, Control Center mapping UI, and D-030 Hero Library + synced WordPress Hero content.

The current authoring model does not yet satisfy the product requirement that a normal site administrator can control all editorial content visible on the Homepage without editing Theme source code.

Two defects are architectural rather than cosmetic:

1. several Homepage source mappings remain generic across presets, so changing a source for one preset can unintentionally change another preset;
2. visible editorial copy still exists in preset renderers as hard-coded Theme strings, while the Control Center is still primarily a technical mapping console rather than a practical Homepage authoring surface.

The target outcome is not a page builder. The target is a reusable authoring system in which each preset has isolated Theme-owned references/presentation state, while the actual authored content stays WordPress-native and portable.

## 2. Approved product decision

Implement one shared **Homepage Authoring System** with:

1. **preset-isolated Content Maps** by default;
2. **one WordPress-native source of truth** for editable content;
3. a **Control Center authoring bridge** that supports both Quick Edit and opening the full native WordPress editor;
4. **explicit sharing only** when an administrator deliberately maps two presets to the same WordPress object;
5. non-destructive, backward-compatible migration from legacy generic Homepage mappings;
6. D-030 Hero retained and extended as the canonical new Hero authoring model.

The core invariant is:

> A preset switch changes presentation selection only. It must not create, rewrite, duplicate, delete, publish, reclassify or remap another preset's WordPress content.

A second invariant is:

> Theme settings may store presentation state and typed references, but never become the authoritative store for editorial Homepage copy.

## 3. Source and ownership constraints

This design is constrained by the current AZT product constitution and architecture:

- AZnet Theme is a presentation owner, not a business/domain engine or page builder.
- WordPress owns Post/Page/Menu/Media/query/editorial lifecycle.
- Theme-owned settings remain in the single normalized `aznet_theme_settings` family.
- Theme must not create a proprietary Homepage content schema or custom domain store.
- Public/provider integrations remain optional, fail-soft and versioned.
- Theme must not infer authoritative sources by slug, title, URL or other heuristics.
- Theme switching must not delete WordPress-owned content.

### 3.1 WordPress owns

- Page/Post titles, bodies and excerpts;
- featured images and Media attachments;
- Categories and taxonomy membership;
- synced `wp_block` content used for Hero or other bounded authoring sources;
- publication state and revision/editorial lifecycle;
- child-Page content used by Page-backed collections.

### 3.2 AZnet Theme owns

- Homepage preset selection;
- preset section registry;
- preset-scoped typed references;
- layout/composition/order;
- visual variants and responsive/a11y presentation;
- bounded query presentation policy;
- Control Center authoring UI;
- validation, diagnostics and fail-soft source resolution;
- explicit source-sharing warnings;
- compatibility resolution for legacy Theme references.

### 3.3 External domain owners retain ownership

RootProfile, ConvertFlow and WooCommerce ownership is unchanged. This design does not authorize private reads, direct storage writes, semantic reconstruction or domain takeover.

## 4. Preset-isolated Content Map

The current logical model of one generic Homepage Content Map is replaced by a shared resolver with **preset-scoped mappings**.

Conceptually:

```text
homepage.presets
  law-01
    hero
    services
    about
    team
    knowledge
    case_analysis
    legal_news
    process
    faq
    final_cta

  curtain-01
    hero
    proof
    services/catalogue
    about
    process
    projects
    knowledge
    final_cta
```

The implementation may use normalized flat keys or a bounded structured map inside the existing Theme Mod, but callers must consume a stable internal API rather than read storage keys directly.

Target internal interface shape:

```php
homepage_source( 'law-01', 'about' );
homepage_source( 'curtain-01', 'about' );
```

The renderer therefore depends on the logical contract `preset + slot -> typed source`, not on a globally shared setting key.

### 4.1 Isolation rule

Default behavior is full isolation:

- changing `law-01.about` must not change `curtain-01.about`;
- changing `law-01.services` must not change Curtain 01 mappings;
- switching `law-01 -> curtain-01 -> law-01` must restore each preset with its previous mappings intact.

Sharing remains possible only by explicitly selecting the same WordPress object in both presets.

## 5. Preset section registry

The reusable system requires a Theme-owned registry that declares what each preset can render and author.

Each registry entry defines:

- preset key;
- section/slot key;
- display label;
- source type;
- allowed source object type;
- renderer;
- diagnostic capability;
- Quick Edit field capability;
- full-editor action capability;
- optional collection behavior;
- optional presentation variant;
- fallback/absence rule.

Conceptual contract:

```text
Preset
  Section
    source
    editable fields
    collection items
    media
    actions
    presentation variant
```

The registry is not content. It is a Theme-owned presentation/authoring contract.

It must remain bounded and explicit. This is not a generic drag/drop page builder, generic query builder or user-defined arbitrary section engine.

## 6. Authoring Bridge: Quick Edit + full native editor

The Control Center becomes the practical Homepage authoring entry point while WordPress remains the storage owner.

For every editable section, the Control Center should present:

- section name;
- current status;
- current source;
- small preview/thumbnail when useful;
- **Sửa nhanh**;
- **Chỉnh đầy đủ**;
- **Đổi nguồn** when the slot supports source replacement.

### 6.1 Quick Edit rule

Quick Edit is a convenience UI over the authoritative WordPress object.

Examples:

- Page title -> `post_title`;
- Page excerpt -> `post_excerpt`;
- featured image -> WordPress Media/featured-image relation;
- child Page title/excerpt/image -> fields on that child Page;
- synced Hero content -> the referenced WordPress `wp_block`.

Quick Edit must not copy these values into `aznet_theme_settings`.

### 6.2 Full editor rule

Every Quick Edit surface must retain a route to the native WordPress editor for the authoritative source.

The Control Center must not attempt to replace the full WordPress editor for long-form or structurally complex content.

### 6.3 Mutation safety

Quick Edit actions are write operations and therefore require:

- appropriate WordPress capability checks;
- nonce validation;
- object-type validation;
- explicit target object identity from the typed mapping;
- sanitization appropriate to the edited field;
- no heuristic target resolution;
- no mutation of another preset's mapping.

## 7. Editorial copy versus UI microcopy

The requirement is that all **editorial content** visible on the Homepage has an editable WordPress-native source.

Editorial examples:

- section eyebrow/label with site-specific meaning;
- section heading;
- section introduction;
- Hero value proposition;
- CTA copy that expresses site-specific intent;
- service names/descriptions;
- About copy;
- Process/FAQ content;
- site-specific trust/supporting messages.

Theme-owned UI microcopy remains allowed when it is generic presentation language, for example:

- "Xem chi tiết";
- "Đọc thêm";
- Previous/Next controls;
- accessibility labels;
- neutral editor/control labels.

UI microcopy should remain translatable through normal WordPress internationalization and must not be converted into per-site Theme settings merely to make every literal string editable.

## 8. Law 01 target authoring model

Law 01 is the first migration pilot.

The Control Center must present sections in frontend reading order and provide a clear authoring path for each.

Expected Law 01 authoring inventory:

1. Hero;
2. Legal services;
3. About;
4. Team;
5. Latest knowledge/articles;
6. Knowledge topics;
7. Case analysis;
8. Legal news;
9. Process;
10. FAQ;
11. Final CTA/contact.

The exact section order may continue to vary by the approved Law 01 visual variant, but the source mappings remain preset-scoped.

### 8.1 Services

The existing Page-backed service model is retained.

- mapped services parent Page owns section-level editorial content;
- direct published child Pages own individual service items;
- Quick Edit may expose child Page title, excerpt and featured image;
- full editing opens the child Page;
- no Theme CPT or service datastore is introduced.

### 8.2 About

A mapped WordPress Page remains the source.

Quick Edit may expose the bounded fields actually used on the Homepage, while full editor handles long-form content.

### 8.3 Team

The existing WordPress Page teaser remains the standalone-core source unless an accepted public RootProfile collection contract exists later.

This design does not authorize Theme-side person discovery or identity inference.

### 8.4 Query-backed sections

Knowledge, case-analysis and legal-news sections continue to use typed Category references and bounded WordPress queries.

Editorial section headings/introductions that are site-specific must not remain hard-coded in renderers. They must come from an approved WordPress-native source associated with that section.

The design must not add a generic query builder, recommendation engine or ranking system.

### 8.5 Process and FAQ

Mapped Pages remain authoritative.

The Theme may present their authored content but must not infer business semantics from arbitrary body structure beyond an explicitly approved bounded renderer.

### 8.6 Final CTA

Site-specific CTA heading/supporting copy must have a WordPress-native authoring source.

A CTA destination remains a typed reference/public capability. Booking-specific language must not be shown without an actual destination/capability that supports that meaning.

## 9. D-030 Hero continuation

D-030 remains authoritative for the new Hero authoring path:

- WordPress owns one synced `wp_block` Hero using Core blocks;
- Theme owns Hero visual variants and the typed reference;
- changing the variant changes presentation only;
- initialization is explicit and draft-first;
- legacy Page/Site fallback remains compatible;
- generic settings save and preset switching remain content-mutation free.

For `lstamduchn.vn`, the current legacy Hero path must not be silently replaced.

### 9.1 Hero migration flow

When the user explicitly chooses to upgrade the Law 01 Hero:

1. resolve the already-authoritative legacy Hero source only;
2. create a WordPress-native synced Hero as `draft`;
3. preserve the legacy Hero unchanged;
4. allow preview/edit;
5. public resolution continues to legacy fallback while the new Hero is not published;
6. once published and explicitly selected, the new Hero becomes the Law 01 Hero source.

No automatic migration occurs during Theme update, settings save or preset switch.

## 10. Storage model

Do not create a second Theme Mod or parallel content store.

Preset-scoped mappings live inside the existing normalized Theme settings family as references/presentation state only.

Conceptual shape:

```text
homepage_schema_version = 2

homepage.presets.law-01.hero.source_type = wp_block
homepage.presets.law-01.hero.source_id   = 412
homepage.presets.law-01.hero.variant     = split
homepage.presets.law-01.about.source_type = page
homepage.presets.law-01.about.source_id   = 91

homepage.presets.curtain-01.about.source_type = page
homepage.presets.curtain-01.about.source_id   = 310
```

The final physical representation must follow the existing normalization/import/export conventions and may be flattened if that is safer for backward compatibility.

The design explicitly forbids storing copied editorial fields such as:

- heading text;
- paragraph text;
- image URLs;
- service descriptions;
- FAQ answers;
- person biographies.

## 11. Compatibility resolver

The new resolver must support non-destructive precedence:

```text
1. preset-scoped mapping
2. compatible legacy generic mapping
3. valid fail-soft fallback
```

Example:

```text
law-01.about
  -> new law-01-specific reference when present
  -> legacy homepage_about_page while migration is incomplete
  -> unavailable/fail-soft when neither is valid
```

No title/slug/URL heuristic repair is permitted.

Legacy generic keys remain readable during the compatibility period and are not deleted in the same implementation slice.

## 12. Explicit migration

Migration is an explicit operation, not a side effect of ordinary settings save.

For the first Law 01 mapping migration on an existing site:

- read the existing normalized generic typed references;
- populate the new Law 01 preset-scoped references with the same IDs;
- do not duplicate WordPress content;
- do not change publication state;
- do not alter Curtain 01 mappings;
- keep legacy references intact for rollback.

The first migration step therefore copies **references**, not content.

It must be idempotent: running the operation again must not create duplicate content or progressively rewrite mappings.

## 13. Shared-source detection and source separation

Preset isolation of Theme mappings does not by itself isolate content when two presets intentionally or historically point to the same WordPress object.

The Control Center must detect this condition.

Example:

```text
law-01.about     -> Page 91
curtain-01.about -> Page 91
```

The UI must warn that editing the shared source affects both presets.

It then provides two explicit outcomes:

1. **Giữ dùng chung** — keep one shared WordPress source;
2. **Tạo nguồn riêng cho mẫu này** — duplicate the WordPress-native source as a draft and remap only the selected preset.

Automatic duplication on save or preset switch is forbidden.

### 13.1 Duplicate-source rule

When explicit separation is requested:

- duplicate only the mapped WordPress-native object;
- create the duplicate as draft unless a separate approved workflow says otherwise;
- keep the original object unchanged;
- remap only the requesting preset;
- preserve WordPress ownership;
- do not duplicate external provider/domain data.

## 14. Control Center UX

The Homepage Control Center should be reoriented from a technical mapping form into a preset-aware authoring console.

Top-level context:

```text
Mẫu đang chỉnh: Luật 01
```

Each section card shows:

- label;
- source type;
- source title/name;
- diagnostic status;
- shared-source warning where applicable;
- edit affordances;
- source replacement affordance where allowed.

Technical source IDs and low-level diagnostics may remain available in an Advanced/Diagnostics area but should not be required for normal authoring.

The Control Center remains bounded. It does not become a drag/drop canvas.

## 15. Diagnostics and fail-soft states

At minimum, source resolution must distinguish:

- `READY`;
- `EMPTY`;
- `DRAFT` when the source type supports draft authoring;
- `UNMAPPED`;
- `INVALID`;
- `LEGACY` / compatibility source where useful;
- `PROVIDER_UNAVAILABLE` for optional provider-enhanced presentation.

Missing or invalid mapped sources must fail soft.

Diagnostics may explain the problem and link to the correct authoring action, but must not silently repair mappings using heuristics.

## 16. Backward compatibility and rollback

The migration must preserve old mappings during the compatibility period.

Rollback requirements:

- old generic references remain available;
- disabling the new preset-scoped resolution path can restore legacy resolution;
- WordPress content created or edited through the authoring bridge remains valid native content;
- draft synced Hero content remains intact even if the Theme rolls back;
- preset switching never destroys content;
- no deprecation/removal of legacy keys occurs in the first implementation slice.

Legacy retirement is a later decision requiring proof that:

- Law 01 passes;
- Curtain 01 passes;
- preset-switch regression passes;
- no production path still depends on legacy generic keys;
- rollback has been verified.

## 17. Hard-coded editorial-copy remediation

Before Law 01 can claim authoring completeness, implementation must inventory every visible non-structural string emitted by Law 01 renderers.

Each string must be classified as one of:

1. WordPress-native editorial content;
2. Theme-owned UI microcopy;
3. external/provider-owned content;
4. defect: hard-coded editorial copy with no valid authoring source.

Category 4 must be eliminated before Law 01 Homepage authoring completion can pass.

A static regression should guard against reintroducing known hard-coded editorial headings into Law 01 template parts.

## 18. Testing strategy

Implementation must follow RED -> minimal GREEN -> regression.

### 18.1 L0 Source/state

- reconcile canonical main and source versions before code;
- record the accepted architecture decision in the authoritative source documents before production implementation;
- preserve D-030 and current ownership rules.

### 18.2 L1 Static

- preset renderers do not direct-read generic storage keys;
- no new content-copy fields appear in Theme settings;
- no prohibited slug/title/URL heuristic repair;
- no direct private provider storage access;
- hard-coded editorial-copy inventory is clean for Law 01 target scope.

### 18.3 L2 Contract/TDD

Required regressions include:

```text
legacy Law 01 -> same public source resolution before migration

migrate Law 01 refs
-> new preset refs match existing legacy refs
-> WordPress content unchanged

edit/map law-01 slot
-> curtain-01 mapping unchanged

switch law-01 -> curtain-01 -> law-01
-> both mapping sets retained

shared source
-> warning detected

explicit duplicate
-> draft native source created
-> only requesting preset remapped

Hero migration
-> draft wp_block created
-> public frontend unchanged before publish

invalid/deleted source
-> fail-soft
-> no heuristic repair
```

### 18.4 L3 Runtime

On a real WordPress runtime:

- normalized settings migration;
- Quick Edit capability/nonce behavior;
- Page/Media/wp_block mutations hit only the mapped WordPress object;
- draft/publish behavior;
- switch/update continuity;
- legacy rollback path.

### 18.5 L4 Browser/admin/a11y

Verify:

- preset-aware Homepage Control Center;
- section cards in usable order;
- Quick Edit and full editor actions;
- source sharing warning;
- responsive admin layout;
- keyboard/focus behavior;
- frontend visual regression for Law 01 and Curtain 01;
- no content mutation caused by changing presentation preset.

### 18.6 L5/L6

Optional provider integration and release/deployment remain separate gates. A Theme-owned authoring PASS does not imply provider integration certification, GitHub release publication or production deployment.

## 19. Source-document impact

Because this design changes Homepage architecture and decision governance, implementation may not begin until the written spec is approved and the implementation plan schedules source updates before production code.

Expected authoritative source changes:

- `AZT-02-architecture.md` — add preset-isolated Homepage Authoring System, authoring-bridge and compatibility rules;
- `AZT-04-roadmap-qa-decisions.md` — record a new accepted decision after D-037 and define the bounded implementation/QA gate;
- `AZT-03-baseline-provenance.md` — update only after implementation/evidence creates a new verified checkpoint;
- `SOURCE_MANIFEST.md` and `AZT-EXEC-MAP.md` — update at the corresponding source/evidence checkpoint.

The new decision must not rewrite historical D-024/D-030 evidence. It supersedes only the current generic cross-preset mapping behavior for new implementation.

## 20. Non-goals

This work does not introduce:

- a proprietary page builder;
- arbitrary drag/drop sections;
- a generic query builder;
- Theme-owned service/person/FAQ CPTs;
- a second Homepage content database;
- AI content generation;
- RootProfile identity inference;
- ConvertFlow Journey editing;
- WooCommerce product/business-state editing;
- automatic content cloning on preset switch;
- destructive legacy cleanup in the first migration slice.

## 21. Success criteria

The architecture is complete when all of the following are true:

1. Law 01 and Curtain 01 have independent preset-scoped mappings by default.
2. Changing a Law 01 mapping cannot mutate Curtain 01 mapping state.
3. Every Law 01 editorial string/media visible in the target Homepage scope has a valid WordPress-native authoring source.
4. Generic Theme UI microcopy remains translatable and does not need per-site storage.
5. Control Center supports bounded Quick Edit plus full native editing without copying content into Theme settings.
6. Shared WordPress sources are detected and made explicit.
7. Legacy existing sites render safely before explicit migration.
8. Law 01 reference migration is non-destructive and reversible.
9. D-030 Hero migration remains explicit, draft-first and rollback-safe.
10. Theme switch/preset switch does not delete WordPress content or domain data.
11. Law 01 and Curtain 01 frontend presentation remain regression-protected.
12. No public/provider ownership boundary is widened by this work.

## 22. Implementation sequencing boundary

After written-spec approval, the next step is a dedicated implementation plan.

The implementation plan must start with authoritative source updates and then decompose work into bounded RED -> GREEN slices. A safe expected order is:

1. source decision + exact current-state reconciliation;
2. preset registry + preset-scoped resolver;
3. legacy compatibility and explicit Law 01 reference migration;
4. preset-isolation tests;
5. Law 01 authoring inventory and hard-coded editorial remediation;
6. Quick Edit authoring bridge;
7. shared-source warning + explicit draft duplication;
8. D-030 Hero migration bridge;
9. Curtain 01 adoption without visual/content regression;
10. L3/L4 verification, evidence and release/deployment gates.

No production implementation is authorized by this design document alone.

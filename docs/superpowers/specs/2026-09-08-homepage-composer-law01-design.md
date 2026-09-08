# AZnet Theme — Homepage Composer + Law 01 Design

**Date:** 2026-09-08  
**Status:** Design approved in chat; written-spec review required before implementation  
**Canonical base:** `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`  
**Product:** AZnet Theme  
**Scope:** Homepage Composer Core, Law 01 presentation preset, and bounded Law Site Provisioning design boundary

## 1. Problem

AZnet Theme already has a valid WordPress-native Homepage shell, and PR #57 established that native WordPress Posts and Pages, including the configured static Front Page, use the Classic Editor. The previous full-page Block Pattern workflow is therefore no longer the correct product direction for Homepage setup.

The product need is broader than a single static Homepage: an AZnet website must be able to select a visual Homepage template and have the existing WordPress content automatically composed according to that template, while preserving the same content when the user changes templates later.

The target pilot is a legal-services website with no product catalogue. The website is expected to have a stable set of WordPress Pages and Categories, and the Homepage should automatically adapt to the content that actually exists.

## 2. Decision

Implement a **Homepage Composer** with three explicit concepts:

1. **Content Map** — typed references that say which existing WordPress objects feed semantic Homepage slots.
2. **Presentation Preset** — Theme-owned visual/compositional rules that decide how those slots are rendered.
3. **Provisioning** — a separate, explicit setup operation that may create a starter WordPress structure once; changing presentation presets never creates, deletes, duplicates or rewrites content.

The first preset is **Law 01**.

The core invariant is:

> Content Map is stable across template changes. Presentation Preset may change freely without changing WordPress content ownership or domain truth.

## 3. Ownership boundary

### WordPress owns

- Post/Page content and identity;
- Page hierarchy;
- Categories and taxonomy membership;
- publication state;
- media and featured images;
- native content/query semantics;
- static Front Page selection.

### AZnet Theme owns

- Homepage composition;
- semantic slot vocabulary for presentation purposes;
- typed references to WordPress sources;
- presentation preset selection;
- layout, card/grid rules, typography, spacing, color and responsive behavior;
- request-local presentation deduplication;
- Control Center configuration and diagnostics for Theme-owned references/presentation state.

### RootProfile owns

- Person/Organization identity;
- profile semantics;
- claims/evidence/relationships/responsibility/readiness;
- public-safe provider projections.

Law 01 v1 does **not** infer an organization team collection from RootProfile because no accepted public team-collection contract has been established for this use case.

### ConvertFlow owns

- Journey structure;
- resolver/state/validation;
- conversion lifecycle;
- analytics/conversion semantics.

The Homepage Composer must not copy or reconstruct ConvertFlow Journey semantics.

### Forbidden

- direct read/write of another product's option/meta/CPT/table/private class;
- title/slug/URL/Page-ID heuristics to infer semantic ownership;
- parallel authoritative content stores;
- custom Theme CPTs for legal services, people, FAQ or Homepage sections;
- auto-writing business facts into Theme settings;
- a proprietary page-builder schema;
- changing Page/Post/Category data when applying a visual preset.

## 4. Classic Editor policy

PR #57 remains authoritative for the current admin editing experience:

- WordPress `post` uses Classic Editor;
- WordPress `page` uses Classic Editor, including the static Front Page;
- other post types preserve their incoming WordPress/provider editor decision.

Homepage Composer must not re-enable Block Editor for the Front Page and must not require users to author the Homepage layout manually.

The Front Page remains a WordPress Page and may supply Hero content, but the overall Homepage layout is composed by the Theme from mapped sources.

## 5. Content Map v1

Content Map v1 uses fixed semantic slots with fixed allowed source types. It is not a generic query builder.

| Slot | Source type | Runtime use |
| --- | --- | --- |
| `hero` | configured static Front Page | title, excerpt, featured image, permalink/context |
| `services` | one published parent Page | parent summary plus direct published child Pages |
| `about` | one published Page | title, excerpt/content summary, featured image, permalink |
| `team` | one published Page | editorial team teaser, image and CTA only |
| `knowledge_topics` | selected Category IDs | topic navigation and optional counts/latest preview |
| `knowledge_latest` | same selected Category IDs | latest published Posts in mapped categories |
| `case_analysis` | one Category ID | latest published analysis Posts |
| `legal_news` | one Category ID | compact latest-news list |
| `process` | one published Page | authored process summary/content |
| `faq` | one published Page | authored FAQ content presented by the Theme |
| `contact` | one published Page plus accepted public provider capability when available | contact CTA/link and public-safe contact projection |

### 5.1 Reference rules

A stored reference is valid only when runtime validation confirms:

- the object exists;
- the object type matches the slot contract;
- the taxonomy matches when the reference is a term;
- the object is public/published when the surface requires public output.

Invalid references do not trigger heuristic repair. They produce a diagnostic state and the affected Homepage section fails soft.

### 5.2 No copied content

The Content Map stores identifiers/references only. It must not persist copies of:

- Page/Post titles;
- excerpts or body content;
- addresses, phone numbers or email addresses;
- people/profile facts;
- legal claims;
- article lists;
- query results.

## 6. Theme settings schema

Do not create a second Theme settings store.

Extend the existing `aznet_theme_settings` Theme Mod schema from schema version 1 to schema version 2 so it can hold Theme-owned presentation/configuration/reference state.

The normalized conceptual shape is:

```text
schema_version = 2
visual_preset = ...
header_preset = ...
homepage_preset = law-01
homepage_references = {
  services_page,
  about_page,
  team_page,
  knowledge_terms[],
  case_analysis_term,
  legal_news_term,
  process_page,
  faq_page,
  contact_page
}
```

The exact PHP representation may remain a flat normalized array if that better matches current implementation style, but the schema must preserve the distinction between presentation choice and typed source references.

Import/export and reset behavior must continue through the same normalized schema.

## 7. Query policy

Homepage Composer v1 supports bounded query policies only.

### `knowledge_latest`

- `post_status=publish`;
- post type `post`;
- Category membership restricted to mapped `knowledge_terms`;
- order by WordPress publication date descending;
- limit determined by the active presentation preset.

### `case_analysis`

- published Posts in the mapped `case_analysis_term`;
- newest first;
- bounded result count determined by preset.

### `legal_news`

- published Posts in the mapped `legal_news_term`;
- newest first;
- bounded compact list count determined by preset.

The Theme must not implement a recommendation/ranking engine, full query builder, semantic search or editorial scoring system in this slice.

## 8. Request-local display ledger

Homepage Composer may keep an in-memory/request-local ledger of Post IDs already rendered on the current Homepage request.

Purpose: avoid showing the same Post repeatedly across `knowledge_latest`, `case_analysis` and `legal_news` when taxonomy overlaps.

Rules:

- no persistence;
- no option/transient/database write;
- no mutation of WordPress queries/content;
- later sections may skip already displayed IDs and pull the next eligible item;
- deduplication is presentation behavior, not authoritative editorial state.

## 9. Law 01 presentation preset

Law 01 targets a combined professional legal-services + legal-knowledge website. Professional services and trust are the primary axis; editorial content supports authority and SEO.

### 9.1 Visual language

- deep navy as the primary dark surface;
- restrained warm gold/brass accent;
- serif display heading paired with highly readable sans-serif body text;
- generous white space;
- professional photography preferred over generic legal stock symbols;
- no fabricated metrics, ratings, testimonials or certifications;
- responsive, keyboard-visible focus and reduced-motion-safe behavior.

### 9.2 Section order

1. Header
2. Hero
3. Legal services
4. About
5. Team teaser
6. Legal knowledge topics
7. Latest legal knowledge
8. Case analysis / case law & practice
9. Legal news
10. Consultation process
11. FAQ
12. Final CTA
13. Footer

### 9.3 Hero

Source: static Front Page.

Presentation:

- eyebrow;
- one H1 from Page title;
- supporting copy from Page excerpt when available;
- featured image when available;
- primary CTA to the mapped contact destination;
- phone CTA only when an accepted public-safe contact provider supplies a phone value.

Fallbacks:

- no featured image => text-centric full-width hero;
- no excerpt => no empty placeholder;
- no valid contact destination => no false booking CTA;
- no phone capability => no phone button.

### 9.4 Legal services

Source: mapped parent Page + its direct published child Pages.

The preset adapts to actual item count:

- 1 => one prominent card;
- 2 => two columns;
- 3 => three columns;
- 4 => four columns or balanced 2x2 depending on width;
- 5 => balanced 3+2 treatment;
- 6 => 3x2;
- 7–8 => 4x2;
- more than preset limit => bounded list plus link to the parent services Page.

Each child Page card uses native title, permalink and safe excerpt/summary presentation. No service is invented from a title heuristic.

### 9.5 About

Source: mapped Page.

Two-column editorial layout when image exists; text-centric layout when it does not. Optional credibility facts may render only when supplied by a valid source; Law 01 ships no default client counts, success rates or years-of-experience claims.

### 9.6 Team teaser

Source: mapped Team Page.

Law 01 v1 renders an editorial teaser instead of synthetic person cards. It may show Page title, excerpt, featured image and CTA.

A future RootProfile collection enhancement is a separate integration change and requires an accepted public/versioned contract.

### 9.7 Knowledge topics

Source: selected Category references.

Render topic cards/links from actual Category names and permalinks. Layout adapts to mapped count. Theme does not decide that one legal domain is semantically more important than another unless explicit presentation ordering is stored as Theme-owned reference order.

### 9.8 Latest knowledge

Preferred desktop layout: one lead article plus four compact articles when data permits. Fewer Posts collapse naturally into a smaller grid. No carousel is required.

Wording must follow selection policy: date-sorted output may be labelled “Mới cập nhật”; manually curated output, if introduced in a future design, must not be labelled “Mới nhất”.

### 9.9 Case analysis / case law & practice

Editorially distinct section. It must use the exact mapped category meaning and must not relabel arbitrary analysis content as “Án lệ” unless the authoritative WordPress source actually uses that semantic/category.

### 9.10 Legal news

Compact section placed after expertise/analysis content so the site remains a professional legal-services site rather than visually becoming a news portal.

### 9.11 Process

Source: mapped Process Page. Theme renders the authored process content in a timeline/step presentation when structurally suitable. Starter provisioning copy is editable WordPress content, not permanent Theme business workflow semantics.

### 9.12 FAQ

Source: mapped FAQ Page. Theme may enhance presentation as an accessible disclosure/accordion only when the source structure can be safely rendered. FAQ answers remain WordPress-authored content; Theme creates no FAQ datastore and emits no fabricated FAQ schema.

### 9.13 Final CTA

Uses mapped Contact Page and accepted public contact capabilities. “Đặt lịch tư vấn” is shown only when an actual booking/contact destination supports that promise; otherwise use a truthful generic contact CTA.

## 10. Control Center — Homepage tab

Add a new `Trang chủ` tab to the existing AZnet Theme Control Center.

### 10.1 Template selector

Show Homepage preset cards with preview, description and active state.

For Law 01:

- name: `Luật 01`;
- description: professional legal-services site with strong legal editorial content;
- applying the preset changes only `homepage_preset` presentation state.

Applying a preset must not:

- create or delete Pages;
- create or delete Categories;
- rewrite Page/Post content;
- alter taxonomy membership;
- change RootProfile/ConvertFlow/Woo state.

### 10.2 Content source mapping

Render type-safe selectors:

- Page selectors for Page-backed slots;
- Category selectors for taxonomy-backed slots;
- multi-select for `knowledge_terms`;
- no free-form slug, URL or manual numeric ID input.

### 10.3 Diagnostics

Each slot receives one of these read-only statuses:

- `READY` — valid source and enough data to render;
- `EMPTY` — valid source exists but contains no renderable child/content items for the section;
- `UNMAPPED` — no reference configured;
- `INVALID` — reference exists but no longer satisfies type/public validity;
- `PROVIDER_UNAVAILABLE` — optional provider-enhanced presentation is unavailable.

Control Center reports status but does not auto-repair by heuristics.

## 11. Runtime architecture

Recommended responsibilities:

- `inc/theme/homepage-settings.php` or equivalent focused module: normalize Homepage preset/reference settings;
- `inc/theme/homepage-content-map.php`: validate typed WordPress references and resolve bounded read models;
- `inc/theme/homepage-composer.php`: orchestrate section order and fail-soft section rendering;
- `template-parts/homepage/`: focused section renderers;
- `assets/css/components/homepage-law-01.css`: Law 01 scoped presentation;
- `inc/admin/control-center.php` or a focused extracted Homepage admin module: source mapping UI and diagnostics.

Existing project patterns should be followed; do not introduce a framework, builder engine or bundler.

`front-page.php` remains the Theme-owned Front Page entry point, but the implementation must preserve a safe WordPress-native fallback when no valid Homepage preset/map can render.

## 12. Fail-soft behavior

Homepage Composer must never fatal because a mapped object or provider disappears.

Examples:

- `services_page` deleted => Services section hidden; diagnostic `INVALID`;
- Services parent exists with zero published direct children => section may render parent introduction only when useful, otherwise `EMPTY` and hidden according to preset rule;
- one knowledge Category deleted => remaining valid Categories still render; deleted reference is reported;
- no valid content in a section => no empty cards/placeholders on the public Homepage;
- optional RootProfile/contact provider absent => WordPress-native Page/link fallback where truthful, otherwise component hidden;
- malformed settings => normalized safe defaults, no arbitrary object lookup.

## 13. Provisioning boundary

Law Site Provisioning is a separate sub-project from Homepage preset selection.

Provisioning may eventually create a starter WordPress-native structure such as:

### Pages

- Giới thiệu
- Dịch vụ pháp lý
  - Luật Doanh nghiệp
  - Dân sự & Tranh chấp
  - Hình sự
  - Đất đai & Bất động sản
  - Hôn nhân & Gia đình
  - Lao động
- Đội ngũ luật sư
- Quy trình tư vấn
- Câu hỏi thường gặp
- Liên hệ

### Categories

- Kiến thức pháp luật
  - Doanh nghiệp
  - Dân sự
  - Hình sự
  - Đất đai
  - Hôn nhân & Gia đình
  - Lao động
- Tin pháp luật
- Phân tích vụ việc / Án lệ & thực tiễn

Provisioning requirements:

- explicit user action;
- idempotent behavior;
- no overwrite of existing user content;
- no duplicate creation when an earlier provisioning record/reference proves the same starter object was already created;
- no title/slug heuristic takeover of unrelated existing objects;
- starter copy must be clearly editable and must not assert unverified business/legal facts;
- changing Homepage preset never invokes provisioning.

Provisioning implementation is not required to complete Homepage Composer Core + Law 01 if doing so would delay a safe first downloadable Theme package. It can be a follow-on bounded slice after the Composer and Law 01 are working with manually mapped existing WordPress objects.

## 14. Migration from the previous Homepage Pattern direction

The previous `Homepage — Professional Services` pattern workflow is superseded for Homepage setup by this design because the current admin policy uses Classic Editor for native Pages.

Before removing any existing pattern code:

- identify whether another surface/test still depends on it;
- keep historical evidence unchanged;
- retire only after the Composer destination path and retained fallback/regression pass;
- do not delete portable WordPress content already inserted by users.

Removal/retirement is a separate cleanup gate; it is not required for first Composer GREEN if retention causes no runtime conflict.

## 15. Accessibility, responsive and performance requirements

Law 01 must pass at minimum:

- one coherent page-level H1;
- heading hierarchy without skipped structural misuse;
- keyboard-reachable links/buttons;
- visible focus state;
- no horizontal overflow at 1440, 1024, 390 and 320 px;
- long Vietnamese titles/diacritics wrap safely;
- images stay inside viewport and use appropriate WordPress image APIs/output;
- no duplicate IDs;
- axe critical/serious = 0 in tested scope;
- no Theme-caused console errors;
- surface-aware CSS loading: Law 01 Homepage CSS only when the preset is active on the Front Page;
- no global ecosystem bundle;
- no new build stack unless separately approved.

## 16. TDD and QA strategy

Implementation follows RED -> minimal GREEN -> regression.

### L0 — Source/state

- confirm canonical `main` and PR #57 Classic Editor policy;
- update authoritative architecture/roadmap source for the new Homepage Composer decision before production implementation proceeds beyond the design gate.

### L1 — Static

- PHP lint;
- naming/prefix checks;
- forbidden private storage scans;
- no new proprietary CPT/store;
- no slug/title/URL heuristic mapper;
- no global Law 01 asset loading.

### L2 — Contract/TDD

Required contract coverage:

- schema v1 -> v2 normalization/migration;
- allow-listed `homepage_preset` values;
- typed Page/Category reference normalization;
- invalid/missing/wrong-type reference fail-soft;
- Services direct-child resolution;
- bounded post queries;
- display-ledger deduplication;
- Control Center type-safe mapping form and diagnostics;
- applying preset does not mutate WordPress content;
- Law 01 required section order and fallback behavior;
- retained Classic Editor policy contract.

### L3 — WordPress runtime

On WordPress 6.9+ / PHP 8.1+:

- activate Theme;
- configure a static Front Page;
- create representative Pages/Categories/Posts through test fixtures;
- save Content Map references through Theme settings path;
- render Law 01 Homepage;
- test mapped/unmapped/deleted source states;
- verify no PHP fatal/warning/parse/uncaught Theme markers.

### L4 — Browser/visual/a11y

Test 1440/1024/390/320:

- section order;
- adaptive service grids;
- long Vietnamese content;
- empty/fewer/more item states;
- keyboard/focus;
- axe critical/serious = 0;
- overflow and duplicate IDs;
- no console errors.

### L5 — Integration

Relevant checks only:

- RootProfile absent/present where current accepted public contracts are used;
- optional contact provider absent => truthful fallback/hide behavior;
- ConvertFlow coexistence must not transfer Journey semantics into Theme;
- theme switch must leave WordPress content intact.

### L6 — Completion/release

- full retained regression;
- deterministic package build;
- package SHA-256;
- unzip/reverify exact package bytes;
- fresh WordPress activation smoke;
- source/provenance update;
- rollback reference;
- owner-approved merge/release only.

## 17. Delivery decomposition

The work is split into three sub-projects so each can be independently reviewed and tested.

### Sub-project A — Homepage Composer Core

Deliver:

- schema v2 settings/reference model;
- Content Map resolver;
- bounded query/read models;
- request-local ledger;
- fail-soft composer;
- Control Center Homepage tab + diagnostics;
- source/architecture updates.

### Sub-project B — Law 01 Presentation Preset

Deliver:

- Law 01 section renderers/composition;
- scoped CSS;
- adaptive content states;
- browser/a11y/runtime evidence;
- package-ready visual implementation.

### Sub-project C — Law Site Provisioning

Deliver later unless it is proven necessary for the first downloadable package:

- explicit starter setup operation;
- idempotent Page/Category creation;
- no-overwrite behavior;
- automatic initial Content Map wiring using IDs returned from objects created by that exact provisioning operation.

## 18. First downloadable package acceptance

The first downloadable/updateable Theme package is acceptable when Sub-project A + B reach L6 and the Theme can:

1. be installed/updated normally;
2. keep Classic Editor for native Posts/Pages;
3. allow Law 01 selection in Control Center;
4. allow type-safe mapping of existing WordPress Pages/Categories;
5. render the complete Law 01 Homepage automatically from mapped real content;
6. fail soft for missing/empty sources;
7. preserve all WordPress content when changing or disabling the Homepage preset;
8. pass retained runtime/browser/a11y/integration regressions;
9. produce a verified ZIP + SHA + rollback reference.

Provisioning is desirable but is not allowed to block the first safe downloadable package if Composer + Law 01 can already be configured from existing WordPress content.

## 19. Non-goals for the first package

- no generic drag-and-drop page builder;
- no AI content generation;
- no arbitrary query builder;
- no RootProfile team collection without a new owner contract;
- no testimonial/review engine;
- no legal service CPT;
- no FAQ CPT/store;
- no auto-generated legal claims;
- no WooCommerce/Product work;
- no ConvertFlow Journey implementation inside Theme;
- no automatic merge/deploy without owner approval.

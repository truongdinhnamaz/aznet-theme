# AZnet Theme — Homepage Composer + Law 01 Design

**Date:** 2026-09-08  
**Status:** Design approved in chat; written-spec review required before implementation  
**Canonical base:** `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`  
**Product:** AZnet Theme  
**Scope:** Homepage Composer Core, Law 01 presentation preset, and bounded Law Site Provisioning design boundary

## 1. Problem

AZnet Theme already has a valid WordPress-native Homepage shell, and PR #57 established that native WordPress Posts and Pages, including the configured static Front Page, use the Classic Editor. The previous full-page Block Pattern workflow is therefore no longer the correct product direction for Homepage setup.

The product need is broader than a single static Homepage: an AZnet website must be able to select a visual Homepage template and have existing WordPress content automatically composed according to that template, while preserving the same content when the user changes templates later.

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

## 4. Classic Editor and existing Homepage body boundary

PR #57 remains authoritative for the current admin editing experience:

- WordPress `post` uses Classic Editor;
- WordPress `page` uses Classic Editor, including the static Front Page;
- other post types preserve their incoming WordPress/provider editor decision.

Homepage Composer must not re-enable Block Editor for the Front Page and must not require users to author the Homepage layout manually.

The Front Page remains a WordPress Page and supplies Hero metadata. The existing public WordPress `the_content` boundary must also remain available because current Homepage integration allows external owners such as ConvertFlow to use that public body boundary without Theme-private coupling.

When Law 01 is active:

1. the Theme resolves Hero metadata from the configured static Front Page;
2. the Theme executes the normal filtered Front Page body exactly once and renders non-empty output in a bounded `native_body` region immediately after Hero;
3. the Theme does not inspect, classify, reconstruct or mutate the filtered body output;
4. the remaining mapped Law 01 sections render after that body region;
5. when the filtered body is empty, no empty body wrapper is emitted.

This preserves the existing WordPress/ConvertFlow public body contract without making Homepage Composer responsible for Journey semantics.

If Law 01 is inactive or the Composer cannot produce a safe model, `front-page.php` keeps the existing WordPress-native loop/`the_content()` fallback.

## 5. Content Map v1

Content Map v1 uses fixed semantic slots with fixed allowed source types. It is not a generic query builder.

| Slot | Source type | Runtime use |
| --- | --- | --- |
| `hero` | configured static Front Page | title, excerpt, featured image, permalink/context |
| `native_body` | filtered static Front Page body | public WordPress `the_content` output rendered exactly once, uninterpreted |
| `services` | one published parent Page | parent summary plus direct published child Pages |
| `about` | one published Page | title, excerpt/content summary, featured image, permalink |
| `team` | one published Page | editorial team teaser, image and CTA only |
| `knowledge_topics` | selected Category IDs | topic navigation and optional counts/latest preview |
| `knowledge_latest` | same selected Category IDs | latest published Posts in mapped categories |
| `case_analysis` | one Category ID | latest published analysis Posts |
| `legal_news` | one Category ID | compact latest-news list |
| `process` | one published Page | authored Page content rendered inside a process presentation wrapper |
| `faq` | one published Page | authored Page content rendered inside an FAQ presentation wrapper |
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

## 6. Theme settings schema v2

Do not create a second Theme settings store.

Extend the existing `aznet_theme_settings` Theme Mod schema from schema version 1 to schema version 2. Use explicit flat keys to match the current normalized settings style.

Required new normalized keys:

```text
schema_version = 2
homepage_preset = off | law-01
homepage_services_page = int >= 0
homepage_about_page = int >= 0
homepage_team_page = int >= 0
homepage_knowledge_terms = int[]
homepage_case_analysis_term = int >= 0
homepage_legal_news_term = int >= 0
homepage_process_page = int >= 0
homepage_faq_page = int >= 0
homepage_contact_page = int >= 0
```

Rules:

- `0` means unmapped for single references;
- `[]` means unmapped for multi-reference terms;
- normalization casts only positive integer IDs into stored references;
- term arrays are unique, positive integer IDs in saved display order;
- object existence/type/publication is resolved at read time, not copied into settings;
- schema v1 settings migrate losslessly by retaining all existing presentation values and adding safe Homepage defaults;
- import/export/reset continue through the same normalized `aznet_theme_settings` schema.

The settings store contains presentation/configuration/reference state only. It is not a content snapshot.

## 7. Query policy

Homepage Composer v1 supports bounded query policies only.

### `knowledge_latest`

- `post_status=publish`;
- post type `post`;
- Category membership restricted to mapped `homepage_knowledge_terms`;
- order by WordPress publication date descending;
- result limit is defined by Law 01 presentation constants/configuration, not editable query logic.

### `case_analysis`

- published Posts in mapped `homepage_case_analysis_term`;
- newest first;
- bounded result count defined by Law 01.

### `legal_news`

- published Posts in mapped `homepage_legal_news_term`;
- newest first;
- bounded compact list count defined by Law 01.

The Theme must not implement a recommendation/ranking engine, full query builder, semantic search or editorial scoring system in this slice.

## 8. Request-local display ledger

Homepage Composer may keep an in-memory/request-local ledger of Post IDs already rendered on the current Homepage request.

Purpose: avoid showing the same Post repeatedly across `knowledge_latest`, `case_analysis` and `legal_news` when taxonomy overlaps.

Rules:

- no persistence;
- no option/transient/database write;
- no mutation of WordPress content/taxonomy;
- each later bounded query may exclude IDs already rendered by earlier Composer sections;
- deduplication is presentation behavior, not authoritative editorial state.

## 9. Law 01 presentation preset

Law 01 targets a combined professional legal-services + legal-knowledge website. Professional services and trust are the primary axis; editorial content supports authority and SEO.

### 9.1 Visual language

- deep navy primary dark surface;
- restrained warm gold/brass accent;
- serif display heading paired with a highly readable sans-serif body face already available to the Theme/system stack;
- generous white space;
- professional photography preferred over generic legal stock symbols;
- no fabricated metrics, ratings, testimonials or certifications;
- responsive, keyboard-visible focus and reduced-motion-safe behavior.

### 9.2 Section order

1. Header
2. Hero
3. Native Front Page body, only when filtered output is non-empty
4. Legal services
5. About
6. Team teaser
7. Legal knowledge topics
8. Latest legal knowledge
9. Case analysis / case law & practice
10. Legal news
11. Consultation process
12. FAQ
13. Final CTA
14. Footer

### 9.3 Hero

Source: static Front Page.

Presentation:

- editable preset eyebrow copy that is generic and non-factual;
- one H1 from Page title;
- supporting copy from Page excerpt when available;
- featured image when available;
- primary CTA to the mapped Contact Page;
- phone CTA only when an accepted public-safe contact provider supplies a phone value.

Fallbacks:

- no featured image => text-centric full-width hero;
- no excerpt => no empty placeholder;
- no valid Contact Page => no false contact/booking CTA;
- no phone capability => no phone button.

Law 01 must not label a CTA “Đặt lịch tư vấn” unless a real mapped destination/capability supports booking. With only a Contact Page, default CTA copy is the truthful generic “Liên hệ tư vấn”.

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
- more than the Law 01 Homepage limit => bounded list plus link to the parent services Page.

Each child Page card uses native title, permalink and an existing Page excerpt when present. If excerpt is empty, the card renders without summary rather than automatically slicing legal body copy into a potentially misleading summary.

### 9.5 About

Source: mapped Page.

Two-column editorial layout when a featured image exists; text-centric layout when it does not. Optional credibility facts may render only from a valid owner/provider source. Law 01 ships no default client counts, success rates or years-of-experience claims.

### 9.6 Team teaser

Source: mapped Team Page.

Law 01 v1 renders an editorial teaser instead of synthetic person cards. It may show Page title, excerpt, featured image and CTA.

A future RootProfile collection enhancement is a separate integration change and requires an accepted public/versioned contract.

### 9.7 Knowledge topics

Source: selected Category references.

Render topic cards/links from actual Category names and permalinks. Layout adapts to mapped count. Stored `homepage_knowledge_terms` order is presentation order; Theme does not infer semantic priority.

### 9.8 Latest knowledge

Preferred desktop layout: one lead article plus four compact articles when data permits. Fewer Posts collapse naturally into a smaller grid. No carousel is required.

Because v1 is date-sorted only, the section may use “Mới cập nhật”. A future curated mode must use wording that reflects its actual selection policy.

### 9.9 Case analysis / case law & practice

Editorially distinct section. It uses the exact mapped Category name as the public semantic label by default. The Theme must not relabel arbitrary analysis content as “Án lệ” unless the WordPress source itself carries that meaning.

### 9.10 Legal news

Compact section placed after expertise/analysis content so the site remains a professional legal-services site rather than visually becoming a news portal.

### 9.11 Process

Source: mapped Process Page.

V1 does **not** parse arbitrary Classic Editor headings/paragraphs into inferred business steps. It renders the authored Page content through the normal WordPress content filter inside a Law 01 process wrapper. If provisioning later creates a starter Process Page, it may seed an ordered-list/step-shaped WordPress body that Law 01 styles generically.

### 9.12 FAQ

Source: mapped FAQ Page.

V1 does **not** infer question/answer pairs from arbitrary Classic Editor content. It renders the authored Page content through the normal WordPress content filter inside a Law 01 FAQ wrapper. Native/authored `<details>` elements, when present in the Page content, receive accessible Law 01 styling without a JavaScript dependency. Provisioning may seed editable `<details><summary>…</summary>…</details>` content, but the Theme does not create a FAQ datastore or auto-generate FAQ schema.

### 9.13 Final CTA

Uses the mapped Contact Page and accepted public contact capabilities. With only a valid Contact Page, use “Liên hệ tư vấn”. Booking-specific copy requires a real booking destination/capability. No CTA renders with a fabricated URL.

## 10. Control Center — Homepage tab

Add a new `Trang chủ` tab to the existing AZnet Theme Control Center.

### 10.1 Template selector

Show Homepage preset cards with preview, description and active state.

For Law 01:

- setting value: `law-01`;
- display name: `Luật 01`;
- description: professional legal-services site with strong legal editorial content;
- applying the preset changes only `homepage_preset` presentation state.

The `off` state preserves the existing native Front Page rendering path.

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
- ordered multi-select for `homepage_knowledge_terms`;
- no free-form slug, URL or manual numeric ID input.

Saving the Homepage form goes through the existing normalized Theme settings write path and preserves settings belonging to other Control Center sections.

### 10.3 Diagnostics

Each mapped slot receives one of these read-only statuses:

- `READY` — valid source and enough data to render;
- `EMPTY` — valid source exists but contains no renderable child/content items for the section;
- `UNMAPPED` — no reference configured;
- `INVALID` — reference exists but no longer satisfies type/public validity;
- `PROVIDER_UNAVAILABLE` — optional provider-enhanced presentation is unavailable.

Control Center reports status but does not auto-repair by heuristics.

## 11. Runtime architecture

Use focused modules with these responsibilities:

- `inc/theme/homepage-content-map.php` — validate typed WordPress references, resolve bounded source/read models and request-local article ledger;
- `inc/theme/homepage-composer.php` — orchestrate Law 01 section order, native-body preservation and fail-soft section rendering;
- `inc/theme/settings.php` — schema v2 normalization/defaults, keeping the existing single settings store;
- `inc/admin/homepage.php` — Homepage Control Center fields, typed selectors and diagnostics;
- `inc/admin/control-center.php` — register/rout the `homepage` tab and delegate rendering to the focused Homepage admin module;
- `template-parts/homepage/law-01/` — focused section renderers;
- `assets/css/components/homepage-law-01.css` — Law 01 scoped presentation;
- `front-page.php` — select Composer versus existing native fallback without domain/provider heuristics.

Do not introduce a framework, builder engine or bundler.

## 12. Fail-soft behavior

Homepage Composer must never fatal because a mapped object or provider disappears.

Examples:

- `homepage_services_page` deleted => Services hidden; diagnostic `INVALID`;
- Services parent exists with zero published direct children => no fake child cards; parent-only introduction may render if its own excerpt/title is useful, otherwise diagnostic `EMPTY`;
- one knowledge Category deleted => remaining valid Categories still render and the deleted reference is reported invalid;
- no valid content in a section => no empty public placeholders;
- optional RootProfile/contact provider absent => mapped WordPress Contact Page remains the truthful CTA fallback; provider-only phone UI disappears;
- malformed settings => normalized safe defaults and no arbitrary object lookup;
- Law 01 preset active but no valid static Front Page => Composer does not claim a valid Law 01 surface; existing native fallback remains available.

## 13. Provisioning boundary

Law Site Provisioning is a separate sub-project from Homepage preset selection.

Provisioning is allowed to create this starter WordPress-native structure only after an explicit user action:

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
- no title/slug heuristic takeover of unrelated existing objects;
- exact IDs returned by objects created in that provisioning run may be used to initialize the Content Map;
- starter copy must be clearly editable and must not assert unverified business/legal facts;
- changing Homepage preset never invokes provisioning.

Provisioning is **not required** for the first downloadable Composer + Law 01 package. The first package is complete when it safely maps and renders existing WordPress content. Provisioning is the next separate sub-project after that package unless the owner explicitly reprioritizes it.

## 14. Migration from the previous Homepage Pattern direction

The previous `Homepage — Professional Services` pattern workflow is superseded for Homepage setup by this design because the current admin policy uses Classic Editor for native Pages.

Before removing existing pattern code:

- identify whether another surface/test still depends on it;
- keep historical evidence unchanged;
- retire only after the Composer destination path and retained fallback/regression pass;
- do not delete portable WordPress content already inserted by users.

Pattern removal is a cleanup gate, not part of the minimum first Composer GREEN unless retained code conflicts with runtime behavior.

## 15. Accessibility, responsive and performance requirements

Law 01 must pass at minimum:

- one coherent page-level H1 from the Front Page title;
- coherent heading hierarchy in Theme-generated sections;
- keyboard-reachable links/buttons;
- visible focus state;
- no horizontal overflow at 1440, 1024, 390 and 320 px;
- long Vietnamese titles/diacritics wrap safely;
- images stay inside viewport and use appropriate WordPress image APIs/output;
- no duplicate IDs;
- axe critical/serious = 0 in tested Theme-owned scope;
- no Theme-caused console errors;
- surface-aware CSS loading: Law 01 Homepage CSS only when `is_front_page()` and `homepage_preset=law-01`;
- no global ecosystem bundle;
- no new build stack unless separately approved.

The uninterpreted `native_body` region is integration output and is tested for coexistence/containment; the Theme must not silently rewrite third-party semantics merely to make its own visual assertions pass.

## 16. TDD and QA strategy

Implementation follows RED -> minimal GREEN -> regression.

### L0 — Source/state

- confirm canonical `main` and PR #57 Classic Editor policy;
- update authoritative AZT-02 architecture and AZT-04 roadmap/decision source for Homepage Composer before production implementation proceeds beyond the design gate.

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
- `homepage_preset` allow-list `off|law-01`;
- typed Page/Category reference normalization;
- invalid/missing/wrong-type reference fail-soft;
- Services direct-child resolution;
- bounded post queries;
- request-local display-ledger deduplication;
- native `the_content` body executed/rendered exactly once when Composer is active;
- Control Center type-safe mapping form and diagnostics;
- applying preset does not mutate WordPress content;
- Process/FAQ do not infer semantics from arbitrary body structure;
- Law 01 required section order and fallback behavior;
- retained Classic Editor policy contract.

### L3 — WordPress runtime

On WordPress 6.9+ / PHP 8.1+:

- activate Theme;
- configure a static Front Page;
- create representative Pages/Categories/Posts through test fixtures;
- save Content Map references through the Theme settings path;
- render Law 01 Homepage;
- verify a non-empty native Front Page body appears exactly once;
- test mapped/unmapped/deleted source states;
- verify no PHP fatal/warning/parse/uncaught Theme markers.

### L4 — Browser/visual/a11y

Test 1440/1024/390/320:

- section order;
- adaptive service grids;
- long Vietnamese content;
- empty/fewer/more item states;
- keyboard/focus;
- axe critical/serious = 0 in Theme-owned scope;
- overflow and duplicate IDs;
- no Theme-caused console errors.

### L5 — Integration

Relevant checks only:

- RootProfile absent/present where current accepted public contracts are used;
- optional contact provider absent => truthful mapped-Page fallback/hide behavior;
- ConvertFlow/public `the_content` coexistence: output preserved exactly once and Theme does not reconstruct Journey semantics;
- theme switch leaves WordPress content intact.

Do not claim ConvertFlow lifecycle/analytics integration PASS unless exact provider evidence is available at that layer.

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

- AZT source decision updates;
- schema v2 settings/reference model;
- Content Map resolver;
- bounded query/read models;
- request-local ledger;
- native-body-preserving fail-soft composer;
- Control Center Homepage tab + diagnostics.

### Sub-project B — Law 01 Presentation Preset

Deliver:

- Law 01 section renderers/composition;
- scoped CSS;
- adaptive content states;
- runtime/browser/a11y evidence;
- package-ready visual implementation.

### Sub-project C — Law Site Provisioning

Follow-on after the first package:

- explicit starter setup operation;
- idempotent Page/Category creation;
- no-overwrite behavior;
- initial Content Map wiring only from IDs returned by that exact provisioning operation.

## 18. First downloadable package acceptance

The first downloadable/updateable Theme package is acceptable when Sub-project A + B reach L6 and the Theme can:

1. be installed/updated normally;
2. keep Classic Editor for native Posts/Pages;
3. allow Law 01 selection in Control Center;
4. allow type-safe mapping of existing WordPress Pages/Categories;
5. render the complete Law 01 Homepage automatically from mapped real content;
6. preserve non-empty public Front Page `the_content` output exactly once;
7. fail soft for missing/empty sources;
8. preserve all WordPress content when changing or disabling the Homepage preset;
9. pass retained runtime/browser/a11y/integration regressions at the evidence layers actually available;
10. produce a verified ZIP + SHA + rollback reference.

## 19. Non-goals for the first package

- no generic drag-and-drop page builder;
- no AI content generation;
- no arbitrary query builder;
- no RootProfile team collection without a new owner contract;
- no testimonial/review engine;
- no legal service CPT;
- no FAQ CPT/store;
- no automatic parsing of arbitrary Classic Editor content into FAQ/process domain semantics;
- no auto-generated legal claims;
- no WooCommerce/Product work;
- no ConvertFlow Journey implementation inside Theme;
- no Law Site Provisioning in the first package;
- no automatic merge/deploy without owner approval.

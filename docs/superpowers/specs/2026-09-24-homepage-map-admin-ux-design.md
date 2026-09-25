# AZnet Theme — Homepage Map Admin UX Design

**Date:** 2026-09-24  
**Status:** Written spec approved by product owner on 2026-09-24; implementation planning authorized  
**Product:** AZnet Theme  
**Scope:** Homepage Control Center authoring UX only  
**Implementation baseline:** production `lstamduchn.vn` AZnet Theme 1.3.38 behavior; canonical GitHub source is currently behind that production lineage and must be reconciled before production-code implementation.

## 1. Problem

The current Homepage Control Center is source-centric. It exposes mapped WordPress sources, technical states, quick-edit controls, and source switching as the primary mental model.

The live Law 01 frontend is surface-centric. The visitor sees a concrete reading order and concrete rendered sections:

1. Hero
2. Services
3. Profile surface containing About + Team
4. Latest Articles
5. Knowledge Topics
6. Final Contact CTA

This mismatch forces an administrator to mentally translate from source objects into visible Homepage sections. The result is technically correct but difficult to operate and easy to misunderstand.

The target experience is:

> The Homepage admin should read like a map of the Homepage that is actually being rendered now.

The map is not a page builder and is not a second content store. It is a Theme-owned authoring/presentation view over the same effective source/composition state already used by the frontend.

## 2. Decision

Replace the current source-first Homepage authoring console with a **Homepage Map**.

Homepage Map has three layers:

1. **Primary map — “Trang chủ đang hiển thị”**  
   Shows only effective sections currently rendered by the active Homepage composition, in frontend reading order.

2. **Section authoring controls**  
   Each map node exposes the smallest safe editing action for the WordPress-owned source that feeds that visible surface.

3. **Advanced source/configuration controls**  
   Source mapping, preset selection, shared-source warnings, migration diagnostics, and technical controls move behind an explicitly labeled advanced disclosure and are not primary actions.

The map must derive from the same effective composition/read model as the frontend. It must not maintain a second manually curated section list.

## 3. Ownership boundary

### WordPress owns

- Pages, Posts, Categories, post status and taxonomy membership;
- post/page titles, excerpts, bodies and media;
- synced `wp_block` Hero content;
- publication state;
- native edit lifecycle;
- query truth and permalinks.

### AZnet Theme owns

- Homepage section composition and reading order;
- presentation presets and variants;
- typed references to WordPress-owned sources;
- request-local effective Homepage read model;
- Homepage Map presentation in wp-admin;
- status wording and visual treatment;
- bounded quick-edit presentation that writes back through WordPress-owned objects;
- section anchor presentation on the frontend.

### External domain/provider owners retain their existing boundaries

- RootProfile identity/profile semantics stay RootProfile-owned.
- ConvertFlow journey/resolver/state/conversion stays ConvertFlow-owned.
- WooCommerce commerce state stays WooCommerce-owned.

Homepage Map must not read private provider storage or infer owner truth from slug/title/URL heuristics.

## 4. Core invariant

The same request-local **effective Homepage surface model** must drive both:

- frontend section composition; and
- Homepage Map section order/status/source summary.

Admin must not define a separate hard-coded list that can drift from frontend rendering.

A surface model is presentation/read state only. It is never persisted as a parallel content store.

## 5. Effective Homepage surface model

Introduce or extract one internal Theme-owned resolver whose output describes the effective presentation state for the active Homepage preset.

Conceptual shape:

```php
[
    [
        'key'         => 'hero',
        'label'       => 'Hero',
        'visible'     => true,
        'status'      => 'active',
        'source_type' => 'wp_block',
        'source_id'   => 123,
        'summary'     => [...],
        'anchor'      => 'aznet-homepage-hero',
        'children'    => [],
    ],
    ...
]
```

This is an internal read model, not a public integration contract.

Rules:

- resolve from existing Theme settings + WordPress public APIs;
- no copied persistent content;
- no new CPT/table/option store;
- no title/slug heuristics;
- fail soft when a source disappears;
- preserve current fallback rules;
- explicitly distinguish configured source from effective rendered source.

## 6. Law 01 current map

For the current Tâm Đức Law 01 surface, the top-level Homepage Map should match the live frontend composition:

### 6.1 Hero

Primary node: **Hero**

Map summary should show the effective Hero source actually rendering now, not merely the configured candidate.

Display:

- current visible H1/title;
- short supporting copy;
- Hero image thumbnail when present;
- CTA labels when available;
- status badge;
- primary action: **Sửa Hero**;
- secondary link: **Xem trên trang chủ**.

Hero source resolution must preserve the existing D-030 behavior:

- published synced Hero content is preferred when valid;
- draft Hero is “Đang soạn”, not “Đang dùng”;
- legacy/fallback Hero remains the effective source until the new Hero is actually publishable/rendered.

### 6.2 Services

Primary node: **Dịch vụ**

Display:

- source parent title;
- visible child count;
- exactly the child items currently selected by the frontend bounded query/order;
- child title and short summary when already available from WordPress;
- status badge;
- primary action: **Chỉnh nội dung**.

The map must not show children that the frontend does not currently render because of bounded limits, invalid publication state, or query rules.

### 6.3 About + Team composite

The live frontend currently renders About and Team inside one Profile surface. Homepage Map should reflect that visual grouping instead of pretending they are unrelated top-level sections.

Primary node: **Giới thiệu & Đội ngũ**

Subregions:

- **Giới thiệu** — visible title/lede/image/fact summary from the effective About source;
- **Đội ngũ** — visible Team heading, current illustration/teaser state and CTA source.

Each subregion may have its own bounded **Chỉnh nội dung** action, but they remain grouped in the map because that matches the frontend surface.

If a future Law 01 composition separates them into distinct frontend sections, the map should separate automatically through the shared composition model.

### 6.4 Latest Articles

Primary node: **Bài viết**

Display:

- current public section heading;
- current query rule in human wording, e.g. “3 bài mới nhất”;
- visible article titles currently selected;
- category/date metadata only when it is already part of the frontend presentation;
- status badge;
- action: **Quản lý bài viết** or bounded source control appropriate to the current owner.

Do not expose raw query parameters as the primary UX.

### 6.5 Knowledge Topics

Primary node: **Chủ đề**

Display:

- category/topic names currently rendered;
- visible count;
- status badge;
- action: **Chỉnh chủ đề**.

The ordered set must match the same source/order used by frontend rendering.

### 6.6 Final Contact CTA

Primary node: **Liên hệ**

Display:

- visible heading;
- visible contact summary;
- CTA label/destination summary;
- status badge;
- primary action: **Chỉnh nội dung**;
- **Xem trên trang chủ**.

Provider-enhanced contact fields remain optional and fail soft according to their existing contracts.

## 7. What does not appear in the primary map

A slot that is configured but not currently rendered must not appear as if it is live.

Examples:

- a draft Hero candidate while fallback Hero is public;
- legacy Law 01 slots no longer rendered by the current composition;
- empty/disabled process, FAQ, legal-news or case-analysis candidates;
- future/experimental sections not in the effective frontend reading order.

Such state belongs in **Nguồn & cài đặt nâng cao**, not the primary Homepage Map.

This removes the current mismatch where the admin slot list can contain entries that do not correspond 1:1 with the live Homepage.

## 8. Status model

Administrator-facing statuses are semantic presentation states, not raw technical enums.

Primary states:

- **✓ Đang dùng** — this is the effective source/surface currently rendered;
- **• Đang soạn** — a configured source exists but is not the effective public source yet;
- **! Chưa có nội dung** — source exists but cannot currently produce the intended visible content;
- **! Nguồn lỗi** — configured reference is invalid;
- **• Chưa chọn** — no source is configured where one is required;
- **Ẩn trên trang chủ** — valid/configured data exists but current composition does not render it.

Rules:

- “Đang dùng” is based on effective render state, not merely `post_status=publish`;
- no raw `READY`, `DRAFT`, `INVALID`, etc. are shown in the primary UI;
- status color is supplemental; text and icon carry meaning for accessibility.

## 9. Primary interaction design

### 9.1 Page header

Title: **Trang chủ**

Lead text: **Chỉnh các phần theo đúng thứ tự đang hiển thị trên website.**

Top utility actions:

- **Xem trang chủ**
- active preset summary, e.g. **Luật 01 · Burgundy + Gold**
- advanced disclosure toggle

Do not lead with “Mẫu trang chủ”, “mapping”, or migration language during normal steady-state use.

### 9.2 One-column visual map

Use one vertical column. Each top-level card corresponds to one effective frontend surface.

Each card contains:

- numbered/order indicator;
- section label;
- semantic status badge;
- compact preview summary;
- source summary in muted secondary text;
- primary edit action;
- **Xem trên trang chủ** deep link.

The visual density should be closer to an editorial outline than a settings dashboard.

### 9.3 In-place editing

Prefer bounded in-place edit disclosures where existing safe forms already exist.

Examples:

- Hero: existing simple Hero editor;
- Page-backed section: title, excerpt/short description, featured image;
- child service item: bounded quick edit;
- category-backed topics: category label/description only where existing Theme write path already supports it.

Do not create a generic arbitrary editor inside Theme.

### 9.4 Preserve location after save

After a save, redirect back to the exact map section anchor that was edited.

Example:

```text
...admin.php?page=aznet-theme&section=homepage#homepage-map-services
```

This removes the current “save then find the section again” friction.

## 10. Frontend section anchors

Add stable Theme-owned section wrapper IDs so “Xem trên trang chủ” can deep-link to the exact visible section.

Examples:

- `#aznet-homepage-hero`
- `#aznet-homepage-services`
- `#aznet-homepage-profile`
- `#aznet-homepage-articles`
- `#aznet-homepage-topics`
- `#aznet-homepage-contact`

These IDs are presentation anchors only. They must not become authoritative routing/domain identifiers.

## 11. Advanced disclosure

A single collapsed area: **Nguồn & cài đặt nâng cao**.

It contains:

- Homepage preset/variant selection;
- typed source mapping;
- source IDs only where technically useful;
- shared-source warnings;
- source-separation action;
- one-time mapping initialization/migration controls;
- diagnostic details;
- native WordPress edit links when truly needed.

Rules:

- advanced controls are not duplicated in primary cards;
- one-time migration panels disappear from the normal steady-state UI once complete;
- source switching is intentionally secondary to editing what is already visible.

## 12. No WYSIWYG clone

Homepage Map is not a pixel-perfect iframe editor and does not scrape the frontend DOM.

Reasons:

- DOM scraping would create an unstable reverse dependency;
- admin auth/frontend cache/provider differences would make state unreliable;
- the Theme already owns composition and can expose a stable internal read model.

The map should be visually recognizable, but semantic rather than pixel-identical.

## 13. Fail-soft behavior

If a source becomes invalid:

- frontend keeps its existing safe fallback/hide behavior;
- map shows the effective result, not the failed candidate as active;
- advanced diagnostics show the failed configured reference;
- no heuristic auto-repair;
- no silent source mutation.

If the active preset is unsupported or Homepage Composer is off:

- Homepage Map falls back to a clear native-state explanation;
- advanced settings remain available;
- no fake list of Theme-composed sections is shown.

## 14. Accessibility

Homepage Map must support:

- keyboard access to every disclosure and action;
- visible focus;
- semantic heading hierarchy;
- status text not dependent on color alone;
- minimum 44px practical touch/click targets for primary controls;
- no focus trap in edit disclosures;
- reduced-motion-safe transitions;
- WordPress admin responsive breakpoint behavior.

## 15. Implementation boundaries

Likely production paths after provenance reconciliation:

- `inc/theme/homepage-content-map.php` and/or existing Homepage composition module — extract effective surface model;
- `inc/theme/homepage-composer.php` / focused Law 01 composition helpers — frontend consumes the same model;
- `inc/admin/homepage.php` — render Homepage Map and advanced disclosure;
- scoped admin CSS/JS only if the existing Control Center asset architecture supports it;
- Law 01 section templates — add stable section anchors;
- tests for model parity, admin rendering, runtime and browser/a11y.

The exact file split is implementation-plan work, not locked by this design.

## 16. TDD and QA gates

Implementation must follow RED → GREEN.

### L0 — Source/state gate

Before production code:

1. reconcile canonical GitHub with the actual production 1.3.38 lineage;
2. prove exact baseline bytes/provenance for the files being modified;
3. do not implement on stale 1.3.32 and later “copy fixes forward” by assumption.

This is a hard gate.

### L1 — Static

Contracts must reject:

- raw technical status labels in the primary UI;
- a manually duplicated Law 01 admin section order;
- primary “Đổi nguồn” actions outside advanced settings;
- new parallel content storage;
- slug/title/URL ownership heuristics.

### L2 — Model/contract

Write failing tests proving:

- surface model order equals effective frontend order;
- only effective visible sections enter the primary map;
- Hero draft candidate does not become “Đang dùng” while fallback is effective;
- composite About + Team maps to the Profile surface;
- services items/count match the frontend bounded selection;
- hidden/invalid slots remain advanced diagnostics.

### L3 — WordPress runtime

On real WordPress:

- load Homepage admin as administrator;
- confirm mapped WordPress sources resolve correctly;
- save each bounded edit path;
- verify ownership remains WordPress/source-owned;
- verify post-save return anchor.

### L4 — Browser / visual / a11y

Verify at desktop and narrow admin widths:

- primary map order matches live frontend;
- status badges and summaries are understandable;
- disclosures work by keyboard;
- deep links reach the intended frontend section;
- no console errors;
- axe has no new serious/critical issues.

### L5 — Integration regression

No new provider contract is introduced. Retain existing RootProfile/ConvertFlow/Woo boundaries and run relevant integration regressions where the changed Homepage surface already participates.

### L6 — Release

Version promotion, package, deployment and production verification remain separate approval/release gates.

## 17. Acceptance criteria

Homepage Map is acceptable when all are true:

1. An administrator can read the primary admin page from top to bottom and see the same effective Homepage section order as the frontend.
2. Every primary card corresponds to a section currently rendered publicly.
3. No primary card claims “Đang dùng” for a non-effective configured candidate.
4. About + Team reflects the current composite Profile frontend surface.
5. Services preview shows the same bounded visible items/order as frontend.
6. Articles and Topics summaries reflect the same live query/source state as frontend.
7. Editing a visible section is the primary action; changing source is advanced.
8. Technical mapping/migration jargon is absent from the normal steady-state flow.
9. Saving returns the administrator to the edited map section.
10. “Xem trên trang chủ” deep-links to the matching visible section.
11. No new authoritative content store, provider-private read, heuristic ownership inference or public integration contract is created.
12. L0 provenance, L1-L4 verification and retained L5 regression gates are satisfied before release.

## 18. Non-goals

This design does not authorize:

- drag-and-drop section reordering;
- arbitrary section creation;
- a generic page builder;
- frontend inline editing;
- iframe-based visual editing;
- Theme-owned legal-service/business data;
- automatic copy generation;
- provider contract changes;
- RootProfile Team reconstruction;
- ConvertFlow Journey editing;
- WooCommerce domain editing;
- automatic migration/deletion of old WordPress sources.

Those require separate decisions.

## 19. Provenance blocker before implementation

As of this design checkpoint:

- production `lstamduchn.vn` has been verified running AZnet Theme **1.3.38**;
- canonical GitHub `main` currently declares **1.3.32**;
- therefore production has Theme changes not yet reconciled into canonical Git history.

The design document can be reviewed and accepted now because it changes no production code.

**Production implementation MUST NOT begin until the 1.3.38 source lineage is reconciled into the canonical repository with evidence.**

That reconciliation must preserve the already accepted production fixes from 1.3.35 → 1.3.36 → 1.3.37 → 1.3.38 rather than recreating them heuristically from stale main.

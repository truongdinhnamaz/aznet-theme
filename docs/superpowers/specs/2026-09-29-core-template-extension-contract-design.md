# AZnet Theme Core Template Extension Contract — Design

**Date:** 2026-09-29  
**Status:** Proposed for owner review  
**Scope:** AZnet Theme Core only  
**Canonical repo:** `truongdinhnamaz/aznet-theme`

## 1. Intent

AZnet Theme Core must become the stable presentation root on which future reusable templates can be attached without repeatedly modifying generic Core logic for each template.

Success is not merely that Law 01, Rèm 01 and Industrial 01 work. The Core is successful when a fourth template can register, render, author, load assets and connect to provisioning through a public Theme-side contract without adding template-name conditionals to generic Core code.

The governing ownership rule remains:

> SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.

## 2. Existing Problem

The current implementation still contains template-name allow-lists and template-specific branches in generic settings normalization, visual preset selection, Homepage administration, Homepage composition and provisioning selection.

That structure scales poorly. Each new template risks edits across multiple Core files, higher regression coupling, and leakage of template/domain assumptions into generic Theme code.

Industrial 01 demonstrates that this must be corrected before the catalog grows further.

## 3. Decision

Introduce one Theme-owned, versioned **Template Extension Contract**.

Core understands only generic presentation concepts:

- template identity/version;
- Theme-owned presentation capabilities;
- visual/Homepage preset registration;
- surface descriptors;
- renderer/template-part mapping;
- scoped asset declarations;
- bounded authoring descriptors;
- optional provisioning-blueprint linkage;
- compatibility/fail-soft metadata.

Core does not understand industry semantics such as law, curtains, industrial equipment, hotels or education.

## 4. Architectural Model

```
AZnet Theme Core
│
├── Template Registry
├── Manifest Validation
├── Settings / Preset Resolution
├── Design Token System
├── Site Shell
├── Header / Footer
├── Generic WordPress Templates
├── Homepage Composition Engine
├── Surface / Source Contract
├── Authoring Orchestration
├── Asset Orchestration
├── Provisioning Bridge
└── Template Lifecycle / Fail-soft
        │
        ├── Law 01
        ├── Rèm 01
        ├── Industrial 01
        └── future templates
```

Template packages stay inside the Theme repository in the first implementation. This design does not introduce arbitrary third-party PHP loading, remote executable code, a plugin marketplace, or a second runtime package manager.

D-040 Remote Template Library remains a separate distribution concern. The Core extension contract is the local runtime architecture that remote distribution may target later.

## 5. Template Manifest

A reusable template registers one normalized manifest.

Illustrative shape:

```php
aznet_theme_register_template([
    'schema_version' => 1,
    'id'             => 'industrial-01',
    'label'          => 'Industrial 01',
    'version'        => '1.0.0',

    'capabilities' => [
        'homepage',
        'woocommerce',
    ],

    'presentation' => [
        'visual_preset'   => 'industrial-01',
        'homepage_preset' => 'industrial-01',
    ],

    'homepage' => [
        'surfaces' => [...],
        'renderer' => ...,
    ],

    'assets' => [...],
    'authoring' => [...],

    'provisioning' => [
        'blueprint' => 'industrial-v1',
    ],
]);
```

The exact production API may use functions, immutable arrays or focused registry classes. Contract semantics are normative; implementation syntax is not locked by this design.

## 6. Registry Rules

The Template Registry is the single Theme-side authority for installed reusable presentation templates.

It must:

1. reject duplicate template IDs deterministically;
2. reject unsupported manifest schema versions;
3. normalize optional fields;
4. expose read-only lookup APIs;
5. preserve ordering only where explicitly meaningful;
6. never persist authoritative domain data;
7. fail soft when optional capabilities are unavailable;
8. never execute arbitrary remote PHP.

Generic Core code must query the registry instead of maintaining independent template allow-lists.

## 7. Ownership Boundary

### Core owns

- template registry and manifest validation;
- Theme settings normalization for registered presentation IDs;
- design tokens and generic visual primitives;
- Header/Footer/site shell;
- generic Page/Post/Archive/Search/404 presentation;
- Homepage composition orchestration;
- authoring orchestration;
- surface-aware asset loading;
- provisioning orchestration and rollback mechanics;
- compatibility checks and fail-soft behavior.

### Template owns

- presentation-specific layout and composition;
- template-specific CSS/JS presentation;
- template-part mapping;
- visual token overrides within Theme contract;
- presentation variants;
- declared source slots needed for presentation;
- bounded starter/provisioning recipe references.

### Template must not own

- WooCommerce product/price/stock/variation/cart/order state;
- RootProfile identity/Entity UUID/Claim/Evidence/Relationship/Readiness semantics;
- ConvertFlow Journey/resolver/state/conversion/analytics semantics;
- SEO metadata authority;
- authoritative organization/contact storage;
- secrets/license/signing/private capability logic;
- duplicate domain stores created only to make a template render.

## 8. Source / Surface Contract

Core uses generic source types rather than template-specific data semantics.

Allowed Theme-side source categories include:

- WordPress Page;
- WordPress Post/Category/query projection;
- WordPress synced block (`wp_block`);
- WordPress Media reference;
- WooCommerce public projection through public APIs;
- public/versioned provider projection;
- Theme-owned presentation-only setting.

A template may declare that a surface needs one of these source types.

A template must not directly read plugin options/meta/CPT/tables/private classes, infer authoritative identity/routing from slug/title/Page ID/URL heuristics, reconstruct provider-owned domain registries, or persist copied domain truth in Theme storage.

Provider absence/error/version mismatch must fail soft.

## 9. Homepage Composition Contract

The Front Page retains exactly one native `the_content()` boundary.

Template-declared surfaces compose around it using:

- `before`;
- `native`;
- `after`.

Core owns ordering and dispatch. Templates provide renderers or template-part descriptors.

Generic Core must not branch on `law-01`, `curtain-01`, `industrial-01`, or future template IDs when dispatching registered templates.

## 10. Settings Contract

The current single Theme settings store remains.

No separate per-template authoritative settings database is introduced.

Core settings normalization derives valid registered visual/Homepage presentation IDs from the registry instead of hard-coded lists.

Unknown/unregistered saved values fail safely to a valid fallback without deleting WordPress/plugin data.

Switching templates changes Theme presentation selection only and must not silently mutate authoritative WordPress/plugin content.

## 11. Asset Contract

Templates declare assets with bounded conditions sufficient to determine:

- handle;
- CSS/JS type;
- path;
- dependencies;
- surface condition;
- capability condition;
- optional template scope.

Core loads only assets required for the active presentation/surface.

A template may not cause all ecosystem assets to load globally merely because it is installed.

## 12. Authoring Contract

The Theme backend progressively becomes registry-driven.

Template Library, Homepage Map and bounded section authoring consume descriptors instead of maintaining independent template-name arrays.

A descriptor may declare:

- display label;
- surface order;
- source descriptor;
- editable Theme-owned presentation fields;
- native WordPress editor handoff;
- capability requirements;
- empty/error state.

The authoring UI must not become a proprietary page builder. Native Post/Page/Block authoring remains WordPress-owned.

## 13. Provisioning Bridge

Template registration and provisioning remain separate.

A template may declare an optional provisioning blueprint key.

Provisioning remains explicit/reviewable, mutation-free before confirmation, idempotent where already proven, WordPress-native, provenance-aware, rollback-bounded and ownership-safe.

Selecting a template must not automatically provision content.

Blueprints may create/map WordPress-native starter structure but may not create authoritative commerce, identity, trust or conversion truth.

## 14. Compatibility and Fail-soft

Core remains functional when WooCommerce, RootProfile or ConvertFlow are absent; when a template lacks an optional capability; when a manifest is invalid; or when a saved template ID is no longer registered.

Invalid registration must not fatal the public site. The failure mode is deterministic: reject/ignore invalid registration, preserve valid settings/data and fall back to safe Core presentation.

## 15. Migration Slices

### C0 — Template Registry + Manifest Contract
Create registry, validator and contract tests. No frontend behavior change.

**Exit:** fixture templates register/lookup; duplicate/invalid manifests fail correctly; existing template frontend paths remain unchanged.

### C1 — Settings + Design Registry
Move visual/Homepage preset validity from hard-coded Core allow-lists to registry-derived values while preserving current saved values/defaults.

**Exit:** Law 01, Rèm 01 and Industrial 01 normalize through registry with unchanged outcomes.

### C2 — Template Library Registry
Make Template Library catalog derive installed local templates from registry metadata.

**Exit:** current cards remain equivalent; a fixture card requires no Template Library code change.

### C3 — Homepage Composition Registry
Introduce generic registered surface/renderer dispatch and migrate existing template dispatch incrementally.

**Exit:** generic composer no longer needs template-name dispatch branches for migrated templates; the single native Front Page boundary remains protected.

### C4 — Authoring Registry
Move Homepage Map/authoring descriptors to registry-driven data.

**Exit:** current authoring remains available; fixture template exposes a bounded source slot without editing generic admin branching.

### C5 — Asset Registry
Move template-specific asset declarations to the extension contract.

**Exit:** surface-aware loading remains exact; absent/inactive templates leak no template assets.

### C6 — Provisioning Bridge
Connect manifest metadata to the existing provisioning catalog without merging template selection and provisioning execution.

**Exit:** template-to-blueprint linkage is discoverable; provisioning stays explicit/idempotent.

### C7 — Three-pilot Regression
Verify independently:

- Law 01 → `lstamduchn.vn`;
- Rèm 01 → `remquocanh.vn`;
- Industrial 01 → `minhnguyen.vn`.

No PASS is inferred between pilots.

### C8 — Fourth-template Extension Proof
Add a test-only fourth template fixture registering at least one visual preset identity, Homepage preset identity, Homepage surface/renderer, scoped asset, bounded authoring descriptor and optional provisioning linkage without modifying generic Core allow-lists.

This is the architecture acceptance proof.

## 16. Pilot Rollout Policy

The owner requests incremental rollout to all three pilots.

Each Core slice follows:

```
RED
→ minimal GREEN
→ retained regression
→ candidate PR
→ required CI GREEN
→ merge
→ exact candidate provenance
→ per-pilot preflight
→ pilot update
→ runtime/browser verification
→ evidence
→ next slice
```

Recommended shared-Core rollout order:

1. `lstamduchn.vn` — Law 01;
2. `remquocanh.vn` — Rèm 01;
3. `minhnguyen.vn` — Industrial 01.

A failure on one pilot stops promotion of that slice to the next pilot until root cause is understood and regression protection exists.

A Theme-directory backup is not equivalent to a full site/database backup. Required rollback state must be established for the site being changed before production-changing rollout.

## 17. QA Gates

### L0 — Source/State
Resolve latest canonical main, related PR/dependency state and superseded checkpoints.

### L1 — Static
PHP syntax, ownership scans, manifest/static contracts, no direct private plugin storage/API access, and no new generic Core template-name branch for migrated paths.

### L2 — Contract/TDD
RED observed before implementation; registry/validation/compatibility/fallback contracts; fourth-template fixture contracts as applicable.

### L3 — Runtime
Real WordPress activation; relevant WP/PHP floor; provider absent/present where applicable; Woo absent/present where applicable; no fatal/warning/ownership drift.

### L4 — Browser/Visual/A11y
Desktop/tablet/mobile, keyboard/focus, overflow, console, accessibility and retained pilot presentation intent.

### L5 — Integration
Only where an actual public/versioned provider contract is exercised. Fixtures cannot claim provider integration PASS.

### L6 — Completion/Release
Exact candidate provenance, package/hash when produced, rollback evidence, and independent three-pilot status.

## 18. Non-goals

This work does not create a page builder, template-domain schema, remote executable-code marketplace, redesign of the three existing templates, new plugin, cross-product source move, or one-shot refactor of every historical Theme component.

## 19. Core Acceptance Criteria

The architecture is complete only when:

1. generic Core has one normalized Template Registry;
2. registered IDs drive relevant preset validity;
3. Template Library consumes registry metadata;
4. Homepage dispatch supports registered templates without template-name branching;
5. authoring descriptors can be registered;
6. assets can be registered with bounded scope;
7. provisioning linkage can be declared without automatic provisioning;
8. provider/plugin absence remains fail-soft;
9. template switching does not delete/mutate authoritative domain data;
10. Law 01, Rèm 01 and Industrial 01 retain independent regression evidence;
11. a fourth test template attaches through the contract without editing generic Core allow-lists.

## 20. Rollback

Every slice is independently revertible.

No slice removes an existing compatible path until the destination path plus retained regressions pass.

Bounded compatibility adapters are allowed during migration and retired only after the registry path is proven.

## 21. Documentation Governance

This design introduces an architectural extension contract.

After owner approval of this written spec and before production architecture implementation:

- update authoritative AZT-02 with the accepted Template Extension Contract;
- record the accepted decision/gates in AZT-04;
- update AZT-03 only when canonical baseline/provenance actually changes;
- update the derived execution map only when execution structure materially changes.

Implementation evidence alone does not rewrite source ownership.

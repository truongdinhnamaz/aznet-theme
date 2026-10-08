# Core / Pilot Synchronization Cleanup Design

**Date:** 2026-10-08
**Status:** Proposed for implementation after owner review
**Canonical repository:** `truongdinhnamaz/aznet-theme`
**Design base:** `main@3226908cae6488e4e957e578cafbedeaba45f5a2`

## 1. Goal

Bring AZnet Theme Core and the three D-042 peer pilots back into one clean presentation architecture without changing domain ownership or using version-number alignment as a substitute for real synchronization.

The target state is:

- Shared Core contains only reusable Theme presentation, orchestration and contracts.
- Law 01, Curtain 01 / Rèm 01 and Industrial 01 keep template/preset-specific presentation in their own scoped extension assets/configuration.
- Shared changes are verified across all three peer pilots and the synthetic fourth-template fixture.
- Production pilot state is recorded by exact evidence: version, candidate/source identity when provable, runtime/browser state and rollback path.
- WordPress, WooCommerce, RootProfile, ConvertFlow and other domain owners retain their authoritative data and lifecycle ownership.

## 2. Source priority

This slice follows the current repository sources first.

1. Current implementation/state: canonical GitHub `main`, current PR/commit/workflow evidence, then fresh pilot readback.
2. Architecture/ownership: current `docs/source/AZT-00-governance.md`, `AZT-01-product-charter.md`, `AZT-02-architecture.md`, `AZT-04-roadmap-qa-decisions.md`, and `AZT-05-product-constitution.md`.
3. Baseline/provenance: current `docs/source/AZT-03-baseline-provenance.md` plus exact evidence files.
4. Historical DOCX/snapshots remain provenance only when superseded by current GitHub source.

Implementation evidence never overrides an accepted ownership or architecture rule merely because code currently exists.

## 3. Current problem statement

Fresh review identified four synchronization gaps.

### 3.1 Generic Core contains pilot-specific presentation debt

`assets/css/components/page.css` still contains Rèm Quốc Anh-specific selectors such as:

- `.page-id-726 ...`
- `.page-id-45 ...`
- `.rqa-quote-* ...`
- `.rqa-contact-* ...`

This is incompatible with the target D-043 extension model when those rules live in a generic Core component. Page IDs and site-specific identifiers are compatibility debt, not acceptable generic-Core identity or routing signals.

### 3.2 Pilot versions are not aligned

Fresh authenticated read-only inventory on 2026-10-08:

- Law 01 — `lstamduchn.vn`: AZnet Theme `1.3.86`
- Curtain 01 / Rèm 01 — `remquocanh.vn`: AZnet Theme `1.3.86`
- Industrial 01 — `minhnguyen.vn`: AZnet Theme `1.3.79`

Version difference is evidence of drift, but equal version numbers do not prove byte identity.

### 3.3 Production byte identity is not currently proven

Law 01 and Rèm 01 both report `1.3.86`, but recent WPVibe pilot work and later canonical merges mean exact production bytes must not be assumed identical to current `main`.

Production synchronization therefore requires fresh identity evidence where the tooling can prove it, not inference from metadata.

### 3.4 Legacy compatibility state coexists with the D-043 target model

All three pilots use schema version 3 and the correct active preset IDs, while legacy/generic and preset-scoped settings still coexist. This is allowed only as bounded compatibility.

The target model remains:

- one manifest-driven preset/template identity;
- one request-local effective presentation model where D-041 applies;
- no parallel content/domain store;
- compatibility adapters explicitly treated as transitional;
- no generic Core branch on industry/site/template semantics.

## 4. Architectural decision

Use **migration by extraction**, not redesign.

The cleanup will preserve already-approved public presentation and move pilot-specific rules to their correct preset scope. It will not redesign pages merely because CSS is being relocated.

### 4.1 Shared Core owns

- semantic design tokens;
- site shell;
- generic Page/Post/Archive/Search/404/Home/Woo presentation primitives;
- manifest registry and validation;
- shared effective-surface orchestration;
- generic authoring/navigation primitives;
- generic responsive/accessibility behavior;
- surface-aware asset orchestration;
- fail-soft handling.

### 4.2 Preset/template scope owns

- Law 01 presentation vocabulary and visual treatment;
- Curtain 01 / Rèm 01 presentation vocabulary and visual treatment;
- Industrial 01 presentation vocabulary and visual treatment;
- preset-specific selectors needed to style already-authored WordPress content;
- manifest-declared scoped assets;
- bounded compatibility presentation adapters.

Preset scope remains presentation-only and must not own WordPress/provider domain truth.

### 4.3 WordPress/provider owners retain

- WordPress: Post/Page/Menu/Media/query/editorial lifecycle;
- WooCommerce: Product/price/stock/variation/cart/order/commerce state;
- RootProfile: authoritative identity/profile/trust semantics;
- ConvertFlow: Journey/resolver/state/conversion/analytics semantics;
- other providers: their own authoritative state.

No direct private-storage read, heuristic authoritative routing, or copied domain logic is authorized.

## 5. Migration rules

### 5.1 No pilot identity in generic Core presentation

Generic Core production CSS/PHP/JS must not depend on:

- WordPress Page IDs for pilot recognition;
- pilot host/domain names;
- RQA/Tâm Đức/Minh Nguyên site identity;
- industry-specific labels as behavioral signals;
- template IDs in generic branching where the manifest contract can supply the behavior.

A Page ID may exist in pilot-owned WordPress data/evidence, but not as the generic Theme mechanism for recognizing a presentation contract.

### 5.2 Existing authored classes may remain in WordPress content

Classes such as `rqa-quote-*` or `rqa-contact-*` already embedded in WordPress-owned content do not need destructive content migration merely to clean Theme architecture.

If retained:

- their CSS must load only in the Curtain 01/preset scope;
- Core must not use them for authoritative routing or data inference;
- removal/renaming requires a separately verified content migration;
- presentation before/after extraction must remain equivalent.

### 5.3 Full-width section rule remains shared

The recently approved Page rule remains generic:

- section/background may extend edge-to-edge;
- inner content is constrained consistently by shared container/gutter tokens;
- full-width must not mean text/cards touch the viewport edge.

Preset-specific visual treatment may decorate the section but must not redefine the basic Core container contract without a scoped reason and regression evidence.

### 5.4 Hero-first remains shared

D-048 remains a shared presentation rule:

- an explicitly configured authored Hero comes first;
- generic Page heading is the fail-soft fallback;
- no slug/title/URL/Page-ID inference;
- exactly one meaningful visible H1;
- shared-Core change requires peer-pilot regression.

## 6. File-boundary target

Expected target layout, following existing repository patterns:

- `assets/css/components/page.css` — generic Page presentation only.
- `assets/css/presets/curtain-01.css` — Curtain 01 / Rèm 01-specific presentation.
- `assets/css/presets/editorial.css` or the existing Law 01 scoped asset path — Law-specific presentation only.
- `assets/css/presets/industrial-01.css` — Industrial 01-specific presentation.
- template manifests — declare preset-specific assets/capabilities through the existing D-043 contract.
- tests — enforce Core/preset boundary and retained three-pilot parity.

If current asset registration reveals a more specific existing Curtain 01 path, implementation must use that path rather than create a parallel asset system.

## 7. Verification strategy

Every behavior-changing migration uses RED -> minimal GREEN -> regression.

### L0 — Source/state

- resolve latest canonical `main`;
- re-read current AZT source owning the affected topic;
- inventory current pilot versions and preset settings;
- preserve existing PASS evidence unless invalidated.

### L1 — Static boundary

Add contracts that fail while generic Core contains pilot/site coupling.

At minimum, generic production Core must reject:

- `.page-id-[0-9]+` pilot-scoped selectors;
- `rqa-` presentation identifiers in generic Core files;
- pilot domain/name identifiers in generic Core behavior;
- new industry/template branches that bypass manifest resolution.

Tests must avoid false positives in historical docs/evidence and preset-scoped files.

### L2 — Contract / TDD

Verify:

- Curtain 01 scoped asset contains the migrated presentation rules;
- active Curtain 01 manifest/asset orchestration loads them only when relevant;
- Law 01 and Industrial 01 do not inherit Curtain-specific CSS;
- synthetic fourth-template fixture still registers and participates without new Core branches;
- WordPress/provider ownership guards remain intact.

### L3 — Runtime

On clean WordPress and representative preset fixtures:

- generic Page still renders without any preset;
- Law 01, Curtain 01 and Industrial 01 load without fatal/warning;
- template switching does not delete or rewrite WordPress/plugin-owned data;
- scoped assets appear only on intended surfaces/presets.

### L4 — Browser / visual / accessibility

For each affected pilot and shared fixture:

- desktop/tablet/mobile layout;
- no horizontal overflow;
- heading order/H1;
- keyboard/focus;
- axe critical/serious findings;
- approved visual composition retained;
- full-width section background + constrained inner content retained;
- no cross-preset CSS leakage.

### L5 — Integration

Only where relevant and available:

- Woo/provider present/absent behavior remains fail-soft;
- no private API/storage access is introduced;
- optional provider failures do not become Core truth.

### L6 — Completion / release

- full regression;
- deterministic package/equivalent release evidence required by current repo governance;
- rollback path;
- exact candidate identity;
- per-pilot deployment remains a separate production gate.

## 8. Three-pilot synchronization rule

D-042 peers are:

1. Law 01 — `lstamduchn.vn`
2. Curtain 01 / Rèm 01 — `remquocanh.vn`
3. Industrial 01 — `minhnguyen.vn`

A shared-Core or shared-presentation change is not considered pilot-synchronized until every affected peer has fresh evidence at the required layer.

A pilot may remain locally blocked under D-044 for external access/deployment reasons, but that blocker must be recorded explicitly and must not be disguised as PASS.

No PASS transfers from one pilot to another.

## 9. Production synchronization policy

Do not update a pilot merely to make version numbers equal.

For each pilot:

1. establish current live version/config/readiness;
2. identify exact candidate source;
3. confirm backup/rollback before deployment;
4. deploy only after applicable repository and pilot gates pass;
5. re-read production runtime;
6. run required browser/a11y checks;
7. record byte/source identity if provable;
8. otherwise record identity as UNKNOWN rather than infer it.

Industrial 01's current `1.3.79` state is therefore an update candidate, not an automatic deployment instruction.

## 10. Non-goals

This cleanup does not:

- redesign the three pilot websites;
- migrate WordPress content solely to rename CSS classes;
- create a page builder;
- change public provider contracts;
- modify RootProfile, ConvertFlow, WooCommerce or another product repository;
- create new domain stores;
- force all pilots to deploy simultaneously;
- publish a release merely because source cleanup passes;
- delete legacy compatibility state before destination + regression + rollback are proven.

## 11. Acceptance criteria

This design is implementation-complete only when all applicable conditions below are evidenced:

1. Generic Core production code has no pilot recognition based on Page ID/site identity.
2. Rèm Quốc Anh-specific Page presentation is removed from generic `page.css` and served through Curtain 01 scope.
3. Public presentation of migrated Rèm pages is materially unchanged except for separately approved fixes.
4. Law 01 and Industrial 01 show no new cross-preset styling leakage.
5. D-043 synthetic fourth-template proof remains GREEN.
6. Fresh D-042 three-pilot regression exists for the affected shared-Core boundary.
7. Full-width section / constrained-inner-content rule remains correct across affected Page surfaces.
8. Hero-first shared behavior remains correct and accessible.
9. No ownership/private-storage/domain regression is introduced.
10. Pilot live versions and exact identity status are recorded without inference.
11. Industrial 01 update, if performed, passes its own backup/runtime/browser gate.
12. Merge, release and production deployment remain separately approval-gated under current source governance.

## 12. Rollback

Each implementation micro-slice must be independently revertible.

- CSS extraction rollback: restore the previous generic rule and remove the scoped replacement in one bounded revert.
- Asset registration rollback: revert manifest/registration change without touching WordPress content.
- Pilot deployment rollback: restore the site-specific Theme backup/package already confirmed before deployment.
- Never roll back by copying provider/domain data into Theme settings.

## 13. Execution shape

Execute as bounded slices:

1. **S0 — Exact inventory and leakage map**
2. **S1 — RED Core/preset-boundary contracts**
3. **S2 — Extract Rèm/Page presentation to Curtain 01 scope**
4. **S3 — Shared regression + synthetic fourth-template proof**
5. **S4 — Three-pilot runtime/browser parity**
6. **S5 — Pilot version/identity reconciliation**
7. **S6 — Evidence/source update and release disposition**

Each slice ends PASS or BLOCKED with one exact next action. Existing PASS is not rerun without an invalidation or a gate that explicitly requires retained regression.

# AZnet Theme v1.1 R0 Source Reconciliation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reconcile canonical source from the stale alpha/G0 baseline to the actual v1.0 merged state and ratify the approved v1.1 product/architecture roadmap before any v1.1 production code.

**Architecture:** This is source-only work. Update only the authoritative source owners whose facts/rules changed, preserve AZT-05 unchanged, refresh the derived execution map and manifest, and keep GitHub implementation evidence separate from product truth.

**Tech Stack:** Markdown source documents under `docs/source/`, Git/GitHub provenance, shell/grep verification only; no production PHP/CSS/JS changes.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-1-native-product-system-design.md`

## Global Constraints

- Baseline to revalidate at execution start: canonical `main@f8e1a95c903c3f246528368ae9878eba780539ff` unless superseded by a later valid `main` commit.
- AZT-05 v1.0 must remain byte-unchanged unless a new owner decision explicitly changes the constitution.
- Record v1.0 truth precisely: production metadata is `1.0.0`; core code is merged; release publication/tag may still be pending and must not be falsely recorded as published.
- RootProfile E5-C/E5-D and ConvertFlow F8 remain optional compatibility tracks; do not turn them into core-v1.1 ownership.
- Canonical source versions for this reconciliation: AZT-01 v0.4, AZT-02 v0.5, AZT-03 v0.18, AZT-04 v0.22, AZT-EXEC-MAP v0.14.
- Update `SOURCE_MANIFEST.md` to match the new versions/checkpoint.
- Do not edit historical evidence to make it look current.

---

### Task 1: Revalidate the v1.0 implementation and publication state

**Files:**
- Read: `style.css`
- Read: `functions.php`
- Read: `README.md`
- Read: `docs/evidence/G0_CORE_V1_SCOPE_INVENTORY_20260907.md`
- Read: `docs/evidence/G3_CORE_STATIC_HYGIENE.md`
- Read: `docs/evidence/G4_WORDPRESS_CLEAN_RUNTIME.md`
- Read: `docs/evidence/G5_CORE_BROWSER_A11Y_PERFORMANCE.md`
- Read: `docs/evidence/G6_CORE_LIFECYCLE.md`
- Read: `docs/evidence/G7_CORE_RELEASE_CANDIDATE.md`
- Read: GitHub PR #34 and exact final PR-head workflow runs
- Read: GitHub tags/releases collections

**Interfaces:**
- Consumes: current GitHub state/evidence.
- Produces: a factual reconciliation note used verbatim by Tasks 2-5.

- [ ] **Step 1: Capture canonical main and version metadata**

Run:
```bash
git rev-parse HEAD
git show HEAD:style.css | grep -E '^Version:|^Requires at least:|^Requires PHP:'
git show HEAD:functions.php | grep 'AZNET_THEME_VERSION'
```
Expected at the known baseline: `1.0.0`, WordPress `6.9`, PHP `8.1`, with HEAD resolving to the current canonical main commit.

- [ ] **Step 2: Verify the final v1.0 merge provenance**

Run:
```bash
git show --no-patch --pretty=raw HEAD
git diff --name-status b2e5cca1461233bcb1a0333c5aa51879c3264756..f8e1a95c903c3f246528368ae9878eba780539ff
```
Expected for the known merge: merge tree is equivalent to the verified PR-head tree; no post-merge conflict-resolution production delta.

- [ ] **Step 3: Verify publication truth separately**

Use GitHub API/connector to list `tags` and `releases`. Record exactly one of:
```text
PUBLISHED: v1.0.0 tag/release exists and points to the verified commit
PUBLICATION_PENDING: metadata/core merge is complete but no v1.0.0 tag/release exists
```
Do not infer publication from `Version: 1.0.0`.

- [ ] **Step 4: Write reconciliation evidence**

Create `docs/evidence/R0_V1_SOURCE_RECONCILIATION_20260907.md` with:
```markdown
# R0 v1.0 -> v1.1 Source Reconciliation

- Canonical main: `<sha>`
- Theme metadata: `1.0.0`
- Core v1.0 technical state: merged / verified from listed evidence
- Release publication: `PUBLISHED` or `PUBLICATION_PENDING`
- Optional compatibility tracks: RootProfile E5-C/E5-D, ConvertFlow F8
- Approved next product stream: v1.1 Native Product System
- Constitution: AZT-05 unchanged
```

- [ ] **Step 5: Commit the evidence checkpoint**

```bash
git add docs/evidence/R0_V1_SOURCE_RECONCILIATION_20260907.md
git commit -m "docs: reconcile v1 core release state"
```

**Exit:** L0 factual baseline exists without overstating tag/release publication.

---

### Task 2: Update AZT-03 current baseline/provenance to v0.18

**Files:**
- Modify: `docs/source/AZT-03-baseline-provenance.md`

**Interfaces:**
- Consumes: Task 1 reconciliation.
- Produces: canonical implementation-baseline source for all v1.1 work.

- [ ] **Step 1: Replace stale current baseline facts**

Set the header to:
```markdown
**Version:** v0.18
**Status:** Working Source
**Date:** 07/09/2026
```
Replace the alpha baseline section with current facts:
```markdown
- Canonical `main`: `<Task-1 SHA>`.
- Internal Theme version: `1.0.0`.
- WordPress floor: `6.9+`.
- PHP floor: `8.1+`.
- Architecture: hybrid PHP theme + `theme.json`.
```

- [ ] **Step 2: Close the old G0 exact-next statement**

Replace the stale G0 wording with:
```markdown
Core v1.0 production code/release-candidate closure is complete on canonical main. Release publication/tag state is tracked separately from implementation readiness and must be stated from live GitHub evidence.

Exact product-development next: **R0/R1 v1.1 Native Product System source/design-system stream**, after canonical source reconciliation is merged.
```

- [ ] **Step 3: Preserve historical provenance inventory**

Do not alter the 817/817 classification or registered historical source artifacts unless Task 1 finds a genuine provenance contradiction.

- [ ] **Step 4: Verify stale alpha/current-next text is absent**

```bash
! grep -q '0.1.0-alpha.7' docs/source/AZT-03-baseline-provenance.md
! grep -q 'Exact next:.*G0' docs/source/AZT-03-baseline-provenance.md
```

- [ ] **Step 5: Commit AZT-03**

```bash
git add docs/source/AZT-03-baseline-provenance.md
git commit -m "docs: update AZT-03 for v1 baseline"
```

**Exit:** AZT-03 no longer contradicts canonical v1.0 implementation state.

---

### Task 3: Update AZT-01 and AZT-02 for the approved v1.1 product/architecture

**Files:**
- Modify: `docs/source/AZT-01-product-charter.md`
- Modify: `docs/source/AZT-02-architecture.md`

**Interfaces:**
- Consumes: approved v1.1 spec and unchanged AZT-05 constitution.
- Produces: authoritative product-scope and architecture rules for R1-R6.

- [ ] **Step 1: Bump AZT-01 to v0.4 and add v1.1 product objective**

Add a section whose normative content is:
```markdown
## v1.1 Native Product System objective

AZnet Theme v1.1 improves authoring usability and commerce presentation through semantic Design System 2.0, curated native patterns, bounded Header presets, WooCommerce presentation presets, presentation-only Control Center/System Health and reusable release/performance infrastructure.

The goal is high commercial-theme usability without a proprietary page builder. WordPress-native content remains portable; Theme options remain limited to Theme-owned presentation state.
```
Add the explicit non-goals from the approved spec: no page builder, mega-menu builder, AJAX filter/search engine, wishlist/compare, swatch domain engine, quick-view business engine or custom checkout engine.

- [ ] **Step 2: Bump AZT-02 to v0.5 and ratify the five architecture boundaries**

Add sections that lock:
```text
Design System: foundation -> semantic -> component -> WordPress mapping
Patterns: core/Woo Blocks first; no proprietary content store
Header: primitive renderers -> preset composer -> surface; no drag/drop builder
Woo: public/native output -> Theme surface adapter -> presentation preset; no commerce truth
Control Center: only Theme-owned presentation settings + read-only public capability diagnostics
```
Also ratify that a public extension hook is not created without a real consumer, versioning/source decision and contract tests.

- [ ] **Step 3: Ratify visual-preset feasibility rule**

Add to AZT-02:
```markdown
On the WordPress 6.9 support floor, R1 must prove whether native style-variation selection satisfies the hybrid PHP architecture. If it does, use the native mechanism. If it does not, use a Theme-owned semantic visual-preset mapping; do not convert the Theme to FSE/block-template ownership merely to obtain style variations.
```

- [ ] **Step 4: Ratify settings/storage boundary**

Add:
```markdown
Theme-owned presentation settings may use one versioned Theme Mod schema (`aznet_theme_settings`) with strict allow-list normalization. It must not store WordPress content, Woo commerce state, RootProfile/ConvertFlow domain state, secrets or private provider snapshots.
```

- [ ] **Step 5: Run ownership scans**

```bash
grep -n 'page builder\|private.*option\|parallel.*store\|aznet_theme_settings\|visual.*preset' docs/source/AZT-01-product-charter.md docs/source/AZT-02-architecture.md
```
Expected: page-builder/private/parallel-store matches occur only as prohibitions; `aznet_theme_settings` is explicitly Theme-presentation-only.

- [ ] **Step 6: Commit AZT-01/AZT-02**

```bash
git add docs/source/AZT-01-product-charter.md docs/source/AZT-02-architecture.md
git commit -m "docs: ratify v1.1 native product architecture"
```

**Exit:** product/architecture owners authorize the v1.1 implementation without changing AZT-05.

---

### Task 4: Update AZT-04 roadmap/QA to v0.22

**Files:**
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`

**Interfaces:**
- Consumes: Tasks 1-3.
- Produces: canonical R0-R6 execution sequencing and v1.1 QA rules.

- [ ] **Step 1: Close the old G stream accurately**

Replace `G ACTIVE / Exact next G0` with:
```text
G Core v1.0 Cleanup / Release — TECHNICAL PASS; publication state tracked from GitHub and may remain PUBLICATION_PENDING until tag/release exists.
```
Do not mark GitHub Release published unless Task 1 proved it.

- [ ] **Step 2: Add v1.1 roadmap rows**

Add:
```text
R0 Source reconciliation — ACTIVE until source PR merges
R1 Design System 2.0 — PLANNED
R2 Native Pattern Library — PLANNED
R3 Header System 2.0 — PLANNED
R4 WooCommerce Presentation 2.0 — PLANNED
R5 Control Center + System Health — PLANNED
R6 Performance + Release 2.0 — PLANNED
```

- [ ] **Step 3: Add accepted v1.1 decisions**

Continue the decision log after D-016:
```text
D-017 v1.1 uses WordPress-native authoring/patterns/presets; no proprietary page builder.
D-018 visual preset outcome is fixed, implementation mechanism is evidence-gated on WordPress 6.9 hybrid support.
D-019 Theme settings use one versioned Theme Mod schema and contain presentation state only.
D-020 Header uses bounded presets/primitives, not drag/drop composition.
D-021 Woo v1.1 expands presentation only; application engines such as wishlist/filter/search/swatches remain outside Theme.
D-022 exact-main post-merge verification is required before a release candidate is promoted.
```

- [ ] **Step 4: Preserve QA layer meanings**

Keep L0-L6 definitions; add that editor/frontend parity for patterns/presets belongs to L4 and provider-specific capability detection belongs to L5 only when external public contracts are involved.

- [ ] **Step 5: Commit AZT-04**

```bash
git add docs/source/AZT-04-roadmap-qa-decisions.md
git commit -m "docs: open v1.1 roadmap and QA gates"
```

**Exit:** roadmap no longer points to completed G0 and contains one bounded next stream.

---

### Task 5: Refresh AZT-EXEC-MAP and SOURCE_MANIFEST

**Files:**
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Read: `docs/source/README.md`

**Interfaces:**
- Consumes: AZT-01/02/03/04 updates.
- Produces: derived execution map and source-version manifest.

- [ ] **Step 1: Bump AZT-EXEC-MAP to v0.14**

Replace G0-G8 as the active map with R0-R6 and encode dependencies:
```text
R0 -> R1 -> {R2,R3,R4} -> R5 -> R6
```
R2/R3/R4 may proceed independently after R1 if they consume only stable R1 interfaces.

- [ ] **Step 2: Update SOURCE_MANIFEST versions**

Set:
```text
AZT-00 v0.4
AZT-01 v0.4
AZT-02 v0.5
AZT-03 v0.18
AZT-04 v0.22
AZT-05 v1.0
AZT-EXEC-MAP v0.14
```
Set the canonical checkpoint to the new source branch/PR baseline rather than the obsolete constitution-source branch statement.

- [ ] **Step 3: Verify AZT-05 is unchanged**

```bash
git diff --exit-code <R0-branch-base> -- docs/source/AZT-05-product-constitution.md
```
Expected: zero diff.

- [ ] **Step 4: Verify source cross-consistency**

```bash
! grep -R 'Exact next.*G0' docs/source/AZT-0*.md docs/source/AZT-EXEC-MAP.md
! grep -R '0.1.0-alpha.7' docs/source/AZT-0*.md docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
grep -R 'R1.*Design System' docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md
```

- [ ] **Step 5: Commit derived source files**

```bash
git add docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
git commit -m "docs: refresh v1.1 execution source map"
```

**Exit:** canonical source set is internally consistent and AZT-05 remains unchanged.

---

### Task 6: Source-only PR verification and merge gate

**Files:**
- No production files.

**Interfaces:**
- Consumes: Tasks 1-5.
- Produces: owner-reviewable source PR; production work remains blocked until it merges.

- [ ] **Step 1: Prove source-only diff**

```bash
git diff --name-only <R0-branch-base>...HEAD
```
Expected: only `docs/source/**` and `docs/evidence/R0_V1_SOURCE_RECONCILIATION_20260907.md`.

- [ ] **Step 2: Scan for placeholders and stale baseline**

```bash
! grep -R -E '\bTBD\b|\bTODO\b' docs/source/AZT-01-product-charter.md docs/source/AZT-02-architecture.md docs/source/AZT-03-baseline-provenance.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
! grep -R '815ffcd43653930d1f302a055961ac24cf5ae843' docs/source/AZT-03-baseline-provenance.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
```

- [ ] **Step 3: Open the source PR and stop at owner merge approval**

PR body must state:
```text
PASS: source reconciliation only
UNCHANGED: AZT-05 constitution
BLOCKED until merge: all v1.1 production code
NEXT after merge: R1 Design System 2.0 feasibility + token/settings foundation
```

**Exit:** source owner review gate reached. Do not start R1 production code before this source PR is approved/merged.

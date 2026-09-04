# Parallel Homepage Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Advance AZnet Theme Milestone F Homepage Shell from the latest valid checkpoint as far as possible without changing domain ownership, merging `main`, deploying production, or duplicating unmerged PR #24 work.

**Architecture:** AZnet Theme owns `front-page.php`, the site shell, layout/composition, responsive/a11y presentation, and native WordPress fallback. ConvertFlow continues to own Homepage Journey body/section semantics, source selection, resolver/state/validation, Save/Preview/Publish, navigation/conversion, analytics/observability, and its own scoped assets. The current ConvertFlow source-owner contract binds Homepage Journey into the selected WordPress Page body via `the_content`; therefore Theme must preserve that public WordPress boundary and must not invent a ConvertFlow PHP capability/runtime adapter. The Theme Integration Contract v1 is CSS-token-only; its AZnet producer mapping is already the unmerged PR #24 dependency and must not be copied.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, classic/hybrid PHP theme + `theme.json`, plain PHP contract harnesses, GitHub Actions runtime/browser verification when available.

**Spec:** Owner-approved Stream H directive in this execution; AZT-00 v0.2, AZT-01 v0.2, AZT-02 v0.4, latest W9 source reconciliation for AZT-03 v0.14 / AZT-04 v0.18 / Slice Map v0.10, ConvertFlow Homepage Journey v3.4, and ConvertFlow Platform & Module Control v1.18.

## Global Constraints

- Canonical repository: `truongdinhnamaz/aznet-theme`.
- Work only on a dedicated `work/f-homepage-shell-*` branch; never code directly on `main`.
- WordPress minimum: 6.9+.
- PHP minimum: 8.1+.
- Theme architecture: hybrid PHP theme + `theme.json`.
- Theme naming family: `AZnet\\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-`, `--aznet-theme-*`.
- AZnet Theme owns front-page/site shell, layout/composition, responsive/a11y presentation, semantic token production, and native WordPress fallback.
- ConvertFlow owns Homepage Journey structure/section semantics/source selection/resolver/state/validation/Save-Preview-Publish/navigation/conversion/analytics/observability and bounded section presentation.
- Never read ConvertFlow option/meta/table/private class; never copy `choiceguide_*` domain/business logic, Journey state, resolver, validation, analytics, or conversion logic into Theme.
- Never modify the ConvertFlow repository.
- Provider/plugin absence must fail-soft; Theme must remain a valid WordPress theme without ConvertFlow.
- PR #24 is draft/open/unmerged. Do not merge it, do not duplicate its token projection, and avoid `inc/theme/assets.php` plus `assets/css/integrations/convertflow.css` unless/until that dependency is merged by owner approval.
- E5-C remains external RootProfile BLOCKED. Do not claim Milestone E PASS, do not enable E5-D production takeover, and do not code RootProfile.
- Main merge and production deploy/takeover remain explicit owner hard gates.
- No PASS claim beyond the evidence layer actually verified.

---

### Task 1: F0 Source Governance and Parallel Sequencing

**Files:**
- Create: `docs/evidence/F0_HOMEPAGE_PARALLEL_SOURCE_DECISION.md`
- Produce outside repo: successor AZT-04 DOCX and successor Implementation Slice Map DOCX from the latest recoverable source plus W9 reconciliation evidence.

**Interfaces:**
- Consumes: owner directive authorizing Stream H parallel to E5-C blocker; W9 reconciliation; AZT-00 source-owner rules.
- Produces: Accepted D-015 parallel sequencing record and current F slice map without changing ownership/public contracts.

- [ ] **Step 1: Reconcile latest source state**
  - Confirm `main` and open PRs, especially PR #24.
  - Confirm W8/W9 candidate evidence does not equal canonical-main merge.
  - Confirm E5-C remains BLOCKED and F was previously gated only by sequencing.

- [ ] **Step 2: Record D-015 in the AZT-04 successor**
  - D-015: Homepage Stream H may progress in parallel while E5-C is externally blocked.
  - It does not claim E PASS, authorize E5-D takeover, change ownership, change a public contract, merge PR #24, merge `main`, or deploy production.

- [ ] **Step 3: Reconcile F numbering in the execution-map successor**
  - F0 governance/front-page boundary.
  - F1 native WordPress front-page fallback shell.
  - F2 WordPress body integration boundary (`the_content`), explicitly no Theme-side ConvertFlow PHP capability adapter under the current public contract.
  - F3 Journey-body coexistence/no-semantics-clone contract.
  - F4 semantic token/layout integration; dependency on PR #24 while it is unmerged.
  - F5 absent/failure ownership/fail-soft path.
  - F6 Save/Preview/Publish regression.
  - F7 analytics/conversion regression.
  - F8 runtime/browser/a11y/performance.
  - F9 integration/release closure; main merge remains approval-gated.

- [ ] **Step 4: Render and visually inspect every page of both DOCX successors**
  - Use the project DOCX render workflow.
  - Record SHA-256 and page-count/visual-QA evidence in the F0 checkpoint file.

- [ ] **Step 5: Commit the repo evidence checkpoint**
  - Commit only the text evidence/plan; authoritative DOCX files remain Project artifacts outside Theme production source, consistent with prior project practice.

### Task 2: F1 Native WordPress Front-Page Shell

**Files:**
- Create: `tests/offline/f1-front-page-native-shell-contract.php`
- Create: `front-page.php`
- Create: `docs/evidence/F1_NATIVE_FRONT_PAGE_SHELL.md`

**Interfaces:**
- Consumes: WordPress template hierarchy and existing AZnet header/footer/global shell.
- Produces: native front-page template that renders the WordPress loop and calls `the_content()` without any ConvertFlow-specific dependency.

- [ ] **Step 1: Write the RED contract**
  - Assert `front-page.php` exists.
  - Assert it uses `get_header()`, `get_footer()`, a single `<main id="main" class="aznet-theme-main ...">`, native loop, and `the_content()`.
  - Assert no `choiceguide_`, ConvertFlow private namespace/storage key, `get_option`, `get_post_meta`, `$wpdb`, `template_include`, or `template_redirect` appears in the template.

- [ ] **Step 2: Run the RED contract**
  - Expected failure: `front-page.php` missing.

- [ ] **Step 3: Implement minimal GREEN `front-page.php`**
  - Keep composition Theme-owned.
  - Do not add a ConvertFlow adapter, state, renderer, provider detection, or token stylesheet.
  - Preserve the WordPress body hook by calling `the_content()` inside the normal loop.

- [ ] **Step 4: Run contract + PHP lint**
  - F1 contract GREEN.
  - `php -l front-page.php` GREEN.

- [ ] **Step 5: Commit F1**
  - Bounded commit: test + template + evidence only.

### Task 3: F2/F3 Public Body Boundary and Journey Coexistence

**Files:**
- Create: `tests/offline/f2-front-page-content-boundary-contract.php`
- Create: `tests/offline/f3-homepage-ownership-static-contract.php`
- Create: `docs/evidence/F2_F3_HOMEPAGE_BODY_BOUNDARY.md`
- Modify: `front-page.php` only if RED exposes a Theme-owned defect.

**Interfaces:**
- Consumes: ConvertFlow public behavior of binding Journey body through WordPress `the_content` on the bound Page.
- Produces: proof that Theme preserves the public body boundary and does not clone/inspect Journey semantics.

- [ ] **Step 1: Write RED render harness for the body boundary**
  - Stub WordPress template functions.
  - Make stub `the_content()` emit a sentinel Journey body.
  - Include `front-page.php` and assert the sentinel appears exactly once inside the Theme main shell.

- [ ] **Step 2: Verify RED against a deliberately broken template copy or pre-GREEN state**
  - Failure must be specifically absence/loss of the `the_content` sentinel, not harness syntax/path failure.

- [ ] **Step 3: Run against F1 GREEN**
  - If already GREEN, retain F1 implementation; do not add code merely to create churn.

- [ ] **Step 4: Add ownership static contract**
  - Recursively scan F production delta for forbidden ConvertFlow storage/private access, `choiceguide_*` business identifiers, Journey section keys, resolver/state/analytics/conversion semantics, or Page-ID/slug heuristics.

- [ ] **Step 5: Run F1-F3 regressions and lint**
  - Claim only local L1/L2.

- [ ] **Step 6: Commit F2/F3 evidence/tests**

### Task 4: F4 PR #24 Dependency Gate

**Files:**
- Create: `docs/evidence/F4_PR24_TOKEN_DEPENDENCY.md`
- Do not modify: `inc/theme/assets.php`
- Do not create: `assets/css/integrations/convertflow.css`

**Interfaces:**
- Consumes: PR #24 W8/W9 candidate and ConvertFlow Theme Integration Contract v1 token vocabulary.
- Produces: exact dependency state, without duplicating unmerged code.

- [ ] **Step 1: Re-check PR #24 state and changed paths**
  - If still unmerged, F4 is BLOCKED on owner merge decision for the already-implemented producer mapping.

- [ ] **Step 2: Verify no F branch byte-overlap with PR #24 production paths**
  - No F commit may recreate or modify those paths merely to bypass the gate.

- [ ] **Step 3: Record F4 BLOCKED precisely and continue independent F slices**

### Task 5: F5 Native Fail-Soft / No-Dependency Path

**Files:**
- Create: `tests/offline/f5-homepage-absent-failsoft-contract.php`
- Create: `docs/evidence/F5_HOMEPAGE_FAILSOFT.md`
- Modify: `front-page.php` only if RED exposes a Theme-owned failure.

**Interfaces:**
- Consumes: native WordPress loop only.
- Produces: proof that Theme front page has no runtime dependency on ConvertFlow and retains native Page/post content when no plugin filters the body.

- [ ] **Step 1: Write RED harness for absent ConvertFlow**
  - Render `front-page.php` with only WordPress stubs and native content sentinel.
  - Assert header/main/native content/footer render without any ConvertFlow function/class/hook being defined.

- [ ] **Step 2: Run RED/GREEN cycle**
  - Any failure must be reduced to the Theme-owned template layer.

- [ ] **Step 3: Record failure ownership**
  - Theme cannot and must not catch arbitrary exceptions thrown inside a foreign `the_content` filter by taking over plugin behavior; actual ConvertFlow provider/error isolation remains ConvertFlow-owned and belongs to F6/F7/F9 integration evidence.

- [ ] **Step 4: Commit F5**

### Task 6: F8 Native Runtime / Browser / A11y / Performance

**Files:**
- Reuse existing runtime/browser workflows when they can exercise the new front-page template without invalidating prior PASS gates.
- Otherwise create narrowly-scoped test-only workflow/harness files; no production-domain changes.
- Create: `docs/evidence/F8_NATIVE_HOMEPAGE_RUNTIME_BROWSER.md`

**Interfaces:**
- Consumes: F1-F5 local candidate.
- Produces: L3/L4 evidence for native Homepage shell, independently of F4 token integration.

- [ ] **Step 1: Provision real WordPress runtime on supported floor**
  - Activate AZnet Theme with ecosystem plugins absent.
  - Configure a static front page and exercise `/`.

- [ ] **Step 2: L3 assertions**
  - HTTP 200.
  - No PHP Fatal/Warning/Uncaught markers.
  - AZnet header/footer/main shell present.
  - Native front-page content present.

- [ ] **Step 3: L4 browser assertions**
  - Desktop 1440, tablet 1024, mobile 390.
  - One main landmark, no duplicate IDs, no horizontal overflow, visible keyboard focus, no console errors, accessibility scan critical/serious = 0 where the existing harness supports axe.

- [ ] **Step 4: Performance/asset assertion**
  - No new F-specific global bundle.
  - PR #24 ConvertFlow token projection remains absent from F branch until approved/merged; no duplicate asset load.

- [ ] **Step 5: Record run IDs/artifacts and commit evidence**

### Task 7: F6/F7 ConvertFlow Lifecycle and Analytics/Conversion Regression

**Files:**
- Prefer integration/test-only harnesses; no ConvertFlow source modification.
- Create: `docs/evidence/F6_F7_CONVERTFLOW_REGRESSION.md`

**Interfaces:**
- Consumes: actual/current ConvertFlow public runtime or exact source-owner fixture, F1-F5 Theme candidate.
- Produces: integration evidence that Theme shell does not change Save/Preview/Publish, Journey resolver/state, navigation/conversion, or analytics ownership/behavior.

- [ ] **Step 1: Locate exact current ConvertFlow test artifact/repository evidence without editing it**
  - If unavailable, record exact BLOCKED dependency; do not synthesize private contracts.

- [ ] **Step 2: Exercise active and disabled Homepage Journey through public WordPress behavior**
  - Disabled: native page body remains.
  - Active/authorized preview: ConvertFlow body appears inside the Theme shell while Header/Footer/template remain Theme-owned.

- [ ] **Step 3: Exercise Save / Preview / Publish / Disable regression**
  - Theme must not write or reinterpret ConvertFlow state.

- [ ] **Step 4: Exercise analytics/conversion no-regression**
  - Observe public behavior/evidence only; Theme must not emit, rename, intercept, or persist ConvertFlow Decision Event/conversion state.

- [ ] **Step 5: Record PASS at L5 only if actual integration evidence exists; otherwise BLOCKED/UNKNOWN**

### Task 8: F9 Closure and PR Handoff

**Files:**
- Create/update F checkpoint evidence only.
- Do not merge `main`.

**Interfaces:**
- Consumes: all completed F slices and explicit blockers.
- Produces: reviewable draft PR with exact PASS/BLOCKED/UNKNOWN layers and rollback path.

- [ ] **Step 1: Run fresh full local/static regression**
  - All F contracts plus retained relevant Theme tests.
  - Production PHP lint.
  - Ownership/private-storage scan.

- [ ] **Step 2: Compare F branch against main and PR #24**
  - Verify no accidental duplication/conflict in PR #24 production paths.

- [ ] **Step 3: Package/release only if all required F gates are actually open**
  - If F4 or F6/F7 remains blocked, do not claim F9 release closure.

- [ ] **Step 4: Open/update draft PR for Stream H**
  - Explicitly state: no E PASS claim, no production takeover, no PR #24 merge, no ConvertFlow/RootProfile code, no main merge authorization.

- [ ] **Step 5: Stop only at the remaining hard gate/external blocker**
  - Owner approval is required for `main` merge or production deployment/takeover.

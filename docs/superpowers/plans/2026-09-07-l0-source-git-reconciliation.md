# L0 Source / Git Reconciliation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reconcile the authoritative AZnet Theme source state with canonical GitHub `main@19de5cf3e88ce3c1121a524a83504370f29be9c1`, remove or quarantine repository drift that conflicts with Theme ownership/governance, and produce a recoverable review checkpoint without merging `main`.

**Architecture:** Treat AZT-00/01/02/03/04 as authoritative by subject and GitHub as implementation evidence. Preserve all previously valid production PASS evidence unless an actual runtime/production byte is invalidated. This slice changes repository documentation/non-runtime artifacts and source/provenance records only; it does not change RootProfile/ConvertFlow/WooCommerce ownership, production takeover, or release version.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, GitHub evidence/checkpoints, DOCX source governance.

**Spec:** `04_Roadmap_QA_va_Decision_Log_v0.19.docx`, `02_Kien_truc_Theme_va_Integration_Contracts_v0.4.docx`, `00_Muc_luc_Nguon_va_Governance_v0.2.docx`, and W9/F0 GitHub evidence.

## Global Constraints

- AZnet Theme is the presentation owner, not a business/domain engine.
- Theme must not create or ship a competing authoritative WooCommerce/RootProfile/ConvertFlow truth store.
- Architecture remains hybrid PHP theme + `theme.json`; `theme.json` is not Full Site Editing template composition source-of-truth for v0.x.
- WordPress minimum remains 6.9+; PHP minimum remains 8.1+.
- Naming family remains `AZnet\\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.
- No `main` merge, release/deploy, E5-D takeover, or PR #24/#27 merge in this slice.
- Historical evidence is not rewritten; successor source documents preserve prior artifact identities.

---

### Task 1: Freeze and classify post-checkpoint `main` drift

**Files:**
- Create: `docs/evidence/L0_SOURCE_GIT_RECONCILIATION_20260907.md`

**Interfaces:**
- Consumes: AZT-00 source-priority rules; `main@2f22e7d...` checkpoint; live `main@19de5cf...`.
- Produces: exact 7-commit/6-file drift classification and invalidation analysis used by Tasks 2-5.

- [ ] **Step 1:** Record compare `2f22e7d11e6856f9635725a49cc32cdddc01ec9c...19de5cf3e88ce3c1121a524a83504370f29be9c1` as 7 commits ahead, 0 behind.
- [ ] **Step 2:** Record the exact six net-added paths: `README.md`, `aznet-preview.png`, `starter-content.xml`, `screenshot.png`, `docs/AUDIT_ESCAPING_REPORT.md`, `docs/AUDIT_ESCAPING_AUDIT.csv`.
- [ ] **Step 3:** Classify runtime-production invalidation separately from repository/package drift. Confirm no pre-existing PHP/CSS/template production path changed in this seven-commit delta.
- [ ] **Step 4:** Record that D7/W0-W7 runtime code PASS is retained unless later verification finds a byte invalidation; release/package closure is not extrapolated to the new repository tree.
- [ ] **Step 5:** Commit the evidence checkpoint on the isolated reconciliation branch.

### Task 2: Correct README against authoritative architecture

**Files:**
- Modify: `README.md`

**Interfaces:**
- Consumes: AZT-02 hybrid PHP + `theme.json` architecture and current repository facts.
- Produces: non-authoritative README that no longer contradicts source documents or references nonexistent files.

- [ ] **Step 1:** Remove the statement that `theme.json` means Full Site Editing template ownership.
- [ ] **Step 2:** State the actual architecture: hybrid PHP theme + `theme.json`; PHP template hierarchy owns v0.x composition while `theme.json` provides settings/styles/tokens.
- [ ] **Step 3:** Remove broken references to nonexistent `docs/STARTER_CONTENT.md` and `LICENSE`.
- [ ] **Step 4:** Remove conversational/meta copy and unsupported demo promises; keep installation, version floor, ownership, and current feature summary factual.
- [ ] **Step 5:** Verify README contains no `Full Site Editing`, `docs/STARTER_CONTENT.md`, or nonexistent `LICENSE` reference.

### Task 3: Restore repository boundary for demo/screenshot artifacts

**Files:**
- Delete: `screenshot.png` (currently a 24-byte literal placeholder, not a valid theme screenshot).
- Create: `docs/fixtures/starter-content-wordpress.xml` from the native WordPress Page/Post portion of the current WXR.
- Delete: `starter-content.xml` from repository root.

**Interfaces:**
- Consumes: Theme presentation ownership and WooCommerce commerce-state ownership.
- Produces: an explicitly non-production WordPress-native documentation fixture with no Woo product/private commerce metadata.

- [ ] **Step 1:** Preserve the current WXR Page and Post examples under `docs/fixtures/starter-content-wordpress.xml`.
- [ ] **Step 2:** Omit the Woo Product item and `_regular_price`, `_price`, `_stock_status` metadata from the Theme-owned fixture.
- [ ] **Step 3:** Delete root `starter-content.xml` so release/package roots do not silently acquire domain demo data.
- [ ] **Step 4:** Delete the invalid `screenshot.png`; retain `aznet-preview.png` only as a repository preview image.
- [ ] **Step 5:** Verify root has neither `starter-content.xml` nor `screenshot.png`, and the retained fixture contains no `<wp:post_type>product</wp:post_type>` or Woo price/stock meta keys.

### Task 4: Demote the ad-hoc escaping audit from authoritative evidence

**Files:**
- Modify: `docs/AUDIT_ESCAPING_REPORT.md`
- Modify: `docs/AUDIT_ESCAPING_AUDIT.csv`

**Interfaces:**
- Consumes: AZT-00 evidence/source distinction and L0-L6 no-inference rule.
- Produces: an explicitly non-authoritative historical scan that does not claim security PASS/FAIL or prescribe merges without verified reproduction.

- [ ] **Step 1:** Replace security verdict language with `UNVERIFIED LEGACY SCAN` status.
- [ ] **Step 2:** Remove auto-merge/auto-PR promises and claims that a fix branch removes an “immediate XSS risk” without a reproducing test.
- [ ] **Step 3:** Keep the file/path observations only as triage candidates and point future work to a separate security slice with RED reproduction before production change.
- [ ] **Step 4:** Change CSV severity values to `needs-review` for unverified findings and keep already-escaped entries as informational.
- [ ] **Step 5:** Verify neither file claims a completed security gate.

### Task 5: Create AZT-03 successor for the real canonical baseline

**Files:**
- Create outside repository production source: `03_Current_Baseline_va_Code_Provenance_v0.15.docx`

**Interfaces:**
- Consumes: AZT-03 v0.13 bytes, recorded identity of missing v0.14, W9 source reconciliation, F0/D-015 successors, live GitHub reconciliation.
- Produces: authoritative AZT-03 successor preserving historical v0.14 identity while registering the current canonical GitHub state.

- [ ] **Step 1:** Preserve the recorded v0.14 identity: SHA-256 `0fbc4fdc0eeacc80ccc52a4987c08e5b1fbe7d9e32a6fbf37eac45279d140dce`; do not pretend its missing bytes were recovered.
- [ ] **Step 2:** Update current state to `main@19de5cf3e88ce3c1121a524a83504370f29be9c1` before reconciliation PR merge, Theme version `0.1.0-alpha.7`.
- [ ] **Step 3:** Register W8 L5 + W9 pre-merge L6 as PR #24 candidate evidence only, and F0/F1/F2/F3/F5 as PR #27 branch evidence only; E5-C remains externally BLOCKED and E5-D locked.
- [ ] **Step 4:** Register the seven-commit non-runtime drift and state that prior runtime production evidence is not invalidated, but package/release bytes must be reverified after repository-boundary reconciliation and before any merge/release claim.
- [ ] **Step 5:** Render the DOCX, inspect every page, and iterate until visual QA passes.

### Task 6: Reconcile PR #24/#27 against the current base without merging `main`

**Files:**
- Existing branches only: `work/w8-convertflow-coexistence`, `work/f-homepage-shell-parallel`.

**Interfaces:**
- Consumes: current `main`, reconciliation findings, PR #24/#27 changed-path sets.
- Produces: explicit stale-base/compatibility status and exact merge/reverification gate.

- [ ] **Step 1:** Compare each branch against current `main`; record ahead/behind counts and changed paths.
- [ ] **Step 2:** Do not merge PR #24/#27 into `main` in this slice.
- [ ] **Step 3:** If reconciliation branch changes overlap neither PR production delta, record no source conflict but mark W9 package closure stale relative to current repository/package tree until refreshed.
- [ ] **Step 4:** Keep F4 blocked on PR #24 and F6/F7/F8/F9 unproven as already governed.
- [ ] **Step 5:** Exact next after this checkpoint is owner review of the reconciliation PR, then refresh/reverify PR #24 on the accepted base before any merge decision.

### Task 7: Final verification and review checkpoint

**Files:**
- All Task 1-6 outputs.

**Interfaces:**
- Consumes: branch diff, DOCX render, live PR metadata.
- Produces: one reviewable PR/checkpoint with rollback to `main@19de5cf...`.

- [ ] **Step 1:** Compare reconciliation branch to `main` and verify no PHP/CSS/template production file changed.
- [ ] **Step 2:** Re-fetch README, fixture, audit files and verify all Task 2-4 assertions.
- [ ] **Step 3:** Verify source DOCX version/date/current-main assertions and complete PNG visual review.
- [ ] **Step 4:** Open a draft reconciliation PR; do not merge it.
- [ ] **Step 5:** Report PASS/BLOCKED/EVIDENCE/NEXT with rollback base `19de5cf3e88ce3c1121a524a83504370f29be9c1`.

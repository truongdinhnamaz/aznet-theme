# AZnet Theme Source/State Reconciliation v0.17

Date: 2026-09-03
Repository: `truongdinhnamaz/aznet-theme`
Baseline main: `2f22e7d11e6856f9635725a49cc32cdddc01ec9c`
Scope: documentation/state reconciliation only; no production Theme code mutation.

## Purpose

Reconcile the authoritative AZnet Theme working documents with the newer canonical GitHub implementation/evidence state that superseded the earlier W1 checkpoint.

The governing ownership rule remains unchanged:

> SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.

No RootProfile, ConvertFlow, WooCommerce, or WordPress domain ownership is transferred by this checkpoint.

## Reconciled source artifacts

The following DOCX artifacts were finalized outside the repository and rendered/visually inspected before this evidence update:

- `03_Current_Baseline_va_Code_Provenance_v0.13.docx`
  - SHA-256: `21caa9a4067a7cab67d32216cd74d1d4732fc26acf7108236a9ca73bd512489e`
  - visual QA: 12 rendered pages
- `04_Roadmap_QA_va_Decision_Log_v0.17.docx`
  - SHA-256: `eb69c9279af3b003863b7dfb6a5b5795da4333c91f40cbd3fb28fe1f3f15a69f`
  - visual QA: 17 rendered pages
- `AZnet_Theme_Implementation_Slice_Map_v0.9.docx`
  - SHA-256: `f707973f7ae73fd3212cfd546866fab2c45bfa2866728daf46a39d33e5f96a23`
  - visual QA: 17 rendered pages

## Canonical GitHub state

Main was freshly verified immediately before this checkpoint at:

`2f22e7d11e6856f9635725a49cc32cdddc01ec9c`

Woo implementation evidence superseding the old W1 state:

- PR #4: W1 Woo public capability + surface-aware asset boundary merged.
- PR #5: Single Product presentation merged.
- PR #6: Product Archive / Shop / taxonomy presentation merged.
- PR #7: Cart presentation merged.
- PR #8: Checkout presentation merged.
- PR #9: My Account presentation merged.
- PR #12: Product CSS runtime selector root-cause fix merged.

Deep Woo QA evidence already present in GitHub:

- Woo L3 Runtime Smoke run `33731145442`: success.
- Woo post-merge L4 Browser Visual A11y run `33737399721`: success.

These prove Woo runtime/browser depth only. They do not imply L5 ConvertFlow coexistence/integration or L6 release closure.

## Current Woo execution numbering

The current execution labels are:

- W0 — Woo override policy.
- W1 — Woo capability + surface-aware asset boundary.
- W2 — Single Product presentation.
- W3 — Archive / Shop / Product taxonomy presentation.
- W4 — Cart presentation.
- W5 — Checkout presentation.
- W6 — My Account presentation.
- W7 — Woo L3 runtime + L4 browser/visual/a11y closure.
- W8 — L5 ConvertFlow coexistence/integration.
- W9 — L6 release closure.

This is an execution-label reconciliation only. D-014 ownership and parallel-progress semantics remain unchanged.

## Milestone E state

Milestone E remains ACTIVE:

- E0-E4: retained PASS.
- E5-B: retained PASS local L0-L2 and merged via PR #2.
- E5-C: BLOCKED on the external RootProfile public current-surface contract.
- E5-D: LOCKED / approval-gated production takeover.
- Milestone F: remains LOCKED behind E.

The Theme must not implement RootProfile source, infer authoritative routing by slug/title/Page ID, or use private storage to self-unblock E5-C.

## Control Center U0 observation

A Control Center U0 candidate exists outside main:

- production candidate: `4b4b79e179356cd9db8dca81064e748e931da883`
- work branch evidence head: `f14aa4d4d248bb7b9f63c1211f4bc71864fe00d4`
- U0 L3 runtime run `33754062627`: success
- U0 L4 browser/a11y run `33752848579`: success

Design PR #16 remains draft/open and the U0 production candidate is not merged to main. Therefore U0 is recorded as `PROPOSED / unmerged candidate`, not as an Accepted roadmap milestone.

## PASS / BLOCKED / UNKNOWN

### PASS

- Source-state reconciliation against canonical GitHub main at the SHA above.
- Woo W0-W6 implementation present in main; W7 evidence through L4 succeeded.
- Updated DOCX artifacts rendered and visually inspected.

### BLOCKED

- E5-C: external RootProfile current-surface public contract.

### UNKNOWN / NOT YET PROVEN

- W8 L5 ConvertFlow coexistence/integration.
- W9 L6 release closure.
- E5-D production takeover.
- Milestone F Homepage Shell.
- Control Center as an Accepted/merged roadmap workstream.

## Exact Next

`W8 — L5 ConvertFlow coexistence/integration` is the safe Theme-owned next while E5-C remains externally blocked.

W8 must consume public/versioned contracts only, verify relevant Woo/ConvertFlow coexistence and fail-soft paths, and preserve Woo commerce authority plus ConvertFlow Journey/Filter/Fit/Fast Conversion authority.

W9 must not open until W8 PASS.

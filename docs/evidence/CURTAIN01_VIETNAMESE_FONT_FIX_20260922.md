# Curtain 01 — Vietnamese Roboto font corrective checkpoint

**Date:** 22/09/2026
**Pilot:** `https://remquocanh.vn/`
**Scope:** Theme presentation / typography only
**Decision:** D-037 Accepted

## Problem

Rèm Quốc Anh displayed Vietnamese text with visibly broken/mismatched glyph rendering on Curtain 01 surfaces even though the stored WordPress/WooCommerce text was valid Unicode.

## Root cause

The Theme selected Roboto globally but did not explicitly prove Vietnamese glyph coverage for each shipped local production weight. Curtain 01 also retained legacy serif overrides that bypassed the canonical Theme typography family.

## Repository corrective implementation

PR #247 implemented RED -> GREEN:

- RED V1 Core PR run `35716943924`, job `106710537993`, failed at the intended typography contract because paired Latin + Vietnamese Roboto faces were missing.
- Added exact-provenance Vietnamese Roboto WOFF2 subsets for weights 400/500/700.
- Added explicit Latin and Vietnamese unicode ranges in `theme.json`.
- Retained `font-display: swap`.
- Removed Curtain 01 Cambria/Georgia/Times overrides and routed presentation back through Theme typography tokens.
- Hardened the typography audit against legacy serif overrides.
- Added browser regression using Vietnamese sample text and requiring all three Vietnamese Roboto faces to reach `loaded` state.

Verified PR head: `272d2d7043871bdb808b51870b7a22706ae8e47a`.

Owner-approved merge: `main@15681fc7f02a9dcf5e6a9036665c9d10fc4d1b2e`.

Fresh exact-main evidence:
- V1 Exact Main Verification `35718432856` — SUCCESS.
- X6 Cross-surface Release Closure `35718432826` — SUCCESS.

## Production reconciliation

Fresh authenticated pilot readback reported active AZnet Theme `1.3.30`, while repository metadata at the corrective merge remained `1.3.28`. Therefore a full repository package deploy would be a version downgrade and was not used.

A WPVibe draft was cloned from the live `1.3.30` Theme and patched only at the typography layer. Preview verification confirmed:

- Theme remains `1.3.30`;
- three Vietnamese Roboto faces are emitted for weights 400/500/700;
- three Vietnamese unicode-range declarations are present;
- existing Latin faces remain present;
- Curtain 01 content such as “Rèm Cầu Vồng”, “Rèm Cuốn” and “Rèm sáo” remains correct Unicode;
- no WooCommerce, RootProfile, ConvertFlow or WordPress-owned domain/content data was mutated.

## Production disposition

**PASS**
- repository corrective implementation;
- RED -> GREEN typography contract;
- exact-main V1/X6 verification;
- live-baseline-safe draft reconciliation;
- preview runtime/DOM font declaration checks.

**BLOCKED / PENDING**
- live production publish and post-publish font verification.

Reason: the connected WPVibe Pro account reached its rolling daily limit. Repeated publish attempts were blocked before mutation. The owner explicitly approved deferring the publish until the connector becomes available.

## Rollback / safety

The live Theme remains unchanged by the blocked publish attempts. The approved production path must preserve the current `1.3.30` baseline and WPVibe Theme-file rollback behavior; do not deploy the lower-version canonical package as a shortcut.

## Exact next

When WPVibe becomes available, publish the already-approved font-only draft, then verify the production Homepage emits/loads Vietnamese Roboto 400/500/700 and the affected Vietnamese labels render correctly. If any safety precondition has drifted, stop and revalidate before mutation.

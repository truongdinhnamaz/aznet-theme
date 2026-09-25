# AZnet Theme 1.3.54 — Law 01 Process/FAQ recovery

Date: 2026-09-25

## Scope
Bounded follow-up from live AZnet Theme 1.3.53 on lstamduchn.vn.

Goal: restore Law 01 Process and FAQ Homepage sections after preset-scoped Homepage source initialization without reopening the 1.3.52 source-regression path.

## Ownership
- WordPress remains authoritative owner of Page content.
- AZnet Theme owns only presentation composition and Theme-local reference selection.
- No RootProfile, ConvertFlow or WooCommerce storage/private API/domain mutation is introduced.

## RED
Parent 1.3.53 Process and FAQ templates read only:
- `setting( 'homepage_process_page', 0 )`
- `setting( 'homepage_faq_page', 0 )`

The new contract requires the preset-scoped resolver first and therefore fails against the 1.3.53 parent source.

## Minimal GREEN
Process:
1. resolve `homepage_source_value( 'law-01', 'process' )`;
2. if it does not resolve to a published WordPress Page, preserve the proven legacy `homepage_process_page` fallback.

FAQ:
1. resolve `homepage_source_value( 'law-01', 'faq' )`;
2. if it does not resolve to a published WordPress Page, preserve the proven legacy `homepage_faq_page` fallback.

No About/Team/Services/News source path is changed.

## Version
- `style.css`: 1.3.54
- `AZNET_THEME_VERSION`: 1.3.54

## Verification
Fresh bounded local verification of the exact fetched GREEN source snippets:
- PASS: Law 01 Process/FAQ scoped-first source contract.
- PHP lint PASS for Process and FAQ changed code.

Exact branch/source inspection also confirms:
- both scoped and legacy paths are present;
- scoped lookup precedes legacy fallback;
- the retained 1.3.53 Profile compatibility files were not changed by this slice.

## Verification limitation
Full exact-branch repository checkout could not be cloned in the execution environment because outbound GitHub DNS/network access was unavailable.
No GitHub Actions runs are currently attached to the exact candidate head.
Therefore full V1 regression, WordPress L3 runtime, L4 browser/a11y, package/release and production publish are not claimed.

## Branch / PR
- branch: `fix/law01-process-faq-recovery-v1.3.54`
- base: `work/homepage-map-v1-3-38-baseline@16aef2ddc64f98c835e3d2455a21a7bdb9e65b5c`
- PR: #275

## Next gate
Fresh exact-head CI/full regression and L3/L4 preview/runtime verification. Main merge and production deployment remain explicit owner approval gates.

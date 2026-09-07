# F0 Homepage Parallel Source Decision

Date: 2026-09-05
Repository: `truongdinhnamaz/aznet-theme`
Branch: `work/f-homepage-shell-parallel`
Canonical main at checkpoint: `2f22e7d11e6856f9635725a49cc32cdddc01ec9c`

## PASS — L0 Source / State

Owner decision D-015 is recorded for AZnet Theme execution: Milestone F Homepage Shell may progress as Parallel Stream H while E5-C remains externally blocked on the RootProfile current-surface contract.

The decision changes sequencing only. It does **not**:
- change domain or presentation ownership;
- change a public integration contract;
- claim Milestone E PASS;
- authorize E5-D production takeover;
- authorize RootProfile or ConvertFlow code changes;
- authorize merge of PR #24 or `main`;
- authorize production deployment.

Current Homepage boundary is reconciled to source owners:
- AZnet Theme owns the WordPress front-page/site shell, global presentation, responsive/a11y composition, semantic token production, and native fallback.
- ConvertFlow owns Homepage Journey body/section semantics, source/provider selection, resolver/state/validation, Save/Preview/Publish, navigation/conversion, analytics/observability, and its scoped Journey presentation.
- Current ConvertFlow Homepage integration replaces only the selected WordPress Page body through the public WordPress `the_content` boundary. Theme keeps the template/Header/Footer/site shell.
- Current Theme Integration Contract v1 is CSS-token-only; it does not define a Theme-side ConvertFlow PHP runtime/capability API.

## PR #24 dependency

PR #24 (`work/w8-convertflow-coexistence`) remains draft/open/unmerged at this checkpoint. It already owns the AZnet producer projection for public `--convertflow-theme-*` tokens via:
- `assets/css/integrations/convertflow.css`
- `inc/theme/assets.php`

Stream H must not duplicate or fork those bytes. F4 is therefore an explicit dependency gate while PR #24 remains unmerged; independent Theme-owned F slices may continue.

PR #24 candidate evidence retained without promotion to canonical main:
- W8 bounded L5 run `33761153314`: success.
- W9 pre-merge L6 run `33761875420`: success.
- deterministic candidate package SHA-256: `003f74fa4693f5fedaf1d31e5464312c150cee20e329af083483ce0a630295c5`.

## Authoritative source successors

The current Project did not contain the exact prior W9-reconciled DOCX bytes for AZT-04 v0.18 or Slice Map v0.10. The W9 evidence records their identities, so the successors below were reconstructed from the available v0.17/v0.9 working sources plus the exact W9 reconciliation facts and D-015. Historical evidence was not rewritten.

- `04_Roadmap_QA_va_Decision_Log_v0.19.docx`
  - SHA-256: `af699df2e9691672a7d2d716f588a643ea0c9b639e27129a727593cc5283de97`
  - render: 19 pages
  - content assertions: PASS
  - visual QA: PASS, 19/19 pages inspected
  - records D-015 Accepted and F = ACTIVE Parallel Stream H
- `AZnet_Theme_Implementation_Slice_Map_v0.11.docx`
  - SHA-256: `82e9cf3691bf220de26cfcf917108bde2a898365293a790d52314a70b14a4105`
  - render: 19 pages
  - content assertions: PASS
  - visual QA: PASS, 19/19 pages inspected after removing an initial redundant page break
  - reconciles current F0–F9 execution and PR #24 dependency

Prior W9 identities preserved in the successors:
- AZT-04 v0.18 SHA-256: `807371e4b79b7b080a70f4efe617bb587597dcc840386ed27516e32fb47d7662`
- Slice Map v0.10 SHA-256: `dcf2fc94d41c9e91992f123fa584e709de46d20ec75822631b39437e5a11c0de`

## BLOCKED / UNKNOWN

- E5-C remains BLOCKED on external RootProfile current-surface publication; no Milestone E closure is claimed.
- E5-D production takeover remains LOCKED/approval-gated.
- F4 token/layout integration is BLOCKED on the already-implemented but unmerged PR #24 producer mapping; Stream H will not duplicate it.
- F6/F7 require actual ConvertFlow runtime/integration evidence and are not claimed by F0.

## NEXT

F1 — write the RED contract for a native WordPress `front-page.php` shell that preserves the public `the_content` boundary and has no ConvertFlow-specific dependency.

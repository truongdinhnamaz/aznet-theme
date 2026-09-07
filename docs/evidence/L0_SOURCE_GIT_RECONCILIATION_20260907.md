# L0 Source / Git Reconciliation — 2026-09-07

Repository: `truongdinhnamaz/aznet-theme`
Authoritative project scope: AZnet Theme presentation ownership only.
Rollback/base before this reconciliation branch: `main@19de5cf3e88ce3c1121a524a83504370f29be9c1`.
Prior authoritative GitHub checkpoint recorded by AZT-04 v0.19: `main@2f22e7d11e6856f9635725a49cc32cdddc01ec9c`.

## PASS — L0 state reconciliation

Live GitHub comparison from `2f22e7d11e6856f9635725a49cc32cdddc01ec9c` to `main@19de5cf3e88ce3c1121a524a83504370f29be9c1` is:

- status: `ahead`;
- ahead: `7` commits;
- behind: `0` commits;
- net changed paths: exactly six additions.

The seven commits are:

1. `81c1beb378a69365c9633db17db12766403362f8` — Add README.md for AZnet Theme.
2. `95f63aca11cb70c2918524de7e0850b5cc5a3bd8` — Add starter-content.xml (minimal WXR).
3. `05d766cc69e2dbf1429a27f93cef10501c0a82fc` — Add uploaded preview image.
4. `6d32ac2c8a575568ae0e7f405f01dc2da8bfec06` — Update README preview notes.
5. `ecf6d76229beac7f90f6a15a31f0ef5c9d9f5b92` — Add screenshot placeholder/blob.
6. `c499c6dd6459f41194f5d84992a03e8e3456339f` — Add automated escaping scan report.
7. `19de5cf3e88ce3c1121a524a83504370f29be9c1` — Add escaping scan CSV.

Net-added paths:

- `README.md`
- `aznet-preview.png`
- `starter-content.xml`
- `screenshot.png`
- `docs/AUDIT_ESCAPING_REPORT.md`
- `docs/AUDIT_ESCAPING_AUDIT.csv`

No pre-existing PHP, CSS, template, integration adapter, `theme.json`, or Theme production path changed in this seven-commit delta. Therefore this L0 review finds no byte-level invalidation of the previously verified runtime production tree solely from these seven commits.

This does **not** preserve any prior package/release claim for a package built from a different repository tree. Repository/package closure must be reverified from accepted bytes before a new merge/release claim.

## Drift classification

### README

Repository documentation, non-authoritative. The current README incorrectly describes `theme.json` as Full Site Editing template ownership, although AZT-02 locks v0.x as a hybrid PHP theme + `theme.json`, with PHP template hierarchy remaining the composition source and `theme.json` owning settings/styles/tokens. It also references nonexistent `docs/STARTER_CONTENT.md` and `LICENSE` paths.

Action in this reconciliation branch: correct README to match authoritative architecture and actual repository paths.

### `starter-content.xml`

Demo fixture, not production Theme logic. The root WXR includes a WooCommerce Product plus `_regular_price`, `_price`, and `_stock_status`. That is not evidence that Theme owns commerce state, but keeping domain demo data at repository root blurs the package/ownership boundary.

Action in this reconciliation branch: keep only the WordPress-native Page/Post fixture under `docs/fixtures/` and remove the root WXR.

### `screenshot.png`

The current GitHub blob size is 24 bytes and connector content resolves to the literal placeholder `<binary content omitted>`, so it is not a valid WordPress theme screenshot artifact.

Action in this reconciliation branch: remove the invalid placeholder. `aznet-preview.png` remains a repository preview image only.

### Escaping scan report/CSV

These are ad-hoc scan outputs, not an accepted QA layer and not a reproducing security test. Their existing text overstates conclusions by calling patterns “immediate XSS risk” and recommending branch merge before an ownership-aware RED reproduction.

Action in this reconciliation branch: retain them only as `UNVERIFIED LEGACY SCAN` triage evidence. Any production security change requires a separate bugfix slice with RED reproduction, minimal GREEN, and regression verification.

## Source-provenance gap

W9 evidence on `work/w8-convertflow-coexistence` records a superseding `03_Current_Baseline_va_Code_Provenance_v0.14.docx` with SHA-256:

`0fbc4fdc0eeacc80ccc52a4987c08e5b1fbe7d9e32a6fbf37eac45279d140dce`

The exact v0.14 DOCX bytes are not present among the current Project artifacts. This reconciliation does not claim to recover those bytes and will not rewrite v0.13 as if it were v0.14. A new AZT-03 successor must preserve the v0.14 identity as historical evidence and register the current GitHub state explicitly.

## Retained current gates

- Milestone 0 / Foundation / Tokens / Header-Footer / Generic Templates: retain prior PASS absent evidence of invalidation.
- Milestone E: E0-E4 and E5-B retained PASS at their evidenced layers; E5-C remains BLOCKED on the external RootProfile current-surface publisher; E5-D remains approval-gated.
- Woo W0-W7: retain prior PASS at evidenced layers.
- PR #24: W8 bounded L5 + W9 pre-merge L6 remain candidate evidence only; PR is unmerged and based on the older `2f22e7d...` checkpoint. Its package closure is stale relative to the current repository tree until refreshed on an accepted base.
- PR #27: Homepage F branch evidence remains branch-only; F4 depends on PR #24; F6/F7/F8/F9 are not proven by this reconciliation.

## Forbidden / not performed

- no RootProfile/ConvertFlow/WooCommerce source mutation;
- no Theme production PHP/CSS/template behavior change;
- no `main` merge;
- no PR #24/#27 merge;
- no E5-D production takeover;
- no release/deploy;
- no force update.

## NEXT

Complete the repository-boundary corrections and AZT-03 successor on this isolated branch/artifact checkpoint, verify the branch still changes no Theme runtime production path, then open a draft reconciliation PR for owner review. After that review, refresh/reverify PR #24 on the accepted base before any merge decision.

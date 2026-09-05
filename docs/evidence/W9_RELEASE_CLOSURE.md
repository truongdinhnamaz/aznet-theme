# W9 Woo / ConvertFlow Release Closure

Date: 2026-09-03
Repository: `truongdinhnamaz/aznet-theme`
Base main: `2f22e7d11e6856f9635725a49cc32cdddc01ec9c`
W8 work branch: `work/w8-convertflow-coexistence`
W8 review head before W9 evidence: `ec32870f5d088af374ac611b5fc6dc2e79339eee`
W8 production-code candidate: `615a35b576e28ee22aacc9524fcc37bd5f1e0a29`
Theme version: `0.1.0-alpha.7`
QA layer: L6 pre-merge release closure

## Scope

W9 closes the releasable-package and regression portion of the Woo workstream after W8 bounded L5 coexistence PASS. It does not authorize merge or deployment.

## Fresh full regression

Test-only branch: `test/w9-release-closure`
Harness head: `8947ea8cd08d3ef3f7f33de7f930da3a91767573`
GitHub Actions run: `33761875420`
Job: `100669937298`
Result: `success`
Runtime for closure harness: PHP `8.1.34` on Ubuntu 24.04.

Fresh run proves:

- retained E5-B contract/verifier chain PASS;
- W1-W6 contract/regression scripts PASS;
- W8 public ConvertFlow theme-contract bridge PASS;
- W2-W6 ownership gates PASS after W8 test-scope reconciliation;
- complete production PHP tree lint: `28/28` PASS;
- deterministic package built twice with byte-identical output;
- package unzip/exact-byte reverify PASS;
- package excludes `.github`, `docs`, `scripts`, and `tests`;
- packaged `style.css` remains `0.1.0-alpha.7`;
- packaged W8 production blobs exactly match the reviewed candidate.

## Release candidate artifact

Package: `aznet-theme-w9.zip`
Package file count: `42`
Package SHA-256:

`003f74fa4693f5fedaf1d31e5464312c150cee20e329af083483ce0a630295c5`

GitHub Actions artifact:

- name: `aznet-theme-w9-release-candidate`
- artifact ID: `9895812531`
- uploaded artifact wrapper SHA-256: `e1a578531a5c0c2ae40e64fbc8b8b2857a3b8014fc3e87a7b431a3ae1e76c56b`

The package was generated deterministically with a single top-level `aznet-theme/` directory and was rebuilt twice in the same closure run before hash acceptance.

## Exact W8 production bytes retained in package

- `inc/theme/assets.php` Git blob: `f19e134137412fa79a4865824c89a76c5024ce8a`
- `assets/css/integrations/convertflow.css` Git blob: `39115a63f00a369be72b40dd01dbd988e1d55cc5`

Both hashes were recomputed from the unpacked release candidate during the fresh W9 run.

## PASS scope

W9 is PASS for pre-merge L6 release closure:

- full relevant retained regression is fresh and green;
- production PHP lint is fresh and green;
- deterministic release package exists;
- package SHA/file count are pinned;
- unzip/exact-byte package verification is green;
- rollback remains the existing `main@2f22e7d11e6856f9635725a49cc32cdddc01ec9c` plus the ability to close/revert PR #24 before any production deployment.

## Approval gate / not inferred

The following are intentionally **not** claimed:

- W8/W9 merged to `main`;
- a production deployment;
- E5-C RootProfile unblock;
- E5-D takeover;
- Milestone F Homepage completion;
- Control Center acceptance/merge.

Merge of PR #24 into `main` remains an owner approval gate. Until that approval occurs, canonical `main` remains unchanged and the release candidate must be treated as reviewed-but-unmerged.

## Next

Owner decision on PR #24 merge. No further Woo implementation slice is opened before that gate unless fresh evidence invalidates this closure.

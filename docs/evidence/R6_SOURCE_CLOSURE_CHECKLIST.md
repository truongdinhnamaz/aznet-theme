# R6 Source Closure Checklist

**Base:** `main@4673202f67068294fe92d49d1ed66e371d0ecbb3`  
**Purpose:** bounded checklist for reconciling authoritative source after the completed R6/1.1.0 promotion. This file does not itself change source semantics.

## Live implementation facts to record

- Theme metadata: `1.1.0` in both `style.css` and `AZNET_THEME_VERSION`.
- R6 infrastructure merged via PR #46.
- Superseded G verification workflows retired via PR #47 while lifecycle/version coverage was retained/replaced appropriately.
- Metadata promotion merged via PR #48 after RED -> GREEN version/preservation gates.
- Exact-main verification succeeded on `main@4673202f...`.
- Manual `V1 Release Candidate Package` run `34187975628` succeeded on the same exact main SHA.
- Final candidate identity: one top-level `aznet-theme/` directory.
- Candidate package: `aznet-theme-1.1.0.zip`.
- Candidate SHA-256: `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.
- No GitHub Release has been published; do not infer tag/publication/deployment.

## Authoritative files requiring reconciliation

- `docs/source/AZT-03-baseline-provenance.md`
- `docs/source/AZT-04-roadmap-qa-decisions.md`
- `docs/source/AZT-EXEC-MAP.md`
- `docs/source/SOURCE_MANIFEST.md`

## Must remain unchanged unless a separate approved decision exists

- `docs/source/AZT-05-product-constitution.md`
- AZT-01 ownership/non-goals
- AZT-02 architecture/public-contract boundaries

## Exit

Source-only diff accurately represents R6 technical PASS and `1.1.0`, removes stale "R6 exact next" current-state claims, keeps publication/deployment explicitly pending, and does not silently ratify a new product roadmap.

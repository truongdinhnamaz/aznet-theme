# R-D G-D1 through G-D6 — Bounded Retirement Checks

Date: 2026-09-05
Base: `main@2f22e7d11e6856f9635725a49cc32cdddc01ec9c`
Branch: `work/g-generic-retirement-rd`

This checkpoint applies the fail-closed retirement rule from G-D0. No production deletion is permitted unless a noncanonical Theme-owned presentation path is proven superseded and unused.

## G-D1 — Page legacy

Canonical destination: `page.php` + D0 content shell/CSS.

Current tree inspection found no second Page template or Page-specific legacy presentation path in the Theme repository. `index.php` is not a Page duplicate; it is the WordPress fallback template and remains required for hierarchy fallback.

Outcome: PASS L0/L1 retirement check; zero eligible deletion. Page destination retained unchanged.

## G-D2 — Post legacy

Canonical destination: `single.php` + D0 content shell/CSS.

Current tree inspection found no second single-Post presentation path in the Theme repository. `index.php` remains generic fallback rather than a superseded Post implementation.

Outcome: PASS L0/L1 retirement check; zero eligible deletion. Post destination retained unchanged.

## G-D3 — Archive legacy

Canonical destination: `archive.php` + `template-parts/content/card.php` + D0 content shell/CSS.

The archive template continues to consume WordPress native loop/pagination APIs and the canonical card primitive. No second Theme archive template was found. Woo archive presentation is separately owned by Workstream W and is excluded from R-D.

Outcome: PASS L0/L1 retirement check; zero eligible deletion. Archive destination/card retained unchanged.

## G-D4 — Search legacy

Canonical destination: `search.php` + `template-parts/content/card.php` + D0 content shell/CSS.

The search template continues to consume WordPress native search query/results and search form. No second Theme search/domain engine or legacy search template was found. Woo/Header search concerns are not generic Search-template retirement candidates.

Outcome: PASS L0/L1 retirement check; zero eligible deletion. Search destination retained unchanged.

## G-D5 — 404 legacy

Canonical destination: `404.php` + D0 content shell/CSS.

No second Theme 404 presentation path was found. The canonical 404 uses WordPress/home/search affordances and does not synthesize domain recovery state.

Outcome: PASS L0/L1 retirement check; zero eligible deletion. 404 destination retained unchanged.

## G-D6 — dead generic assets / references

Reviewed live generic artifacts:

- `inc/theme/content-shell.php` — live helper called by Page/Post/Archive/Search/404 and asset eligibility.
- `assets/css/components/generic-content.css` — live surface-aware enqueue target from `inc/theme/assets.php`.
- `template-parts/content/card.php` — live Archive/Search primitive.
- `index.php` — live WordPress hierarchy fallback.

None is dead. No second generic stylesheet/helper/card implementation is present in the current Theme tree. No ConvertFlow/RootProfile private storage or domain implementation is copied into these generic destinations.

Outcome: PASS L0/L1 dead-reference inspection; zero eligible deletion.

## Guard contract

`tests/offline/gd-generic-retirement-contract.php` was added to protect:

1. existence of all D0-D5 canonical destinations;
2. continued functional `index.php` fallback;
3. Generic Content Shell surface eligibility for Page/Post/Archive/Search/404;
4. Woo exclusion from generic asset eligibility;
5. Archive/Search use of the canonical card primitive;
6. absence of direct plugin/private storage reads in generic destinations.

The repository connector available in this execution context does not provide a shell/runtime checkout, and this repository has no GitHub Actions workflow on the current tree, so the new PHP contract cannot be truthfully claimed as executed here. Its source has been committed for the PR and is pending execution in a checkout/CI-capable environment. Existing D7 runtime/browser/integration/release evidence remains retained rather than being rerun without an invalidation.

## Consolidated status

PASS: G-D1 Page, G-D2 Post, G-D3 Archive, G-D4 Search, G-D5 404, G-D6 generic asset/reference **inventory and retirement eligibility** at L0/L1. Each found zero safe production deletions under the fail-closed rule.

BLOCKED/UNKNOWN: Fresh execution result for `tests/offline/gd-generic-retirement-contract.php` is UNKNOWN in this connector-only execution environment. Latest superseding AZT-03 v0.14 / AZT-04 v0.18 / Execution Map v0.10 source-byte mutation remains pending exact authoritative bytes.

EVIDENCE: `docs/evidence/GD0_GENERIC_RETIREMENT_INVENTORY.md`; current canonical tree and call-sites; guard contract `tests/offline/gd-generic-retirement-contract.php`; retained Milestone D L0-L6 closure evidence.

NEXT: G-D7 consolidated regression/rollback checkpoint — verify branch diff is documentation/test-only, preserve D7 rollback chain, open draft PR without merging main.
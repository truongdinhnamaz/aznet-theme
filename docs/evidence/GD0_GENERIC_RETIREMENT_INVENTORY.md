# R-D G-D0 — Generic Templates Retirement Inventory

Date: 2026-09-05
Repository: `truongdinhnamaz/aznet-theme`
Branch: `work/g-generic-retirement-rd`
Base: `main@2f22e7d11e6856f9635725a49cc32cdddc01ec9c`
Scope: Theme-owned generic Page/Post/Archive/Search/404 presentation retirement only.

## Governing state

- Milestone D remains accepted as PASS through L0-L6; no invalidating Generic Templates regression or contract change was found before this stream opened.
- D7 rollback references remain the canonical alpha.6 recovery package plus frozen D5/reconstruction evidence; alpha.7 remains the Generic Templates delivery reference.
- Owner approval in the 2026-09-05 R-D command permits partial Cleanup for the Generic Templates destination dependency independently of Profile/Contact E, Homepage F, Woo, and the external RootProfile blocker.
- GitHub evidence in PR #24 records newer working-source artifacts `AZT-03 v0.14`, `AZT-04 v0.18`, and `Execution Map v0.10`, but the current Project/Library lookup did not provide their authoritative DOCX bytes. Therefore this branch does not mutate superseded v0.13/v0.17/v0.9 source documents. The source-governance update is recorded as pending exact latest bytes rather than rewriting historical sources.

## Canonical destination map

| Surface | Canonical Theme destination | Supporting primitive | Owner boundary |
| --- | --- | --- | --- |
| Page | `page.php` | `inc/theme/content-shell.php`, `assets/css/components/generic-content.css` | Theme presentation; WordPress Page/content state |
| Post | `single.php` | `inc/theme/content-shell.php`, `assets/css/components/generic-content.css` | Theme presentation; WordPress Post/content state |
| Archive | `archive.php` | `template-parts/content/card.php`, content shell/CSS | Theme presentation; WordPress query/pagination |
| Search | `search.php` | `template-parts/content/card.php`, content shell/CSS | Theme presentation; WordPress search/query semantics |
| 404 | `404.php` | content shell/CSS | Theme presentation; WordPress request/404 state |
| Generic Content Shell | `inc/theme/content-shell.php` | `assets/css/components/generic-content.css` | Theme presentation helper + surface-aware asset eligibility |

## Current call-site / use-site scan

Current canonical tree contains exactly the dedicated root templates above plus `index.php` as the required WordPress fallback template. `index.php` remains a compatibility/fallback path and is **not** eligible for deletion merely because D1-D5 destinations exist.

`inc/theme/assets.php` enqueues `assets/css/components/generic-content.css` only through `should_enqueue_generic_content_assets()`. That helper recognizes Page, singular Post, Archive, Search, and 404 and explicitly excludes normalized Woo surfaces. The generic stylesheet is therefore live canonical presentation, not a dead asset.

The current tree contains no second Page/Post/Archive/Search/404 root template, no Theme `woocommerce/` template override for these generic surfaces, and no `choiceguide_*`/ConvertFlow-owned generic template source that can be retired from this repository.

## Retirement classification

| Candidate | Classification | R-D action |
| --- | --- | --- |
| `page.php` | Canonical D1 destination | KEEP |
| `single.php` | Canonical D2 destination | KEEP |
| `archive.php` | Canonical D3 destination | KEEP |
| `search.php` | Canonical D4 destination | KEEP |
| `404.php` | Canonical D5 destination | KEEP |
| `inc/theme/content-shell.php` | Canonical D0 destination | KEEP |
| `assets/css/components/generic-content.css` | Canonical D0 asset | KEEP |
| `template-parts/content/card.php` | Canonical archive/search card primitive | KEEP |
| `index.php` | WordPress compatibility fallback; still reachable for hierarchy cases not covered by dedicated D templates | RETAIN — compatibility fallback |
| Historical presentation in external ConvertFlow/RootProfile snapshots | Outside canonical Theme repository/product scope | DO NOT DELETE in R-D |

## Fail-closed result

G-D0 found **no Theme-repository file that is both historical, superseded by D1-D5, and safe to delete**. Deleting any current D destination would destroy the canonical implementation. Deleting `index.php` would break WordPress theme fallback requirements. Deleting external plugin source would violate product ownership and the explicit R-D scope.

Therefore G-D1 through G-D5 must be executed as focused no-delete retirement checks unless fresh provenance identifies an additional in-repo legacy path. G-D6 may remove only a reference/asset proven dead; current generic content helper/CSS/card are not dead.

PASS: G-D0 L0/L1 inventory, canonical destination mapping, ownership classification, current call-site/use-site inspection.

BLOCKED/UNKNOWN: Exact source-level `Retired` status mutation in the superseding AZT-03 v0.14 / AZT-04 v0.18 / Execution Map v0.10 is pending access to those exact latest bytes. External plugin legacy deletion is intentionally out of scope, not a Theme blocker.

EVIDENCE: canonical `main@2f22e7d11e6856f9635725a49cc32cdddc01ec9c`; current root tree; `page.php`, `single.php`, `archive.php`, `search.php`, `404.php`, `index.php`, `inc/theme/content-shell.php`, `inc/theme/assets.php`, `template-parts/content/card.php`; D7 runs `33598401052`, `33598804637`, `33599269539` from authoritative provenance/roadmap evidence.

NEXT: G-D1 Page retirement check — prove no noncanonical Page presentation path remains before making any deletion.
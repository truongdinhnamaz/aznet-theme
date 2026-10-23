# R-C Header/Footer Legacy Retirement Checkpoint

Date: 2026-09-05
Stream: R-C — Header/Footer Legacy Retirement
Base: `main` at `2f22e7d11e6856f9635725a49cc32cdddc01ec9c`
Branch: `work/g-header-footer-retirement-rc1`
PR: #25

## PASS

- L0 source/state: Milestone C Header/Footer v1.5 remains the accepted destination. Current `main` is a descendant of the E1 imported baseline commit `56da40512cab44a5462e294c348d17abcd605bd6`; comparing that baseline to current main shows no Header/Footer production-path changes. No invalidation was found in this stream.
- Canonical destination/use-site: `header.php` delegates to `template-parts/header/site-header.php`; `footer.php` delegates to `template-parts/footer/site-footer.php`; `inc/theme/assets.php` enqueues only the canonical component CSS paths.
- Compatibility retained: WordPress native menu locations remain registered; Header Woo account/cart utilities remain guarded by public `function_exists()` checks; no private RootProfile/ConvertFlow storage reads were introduced.
- Theme-local duplicate inventory: no exact Theme-local `SiteHeaderRenderer.php`, `SiteFooterRenderer.php`, `assets/css/site-header.css`, or `assets/css/site-footer.css` path exists in the canonical Theme tree. No deletion is therefore justified inside `aznet-theme` merely from those historical provenance names.
- L1/L2 regression guard: GitHub Actions run `33908231419` PASS on PHP 8.1.34. It verifies canonical Header/Footer destinations, CSS enqueue paths, menu compatibility, fail-soft Woo guards, absence of exact Theme-local historical duplicate paths/dangling renderer includes, and PHP lint for the protected Header/Footer files.
- Rollback: base commit `2f22e7d11e6856f9635725a49cc32cdddc01ec9c`; R-C commits are additive test/CI/evidence commits and independently revertible. No production file has been deleted or modified in this slice.

## BLOCKED / UNKNOWN

- Exact external legacy retirement candidates are UNKNOWN at file-row level in this repository. AZT provenance records the 817/817 source-bound inventory and class totals, but the actual row-level inventory/extracted source is not present in canonical `aznet-theme` and the separate evidence repository is not accessible through the current GitHub installation.
- P5.223 cannot be used alone to authorize deletion: current ConvertFlow source governance marks it historical reference/provenance only and explicitly says it is not retirement evidence. Therefore filename similarity or the provenance comments in the destination templates are insufficient to retire an external Mixed/Adapter/Domain path.
- Owner-approved parallel partial-retirement is not reflected in the currently available authoritative AZT-04 v0.17 / Execution Map v0.9. Per AZT-00 ownership, that enduring roadmap/execution-order decision requires an AZT-04 successor and an Execution Map successor in the Project source layer; repository evidence does not substitute for that source update.

## EVIDENCE

- Branch: `work/g-header-footer-retirement-rc1`
- PR: #25
- Contract commit: `3af2e582fe74b037f60e4335dff865b30a114973`
- CI commit: `42fdcf9c4c1326b66b308aa64c55423cbb1061e7`
- CI run: `33908231419` — PASS
- Rollback/base: `2f22e7d11e6856f9635725a49cc32cdddc01ec9c`
- Historical destination baseline checked: `56da40512cab44a5462e294c348d17abcd605bd6`

## NEXT

Obtain the exact source-bound provenance row(s) and current use-site evidence for the next Header/Footer historical candidate. Retire only a candidate classified Presentation with a proven Milestone C destination and preserved compatibility/regression/rollback. Keep Mixed/Adapter/Domain or unproven candidates unchanged and mark UNKNOWN/BLOCKED.

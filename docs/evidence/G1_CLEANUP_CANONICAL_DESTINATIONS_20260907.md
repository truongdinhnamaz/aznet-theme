# G1 — Cleanup Candidate / Canonical Destination Review — 2026-09-07

Repository: `truongdinhnamaz/aznet-theme`  
Branch: `work/v1-core-completion`  
Consumes: G0 core-v1.0 scope freeze.

## Goal

Identify only Theme-local historical presentation code/assets that are demonstrably superseded, unused, reversible, and safe to retire. Unknown, Mixed, Adapter, domain-owned, WordPress hierarchy and repository-evidence paths fail closed.

## Current canonical destinations and live use-sites

### Header

- Entry: `header.php`
- Canonical renderer: `template-parts/header/site-header.php`
- Canonical style: `assets/css/components/site-header.css`
- Live use-site: `header.php` calls `get_template_part( 'template-parts/header/site-header' )`.
- Live asset registration: `inc/theme/assets.php` enqueues `aznet-theme-site-header` from the canonical component stylesheet.

No second Theme-local Header renderer or second Theme-local Header component stylesheet is present in the current destination tree.

### Footer

- Entry: `footer.php`
- Canonical renderer: `template-parts/footer/site-footer.php`
- Canonical style: `assets/css/components/site-footer.css`
- Live use-site: `footer.php` calls `get_template_part( 'template-parts/footer/site-footer' )`.
- Live asset registration: `inc/theme/assets.php` enqueues `aznet-theme-site-footer` from the canonical component stylesheet.

No second Theme-local Footer renderer or second Theme-local Footer component stylesheet is present in the current destination tree.

### Generic content

- Page: `page.php`
- Post: `single.php`
- Archive: `archive.php`
- Search: `search.php`
- 404: `404.php`
- WordPress hierarchy fallback: `index.php`
- Shared listing card: `template-parts/content/card.php`
- Canonical generic component style: `assets/css/components/generic-content.css`
- Archive/Search live use-site: `get_template_part( 'template-parts/content/card' )`.
- Asset use-site: `inc/theme/assets.php` conditionally enqueues `aznet-theme-generic-content`.

`index.php` is explicitly **not** a duplicate retirement candidate; it is the WordPress template-hierarchy fallback.

### Native Homepage

- Canonical entry: `front-page.php`
- Theme-native shell is retained core functionality under AZT-05.
- It is not a cleanup candidate.

### Profile / Contact presentation

- Consumer/adapter paths: `inc/theme/profile-surface.php`, `inc/theme/contact-surface.php`, `inc/theme/rootprofile-current-surface.php`.
- Canonical renderers: `template-parts/profile/surface.php`, `template-parts/contact/surface.php`.
- Corresponding component styles remain Theme presentation assets.

These are optional integration/presentation paths, not historical dead code. E5-C/E5-D remain outside the core-v1.0 critical path, so G1 does not retire or rewrite them.

### WooCommerce presentation

- Theme-owned presentation adapters remain under `inc/theme/woocommerce-*.php` and `assets/css/components/woocommerce-*.css`.
- WooCommerce remains the commerce state owner.
- These files are active presentation capability and are not cleanup candidates merely because Woo is optional.

## Prior retirement findings reconciled

Prior Header/Footer retirement review found no in-repository Theme-local duplicate file eligible for deletion; historical ConvertFlow P5.223 references are provenance only and do not themselves authorize deletion.

Prior Generic Templates retirement review likewise found no in-repository production template/asset that was both historical, superseded and safe to delete. That finding remains consistent with the current destination/use-site inspection.

## Repository-only material

The following are not production behavior and must be handled by package exclusion rather than destructive repository cleanup:

- `.github/`
- `docs/`
- `scripts/`
- `tests/`
- `README.md`
- `aznet-preview.png`

They remain in Git history/repository. G3/G7 own package-root hygiene.

## Deletion eligibility result

A production path may be retired only if all are true:

1. Theme-owned presentation;
2. a canonical destination exists;
3. current use-site evidence proves the candidate is superseded/unreferenced;
4. regression and rollback are known.

**Current eligible production deletion list: EMPTY.**

No candidate satisfies all four conditions with current repository evidence. Deleting files now would convert uncertainty into regression risk without creating product value.

## PASS

**G1 PASS.** Current canonical destinations and live use-sites are explicit. No safe Theme-owned production deletion is identified.

## Rollback

Evidence-only slice. Production tree remains unchanged.

## NEXT

**G2 — close bounded retirement as an explicit no-op PASS unless new concrete evidence produces a qualifying deletion candidate.**
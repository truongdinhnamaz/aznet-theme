# G3 — Core Static / Security / Package Hygiene — 2026-09-07

Repository: `truongdinhnamaz/aznet-theme`  
Branch: `work/v1-core-completion`  
Workflow: `.github/workflows/g-core-static.yml`

## Fresh evidence

GitHub Actions run: `34105085442`  
Job: `101688142020` (`static-release-gate`)  
Head: `220b7821f74bddf1bae933cdd3f4eb38d3597c05`  
Runner: Ubuntu 24.04  
PHP: `8.1.34`

Result: **SUCCESS**.

## Assertions proven

- `style.css` support floor remains WordPress `6.9`, PHP `8.1`, text domain `aznet-theme`.
- `style.css` version and `AZNET_THEME_VERSION` match at `0.1.0-alpha.7`; version promotion remains intentionally deferred.
- Production PHP inventory: **29 files**.
- Production PHP lint: **29/29 PASS**.
- Theme production PHP scan found no direct `$wpdb` use and no quoted `_rootprofile_*` / `_choiceguide_*` private storage keys.
- Retained E5-B current-surface consumer/model/dispatcher/no-takeover contracts PASS.
- Retained Woo W1-W6 capability/surface/ownership/asset-scope contracts PASS.
- W8 ConvertFlow public theme-contract bridge PASS.
- F1 native front-page shell, F2 content boundary, F3 ownership and F5 absent/native fail-soft contracts PASS.
- Release-package exclusion policy is explicit for `.git`, `.github`, `docs`, `scripts`, `tests`, `README.md`, and `aznet-preview.png`.

## Boundary

This is L1-L2 evidence only. It does not prove WordPress runtime, browser/a11y, lifecycle, integration certification, package determinism or release readiness.

## PASS

**G3 / L1-L2 PASS** on the exact head above.

## Rollback

G3 adds verification infrastructure/evidence only; no production Theme behavior changed.

## NEXT

**G4 — verify the independent Theme on clean WordPress 6.9 / PHP 8.1 across Home, Page, Post, Archive, Search and 404 with all optional ecosystem plugins absent.**
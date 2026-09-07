# G4 — WordPress-Clean Runtime — 2026-09-07

Repository: `truongdinhnamaz/aznet-theme`  
Branch: `work/v1-core-completion`  
Workflow: `.github/workflows/g-core-runtime.yml`

## Fresh evidence

GitHub Actions run: `34105202890`  
Job: `101688513611` (`wordpress-clean-runtime`)  
Head: `172d3698b969fa240d1ce7b9d381f395aa4606bf`  
Runner: Ubuntu 24.04  
WordPress: `6.9`  
PHP: `8.1.34`  
MySQL: `8.0.46`

Result: **SUCCESS**.

Runtime artifact: `g4-wordpress-clean-runtime`  
Artifact ID: `10012087257`  
Artifact ZIP SHA-256 reported by Actions: `efab2609a058bf46e319c09e669c77218e905bd5539e084b60883453daf4822b`

## Clean-environment proof

- AZnet Theme activated successfully from the exact branch bytes.
- WordPress active-plugin list was empty; no RootProfile, ConvertFlow, WooCommerce or other ecosystem plugin was required.
- Theme source was copied into WordPress with repository-only paths excluded.

## Core routes verified

The runtime gate requested and verified:

- `/` — native static front page, HTTP 200, Theme Header/Main/Footer and front-page sentinel present.
- `/native-page/` — Page, HTTP 200, Theme Header/Main/Footer and Page sentinel present.
- `/core-sentinel-post/` — Post, HTTP 200, Theme Header/Main/Footer and Post sentinel present.
- `/category/core-test/` — Archive, HTTP 200, Theme Header/Main/Footer and post card present.
- `/?s=core-sentinel` — Search, HTTP 200, Theme Header/Main/Footer and result present.
- `/definitely-missing/` — 404, HTTP 404 with Theme Header/Main/Footer present.

The server log contained no Theme-caused `PHP Fatal error`, `Warning`, `Parse error` or `Uncaught` marker during the route verification.

## Boundary

This is **L3 runtime evidence**. It does not prove browser/responsive/accessibility, update/theme-switch lifecycle, deterministic package, optional provider certification or final release promotion.

## PASS

**G4 / L3 PASS** — AZnet Theme is proven to activate and render its core WordPress surfaces on clean WordPress without any ecosystem plugin dependency.

## Rollback

G4 adds verification infrastructure/evidence only; production Theme behavior remains unchanged.

## NEXT

**G5 — run real-browser desktop/tablet/mobile verification across the core routes for landmarks, duplicate IDs, overflow, keyboard/focus, axe critical/serious, console/page errors and surface-aware asset loading.**
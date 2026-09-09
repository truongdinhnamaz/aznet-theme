# Homepage Composer + Law 01 — L0-L4 and Candidate Package Evidence

**Date:** 2026-09-08  
**Branch:** `work/homepage-composer-law01`  
**Verified implementation/test head:** `51dbc66e8ba99bd718d5abad47594e260534ee19`  
**Base:** `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`  
**Theme metadata:** `1.1.0`  
**Decision:** AZT-04 D-024  

## PASS

### L0 — Source / ownership

- AZT-02 v0.6 ratifies Homepage Composer as Theme-owned presentation composition.
- AZT-04 v0.29 records D-024 and the P4-A bounded implementation slice.
- Content Map stores typed references to existing WordPress Page/Category objects; no copied domain master data.
- Presentation Preset is independent from WordPress content state.
- Native Post/Page remain on the accepted Classic Editor policy.
- `front-page.php` retains the WordPress Loop and exactly one `the_content()` execution.
- Applying `law-01` does not create, rewrite, delete, publish or reclassify WordPress content.
- RootProfile/ConvertFlow/Woo private state and domain semantics remain outside Theme.
- Law Site Provisioning remains a separate follow-on slice.

### L1-L2 — Static / contract / TDD

Homepage Composer retained contracts are GREEN on the verified head:

- settings schema v2 and allow-list normalization;
- typed Page/Category Content Map with no title/slug/URL heuristic discovery;
- fail-soft composer / request-local dedupe ledger;
- Front Page `the_content()` boundary;
- Control Center `Trang chủ` typed mapping/diagnostics with no native-content mutation actions;
- Law 01 presentation/source-aware asset/contrast contracts.

Fresh reusable `V1 Core Pull Request CI` run `34251383405` succeeded on the verified head. Full production PHP lint in the package gate is `82/82`.

### L3-L4 — WordPress runtime / browser / visual / accessibility

`Homepage Composer Law 01 Browser Quality` run `34251383540` succeeded on the verified head.

Artifact:

- name: `homepage-law01-browser-quality`
- ID: `10066173059`
- artifact digest: `sha256:b590c112393b9379f5c408da26f20ec39fb049fef8be6617444d68468ca5359f`

Matrix passed at `1440x1000`, `1024x900`, `390x844`, and `320x760` on WordPress 6.9 / PHP 8.1. Verified outcomes include:

- exactly one `main#main`;
- exactly one H1;
- six mapped Service cards;
- mapped Knowledge / Analysis / News editorial streams with request-local dedupe;
- zero horizontal overflow;
- visible skip-link first keyboard focus;
- axe critical = 0 and serious = 0;
- no Theme-caused console/page/request errors;
- scoped `homepage-law-01.css` only on the active Law 01 Front Page surface.

The first L4 candidate exposed two genuine contrast failures. The production fix introduced separate contrast-safe light-surface and dark-surface color roles; the contract now guards them.

### Retained cross-stream regression

All PR workflows triggered on verified head `51dbc66e8ba99bd718d5abad47594e260534ee19` completed successfully, including:

- F Homepage Native Contract `34251383416`;
- F Homepage Native Runtime Browser `34251383433`;
- F Homepage Native Regression Package `34251383410`;
- G Core Lifecycle Update/Switch/Rollback `34251383507`;
- R1 Visual Preset Feasibility `34251383509`;
- R1 Design System Static `34251383470`;
- R1 Design System Browser Parity `34251383516`;
- R2 Pattern Library Static `34251383493`;
- R2 Pattern Library Browser `34251383466`;
- R3 Header Static `34251383418`;
- R3 Header Browser `34251383444`;
- R4 WooCommerce Static `34251383435`;
- R4 WooCommerce Browser `34251383395`;
- R5 Control Center Static `34251383424`;
- R5 Control Center Browser `34251383403`;
- R6 Performance Baseline `34251383404`;
- P4 legacy Homepage Blueprint Browser `34251383397`;
- V1 Core Pull Request CI `34251383405`;
- Homepage Composer Law 01 Browser `34251383540`.

R3 initially exposed disposable PHP development-server instability after Woo activation/background requests. No Theme PHP fatal was present and all clean scenarios had already passed. The test harness now restarts only the disposable PHP server after Woo lifecycle mutation; unchanged Header assertions then passed on the verified head. No production Header code was changed for that failure.

## Candidate package

`F Homepage Native Regression Package` run `34251383410` succeeded on the verified head.

Artifact:

- ID: `10066139014`
- artifact digest: `sha256:220b9dd0284237b979eaf466dcb88763b261e286e644da696392c93719939233`

Inner installable ZIP:

- filename: `aznet-theme-homepage-native.zip`
- files: `107`
- SHA-256: `a53e58b4e80417190c25bfa3d2a3a2ddcd22505f76b3279f2ba24fd9ff7e4f94`
- deterministic double-build: PASS;
- exact-byte unzip/source-tree comparison: PASS;
- compressed-data integrity: PASS;
- one canonical top-level directory: `aznet-theme/`.

## BLOCKED / UNKNOWN / not implied

- PR #58 is intentionally draft/open and unmerged.
- This evidence does not authorize or imply `main` merge, Git tag, GitHub Release, or production deployment.
- Law Site Provisioning / one-click creation of starter Pages/Categories is not included in this package.
- Provider-specific certification beyond the retained public-contract regressions is not inferred from Theme-owned L0-L4 evidence.

## Rollback

For a site using the candidate, the bounded presentation rollback is to set `homepage_preset=off`. Package-level rollback is to reinstall the previously known-good AZnet Theme package. WordPress content remains native and is not deleted by either rollback path.

## NEXT

Owner-gated next after review is either merge PR #58 to `main` or retain it for further pilot review. The separate product slice after this package is Law Site Provisioning, if approved.

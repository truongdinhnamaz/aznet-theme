# Law Site Provisioning v1 — L0-L4 and Candidate Evidence

**Date:** 2026-09-08  
**Implementation branch:** `work/law-site-provisioning`  
**Stacked base:** `work/homepage-composer-law01@e7f3117948e00efb89430aec14f758bcac8a7081`  
**Verified production/package head:** `6acd8b633503a701eb47ed0df072f6def4e1b03e`  
**Theme candidate:** `1.1.1`  
**Decision:** AZT-04 D-025

## PASS

### L0 — Source / ownership

- AZT-02 v0.7 ratifies bounded WordPress-native provisioning as an explicit setup workflow, separate from Homepage preset switching.
- AZT-04 v0.30 records D-025 Accepted.
- Activation remains mutation-free; writes start only after an authorized user confirms the visible Step 3 change plan.
- WordPress remains owner of Page/Post/Category/Menu/publication data after successful provisioning.
- Existing-site reuse is explicit and typed; no title/slug/URL heuristic establishes authoritative semantic mapping.
- Provisioning does not create RootProfile, ConvertFlow or WooCommerce domain state.

### L1-L2 — Static / contract / TDD

`Law Site Provisioning Candidate Package` run `34258576549` checked out exact head `6acd8b633503a701eb47ed0df072f6def4e1b03e` on PHP 8.1.34 with mbstring/mysqli and completed `scripts/verify-v1-core.sh` successfully.

Fresh results include:

- production PHP lint: 89 files PASS;
- blueprint shape/publication/starter-copy contract PASS;
- activation mutation-free contract PASS;
- read-only typed discovery contract PASS;
- explicit change-plan contract PASS;
- public-API runner/rollback contract PASS;
- idempotency/current-mapping/user-edit contract PASS;
- Setup Ready vs Launch Ready contract PASS;
- provisioning wizard accessible-control contract PASS;
- retained Homepage Composer/Law 01/Classic Editor and reusable v1 core regressions PASS.

TDD/runtime verification exposed two useful REDs before final GREEN:

1. idempotency/current Content Map precedence initially failed; explicit current mapping was made authoritative over old provisioning provenance;
2. first browser run `34257752135` found axe `select-name` failures on Step 2 controls. Root cause was missing accessible names in the provisioning renderer. The fix adds fieldset/legend plus explicit label/ID pairs and a regression contract.

### L3-L4 — WordPress runtime / browser / accessibility

`Law Site Provisioning Browser Quality` run `34258576713` completed successfully on exact head `6acd8b633503a701eb47ed0df072f6def4e1b03e` using WordPress 6.9, PHP 8.1 and MySQL 8.

Artifact:

- name: `law-site-provisioning-browser-quality`
- ID: `10068945557`
- artifact digest: `sha256:c2a55865cc70541da463c26d8fad9a71da62d13c6d225a24b7b4d8d8c5e5f039`

Verified scenarios:

- Theme activation created no Page/Category and did not assign a static Front Page;
- empty site -> 13 structural Pages, 6 direct Service child Pages, 9 starter Categories, Primary Menu, static Front Page, Law 01 Content Map and preset;
- existing site -> explicitly reused Home/About/Services/Contact/Menu with zero created content and byte-preserved existing Page bodies;
- rerun after success -> zero duplicate provisioning roles/menu artifacts;
- injected `homepage:map` failure -> current-run rollback restored settings/Front Page/menu assignment and removed current-run Pages;
- user edit after provisioning -> rerun preserved the edited Page body.

Browser summary:

- Wizard Step 1-4: axe critical/serious blocking issues = 0;
- final wizard state: `READY_WITH_WARNINGS`, not a false Launch Ready claim;
- Step 3 showed 26 explicit plan rows;
- public Law 01 Homepage at 1440x1000, 1024x900, 390x844 and 320x760: exactly one H1, 6 Service cards, horizontal overflow = 0, axe blocking issues = 0;
- three handoff actions present: `Xem trang chủ`, `Chỉnh nội dung cần thiết`, `Quản lý Trang chủ`.

The disposable PHP development-server log contains WordPress-core `WP_Query::rewind_posts()` warnings on repeated GET `/` requests (`class-wp-query.php:3872`). No Theme code mutates the global main-query posts array, the requests returned HTTP 200, browser/page/axe checks remained GREEN, and the warning is not used as evidence of Theme success. It remains an environment/core-runtime observation rather than a claimed Theme failure or provider certification.

## Candidate package

`Law Site Provisioning Candidate Package` run `34258576549` completed successfully on the exact verified head.

Artifact:

- ID: `10068904666`
- artifact digest: `sha256:7e86457e3829f92b1c542fa367e225b179e802df113b8c1fcd57929d6ff43584`

Inner installable ZIP:

- filename: `aznet-theme-1.1.1-law-site-provisioning-candidate.zip`
- files: 112
- SHA-256: `570e4e46294c45621ed5bbbd030c4aa8e12c050254db413f2d34da2855e628dd`
- deterministic double build: PASS;
- compressed-data integrity: PASS;
- exact-byte unpacked/source comparison: PASS;
- package excludes `.git`, `.github`, `docs`, `scripts`, `tests`, `README.md`, `aznet-preview.png`;
- canonical top-level directory: `aznet-theme/`.

The downloaded artifact was independently unpacked in the session; the inner ZIP SHA-256 matched the CI value, `unzip -t` found no errors, metadata reported Theme `1.1.1`, provisioning runtime/admin modules were present and the package contained 112 files.

## BLOCKED / UNKNOWN / not implied

- PR #59 is intentionally draft and stacked on unmerged PR #58.
- No claim is made that `main` contains these bytes.
- No Git tag, GitHub Release or production deployment is authorized or implied.
- RootProfile/ConvertFlow/Woo provider certification beyond retained public-contract regressions is not inferred from Theme-owned provisioning evidence.
- Setup Ready does not imply the client's organization/contact/profile/editorial facts have been reviewed for launch.

## Rollback

Before successful handoff, failed-run rollback restores captured Theme Homepage settings, Front Page/menu assignment and removes only objects proven to have been created by that failed run. Reused/pre-existing objects are not rollback-deleted.

After successful handoff, no destructive Theme reset exists. Presentation rollback is `homepage_preset=off` or explicit remapping; package rollback is reinstalling the prior known-good Theme package. Provisioned WordPress content persists.

## NEXT

Owner-gated next is pilot installation/review of the verified `1.1.1` candidate. Main merge of PR #58/#59, release/tag and production deployment remain separate approval gates.

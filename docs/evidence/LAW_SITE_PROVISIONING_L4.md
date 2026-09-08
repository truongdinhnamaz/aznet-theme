# Law Site Provisioning v1 — L0-L4 and Candidate Evidence

**Date:** 2026-09-09  
**Implementation branch:** `work/law-site-provisioning`  
**Stacked base:** `work/homepage-composer-law01@e7f3117948e00efb89430aec14f758bcac8a7081`  
**Verified production/package head:** `1a735889e88e1a5e649c5b15a5044d7471dff669`  
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

Fresh exact-head `Law Site Provisioning Candidate Package` run `34261603657` checked out `1a735889e88e1a5e649c5b15a5044d7471dff669` on PHP 8.1.34 with mbstring/mysqli and completed `scripts/verify-v1-core.sh` successfully.

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

TDD/runtime verification exposed three useful REDs before the current GREEN candidate:

1. idempotency/current Content Map precedence initially failed; explicit current mapping was made authoritative over old provisioning provenance;
2. first browser run `34257752135` found axe `select-name` failures on Step 2 controls; the renderer received fieldset/legend plus explicit label/ID pairs and a regression contract;
3. pilot screenshot showed Law 01 section surfaces constrained by the generic `.aznet-theme-main` max-width. A Law 01 presentation contract was first tightened and failed on the old candidate because no `.aznet-theme-main--front-page` full-width override existed. The minimal GREEN fix lives only in the Law 01 stylesheet: the Front Page main surface becomes full width while `.aznet-theme-law01-container` remains shell-constrained.

### L3-L4 — WordPress runtime / browser / accessibility

Fresh exact-head `Law Site Provisioning Browser Quality` run `34261603755` completed successfully on `1a735889e88e1a5e649c5b15a5044d7471dff669` using WordPress 6.9, PHP 8.1 and MySQL 8.

Artifact:

- name: `law-site-provisioning-browser-quality`
- ID: `10070160592`
- artifact digest: `sha256:d38623fe441fc637928f92fe418067985a8fc877ca4fb746fdd07ee28a15fac1`

Verified scenarios:

- Theme activation created no Page/Category and did not assign a static Front Page;
- empty site -> structural Pages, six direct Service child Pages, starter Categories, Primary Menu, static Front Page, Law 01 Content Map and preset;
- existing site -> explicitly reused mapped native content with no overwrite;
- rerun after success -> no duplicate provisioning roles/menu artifacts;
- injected mid-run failure -> bounded current-run rollback;
- user edit after provisioning -> rerun preserved edited Page body.

Browser summary:

- Wizard Step 1-4: axe critical/serious blocking issues = 0;
- final wizard state remains Setup Ready / `READY_WITH_WARNINGS`, not a false Launch Ready claim;
- public Law 01 Homepage at 1440x1000, 1024x900, 390x844 and 320x760: exactly one H1, six Service cards, horizontal overflow = 0, axe blocking issues = 0;
- fresh 1440 screenshot visually confirms colored Law 01 section surfaces run edge-to-edge across the viewport while section content remains aligned to the inner shell container;
- three handoff actions remain present: `Xem trang chủ`, `Chỉnh nội dung cần thiết`, `Quản lý Trang chủ`.

The disposable PHP development-server may emit the previously recorded WordPress-core `WP_Query::rewind_posts()` warning on repeated Front Page requests. It is not used as Theme success evidence and remains a separate environment/core-runtime observation.

## Candidate package

`Law Site Provisioning Candidate Package` run `34261603657` completed successfully on the exact verified production head.

Artifact:

- ID: `10070115746`
- artifact digest: `sha256:7d40179b687dace4b63368d19dc8858df8f40939312fbbfb05d25c9570556896`

Inner installable ZIP:

- filename: `aznet-theme-1.1.1-law-site-provisioning-candidate.zip`
- files: 112
- SHA-256: `3fea2ae3d8293c7873442d61d61ac9695bc0dab92145485c9a57b303f663edca`
- deterministic double build: PASS;
- compressed-data integrity: PASS;
- exact-byte unpacked/source comparison: PASS;
- package excludes `.git`, `.github`, `docs`, `scripts`, `tests`, `README.md`, `aznet-preview.png`;
- canonical top-level directory: `aznet-theme/`.

The downloaded artifact was independently unpacked in-session. The inner ZIP SHA-256 matched CI, `unzip -t` reported no errors, and the packaged Law 01 CSS contains the verified full-width Front Page main override plus the retained constrained inner container rule.

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

Owner-gated next is pilot installation/review of the refreshed `1.1.1` full-width candidate. Main merge of PR #58/#59, release/tag and production deployment remain separate approval gates.

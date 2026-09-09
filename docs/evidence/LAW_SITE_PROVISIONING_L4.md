# Law Site Provisioning v1 — L0-L4 and Candidate Evidence

**Date:** 2026-09-09  
**Implementation branch:** `work/law-site-provisioning`  
**Stacked base:** `work/homepage-composer-law01@e7f3117948e00efb89430aec14f758bcac8a7081`  
**Verified production/package head:** `9a39977a6888f1fc656dd64f202e47dbd441ac3c`  
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

Fresh exact-head `Law Site Provisioning Candidate Package` run `34343371228` checked out `9a39977a6888f1fc656dd64f202e47dbd441ac3c` on PHP 8.1.34 with mbstring/mysqli and completed `scripts/verify-v1-core.sh` successfully.

Fresh results include:

- production PHP lint: 89 files PASS;
- Homepage Composer + Law 01 presentation contract PASS;
- Law 01 full-width surface contract PASS;
- Law 01 content-derived stylesheet cache-key contract PASS;
- provisioning blueprint/activation/discovery/plan/runner/idempotency/readiness/admin-a11y contracts PASS;
- retained Classic Editor, Woo, editorial and reusable v1 core regressions PASS;
- deterministic double package build and exact-byte unpacked/source comparison PASS.

TDD/runtime verification exposed four useful REDs before the current GREEN candidate:

1. idempotency/current Content Map precedence initially failed; explicit current mapping was made authoritative over old provisioning provenance;
2. first browser run `34257752135` found axe `select-name` failures on Step 2 controls; the renderer received fieldset/legend plus explicit label/ID pairs and a regression contract;
3. pilot screenshot showed Law 01 section surfaces constrained by the generic `.aznet-theme-main` max-width. A Law 01 presentation contract was tightened first; the minimal fix added a Law-01-only Front Page main override while retaining `.aznet-theme-law01-container` as the constrained inner shell;
4. the real pilot still showed the old constrained layout after installing the refreshed `1.1.1` candidate. Root-cause investigation confirmed the packaged full-width CSS and Front Page class were correct, while `enqueue_homepage_law01_asset()` still used only `AZNET_THEME_VERSION` (`1.1.1`) as the stylesheet cache key. Because both the pre-fix and full-width packages advertised the same asset URL version, browser/CDN caches could legitimately retain the old stylesheet. A new offline regression was first run against the previous package and failed on the missing content-derived cache key. The minimal GREEN fix fingerprints `homepage-law-01.css` bytes with SHA-256 and appends a 12-character digest to the existing Theme version.

### L3-L4 — WordPress runtime / browser / accessibility

Fresh exact-head `Law Site Provisioning Browser Quality` run `34343371237` completed successfully on `9a39977a6888f1fc656dd64f202e47dbd441ac3c` using WordPress 6.9, PHP 8.1 and MySQL 8.

Artifact:

- name: `law-site-provisioning-browser-quality`
- ID: `10100703126`
- artifact digest: `sha256:ec8d03a9b42344c9350800e505ac779779d009e2063494645eb2d523da451d08`

Verified scenarios remain GREEN:

- Theme activation mutation-free;
- empty-site provisioning;
- existing-site no-overwrite;
- rerun idempotency;
- injected current-run rollback;
- user-edit preservation;
- Wizard Steps 1-4 axe critical/serious blocking issues = 0.

The refreshed browser regression now asserts the actual layout geometry rather than inferring full width from lack of overflow. At 1440x1000, 1024x900, 390x844 and 320x760:

- `.aznet-theme-homepage--law-01`, Hero, Services and Final CTA have left edge `0` and right edge equal to viewport width within 1px tolerance;
- horizontal overflow = 0;
- exactly one H1 and six Service cards;
- axe critical/serious blocking issues = 0;
- `homepage-law-01.css` is emitted with a content-derived URL such as `?ver=1.1.1-4bba9dc61a66`, preventing the stale `?ver=1.1.1` stylesheet from being reused after its bytes change.

The disposable PHP development-server may emit the separately recorded WordPress-core `WP_Query::rewind_posts()` warning on repeated Front Page requests. It is not used as Theme success evidence and remains a separate environment/core-runtime observation.

## Candidate package

`Law Site Provisioning Candidate Package` run `34343371228` completed successfully on the exact verified production head.

Artifact:

- ID: `10100675340`
- artifact digest: `sha256:3cb8dd290319ee426c224ea73187da97a053437a3d012a91dddc3105fc986910`

Inner installable ZIP:

- filename in CI: `aznet-theme-1.1.1-law-site-provisioning-candidate.zip`
- files: 112
- SHA-256: `09d92603e97688abe8a30212ed17b7e5be8e0c642ae1f78674cc95107ecf91c4`
- deterministic double build: PASS;
- compressed-data integrity: PASS;
- exact-byte unpacked/source comparison: PASS;
- package excludes `.git`, `.github`, `docs`, `scripts`, `tests`, `README.md`, `aznet-preview.png`;
- canonical top-level directory: `aznet-theme/`.

The downloaded artifact was independently unpacked in-session. The inner ZIP SHA-256 matched CI, `unzip -t` reported no errors, and the package contains all three required pieces for the pilot symptom: the Front Page full-width class, the Law 01 full-width CSS override and the content-derived Law 01 stylesheet cache key.

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

Owner-gated next is pilot replacement with the cache-safe `1.1.1` candidate and visual confirmation on the real site. Main merge of PR #58/#59, release/tag and production deployment remain separate approval gates.

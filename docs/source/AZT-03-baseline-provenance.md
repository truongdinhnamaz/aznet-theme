# AZT-03 — Current Baseline và Code Provenance

**Version:** v0.52
**Status:** Working Source  
**Date:** 18/09/2026
**Repository:** `truongdinhnamaz/aznet-theme`

## 1. Source ownership

AZT-03 owns the canonical implementation baseline, provenance rules, registered source artifacts and migration evidence model. It does not own product scope, architecture, public contracts or roadmap semantics.

Live implementation facts are resolved from GitHub. Historical evidence is not rewritten merely to make the document look current.

## 2. Current canonical baseline

- Live `main` HEAD is resolved from GitHub at execution time; stable provenance checkpoints are recorded below rather than treated as permanently current.
- Canonical X6 technical integration is `main@c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`, tree `70c04929afe38e87d317bad3414157e9e2bd6f74`, after owner-approved PR #87; `V1 Exact Main Verification` run `35129331899` and X6 push-to-main run `35129331927` both completed SUCCESS on that exact merge SHA.
- Published release `v1.1.0` source anchor: `7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`, tree `740406a02ea77a4cb6fdd8f2ee98b7b88f909560`.
- Owner-approved publication closure PR #72 merged at `main@15f25e4f6d8e64c588866405629b793a85ee0323`; restored post-noop checkpoint `main@e7e5a9c2d2a867f631b029f3f77a675e32fad573` has the same tree `d50c9f04465f7f6990ac3747ee55a848e3187beb` and zero file delta from PR #72.
- Final published v1.1.0 production package remains the P5-verified 121-file set, SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`.
- Canonical repository technical baseline is Theme version `1.3.0` at owner-approved PR #98 merge `main@afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`, tree `020db34ca6f360d5af12e6f15abf4c9ba51179f0`; v1.3 technical closure, publication and production deployment are now PASS at their separately verified scopes.
- Post-release live repository checkpoint: PR #129/#130/#131 closed the Law 01 Burgundy Homepage presentation sequence, media-rich visual QA/Team portrait link accessibility, and Professional Footer column order. Canonical `main@8238248f0d97383d0e43461c481ad6bbc1f480a7` passed `V1 Exact Main Verification` run `35315451153` on exact merged bytes. Theme metadata/released baseline remains `1.3.0`; no new release publication/deployment is inferred. Evidence: `docs/evidence/LAW01_HOMEPAGE_CLOSURE_20260918.md`.
- Corrective inheritance checkpoint: owner-approved PR #133 merged to `main@a66ef1639c299a06e62e9797aa2f44fe4e26cd8d` with Theme metadata `1.3.3`. It restores the approved v1.3.2 Law 01 unified-heading system while retaining the later #129-#131 closure. Final-head Y5 run `35318352870` and X6 run `35318352677` PASS; deterministic `aznet-theme-1.3.3.zip` has 145 production files, 108/108 packaged PHP lint PASS and SHA-256 `172a98dc00a52228fbb44a6ad24b79d568b7b543faa484c1ded59511b33e35d7`. Fresh exact-main V1 run `35318696376` and X6 run `35318696388` PASS. Published GitHub Release/deployment remain historical `v1.3.0`; no v1.3.3 tag/Release/deploy is inferred. Evidence: `docs/evidence/V1_3_3_CORRECTIVE_INHERITANCE_20260918.md`.
- Dedicated Law 01 Hero source checkpoint: owner-approved PR #135 merged exact verified head `d0781a2c25a3b15bbc535184e0b03bafa2141a9b` to canonical `main@d008f66cfa7357f3d6e4bede492e41a5d49a4112`. Git comparison from final head to merge commit has zero file delta, so merged production/test bytes are identical to the verified PR head. Theme metadata is `1.3.4`; Law 01 Hero now consumes the typed `homepage_hero_page` WordPress Page reference, with title/excerpt/body/featured image owned by that Page and fail-soft omission when the mapping is invalid. All 34 triggered final-head workflows completed SUCCESS, including D-027 L3/L4/Exact Package/Retained Regression, V1 Core PR CI, Homepage Law 01 browser, Law Site Provisioning browser/candidate package, F Homepage runtime/regression, X6 release closure, Y5 client delivery and Release Version Consistency. Y5 deterministic package evidence produced `aznet-theme-1.3.4.zip`, 145 files, SHA-256 `5cf0041ab09a6b9718e4354d2aa9c00850aa93e83c9ae775a640dc613caf3f83`. No exact-main workflow run was observed on merge commit `d008f66...`; therefore this checkpoint claims merged-byte identity from zero file delta plus final-head evidence, not a fresh exact-main rerun. Published GitHub Release and verified production deployment remain historical `v1.3.0`; no `v1.3.4` tag/Release/deploy is inferred. Evidence: `docs/evidence/V1_3_4_DEDICATED_HERO_SOURCE_20260918.md`.
- Law 01 Hero backward-compatibility checkpoint: owner-approved PR #138 merged final verified head `949e9dd97ce805eed43811ce633739ed1934c1c2` to canonical `main@a9a3578516971abd9a9223c1e8dac69da5657bea`. Theme metadata is `1.3.5`. A valid dedicated `homepage_hero_page` remains preferred; when the mapping is absent/invalid on upgraded sites, the Theme may compose the pre-1.3.4 Hero from the same public WordPress-native Site/Front Page values previously used, without introducing duplicate/domain storage. Final-head 25/25 workflows PASS; exact-main V1 run `35335680576` and X6 run `35335680518` PASS. Y5 deterministic `aznet-theme-1.3.5.zip` has 145 files, 108 packaged PHP lint PASS and SHA-256 `ec9cf5c35dd3de8c7cff5673c5e3f392abdc3894cd676738e746b354c70e1564`. Published GitHub Release/deployment remain historical `v1.3.0`. Evidence: `docs/evidence/V1_3_5_HERO_COMPATIBILITY_20260918.md`.
- Law 01 visual-parity checkpoint: owner-approved PR #139 merged final verified head `b7947bf2722db6bde8fd1c29ae377318b8e99c9c` to canonical `main@ffafe55b90db1d5313b36a5151110ca1e3f67789`. The Theme-owned Burgundy Law 01 presentation now uses the approved Hero 48/52 desktop proportion, compact trust-strip rhythm and responsive Services geometry (6-up wide desktop, 3+3 compact desktop, 1-up mobile). Final-head 24/24 workflows PASS, including Homepage Composer Law 01 Browser Quality; exact-main V1 run `35343960510` and X6 run `35343960430` PASS. This is presentation-only and does not resolve RootProfile Team issue #112. Evidence: `docs/evidence/LAW01_VISUAL_PARITY_HERO_SERVICES_20260918.md`.
- Published release `v1.2.0` remains retained historical PASS at exact technical source `c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`; its owner-approved production deployment to `tamduchanoi.aznet.vn` remains retained evidence via deployment run `35135759389` and independent read-only verification run `35136876330`.
- v1.3 exact-main verification: V1 Exact Main run `35206627102` SUCCESS plus pre-existing X6 push-to-main release path run `35206626992` SUCCESS on `afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`; exact `aznet-theme-1.3.0.zip` SHA-256 `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`, 144 package files, 107 packaged PHP lint PASS, 32/32 browser/axe PASS, switch-away/switch-back lifecycle PASS. Evidence: `docs/evidence/V1_3_TECHNICAL_CLOSURE_20260917.md`.
- Published/GitHub-Release release is `v1.3.0`: owner-approved publication run `35211254633` published annotated tag `v1.3.0` (tag object `56f2ae2e8ba6e1fb4e8359447656e88ab6411ba6`) dereferencing to exact technical commit `afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`, GitHub Release `390623960`, and asset `570039299` (`aznet-theme-1.3.0.zip`) with SHA-256 `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`; publication evidence artifact `10492411096` has digest `sha256:0a18d2169ad8d92a587421289ede773031e56d3646ddac44ba326ebe4ea6fd75`. Evidence: `docs/evidence/V1_3_PUBLICATION_20260917.md`.
- Owner-approved v1.3.0 production deployment to `tamduchanoi.aznet.vn` is PASS at the verified Theme-owned/site-operations scope. Deployment run `35222200660` upgraded active Theme `1.2.0 -> 1.3.0` using the exact published asset and completed SUCCESS; deployment artifact `10497483290` has digest `sha256:84d041445b606279a0caebef60b9c38206f5a9a88b8e1b759453484dfad1c6b1`. Fresh independent read-only run `35223053733` then completed SUCCESS, verifying active Theme `1.3.0`, Standalone Core `ready`, retained `header-utility` menu ID `15`, approved phone presentation and the 28/28 public matrix; artifact `10497925626` has digest `sha256:756f765fe70f2ca61978111678853204808ea4cb4cd44fdd3453d50e177313b2`. Temporary ops files were removed at cleanup commit `6ef53f0bd20c69c65b88a753627436e31976d606`; the cleaned ops branch had zero net file delta from `main@2c4e6f0442335ac3e690e97beaca4d4bf6405fa5`. Provider L5 remains unproven. Evidence: `docs/evidence/V1_3_PRODUCTION_DEPLOYMENT_20260917.md`.
- WordPress floor: `6.9+`.
- PHP floor: `8.1+`.
- Architecture: hybrid PHP theme + `theme.json`.
- Naming family: `AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.

Current production state includes:

- Milestone 0 Source Freeze & Provenance;
- Theme Foundation;
- Semantic Design Tokens;
- Header/Footer presentation;
- Generic Templates Page/Post/Archive/Search/404;
- WooCommerce presentation work W1-W9 including the public ConvertFlow token projection;
- Theme-native WordPress Homepage shell;
- G0-G8 core-v1.0 cleanup/release-candidate closure, including metadata promotion to `1.0.0`;
- R0 v1.0 -> v1.1 source reconciliation;
- R1 Design System 2.0, including one normalized/versioned Theme settings schema, expanded semantic tokens, Default/Editorial/Commerce visual presets and editor/frontend parity evidence;
- R2 Native Pattern Library with 18 shipped patterns: 16 WordPress-native/core-block patterns plus 2 Woo-block-dependent patterns registered only through exact public block capability detection;
- R3 Header System 2.0 with four bounded Theme-owned presets (`standard`, `compact`, `commerce`, `overlay`), three sticky modes, shared primitives/composer, accessible progressive-enhancement mobile navigation and public-Woo fail-soft Commerce actions;
- R4 WooCommerce Presentation 2.0 with bounded catalog/card/product presentation presets, native Woo product/gallery/variation controls, responsive Cart/Checkout/Account presentation and public-capability/content-gated Woo Blocks styling while WooCommerce retains commerce truth;
- R5 Control Center + System Health with WordPress-native Logo/Menu ownership, the single `aznet_theme_settings` presentation schema, guarded save/reset/import/export, public/read-only capability diagnostics and update/theme-switch continuity evidence;
- R6 Performance + Release 2.0 technical closure: measured asset/performance evidence, reusable PR/exact-main verification, deterministic v1.x package infrastructure, bounded workflow retirement, atomic metadata promotion to `1.1.0`, and post-promotion exact-main/package verification;
- P2 Editorial Single-Post Hardening: native WordPress publication presentation, singular-Post scoped article assets and fail-soft native author handling without SEO/domain/query takeover;
- P3 Editorial Listing/Search Hardening: native featured image/date scan presentation and resilient long-title/excerpt styling while preserving WordPress main-query ownership.
- P4-A Homepage Composer + Law 01 Premium presentation with typed WordPress Content Map, exactly one native `the_content()` boundary and Theme-owned presentation only.
- P4-B D-025/D-026 Law Site Provisioning with mutation-free activation, proposal-only recommendations, WordPress-owned starter content/media and index-safe publication boundaries.
- P4-C D-027 Standalone Core independence with zero mandatory third-party runtime dependency, explicit Standalone Core vs Optional Integrations health presentation, and zero-plugin L1-L4/exact-package evidence.
- P4 real-pilot QA with fresh public route/viewport verification plus authenticated, read-only WordPress Admin/System Health verification on the canonical D-027-capable Theme; P1 legacy duplicate cleanup is closed with fresh post-delete verification and final P4 pilot sign-off is PASS at the tested Theme-owned scope.
- P5 final-candidate technical verification, owner-approved Git tag/GitHub Release publication, and owner-approved v1.1 production deployment disposition for `tamduchanoi.aznet.vn` are complete. Fresh read-only deployment verification run `35054176392` PASS at the tested Theme-owned/site-operations scope.
- v1.2.0 publication and owner-approved production deployment are complete at the verified Theme-owned/site-operations scope. Deployment run `35135759389` installed the exact published `1.2.0` package and assigned the existing WordPress-owned menu ID `15` to the new `header-utility` location; fresh independent read-only run `35136876330` reverified active Theme `1.2.0`, Standalone Core `ready`, retained logo/menu identity and a 28/28 public regression with zero Theme-owned blocker. Provider L5 remains separate and unproven.
- v1.3.0 technical integration, owner-approved publication and owner-approved production deployment are complete at their separately verified scopes. Deployment run `35222200660` installed the exact published `1.3.0` package on `tamduchanoi.aznet.vn`; fresh independent read-only run `35223053733` reverified active Theme `1.3.0`, Standalone Core `ready`, retained WordPress-owned menu/phone identity and a 28/28 public regression with zero Theme-owned blocker. Provider L5 remains separate and unproven.

## 2.1 Current post-release / pilot delta — 18/09/2026

- Live canonical GitHub `main` is `371e09035a23664cf38f52f70aa3189e5c10f2cf` after owner-approved PR #111. PR #110 merged earlier at `45073221ba1f243cc95692c002ddfb34ede7a1d6`. PR #109 remains draft/open/unmerged and is not canonical state.
- PR #110 closes the bounded Law 01 Quick Setup rerun/provenance-reuse gap: compatible Law provenance is reused instead of creating duplicate starter Post/media objects; Step 3 preview reflects reuse. Exact PR head `d5e58c4941bec144f05ec8de6a675eb86ebff74f` merged through #110.
- PR #111 adds bounded Law 01 homepage editorial presentation only, using WordPress-owned content and preserving the existing ownership boundary. Exact head `e9afe7a36b4f5d4f0a0f5f6e8c5e8f6ca4765648` merged through #111.
- The live pilot `tamduchanoi.aznet.vn` reports active AZnet Theme `1.3.0`. After owner preview/backup approval, a WPVibe draft was published and the previous live theme files were retained as `aznet-theme-wpvibe-backup`. The live Theme therefore has post-release local file changes; those bytes were behaviorally verified but were not proven byte-identical to canonical `main` or to the published `v1.3.0` package. Do not use the published package SHA as identity for the current live Theme files.
- Fresh live rerun evidence: Quick Setup reached Step 4/4 with `READY_WITH_WARNINGS`, `Created: 0 · Reused: 36`; a read-only database duplicate check returned zero duplicate starter roles for `law01-v1-2`. This proves the rerun/idempotency behavior at the tested runtime scope without claiming byte parity.
- Fresh live homepage evidence after content classification: five real published Posts render in `Mới cập nhật`; About excerpt renders; `blog_public=1`; Rank Math homepage meta description is present; fresh mobile Lighthouse returned Performance 98/100, Accessibility 100/100, Best Practices 100/100 and SEO 100/100.
- ConvertFlow F9/provider L5 certification is separately PASS at the exact tested compatibility scope recorded in `docs/evidence/F9_PROVIDER_L5_SOURCE_SYNC_20260918.md`: WordPress 6.9, PHP 8.1, AZnet Theme `v1.3.0` reference producer, ConvertFlow Core P5.244 + Pro P5.164, and Twenty Twenty-Five alternate-theme coverage. This does not promote the ConvertFlow released baseline or change Theme ownership.
- RootProfile remains an independent optional-integration boundary. On the pilot, public routes expose person-by-ID, organization and readiness reads, but no public/versioned organization-team collection/projection is available; `GET /rootprofile/v1/organization` currently returns `404 rootprofile_not_found`. Homepage Team authoritative rendering is therefore `BLOCKED` under issue #112. Theme must not infer membership from WordPress users/authors/posts/slugs/URLs or read RootProfile private storage.

X6 technical candidate provenance is distinct from current production state. Pre-promotion head `08e12fa7d4feee3930b2ce12d4713dc425d1aada` retained exact `1.1.0`; X6 run `35098577606` completed SUCCESS with integrated 32/32 browser verification, deterministic `aznet-theme-1.1.0.zip` SHA-256 `9c8b24416d1692fb2f4e67e1557ffd539dd00ddaeaa05bace97d6605875962c6`, 95 packaged PHP lint checks and artifact `10447571392` digest `sha256:71a909cb839b43b38efa74424f992063f0ef88f0b0bc1e26da7e3b9995694807`. The exact-`1.2.0` promotion contract was written RED before the version change. After explicit owner approval of the metadata-promotion gate, commit `682285c34e808bc4148702419fd857065a2a3bf1` promoted both declarations atomically; `60186106769819f8f108dafade816b2441d48e79` preserved metadata-file trailing newlines.

After `main` advanced through PR #88/#89, the X6 branch was synchronized by a normal two-parent merge rather than history rewrite. Synchronized candidate `c145f72cc7ac4783eb9f4a2f0b11c1d26e178445`, tree `3594fab0c89d015711e914325734646d1e7df159`, was `behind_by=0` against canonical `main@dddcee1cbd9a406f365ec57ece40097581e0bcf3` before source/evidence closure. Fresh X6 run `35126908185` completed SUCCESS: WordPress 6.9 zero-plugin runtime, integrated 32/32 Playwright/axe, deterministic exact-`1.2.0` package/source identity, 95 packaged PHP lint checks and exact-package switch-away/switch-back lifecycle continuity. `aznet-theme-1.2.0.zip` SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`; artifact `10459925538`, digest `sha256:c6b480168282e7dc9eea8f749d303012763cabf16213a6e69619c62f96f16e03`. All 19 observed same-head PR workflows completed SUCCESS. Durable evidence: `docs/evidence/X6_CROSS_SURFACE_RELEASE_CLOSURE_20260916.md`.

Owner-approved PR #87 merged exact final head `ff459b99a348d3004a3a4e11474b7c5ff19508f2` to canonical `main@c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`, tree `70c04929afe38e87d317bad3414157e9e2bd6f74`, using expected-head protection. Fresh `V1 Exact Main Verification` run `35129331899` completed SUCCESS on the exact merge SHA; artifacts `10460675823` (`sha256:f24f0b6fba2f8cbb3aa1099ce225616b584113c590e912ed171a4ef8f97995b3`) and `10460660919` (`sha256:7edf42b87e1d0d1c30743761bfa6292f83984af44c94e12b1f85333aba5ef709`) retain exact-main static and clean runtime/browser evidence. X6 push-to-main run `35129331927` also completed SUCCESS on the same merge SHA with WordPress 6.9 zero-plugin runtime, 32/32 integrated browser/axe, exact `1.2.0` metadata, 95 packaged PHP lint checks and lifecycle continuity. Exact-main package `aznet-theme-1.2.0.zip` SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`; artifact `10459889475`, digest `sha256:f537b5f6ed33a53dcad8041c3d8cc0aef32c5bb6dbd8c10c5102e76624128fb9`. X6 technical state is **TECHNICAL PASS**. Owner-approved publication run `35131383107` published annotated tag `v1.2.0` (tag object `62b701ed9e371b23d76b3259d459e76e824206c1`) dereferencing to this exact technical commit and GitHub Release `390148740`; release asset `568495178` (`aznet-theme-1.2.0.zip`) reports SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`. Publication evidence artifact `10461657247` has digest `sha256:57c1400145ac09559e9b8974045b31d6fff3b3349e4cf702480df50fffc5b59c`. v1.2.0 publication state is **PUBLICATION PASS**. Owner-approved production deployment subsequently completed through run `35135759389`; fresh independent read-only run `35136876330` verified active Theme `1.2.0`, Standalone Core `ready`, retained official logo/menu data, public `header-utility` phone presentation and the 28/28 public matrix with zero Theme-owned blocker. Deployment evidence: `docs/evidence/V1_2_PRODUCTION_DEPLOYMENT_20260917.md`. Provider L5 remains separate and unproven.

PR #34 final verified head is `b2e5cca1461233bcb1a0333c5aa51879c3264756`. Its tree `b716b89f04e45c2012f8e191c7d0edf605c9dd11` is identical to the merge commit tree on canonical `main@f8e1a95c903c3f246528368ae9878eba780539ff`, so the v1.0 technical merge introduced no conflict-resolution production delta.

R0 source reconciliation merged through PR #36 and established `main@fdb227b20d569bc3bcbf81ea570f94c3a7723e87` as the production base for R1.

R1 merged through PR #37 from verified head `a6b36615c9f6391cbe103844dee7659ff0dccb76` to canonical `main@80f28be8042cee0d2995784506ffb685b9eb36cb`. The R1 head tree and merge tree are identical at `880ce360d8c0c5003869d761e76833737d895fff`, so the merge introduced no conflict-resolution delta. Fresh exact-PR-head verification recorded 10/10 successful workflows before merge; no post-merge pull-request-triggered run exists on the merge SHA.

R1 current-state closure merged through PR #38 and established `main@b31f294d349deea62b07f2358c733c649c4c48a2` as the production base for R2.

R2 merged through PR #39 from verified head `6a40a44faaa8d8207c2a3b850e2bc6d65cd561ee` to canonical `main@d2e4ae567108b6a224b623febca7a676b7114715`. The R2 head and merge tree are identical at `3f9e934eaa4bdcb058ccf289d7ef1faa7eac1ee5`, so the merge introduced no conflict-resolution delta. Fresh exact-PR-head verification recorded 12/12 successful workflows before merge. No post-merge pull-request-triggered workflow run exists on the merge SHA, so no fresh L2-L4 claim is inferred for the merge commit beyond exact tree equivalence.

R2 current-state closure merged through PR #40 and established `main@29d05e501b6f992793df8f961003d29aa8636af2` as the production base for R3.

R3 merged through PR #41 from final evidence head `c4e6e623e4fc0113bdbc211fecf34b40528b7f77` to canonical `main@d8d5670d57dc62f7e2399d9e1b2eee70fd85ef9d`. The R3 head and merge tree are identical at `ebe79f1dc65ab58794592eab59a5bb2b9b3a1a00`, so the merge introduced no conflict-resolution production delta. Fresh exact-PR-head verification recorded 14/14 successful workflows before merge, including R3 static/browser, retained R1/R2, clean WordPress runtime, core browser/a11y/performance, lifecycle, release-candidate package and native Homepage regression.

R4 merged through PR #43 from final evidence head `bfd383da4fd1e7029d8c901ad807e9ca3c03d5ab` to canonical `main@0ddc6c799391d57db134f04449effdf511d41d1f`. Head and merge share tree `e66c6ebbcc1de7d695ffa67172928d0f0580e443`, so merge introduced no conflict-resolution delta. The verified functional/test head `675a4f0a07b7c6c4796ad359a4b13f9c2d94f74a` completed 16/16 workflows successfully; the final R4 evidence-only head retained fresh 16/16 success. Detailed evidence is `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`.

R5 merged through PR #44 from final evidence head `fed7429853ce5f6a209a0bf763a4c72f5ed1d376` to canonical merge `main@6c69def2ff4ac7adb1221de09aec057b63b1adf6`. Head and merge share tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`, so merge introduced no conflict-resolution delta. The verified functional/test head `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` completed 18/18 workflows successfully, including authenticated clean-WP/Woo-present Control Center, update continuity, theme-switch continuity and retained R1-R4/core regressions. The final R5 evidence-only head differs from that verified functional/test head only by `docs/evidence/R5_CONTROL_CENTER_L4.md`. Detailed evidence is recorded there.

Immediately after the approved R5 merge, an accidental source-state sentinel file was committed to `main` at `e924e964c719149cf285fad8243331c0a27ab947` and then removed at `484e896cace06d684d29a3f93e3cce85e84a9f80`. Direct compare `6c69def2... -> 484e896...` returns no changed files, so those two no-op history commits did not alter implementation bytes or any PASS claim.

R6 infrastructure merged through PR #46 to `main@a3dc550bd748acab59e1df2384f8e2ada55541e9`. It established measured clean/Woo route and asset baselines, reusable v1 PR/exact-main verification and deterministic release-candidate packaging while retaining the ConvertFlow asset-gating item as `BLOCKED_EXTERNAL_CONTRACT` rather than using private detection.

Post-merge workflow retirement merged through PR #47 to `main@27537e520ba15afe771cea86080abd99a7276a35`, removing only superseded G workflows after replacement coverage and retaining unmatched lifecycle/version gates.

Owner-approved metadata promotion merged through PR #48 to `main@4673202f67068294fe92d49d1ed66e371d0ecbb3`. `style.css` Theme Version and `AZNET_THEME_VERSION` were promoted atomically to exact `1.1.0` after RED -> GREEN version and byte-preservation gates. Fresh exact-main verification succeeded on those promoted bytes. Manual `V1 Release Candidate Package` run `34187975628` also succeeded on that exact main SHA. The deterministic R6 candidate was `aznet-theme-1.1.0.zip`, contained exactly one top-level `aznet-theme/` directory and had SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.

PR #49 merged evidence/audit and the law-site production-readiness plan to `main@38843b08bdc5a3d3dccbc2447029879701168db3`; it changed documentation only and therefore did not invalidate the then-verified R6 package bytes.

P2 Editorial Single-Post Hardening merged through PR #51 from final verified head `dec498b997aec6bc8b08cf91f3e09c42611bbd66` to `main@a863da861b6a299920568b2b0c9ca57924727431`. It added bounded native-Post editorial presentation and singular-Post scoped article CSS. During verification, regressions caught an empty native-author link and a Woo/CPT scope leak; both were reduced to Theme-owned root causes and fixed before merge. Fresh exact-main verification succeeded after merge.

P3 Editorial Listing/Search Hardening merged through PR #52 from final verified head `d78091900451176c3815b23d1485036e784bdde9` to `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`. Head and merge share tree `8fc309ea8e01bfe727da943754c98164b3f92a58`. Fresh `V1 Exact Main Verification` run `34195248467` completed successfully on that exact merge SHA, covering reusable static/contracts plus clean WordPress 6.9 runtime/browser/a11y.

Because P2/P3 changed production bytes after the R6 package, SHA-256 `000735...` remains **historical R6 candidate evidence**. P5 later rebuilt and verified the final post-hardening package as `aznet-theme-1.1.0.zip`, SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`, which is now the published v1.1.0 release asset.

Owner-approved P5 publication completed on 16/09/2026 through GitHub Actions run `35052694111`. Annotated tag `v1.1.0` (tag object `3c3e07fd0ba64037f7ec1d79fb6e6276308786a3`) dereferences to canonical `main@7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`. GitHub Release `389622362`, title `AZnet Theme 1.1.0`, is published (`draft=false`, `prerelease=false`) with release asset `aznet-theme-1.1.0.zip` (`asset 567100593`, 170944 bytes) and GitHub-reported digest `sha256:72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`. Publication workflow evidence artifact `10428918492` has digest `sha256:544895a168e12b7260e086b14f85ecfb3ae4505fcc600feff7d27bbbd6f88814`. The temporary publication workflow was removed after successful publication and its ops branch has zero net file delta from the tagged canonical commit. This publication does not authorize or imply production deployment or optional-provider L5 certification.

Owner-approved production deployment disposition for `tamduchanoi.aznet.vn` completed on 16/09/2026 without re-uploading identical package bytes. Fresh read-only run `35054176392` reverified release identity, Theme inventory, authenticated System Health and the retained public route/viewport matrix. The site reports active AZnet Theme `1.1.0`, `NO_DUPLICATES`, D-027 current System Health shape and Standalone Core `ready`; the public matrix passed 28/28 checks. Evidence artifact `10430017585` (`sha256:c2066532e341f6243f466f5ed885822e5428d6ec344a0f74af20b9247921785b`). This closes P5 deployment at the tested Theme-owned/site-operations scope without inferring provider L5 certification or live-filesystem checksum equivalence. Evidence: `docs/evidence/P5_PRODUCTION_DEPLOYMENT_20260916.md`.

No source statement above transfers domain ownership to the Theme.

### Homepage Law 01 + Law Site Provisioning — canonical main integration

The D-024/D-025/D-026 stack is canonical Theme implementation after owner-approved PR #58 merge. The previously recorded stacked `1.1.1` package remains historical candidate evidence only; the integration/release metadata baseline is the source-approved Theme `1.1.0`.

PR #58 merged to canonical `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` with tree `28c30353e9f01bc51f4474347069738d410b5256`. The merge tree is identical to final PR head `992cf0274a9bdec93131fbb69401516b5c602ed8`, so no conflict-resolution byte delta was introduced by the canonical merge.

Fresh D-022 `V1 Exact Main Verification` run `34414878827` completed SUCCESS on exact `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`. `static-contracts` passed the reusable v1 core/static contract gate on PHP 8.1; `clean-runtime-browser` passed WordPress 6.9 clean runtime routes plus core browser/a11y verification. Exact-main evidence artifacts: static `10128678294` and runtime/browser `10128707347`.

Pre-merge integration package evidence remains retained: `Law Site Provisioning Candidate Package` run `34413505990`, Theme `1.1.0`, 121 packaged files, SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`, artifact `10128165973`; browser/runtime run `34413506017`, artifact `10128198277`.

The separately known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking and is not a blanket PHP-log-clean claim. No source statement here authorizes a Git tag, GitHub Release, L5 provider certification, destructive pilot cleanup or final production deployment. Detailed integration evidence remains `docs/evidence/HOMEPAGE_LAW01_PROVISIONING_PR58_INTEGRATION.md`.

### D-027 Standalone Core — canonical main closure

D-027 source ratification merged through PR #63 to `main@5d6dc56a756028d696a269ed75dd762daf4b83f2`. Owner-approved implementation PR #64 then merged final branch head `4b38f792d7de575bc08733748dac0399372e18c2` to canonical `main@16563bdc88f172a1d7737b92293c7958670aac96`. The final branch head and merge commit share tree `d2c4128f2b860bfd59c5efa11565dba38a34458e`, so the merge introduced no conflict-resolution byte delta.

Fresh `V1 Exact Main Verification` run `35042767873` completed SUCCESS on exact `main@16563bdc88f172a1d7737b92293c7958670aac96`: reusable static/contracts passed on PHP 8.1 and clean WordPress 6.9 runtime/browser/a11y passed. Exact-main artifacts are static `10425656989` and runtime/browser `10426026827`.

Pre-merge final-head D-027 evidence remains retained separately: Offline `35042040619`, L3 `35042040512`, L4 `35042040532`, and exact-package run `35042040620` all succeeded on `4b38f792d7de575bc08733748dac0399372e18c2`. The exact D-027 candidate package SHA-256 is `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`; this proves the standalone package path but does not itself publish a release.

D-027 does not authorize a Git tag, GitHub Release, final production deployment, destructive pilot cleanup or optional-provider L5 certification. P4 public-pilot QA first completed on GitHub Actions at functional head `758392b03227eb446627cc4669b0b0ddf56ed71e`. After the owner replaced historical pilot Theme `1.1.1` bits with the exact verified D-027 `1.1.0` package, authenticated read-only run `35047440842` completed SUCCESS on the real pilot and reported WordPress `7.1`, PHP `8.4.25`, current D-027 System Health shape and Standalone Core `ready`; artifact `10427876863`, digest `sha256:96d2943661e27ef05ce5b16940a792fbd4727d635849a8f5e9e8589b03d2abe9`. The public matrix was then rerun after replacement and again passed 28/28 route/viewport checks with zero Theme-owned blocker; fresh artifact `10427713118`, digest `sha256:c58d0efebe9fdeea14c2d9d1ba3fa8a4439f2bafffc1dc180cf3b1d1554cbadb`. Authenticated QA harness/evidence merged through owner-approved PR #67 to canonical `main@bf45a315e93fa22c441c81377f98ff34901d7ca5`, tree `c7db15f585e181de3eb58f96ca388878964fe64a`. Fresh exact-main run `35048609753` then completed SUCCESS; artifacts: static `10427853326`, clean runtime/browser `10427687863`. P4 public + authenticated QA is therefore PASS at the tested Theme-owned scope. P1 duplicate-theme cleanup remains a separate destructive site-operations gate before final pilot sign-off, and RootProfile/ConvertFlow optional-provider L5 certification is not inferred. Detailed evidence is `docs/evidence/P4_PUBLIC_PILOT_QA_20260916.md` plus `docs/evidence/P4_AUTHENTICATED_PILOT_QA_20260916.md`.

## 3. Current release-path classification

Under AZT-05 v1.0 and D-016:

- WordPress-clean core Theme readiness is release-critical.
- v1.1.0 and v1.2.0 publication/deployment remain retained historical PASS; current published release and verified production Theme on `tamduchanoi.aznet.vn` are `1.3.0` at their respective verified scopes.
- v1.2 X1-X5 functional closure and X6 technical/publication/deployment evidence are retained.
- v1.3 Y1-Y5 technical closure, exact-main/package verification, publication and owner-approved production deployment are PASS at their separately verified scopes.
- Provider L5 is outside v1.3 Core and is not inferred from technical/publication/deployment PASS.
- RootProfile E5-C/E5-D remains an optional provider certification/takeover track, not a core-v1.x blocker.
- ConvertFlow F6/F7 actual-package integration evidence remains retained; F8 integrated compatibility remains BLOCKED because provider body output introduces a second document-level `<main>` inside Theme-owned `main#main`.
- R6 ConvertFlow projection asset optimization remains `BLOCKED_EXTERNAL_CONTRACT` until a documented public/versioned Theme-consumable capability exists; safe global bridge behavior is retained.
- External compatibility defects must not be bypassed with private APIs, authoritative heuristics or copied domain logic.

## 4. Registered source artifacts

| ID | Artifact | Role | Inventory status |
| --- | --- | --- | --- |
| S-001 | `convertflow-0.1.0-p5.223-homepage-no-woo-mode.zip` | ConvertFlow historical evidence/provenance candidate | frozen/hash/extract/classify PASS |
| S-002 | `convertflow-pro-0.1.0-alpha.118-p5.162-r1-platform-compatible.zip` | ConvertFlow Pro compatibility/presentation evidence | frozen/hash/extract/classify PASS |
| S-003 | `RootProfile-0.1.0-alpha.99.zip` | RootProfile profile/integration evidence | frozen/hash/extract/classify PASS |
| R-001 | AZnet Theme Code Reuse Handoff from ConvertFlow v1.0 | technical handoff/reference | reviewed |

Historical build labels do not establish file ownership by themselves.

## 5. Provenance inventory baseline

The accepted source-bound inventory contains **817/817 classified candidates**:

- Domain: 454
- Mixed: 203
- Adapter: 97
- Presentation: 63

Reuse classification:

- Keep: 602
- Split: 203
- Port: 9
- Reference: 3

The 9/9 pre-port visual baselines for Header, Footer and Generic content shell remain accepted unless a concrete invalidation is found.

## 6. Classification rules

| Code type | Canonical owner/action |
| --- | --- |
| Presentation-only | Theme may port/move with provenance + regression evidence |
| ConvertFlow Journey/Product experience presentation | Keep with ConvertFlow; Theme may expose semantic tokens only |
| Domain/behavior | Keep with domain owner; do not transfer |
| Mixed presentation + behavior | Split before ownership changes |
| Consumer adapter/compatibility | Keep minimal at the public integration boundary |
| Historical duplicate | Retire only after canonical destination + compatibility/fallback + regression + rollback PASS |

## 7. Extraction and migration invariants

1. Freeze and hash raw evidence before extraction.
2. Never edit registered raw snapshots in place.
3. Inventory origin path/SHA before deciding reuse.
4. Classify from code + call/use sites, not filenames alone.
5. Mixed code must be split before presentation ownership moves.
6. Prefer history-preserving migration when a canonical repository exists.
7. Theme integration consumes public/versioned contracts; direct storage reads are forbidden.
8. Retire historical code only after destination, regression, compatibility/fallback and rollback are proven.

## 8. Evidence depth discipline

- L0/L1/L2 evidence cannot be promoted to runtime/browser/integration/release claims.
- Runtime/browser evidence is retained unless a concrete production-byte or contract invalidation is found.
- Package/release claims are byte-specific and must be refreshed on the final candidate bytes of the release being promoted.
- PRs, Actions runs, commit hashes and package hashes live in GitHub evidence; they do not require AZT-03 version bumps unless the represented canonical baseline/provenance statement changes.
- A Theme version string does not prove publication. Tag/Release existence and target SHA are live GitHub facts and must be checked separately.
- Pilot evidence on WordPress `7.1` / PHP `8.4.24` is current-stack evidence only and does not replace the support-floor `6.9+` / `8.1+` release matrix.

## 9. Canonical evidence locations

Use GitHub in this order for live state:

1. canonical `main`;
2. relevant branch/PR;
3. fresh CI/runtime/browser/integration evidence;
4. release/package artifacts when byte identity matters;
5. `docs/evidence/*` checkpoints.

## 10. Rollback rule

Every release candidate must have a concrete previous package/commit or restoration path. Cleanup cannot delete provenance required to reproduce or reverse an ownership-safe migration.

## 11. Exact next

**NEXT — PR #147 is the current owner-approved Law 01 screenshot-reference presentation candidate. Exact production head `aacd6fe4abc4d5f3d49b6bfd85570df66688db46` completed 18/18 triggered workflows SUCCESS. Canonical merge remains the next approval gate. No new package/tag/GitHub Release/production deployment is authorized by this visual slice; RootProfile-backed Team integration remains `BLOCKED_EXTERNAL_CONTRACT` at issue #112.**

### AZnet Theme 1.3.6 client-delivery candidate — 18/09/2026

The owner approved creation of the newest installable Theme package for client delivery after the Law 01 visual-parity sequence closed. PR #143 promotes Theme metadata from `1.3.5` to `1.3.6` so the later visual-parity bytes are not shipped under the earlier 1.3.5 package identity.

RED head `120cd35e7d245f6a7e564f544df76d987fec2ddd` failed the intended Y5 metadata contract while production still declared 1.3.5. Minimal GREEN changed only the two Theme version declarations. Exact production head `7db436b316ef5a9f9264fa9dd71f6cfad4d7ad80` then completed **14/14 triggered workflows SUCCESS, 0 failure**.

Y5 built `aznet-theme-1.3.6.zip` twice with byte identity, 145 production files, 108/108 packaged PHP lint PASS and SHA-256 `25271cd4c68f0bc88e236f084acb73d7661a618d1ae1d737f0f2a5202307a84a`. Exact-package lifecycle verified clean WordPress 6.9 installation with zero active third-party plugins plus switch-away/switch-back continuity. Run `35353852165`; promoted-package artifact `10550649207`, digest `sha256:7efe3e575a46950e85abd703bad87e05daac32bd2346265fa2e148569de48cfc`.

This is a technically verified client-delivery candidate. Canonical `main` remains Theme metadata 1.3.5 until PR #143 is merged. No tag/GitHub Release or production deployment is claimed. Evidence: `docs/evidence/V1_3_6_CLIENT_DELIVERY_20260918.md`.


### Law 01 screenshot-reference parity candidate — 18/09/2026

Canonical `main@9781d29a9d1ba32a643ffe3cfe9d47c8c4ba4c1c` is Theme metadata `1.3.6` after PR #143 and the intermediate PR #145 Hero-width correction. The owner then clarified that the supplied Tâm Đức - Hà Nội homepage screenshot is the canonical visual reference for the next presentation slice. PR #146 was closed unmerged as superseded; PR #147 carries the replacement bounded presentation work.

The accepted candidate keeps full-width section surfaces but restores one shared `96rem` inner shell across Header, Hero, Trust, Services, Profile, Latest and Footer. The Hero remains 48/52 on wide desktop. About and Team now share a 48/52 Profile band, About is text-led for this reference variant, Team retains up to four WordPress-owned direct child Page portrait slots with CTA below, Services remains six compact cards, Latest remains three WordPress-native Posts, and Header/Footer can render WordPress logo + site-title lockups. The Hero may reuse the WordPress-owned `header-utility` menu for phone/hotline links. No fabricated business statistics are introduced.

Exact production head `aacd6fe4abc4d5f3d49b6bfd85570df66688db46` completed **18/18 triggered workflows SUCCESS, 0 failure**. Homepage Composer Law 01 Browser Quality run `35363025279` passed the 1920/1440/1024/390/320 matrix; artifact `10555328476`, digest `sha256:4c0af3b17d8d38768f994f2dacd3dbe20f5da5b693ec28c331df7fc632d520ba`. The 1440 screenshot was manually inspected against the owner-supplied reference. Evidence: `docs/evidence/LAW01_REFERENCE_PARITY_FINAL_20260918.md`.

RootProfile authoritative Team membership remains blocked at issue #112. The WordPress Page/direct-child fixture remains presentation input only and does not become authoritative identity or membership.

### Law 01 About/Profile + Latest visual-parity closure — 18/09/2026

Owner-approved PR #141 merged final verified head `0a2826dfc52343f187dc6198d61d83a6608b62d4` to canonical `main@afbef3d0adc39e4b1595cf2f3506f58bb567bcff`.

The slice is presentation-only: About uses a dedicated 44/56 desktop editorial copy/media band with responsive stacking; Team follows as a distinct presentation band over the existing WordPress-owned mapped Team Page/direct published child Page fixtures; Latest remains three WordPress-native posts with equal-height cards and 3:2 media. No authoritative Team identity/membership is inferred.

The final PR head completed **25/25 workflows SUCCESS, 0 failure**, including V1 Core PR CI, Homepage Composer Law 01 Browser Quality, D-027 L3/L4/Exact Package/retained regression, X6, R6 and retained Y-series/browser gates. A Navy Team contrast regression exposed by D-027 L4 was corrected at the CSS presentation layer before final verification.

No fresh post-merge workflow run was emitted for merge SHA `afbef3d...` at the time of closure, so no independent exact-main rerun is claimed. Evidence: `docs/evidence/LAW01_PROFILE_LATEST_VISUAL_PARITY_20260918.md`.

### P1 pilot identity cleanup and final P4 sign-off

Owner-approved PR #69 merged the read-only identity harness/evidence to canonical `main@9ed7a586b06b4c6dd91e66c51b049caa86e0485b`, tree `313f6744066a1d94033d4b2868343a471fbc6a70`. Fresh `V1 Exact Main Verification` run `35049959237` completed SUCCESS on that exact merge SHA.

The pre-delete P1 inventory proved exactly one inactive legacy AZnet Theme candidate, folder `aznet-theme-release-v1.0.0` version `1.0.0`, distinct from active `aznet-theme` version `1.1.0`, with zero ambiguous AZnet identities. After the owner confirmed backup availability and explicitly approved deletion of only that inactive legacy identity through WordPress core UI, post-delete run `35050519817` completed SUCCESS. Its P1 artifact `10428552132` (`sha256:2f7598a663c1db1cca1952ce9afa6616b9cb53bf00c594094742f023840bba03`) reports `NO_DUPLICATES`, active `aznet-theme` `1.1.0`, zero inactive AZnet candidates, zero ambiguous identities and Standalone Core `ready`. The same run's public regression artifact `10428429083` (`sha256:a600f23ec48f3c1948a185d37ce47e63b2153f0f18fbc6d0a7a5f41b02d1704f`) passed the existing 28 route/viewport matrix with no Theme-owned blocker.

P1 is therefore PASS and P4 final pilot sign-off is PASS at the tested Theme-owned scope. This does not create provider L5 certification and does not authorize Git tag, GitHub Release or final production deployment. Detailed evidence: `docs/evidence/P1_POST_DELETE_CLOSURE_20260916.md`.

### P5 final-candidate technical verification

Owner-approved P1/P4 closure PR #70 merged to canonical `main@f0b2d581b16a663e4b422234e6ac15568bcc7985`, tree `f21884f5989239858261721c5002c5158d6d9be0`. P5 run `35051428372` explicitly checked out and asserted those exact canonical bytes before both verification jobs.

The `exact-main` job completed SUCCESS on WordPress `6.9` / PHP `8.1` with zero active third-party plugins. Reusable v1 core verification passed; clean runtime route smoke passed; the core browser/a11y harness passed 18/18 route/viewport cases with maximum overflow `0`, blocking axe findings `0`, failed subresources `0`, console errors `0`, page errors `0` and one main landmark per case. Exact-main artifact `10429087315`, digest `sha256:f95f6281d1d5daf729896cc24d42b786e7cb030ed9588b80600ce15287c7cb4d`.

The `final-package` job independently built `aznet-theme-1.1.0.zip` twice with byte identity, verified one canonical top-level `aznet-theme/` directory, exact-matched 121 production files against canonical source, linted 93 packaged PHP files, installed the exact ZIP on clean WordPress `6.9` with zero active third-party plugins and passed the 18/18 browser/a11y matrix. WordPress-owned Page/Post/Category/Menu/meta continuity survived switching to bundled `twentytwentyfive`, switching back to `aznet-theme` restored active Theme `1.1.0`, and the 18/18 browser matrix passed again. Final ZIP SHA-256: `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`. Package artifact `10428893463`, digest `sha256:12e48cd836e4ab1a4e347749cc275ff555176930fad3987b702fc0d4192df61e`. Detailed evidence: `docs/evidence/P5_FINAL_CANDIDATE_VERIFICATION_20260916.md`.

P5 technical state is therefore **TECHNICAL CANDIDATE PASS / PUBLICATION GATED** at its historical pre-publication checkpoint. Later owner-approved publication and production deployment closed v1.1; that later closure does not authorize later release lines by itself.

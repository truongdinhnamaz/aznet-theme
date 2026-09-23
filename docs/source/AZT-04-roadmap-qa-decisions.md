# AZT-04 — Roadmap, QA và Decision Log

**Version:** v0.91
**Status:** Working Source  
**Date:** 23/09/2026

## 1. Purpose

AZT-04 owns milestone sequencing, QA layers, release gates, accepted decisions and open questions for AZnet Theme. It does not own external domain semantics or rewrite the product constitution.

AZT-05 v1.0 governs the v1.x release constitution: **WordPress-clean core Theme readiness is release-critical; optional provider compatibility is a separate certification track unless a future approved source explicitly makes it mandatory.**

## 2. Current roadmap state

| ID | Workstream | State | Current meaning |
| --- | --- | --- | --- |
| 0 | Source Freeze & Provenance | PASS | Raw freeze/hash/extraction, 817/817 classification and pre-port baselines accepted |
| A | Theme Foundation | PASS | Hybrid PHP + `theme.json`, WordPress-clean foundation |
| B | Semantic Design Tokens | PASS | Public `--aznet-theme-*` tokens + `theme.json` mapping |
| C | Header / Footer | PASS | Theme-owned presentation with runtime/browser/a11y evidence |
| D | Generic Templates | PASS / retained | Page/Post/Archive/Search/404 technical closure plus P2/P3 editorial hardening and P4 real-pilot quality evidence are retained |
| W | WooCommerce presentation shell | PASS / retained | W1-W9 + R4; WooCommerce retains commerce truth |
| E | RootProfile Profile / Contact | OPTIONAL COMPAT ACTIVE / TEAM PROJECTION BLOCKED | E0-E4/E5-B retained PASS; E5-C remains external-blocked and E5-D takeover locked. Pilot evidence confirms no public/versioned organization-team projection and no organization profile on the target site; issue #112 records the blocker. Non-blocking for core under D-016. |
| F | Homepage | CORE PASS / LAW 01 FINAL HOMEPAGE PASS / v1.3.20 PUBLISHED / PROD DEPLOY BLOCKED_EXTERNAL_ACCESS / F9 PROVIDER L5 RETAINED | Owner final approval on 20/09/2026 closes the Law 01 Homepage presentation at the reviewed scope. PR #193 premium refinement merged to `main@0a66d5b5e30cb91c6f61d593ca950e43eec6ee10`; release PR #194 promoted `1.3.20` to canonical `main@a81067b1f1804783a35e8113ebf6859c7fcc38fa`. Exact-main V1 `35509805662` + X6 `35509805668` PASS; public `v1.3.20` Release `392420684` carries exact package SHA-256 `96ff01960e17d1da7167c5c3e296abbdabad0e5d51603f0f73ca4513ac880c00`. Burgundy Homepage preserves WordPress/public-safe source ownership, fail-soft mappings and the approved premium sections; no provider/private-storage/domain takeover. No live `v1.3.20` deployment is claimed. RootProfile Team membership remains independently blocked under issue #112. |
| D-030 | Homepage Hero Library | PASS / MERGED / PUBLISHED 1.3.12 | Base D-030 PR #156 remains retained; owner-approved corrective PR #159 removed stale Hero title width caps and completed the single WordPress-owned Hero authoring surface. Final head `4b8fc7c78fb17e32fbc9c4fb9399bdeaf3f98744` completed 28/28 workflows SUCCESS and merged to `main@7c73f7e050e95c23c5c968b2365b643753431246` with zero file delta. Exact-main V1 `35434502224` + X6 `35434502232` PASS. Owner-approved `v1.3.12` publication is PASS; production deployment remains separately gated. |
| D-031 | Burgundy Law 01 Homepage Composition | PASS / MERGED / PUBLISHED 1.3.16 | Owner-approved PR #171 locks the Burgundy presentation sequence around the existing single native Front Page content boundary: Hero -> Trust -> Services -> About/Team -> native content -> Process -> FAQ -> Latest legal knowledge -> Contact CTA -> Footer. Mapped sources fail soft; Topics/Analysis/News remain excluded; no provider/domain semantics or private storage move into Theme. Exact-main V1 `35477748238` + X6 `35477748216` PASS; publication run `35478013102` PASS. |
| D-032 | Burgundy Law 01 Homepage Expansion | PASS / MERGED / FINAL OWNER APPROVED / PUBLISHED 1.3.20 | D-032 expansion was implemented and merged, then premium refinement PR #193 raised Analysis/Legal News/Process/FAQ/CTA presentation without changing source ownership. Owner final approval on 20/09/2026 marks the Homepage result PASS. Exact-main V1/X6 on `main@a81067b1f1804783a35e8113ebf6859c7fcc38fa` PASS and `v1.3.20` is published. WordPress/public-safe sources retain content/data ownership; mappings fail soft; Footer remains independent; no heuristic source discovery, private storage read, provider takeover or domain semantics are introduced. |
| G | Core v1.0 Cleanup / Release | TECHNICAL PASS | G0-G8 production/release-candidate closure merged; publication is a separate live GitHub state |
| R0 | v1.0 -> v1.1 Source Reconciliation | PASS | Source-only reconciliation merged through PR #36; v1.1 product/architecture ratified |
| R1 | Design System 2.0 | PASS | PR #37 merged; settings/tokens + Default/Editorial/Commerce outcomes + editor/frontend parity L1-L4 |
| R2 | Native Pattern Library | PASS | PR #39 merged; 18 portable patterns; public Woo-block gating; L1-L4 + theme-switch portability evidence |
| R3 | Header System 2.0 | PASS | PR #41 merged; four bounded presets, three sticky modes, reusable primitives, mobile progressive enhancement and L1-L4 browser/a11y evidence |
| R4 | WooCommerce Presentation 2.0 | PASS | PR #43 merged; catalog/card/product/cart/checkout/account presentation, clean-WP fail-soft and Woo-present L1-L4 evidence |
| R5 | Control Center + System Health | PASS | PR #44 merged; bounded presentation admin, System Health, authenticated browser/a11y and update/theme-switch continuity evidence |
| R6 | Performance + Release 2.0 | PASS / TECHNICAL CANDIDATE | PR #46-#48 technical candidate evidence retained; publication and production deployment were later completed under P5, while R6 remains technical provenance only |
| P1 | Pilot identity cleanup | PASS | Owner-approved inactive legacy `aznet-theme-release-v1.0.0` deletion completed through WordPress core UI; the post-delete checkpoint proved only intended active `aznet-theme` `1.1.0` remained before the later v1.2 deployment |
| P2 | Editorial single-post hardening | PASS | PR #51 merged; native Post editorial presentation + singular-post scoped assets + retained regressions |
| P3 | Editorial archive/search hardening | PASS | PR #52 merged; native thumbnail/date scan presentation + resilient long-title/excerpt styling; WordPress main query retained |
| P4 | Real law-site pilot QA | PASS / PILOT SIGN-OFF CLOSED | Public + authenticated QA retained; P1 cleanup is closed and fresh post-delete inventory/System Health plus 28-check public regression PASS with no Theme-owned blocker |
| P4-A | Homepage Composer + Law 01 | PASS / MERGED | Premium Law01 + Composer merged through PR #58; exact-main static/runtime/browser core verification PASS |
| P4-B | Law Site Provisioning v1.1 | PASS / MERGED | D-026 smart setup merged through PR #58 with Theme metadata `1.1.0`; deterministic package/browser evidence retained and exact-main verification PASS |
| P4-C | Standalone Core independence | PASS / MERGED | D-027 source + implementation merged through PR #63/#64; zero-plugin L1-L4, exact-package and exact-main gates PASS; optional integrations remain additive |
| P5 | Publication & production deployment | PASS | Tag `v1.1.0` + GitHub Release publication and owner-approved production deployment disposition for `tamduchanoi.aznet.vn` are complete; fresh read-only run `35054176392` PASS at the tested Theme-owned/site-operations scope |
| X | v1.2 WordPress Experience Completion | PUBLICATION + PRODUCTION DEPLOYMENT PASS | X1-X5 retained; PR #87 technical integration and exact-main V1 + X6 L1-L4/L6 PASS; owner-approved `v1.2.0` publication PASS; owner-approved production deployment run `35135759389` plus fresh independent read-only run `35136876330` PASS; no provider L5 expansion |
| Y | v1.3 Client Delivery System | v1.3.23 TECHNICAL PASS / PUBLICATION PASS | Current canonical implementation is `main@c3d991199bb5c95dec3bac6e0ae06072c8a249f2`, Theme metadata `1.3.23`. PR #202 completed RED -> GREEN promotion with all 14 final-head workflows SUCCESS. Exact-main V1 `35521890888` and X6 `35521890901` PASS; deterministic `aznet-theme-1.3.23.zip` has 159 production files, 113 packaged PHP files verified and SHA-256 `7129b1506131c6b9e413bcba3087475fce75f52df7b71593c5f2804e90c4f005`. Publication run `35522052972` PASS; annotated `v1.3.23` and Release `392490797` carry the exact verified package. Under D-033, repository release verification/publication does not depend on WPVibe or another production-site connector. |
| U | Historical Control Center stream | SUPERSEDED / REFERENCE ONLY | Historical PR/evidence may inform provenance only; no parallel admin/settings architecture |

Live `main` HEAD is resolved from GitHub at execution time. Stable release anchor `v1.1.0` points to `7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`, tree `740406a02ea77a4cb6fdd8f2ee98b7b88f909560`. Publication source closure PR #72 merged at `main@15f25e4f6d8e64c588866405629b793a85ee0323`; restored post-noop checkpoint `main@e7e5a9c2d2a867f631b029f3f77a675e32fad573` has identical tree `d50c9f04465f7f6990ac3747ee55a848e3187beb` and zero file delta from PR #72. P5 technical run `35051428372` verified final package bytes, publication run `35052694111` published the matching tag/Release, and production read-only run `35054176392` closed deployment at the tested Theme-owned/site-operations scope.

Canonical X6 technical integration is `main@c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`, tree `70c04929afe38e87d317bad3414157e9e2bd6f74`, after owner-approved PR #87. Fresh `V1 Exact Main Verification` run `35129331899` and X6 push-to-main run `35129331927` both completed SUCCESS on that exact merge SHA. Owner-approved publication run `35131383107` published annotated tag `v1.2.0` and GitHub Release `390148740` with exact package SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`. Owner-approved production deployment then completed through run `35135759389`; fresh independent read-only verification run `35136876330` reverified active Theme `1.2.0`, Standalone Core `ready`, retained WordPress-owned logo/menu identity and the public regression matrix. Evidence: `docs/evidence/V1_2_PRODUCTION_DEPLOYMENT_20260917.md`.

Canonical v1.3 technical source is `main@afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`, tree `020db34ca6f360d5af12e6f15abf4c9ba51179f0`. Fresh exact-main V1 run `35206627102` and X6 release-path run `35206626992` completed SUCCESS; owner-approved publication run `35211254633` published annotated `v1.3.0` and GitHub Release `390623960` with exact package SHA-256 `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`. Owner-approved production deployment run `35222200660` installed that published release on `tamduchanoi.aznet.vn`; independent read-only run `35223053733` then verified active Theme `1.3.0`, Standalone Core `ready`, retained WordPress-owned menu/phone identity and the 28/28 public regression. Evidence: `docs/evidence/V1_3_PRODUCTION_DEPLOYMENT_20260917.md`.

The earlier deterministic R6 candidate SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b` remains historical release-provenance evidence only. P5 independently rebuilt and verified the final post-hardening production bytes as `aznet-theme-1.1.0.zip`, SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`; owner-approved publication and production deployment disposition are now complete, with fresh deployment evidence in run `35054176392`.

R4 merged through PR #43 to `main@0ddc6c799391d57db134f04449effdf511d41d1f`; final head and merge share tree `e66c6ebbcc1de7d695ffa67172928d0f0580e443`. R4 functional/test head `675a4f0a07b7c6c4796ad359a4b13f9c2d94f74a` completed 16/16 workflows successfully and the final evidence-only head retained fresh 16/16 success.

R5 merged through PR #44 to `main@6c69def2ff4ac7adb1221de09aec057b63b1adf6`; final head and merge share tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`. R5 functional/test head `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` completed 18/18 workflows successfully, including R5 authenticated browser/continuity plus all retained R1-R4/core regressions.

R6 infrastructure merged through PR #46 to `main@a3dc550bd748acab59e1df2384f8e2ada55541e9`; covered legacy G workflows were retired through PR #47 to `main@27537e520ba15afe771cea86080abd99a7276a35`; owner-approved atomic metadata promotion merged through PR #48 to `main@4673202f67068294fe92d49d1ed66e371d0ecbb3`. Fresh exact-main verification succeeded on promoted bytes. Manual `V1 Release Candidate Package` run `34187975628` succeeded on that exact main SHA with one top-level `aznet-theme/` directory and historical candidate SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.

P2 merged through PR #51 from final verified head `dec498b997aec6bc8b08cf91f3e09c42611bbd66` to `main@a863da861b6a299920568b2b0c9ca57924727431`. Verification caught and fixed two Theme-owned defects before merge: an empty native-author link and a Woo/CPT scope leak. The final bounded behavior applies Editorial Article presentation only to native Post and fails soft when native author data is unavailable.

P3 merged through PR #52 from final verified head `d78091900451176c3815b23d1485036e784bdde9` to `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`. Head and merge share tree `8fc309ea8e01bfe727da943754c98164b3f92a58`. Fresh exact-main verification succeeded after merge.

A GitHub release check on 08/09/2026 historically established that publication had not yet occurred at that checkpoint. That historical `PUBLICATION_PENDING` state was superseded on 16/09/2026 by owner-approved publication run `35052694111`; final production deployment was then separately owner-approved and verified by read-only run `35054176392`.

## 2.1 Current checkpoint — 18/09/2026

- Canonical GitHub `main` is `a66ef1639c299a06e62e9797aa2f44fe4e26cd8d` after owner-approved corrective PR #133. Theme metadata is `1.3.3`; the tree explicitly inherits the approved v1.3.2 Law 01 unified-heading work plus PR #129-#131 closure. Final-head Y5/X6 and fresh exact-main V1/X6 all PASS; deterministic package SHA-256 is `172a98dc00a52228fbb44a6ad24b79d568b7b543faa484c1ded59511b33e35d7`. Published GitHub Release and previously verified production deployment remain `v1.3.0` until separately changed.
- ConvertFlow F9/provider L5 is PASS only at the exact certified compatibility boundary in `docs/evidence/F9_PROVIDER_L5_SOURCE_SYNC_20260918.md`. This closes the Theme-side optional-provider certification question for that tested producer/consumer path; it does not release/deploy ConvertFlow or change the released ConvertFlow baseline.
- The Tâm Đức Hà Nội pilot is live on Theme metadata `1.3.0`. Owner-approved WPVibe publishing applied bounded post-release Theme-file changes with automatic `aznet-theme-wpvibe-backup`; byte identity to the published `v1.3.0` release asset or current GitHub `main` was not established, so runtime PASS must not be restated as package-byte PASS.
- Law 01 Quick Setup rerun is runtime PASS at the tested scope: Step 4/4, `READY_WITH_WARNINGS`, `Created: 0 · Reused: 36`, plus zero duplicate `law01-v1-2` starter roles in the read-only duplicate query.
- Live Homepage/content readiness at the tested scope: five published Posts render in `Mới cập nhật`; About excerpt renders; indexing is enabled; homepage meta description is present; fresh mobile Lighthouse is 98 Performance / 100 Accessibility / 100 Best Practices / 100 SEO.
- RootProfile Team remains `BLOCKED_EXTERNAL_CONTRACT`: no public/versioned organization-team projection exists on the pilot and the organization endpoint currently returns not-found. Issue #112 is the durable Theme-side blocker record. No user/author/post heuristic or private-storage fallback is allowed.

**Exact next:** preserve the canonical `v1.3.23` technical/publication PASS. The exact published package may be installed by any approved deployment path; repository package creation, technical verification and publication must not be blocked on WPVibe. RootProfile Team remains independently blocked at issue #112.

## 3. v1.0 closure state

### G0-G8 — Technical PASS

The source-defined G stream is no longer the active implementation path. Its retained meaning is:

- G0-G2: scope/cleanup/retirement inventory closed without ownership-unsafe deletion;
- G3: static/security/package hygiene PASS on verified candidate head;
- G4: WordPress-clean runtime PASS;
- G5: browser/responsive/a11y/performance PASS;
- G6: update/theme-switch/rollback PASS;
- G7: deterministic package candidate PASS;
- G8: metadata promoted atomically to `1.0.0` and final candidate gates PASS.

PR #34 final verified head remains `b2e5cca1461233bcb1a0333c5aa51879c3264756`, and merge commit `f8e1a95c903c3f246528368ae9878eba780539ff` shares tree `b716b89f04e45c2012f8e191c7d0edf605c9dd11` with that verified head.

Publication/tag is not inferred from technical PASS and remains a separate owner action/state.

## 4. v1.1 Native Product System roadmap

### R0 — Source reconciliation

**Goal:** make canonical source agree with the v1.0 merged state and ratify the approved v1.1 Native Product System architecture.

**Exit:** PASS — source-only PR #36 merged; AZT-05 remained unchanged.

### R1 — Design System 2.0

**Exit:** PASS — L1-L4 and editor/frontend parity proven; PR #37 merged.

### R2 — Native Pattern Library

**Exit:** PASS — 18 patterns shipped; 16 core/native plus 2 public-Woo-block-gated patterns; L1-L4 + theme-switch portability proven; PR #39 merged.

### R3 — Header System 2.0

**Exit:** PASS — four bounded presets and three sticky modes delivered; mobile no-JS fallback, keyboard/focus, 1440/1024/390/320 responsiveness, Overlay fallback and Woo-present/absent Commerce behavior verified through L1-L4; PR #41 merged.

### R4 — WooCommerce Presentation 2.0

**Exit:** PASS — public-Woo presentation settings/surfaces delivered without template takeover or commerce state duplication; clean-WP absence regression and Woo-present Theme-owned L1-L4 matrix complete; PR #43 merged.

### R5 — Control Center + System Health

**Exit:** PASS — static/contract, authenticated WordPress browser/a11y, clean-WP/Woo-present capability gating, settings portability, in-place update continuity and theme-switch continuity verified; PR #44 merged.

### R6 — Performance + Release 2.0

**Goal:** close v1.1 with measured asset/runtime behavior, reusable PR/exact-main CI, deterministic v1.x packaging and atomic metadata promotion.

**Exit:** PASS — measured clean/Woo route/asset baseline established; Theme-owned surface-aware loading guarded; ConvertFlow projection optimization correctly recorded `BLOCKED_EXTERNAL_CONTRACT`; reusable PR/exact-main verification merged; deterministic main-only candidate builder merged; covered G workflows retired; final exact-main R1-R6 verification passed; owner-approved metadata promotion moved both `style.css` and `AZNET_THEME_VERSION` atomically to `1.1.0`; post-promotion exact-main/package verification passed.

**Historical candidate:** `aznet-theme-1.1.0.zip`, one canonical top-level `aznet-theme/` directory, SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.

**Not implied:** Git tag, GitHub Release, production deployment or provider certification. Later P2/P3 production hardening requires a new final package before publication.

## 5. Post-R6 production-readiness program

The law-site pilot does not create legal/domain ownership for the Theme. It is a concrete professional editorial use case used to harden generic Theme presentation.

### P1 — Pilot identity cleanup

**Owner:** site operations / WordPress core lifecycle, not Theme runtime code.

Pre-delete read-only evidence proved active `aznet-theme` v1.1.0 and exactly one distinct inactive legacy candidate, `aznet-theme-release-v1.0.0` v1.0.0, with zero ambiguous AZnet identities. The owner confirmed backup availability and separately approved deletion of only that inactive identity through WordPress core UI.

**Exit:** **PASS** — post-delete run `35050519817` reports `NO_DUPLICATES`, active `aznet-theme` v1.1.0, zero inactive/ambiguous AZnet identities, Standalone Core `ready`, plus a fresh 28-check public regression PASS. Evidence: `docs/evidence/P1_POST_DELETE_CLOSURE_20260916.md`.

**Boundary retained:** installed-Theme deletion remains WordPress/site-operations ownership and is never automated by Theme runtime code.

### P2 — Editorial single-post hardening

**Goal:** production-grade generic long-form Post presentation for professional/editorial sites.

**Allowed:** native WordPress author/date/taxonomy/featured-image/content-pagination/post-navigation APIs; existing Theme shell/tokens; existing public RootProfile consumer only when it can enhance author presentation without a new contract.

**Required presentation:** semantic article header, native author, published/modified dates, categories/tags, optional featured image, long-form typography, accessible headings/lists/blockquote/table/media behavior, content pagination, previous/next navigation and WordPress-native author fallback.

**Forbidden:** legal/business semantics; SEO title/meta/canonical/schema/OG generation; custom related-post ranking/query engine; private RootProfile/ConvertFlow/Woo storage/API reads; authoritative slug/title/Page-ID/URL heuristics.

**Execution:** RED contract -> intended failure on previous minimal `single.php` -> minimal GREEN -> retained regression. Article assets remain singular-post scoped.

**Exit:** **PASS** — PR #51 merged to `main@a863da861...`; exact-main verification succeeded. Deeper real-site quality is intentionally deferred to P4 and is not inferred from L1/L2/L3/L4 generic CI.

### P3 — Editorial archive/search hardening

Improve native archive/search card scan quality for long Vietnamese titles, optional native thumbnail/date and excerpt. WordPress main query remains authoritative. Use RED -> GREEN and retain archive/search regression.

**Exit:** **PASS** — PR #52 merged to `main@a1dd42dd...`; exact-main verification run `34195248467` succeeded. The final P3 card retains native WordPress Loop/query ownership and adds presentation only.

### P4 — Real law-site pilot QA

**State:** **PASS / PILOT SIGN-OFF CLOSED.** The real pilot public matrix and authenticated read-only Admin/System Health matrix are retained, and P1 cleanup is now closed with fresh post-delete inventory/System Health plus a fresh 28-check public regression.

Fresh GitHub-only public-pilot run `35045540722` succeeded at functional head `758392b03227eb446627cc4669b0b0ddf56ed71e`; after the owner replaced historical pilot Theme `1.1.1` bits with the exact verified D-027 `1.1.0` package, the same public workflow was rerun and again passed 28/28 route/viewport checks. Fresh post-replacement public artifact: `10427713118`, digest `sha256:c58d0efebe9fdeea14c2d9d1ba3fa8a4439f2bafffc1dc180cf3b1d1554cbadb`. Authenticated read-only run `35047440842` also completed SUCCESS: WordPress `7.1`, PHP `8.4.25`, Theme `1.1.0`, current D-027 report shape, Standalone Core `ready`, 1440x1000 + 390x844 overflow `0`, visible focus retained and axe critical/serious findings `0`; artifact `10427876863`, digest `sha256:96d2943661e27ef05ce5b16940a792fbd4727d635849a8f5e9e8589b03d2abe9`. The representative Post still contains ten unlabeled task-list checkboxes inside WordPress-owned `the_content()`; those remain `CONTENT_AUTHORED_SEMANTICS`. RootProfile public profile surface was not observed, so no provider L5 claim is made. Evidence: `docs/evidence/P4_PUBLIC_PILOT_QA_20260916.md` and `docs/evidence/P4_AUTHENTICATED_PILOT_QA_20260916.md`.

Run realistic Vietnamese legal/editorial fixtures and the actual pilot stack across homepage, long Post, Page, archive/category, search/no-results, 404 and public RootProfile surfaces. Support-floor evidence remains separate from current-stack pilot evidence.

Minimum quality: no Theme-caused PHP fatal/warning/uncaught, no horizontal overflow, keyboard/focus/landmark/heading/label quality, responsive table/media behavior, Rank Math independence, RootProfile contract accuracy, update/theme-switch/rollback continuity and measured actual-site asset behavior.

P4 may install/update a test candidate on the pilot only as necessary to perform approved QA. It does **not** authorize tag/GitHub Release or final production deployment. Any destructive duplicate-theme deletion remains under P1's separate site-operations gate.

### P4-A — Homepage Composer + Law 01

**State:** **PASS / MERGED TO CANONICAL MAIN.**

Homepage Composer Core + `law-01` Premium presentation merged through PR #58 to `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`. Content Map remains typed WordPress references only; preset switching does not mutate WordPress content; native Post/Page Classic Editor policy remains; `front-page.php` retains exactly one `the_content()` boundary.

Fresh D-022 exact-main run `34414878827` passed reusable static/contracts plus WordPress 6.9 clean runtime/browser/a11y on the exact merge SHA. The integration-only `1.1.1` metadata attempt remains rejected historical evidence; canonical Theme metadata is `1.1.0`.

### P4-B — Law Site Provisioning v1.1

**State:** **PASS / MERGED TO CANONICAL MAIN.**

D-026 `law01-v1-1` remains an additive WordPress-native setup workflow. Recommendations are proposal-only until explicit change-plan confirmation; activation remains mutation-free; existing-site overwrite and whole-site noindex takeover remain forbidden. Starter Pages/Posts/Categories/Attachments become ordinary WordPress-owned content after successful provisioning. New/mostly-empty-site starter publication is coupled to explicit native search-visibility consent; active-site starter Posts fail safe to Draft without a public per-Post SEO contract.

Pre-merge integrated QA remains retained: package run `34413505990` produced Theme `1.1.0` deterministic candidate SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`; browser/runtime run `34413506017` passed WordPress 6.9 / PHP 8.1 / MySQL 8 provisioning scenarios. Canonical integration is separately proven by exact-main run `34414878827` SUCCESS on `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`.

**Known observation:** the separately recorded WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking and does not support a blanket PHP-log-clean claim.

**Next:** P4 Real Pilot QA under the already-approved pilot execution scope. No tag/GitHub Release, L5 provider certification, destructive duplicate-theme deletion or final production deployment is authorized by this merge.

### P4-C — Standalone Core independence

**State:** **PASS / MERGED TO CANONICAL MAIN.**

D-027 source ratification merged through PR #63 to `main@5d6dc56a756028d696a269ed75dd762daf4b83f2`; owner-approved implementation PR #64 merged final head `4b38f792d7de575bc08733748dac0399372e18c2` to `main@16563bdc88f172a1d7737b92293c7958670aac96`. Head and merge share tree `d2c4128f2b860bfd59c5efa11565dba38a34458e`.

Standalone Core now has executable evidence that WordPress 6.9 + AZnet Theme 1.1.0 alone can activate, provision, author/render and survive update/theme-switch continuity with zero active third-party plugins. Optional WooCommerce, RootProfile, ConvertFlow and SEO providers remain additive public-contract tracks; their absence is not Core failure.

Fresh exact-main run `35042767873` passed reusable static/contracts plus clean WordPress 6.9 runtime/browser/a11y on the exact merge SHA. Pre-merge final-head runs `35042040619`, `35042040512`, `35042040532` and exact-package `35042040620` are retained; exact D-027 candidate ZIP SHA-256 is `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`.

**Historical next at D-027 closure:** P4 Real Pilot QA when pilot access became available; P5 publication/deployment required later separate owner approvals. Those release-critical steps are now closed, while no provider L5 certification is inferred from Core PASS.

### P5 — Publication and deployment

**State:** **PUBLICATION + PRODUCTION DEPLOYMENT PASS.**

Technical run `35051428372` verified the final deterministic `aznet-theme-1.1.0.zip`: exact canonical production bytes, 121-file source/package identity, 93 packaged PHP files lint PASS, clean WordPress 6.9 zero-plugin runtime/browser, and switch-away/switch-back continuity. Final SHA-256: `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`. Evidence: `docs/evidence/P5_FINAL_CANDIDATE_VERIFICATION_20260916.md`.

Owner-approved publication run `35052694111` then created annotated tag `v1.1.0` targeting canonical `main@7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78` and GitHub Release `389622362` (`AZnet Theme 1.1.0`). Attached asset `aznet-theme-1.1.0.zip` (`567100593`) reports the same SHA-256 and was downloaded/re-hashed by the publication workflow. Publication evidence artifact: `10428918492`. Evidence: `docs/evidence/P5_PUBLICATION_20260916.md`.

**Exit:** Git tag + GitHub Release publication PASS; owner-approved production deployment disposition for `tamduchanoi.aznet.vn` PASS with fresh read-only run `35054176392`. No provider L5 certification is inferred.

### X — v1.2 WordPress Experience Completion

**State:** **PUBLICATION + PRODUCTION DEPLOYMENT PASS.**

**Goal:** complete the native WordPress Core experience of AZnet Theme while keeping the Theme a presentation owner. v1.2 does not open a new provider/plugin integration program.

**Architecture:** retain shared tokens/content primitives and the hybrid PHP + `theme.json` architecture; add bounded surface modules only where a surface has distinct markup/asset needs. WordPress continues to own comment/search/media/pagination/form semantics and state. Theme owns presentation, responsive behavior, accessibility presentation and surface-aware Theme assets.

**Deep-polish surfaces:** Comments; Search + 404 + empty states; Media/gallery/embed. Pagination/navigation and native form controls reach production-ready quality without becoming large application subsystems.

**Execution order:** `X1 Comments -> X2 Search/404 -> X3 Media -> X4 Navigation -> X5 Forms -> X6 Closure`.

**Version discipline:** X1-X5 remained at `1.1.0`. After fresh pre-promotion X6 evidence and explicit owner approval, the isolated X6 candidate was atomically promoted to exact `1.2.0`. The separate merge, publication and production-deployment gates have now all been crossed: canonical `main`, the published release and the verified production site are `1.2.0` at their respective verified scopes.

**QA:** each production slice uses RED -> intended failure -> minimal GREEN -> retained regression, then L3 real WordPress runtime and L4 responsive/keyboard/focus/overflow/a11y. X6 adds L6 deterministic package/source identity, update/theme-switch continuity, rollback and exact-main verification. Provider L5 is outside v1.2 and is not inferred.

**Forbidden:** provider integration expansion, private provider reads, custom search/ranking engine, custom comment engine/store, gallery/lightbox application engine, page builder/FSE takeover, SEO/domain ownership or a new build stack without a separate accepted decision.

**Design record:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`.

**X1 — Comments Surface:** **PASS at L1-L4** on functional closure head `1b58012f78eab6525e5e8e424e8904995035f4ea`; final functional workflow `35060458118` completed SUCCESS with ownership/diff/version regressions, clean WordPress 6.9 zero-plugin runtime and 3/3 browser/a11y matrix. Evidence: `docs/evidence/X1_COMMENTS_SURFACE_20260916.md`. WordPress retains all comment data/lifecycle ownership; provider L5 is not inferred.

**X2 — Search / 404 / Empty States:** **PASS at L1-L4** on functional closure head `c5c9415f851e2ed77c3069c08ff4f963b7c33c3c`; X2 workflow `35071280839` completed SUCCESS on WordPress 6.9 with zero active plugins, native HTTP 404 semantics, surface-scoped recovery CSS, and a 16/16 Playwright/axe route/viewport matrix. Artifact `10436541455`, digest `sha256:c1e31a506172f90939d5688317aae0bd1b2ec9fa437dcd8726605c80fc191cd2`. All 23 retained PR workflows observed on the same functional head completed SUCCESS. Evidence: `docs/evidence/X2_SEARCH_404_EMPTY_20260916.md`. WordPress retains search query/main-query/archive/404 authority; provider L5 is not inferred.

**X3 — Media / Gallery / Embed:** **PASS at L1-L4** on functional closure head `ff6d7a1a025a97d8710644a78b6dcf6d2b34aa69`. X3 workflow `35078254834` completed SUCCESS on WordPress 6.9 with zero active plugins: scoped authored-content media presentation for Post/Page/static Front Page, modern Core and approved legacy media output, native video/audio/embed controls, safe wide/full alignment, 12/12 responsive Playwright/axe cases across 1440/1024/390/320 widths, and Classic Editor parity PASS. Artifact `10439630267`, digest `sha256:2ee736d3b0e9e0182ab935367b55044df35d4c7e0234e56f3e9c273b968e9bd2`. All 24 triggered exact-head workflows completed SUCCESS after one R4 Woo browser retry reproduced as a transient disposable PHP-server empty reply rather than a Theme code regression. Evidence: `docs/evidence/X3_MEDIA_GALLERY_EMBED_20260916.md`. WordPress retains attachment/content/block/shortcode/embed semantics and lifecycle; Theme metadata remains `1.1.0`; provider L5 is not inferred.

**X4 — Pagination & Navigation Completion:** **PASS at L1-L4** on functional closure head `f34db6f89cff505c16c2bb98f479eebba5661079`. Dedicated workflow `35083830374` completed SUCCESS on WordPress 6.9 with zero active plugins, native runtime/asset-boundary PASS and a 20/20 Playwright/axe browser matrix covering archive/search pagination, Post page links, adjacent Post navigation and comment pagination. Artifact `10440569415`, digest `sha256:16b1b4d69b81a5ce7403d081b565d80c67a4c4e79ddb50538abdc5771928fb84`. All 25 exact-head pull-request workflows observed on the functional head completed SUCCESS. WordPress retains pagination/navigation/comment state and URL authority; Theme metadata remains `1.1.0`; provider L5 is not inferred. Evidence: `docs/evidence/X4_PAGINATION_NAVIGATION_20260916.md`.

**X5 — Native Form Controls:** **PASS at L1-L4** on functional closure head `8aac300f805e83fb7a7e7243aa779fe37376838d`. Dedicated workflow `35088686988` completed SUCCESS on WordPress 6.9 with zero active plugins, scoped `forms.css` presentation for native Comment, Core-block form-control, password-protected Page, Search and 404 surfaces, absence on Archive/Home controls, and a 16/16 responsive Playwright/axe matrix across 1440/1024/390/320 widths. Artifact `10442863335`, digest `sha256:88d06c8b3449233e7a5b85421dc2be779eac51dc9cacbe4d694ceab86f2705ac`. All 26 exact-head pull-request workflows observed on the functional head completed SUCCESS. WordPress retains form submission/validation semantics and submitted data authority; Theme metadata remains `1.1.0`; provider L5 is not inferred. Evidence: `docs/evidence/X5_NATIVE_FORM_CONTROLS_20260916.md`.

**X6 — Cross-surface QA & Release Closure:** fresh synchronized candidate `c145f72cc7ac4783eb9f4a2f0b11c1d26e178445`, tree `3594fab0c89d015711e914325734646d1e7df159`, passed run `35126908185` across L1-L4 and L6: WordPress 6.9 zero-plugin runtime, integrated `32/32` Playwright/axe matrix, deterministic `aznet-theme-1.2.0.zip`, exact source/package identity, 95 packaged PHP lint checks, and switch-away/switch-back lifecycle continuity. Package SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`; artifact `10459925538`, digest `sha256:c6b480168282e7dc9eea8f749d303012763cabf16213a6e69619c62f96f16e03`. All 19 observed same-head PR workflows completed SUCCESS. Durable evidence: `docs/evidence/X6_CROSS_SURFACE_RELEASE_CLOSURE_20260916.md`. Owner-approved PR #87 then merged exact final head `ff459b99a348d3004a3a4e11474b7c5ff19508f2` to `main@c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`, tree `70c04929afe38e87d317bad3414157e9e2bd6f74`. Exact-main run `35129331899` and X6 push-to-main run `35129331927` both completed SUCCESS; the exact-main package retains SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798` with artifact `10459889475` (`sha256:f537b5f6ed33a53dcad8041c3d8cc0aef32c5bb6dbd8c10c5102e76624128fb9`). Owner-approved publication run `35131383107` then published the matching annotated tag/Release. Owner-approved production deployment run `35135759389` installed the exact published package on `tamduchanoi.aznet.vn`, and fresh independent read-only run `35136876330` verified active Theme `1.2.0`, Standalone Core `ready`, retained WordPress-owned identity/menu data and the 28/28 public regression. Evidence: `docs/evidence/V1_2_PRODUCTION_DEPLOYMENT_20260917.md`.

**Exact Next:** the v1.2.0 Core release path is closed at technical integration + publication + production deployment PASS. Begin no new Theme milestone or provider certification without a separately approved slice; provider L5 remains separate and is not inferred.

## 6. Dependency order

Core v1.1 is complete:

`R0 -> R1 -> {R2, R3, R4} -> R5 -> R6 = PASS`

Post-R6 production readiness:

`P1 site-ops cleanup (parallel/destructive gate)`  
`P2 Editorial Single-Post PASS -> P3 Editorial Listing/Search PASS -> P4-C Standalone Core PASS -> P4 Real Pilot QA PASS -> P1 cleanup PASS -> P5 technical candidate PASS -> P5 publication PASS -> production deployment PASS`

P1/P4 pilot sign-off and the v1.1 P5 publication/deployment path are closed. v1.2 technical integration, publication and owner-approved production deployment are also PASS at their separately verified scopes; no v1.2 Core release gate remains open.

## 7. QA layers

| Layer | Purpose | Minimum exit |
| --- | --- | --- |
| L0 — Source & State | Correct baseline, owner, dependencies, exact next | No source/ownership ambiguity |
| L1 — Static | Cheap deterministic defects | Syntax/naming/private-storage/dependency scans clean |
| L2 — Contract / TDD | Behavior boundary | RED fails for intended reason -> minimal GREEN -> related regression PASS |
| L3 — Runtime | Real WordPress execution | No Theme-caused fatal/warning; route/component smoke PASS |
| L4 — Browser / Visual / A11y | Real presentation | Responsive/keyboard/focus/landmark/console intent PASS; editor/frontend parity belongs here for presets/patterns |
| L5 — Integration | Cross-product boundary | Public contract, ownership and fail-soft behavior PASS for the certification target |
| L6 — Completion / Release | Deliverable integrity | Regression/package/SHA/unzip-reverify/rollback/source state complete on final candidate bytes |

No PASS may be inferred across layers.

## 8. Release-critical test matrix

| Case | WordPress | Optional providers | Expected result |
| --- | --- | --- | --- |
| C1 | On | All off | Theme activates and renders core site without fatal; release-critical |
| C2 | On | Woo off | Non-commerce surfaces remain normal |
| C3 | On | Woo on | Theme presentation works without taking commerce ownership |
| C4 | On | RootProfile absent/error/unsupported | Theme-side adapter fails soft; core Theme remains usable |
| C5 | On | ConvertFlow absent/error/unsupported | Native WordPress Homepage path remains usable |
| C6 | On -> switch Theme | Any optional provider state | Switching Theme must not delete external domain data |
| C7 | In-place Theme update | WordPress-native content + Theme-owned settings | Content/settings continuity and rollback path PASS |
| C8 | Final v1.x merge | Exact canonical `main` bytes | Post-merge static/runtime/browser core verification must run on exact `main` SHA before release-candidate promotion |
| C9 | Professional editorial pilot | Long Vietnamese editorial/legal-form content | Theme-owned single/listing presentation remains readable, responsive, accessible and SEO-owner-neutral |

Provider-specific runtime correctness beyond Theme-side fail-soft is required only before claiming that provider/version as certified compatibility, unless the defect is in Theme-owned code.

## 9. QA dimensions

- Runtime: activation, PHP error markers, template hierarchy/fallback, update and theme switch.
- Visual: desktop/tablet/mobile on core surfaces.
- Responsive: navigation, containers, overflow, long labels/content.
- Accessibility: landmarks, heading order, keyboard, visible focus, labels, reduced motion and contrast within Theme control.
- Security: context-correct escaping/sanitization and no private provider leakage.
- Contract: public/version/capability detection and fail-soft paths.
- Ownership: no parallel identity/product/Journey/commerce store in Theme.
- Performance: surface-aware asset loading; no global ecosystem bundle; improvement claims require measured evidence.
- Provenance: migration/retirement remains traceable and reversible.
- Authoring portability: patterns/settings must not lock WordPress content into proprietary Theme data.
- Release provenance: PR-head PASS and exact-main PASS are distinct evidence points.
- Editorial trust presentation: native publication facts may be rendered, but Theme does not become owner of legal accuracy, SEO metadata or authoritative identity semantics.

## 10. Accepted Decision Log

| ID | Decision | Status |
| --- | --- | --- |
| D-001 | Source owns data; Theme owns presentation; contracts connect them | Accepted |
| D-002 | AZnet Theme is a house/reference theme and not a hard dependency of ecosystem plugins | Accepted |
| D-003 | Provenance inventory precedes redesign/reuse | Accepted |
| D-004 | Theme does not read private internals of plugins | Accepted |
| D-005 | Provider/plugin absence or failure must fail soft/safe | Accepted |
| D-006 | Retire historical code only after destination + fallback/compatibility + regression + rollback | Accepted |
| D-007 | Architecture mode is hybrid PHP theme + `theme.json` | Accepted |
| D-008 | Support floor is WordPress 6.9+ and PHP 8.1+ | Accepted |
| D-009 | Naming family is `aznet-theme` (`AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`) | Accepted |
| D-010 | Milestone 0 provenance gate and WordPress-clean T1 runtime gate are distinct | Accepted |
| D-011 | Performance is surface-aware; no global frontend ecosystem bundle by default | Accepted |
| D-012 | Execute by evidence depth; do not infer deeper PASS from shallower evidence | Accepted |
| D-013 | RootProfile current-surface context does not grant production takeover | Accepted |
| D-014 | Woo presentation work may progress in parallel with externally blocked RootProfile integration | Accepted |
| D-015 | Homepage Theme-owned stream may progress in parallel without taking ConvertFlow semantics | Accepted |
| **D-016** | **Core v1.0 release independence: WordPress-clean Theme quality defines core readiness; optional provider defects remain compatibility blockers unless Theme-owned or explicitly mandatory** | **Accepted** |
| **D-017** | **v1.1 uses WordPress-native authoring/patterns/presets and does not introduce a proprietary page builder** | **Accepted** |
| **D-018** | **Visual preset outcome is fixed; implementation mechanism is evidence-gated on WordPress 6.9 hybrid support** | **Accepted** |
| **D-019** | **Theme presentation settings use one versioned `aznet_theme_settings` Theme Mod schema with strict allow-list normalization** | **Accepted** |
| **D-020** | **Header v1.1 uses bounded presets/primitives, not drag/drop composition or mega-menu application ownership** | **Accepted** |
| **D-021** | **Woo v1.1 expands presentation only; wishlist/filter/search/swatches/quick-view/custom-checkout engines remain outside Theme** | **Accepted** |
| **D-022** | **Exact-main post-merge verification is required before a v1.x release candidate is promoted** | **Accepted** |
| **D-023** | **Professional editorial/law-site pilot hardening remains generic Theme presentation: WordPress owns publication/content state, SEO owner keeps metadata/schema, and no legal/domain semantics move into Theme** | **Accepted** |
| **D-024** | **Homepage template switching uses a stable typed Content Map plus Theme-owned Presentation Preset; Classic Editor remains for native Post/Page; provisioning is separate from preset application; the Front Page keeps exactly one `the_content()` boundary and no provider/domain semantics are cloned into Theme.** | **Accepted** |
| **D-025** | **Law Site Provisioning v1 is an explicit idempotent WordPress-native bootstrap workflow: activation is mutation-free; new/existing sites use a visible confirmed change plan; created content becomes WordPress-owned; failed-run rollback is current-run bounded; successful setup is not equivalent to Launch Ready.** | **Accepted** |
| **D-026** | **Law Site Provisioning v1.1 may preselect non-authoritative exact-label recommendations, seed bounded WordPress-native starter editorial/media content, and temporarily discourage indexing only on a confirmed new/mostly-empty-site plan; active-site starter content fails safe to Draft without a public per-Post SEO contract, and all authoritative mapping/mutation remains explicit in the confirmed provisioning plan.** | **Accepted** |
| **D-027** | **AZnet Theme Core has zero mandatory third-party runtime dependency. WordPress + Theme alone must complete install/activate/setup/provision/author/render on the support floor; optional providers are additive capability tracks, provider absence is not Core failure, and the exact final package must pass a zero-plugin standalone release path before Core Ready/publication. External development/QA tooling is allowed only outside deployed runtime.** | **Accepted** |
| **D-028** | **v1.2 WordPress Experience Completion is a WordPress-Core-only Theme milestone using surface modules + shared primitives. No new provider integration is opened; Comments, Search/404/empty states and Media/gallery/embed receive deep polish; metadata remains 1.1.0 until X6 final-candidate promotion.** | **Accepted** |
| **D-029** | **v1.3 Client Delivery System prioritizes fast, professional client handoff through WordPress-native inner Page presentation, five portable professional Page kits, bounded Footer presets, and a generic confirmed professional-services provisioning blueprint. It preserves Classic Editor policy for native Post/Page, the single `aznet_theme_settings` store, zero mandatory provider runtime dependency, and keeps metadata at 1.2.0 until a separately approved Y5 promotion gate.** | **Accepted** |
| **D-030** | **Homepage Hero authoring uses a Theme-owned visual library over one WordPress-owned Core-block synced Hero (`wp_block`). Theme stores only the typed block reference and presentation variant; Page Hero is legacy compatibility only. Explicit Hero initialization is draft-first so current public Hero output remains unchanged until the user publishes the new synced Hero; ordinary settings/preset saves remain content-mutation free.** | **Accepted** |
| **D-031** | **Burgundy Law 01 Homepage composition is Theme-owned presentation around the existing single native Front Page `the_content()` boundary: Hero/Trust/Services/About-Team render before native content; Process/FAQ/Latest legal knowledge/Contact CTA render after it; Topics/Analysis/News stay excluded from the Burgundy reference composition. Every mapped section fails soft when its WordPress/public-safe source is absent or invalid, and no provider/domain data ownership or authoritative inference moves into Theme.** | **Accepted** |
| **D-032** | **Burgundy Law 01 Homepage expansion preserves the owner-accepted v1.3.18 visual baseline for Hero/Trust/Services/About-Team/Latest and extends only below Latest with exact mapped WordPress-native Topics, Analysis, Legal News, Process, FAQ and Contact CTA. The Theme owns composition/presentation only; WordPress/public-safe sources own content/data; every added section fails soft when its mapped source is absent/invalid; Footer remains page-independent; no heuristic source discovery, private storage read, provider takeover or domain semantics are introduced. D-032 supersedes D-031 only where D-031 excluded Topics/Analysis/News or ordered post-content sections differently.** | **Accepted** |
| **D-033** | **Repository-native static/contract/WordPress runtime/browser/a11y/exact-package/lifecycle evidence is sufficient for AZnet Theme technical release closure; WPVibe is not a mandatory release-verification dependency. Production deployment remains a separate gate and still requires a safe authenticated target-site path, fresh preflight and rollback evidence by an available approved mechanism.** | **Accepted** |
| **D-034** | **Rèm 01 is a reusable curtain/interior presentation preset and migration pilot over WordPress/WooCommerce/provider-owned data. It may extend existing Homepage/Header/Footer/Page/Woo/provisioning presentation systems but must not interpret Flatsome/UX Builder at runtime, create a parallel product/contact/identity store, or absorb ConvertFlow decision/conversion semantics. Existing-site Flatsome cutover requires read-only inventory, native-content portability for trapped builder content, real runtime/browser evidence, rollback, and a separate production approval gate.** | **Accepted — owner approved 21/09/2026** |
| **D-035** | **AZnet Theme uses a full-bleed outer presentation shell by default: page/front-page/section backgrounds may span the viewport while readable content is constrained only by explicit inner containers. A first Hero must sit flush directly below the Header with no Theme-owned top gap, outer gutter, boxed shell, rounded outer edge or shadow unless a preset specification explicitly opts into one.** | **Accepted — owner approved 22/09/2026** |
| **D-036** | **On desktop, AZnet Theme must not artificially wrap a heading onto an additional line when the heading can fit within its actual available container width. Presentation may not use narrow decorative width caps to force wrapping, and must not use `white-space: nowrap` to create overflow. Natural wrapping remains correct when the real container is insufficient; tablet/mobile may wrap responsively for readability.** | **Accepted — owner approved 22/09/2026** |
| **D-037** | **Self-hosted Theme fonts used for Vietnamese content must explicitly prove Vietnamese glyph coverage for every shipped production weight. The canonical typography family must be consumed through Theme tokens/presets; presentation presets may not silently replace it with unrelated serif/system stacks. A font fix is not production PASS until the Vietnamese face is verified at runtime/browser level; repository PASS and live-site publish remain separate gates.** | **Accepted — owner approved 22/09/2026** |

## 10A. v1.3 Client Delivery System

**State:** **PUBLICATION + PRODUCTION DEPLOYMENT PASS.**

**Goal:** materially reduce the time from clean WordPress to a professional, client-ready service/business website without introducing a proprietary builder or domain engine.

**Design record:** `docs/superpowers/specs/2026-09-17-v1.3-client-delivery-system-design.md`.

**Implementation plan:** `docs/superpowers/plans/2026-09-17-v1.3-client-delivery-system.md`.

**Y1 — Page Experience 2.0:** upgrade native inner Page presentation with WordPress-owned title/excerpt/featured image/hierarchy, child-Page breadcrumb presentation, Standard/Wide/Landing Page variants, surface-aware assets and L1-L4 evidence. Breadcrumbs use real WordPress ancestors only; no URL/slug/menu heuristics and no SEO schema ownership.

**Y2 — Professional Page Kits:** ship five portable WordPress-native compositions for About, Services, Team/Expertise, Contact and Content Landing. Patterns may use neutral replaceable copy but must not fabricate identity/contact/credentials/service truth. Native Post/Page Classic Editor policy remains in force; stored WordPress content must remain portable through theme switch.

**Y3 — Footer System 2.0:** provide Standard/Professional/Compact Theme-owned presentation presets over existing WordPress site identity and Footer menu locations. Empty data sources fail soft; no contact/social store is added.

**Y4 — Professional Services Starter Blueprint:** extend the existing D-025/D-026 confirmed/idempotent provisioning engine with a generic professional-services blueprint. Activation stays mutation-free; the user reviews and confirms a visible change plan; created content becomes WordPress-owned; current-run rollback remains bounded; active sites are not silently overwritten.

**Y5 — Client Delivery QA & Release Closure:** prove the full clean-site journey on WordPress 6.9+/PHP 8.1+, zero mandatory third-party plugins, responsive/a11y browser matrix, deterministic exact-package identity, packaged-PHP lint, theme-switch/update/rollback continuity and exact-main verification. Metadata remained `1.2.0` through Y1-Y4; promotion to `1.3.0`, publication and production deployment were separately owner-approved and completed.

**Provider boundary:** v1.3 Core opens no RootProfile/ConvertFlow/Woo provider L5 expansion. Existing optional compatibility tracks remain separate and fail-soft.

## 10B. Rèm 01 curtain/interior pilot — ACCEPTED

**State:** LIVE PILOT CUTOVER + FOLLOW-UP PRESENTATION + PROCESS RAIL + PROJECT SHOWCASE PRODUCTION PASS AT THE VERIFIED SCOPE. The live pilot now runs AZnet Theme `1.3.31`; the owner-approved Process Rail and fail-soft Project Showcase are live with WordPress-owned sources. D-037 Vietnamese Roboto declarations are live and emitted by the public Homepage, while a dedicated public-host browser assertion that every Vietnamese face reaches `loaded` remains UNKNOWN. Independent pixel-level external L4 verification and provider-specific L5 certification remain separate and are not inferred.

**Pilot:** `https://remquocanh.vn/` is the migration/reference pilot. The reusable output is a generic Rèm 01 presentation, not a hardcoded Rèm Quốc Anh site.

**Design record:** `docs/superpowers/specs/2026-09-21-curtain01-pilot-design.md`.

**Intent:** replace a Flatsome presentation layer with AZnet Theme while preserving WordPress/WooCommerce/provider ownership, keeping Flatsome rollback until destination QA passes, and avoiding a proprietary builder or Flatsome runtime compatibility engine.

**Current checkpoint:** C1 read-only inventory is complete; C2-C5 Theme-owned Rèm 01 implementation and repository-native browser/a11y verification are retained PASS. Owner-approved PR #262 added the WordPress-owned Process Rail source (Page #997) and PR #263 added a fail-soft Project Showcase using one explicit WordPress Category mapping. Canonical implementation is `main@f84d45a58261ec41d9b0c11a5e02a0873edb28ff`, Theme metadata `1.3.31`; exact-main V1 `35870034014`, X6 `35870034255` and Project Showcase `35870033789` are SUCCESS. The live pilot runs `homepage_preset=curtain-01` and `visual_preset=curtain-01` with Hero block #991, About Page #35, Process Page #997, Project Category #80, Knowledge Category #57, Contact Page #45 and WooCommerce-owned catalogue data. Post #999 is the first published WordPress-native project source. WPVibe retains `aznet-theme-wpvibe-backup`; Flatsome/Flatsome Child remain inactive.

**Live QA disposition:** fresh production readback proves Theme `1.3.31`, `homepage_curtain01_projects_term = 80`, live composition order `about -> category-showcase -> catalogue -> process -> projects -> knowledge -> final-cta`, and a rendered Project Showcase card for Post #999. A stale draft-only `max-width: 22ch` Process heading rule was detected after the first publish by direct live-to-canonical comparison, removed by resetting Curtain 01 CSS/JS to exact canonical bytes, previewed, and republished. Fresh production verification then confirmed live Curtain 01 CSS/JS and `theme.json` byte-identical to canonical main. Lighthouse on the public Homepage is Accessibility 100 / Best Practices 100 for both mobile and desktop. The public head emits Vietnamese Roboto Regular/Medium/Bold face URLs; dedicated browser loaded-state verification remains UNKNOWN. Independent pixel-level external L4 and provider-specific L5 remain unclaimed.

**Evidence:** `docs/evidence/C6_CURTAIN01_PAGE2_PREWRITE_SNAPSHOT_20260921.md`, `docs/evidence/C6_CURTAIN01_LIVE_CUTOVER_20260922.md`, `docs/evidence/CURTAIN01_EXACT_MAIN_FOLLOWUP_20260922.md`, and `docs/evidence/CURTAIN01_PROJECT_SHOWCASE_PRODUCTION_20260923.md`.

**Exact Next:** preserve the live Project Showcase/Process PASS and WordPress ownership boundaries. Do not open another Theme/provider feature merely because the pilot is live. The remaining verification gaps are a dedicated public-host Vietnamese font loaded-state assertion and independent external pixel-level L4; provider-specific L5 remains separate. Additional project cards should come from verified WordPress-native project Posts, not Theme hard-coding or provider heuristics.

## 11. Open questions

- O-004 RootProfile provider shape: CLOSED at E0.
- O-005 Woo override policy: CLOSED; hooks/CSS/Blocks-first, template override by exception only.
- O-007 build tooling: OPEN only when real module/minification needs appear; it is not automatically introduced by v1.1.
- O-008 ConvertFlow projection asset gating: OPEN/BLOCKED unless a documented public/versioned Theme-consumable capability exists; private detection is forbidden.

## 12. Non-goals

- Do not add a page builder or FSE architecture takeover.
- Do not create a mega-menu builder, AJAX filter/search engine, wishlist/compare, swatch domain engine, quick-view business engine or custom checkout engine.
- Do not implement RootProfile or ConvertFlow source code inside Theme.
- Do not claim provider certification without provider/version evidence.
- Do not fix external defects through private API/storage reads or authoritative routing heuristics.
- Do not create a second Theme settings store or duplicate WordPress native logo/menu/content data.
- Do not create legal-domain storage/workflows merely because the pilot is a law website.
- Do not generate SEO canonical/meta/schema/OG output in Theme when SEO ownership remains external/native.
- Do not add a related-post ranking/query engine to Theme.
- Do not reopen already-PASS layers without a concrete invalidation; the editorial pilot quality finding reopened presentation quality only, not prior runtime/ownership conclusions.

## 13. Hard gates

The following remain explicit owner approval gates:

- any later product/architecture/public-contract change not already ratified here;
- provider takeover paths such as E5-D;
- destructive retirement/deletion without proven rollback;

Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. Owner-approved PR #58 canonical integration remains historical PASS evidence. D-027 source PR #63 and owner-approved implementation PR #64 remain canonical history. P4 public QA PR #66 and owner-approved authenticated QA PR #67 are merged; canonical `main@bf45a315e93fa22c441c81377f98ff34901d7ca5` passed exact-main run `35048609753`. P4 public + authenticated QA is retained and P1 cleanup is PASS after owner-approved deletion of only `aznet-theme-release-v1.0.0`; post-delete run `35050519817` closes final P4 pilot sign-off at the tested Theme-owned scope. P5 technical closure PR #71 was owner-approved and merged; the owner then separately approved publication as tag `v1.1.0` + GitHub Release, completed by run `35052694111`, and separately approved the existing-package production deployment disposition verified by run `35054176392`. Provider L5 certification remains separate/unproven. X4 PR #83 and X5 PR #85 canonical merges are retained historical owner-approved integration evidence. The X6 metadata-promotion gate was separately approved after fresh pre-promotion GREEN evidence. The owner later separately approved PR #87 canonical merge; it merged to `main@c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad` and fresh exact-main V1 + X6 verification passed. The owner then separately approved v1.2.0 annotated-tag/GitHub-Release publication, completed by run `35131383107`, and later separately approved production deployment. Deployment run `35135759389` and fresh independent read-only run `35136876330` both completed SUCCESS at the verified Theme-owned/site-operations scope. Provider L5 certification remains separate and unproven.

### v1.3 technical closure checkpoint — 17/09/2026

Owner-approved PR #98 merged Y5/v1.3 to canonical `main@afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`, tree `020db34ca6f360d5af12e6f15abf4c9ba51179f0`. Fresh exact-main V1 run `35206627102` and the pre-existing X6 push-to-main release path run `35206626992` both completed SUCCESS. The exact main package is deterministic `aznet-theme-1.3.0.zip`, SHA-256 `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`, with 144 files, 107 packaged PHP files linted, 32/32 browser/axe cases PASS and switch-away/switch-back lifecycle continuity PASS. This closes D-029 implementation technically without changing domain ownership or claiming provider L5. Evidence: `docs/evidence/V1_3_TECHNICAL_CLOSURE_20260917.md`.

### v1.3 publication closure checkpoint — 17/09/2026

Owner-approved publication run `35211254633` completed SUCCESS and published annotated tag `v1.3.0` (tag object `56f2ae2e8ba6e1fb4e8359447656e88ab6411ba6`) dereferencing to exact technical commit `afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`, plus GitHub Release `390623960`. Release asset `570039299` is `aznet-theme-1.3.0.zip`, SHA-256 `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`; publication evidence artifact `10492411096` has digest `sha256:0a18d2169ad8d92a587421289ede773031e56d3646ddac44ba326ebe4ea6fd75`. Publication is PASS without production deployment. Evidence: `docs/evidence/V1_3_PUBLICATION_20260917.md`.

### v1.3 production deployment closure checkpoint — 17/09/2026

The product owner separately approved production deployment of the exact published v1.3.0 asset to `tamduchanoi.aznet.vn`. Deployment run `35222200660` completed SUCCESS and upgraded the active Theme from `1.2.0` to `1.3.0` while retaining Standalone Core `ready`, approved WordPress-owned menu/location data, logo and phone presentation; rollback remained unused. Deployment artifact `10497483290` has digest `sha256:84d041445b606279a0caebef60b9c38206f5a9a88b8e1b759453484dfad1c6b1`.

Fresh independent read-only run `35223053733` then completed SUCCESS: active Theme `1.3.0`, Standalone Core `ready`, `header-utility` retained on menu ID `15`, exact phone retained, desktop/mobile public shell PASS and broad public matrix `28/28` with no Theme-owned blocking failure. Post-deploy artifact `10497925626` has digest `sha256:756f765fe70f2ca61978111678853204808ea4cb4cd44fdd3453d50e177313b2`. Temporary ops workflows/harnesses were removed at cleanup commit `6ef53f0bd20c69c65b88a753627436e31976d606`; the cleaned ops branch had zero net file delta versus `main@2c4e6f0442335ac3e690e97beaca4d4bf6405fa5`. Evidence: `docs/evidence/V1_3_PRODUCTION_DEPLOYMENT_20260917.md`.

Published/GitHub-Release release and current verified production deployment are now both `v1.3.0`. Provider L5 remains separate and unclaimed.

### F8 integrated compatibility closure checkpoint — 17/09/2026

ConvertFlow owner-side compatibility fix PR #31 was merged to canonical `truongdinhnamaz/convert-flow` `main@64cf2e66d41e6619e2bbcf53c12d8831e2852442` from exact verified head `7cad02091ce12ef0c146fd6e0837a653169ca753`. Comparison from verified head to merge commit reports no file delta, so the canonical merge tree preserves the exact verified candidate bytes.

Fresh exact integration evidence used AZnet Theme `252554961bc81d22427363aee0048365fb4e116b`, WordPress 6.9, PHP 8.1, ConvertFlow Core P5.242 and Pro P5.164. F8 workflow `35231522317` and retained C1 workflow `35231522361` completed SUCCESS. L3 proved exactly one document-level `<main>` owned by the Theme while the ConvertFlow Homepage Journey rendered through `the_content()` with a neutral root. L4 desktop/tablet/mobile + keyboard/focus + console + Axe matrix passed 4/4. Artifact `10501317335` has digest `sha256:a353d11b456e31974be4b5362005a1e02ddad2a5770e2f2c62efd4bea87fbdda`.

This closes the historical F8 nested-main compatibility blocker only. It does not promote ConvertFlow release/deployment state and does not infer F9/provider L5 certification beyond the verified L3-L4 compatibility scope. Historical P5.239 blocker evidence remains provenance.

### v1.3.4 dedicated Hero source closure checkpoint — 18/09/2026

Owner-approved PR #135 merged final verified head `d0781a2c25a3b15bbc535184e0b03bafa2141a9b` to canonical `main@d008f66cfa7357f3d6e4bede492e41a5d49a4112` with zero file delta between final head and merge commit. The slice introduces a dedicated WordPress-owned Hero Page reference (`homepage_hero_page`) for Law 01 and removes Site Title/Site Tagline/Front Page title as implicit Hero copy sources. Theme remains presentation/composition owner only; WordPress owns Hero content/media.

All 34 triggered workflows on the final PR head completed SUCCESS. This includes D-027 Standalone Core L3/L4/Exact Package/Retained Regression, V1 Core PR CI, Homepage Composer Law 01 Browser Quality, Law Site Provisioning Browser Quality + Candidate Package, F Homepage Native Runtime + Regression Package, X6 Cross-surface Release Closure, Y5 Client Delivery Release and Release Version Consistency. Y5 deterministic package evidence produced `aznet-theme-1.3.4.zip`, 145 files, SHA-256 `5cf0041ab09a6b9718e4354d2aa9c00850aa93e83c9ae775a640dc613caf3f83`. No fresh exact-main workflow run is claimed for merge commit `d008f66...`; merged-byte identity is supported by zero file delta from the verified final head. Publication/tag and production deployment remain historical `v1.3.0` until separately approved and executed. RootProfile Team remains external-blocked under issue #112.

### v1.3.5 Hero backward-compatibility closure checkpoint — 18/09/2026

Owner-approved PR #138 merged final verified head `949e9dd97ce805eed43811ce633739ed1934c1c2` to canonical `main@a9a3578516971abd9a9223c1e8dac69da5657bea`. Theme metadata is `1.3.5`. The dedicated Hero Page remains the preferred source; upgraded sites with no valid mapping may retain the previous public WordPress-native Hero projection so the presentation does not disappear. This does not create a parallel domain store or provider heuristic.

Final-head 25/25 workflows completed SUCCESS. Fresh exact-main `V1 Exact Main Verification` run `35335680576` and `X6 Cross-surface Release Closure` run `35335680518` completed SUCCESS. Y5 deterministic `aznet-theme-1.3.5.zip` contains 145 files, 108 packaged PHP files lint PASS and SHA-256 `ec9cf5c35dd3de8c7cff5673c5e3f392abdc3894cd676738e746b354c70e1564`. Publication/tag and verified production deployment remain historical `v1.3.0`. Evidence: `docs/evidence/V1_3_5_HERO_COMPATIBILITY_20260918.md`.

### Law 01 Hero / Trust / Services visual-parity closure checkpoint — 18/09/2026

The product owner approved the supplied law-firm homepage reference as the visual target for the first Law 01 presentation band. PR #139 implemented only Theme-owned presentation changes and merged final verified head `b7947bf2722db6bde8fd1c29ae377318b8e99c9c` to `main@ffafe55b90db1d5313b36a5151110ca1e3f67789`.

The verified target now includes a 48/52 wide-desktop Hero split, compact four-item trust strip and Services geometry of 6-up wide desktop, 3+3 compact desktop and 1-up mobile. Final-head 24/24 workflows completed SUCCESS; Homepage Composer Law 01 Browser Quality passed the 1440/1024/390/320 matrix. Fresh exact-main V1 run `35343960510` and X6 run `35343960430` completed SUCCESS.

This decision is presentation-only. RootProfile-backed authoritative Team membership remains BLOCKED under issue #112; the Theme must not infer Team identity/membership from WordPress users/authors/slugs/URLs or private provider storage. Evidence: `docs/evidence/LAW01_VISUAL_PARITY_HERO_SERVICES_20260918.md`.

### Law 01 About/Profile + Latest visual-parity closure checkpoint — 18/09/2026

PR #141 implemented the second approved Law 01 visual-parity slice using Theme-owned presentation only and merged final verified head `0a2826dfc52343f187dc6198d61d83a6608b62d4` to `main@afbef3d0adc39e4b1595cf2f3506f58bb567bcff`.

The accepted presentation state now uses a dedicated 44/56 About editorial band, a distinct Team presentation band and a three-card Latest Posts row with 3:2 media on wide desktop plus responsive stacking. The existing WordPress-owned Team Page/direct child Page fixture remains presentation input only and is not promoted to authoritative identity/membership.

Final-head verification completed **25/25 workflows SUCCESS** with zero failure, including Homepage Composer Law 01 Browser Quality, D-027 Standalone Core L4 and V1/X6/R6 retained gates. The intermediate D-027 contrast failure was reproduced and fixed in the shallow CSS layer before final closure. Evidence: `docs/evidence/LAW01_PROFILE_LATEST_VISUAL_PARITY_20260918.md`.

RootProfile-backed authoritative Team integration remains BLOCKED under issue #112. No architecture/public-contract change is introduced.

### AZnet Theme 1.3.6 client-delivery candidate checkpoint — 18/09/2026

The product owner approved creation of the newest installable Theme package for client delivery. PR #143 uses a patch promotion from 1.3.5 to 1.3.6 because the current client-delivery bytes include the completed Law 01 visual-parity work merged after the earlier 1.3.5 package.

The release slice followed RED -> GREEN. RED head `120cd35e7d245f6a7e564f544df76d987fec2ddd` failed the intended Y5 1.3.6 metadata assertion. Minimal GREEN promoted only `style.css` and `AZNET_THEME_VERSION`. Exact production head `7db436b316ef5a9f9264fa9dd71f6cfad4d7ad80` completed 14/14 triggered workflows SUCCESS with zero failure, including Y5, X6, V1, Release Version Consistency, D-027 Exact Package/retained regression, R6 and retained browser/runtime gates.

Deterministic package `aznet-theme-1.3.6.zip`: 145 files; 108 packaged PHP lint PASS; SHA-256 `25271cd4c68f0bc88e236f084acb73d7661a618d1ae1d737f0f2a5202307a84a`. Y5 run `35353852165`, promoted-package artifact `10550649207`, artifact digest `sha256:7efe3e575a46950e85abd703bad87e05daac32bd2346265fa2e148569de48cfc`.

This checkpoint does not change Theme/provider ownership and does not authorize tag/GitHub Release or production deployment. RootProfile Team remains BLOCKED at issue #112. Evidence: `docs/evidence/V1_3_6_CLIENT_DELIVERY_20260918.md`.

### Law 01 screenshot-reference parity candidate checkpoint — 18/09/2026

After canonical main advanced to `main@9781d29a9d1ba32a643ffe3cfe9d47c8c4ba4c1c` with Theme metadata `1.3.6`, the product owner clarified that the supplied Tâm Đức - Hà Nội homepage screenshot is the visual authority for this Law 01 presentation slice. The earlier intermediate client-delivery PR #146 was closed unmerged as superseded.

PR #147 is the replacement bounded presentation candidate. It preserves full-width section surfaces while aligning Header, Hero inner content, Trust, Services, Profile, Latest and Footer to one shared `96rem` shell. Wide desktop keeps a 48/52 Hero; Services remains six compact cards; About + Team share a 48/52 band; Team retains four presentation slots with CTA below; Latest remains three cards; Header/Footer support WordPress logo + site-title lockups; Hero may reuse the WordPress-owned Header Utility menu for phone/hotline presentation.

The screenshot contains business statistics, but this decision does not authorize fabricated values. Such values remain omitted unless supplied by a legitimate public owner/source.

Exact production head `aacd6fe4abc4d5f3d49b6bfd85570df66688db46` completed **18/18 triggered workflows SUCCESS, 0 failure**. Law 01 browser run `35363025279` passed 1920/1440/1024/390/320 coverage. Artifact `10555328476`, digest `sha256:4c0af3b17d8d38768f994f2dacd3dbe20f5da5b693ec28c331df7fc632d520ba`. Evidence: `docs/evidence/LAW01_REFERENCE_PARITY_FINAL_20260918.md`.

This is a presentation-only decision. RootProfile authoritative Team membership remains BLOCKED under issue #112; the existing WordPress Page/direct-child fixture is presentation input only.

### Law 01 screenshot-reference canonical merge closure — 18/09/2026

PR #147 merged final verified head `225e36af27193358b2a252ac7edce4d9867d61ba` to `main@f608c7ec98587b61da570871bb304a9e76d09bbd`. Final-head verification completed **21/21 workflows SUCCESS**; Git comparison shows zero file delta between the verified head and merge commit.

No fresh post-merge exact-main workflow is claimed. Theme metadata remains `1.3.6`; published GitHub Release remains `v1.3.0`. A new installable package/version is a separate approval-gated slice. Evidence: `docs/evidence/LAW01_REFERENCE_PARITY_MERGE_CLOSURE_20260918.md`.

### AZnet Theme 1.3.8 final client-delivery candidate checkpoint — 19/09/2026

The product owner explicitly approved creation of a new installable package after the Law 01 screenshot-reference closure. PR #149 promotes canonical Theme metadata from 1.3.6 to 1.3.8. The 1.3.7 identity is intentionally skipped because a 1.3.7 package had already been generated from a superseded, unmerged intermediate candidate; reusing that version for different bytes would weaken provenance.

The release slice followed RED -> GREEN. RED head `5f188f3fcca6fd32122a1799aff703d4837b89e9` failed the intended Y5 1.3.8 metadata assertion. Minimal GREEN changed only the two Theme version declarations and extended Y5/X6 promotion guards for the exact `1.3.6 -> 1.3.8` boundary.

Exact production head `5f3dcf00f0a619b58a966078b2cd969cb7fec45d` completed **14/14 triggered workflows SUCCESS, 0 failure**. Deterministic package `aznet-theme-1.3.8.zip`: 145 production files, 108 packaged PHP lint PASS, SHA-256 `0aba4cf0e213150319c8288d62d06cb136481670cd8f53fd0edbfd9276a03238`. Y5 run `35403751052`; promoted-package artifact `10571900478`; artifact digest `sha256:2f8b380f8ffa6e88d481d99a14ea1d248f86243ebd1c213bdccb49fda0fc9da4`.

This checkpoint does not change Theme/provider ownership. RootProfile authoritative Team remains BLOCKED at issue #112. Tag/GitHub Release and production deployment remain separate explicit approval gates.

### Law 01 seamless-Hero correction and AZnet Theme 1.3.9 client-delivery candidate — 19/09/2026

The owner clarified a visual requirement not fully captured by the prior reference-parity slice: the Hero must not expose visible boxed boundaries between the text/image region and the page background. The section surface stays full-width; the shared 96rem shell still owns content alignment; the text column no longer paints a separate background; the image surface bleeds to the viewport edge. On mobile, the stacked image surface reaches both viewport edges.

Visual RED head `ffd2c8e48498bf0849ec8fa022feddb52da80806` failed the intended no-boxed-background assertion. Exact visual head `8cf81cb0ec7955d7605e32913aa36af7fe31b20d` completed 14/14 workflows SUCCESS after the retained 48/52 check was correctly measured against the shared grid rather than the bleeding image width.

Because 1.3.8 is already canonical and packaged, the improved bytes are promoted to 1.3.9. Release RED head `886c3d751326d16a90bddfa54bcaafa7b9af31d4` failed the intended 1.3.9 metadata contract. Exact production head `b4ead6ea541074fa76b1657d20de07828c5bfde9` completed **19/19 triggered workflows SUCCESS, 0 failure**.

Deterministic package `aznet-theme-1.3.9.zip`: 145 production files, 108 packaged PHP lint PASS, SHA-256 `33a70a1d63ae96c21d6efe381f8a0fea299ac0c14a49df56c5edfe8c2eb9404a`. Y5 run `35406199947`; promoted-package artifact `10571874751`; artifact digest `sha256:892e96890c189950000367c886bccd70b697de6c0ed442bcbb3527480ae7d9d9`.

This is Theme presentation only. RootProfile authoritative Team remains BLOCKED under issue #112. Tag/GitHub Release and production deployment remain separate explicit approval gates.

### Law 01 Hero typography/transition polish + AZnet Theme 1.3.10 candidate — 19/09/2026

The owner approved a bounded visual polish after the seamless-Hero correction: retain the full-width section and shared-shell geometry, but improve the title's editorial rhythm and soften the cream-to-image transition so the image no longer reads as a hard pasted rectangle.

Visual RED `f95fba80020db53c126c41fe52f6fa8020c516eb` failed the intended title-polish assertion. Minimal production change tightened title measure/line-height/tracking, added a non-interactive cream-to-transparent transition overlay at the image edge and a subtle image crop. Browser geometry/no-overflow checks remain retained.

Because 1.3.9 had already been packaged from the still-unmerged candidate before this extra polish, it is superseded and not reused. The current package is 1.3.10, with canonical promotion guarded as `1.3.8 -> 1.3.10`.

Exact production head `a4c3621ec6d651b7e4c4d1085a2b900f8c94454b` completed **22/22 triggered workflows SUCCESS, 0 failure**. Deterministic package `aznet-theme-1.3.10.zip`: 145 production files, 108 packaged PHP lint PASS, SHA-256 `9eb8a3ad1c24b7e75a695199de3e45bf3e196bebd7fff8774f7390969283c92d`. Y5 run `35410299118`; artifact `10574586211`; digest `sha256:dadb066567c2b6c6d3c1f28f338a22441903fed03a55ad47433ac2d164f58132`.

This remains Theme presentation only. RootProfile authoritative Team remains BLOCKED under issue #112. Tag/GitHub Release and production deployment remain separate explicit approval gates.

## 14. Exact next

**NEXT — preserve canonical `v1.3.21` technical PASS and current public `v1.3.20` publication boundary. `v1.3.21` publication remains separate. Any production deployment remains a separate explicit gate and must revalidate authenticated target-site state plus rollback before mutation. RootProfile Team remains externally blocked at issue #112.**


### Canonical 1.3.10 merge and Homepage Hero editing UX candidate — 19/09/2026

The product owner approved PR #150 canonical merge. Verified 1.3.10 polished-Hero bytes merged to `main@55ca349c8808d0d6a3c96d5201983ae966b1d822`; fresh exact-main V1 run `35412045895` and X6 run `35412045904` completed SUCCESS. This closes the PR #150 merge gate only; tag/GitHub Release and production deployment remain separate gates.

The owner also approved proceeding with the follow-up Hero authoring UX slice. PR #151 preserves the accepted WordPress-content ownership model: no Hero title/subtitle/body is copied into `aznet_theme_settings`. The Theme exposes the typed `homepage_hero_page` selection, current-source/fallback status, mapped-content preview and native WordPress Page edit/create actions.

TDD RED was recorded at `2d63c0c2b1d993e63ccb0a22efcdacd6fc16a373`. After reconciliation with canonical 1.3.10, exact production/test head `82073e715be349f29fe84dbd0405c5af6e1b5c93` completed 19/19 triggered workflows SUCCESS at final disposition. R2 Pattern Library run `35412090864` required one retry after a disposable-runtime `net::ERR_CONNECTION_RESET`; retry attempt 2 passed the previously failing Woo-present step and the remaining gates with no byte changes.

PR #151 canonical merge remains an explicit owner approval gate. This candidate does not authorize publication or deployment and does not alter RootProfile/ConvertFlow/WooCommerce ownership.


### Homepage Hero editing UX canonical merge checkpoint — 19/09/2026

The product owner approved PR #151 canonical merge. Final verified head `b17532c0bc7348317c5044fa25262d28cb9fef54` merged to `main@39df23c3385118888e262c27a2331de34dda430d` with zero file delta from the verified head.

Final-head QA: 22/22 triggered workflows SUCCESS. Fresh exact-main V1 run `35412829723` completed SUCCESS on exact merge SHA `39df23c3385118888e262c27a2331de34dda430d`. Artifacts: static `10575125302` (`sha256:0fae4559000ac195e6f58a5fd50b1070b232c74bd472dd90c36d6abb0a15cb92`) and clean runtime/browser `10575290283` (`sha256:a2609a4ffee6f7c538402a4850103d5c14f6baa51fe6dceea4f61030ecba9e89`).

No release/deployment authorization is created by this merge. RootProfile authoritative Team remains separately blocked at issue #112.


### Post-closure state — 19/09/2026

Owner-approved source/evidence PR #152 merged to `main@27e6a2bc3d68bfa4aa51b22c0e1fda2867cec174`. The PR was documentation/evidence only, completed 7/7 triggered workflows SUCCESS and has zero file delta between its final head and merge commit. The implementation verification anchor remains PR #151 merge `main@39df23c3385118888e262c27a2331de34dda430d` plus fresh exact-main V1 run `35412829723` SUCCESS.

No new Theme implementation slice is opened by this closure. Release/deployment/takeover remain separately approval-gated; RootProfile Team remains externally blocked at issue #112.


### AZnet Theme 1.3.10 publication — 19/09/2026

The product owner explicitly approved release 1.3.10. Isolated publication run `35413516574` completed SUCCESS against exact verified implementation source `39df23c3385118888e262c27a2331de34dda430d`.

The publication workflow rebuilt the exact post-PR #151 package twice and verified deterministic byte identity, 145 production files, 108/108 packaged PHP lint, exact source/package identity, clean WordPress 6.9 package activation/render, annotated tag and GitHub Release state, and published-asset SHA rehash.

Published state:
- annotated `v1.3.10` tag object `c4d295acb6e849415ac5a228656e361660298e12`;
- tag target `39df23c3385118888e262c27a2331de34dda430d`;
- GitHub Release `391877350`;
- asset `573847909` / `aznet-theme-1.3.10.zip`;
- published SHA-256 `5ff6c4862839dcdb6dfbbb77a71cb8bd87259b83cfbf308aa6b04279396bd572`;
- publication evidence artifact `10574826580`, digest `sha256:822d07e3908400c0357d9497695ea88fb82468c684944a9d95cc24aa49a14e21`.

The earlier pre-PR #151 1.3.10 candidate package is superseded and not reused as the release asset. Publication does not deploy any production site. The currently verified production deployment remains v1.3.0 until a separate owner-approved v1.3.10 deployment operation is executed and independently reverified.


### D-030 Homepage Hero Library approval — 19/09/2026

The product owner rejected the dedicated-Page authoring friction for a Homepage-only Hero and approved a Hero Library model.

Accepted boundary:
- WordPress owns Hero content in one normal `wp_block` synced-pattern entity made only from Core blocks.
- Theme owns Hero visual variants/library, the typed block reference and presentation composition.
- `homepage_hero_block` + `homepage_hero_variant` are additive settings in the existing `aznet_theme_settings` schema; schema version remains `3`.
- New authoring UX must not require a dedicated Page.
- Ordinary Theme settings save and preset selection remain content-mutation free.
- Initial Hero block creation is allowed only through an explicit user action protected by capability + nonce and implemented through public WordPress APIs. The created block starts as `draft`; the current public Hero remains on the legacy/fallback path until the user publishes that block.
- Variant changes must not rewrite WordPress Hero content.
- Legacy `homepage_hero_page` and the older Site/Front Page projection remain compatibility fallbacks; neither is auto-deleted or silently migrated.
- No proprietary block type, page-builder schema, provider storage read or domain ownership transfer is introduced.

Implementation is a new post-release candidate and must not mutate the already-published v1.3.10 release identity. The implementation metadata target is 1.3.11 unless a later release gate explicitly changes it.


### D-030 authoring completion refinement — 19/09/2026

The product owner approved a corrective authoring refinement after live preview exposed two issues: avoidable Hero-title wrapping caused by narrow Theme-owned width caps, and legacy visible Hero copy being assembled from multiple unrelated WordPress sources.

Accepted refinement:
- Hero title layout must use responsive available-width rules; no client-specific line breaks and no narrow `ch` cap that wastes an otherwise available copy column.
- The primary Hero Library path remains one WordPress-owned synced `wp_block`, but its scaffold must now cover the complete user-editable Hero surface: eyebrow/label, H1, value/supporting copy, CTA labels/links, media and trust/supporting messages.
- Theme-owned code may provide structure, icons/decoration, variants and responsive presentation only; it must not own the authoritative text for those Hero fields.
- Site Title, Front Page title, Site Tagline and generic legacy trust strings remain fallback/compatibility sources only. They must not remain dependencies once a valid published Hero block is configured.
- Control Center must identify the synced Hero as the single editing destination for the configured Hero.
- Existing draft-first creation, no-auto-migration, no-auto-delete, variant-does-not-rewrite-content, and theme-switch content-retention rules remain unchanged.
- Because `v1.3.11` is already published and immutable, this corrective implementation advances candidate metadata to `1.3.12`; it does not rewrite the published `v1.3.11` artifact.

Implementation/QA is tracked in PR #159. Publication and production deployment remain separate owner gates after implementation merge and exact-main verification.

### D-030 corrective v1.3.12 publication closure — 19/09/2026

Owner-approved corrective release `v1.3.12` is published.

- PR #159 final head `4b8fc7c78fb17e32fbc9c4fb9399bdeaf3f98744`: 28/28 workflows SUCCESS.
- Canonical merge: `main@7c73f7e050e95c23c5c968b2365b643753431246`; final-head -> merge comparison has zero changed files.
- Fresh exact-main V1 run `35434502224`: SUCCESS; X6 push closure `35434502232`: SUCCESS.
- Annotated tag `v1.3.12` object `dcdca2f199049bbb025dce18e8f9b322b6a46c00` dereferences to the exact merge commit.
- GitHub Release `392012403`, asset `574476748` `aznet-theme-1.3.12.zip`: 147 production files, 110 packaged PHP lint PASS, SHA-256 `e9299f51bcedb6da781ab60c6c1570ebf7f2633557040ed3368389483b0078b6`.
- Final publication/readback run `35434785751`: SUCCESS; evidence artifact `10581698277`, digest `sha256:4e6e98a56873b9080d634b12dfc0417f7060a8325ea5e749133d2af0e1e7330b`.
- Publication does not authorize production deployment. Fresh read-only state on current target `lstamduchn.vn` remains active Theme `1.3.10`; older `tamduchanoi.aznet.vn` records are historical only.

Evidence: `docs/evidence/V1_3_12_PUBLICATION_20260919.md`.

### D-030 canonical merge closure — 19/09/2026

Owner-approved PR #156 merged final head `c8a14556706a49bb380669b72bffd5b6f0cd98d5` to `main@01ddbff1843dfd8a0e1cc276be34144f35aeba35` with zero changed files between final head and merge commit. The final head completed 37/37 triggered workflows SUCCESS. Fresh `V1 Exact Main Verification` run `35422599658` completed SUCCESS on the exact merge SHA.

The accepted D-030 ownership model is preserved: WordPress owns synced Hero content/media; Theme owns typed reference, visual variant and rendering composition only. Draft-first initialization, published-block precedence, variant reuse without content rewrite and legacy Page/Site fallback are retained. The known WordPress-core `WP_Query::rewind_posts()` warning remains separately classified rather than converted into a blanket clean-log claim.

Y5 run `35422400222` produced deterministic candidate `aznet-theme-1.3.11.zip`, 147 production files, 110 packaged PHP lint PASS, SHA-256 `723f502f81033136410a49ee1196ff098837b20edbdadaf754cadf522efe9fb0`. This closes D-030 technical implementation/merge only. Tag/GitHub Release and production deployment remain separate owner gates. Evidence: `docs/evidence/HOMEPAGE_HERO_LIBRARY_D030_MERGE_CLOSURE_20260919.md`.


### v1.3.11 publication closure — 19/09/2026

Owner-approved publication is PASS. Annotated tag `v1.3.11` targets exact verified D-030 implementation `01ddbff1843dfd8a0e1cc276be34144f35aeba35`; GitHub Release `391924224` is public/non-prerelease; asset `574135564` is `aznet-theme-1.3.11.zip` with SHA-256 `723f502f81033136410a49ee1196ff098837b20edbdadaf754cadf522efe9fb0`. The package has 147 production files and 110/110 packaged PHP lint PASS and activated/rendered cleanly on WordPress 6.9.

Initial publication run `35423516185` created correct immutable tag/package bytes but malformed release-note inline literals via shell command substitution. Repair run `35424304502` corrected the notes idempotently and reverified tag/release/asset bytes without changing release identity. Temporary workflow cleanup leaves zero net file delta. Publication does not authorize production deployment. Evidence: `docs/evidence/V1_3_11_PUBLICATION_20260919.md`.


### Law 01 v1.3.15 technical closure — 20/09/2026

Owner-approved PR #167 closed the remaining source-safe Law 01 demo Team presentation and promoted the technical candidate to Theme metadata `1.3.15` without changing domain ownership.

- Final head `a47e2f371db3de6c85eb43f34db7ae360f482458`: 24/24 exact-head checks SUCCESS.
- Merge: `main@6cf48d1653d6468ec5aa25761c1a5b1dc82d4aa3`; final-head -> merge comparison has zero changed files.
- Exact-main V1 run `35473959243`: SUCCESS.
- Exact-main X6 run `35473959238`: SUCCESS; 32/32 integrated browser/axe cases PASS.
- Deterministic package: `aznet-theme-1.3.15.zip`, SHA-256 `8f91e9bd3a40a7eae4e6984a77f2735f339d188e52cfc6f97fac57a96bfcecd8`, 148 production files, 111 packaged PHP files.
- X6 artifact `10593342179`, digest `sha256:e2561025314574c12ad63af1fd9b4db80e9c78165e833e850fbd490075ddf5f3`.
- RootProfile Team membership remains BLOCKED_EXTERNAL_CONTRACT under open issue #112; no workaround or parallel authoritative Team store is authorized.
- Latest public GitHub Release remains `v1.3.12`. Publication of `v1.3.15` and production deployment are separate owner gates.

Evidence: `docs/evidence/V1_3_15_TECHNICAL_CLOSURE_20260920.md`.


### Law 01 v1.3.15 publication closure — 20/09/2026

Owner-approved GitHub publication is PASS.

- Exact release source: `6cf48d1653d6468ec5aa25761c1a5b1dc82d4aa3`.
- Publication workflow: `35474494290` — SUCCESS.
- Annotated tag `v1.3.15` object `a0895fc339b1020d7147b00eac243340691fde23` -> exact implementation commit.
- GitHub Release `392243619`, asset `575623879` `aznet-theme-1.3.15.zip`.
- Public asset SHA-256: `8f91e9bd3a40a7eae4e6984a77f2735f339d188e52cfc6f97fac57a96bfcecd8`.
- Package: 148 production files, 111 packaged PHP files.
- Publication evidence artifact `10593402386`, digest `sha256:cf35664008ef75b2a93dca455e14987c571949e826d0bda97b000ad86f6dff67`.
- Temporary workflow cleanup `efcfa1cd3a64470808f98d01ec9911dddd87298b`; cleaned ops branch has zero net file delta from publication-time `main@79fcf11f043129a4ce82629a77fc1267802b2bba`.
- Production deployment is not authorized or claimed by this publication step.
- Fresh post-publication live readback is UNKNOWN because WPVibe hit its rolling daily fair-use cap; prior Theme `1.3.10` remains the last verified production state, not a fresh observation.
- RootProfile authoritative Team membership remains BLOCKED_EXTERNAL_CONTRACT under issue #112.

Evidence: `docs/evidence/V1_3_15_PUBLICATION_20260920.md`.


### v1.3.15 lstamduchn.vn deployment preflight — BLOCKED_EXTERNAL_ACCESS — 20/09/2026

Owner-approved production deployment was stopped safely at preflight.

- Initial authenticated read-only preflight run `35474904344`: FAILURE before login due HTTPS navigation timeout.
- Network diagnostic run `35475064743`: DNS PASS; TCP/80 and TCP/443 reachable; HTTP redirects to HTTPS; TLS handshake stalls after ClientHello on each observed IPv4 backend.
- TLS isolation run `35475224372`: forced TLS 1.2 and TLS 1.3 both time out on all three IPv4 backends.
- WPVibe authenticated fallback: temporarily unavailable due rolling daily usage cap.
- Production mutation: none.
- Fresh active Theme version: UNKNOWN.
- Last verified live Theme: `1.3.10`, retained as historical operational evidence only.
- Safe rollback identity remains UNKNOWN until current live version is freshly verified; published `v1.3.10` is not assumed to be current.
- RootProfile issue #112 is unrelated and remains separately external-blocked.

Evidence: `docs/evidence/V1_3_15_LSTAMDUCHN_DEPLOYMENT_PREFLIGHT_BLOCKED_20260920.md`.


### v1.3.21 About Page technical closure — 20/09/2026

The product owner approved the refined About / Giới thiệu presentation and explicitly removed WPVibe as a required repository release-verification dependency. PR #196 merged the scoped icon-led presentation to `main@aab636d3fe1c21be924ba62ea30b7a910cf2e019`; WordPress remains owner of Page content and the Theme adds presentation only.

Release PR #197 followed RED -> GREEN. RED head `d7d91627d325dc76433c9e693140fcd37192ee17` failed the intended Y5 `1.3.21` metadata assertion. Exact GREEN head `f7107f301485839d361fd00e9cb11c0837074434` completed 14/14 triggered workflows SUCCESS and merged to canonical `main@998a8dcf342b09824b197059524c50c013dba828`.

Fresh exact-main V1 `35518082825` and X6 `35518082833` completed SUCCESS. X6 browser/axe passed 32/32; deterministic `aznet-theme-1.3.21.zip` has 158 files, 113 packaged PHP lint PASS and SHA-256 `da695f2b33aafc38172a9a412a3b1afd16263db3f03d305be4304eba877e09e2`. This closes `v1.3.21` technically only. Current GitHub Release remains `v1.3.20`; no `v1.3.21` publication or production deployment is claimed. Evidence: `docs/evidence/V1_3_21_ABOUT_PAGE_TECHNICAL_CLOSURE_20260920.md`.

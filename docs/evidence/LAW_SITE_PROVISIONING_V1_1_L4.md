# Law Site Provisioning v1.1 — L0-L4 and Candidate Evidence

**Date:** 2026-09-09  
**Implementation branch:** `work/law-site-provisioning-v1-1`  
**Stacked base / PR:** `work/law01-premium-presentation` / PR #61  
**Verified production/test/package head:** `d564c6e8a43f458a493fe816c4fc768d4917065f`  
**Post-verification CI-only retirement head:** `a64053ced9c64e16a771bed7695d63437bd3838c`  
**Theme candidate:** `1.1.1`  
**Decision / source gate:** AZT-04 D-026 Accepted; AZT-02 v0.8

## PASS

### L0 — Source / ownership

- `law01-v1-1` is an additive WordPress-native setup slice. It does not transfer ongoing WordPress content/publication ownership to the Theme.
- Recommendations are proposal-only until the authorized user confirms the visible change plan.
- No title/slug/URL/fuzzy/synonym heuristic establishes authoritative semantic mapping.
- Starter Pages, Posts, Categories and imported Attachments become normal WordPress-owned objects after successful provisioning.
- New/mostly-empty-site starter publication is coupled to explicit WordPress-native search-visibility consent (`blog_public=0`).
- Existing active sites never receive whole-site noindex from this workflow; starter Posts fail safe to Draft when no public per-Post SEO publication contract exists.
- No Rank Math/Yoast/private SEO storage writes, no external provider private API/storage reads, and no fake lawyer/person/credential/current-news claims are introduced.

### L1-L2 — Static / contract / TDD

Fresh exact-head `Law Site Provisioning Candidate Package` run `34362997863` checked out `d564c6e8a43f458a493fe816c4fc768d4917065f` on PHP 8.1.34 with mbstring/mysqli and completed `scripts/verify-v1-core.sh` successfully.

Fresh results include:

- production PHP lint: 93 files PASS;
- retained v1.x core, WooCommerce presentation, editorial, Homepage Composer/Law 01 and Classic Editor contracts PASS;
- v1.1 provisioning activation/discovery/recommendation/editorial/media/plan/runner/idempotency/readiness/indexing/admin-a11y contracts PASS;
- deterministic double package build PASS;
- compressed-data integrity PASS;
- exact-byte unpacked/source comparison PASS for 121 packaged files.

The v1.1 verification sequence caught and reduced browser/runtime REDs before the final GREEN head without broadening production ownership:

1. a user-edit runtime fixture initially used an empty synthetic attachment that WordPress would not persist as a valid featured image; the fixture was corrected to use an actually imported starter WebP attachment, after which new/active/rerun/rollback/user-edit/index-restore runtime scenarios passed;
2. fresh WordPress installs contain a published default `Hello world!` Post, so the disposable browser fixture was correctly classified by production code as active. The browser fixture now drafts its default published Post only after proving Theme activation is mutation-free; production classification logic was not weakened by a WordPress-default-content heuristic;
3. the browser coverage assertion initially counted Legal News with the generic `.aznet-theme-law01-article-card` selector, while the production Law 01 News template intentionally uses `.aznet-theme-law01-news-list > article` with dedicated CSS. The regression selector was aligned to the actual presentation contract; production News markup was not changed to satisfy the test.

### L3 — Disposable WordPress runtime

Fresh persistent browser workflow run `34362997879` completed the v1.1 runtime matrix successfully on WordPress 6.9 / PHP 8.1 / MySQL 8:

- new-site starter coverage/media/index safety PASS;
- active-site suggestion/no-overwrite/Draft safety PASS;
- rerun idempotency PASS;
- current-run rollback restores captured content/config/indexing PASS;
- user edits and replacement media are preserved PASS;
- explicit Theme-owned index restore PASS.

Theme activation remains mutation-free in the browser fixture before any disposable-site normalization or provisioning write is performed.

### L4 — Browser / responsive / accessibility

`Law Site Provisioning Browser Quality` run `34362997879` completed successfully on exact head `d564c6e8a43f458a493fe816c4fc768d4917065f`.

The recommendation-first wizard passed through Step 1-4 with proposal-only guidance, explicit plan confirmation, starter publication/index-safety controls and the explicit index-restore handoff. Axe reported no critical/serious blocking violations in the covered wizard/public surfaces.

The resulting Law 01 Homepage was checked at 1440x1000, 1024x900, 390x844 and 320x760. Fresh assertions covered:

- starter Hero rendered from WordPress `/uploads/`, not from a Theme asset URL;
- six Service cards;
- starter Knowledge, Case Analysis and Legal News coverage;
- exactly one H1;
- horizontal overflow = 0;
- Hero, Services and Final CTA viewport-edge full-width geometry while inner Law 01 content remains container-constrained;
- no browser console/page errors under the tested route;
- no critical/serious axe blockers.

Browser evidence artifact:

- name: `law-site-provisioning-browser-quality`
- ID: `10108690457`
- artifact digest: `sha256:a710161a37a31cc5eade956a6d77662ba49224776423baad4e59b35b414beb6e`

The workflow log boundary rejects PHP fatal/parse/uncaught errors and unexpected PHP warnings. The separately known WordPress-core `WP_Query::rewind_posts()` warning (`Undefined array key 0` in `class-wp-query.php` line 3872) remains explicitly classified as an unresolved/non-blocking core-runtime observation and is not converted into a clean-log claim.

## Candidate package

`Law Site Provisioning Candidate Package` run `34362997863` completed successfully on the exact verified production/test/package head.

Inner installable ZIP:

- filename: `aznet-theme-1.1.1-law-site-provisioning-v1-1-candidate.zip`;
- packaged files: 121;
- SHA-256: `51006edeed48c92be0de84926e033a6d081e8fd493b2be7c03223056da784bf9`;
- deterministic double build: PASS;
- `unzip -t`: PASS;
- exact-byte unpacked/source comparison: PASS;
- canonical package directory: `aznet-theme/`;
- package excludes `.git`, `.github`, `docs`, `scripts`, `tests`, `README.md` and `aznet-preview.png`.

Actions artifact:

- name: `aznet-theme-law-site-provisioning-v1-1-candidate`;
- ID: `10108650421`;
- outer artifact digest: `sha256:16eaf047bd5c3c72f5fb15f113fc66d1ec356c33f752793dc3b6ead1e6c67a56`.

The commit immediately after the verified head, `a64053ced9c64e16a771bed7695d63437bd3838c`, deletes only the temporary RED/GREEN workflow `.github/workflows/tmp-v11-tdd.yml` after both persistent gates were GREEN. `.github` is excluded from the installable package, and that CI-retirement commit does not alter production Theme or test bytes verified at `d564c6e...`.

## BLOCKED / UNKNOWN / not implied

- PR #61 remains draft/open/stacked and unmerged.
- No claim is made that canonical `main` contains these candidate bytes.
- No Git tag, GitHub Release or final production deployment is authorized or implied.
- L5 provider certification is not inferred from Theme-owned provisioning/runtime/browser evidence.
- The WordPress-core `WP_Query::rewind_posts()` warning root cause remains UNKNOWN/non-blocking; no blanket PHP-log-clean claim is made.
- Setup Ready does not imply client organization/contact/profile/editorial facts have been reviewed for Launch Ready.

## Rollback

Before successful handoff, failed-run rollback restores captured Theme Homepage settings, Front Page/menu assignment and owned indexing state and removes only objects proven to have been created by that failed run. Reused/pre-existing objects are not rollback-deleted.

After successful handoff, no destructive Theme reset exists. Presentation rollback is `homepage_preset=off` or explicit remapping; package rollback is reinstalling the prior known-good Theme package. Provisioned WordPress content remains WordPress-owned and persists.

## NEXT

Owner-gated next for this stacked candidate is integration/merge disposition. Main merge, tag/GitHub Release and final production deployment remain separate explicit approval gates. P4 real-site pilot certification and external provider L5 claims remain separate from this Theme-owned L0-L4 candidate evidence.

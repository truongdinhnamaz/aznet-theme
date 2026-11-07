# aznet.vn v1.3.98 production publication

Date: 27 October 2026, Vietnam time. Publication-side WordPress widget snapshot timestamp: `2026-10-27T15:50:25.000Z` (22:50:25 Vietnam time). Fresh authenticated post-publish site read at `2026-10-27T15:51:07.891Z`.
Repository: `truongdinhnamaz/aznet-theme`. Canonical release source: `d4ecae7914b2d7e9107dfdf50e7de9d693481d92`.

## Outcome and authorization

**PASS — bounded production update:** `https://aznet.vn` now has active AZnet Theme **1.3.98**, original stylesheet/template slug `aznet-theme`, WordPress 7.1.3, PHP 8.4.26. WordPress Page content/front-page/menu/presentation settings remained intact in the checks below.

After the preceding explicit question covering a recent full hosting backup (files, database, uploads), recovery route and installation of 1.3.98, the owner replied: “xác nhận, bạn hãy xuất bản ngay đi”. This supplies the separate production publication authorization and owner-reported backup confirmation. No independent hosting restore was performed or inferred.

Historical preflight: [AZNET_V1398_PRODUCTION_PREFLIGHT_20261027.md](AZNET_V1398_PRODUCTION_PREFLIGHT_20261027.md), original evidence commit `c81831a25ffc68ad69508c5b5a5639e153d5c78f`. Its prior pending owner gate is superseded by this publication record; historical facts are not rewritten.

## Exact implementation and transport

- Revalidated the official `v1.3.98` release metadata: canonical target commit above, ZIP size 654,161 bytes, SHA-256 `dcca1a3a84707b0ec1ea7c11a75870ece44fba6e96dd0b75494c052b47341360`. Pre-update native Page fingerprints matched the preflight.
- A direct WPVibe CLI install of the official GitHub ZIP URL returned `Theme not found` without changing production. Read-only inspection of the installed WPVibe `handle_theme_install` confirmed it sanitizes the input as a slug and queries `themes_api`; it supports WordPress.org theme identifiers, not arbitrary ZIP URLs.
- Created a new WPVibe draft by cloning active 1.3.97. No pre-existing draft was listed. Applied exactly the two approved canonical runtime edits: `Version: 1.3.97 → 1.3.98` in `style.css`, and `AZNET_THEME_VERSION` `1.3.97 → 1.3.98` in `functions.php`.
- Fresh preview HTML had one unchanged H1, correct homepage canonical, Brand Light CSS `1.3.98-3a8d924cefc8`, exclusively 1.3.98 Theme asset versions, and zero visible PHP error markers. Candidate `style.css`/`functions.php` matched canonical 1.3.98 returned source exactly after normalizing the WPVibe draft name.
- Published through the dedicated WPVibe `publish_draft_theme` tool. `saved_customizations=keep` preserved existing WordPress Site Editor customization ownership during this metadata-only update. Native Global Styles Post 43748 contained only the WordPress user-theme JSON marker, no authored styles, before publication.
- The tool reported successful publication to `aznet-theme` and creation of `aznet-theme-wpvibe-backup` from the previous live Theme. It also reported automatic Rank Math sitemap cache and WP Rocket purge, plus object-cache flush.
- This was a **site-clone patch publication** using the exact official runtime delta. The complete official ZIP was not transferred/replaced on the host. Full installed-tree identity against all 242 official ZIP entries remains unproven; do not label the site archive with the official ZIP SHA or claim a full 242-file byte attestation.

## Fresh post-publication verification

### Version, public assets and recovery

- Authenticated `site_info`: active Theme 1.3.98 under original `aznet-theme` slug; expected PHP templates present.
- Active `style.css` and `functions.php` re-read from `wp-content/themes/aznet-theme` match the exact canonical 1.3.98 returned text, including restored original Theme name.
- `theme list`: active 1.3.98; inactive `aznet-theme-wpvibe-backup` 1.3.97; no remaining WPVibe draft.
- Public homepage loads the original Theme path, without a draft path, with Brand Light CSS `?ver=1.3.98-3a8d924cefc8`. The CSS fingerprint itself is unchanged from pre-update.
- Existing WordPress Global Styles ownership was preserved. No plugin/domain data was directly accessed or mutated by the patch.

### Native WordPress continuity

Semantic JSON comparison was used; CLI timings were excluded from comparisons.

- `page_on_front=43515` retained.
- Page IDs **2**, **18897**, **43515** retain pre-update status, modified GMT, character counts and MD5 content fingerprints.
- Theme `aznet_theme_settings`, all native menu assignments, logo and Custom CSS reference remain unchanged.
- The only Theme-mod difference is the timestamp in WordPress's `sidebars_widgets` snapshot: `1793017696 → 1793116225`. Its nested widget placements/data are identical. This is recorded as a native publication side effect, not hidden as an entirely byte-identical Theme-mod set.
- No Page/Post/Menu/Media edits, SEO configuration edits, commerce actions or provider domain workflows were requested or performed.

### Five public rendered HTML checks

| Surface | URL | Returned HTML chars | H1 | Theme assets | Visible PHP errors |
| --- | --- | ---: | --- | --- | ---: |
| Homepage | `https://aznet.vn/` | 127081 | Xây dựng website tăng trưởng thực sự | 1.3.98 | 0 |
| About | `https://aznet.vn/gioi-thieu` | 108968 | Giới thiệu về AZnet | 1.3.98 | 0 |
| Products | `https://aznet.vn/san-pham` | 77059 | Sản phẩm | 1.3.98 | 0 |
| Cart | `https://aznet.vn/gio-hang` | 135851 | Giỏ hàng | 1.3.98 | 0 |
| Account | `https://aznet.vn/tai-khoan` | 72393 | Tài khoản | 1.3.98 | 0 |

Homepage title and canonical retain `AZnet – Thiết Kế Và Phát Triển Website Doanh Nghiệp` and `https://aznet.vn`.
The observed About canonical points to the homepage, matching the previously documented independent SEO-owner issue; this publication does not correct or certify it.

The three commerce URLs were observed in the live Homepage's native links before their read-only HTML checks. No cart submissions, login, order, payment or email delivery was tested.

## QA claim boundary and recovery

**PASS:** L0 source/version reconciliation; exact two runtime-file patch/readback; bounded authenticated production runtime/version readback; native content/settings continuity; five fresh public HTML projections; 1.3.97 Theme backup existence/version.

**UNKNOWN / NOT CLAIMED:** entire installed Theme/archive SHA; full DB/media/host restore rehearsal; fresh pixel/desktop/mobile/a11y browser matrix; provider L5 certification; commerce/login/email transaction correctness; secondary-page legacy-content repairs. Retained release CI/disposable WordPress QA remains separate evidence and was not needlessly rerun for this metadata-only site patch.

Recovery reference is the freshly retained `aznet-theme-wpvibe-backup` 1.3.97 and owner-confirmed hosting backup route. On a material regression, restore the captured Theme directory to the original `wp-content/themes/aznet-theme` path; preserve native settings/content and avoid switching to a different backup stylesheet slug. No rollback was needed in the bounded checks.

No main merge, Git tag or release alteration occurred in this publication slice.

**EXACT NEXT:** Keep 1.3.98 as the verified live version and its 1.3.97 backup available. Any already reported secondary-page defects need their own reproduction and ownership-safe repair slice; the version-only publication does not resolve them.

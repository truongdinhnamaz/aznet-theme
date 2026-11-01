# ANP live Theme filesystem inventory — THREE Wellness 01 paths outside canonical main (2026-11-01)

## Scope and authority

This is a **read-only, path-level inventory** of the *active* theme at `anpvietnam.vn`, independently inspected using the authenticated WordPress connector's `list_files(scope=wp-content, directory=themes/aznet-theme, offset=0/200)`. It is NOT the separate `aznet-theme-wpvibe-draft` namespace. Confirmed active Theme is `aznet-theme` v1.3.98.

Canonical references:
- GitHub `main` `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405` / tree `328bd2b2a7af321cc49340bca3d05550ecad8e57`.
- PR #552 source `fc6725654ab20767fecd17412416b2ad12a6c2bd` / tree `ccfcdfc51f78832383cfb3824ad9273a7492ad7b`, Draft, unmerged.
- PR #544 source `1ddb779bae2ab8f3ec715b6ad753305882e49d6d`, Draft, unmerged.
- Existing preservation branch `work/anp-wellness01-draft-text-preservation-20261101` commit `1b2ee7439159d4684130cd84be86ccd8eb6752f5` stores readable text reconstructed from separate draft responses.

## Inventory reconciliation (paths, not bytes)

Existing Theme package builder inclusion/exclusion rules applied to GitHub `main` untruncated recursive tree yield **242** production blob paths. Of these, **223** have file extensions readable/listable by WPVibe's `wp-content` source API (.php/.css/.js/.json/.txt/.html). The remaining **19** production paths are 8 SVG, 6 WOFF2 and 5 WebP binary assets, whose active presence/byte identity is **UNKNOWN** under this connector. (They are present in GitHub source.)

Live read-only WPVibe directory listing: **226** visible source paths (pages 1–200 and 201–226), unique **226**, no sign of truncated pagination. Reconciliation against the **223** expected text source paths:
- **223/223 expected path names present**.
- **0 expected visible text paths missing**.
- **3 *additional* text paths in the actually active live Theme** which exist neither in `main` nor PR #552:
  1. `assets/css/components/homepage-wellness-01.css`
  2. `inc/theme/templates/wellness-01/manifest.php`
  3. `patterns/homepage-wellness-01.php`

This supersedes any suggestion that Wellness 01 files are *only* in the WPVibe mixed draft. It does **not** establish whether additional Wellness WebP binaries also exist on the active server: the connector excludes binary extensions from `wp-content` listing. Four `assets/starter/wellness01/*.webp` paths are visible in the **separate draft** and PR #544, with unproven live/draft-specific binary hashes.

## Exact text comparisons for the three additional active files

`read_file(scope=wp-content, path=themes/aznet-theme/<relative path>)` vs separate default WPVibe draft `read_file(path=...)`, and separately vs GitHub PR #544 `fetch_file`. Each returned numbered WPVibe block was complete and contiguous.

| File | Live text lines | Live vs WPVibe draft | Live vs PR #544 | PR #544 first differing line |
| --- | ---: | --- | --- | ---: |
| `homepage-wellness-01.css` | 66 | **equal** normalized text | **DIFFERENT** (PR: 69 lines) | 2 |
| `manifest.php` | 35 | **equal** normalized text | equal (35 lines) | — |
| `homepage-wellness-01.php` | 96 | **equal** normalized text | **DIFFERENT** (PR: 97 lines) | 14 |

The earlier isolated preservation commit `1b2ee7439159...` contains normalized text snapshots of these three file versions, now observed in both **active** and **draft** Theme. This preservation is **not a byte-exact active server archive**. No backup/restore assertion about live binaries is possible.

## Updated release/rollback decision

**Hard safety consequence:** replacing the entire active Theme directory with a clean 242-path PR #552 package would OMIT at least these three extra live-owned Wellness files; it could also omit other unknown server binary files. A clean ZIP is a valid **source candidate** but **not a safe full-directory replacement plan** for this particular live installation absent explicit inventory and preservation.

**Preferred bounded deployment after independent backup/authorization:** take and verify a byte-exact snapshot of the actual active Theme directory (including undeclared Wellness binaries) and full MySQL database; then validate a **five-file, allowlisted, atomic or recoverable overlay** of PR #552's changed presentation assets onto that unchanged existing Theme tree, preserving all other active files. This is a **proposed deployment method**, not yet an approved release-contract change or executed action. The exact packaged source must still satisfy required QA; an overlay cannot be declared equivalent to source package testing without separate verification. If only whole-directory install is available, **stop and seek explicit source-ownership/retention decision** instead of deleting the extras or quietly carrying them into the canonical release.

**Rollback default:** restore the exact before-deploy active Theme file snapshot, not only public v1.3.98 release ZIP; preserve site data/WooCommerce orders. A full database restore is a separate exceptional action requiring review of post-backup writes, approval and recovery controls.

No live/admin file edits, no WordPress DB change, no deployment, no draft replacement, no main merge, and no PR #544 modification occurred in this investigation.

## PASS / BLOCKED

- **PASS L0:** complete 226/226 visible source-path reconciliation against 223 expected + 3 extra; all three live Wellness text sources read and content-normalized identical to existing draft; scoped active-vs-main checks for 6 Industrial/header/digital files separately recorded in [ANP_ACTIVE_BASELINE_SOURCE_AND_ROLLBACK_READONLY_20261101.md](./ANP_ACTIVE_BASELINE_SOURCE_AND_ROLLBACK_READONLY_20261101.md).
- **UNKNOWN:** 19 expected baseline binary assets on hosting; 4 Wellness draft WebP files' identity/active presence; exact full live Theme archive, hash and recovery point; source package exact ZIP and runtime/browser test status at the whole-release level.
- **BLOCKED L6:** authorized, timestamped full hosting file + MySQL backup, proven Theme recovery, independently QA'd final method and separately approved publish.

**EXACT NEXT:** independently obtain actual live Theme full-directory archive/hash and database restore-point evidence through authorized 7HOST/cPanel, then test a bounded five-file overlay on a restored isolated copy of that Theme state before any live publish.

# RQA 1.3.97 - Production Theme Backup/Recovery Preflight (Read-only)

**Date:** 2026-10-24 Asia/Bangkok. **Canonical repo:** `truongdinhnamaz/aznet-theme`. **PR:** [#507](https://github.com/truongdinhnamaz/aznet-theme/pull/507). **Scope:** L0 production read-only evidence, not actual hosting restore, ConvertFlow L5 or release.

## Live production facts

- Authenticated WPVibe 1.21.0 `site_info` verifies `remquocanh.vn`: WordPress 7.1.3, PHP 8.4.27, active `aznet-theme` **1.3.92**. `wp theme list` also finds inactive `aznet-theme-wpvibe-backup` **1.3.87** and inactive `aznet-theme-wpvibe-draft` **1.3.92**. Backup 1.3.87 is **not** a current 1.3.92 rollback image. Earlier evidence describes the draft's unrelated unpublished changes; it must not be published or discarded.
- Read-only `wp theme mod get custom_css_post_id` returns **1133**; `wp post get 1133` confirms `post_type=custom_css`, `post_status=publish` and title `aznet-theme`. Do not disable the live CSS hotfix.
- `wp plugin list` confirms active ConvertFlow Core **0.1.0**, ConvertFlow Pro **0.1.0-alpha.120**, WooCommerce **11.2.1**. RootProfile **0.1.0-alpha.114** is inactive. This inventory is **not** proof of provider integration on candidate 1.3.97.
- WordPress REST namespace index exposes `choiceguide/v1` and `choiceguide-pro/v1`, but mere namespace registration is not an accepted Theme-facing public integration contract. No provider-private endpoint was called.

## Exact published v1.3.92 source inventory

- WPVibe read-only `wp-content` listing of `themes/aznet-theme` (offset 0 and 200) yielded **216 unique visible source-file paths**.
- Compared to the recursive GitHub source tree at official `v1.3.92` commit `b7738467b21dba1bcd63f62a11b5a248cd7f1ba9` after applying the current canonical package's nonruntime directory/root-file exclusions: **231 package-source paths**, **216/216 visible source names match**, zero unexpected visible paths.
- The 15 other tag paths are **6 `.woff2`, 4 `.svg`, 5 `.webp`**. WPVibe wp-content read-only source listing excludes these binary/media extensions. Their physical presence and byte identity remain **UNKNOWN**, not "missing".
- File-name parity is **not** file-content parity. Prior retained evidence `docs/evidence/RQA_ACTIVE_THEME_SOURCE_PROVENANCE_20261024.md` established nine full returned-text matches only. `inc/admin/homepage.php` ~76.3 KiB exceeds WPVibe's **64 KiB** read-only source cap (HTTP 413), and WPVibe may redact secret patterns. No full active Theme directory SHA-256 or exact archive was obtainable.

## Recovery reference and proven scope

- Published [GitHub Release v1.3.92](https://github.com/truongdinhnamaz/aznet-theme/releases/tag/v1.3.92) contains `aznet-theme-1.3.92.zip` (620,170 bytes), SHA-256 `204458e8518096fcf08f01f42c75d0880471f0b6f26ebfbc283f57b894b9b3ed`.
- Retained [isolated GitHub Actions #40070849548](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40070849548) previously verified the exact official ZIP checksum and exercised **1.3.92 -> historical 1.3.96 preview -> 1.3.92** with Theme-switch, content/settings continuity and native custom CSS hotfix restoration in **disposable WordPress/Woo fixtures**. That PASS does **not** certify hosting restore, live source bytes or a 1.3.97-to-1.3.92 rehearsal.
- 1.3.97 candidate pre-documentation HEAD `0b9433d54f3e675971c899332244f08bb65887b9` achieved **44/44 GitHub CI checks SUCCESS**. Deterministic preview ZIP (241 files) SHA-256: `63df4b7e404df92869a30865fa03f44a0a9cdb99a52571694e6af74c59e5702e`. Documentation-only checkpoints must not be interpreted as new runtime build changes.

## Gate

**PASS L0 (read-only only):** WordPress/Theme/backup-version readback, live native CSS preservation check, 216/216 visible path reconciliation, official 1.3.92 rollback asset reference. Retain historical isolated L3/L4 staging PASS as separate evidence.

**BLOCKED/UNKNOWN:** User-reported full hosting backup is not accessible through current authenticated WPVibe/GitHub tools; exact active production Theme archive, SHA-256, hosting backup age/completeness, and production-restorable status unproven. WPVibe backup folder is older 1.3.87. Actual ConvertFlow Core/Pro L5 on 1.3.97 unproven. No L6 release approval.

**NO MUTATION:** No WordPress Theme/plugin/site data/Custom CSS changes, no hosting restore, no GitHub main merge/Release/tag.

**EXACT NEXT:** Obtain an authorized read-only archive/snapshot of *the actually installed* `wp-content/themes/aznet-theme` together with exact source path, size, timestamp and SHA-256 plus full-site backup restore evidence. Restore that snapshot in **isolated staging** and verify source-owned settings/content before any production decision. Without the external artifact/access, keep production backup recovery gate **BLOCKED**, not PASS.

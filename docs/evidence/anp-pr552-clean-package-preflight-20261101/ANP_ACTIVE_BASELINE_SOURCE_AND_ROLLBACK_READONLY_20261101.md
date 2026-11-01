# ANP Industrial 01 — Production source provenance and rollback boundary (2026-11-01, READ-ONLY)

## Authority and immutable references

- Canonical repository: `truongdinhnamaz/aznet-theme`.
- Live site: `https://anpvietnam.vn`, authenticated WPVibe `site_info` reports `aznet-theme` **1.3.98**, WordPress **7.1.4**, PHP **8.2.35**.
- Official published GitHub Release [v1.3.98](https://github.com/truongdinhnamaz/aznet-theme/releases/tag/v1.3.98) points to `d4ecae7914b2d7e9107dfdf50e7de9d693481d92`, ZIP `aznet-theme-1.3.98.zip` size **654161**, provider-published SHA-256 `dcca1a3a84707b0ec1ea7c11a75870ece44fba6e96dd0b75494c052b47341360`. ZIP bytes were NOT downloaded/rehashed in this checkpoint.
- Current canonical `main`: `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`. Git compare release-tag -> main: **2 commits ahead, 0 behind**, changing exactly `inc/theme/header.php` and `assets/css/presets/digital-01.css`:
  - `431af3026372259fd6ac8b89ce830effdee815f7` — logo ALT text uses site title;
  - `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405` — Digital 01 taxonomy link contrast.
- Candidate Draft [PR #552](https://github.com/truongdinhnamaz/aznet-theme/pull/552) remains `fc6725654ab20767fecd17412416b2ad12a6c2bd`, 6 commits ahead of current `main`, unmerged. This record does NOT alter candidate sources.

## New read-only hosted source comparison

WPVibe `read_file(scope="wp-content", path="themes/aznet-theme/<path>")` inspected **active** ANP Theme files, explicitly not the mixed `aznet-theme-wpvibe-draft`. Compare each numbered WPVibe text response to exact GitHub `fetch_file` content at `main` and the release tag. For the large homepage CSS, contiguous line-range responses **1–800** and **801–1451** were assembled with line-number continuity checks (1451/1451 lines present).

| Active Theme path | Captured lines | Normalized text vs `main` | vs official v1.3.98 tag | `main` Git blob |
|---|---:|---|---|---|
| `inc/theme/header.php` | 242 | **same** | **DIFFERENT** starting line 183 | `afa6286199a82050dba8619f0e928e17e4ab3abf` |
| `assets/css/presets/digital-01.css` | 61 | **same** | **DIFFERENT** starting line 28 | `94c04aac36a3398e73c9e5d030a802fe5cf4082c` |
| `assets/js/catalog-navigation.js` | 47 | **same** | same | `0d57caa4951949645b4635bc3fd76bee4b6f52b6` |
| `assets/css/components/catalog-navigation.css` | 396 | **same** | same | `43e5d69be14537fcc6e2f032e0c333f059cf5f6a` |
| `template-parts/homepage/industrial-01/categories.php` | 110 | **same** | same | `bc0ca5bda543c5414e724bb2854f71df35dc4f21` |
| `assets/css/components/homepage-industrial-01.css` | 1451 | **same** | same | `f915b9b13116d05b7e1f55559e6df3c07b9a2c6a` |

**PASS L0 / scoped provenance:** six complete, readable active source files equal canonical `main` text after LF + final-newline normalization. Both published-release divergence files explicitly match `main`, not the release-tag bytes. WPVibe may normalize or redact source; these comparisons do not prove literal file-byte hashes, full active Theme directory equality, database integrity or live deploy readiness. No site or admin mutation was performed.

## Rollback safety and decision

1. **Do not assume the official v1.3.98 ZIP is an exact restore of ANP's active Theme**: the same displayed version number covers two distinct Git source states. The official published ZIP omits two main commits confirmed present in ANP active source. Reinstalling it may regress current header logo ALT and Digital 01 link contrast.
2. For the eventual PR #552 production rollout, first obtain a timestamped **backup of the *actual active* `wp-content/themes/aznet-theme` bytes** or an independently verified equivalent, plus an independently restorable **database backup** and retention/access proof. The source `main` SHA can be a provenance reference but is not a replacement for a verified hosting snapshot.
3. **Rollback default is Theme-file restoration**, not wholesale WordPress database overwrite. WooCommerce can receive new orders or other writes between backup and rollback; restoring a stale entire DB could discard them. Only consider database restore if relevant data was altered/corrupted, assess the write window and replay/migration implications, and require separate explicit approval.
4. Do not delete, overwrite or publish the mixed Wellness 01 WPVibe draft. The safe future candidate is the exact pinned clean PR #552 source package, not the mixed draft.
5. Host backup point creation/existence and actual restore are **BLOCKED/UNKNOWN**, because the connected WPVibe/GitHub tools do not expose 7HOST's hosting backup filesystem or authenticated cPanel restore interface. Avoid pretending that a provider's generic retention policy establishes an ANP-specific restore point.
6. L1 full ZIP PHP lint, L3 WordPress floor, L4 accessible responsive sitewide flows, L5 provider interactions and L6 release all remain unproven for exact packaged bytes. Never infer release approval from L0 text parity.

**EXACT NEXT:** obtain read-only proof of an active-Theme + database recovery point and authorized restore method from 7HOST/cPanel; then build and verify the pinned PR #552 full deterministic ZIP in an isolated executable environment. Only after real package QA and explicit go-live approval consider production change.

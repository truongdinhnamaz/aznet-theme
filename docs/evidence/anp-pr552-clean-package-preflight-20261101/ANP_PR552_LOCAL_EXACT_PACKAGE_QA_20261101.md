# ANP Industrial 01 PR #552 — Local exact-source package QA (2026-11-01)

## Scope and authority

- Canonical repository: `truongdinhnamaz/aznet-theme`, candidate **Draft PR #552** at `fc6725654ab20767fecd17412416b2ad12a6c2bd`, current `main` at `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`, no merge.
- This is **isolated offline L0–L2/package QA**, NOT approval to publish and NOT an ANP hosting/WordPress data operation.
- Original active live Theme contains extra Wellness 01 text paths not in `main`; an ordinary full-directory WordPress Theme replacement may discard them. Retain existing Wellness and existing mixed WPVibe draft without modification.

## Source recovery and byte-identity approach

Previous local `git clone` / GitHub archive fetch was blocked by DNS. A **pre-existing successful GitHub Actions artifact**, not a new workflow/release, allowed an exact source reconstruction:

1. [Run #40628520518](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40628520518), artifact ID `12790642034`, `x6-cross-surface-release-evidence`, had its own checkout metadata: commit `431af3026372259fd6ac8b89ce830effdee815f7`, Git tree `fbd9b1518d5359fecce17aae01993341fc7903d8`. It contained `x6/expected/aznet-theme/` with exactly **242** production files and two baseline deterministic builds.
2. The two baseline builds were **byte-for-byte equal**, SHA-256 `a711892318f49cc9c65d4b5a01e3a7b284d15a75482fb0770671400adf5428fb`. Entire baseline package was extracted and all **242/242 paths and file contents** compared to `x6/expected`: PASS.
3. GitHub canonical tree diff, baseline `431af...` to PR #552 head `fc672...`, shows exactly **6 changed production blobs**: `assets/css/presets/digital-01.css` (current main accessibility adjustment) plus the **5 PR #552 changed Theme files**. Zero added/removed production paths. The other 236 production blobs are Git-identical between baseline and target.
4. Applied the exact source changes from canonical GitHub diff to isolated source. Rechecked all six updated files using `git hash-object` against full expected GitHub tree SHA. All six matched. For the other 236 files, content came from the proven unchanged baseline artifact.
5. Computed all **242 local Git blob IDs** with `git hash-object`, sorted `relative_path<TAB>blob_sha<LF>`, and compared a 64-bit FNV-1a fingerprint computed independently from the full GitHub target tree inventory: **`a049b3ad0736de1e` MATCH**. FNV is a non-cryptographic cross-check of the manifest, not a standalone security signature. Local serialized inventory SHA-256 `4d59f83054ce15fb57f2186d0c2218fe447cbbda26260f31f46dc2b692baceb3`.
6. Existing canonical `scripts/build-release-package.py` was reconstructed with Git blob verified as `744a8f6c94b9722da95af3d4f3246a0c78c9a184`. No custom package semantics or build stack were introduced.

## Fresh local package verification

- Version: both `style.css` and `functions.php` declare **1.3.98**; unchanged. No version bump or tag.
- Deterministic ZIPs rebuilt twice and **byte-compared**. Rebuilding after adding excluded offline test files produced the **same bytes**.
- ZIP root `aznet-theme/`; **242 entries**, 1,938,745 uncompressed source bytes, output **656,000 bytes**.
- ZIP SHA-256: **`44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f`**.
- `ZipFile.testzip()` clean; unique paths, no traversal, fixed entry timestamp `2020-01-01`, file mode `0644`. Extracted ZIP bytes match local source **242/242**. No `.github/`, `docs/`, `tests/`, `scripts/`, `dist/` or Wellness 01 payload in the ZIP.
- **164/164 packaged PHP files**: `php -l` PASS on **PHP 8.4.24**; **9/9 JavaScript files**: `node --check` PASS on **Node 22.16.0**. This does not establish PHP 8.1/WordPress 6.9 support-floor runtime.
- Original exact GitHub L2 tests, with source test Git blobs matched, executed against the candidate:
  - `tests/offline/catalog-navigation-accessibility-behavior.test.mjs` Git blob `2d1b33136d8484ed3e1603e842b9a7a8029cab95` — PASS, opening, accessible names, `aria-expanded` and Escape/focus behavior in mock DOM.
  - `tests/offline/industrial01-native-front-page-presentation-contract.test.mjs` Git blob `726fc397f78ea1a3a1a42b233a8167acb8fd6962` — PASS, native wrapper hierarchy and responsive presentation contract.
  - `tests/offline/core-vertical-catalog-submenu-contract.php` Git blob `fad3d65f92dd0534f376848f619513d4f81ac52d` — PASS, progressive-enhancement and absence of private domain logic.
- No existing PASS claimed outside these exact files/syntax gates. Earlier 14-route preview and focused browser evidence remain separately scoped.

## Five-file overlay preparation — NOT a live deploy

A second local archive, `ANP_PR552_FIVE_FILES_OVERLAY_NOT_WP_INSTALL.zip`, contains **only five changed Theme production files**, plus a machine-readable **before/after Git-blob and SHA-256 allowlist** and a safety README. Deterministic double-build, ZIP CRC, per-file exact candidate blob parity, no Wellness paths: PASS.

- Overlay size **17,881 bytes**; SHA-256 **`9fb2fe2318c0f246e9703d716aae8cba500bb36d79cf8eea669551010ae74386`**.
- The overlay **is NOT installable via WordPress Appearance → Themes**. It is an offline handoff for a future approved *bounded file overlay*, requiring live preimage/backup verification and rollback. It was **NOT applied** to ANP.
- The full 242-file ZIP is an **installation-like source candidate** only, not safe to use as a whole-directory replacement on ANP because of existing live-only Wellness 01 assets.

## PASS / BLOCKED / exact next

- **PASS L0:** canonical branch/commit/tree input, exact source reconstruction, 242-file Git-blob manifest parity and 5-file allowlist.
- **PASS L1:** twice identical 242-file ZIP, CRC/extract/source byte parity, 164 PHP syntax checks, 9 JS checks, exclusions.
- **PASS L2 (focused):** three exact-source offline contracts listed above.
- **UNKNOWN:** no fresh *exact package* L3 on a disposable WordPress 6.9+/PHP 8.1+ environment, no complete 390/768/1440 browser/axe QA on exact package, no full L5 provider integration, no live bytes/snapshot/rollback test, no complete binary live Wellness inventory. Previous live/preview QA cannot be generalized to the whole package.
- **BLOCKED L6:** owner-controlled publish gate and verified rollback; no main merge/tag, no WPVibe publish, no ANP hosting/cPanel use, no live Theme/data writes. Do not use the mixed WPVibe draft as a whole-theme release shortcut.

**EXACT NEXT:** execute the pinned `44e90157...` full ZIP on isolated disposable WordPress at the support floor and complete the missing L3/L4/L5/rollback gates; preserve extra Wellness paths and use only an approved bounded deployment mechanism when go-live authorization is explicit. User has directed **no hosting interaction** in this continuation.

All local QA artifacts remained in the conversation workspace; this repository document records checksums/provenance but does not claim the ZIPs were uploaded to GitHub Releases.


## Additional isolated five-file overlay / rollback fixture (fresh L1/L2)

An independent **temporary local fixture** restored all 242 original files from the archived, previously successful baseline, then added **7 explicit artificial sentinels** at the three known extra Wellness text paths and four potential Wellness WebP paths. These sentinel bytes are **not** copies of ANP live; this proves preservation logic only.

- Preflight used the overlay's manifest to verify **all five expected-before Git blob SHA values before any file modification**.
- Applied the five allowlisted new source files to the disposable fixture only; per-file postimage Git blob check PASS.
- Full SHA-256 tree inventory before/after: **249 files**, exactly five changed paths and **7/7 extra sentinel files unchanged**.
- Restored the five captured preimage bytes; **249/249 temporary fixture files returned to their exact original SHA-256 inventory**, including 7/7 extra sentinels.
- **PASS at fixture L1/L2 only.** This is not live hosting backup proof, actual WordPress runtime, a tested production rollback, or permission to deploy an overlay. No ANP site, draft, theme folder, WordPress database, hosting or main repo changed.

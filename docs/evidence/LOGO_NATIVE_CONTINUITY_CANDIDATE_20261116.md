# Native Custom Logo continuity - candidate 2026-11-16

Base: main 07eab630ba4a546500e6a5eaca285458a423c122, AZnet Theme 1.3.110.
Observed reference: anpvietnam.vn Header and Footer restored after setting native WordPress theme mod custom_logo=9727. This was a site configuration issue, not an image-rendering defect in Theme code.

Bounded candidate:
- On a future theme switch to AZnet Theme, preserve ONLY an explicit, valid, previous WordPress-native custom_logo attachment when the current Theme value is empty.
- Show a scoped admin notice in AZnet Theme Control Center for an unset/invalid image. Manual site-owned selection remains authoritative.
- No filename/URL/site_icon/third-party-option guessing, no cross-site IDs, no plugin reads.
- No release promotion or production site mutation by this work; candidate-only version metadata update.

Local evidence: RED before module existed (require missing), then PHP lint PASS and offline contract GREEN covering existing, valid old, missing, malformed, permissions and notice scope. Full WordPress staging L3, full browser/visual L4, integration L5 and release L6 are not claimed; scoped draft DOM smoke is recorded below.

Acceptance gate before release: WordPress 6.9+/PHP 8.1+ runtime, theme switch from a native custom-logo theme, site without logo, site with existing logo, page Header/Footer + a11y/mobile, negative tests and package/unzip/reverify. Rollback: revert this isolated commit or remove require and module.


## Installable candidate package 2026-11-16

- Candidate Theme version: `1.3.111-rc.1` (non-release; branch only).
- Base full Theme artifact: GitHub Actions run `43591522622`, artifact `14112163832`; nested `aznet-theme-homepage-native.zip` SHA-256 `6d03d3110568c8b3f3b9ab0fb36cc350a881a63c86eb29e7a689b0bcdf328de0`.
- Artifact source commit `38089ee42ee5c61756ec0acf0bd499b87ab5c571` has identical tree `c304eb4edb527d275728faf6960f49915530475b` to latest canonical main `07eab630ba4a546500e6a5eaca285458a423c122`.
- Installable candidate ZIP: `aznet-theme-1.3.111-rc.1-logo-candidate.zip`; 255 files, `aznet-theme/` root, SHA-256 `364c59a83128b0a97ccd3c373320446d835a11cf9e2654d61a8640af4c34c0e3`.
- Relative changes from upstream artifact: `inc/theme/bootstrap.php`, `inc/theme/logo-continuity.php`, `style.css`, `functions.php` and `README.md` (candidate metadata alignment); no files removed.
- Verification: full archive integrity PASS; 168 PHP syntax checks PASS on PHP 8.4.24; offline native-logo continuity contract PASS (mocked WordPress functions, **not** L3 runtime).
- Full runtime staging L3, full browser L4, integration L5, release L6: UNKNOWN / not claimed. This QA session did not modify production WordPress sites; note live drift below.
- IMPORTANT: for a site already running AZnet Theme with empty `custom_logo`, installing the candidate does not automatically choose a brand image. It displays a scoped configuration prompt instead; the site owner must select its verified official logo. Do not present this as global automatic restoration.
- Candidate rollback: restore prior Theme ZIP (after site-specific backup) or revert this isolated branch; preserve WordPress content and settings.

## Draft WordPress smoke check (WPVibe), 2026-11-16

- Target site: remquocanh.vn, active AZnet Theme 1.3.110 (matches candidate base), WordPress 7.1.4 / PHP 8.4.27.
- Created an isolated WPVibe draft from the active Theme; injected `inc/theme/logo-continuity.php` and its one-line bootstrap include only in draft, not live.
- Draft preview: Header rendered official existing `custom_logo` attachment ID 26 (`logo-rem-quoc-anh-chuan-ngang-1.png`), with `alt="Rèm Quốc Anh"`.
- Draft Footer also rendered the same image under WordPress-sourced identity. **PASS: L3/L4 scoped DOM smoke for unchanged existing-logo path only** (not a full L3/L4 matrix; no screenshots).
- Draft deleted, and the live Header read back still rendered the same logo. No production Theme activation, code installation, release or website setting edits.
- Still unverified: actual switch-time data preservation, missing-logo admin notice in wp-admin, absence/error paths on WordPress 6.9 and PHP 8.1, comprehensive browser/mobile/a11y, package installation and rollback on staging.

## Staging gate evaluation (2026-11-16, follow-up)

**Scope/provenance:** Candidate branch `work/logo-native-continuity-20261116`; canonical main stayed `07eab630ba4a546500e6a5eaca285458a423c122` during the QA. Official WordPress hook reference confirms `after_switch_theme($old_name, WP_Theme $old_theme)` fires on the next WordPress load after activation (https://developer.wordpress.org/reference/hooks/after_switch_theme/).

**Package QA PASS (L0/L1/L2):** ZIP root `aznet-theme/`, 255 files, 2,544,745 bytes, SHA-256 `364c59a83128b0a97ccd3c373320446d835a11cf9e2654d61a8640af4c34c0e3`; archive CRC/integrity PASS, 168 production PHP lint checks PASS on PHP 8.4.24; offline contract `tests/offline/logo-native-continuity-contract.php` PASS with mocked WordPress APIs. Compared to run 43591522622 baseline: exactly four modified files (`README.md`, `functions.php`, `style.css`, `inc/theme/bootstrap.php`) and one new module (`inc/theme/logo-continuity.php`), no deletes. README's stale `1.0.0` version corrected within candidate branch only. Earlier ZIP SHA-256 `341e321aad06640e1a5d9fa31b7766769f26a9d47f70ee0547c1af6f1f4d1ccc` was superseded (only README changed between these archives).

**Rollback evidence PASS (files only):** isolated replace candidate Theme folder with frozen baseline ZIP; all 254 baseline files restored byte-for-byte and new logo module absent. Not a WordPress theme activation/database rollback test.

**WordPress read-only observations (not staging):** `vananhyoga.com` currently reports an **active 1.3.111-rc.1** installation in WPVibe `site_info` and `wp theme list`, whereas earlier authorized scan reported 1.3.108; `custom_logo` remains `null`. Live Header displays a text span and Footer a strong tag with site name; no logo image. This QA turn did **not** install, activate, update, or rollback anything on that production site. Actor/timing/coordination of the existing live version change is UNKNOWN; not proof the staging gate passed. `remquocanh.vn` remains active 1.3.110 with its original working logo. `anpvietnam.vn` remains active 1.3.108 with custom logo restored and rendered. A preexisting WPVibe draft was observed on ANP; it was not touched.

**Staging/browser blockers:** Current WPVibe connections list only live domains; no authorized dedicated staging WordPress site identified. Container has PHP 8.4.24 but no WordPress runtime, database engine or PHP DB drivers; public WordPress download failed due DNS. Local Chromium/Playwright navigation blocked with `ERR_BLOCKED_BY_ADMINISTRATOR` for both file:// and http://127.0.0.1; no visual/mobile/keyboard assertions or screenshots passed. Static CSS/logo sizing remains inspectable but cannot be called browser PASS. Promoted NWQS endpoint was inaccessible; fallback standard only.

**Gate verdict:** **NO-GO / BLOCKED** for a released or production-installable version: actual WordPress ZIP install/activation, prior-theme `custom_logo` preservation under `after_switch_theme`, missing/invalid-logo admin notice on real wp-admin, WP 6.9/PHP 8.1 minimum-floor, responsive/mobile/keyboard/a11y L4, and full database+theme rollback are not proven. Existing good site-specific logos must not be overwritten. No merge into main or release/deploy executed.

**EXACT NEXT:** Acquire an explicitly authorized isolated staging WordPress instance with DB backup and browser access (WordPress >=6.9/PHP >=8.1); install **this exact SHA-256 candidate**, run old-theme/native-logo -> AZnet switch, no-logo, invalid-logo, existing AZnet logo and reversal; collect Header/Footer admin notice and mobile keyboard/a11y evidence; only then reassess release gate.

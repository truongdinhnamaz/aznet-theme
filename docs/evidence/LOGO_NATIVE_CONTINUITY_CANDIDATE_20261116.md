# Native Custom Logo continuity - candidate 2026-11-16

Base: main 07eab630ba4a546500e6a5eaca285458a423c122, AZnet Theme 1.3.110.
Observed reference: anpvietnam.vn Header and Footer restored after setting native WordPress theme mod custom_logo=9727. This was a site configuration issue, not an image-rendering defect in Theme code.

Bounded candidate:
- On a future theme switch to AZnet Theme, preserve ONLY an explicit, valid, previous WordPress-native custom_logo attachment when the current Theme value is empty.
- Show a scoped admin notice in AZnet Theme Control Center for an unset/invalid image. Manual site-owned selection remains authoritative.
- No filename/URL/site_icon/third-party-option guessing, no cross-site IDs, no plugin reads.
- No production takeover, no release/version bump, no site mutation.

Local evidence: RED before module existed (require missing), then PHP lint PASS and offline contract GREEN covering existing, valid old, missing, malformed, permissions and notice scope. WordPress L3, browser L4, integration L5 and release L6 are not claimed.

Acceptance gate before release: WordPress 6.9+/PHP 8.1+ runtime, theme switch from a native custom-logo theme, site without logo, site with existing logo, page Header/Footer + a11y/mobile, negative tests and package/unzip/reverify. Rollback: revert this isolated commit or remove require and module.


## Installable candidate package 2026-11-16

- Candidate Theme version: `1.3.111-rc.1` (non-release; branch only).
- Base full Theme artifact: GitHub Actions run `43591522622`, artifact `14112163832`; nested `aznet-theme-homepage-native.zip` SHA-256 `6d03d3110568c8b3f3b9ab0fb36cc350a881a63c86eb29e7a689b0bcdf328de0`.
- Artifact source commit `38089ee42ee5c61756ec0acf0bd499b87ab5c571` has identical tree `c304eb4edb527d275728faf6960f49915530475b` to latest canonical main `07eab630ba4a546500e6a5eaca285458a423c122`.
- Installable candidate ZIP: `aznet-theme-1.3.111-rc.1-logo-candidate.zip`; 255 files, `aznet-theme/` root, SHA-256 `341e321aad06640e1a5d9fa31b7766769f26a9d47f70ee0547c1af6f1f4d1ccc`.
- Relative Theme changes from upstream artifact: `inc/theme/bootstrap.php`, `inc/theme/logo-continuity.php`, `style.css`, `functions.php` only.
- Verification: full archive integrity PASS; 168 PHP syntax checks PASS on PHP 8.4.24; offline native-logo continuity contract PASS (mocked WordPress functions, **not** L3 runtime).
- Runtime L3 / browser L4 / integration L5 / release L6: UNKNOWN / not claimed. No actual WordPress site has been changed by this candidate.
- IMPORTANT: for a site already running AZnet Theme with empty `custom_logo`, installing the candidate does not automatically choose a brand image. It displays a scoped configuration prompt instead; the site owner must select its verified official logo. Do not present this as global automatic restoration.
- Candidate rollback: restore prior Theme ZIP (after site-specific backup) or revert this isolated branch; preserve WordPress content and settings.

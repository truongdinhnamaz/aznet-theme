# UPN0 — WordPress-native Theme update mechanism verification

Status: **PASS (documentation/source semantics only), NOT runtime/integration/release PASS**  
Date: 2026-11-02  
Issue: #553; proposal PR: #554; canonical base `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`.

## Verified authoritative external references

- WordPress Developer: https://developer.wordpress.org/reference/hooks/update_themes_hostname/
- WordPress Developer: https://developer.wordpress.org/reference/functions/wp_update_themes/
- WordPress Core: https://make.wordpress.org/core/2022/10/06/introducing-update-uri-theme-header-in-wordpress-6-1/

WordPress introduced the Theme `Update URI` header and `update_themes_{$hostname}` filter in 6.1, earlier than AZnet's 6.9 minimum. WordPress' `wp_update_themes()` reads the installed theme's UpdateURI, extracts hostname, applies its host-specific filter, sets `id` to UpdateURI, uses `version` as `new_version` when absent, and compares new and installed versions before recording a response in the `update_themes` site transient. The hook accepts false to decline the update. This function only checks updates and does not install.

## Target contract research (not accepted implementation)

- `style.css` currently has Theme version 1.3.98 and **no Update URI header** as verified on main.
- `functions.php` currently sets `AZNET_THEME_VERSION` 1.3.98.
- Preferred runtime: add an approved update host/URI, an exact-host WordPress update filter, and a bounded metadata reader. Never publish update metadata for a candidate branch or unauthorized package.
- Conservative first release: **notification only**, no package installation URL until immutable officially accepted release package identity, transport authorization, integrity preflight and site rollback acceptance are proven. When transport fails, return false. Keep WordPress native update permissions.
- A package download URL in an update response enables install flow; do not equate arbitrary manifest SHA-256 field with core installer verification. Verify end-to-end actual updater behavior and integrity before advertising installable update.
- Avoid hard-coded unapproved service hostname, license dependency, automatic installation and cross-site data collection.

## Acceptance tests to write after UPN0 owner/source approval

1. Missing `Update URI` or unsupported/wrong hostname produces no update and does not claim notification PASS.
2. Valid newer version appears in native WordPress update transient/admin notice for `aznet-theme`.
3. Same/lower version yields no update.
4. Invalid metadata, timeout, unsafe/non-HTTPS locator, unsupported compatibility or malformed version fails safe.
5. Installable package is restricted to approved immutable release artifact and separately verified upgrade/rollback path.
6. External Template Distribution Service absent does not block Theme Core check; selected template update remains a D-040/TPL5 external contract track.

## Gate

This is research/evidence only. `AZT-02` and `AZT-04` proposal remains **PROPOSED**. It does not authorize merge, Core Theme updater production code, tag/release, distribution endpoint creation, or client-site deployment. Real WordPress 6.9/PHP 8.1 update admin/runtime/upgrade is still UNKNOWN.

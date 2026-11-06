# UPN2 read-only notice correction — 2026-11-02

## Root cause and fix
WordPress core `wp_update_themes()` accepts a newer response without `package` into the update list; `Theme_Upgrader::upgrade()` then consumes `$upgrade_data['package']`. Therefore the former candidate advertised an actionable update without a usable package.

Correction on candidate PR #556:
- Keep `Update URI` host discrimination.
- `update_themes_github.com` declines an AZnet Theme core upgrade until a separately approved, verified install package exists.
- Show a **read-only admin notice** to authorized Theme updaters for a newer stable official GitHub Release; the link goes to the trusted release details, not `update.php`.
- Continue six-hour metadata cache, strict official Release URL and version checks, no automatic installation, no plugin dependency or domain storage mutation.
- Revise isolated tests for the two distinct contracts: latest release metadata for notice, and false for native upgrade queue.

## Evidence boundary
GitHub source paths:
- `inc/theme/core-update-notice.php`
- `tests/offline/upn2-core-update-notice-contract.php`
- `style.css`, `inc/theme/bootstrap.php` unchanged since earlier candidate edits.

WordPress upstream:
- https://developer.wordpress.org/reference/functions/wp_update_themes/
- https://developer.wordpress.org/reference/classes/theme_upgrader/

**Current QA:** source review/patch provenance only. Exact PHP file execution NOT VERIFIED; local runtime cannot resolve `raw.githubusercontent.com` (curl error 6). Do not carry forward the older 14/14 offline PASS to this changed candidate. It applies to a prior version only.

**Next:** obtain byte-exact code into PHP 8.1/WordPress 6.9 executor, run updated tests, inspect authenticated wp-admin notices, then source/implementation merge gates. No release/deploy approval.

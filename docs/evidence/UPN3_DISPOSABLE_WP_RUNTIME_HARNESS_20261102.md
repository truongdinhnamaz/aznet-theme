# UPN3 disposable WordPress runtime harness — checkpoint 2026-11-02

## Why this artifact exists

UPN2 isolated PHP **22/22 PASS** has exact Git-blob byte provenance (see `UPN2_BOUNDED_METADATA_EXACT_BLOB_20261102.md`), but does not validate the WordPress 6.9/PHP 8.1 runtime.

A bounded integration verifier is now in `tests/wordpress/upn3-readonly-update-notice.php`, intended for `wp eval-file` on a disposable WordPress test site. It is not production-updater code.

## Mandatory safety gates

The verifier aborts unless:
- `AZT_UPN3_DISPOSABLE=YES_DISPOSABLE` explicitly confirms a disposable instance.
- Real WordPress >=6.9 and PHP >=8.1 are loaded, single-site mode, with AZnet Theme candidate active and without active third-party plugins.
- Installed updater module's Git blob matches `AZT_UPN3_EXPECT_BLOB`. For current UPN2 module: `a3f118fb987aad11e7072475dfd80e1ac16c4d37`.
- A disposable administrator has `update_themes` capability.

No real GitHub Release is created or installed. `pre_http_request` stubs official GitHub metadata and WordPress.org theme update checks and blocks all other outbound traffic while running. Prior user, cache transient, and WordPress update transient are restored in a `finally` block.

## What the verifier is designed to prove when executed

- WordPress hook registration: `update_themes_github.com`, `admin_notices`.
- Matching Theme version yields no notice; newer official stable version produces read-only admin markup; cache avoids repeat HTTP fetch.
- No installable update object, including through the *actual* WordPress `wp_update_themes()` routine using a controlled WordPress.org response.
- Foreign UpdateURI remains untouched, GitHub outage fails soft, and unauthorized actors neither see a notice nor trigger a fetch.

This exercises real WP runtime logic, but **does not on its own prove a browser screenshot, multisite, live GitHub egress, or a production upgrade**.

## Verification status — no inflated PASS

- **L1 structural review:** source fetched from GitHub, required disposable gate and cleanup verified, balanced PHP delimiters/strings by lightweight review. This is **not** `php -l` or runtime execution.
- **L3 runtime:** **BLOCKED/UNKNOWN** — current executor has PHP 8.4.24 only; no WordPress installation, `wp`, MySQL/MariaDB, Docker/Podman or PHP 8.1 runtime. Network/DNS cannot retrieve those dependencies. No WordPress instance was accessed or modified.
- **L4 browser:** **UNKNOWN**, not attempted.
- **L5/L6:** **UNKNOWN**, separate UPN4/package provenance/rollback and release approval gates.

## Future exact run (on a freshly installed disposable test instance)

```bash
AZT_UPN3_DISPOSABLE=YES_DISPOSABLE \
AZT_UPN3_EXPECT_BLOB=a3f118fb987aad11e7072475dfd80e1ac16c4d37 \
wp eval-file tests/wordpress/upn3-readonly-update-notice.php --skip-plugins
```

Record WordPress/PHP version, actual Theme source commit, exit status, wp-admin browser proof (when a real newer release is available or with an authorized controlled response), and restoration evidence. Do **not** run against production or treat read-only notices as authorization to install.

## Exact next

Obtain a disposable real WordPress 6.9/PHP 8.1 execution environment, run this pinned verifier, inspect actual wp-admin behavior, then review source PR #554 and implementation PR #556 merge gates separately. No merge, release, or deployment is authorized yet.

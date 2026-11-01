# ANP PR #552 — Clean source package preflight (L0 only) — 2026-11-01

- Canonical source: [Draft PR #552](https://github.com/truongdinhnamaz/aznet-theme/pull/552) at exact head `fc6725654ab20767fecd17412416b2ad12a6c2bd`; unchanged `main` base `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`.
- Git object identity: `tree=ccfcdfc51f78832383cfb3824ad9273a7492ad7b`, `scripts/build-release-package.py` Git blob `744a8f6c94b9722da95af3d4f3246a0c78c9a184`.
- Applied **existing** deterministic package policy to the complete, untruncated recursive Git tree (960 Git files): exclude directory segments `.git/.github/docs/scripts/tests/node_modules/dist`, plus root `README.md` and `aznet-preview.png`; the payload would have `242` files, `164` PHP files, `1938745` uncompressed source bytes. Version check via actual `style.css` and `functions.php` both equals `1.3.98`.
- Only **five** Theme production paths change relative to main: `assets/css/components/catalog-navigation.css`, `assets/css/components/homepage-industrial-01.css`, `assets/js/catalog-navigation.js`, `inc/theme/header.php`, `template-parts/homepage/industrial-01/categories.php`. All five exact Git blob IDs are in the attached machine-readable `git-tree-payload.json`.
- Safety gate: 0 Wellness 01 source paths, 0 symlinks and 0 traversal paths in production payload; 5/5 essential files present. PR #544 and current mixed WPVibe ANP draft were **not modified**, and no clean draft was created.
- **PASS only L0 source-tree/package input selection**. This is an immutable-by-SHA manifest of Git objects, **not an actual ZIP file**, not an assertion of successful PHP syntax, WordPress 6.9/PHP 8.1 runtime, 390/768/1440 L4/axe, full L5 integration, host/database backup, rollout or rollback. GitHub Actions startup failure has no successful CI steps.
- Since the active ANP Theme and mixed preview files cannot be safely replaced without a fresh independent host+DB backup and release approval, **do not merge main, change version, deploy or destroy the old draft**. The release source remains pinned PR #552, not this evidence-only branch.
- Next: once executable source bytes/runner or approved staging is available, reconstruct deterministic ZIP from pinned Git objects, build twice, compare SHA-256 and unzip members against the manifest; test floor WordPress/PHP, browser/a11y and backup/restore; obtain distinct owner go-live approval.

## File-level identity

`git-tree-payload.json` is complete source-path + Git blob SHA + mode + size manifest (no file contents), derived directly from GitHub's recursive tree. It is a provenance reference only.

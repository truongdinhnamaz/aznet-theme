# AZnet Theme PR #552 — exact-package isolated WordPress support-floor L3 (2026-11-01)

## Source of truth / scope
- Canonical repository: `truongdinhnamaz/aznet-theme`.
- Draft PR #552 pinned candidate: `fc6725654ab20767fecd17412416b2ad12a6c2bd`; canonical main: `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`. This evidence does not authorize merge or release.
- AZT-05 v1.0 (on main), AZT-00–04 and `docs/SOURCE_GOVERNANCE.md` retain authority. Core is WordPress-clean independent; no external domain state mutation.
- Prior checkpoint: `16e60020aa1712260d3c04ccb6ad50e422871e7b`, plus PR comment `6558730247`.
- This execution touched **only disposable WordPress Playground**. No ANP hosting, live Theme, live/database state, WPVibe draft, Wellness 01 files, candidate branch, or canonical main changed.

## QA harness defect: RED → GREEN (not a Theme defect)
- V1 Blueprint [run cae28340](https://agent.tinyfish.ai/runs/cae28340-e03d-43a4-9628-74bbdbdccecb) reached WordPress installation but failed because `/wordpress/wp-content/uploads/azt-pr552-exact-candidate.zip` did not exist.
- The package function computed an in-memory SHA and returned even when `file_put_contents` failed due to a missing destination directory. No Theme stack trace or Theme production defect was established.
- Local independent reproduction used the same 242-file source and exact PHP ZIP builder: **RED**: computed expected `44e90157...` SHA but produced no file; **GREEN**: created destination directory, persisted the ZIP, verified on-disk 656000 bytes and SHA. This verification is of QA harness behavior only.
- Correction was isolated to docs-only [V2 test Blueprint](https://github.com/truongdinhnamaz/aznet-theme/blob/41122043a94ae1f60e52b795ea79ce24a50fe486/docs/evidence/anp-pr552-clean-package-preflight-20261101/AZT_PR552_EXACT_ZIP_INSTALL_BLUEPRINT_V2_20261101.json). It ensures upload directory exists and verifies **persisted** ZIP SHA, then invokes WordPress **core** `Theme_Upgrader` and activates installed Theme (no Blueprint VFS prefetch assumption).
- The reproducible exact ZIP builder uses ZIP_DEFLATED with zlib compression level 6 / memory 8, 242 sorted entries, fixed DOS timestamp 2020-01-01, 0644 attributes, one top-level `aznet-theme/` folder. Local PHP-built ZIP compared **byte-for-byte** against the pinned Python-built artifact: 656000 bytes, SHA-256 `44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f`. This is a new packaging method validation, not a production code edit.

## Fresh real WordPress evidence — bounded L3 PASS
- [WordPress Playground run c009127c](https://agent.tinyfish.ai/runs/c009127c-a3b2-4645-87c2-bd3c0f2ad9a6): final V2 Blueprint **completed** successfully in a disposable WordPress environment.
- Browser-observed machine JSON at `/wp-content/uploads/azt-pr552-l3-report.json` (this is a report from isolated WordPress, not a public or production page):
  - `candidate=fc6725654ab20767fecd17412416b2ad12a6c2bd`
  - `zip_sha256=44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f`
  - `package_file_count=242`
  - `actual_payload_sha256=expected_payload_sha256=08b23a84281259d5c0c5b593eee197051865451e920253a18aa8ea05ff5d337c`
  - `payload_match=true`
  - `wp_version=6.9.11`, `php_version=8.1.34`, `active_theme=aznet-theme`, `active_plugins=[]`
- ZIP created inside the WordPress filesystem and inspected at `/wp-content/uploads/azt-pr552-exact-candidate.zip`; WordPress core `Theme_Upgrader` installed the ZIP; `aznet-theme` became active.
- **Observed frontend smoke:** isolated native Homepage and isolated Contact Page rendered with their expected headings/content and no browser-visible fatal.
- **Not PASS yet:** Post navigation did not fully settle in the Playground iframe; Search and 404 were not inspected within that run. No full PHP debug-log or HTTP status/final-URL report was collected. Do not extend this evidence to all L3 routes or L4/axe.
- Runtime is **Playground PHP/SQLite**, not a MySQL/MariaDB certification or a live-site preview. This fulfills package installation/activation on the declared WordPress 6.9+/PHP 8.1+ support floor only at the scoped level.

## Release / compatibility gates unchanged
- Prior L0–L2, 390/768/1440 synthetic Chromium and 8/8 AX fixture results remain retained; not rerun without invalidation.
- L3 installation, activation and 242-file payload integrity: **PASS** for isolated WordPress+SQLite at support floor.
- L3 full route/PHP log, L4 sitewide browser/visual/a11y, L5 actual provider integration and L6 live rollback/release: **UNKNOWN/BLOCKED** as applicable.
- Current ANP active installation contains Wellness-only paths not in this clean 242-file ZIP. **No whole-theme live overwrite**, no mixed WPVibe draft publish, no destructive deletion, no provider/domain ownership transfer, no main merge, no release/tag.
- WP Playground temporary instance is not a durable production rollback artifact; browser-local persistence cannot replace an ANP filesystem+DB preimage.

**EXACT NEXT:** On a disposable WordPress+database instance, complete **remaining Post/Search/404 and PHP-debug-log smoke plus a Theme switch-away/back rollback rehearsal**, checking native content/data continuity. Keep source candidate and production untouched. Only then advance to genuine L4/L5; main merge and live publish require separate release approval.

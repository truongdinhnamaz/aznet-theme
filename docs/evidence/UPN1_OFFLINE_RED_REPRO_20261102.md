# UPN1 Offline RED execution — 2026-11-02

**Scope:** Local PHP 8.4 CLI, three-file offline reconstruction from connector-read GitHub texts, not a complete Git checkout and **not** WordPress 6.9/PHP 8.1 certified runtime.

## Source and identity
- Test source: `tests/proposals/upn1-native-update-red.php`, PR #555, GitHub blob `70144ac1c4637d1f5690c3ec91e90d1b7038d664`.
- Baseline `style.css` main v1.3.98, GitHub blob `1f96eb92c749bcbf9753b088e8a970a132020549`; local `git hash-object style.css` = **same blob**.
- Bootstrap source main GitHub blob `159b31bd08dce27c168fce83ae86b60415d9be17`. Local bootstrap was reconstructed with matching relevant `require_once` and `add_filter` lines, but whole-file blob differs; the test file was also reconstructed and whole-file blob differs. Therefore evidence is **offline reproduction of the intended assertions**, not exact Git checkout test identity.
- `git clone --depth 1 --branch test/upn1-red-notification-20261102` failed before checkout: `Could not resolve host: github.com`. GitHub connection separately read original source successfully.

## Command
`php -l tests/proposals/upn1-native-update-red.php` -> `No syntax errors detected`.

`php tests/proposals/upn1-native-update-red.php` -> **exit code 1**, with stdout:
```text
RED: Update URI is explicitly declared in style.css
RED: WordPress external theme update host filter is registered
```
and stderr:
```text
EXPECTED RED: native update-notification entrypoint is absent. No claim is made about runtime, install authenticity, or release readiness.
```

## Interpretation
- **PASS**: Offline RED reproduced: the intentional static entrypoint assertions fail for the specific missing `Update URI` and registered update integration.
- **UNKNOWN**: Exact Git checkout execution, PHP 8.1 compatibility, WordPress 6.9 admin/update transient, approved release transport, package integrity, real upgrade/rollback, external D-040 Template Distribution Service L5.
- UPN0 architecture source proposal PR #554 remains DRAFT, not Accepted. **Do not** implement production updater or merge/release/deploy based on this RED reproduction.

## Next
After UPN0 owner acceptance, run exact-head RED via GitHub CI or a clean checkout and implement minimal GREEN with WordPress runtime tests. Keep PR #555 draft until then.

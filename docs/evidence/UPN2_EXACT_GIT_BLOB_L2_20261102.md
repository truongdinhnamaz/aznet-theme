# UPN2 — Byte-exact GitHub blob PHP test — 2026-11-02

## Gate / scope
PR #556 head before evidence: `7dd54e7f0f669c6b7001ecadaf28c2e4e4d1b8ca`. Test target: exact candidate blob bytes retrieved via the connected GitHub content API and reconstructed locally without HTTP Git access.

## Byte identity evidence
Local `git hash-object <file>` exactly matched the `sha` returned by GitHub `fetch_file` for all three files:
- `inc/theme/core-update-notice.php` — `b0e8e100653a085e930af94ed403040057e9de1c`
- `tests/offline/upn2-core-update-notice-contract.php` — `ebbd93b096b95bb7d1e9d6cce1f9dcb532fc07ea`
- `tests/offline/upn2-wp-global-stub.php` — `7fa7bb0cca9c71f9fa2d8ecb4a6b6fa276b2c161`

## Execution
- Runtime: PHP 8.4.24 CLI (not the WordPress support floor PHP 8.1).
- `php -l` for each of the 3 byte-verified files: **3/3 PASS**.
- `php -d display_errors=1 -d error_reporting=32767 tests/offline/upn2-core-update-notice-contract.php`: **exit 0; 19/19 PASS**.
- Covered: WordPress host hook; official newer stable release metadata; no package; single request and cached result; same/lower version; draft/pre-release; untrusted URL; malformed tag; HTTP 503 failure; unrelated theme/client isolation; native updater queue suppression; read-only admin notice and no broken install link.

## Limitations / honest acceptance level
**PASS: exact-Git-blob isolated L2 PHP assertions**, not the previous manually reconstructed test proxy. A full Git checkout was not available because this runtime cannot resolve github.com, but byte identity of each executed PHP source/test file was verified. No source change was required to pass.

**NOT PASS / UNKNOWN:** PHP 8.1; real WordPress 6.9 update core logic and authenticated wp-admin; actual GitHub networking from WordPress instance; true release signal and positive notice with a later official version; template-service D-040 external L5; package integrity/rollback; main merge; official release; deployment. Local executor has no installed WordPress or Docker.

## EXACT NEXT
Run WordPress 6.9/PHP 8.1 runtime/admin verification on an authorized disposable test instance (both same-version/no-notice and newer official release simulated via controlled HTTP response), then stage source/implementation merge review. Do not ship a theme update package or deploy to customer production before independent approval.

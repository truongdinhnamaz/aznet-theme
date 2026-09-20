# AZnet Theme v1.3.20 lstamduchn.vn Production Preflight — BLOCKED_EXTERNAL_ACCESS — 20/09/2026

## Scope

Owner approval for production deployment of exact published AZnet Theme `v1.3.20` was received. Before any production mutation, a fresh authenticated read-only preflight was executed against `https://lstamduchn.vn`.

No production mutation occurred.

## Exact release target

- Canonical implementation: `a81067b1f1804783a35e8113ebf6859c7fcc38fa`.
- Published release: `v1.3.20`, GitHub Release `392420684`.
- Asset: `576757385`, `aznet-theme-1.3.20.zip`.
- SHA-256: `96ff01960e17d1da7167c5c3e296abbdabad0e5d51603f0f73ca4513ac880c00`.

## Fresh preflight

Operational branch: `ops/deploy-v1.3.20-lstamduchn`.

Read-only preflight run: `35513866003`.

Result: **FAILURE / BLOCKED_EXTERNAL_ACCESS** before WordPress authentication.

Observed transport behavior:

- DNS resolution completed.
- TCP/443 was reachable.
- HTTPS root emitted TLS ClientHello then timed out.
- HTTPS `/wp-login.php` emitted TLS ClientHello then timed out.
- HTTP/80 redirected normally to HTTPS, after which the TLS connection timed out.
- Playwright could not reach `/wp-login.php` within the 30-second navigation timeout.

The authenticated read-only gate therefore could not determine the active Theme version, current Theme-file state, current rollback baseline, System Health, menu-location continuity or public post-deploy baseline.

## Evidence

- Workflow run: `35513866003`.
- Evidence artifact: `10606485629`.
- Artifact digest: `sha256:afe6b8b35c8744a13602577082ca11784a18a3d55ed8a86ad7a7e2470b145617`.

WPVibe fallback was also unavailable during this execution window because the account reported `550 / 500` rolling-24-hour calls used and `0 remaining`; the account indicated capacity should begin returning around 21/09/2026 03:40 UTC.

## Safety disposition

Deployment is **not attempted**.

This is intentional fail-safe behavior: without a fresh authenticated read path, the deployment cannot prove the current active Theme or a safe rollback baseline. The exact published `v1.3.20` release remains ready, but live production state remains UNKNOWN. The last source-backed verified live Theme remains `1.3.10`; that value is historical evidence, not a fresh assertion.

## Next

Restore one secure authenticated read path (GitHub-hosted HTTPS or WPVibe capacity), rerun the read-only preflight, and only if active Theme state + rollback baseline are freshly verified proceed with the owner-approved exact `v1.3.20` deployment.

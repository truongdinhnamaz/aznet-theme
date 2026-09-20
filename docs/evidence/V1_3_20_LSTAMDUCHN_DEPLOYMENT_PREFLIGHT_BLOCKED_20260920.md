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
- Attempt 1: FAILURE / BLOCKED_EXTERNAL_ACCESS before WordPress authentication.
- Attempt 2: FAILURE / BLOCKED_EXTERNAL_ACCESS with the same transport symptom: TLS ClientHello followed by SSL connection timeout for both `/` and `/wp-login.php`; Playwright again timed out at `/wp-login.php` after 30 seconds.
- Attempt-1 evidence artifact: `10606485629`.
- Artifact digest: `sha256:afe6b8b35c8744a13602577082ca11784a18a3d55ed8a86ad7a7e2470b145617`.

WPVibe fallback was also rechecked after attempt 2 and remained unavailable because the account daily cap was still exhausted. WPVibe reported capacity should begin returning around 21/09/2026 03:30 UTC.

## Safety disposition

Deployment is **not attempted**.

This is intentional fail-safe behavior: without a fresh authenticated read path, the deployment cannot prove the current active Theme or a safe rollback baseline. The exact published `v1.3.20` release remains ready, but live production state remains UNKNOWN. The last source-backed verified live Theme remains `1.3.10`; that value is historical evidence, not a fresh assertion.

## Next

Restore one secure authenticated read path (GitHub-hosted HTTPS or WPVibe capacity), rerun the read-only preflight, and only if active Theme state + rollback baseline are freshly verified proceed with the owner-approved exact `v1.3.20` deployment.


## WPVibe-independent access attempts

The deployment path was explicitly continued without relying on WPVibe.

### Tor egress fallback

A Tor SOCKS egress path was added on the GitHub-hosted runner with certificate validation retained end-to-end.

- Tor circuit bootstrap: PASS via `https://check.torproject.org/api/ip`.
- Fresh preflight run: `35515941559`.
- Direct GitHub-runner HTTPS to `lstamduchn.vn`: TLS ClientHello -> SSL connection timeout.
- Tor-routed HTTPS to `lstamduchn.vn`: SOCKS connection established, then TLS -> SSL connection timeout.
- Tor-routed authenticated Playwright preflight: timed out at `/wp-login.php` before authentication.
- Evidence artifact: `10606827830`.
- Artifact digest: `sha256:4dffd8538f2ef3cc042a0e57ed526c70a71c66c37aa69547b4766e26e0431ce4`.

This proves the blocker is not simply the original GitHub-hosted runner egress IP and does not depend on WPVibe availability.

### Alternate GitHub-hosted runner matrix

Fresh no-secret TLS probes were then executed across multiple GitHub-hosted runner families in run `35516159536`:

- `ubuntu-22.04`: FAIL.
- `ubuntu-24.04`: FAIL.
- `macos-14`: FAIL.
- `macos-15`: FAIL.

All four failed to establish the target HTTPS login surface. No WordPress credentials were used in this matrix and no production mutation occurred.

## Revised blocker classification

**BLOCKED_EXTERNAL_TLS_TERMINATION / ACCESS PATH**, independent of WPVibe.

The remaining requirement is one secure authenticated path that can complete TLS to the production site. Acceptable examples are:

1. repair/allow the target HTTPS/WAF path for an external deployment runner;
2. use an authenticated hosting/SSH/SFTP/control-panel path that bypasses the broken public TLS edge;
3. run the deployment from a trusted local/self-hosted runner on a network from which `lstamduchn.vn` HTTPS works.

Do not send WordPress administrator credentials over plain HTTP and do not bypass TLS certificate verification for deployment.

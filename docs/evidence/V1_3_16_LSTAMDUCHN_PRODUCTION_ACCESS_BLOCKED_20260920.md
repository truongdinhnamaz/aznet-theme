# v1.3.16 lstamduchn.vn Production Access Recheck — BLOCKED

Date: 20/09/2026

## Scope

After owner-approved `v1.3.16` publication and source closure, a fresh read-only production preflight was attempted for `https://lstamduchn.vn` before any production mutation.

No deployment, upload, Theme replacement or live-site write was performed.

## Release identity retained

- Published release: `v1.3.16`.
- Exact implementation: `ac5f6a41122f11bfdf334a8e080532f6165aa120`.
- GitHub Release: `392259635`.
- Asset: `575729964` `aznet-theme-1.3.16.zip`.
- Asset SHA-256: `7d6798077bfa0dfcda5e770c31df5e89ff00cc82c4686d3256e9897a3959e431`.
- Last verified live Theme remains `1.3.10`; this is retained historical evidence only, not a fresh current assertion.

## Fresh GitHub-hosted runner recheck

The retained read-only preflight workflow on branch `ops/deploy-v1.3.15-lstamduchn` was rerun only to recheck the secure transport/authentication path. The run never reached any version-specific authenticated assertion because HTTPS failed before WordPress login.

- Workflow run: `35475064743`.
- Run attempt: `2`.
- Job: `105991561813`.
- Result: FAILURE at authenticated read-only preflight.
- Evidence artifact: `10594703662`.
- Artifact digest: `sha256:71457e89f245746aab2947d8ebfc38789b9bda7152a9aab52e8db9235e117e9e`.

### Network evidence

Fresh diagnostics reproduced the previous transport boundary:

- DNS returned the same three observed IPv4 backends: `103.166.183.10`, `103.56.163.10`, `103.216.118.10`.
- TCP port 443 connected successfully.
- TLS ClientHello was sent.
- No TLS server response arrived before timeout.
- Each observed IPv4 backend reproduced `SSL connection timeout`.
- Port 80 remained reachable and returned `301 Moved Permanently` to HTTPS.
- The observed IPv6 endpoint remained unreachable from the runner.
- Playwright navigation to `https://lstamduchn.vn/wp-login.php` timed out after 30 seconds, before WordPress authentication.

This reproduces the same external HTTPS/TLS access failure seen during the earlier v1.3.15 preflight. No evidence points to Theme code as the cause.

## WPVibe fallback

The connected WPVibe account was also checked. The account was over its rolling Free-plan daily fair-use allowance (`335/300`, `0` remaining at the check), so authenticated `site_info` could not run.

Therefore the alternative authenticated read-only path remained unavailable during this checkpoint.

## Safety decision

No production mutation was attempted because both secure authenticated paths were unavailable:

1. GitHub-hosted runner -> HTTPS stalls after ClientHello;
2. WPVibe authenticated fallback -> temporarily rate-limited.

Unsafe shortcuts remain forbidden:

- do not send WordPress credentials over HTTP;
- do not infer the current live Theme from stale evidence;
- do not select a rollback package until the active pre-deploy Theme is freshly verified;
- do not infer production state from release/publication state.

## State

**PASS**
- `v1.3.16` technical/publication identity remains intact.
- The production-access failure was freshly reproduced and localized to external HTTPS/TLS transport before WordPress authentication.
- Production remained unmodified.

**BLOCKED_EXTERNAL_ACCESS**
- GitHub-hosted runner -> `lstamduchn.vn:443`: TCP succeeds; TLS stalls after ClientHello.
- WPVibe authenticated fallback: temporarily unavailable due rolling usage cap.

**UNKNOWN**
- Fresh current active Theme version.
- Fresh authenticated WordPress state.
- Safe exact rollback baseline.
- Any production deployment status for `v1.3.16`.

## Exact Next

Restore one secure authenticated read path, then rerun read-only preflight:

- either the site's HTTPS termination must accept the runner path;
- or WPVibe authenticated access must become available again.

Only after the active Theme version and rollback baseline are freshly verified may a separately owner-approved `v1.3.16` production deployment proceed.

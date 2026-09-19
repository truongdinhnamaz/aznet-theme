# v1.3.15 lstamduchn.vn Production Deployment Preflight — BLOCKED

Date: 20/09/2026

## Scope

Owner approved production deployment of the exact published AZnet Theme `v1.3.15` asset to `https://lstamduchn.vn`.

This checkpoint records the mandatory read-only preflight and transport diagnosis performed **before any production mutation**.

## Release identity retained

- Published release: `v1.3.15`.
- Exact implementation source: `6cf48d1653d6468ec5aa25761c1a5b1dc82d4aa3`.
- GitHub Release: `392243619`.
- Asset: `575623879` `aznet-theme-1.3.15.zip`.
- Asset SHA-256: `8f91e9bd3a40a7eae4e6984a77f2735f339d188e52cfc6f97fac57a96bfcecd8`.
- Package identity: 148 production files, 111 packaged PHP files.

The last source-backed live verification before this deployment attempt reported AZnet Theme `1.3.10` on `lstamduchn.vn`. That remains **last verified**, not a fresh current assertion.

Published `v1.3.10` exists as a potential rollback package only if a fresh preflight reconfirms that it is still the active pre-deploy version:

- GitHub Release `391877350`.
- Asset `573847909` `aznet-theme-1.3.10.zip`.
- SHA-256 `5ff6c4862839dcdb6dfbbb77a71cb8bd87259b83cfbf308aa6b04279396bd572`.

No rollback package is authorized merely from stale state; the active version must be freshly verified before mutation.

## Read-only preflight branch

Operational branch: `ops/deploy-v1.3.15-lstamduchn`.

No file from this branch is merged to production source and no production Theme mutation was attempted.

### Initial preflight

- Head: `a2e3f03181d595fc4a3d7dda68839f6311b9aae7`.
- Run: `35474904344`.
- Result: FAILURE before authentication.
- Artifact: `10593998752`.
- Artifact digest: `sha256:30a127496e16222ae02a924997996b5b03d0c2ff5302286c6d34bf0f493303c2`.

Failure:
- Playwright timed out while navigating to `https://lstamduchn.vn/wp-login.php`.
- The failure occurred before WordPress login and before any site mutation.

### Network/HTTP diagnosis

Diagnostic head: `800586ac4443d32be94d4beca6e9a955238e284a`.

Run: `35475064743`.

Observed DNS:
- IPv4 `103.166.183.10`
- IPv4 `103.56.163.10`
- IPv4 `103.216.118.10`
- IPv6 was also advertised; GitHub-hosted runner had no route to the observed IPv6 endpoint.

For **each of all three IPv4 backends**:

1. TCP port 80 accepted the connection.
2. HTTP returned `301 Moved Permanently` to `https://lstamduchn.vn/`.
3. TCP port 443 accepted the connection.
4. After the TLS ClientHello, no TLS server response was received.
5. The TLS connection timed out.

The authenticated read-only browser preflight therefore again failed before WordPress authentication.

Run artifact:
- ID `10594058914`.
- Digest `sha256:7d6fd2204f6b5582765720d994b54534f2489b63177109835e4444ab46ebc4e8`.

## TLS protocol isolation

Temporary diagnostic workflow head: `58c41c22d251fa8066a8925472d49e3853ad9cf1`.

Run: `35475224372`.

Result of explicit protocol probes against **each IPv4 backend**:

- forced TLS 1.2: TCP connected, ClientHello sent, SSL connection timeout;
- forced TLS 1.3: TCP connected, ClientHello sent, SSL connection timeout.

Therefore the direct CI failure is not explained by TLS 1.3 negotiation alone. The transport is blocked/stalled between GitHub-hosted runners and the site's HTTPS termination after TCP connect and before ServerHello.

## Alternative connected WordPress path

A fresh WPVibe read-only site-info attempt was also made before deployment. The connected WPVibe account had reached its rolling daily fair-use limit, so that authenticated path was unavailable during this execution window.

No claim is made that the site's WordPress credentials are invalid: the direct CI path never reached the login surface.

## Safety decision

No production mutation was performed.

The following unsafe shortcuts are explicitly rejected:

- do not send WordPress credentials over HTTP port 80;
- do not deploy without freshly verifying the current active Theme version;
- do not assume the stale `1.3.10` observation is current;
- do not choose a rollback package from stale state;
- do not infer deployment success from publication success;
- do not bypass HTTPS/TLS verification.

## State

**PASS**
- `v1.3.15` publication identity remains verified.
- Deployment preflight failure is reproduced and localized below DNS/TCP to the HTTPS/TLS transport boundary.
- All three IPv4 backends and TLS 1.2/1.3 were tested.
- Production remains unmodified.

**BLOCKED_EXTERNAL_ACCESS**
- GitHub-hosted runner -> `lstamduchn.vn:443`: TCP succeeds, TLS handshake stalls after ClientHello.
- WPVibe authenticated fallback: temporarily unavailable due rolling daily usage cap.

**UNKNOWN**
- Fresh current production Theme version.
- Current WordPress authenticated state.
- Safe exact rollback package until the pre-deploy Theme version is freshly verified.

## Exact Next

Restore one secure authenticated path to `lstamduchn.vn`:

1. either the site/host HTTPS termination must accept the deployment runner path;
2. or the existing WPVibe authenticated path must become available again.

Then rerun read-only preflight first. Only after current Theme version + baseline are freshly verified may the owner-approved `v1.3.15` production mutation proceed with an exact rollback package and post-deploy verification.

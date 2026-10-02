# Rèm 01 WPVibe external-access blocker evidence — 02/10/2026

## Scope

This checkpoint records the external connectivity blocker preventing Theme-side runtime/admin verification on `remquocanh.vn`.

## User-provided WPVibe connection report

Checked around `2026-10-02T00:45:53Z` to `2026-10-02T00:45:57Z`.

Local/site-side checks passed for:

- WPVibe plugin active.
- WordPress HTTPS.
- Application Password availability.
- Current WordPress account is administrator and can create an Application Password.
- WordPress address resolves to `https://remquocanh.vn`.
- REST API answers from inside the site with the routes WPVibe needs.
- Authorization header reaches WordPress.

The report explicitly did not validate saved credentials in that local check.

## Remote failure evidence

WPVibe remote/direct and relay installation checks returned HTTP 503 for both supported connection-check paths:

- `GET https://remquocanh.vn/?rest_route=/wpvibe/v1/connection-check-challenge`
- `GET https://remquocanh.vn/wp-json/wpvibe/v1/connection-check-challenge`

Observed request evidence:

- `2026-10-02T00:45:54.651Z` direct public capture → HTTP 503, Cloudflare Ray `a43fafe09d227981-SIN`.
- `2026-10-02T00:45:54.651Z` relay public capture → HTTP 503.
- `2026-10-02T00:45:56.456Z` direct public capture → HTTP 503, Cloudflare Ray `a43fafebdfd47981-SIN`.
- `2026-10-02T00:45:56.456Z` relay public capture → HTTP 503.
- WPVibe configured static relay source IP reported by the diagnostic: `192.241.129.49`.

A fresh Theme-side read retry after receiving the report still returned WP API 503, so retries were stopped.

## Disposition

State: **BLOCKED_EXTERNAL_ACCESS**.

This evidence does not establish a broken credential, PHP fatal, host firewall block, Cloudflare rule, or proxy fault by itself. The responsible layer must be identified from hosting/Cloudflare/origin logs using the UTC timestamps and Ray IDs above.

No Theme architecture, domain ownership, public contract, or pilot production state is invalidated by this blocker.

## Exact next

Hosting/Cloudflare support should inspect the matching requests and identify which layer produced HTTP 503. If a security rule is responsible, use only a narrowly scoped exception for this site and the affected WordPress REST paths. Do not disable the firewall globally and do not reconnect WPVibe solely because of this 503.

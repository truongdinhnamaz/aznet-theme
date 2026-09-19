# Pilot Target Supersession — 2026-09-19

## Decision

The product owner replaced the AZnet Theme operational pilot completely:

- active pilot: `https://lstamduchn.vn/`;
- retired pilot: `https://tamduchanoi.aznet.vn/`.

This is an operational QA/site-operations target change. It does not change AZnet Theme ownership, provider contracts, release constitution, or domain authority.

## Evidence boundary

Historical runs, deployment records, screenshots and findings produced against `tamduchanoi.aznet.vn` remain immutable evidence for that exact site/time/version. They are not current-pilot evidence and MUST NOT be transferred as PASS to `lstamduchn.vn`.

At this checkpoint, replacement-pilot site-specific state is UNKNOWN until fresh verification:

- active Theme/plugin inventory;
- WordPress/PHP runtime;
- System Health;
- public route/browser/responsive/a11y state;
- RootProfile/ConvertFlow optional-provider availability;
- package/live-byte identity;
- deployment state.

Canonical source/package/support-floor evidence that is not site-specific remains retained.

## Active configuration migration

Current pilot QA defaults/workflows are being switched to `https://lstamduchn.vn`. The old Tâm Đức homepage mutation plan is explicitly marked `historical_retired_pilot` and must not be replayed on the replacement pilot; a fresh baseline is required before any mutation plan.

## Governance

AZT-04 D-030 owns the pilot-target decision. AZT-03 records the provenance boundary. AZT-EXEC-MAP derives the operational execution target.

Production deployment remains a separate explicit owner gate.

## Rollback

If this target assignment itself is later superseded, create a new source/evidence checkpoint. Do not rewrite this historical record.

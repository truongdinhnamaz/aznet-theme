# Tâm Đức exact Homepage 1.3.54 staging attempt — 27/09/2026

## Goal

Execute the owner-selected option 2: load the complete canonical AZnet Theme 1.3.54 on a staging WordPress and close Homepage L3/L4 without using production.

Canonical source at start: `main@55dda598adc7bd677ea2d5301fb42ecc90eae94a`, Theme metadata `1.3.54`.

## Staging target

`https://tamduchanoi.aznet.vn` was selected. Fresh authenticated metadata before this slice showed WordPress `7.1.2`, PHP `8.4.25`, active AZnet Theme `1.3.1`, WPVibe `1.17.2`, and a static Front Page.

## Exact package / rollback preparation

- Exact target package source is pinned to canonical `55dda598adc7bd677ea2d5301fb42ecc90eae94a`.
- Exact rollback source for the current staging Theme was identified as `89cc97599068c57f3446e617d0076e9f67936e35`; `style.css` and `functions.php` both declare `1.3.1`.
- Branch `ops/tamduc-homepage-1354-runtime-staging` contains a one-shot staging workflow plus read-only Homepage L3/L4 browser harness.
- The workflow builds deterministic target/rollback ZIPs, uses WordPress core Theme Upload/Replace, verifies System Health, Template Library, Homepage Map, native Front Page card, Process/FAQ full editor actions, admin/frontend surface-order parity, desktop/mobile overflow and axe, and rolls back to exact `1.3.1` on failure.

## GitHub Actions result

Push `0032b3156a6afd7a0d4eb560b934d16f8f58c4de` triggered workflow run `36296778282` / job `108556977309`.

Result: workflow completed `failure` before any workflow step executed; job steps are empty. This reproduces the external hosted-runner failure and is not Theme-code evidence.

## Direct staging alternatives checked

- WPVibe CLI `theme install <GitHub ZIP URL>`: refused as `Theme not found`; the emulator accepts installable theme slugs, not arbitrary package URLs.
- `theme list --update=available`: no update source exists for AZnet Theme.
- WPVibe REST exposes draft file operations but no exact Theme package upload/install route.
- WPCode-based one-time installer is unavailable because WPCode is not installed; per WPVibe safety contract, installing WPCode requires the site owner's own explicit opt-in and cannot be silently added by the assistant.
- Local container has PHP 8.4, Node 22 and Chromium but no MySQL/MariaDB server or PDO SQLite. Package/network installation is unavailable in that runtime, so it cannot be promoted to an actual WordPress staging environment.
- Render tools available in this session do not provide the Docker/MySQL combination required to create a faithful WordPress staging instance directly.
- `remquocanh.vn` exposes `aznet-devbridge/v1/deploy`, but it is a production site, not the owner-selected staging target; using that deployment surface would cross the production deployment gate.

## State

- Canonical Homepage L1/L2 PASS remains retained.
- Exact-current L3: `UNKNOWN`.
- Exact-current L4: `UNKNOWN`.
- Current hard block: no connected non-production WordPress target in this session can accept the complete exact 1.3.54 package through an approved package-install path, and GitHub-hosted runners still execute zero steps.

## Recovery

Do not publish the incomplete WPVibe draft on `tamduchanoi.aznet.vn`.

Resume when one of these becomes available:

1. a staging WordPress URL/account with a working Theme ZIP upload/replace path; or
2. the owner explicitly installs/enables a package-capable helper on the existing staging site; or
3. GitHub hosted runners execute again, allowing the already-prepared exact staging workflow to run.

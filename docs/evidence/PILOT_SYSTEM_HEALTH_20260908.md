# Pilot System Health Evidence — 2026-09-08

**Pilot:** `tamduchanoi.aznet.vn`  
**Evidence source:** authenticated WordPress Admin -> AZnet Theme -> System Health screenshot supplied by product owner.  
**Purpose:** record real-site runtime/configuration evidence for the law-site production-readiness audit. This is evidence, not a source/architecture decision.

## Observed active environment

- WordPress: `7.1`
- PHP: `8.4.24`
- Active Theme name: `AZnet Theme`
- Active Theme version: `1.1.0`
- Visual preset: `editorial`
- Header preset: `standard`
- Woo catalog preset: `grid`
- Custom logo: `no`
- Primary menu: `yes`

## Observed public capability diagnostics

- WooCommerce: `no`
- RootProfile provider v1: `yes`
- RootProfile provider v2: `yes`
- RootProfile current-surface v1: `no`
- ConvertFlow: `unknown`

`ConvertFlow=unknown` is expected under the accepted boundary when no documented public Theme-consumable capability is available; the Theme must not privately infer provider state.

## What this proves

- The active pilot Theme is the promoted `1.1.0` implementation, not the prior `1.0.0` install.
- The R5 Control Center/System Health code is executing on the real site.
- The site retains a primary WordPress menu after activation.
- RootProfile public provider v1/v2 are detectable by the Theme; current-surface takeover capability is not available.
- WooCommerce is not part of the current pilot runtime.

## What this does not prove

- It does not prove the active stylesheet/folder slug by itself.
- It does not prove full frontend route/browser/a11y compatibility on WordPress 7.1 / PHP 8.4.24.
- It does not certify RootProfile current-surface takeover or ConvertFlow integrated compatibility.
- It does not justify deleting any inactive legacy Theme directory without checking the inactive card/version and preserving rollback confidence.

## Pilot identity-cleanup consequence

The previous blocker "active Theme still may be 1.0.0" is cleared. The active site is on `1.1.0`.

Safe cleanup sequence:

1. frontend smoke the active `1.1.0` site;
2. in WordPress Appearance -> Themes, inspect each inactive `AZnet Theme` card;
3. delete only inactive legacy versions after confirming they are not the active `1.1.0` install;
4. if an inactive card also reports `1.1.0`, stop and capture its details before deletion;
5. retain canonical Git/package rollback evidence outside the live Theme directory.

## Next

Close authoritative R6 source drift, then execute the editorial production-readiness slice and real-site pilot QA before production release/deployment is claimed.
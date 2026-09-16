# P4 Authenticated Pilot QA — 2026-09-16

**Pilot:** `https://tamduchanoi.aznet.vn`  
**Scope:** authenticated, read-only WordPress Admin / AZnet Theme Control Center / System Health verification plus post-replacement public-matrix revalidation.  
**Theme scope:** AZnet Theme only. Optional provider certification remains separate.

## 1. Purpose

Close the remaining authenticated/Admin portion of P4 Real Pilot QA without transferring WordPress, RootProfile, ConvertFlow, WooCommerce or SEO-provider ownership into the Theme and without using private provider storage/API reads.

The harness is deliberately read-only after the WordPress login submit action. It does not save Theme settings, run provisioning, reset/import settings, update plugins/themes, publish content, delete content or deploy a release.

## 2. Auth path and RED -> GREEN

The product owner supplied `PILOT_WP_USER` and `PILOT_WP_PASSWORD` as GitHub Actions repository secrets; credentials were not placed in source, logs or artifacts.

Initial contract RED run `35047029137` failed for the intended reason: the authenticated pilot workflow did not yet exist.

A first GREEN implementation exposed one over-strict static contract marker. That contract was corrected at the test layer; the harness behavior was not loosened.

The first real authenticated runtime execution then reached the pilot successfully and exposed a legitimate environment drift: the pilot was running historical Theme `1.1.1` bits with the pre-D-027 `configuration/capabilities` System Health shape. The harness reduced the downstream symptom cascade to one explicit blocker: `PILOT_THEME_BITS_DRIFT`.

## 3. Canonical pilot replacement

The pilot Theme was replaced through normal WordPress Theme upload/replace UI with the exact D-027 candidate package previously verified by run `35042040620`:

- Theme version: `1.1.0`
- Package SHA-256: `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`

The replacement did not run Quick Setup, provisioning, reset or import actions. WordPress-owned content/domain data remained outside Theme file ownership.

## 4. Fresh authenticated PASS after replacement

GitHub Actions run `35047440842`, latest attempt, completed successfully on authenticated harness functional head `dc807c932665fead447e14b792b63dfd39a47ede`.

Fresh artifact:

- name: `p4-authenticated-pilot`
- artifact ID: `10427876863`
- digest: `sha256:96d2943661e27ef05ce5b16940a792fbd4727d635849a8f5e9e8589b03d2abe9`

Observed System Health after replacement:

- WordPress: `7.1`
- PHP: `8.4.25`
- active Theme: `AZnet Theme`
- active Theme version: `1.1.0`
- report shape: `d027_current`
- Standalone Core status: `ready`
- visual preset: `editorial`
- header preset: `standard`
- custom logo: `false`
- primary menu: `true`
- WooCommerce: `not_present`
- RootProfile v1: `available`
- RootProfile v2: `available`
- RootProfile current surface: `not_present`
- ConvertFlow: `unknown`

The Admin overview exposed exactly one AZnet Theme menu entry and the expected `AZnet Theme` heading.

At 1440x1000 and 390x844 on the System Health surface:

- horizontal overflow: `0`
- focused Control Center tab retained visible focus indication
- axe critical/serious findings inside the Theme Control Center: `0`
- Theme-owned console/page/request blocking failures: `0`

The recorded aborted `admin-ajax.php` / Cloudflare RUM requests were not Theme-owned blocking failures.

## 5. Fresh public revalidation after Theme replacement

Because replacing the active Theme invalidated the earlier public visual/runtime observation, the existing P4 public workflow was rerun against the new active `1.1.0` pilot.

Run `35045540722`, rerun attempt after replacement, completed successfully:

- 7 public routes x 4 viewports = `28` checks
- Homepage, representative Post, representative Page, Category, search results, search no-results and 404
- Theme-owned blocking failures: `0`
- horizontal overflow: `0`
- each route retained one `<main>` and at least one H1
- Theme-owned console/page/request errors: `0`
- RootProfile public profile surface: `NOT_OBSERVED_ON_TESTED_ROUTES`

Fresh public artifact:

- name: `p4-public-pilot`
- artifact ID: `10427713118`
- digest: `sha256:c58d0efebe9fdeea14c2d9d1ba3fa8a4439f2bafffc1dc180cf3b1d1554cbadb`

The representative Post still contains ten unlabeled task-list checkboxes inside WordPress-owned authored content. The public harness retains those as `CONTENT_AUTHORED_SEMANTICS`; it does not silently assign authored-content semantics to the Theme.

## 6. What this proves

At the tested Theme-owned P4 scope, the real pilot now runs the canonical `1.1.0` D-027 System Health shape and passes both the fresh authenticated read-only Admin/System Health matrix and the fresh public route/viewport matrix after replacement.

This clears the previous authenticated-access blocker and the discovered historical-Theme-bits drift blocker.

## 7. What this does not prove or authorize

- It does not certify RootProfile current-surface/contact integration at L5; no such public surface was observed in the tested public route set.
- It does not promote ConvertFlow beyond `unknown` without a documented public Theme-consumable capability.
- It does not certify WooCommerce on this pilot because WooCommerce is not present.
- It does not authorize destructive duplicate-theme deletion.
- It does not authorize a Git tag, GitHub Release or final production deployment.
- It does not replace clean WordPress support-floor, deterministic package or exact-main evidence already owned by D-027/R6.

## 8. Next

Merge the authenticated QA harness/evidence through the normal PR gate, then reconcile authoritative current-state sources. P5 publication/deployment remains a separate owner approval gate.
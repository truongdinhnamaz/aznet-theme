# D-027 Standalone Core — Candidate Evidence

**Date:** 2026-09-16  
**Decision:** D-027  
**Design/source PR:** #63 (`docs/d027-standalone-core`)  
**Implementation branch:** `work/d027-standalone-core`  
**Verified functional head:** `9f5b5e7b685e093f8aba1386890542fb768d725b`  
**Status:** PASS CANDIDATE through D-027 L1-L4 + retained regression + exact-package pre-P5 gate; **not merged to canonical main**.

## 1. Scope and ownership

D-027 proves that AZnet Theme Core has zero mandatory third-party runtime dependency on the supported floor. WordPress + AZnet Theme alone must complete install/activate/setup/provision/author/render without WooCommerce, RootProfile, ConvertFlow, SEO plugins or mandatory external runtime services.

Ownership remains unchanged:

- WordPress owns Post/Page/Menu/Media/query/publication state;
- WooCommerce owns commerce truth;
- RootProfile owns authoritative identity/Profile semantics;
- ConvertFlow owns Journey/conversion semantics;
- AZnet Theme owns presentation, bounded Theme configuration and explicit WordPress-native provisioning orchestration.

No provider private storage/API, authoritative slug/title/Page-ID routing heuristic, provider-domain clone or parallel truth store was added.

## 2. TDD evidence

### Focused RED

Run `35039957536` failed for the intended missing contract:

`FAIL: system_health_report must expose standalone_core`

This demonstrated that the pre-D-027 System Health shape did not explicitly separate Core readiness from optional provider availability.

### Minimal GREEN

The production change is bounded to:

- `inc/admin/system-health.php` — separates `standalone_core` from `optional_integrations`;
- `inc/admin/control-center.php` — presents Standalone Core independently from Optional Integrations and uses human-readable provider labels.

Provider absence is informational (`not_present` or `unknown` where no approved detector exists) and does not downgrade Standalone Core readiness.

Initial GREEN run `35040121215` succeeded. Retained R5 Control Center regression also remained GREEN.

## 3. Fresh same-head verification

All closure runs below target verified functional head `9f5b5e7b685e093f8aba1386890542fb768d725b`:

| Layer/gate | Workflow run | Result |
| --- | ---: | --- |
| D-027 Offline / L1-L2 | `35041843130` | SUCCESS |
| Clean WordPress runtime / L3 | `35041843115` | SUCCESS |
| Browser / responsive / a11y / L4 | `35041843090` | SUCCESS |
| Retained full v1 Core regression | `35041843129` | SUCCESS |
| Exact-package standalone pre-P5 gate | `35041843120` | SUCCESS |

The retained full-regression run executes `scripts/verify-v1-core.sh` plus the D-027-specific offline contracts on PHP 8.1.

## 4. Standalone runtime proof

The L3 path uses WordPress `6.9`, PHP `8.1`, AZnet Theme `1.1.0` and zero active third-party plugins. It proves:

- Theme activation on clean WordPress;
- retained Law01 v1 provisioning behavior;
- Law Site Provisioning v1.1 new-site setup and rerun/idempotency;
- Theme-owned Homepage preset state plus WordPress-owned Front Page/Menu/Page/Post/Attachment outputs;
- Hero media imported into WordPress uploads rather than served from the Theme package at runtime;
- System Health separates Standalone Core from Optional Integrations;
- representative Homepage, Post, Page, Category, Search and 404 runtime routes;
- no Theme-caused fatal/uncaught condition in the covered path.

## 5. Browser, responsive and accessibility proof

The D-027 L4 matrix covers four viewports:

- `1440x1000`;
- `1024x900`;
- `390x844`;
- `320x800`.

At each viewport it covers Homepage, Post, Page, Category, Search and 404 plus authenticated Provisioning/System Health admin surfaces. The exact-package artifact records **24/24 frontend route/viewport results, zero failures and zero horizontal overflow**.

The browser gate also checks keyboard focus, heading flow, axe critical/serious findings, console/page errors, no required-plugin dependency copy, Law01 Homepage rendering and same-origin WordPress-upload Hero media.

## 6. Exact-package pre-P5 evidence

Run `35041843120` verifies package bytes, not only the source tree.

- Workflow artifact ID: `10425199214`
- Artifact digest: `sha256:791d89fc5109dd9a73b333c837eb9a0ce1ac28b41bc759c08952dfc0d8189a0e`
- Theme version: `1.1.0`
- Exact D-027 candidate ZIP SHA-256: `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`
- Approved predecessor package SHA-256: `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`
- Theme-switch verification target: bundled WordPress `twentytwentyfive`

The gate proves:

1. deterministic double build and exact byte equality;
2. package hygiene and source/package byte matching;
3. package PHP lint;
4. clean WordPress 6.9 install with zero active plugins;
5. provisioning on the approved predecessor package;
6. in-place update from exact D-027 candidate ZIP;
7. D-027 runtime verifier after update;
8. browser/a11y matrix against the installed candidate ZIP bytes;
9. switch away to `twentytwentyfive` while WordPress-owned content/media/menu continuity survives;
10. switch back to AZnet Theme and reverify Standalone Core/runtime continuity.

## 7. Defects and harness corrections found during verification

One real Theme-owned presentation gap was found and fixed: Optional Integrations initially exposed machine key `woocommerce` instead of the intended human-readable `WooCommerce` label. The production fix is confined to Control Center presentation.

The following failures were test-harness mismatches/noise and were corrected without changing domain semantics:

- `law-site-provisioning-empty.php` is a post-provision verifier and was initially called before provisioning;
- `homepage_composer_active()` is request-scoped through `is_front_page()` and therefore must not be asserted true from a WP-CLI `eval-file` context;
- a correct HTTP 404 document causes Chromium's expected `Failed to load resource ... 404` console entry; only that exact entry is ignored on the 404 route, while other console errors still fail.

## 8. Rollback / recovery

- D-027 design predecessor: PR #63 head `b6c9546b00c61e4ff96f0df6f04c2aa5abeea7c5`.
- Canonical main predecessor used by the exact-package lifecycle gate: `fa1df2dd79ee7740298bb2e5685764ed3279584a`.
- Approved predecessor package SHA-256: `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`.
- No destructive domain migration, provider takeover or provider data mutation is part of D-027.

## 9. Non-claims / remaining gates

This candidate evidence does **not** claim:

- merge to canonical `main`;
- Git tag or GitHub Release;
- final production deployment;
- P4 real-pilot completion (pilot access remains a separate environment gate);
- optional provider L5 certification;
- RootProfile/ConvertFlow/WooCommerce/SEO ownership transfer or runtime requirement.

The next integration action is review of the stacked implementation PR after design/source PR #63. Any merge to canonical `main` remains an explicit owner approval gate. P5 publication/deployment remains separately gated.
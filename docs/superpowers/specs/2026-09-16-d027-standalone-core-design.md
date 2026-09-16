# D-027 — AZnet Theme Standalone Core Design

**Date:** 2026-09-16  
**Status:** Approved design, implementation pending  
**Scope:** AZnet Theme runtime/product independence on clean WordPress  
**Canonical repo:** `truongdinhnamaz/aznet-theme`

## 1. Decision summary

AZnet Theme has **zero mandatory third-party runtime dependency**.

A clean WordPress installation that satisfies the Theme support floor must be able to install, activate, configure, provision, author, render and operate the Theme's core product capabilities without installing WooCommerce, RootProfile, ConvertFlow, Rank Math, Yoast or any other third-party plugin/service.

External development and QA tooling such as GitHub Actions, WP-CLI, Playwright, axe and test runners is allowed because it does not become a website runtime dependency and is not shipped as a requirement for operating the Theme.

This decision strengthens the existing product principle that AZnet Theme is a standalone presentation product and that provider integrations are optional, public-contract-based enhancements.

## 2. Product invariant

> **AZnet Theme Core must be complete on WordPress + AZnet Theme only. Optional providers may add capabilities but may not complete a partially functional Theme.**

No AZnet Theme release may be declared Core Ready unless the exact final package passes a zero-plugin standalone path covering install -> activate -> setup -> provision -> render -> update/theme-switch verification.

Optional integration certification is additive and cannot substitute for Standalone Core PASS.

## 3. Core capability boundary

The following are Core capabilities and must work with zero active third-party plugins:

- Design System and Theme-owned visual presets;
- Header and Footer presentation;
- WordPress Custom Logo, Menu and native Search presentation;
- Homepage Composer and Theme-owned homepage presentation presets;
- `law-01` presentation preset;
- Quick Setup / Law Site Provisioning;
- starter Pages, Posts, Categories, Menu and Media created through WordPress public APIs;
- Page, Post, Archive, Category, Search/no-results and 404 presentation;
- WordPress-native author fallback;
- Control Center;
- System Health core readiness reporting;
- Theme-owned presentation settings import/export;
- responsive, accessibility and surface-aware performance behavior;
- update and theme-switch continuity for WordPress-owned content.

There is no "Core Lite" state that requires plugins to become a complete Theme.

## 4. Optional integration boundary

Optional providers remain domain owners and only enhance Theme presentation through public/versioned contracts.

### WooCommerce

When absent, AZnet Theme remains a complete non-commerce WordPress Theme.

When present, WooCommerce may enable catalog, product, cart, checkout and account presentation through public capabilities. WooCommerce remains owner of product, price, stock, variation, cart, session, checkout and order state.

The Theme may keep a Theme-owned `commerce` visual style as a visual preset, but it must not simulate a commerce engine when WooCommerce is absent.

### RootProfile

When absent, WordPress-native author/site presentation remains available. The Theme must not infer authoritative identity/profile semantics.

When present, RootProfile may enhance identity/Profile/Contact presentation only through approved public contracts. RootProfile remains owner of authoritative identity, Profile URL, claim/evidence/readiness and related semantics.

### ConvertFlow

When absent, Theme-owned Homepage Composer and native homepage presentation remain complete.

When present, ConvertFlow may provide Journey semantics and public projections through an approved contract. ConvertFlow remains owner of Journey structure, resolver, state, validation, conversion and analytics.

### SEO plugins

Rank Math, Yoast or similar plugins are never required for Theme core readiness. The Theme must coexist safely and must not write private SEO-plugin storage merely to provide standalone behavior.

## 5. UX and admin rules

### 5.1 No plugin nags

Core UX must not contain:

- "required plugin" notices for optional providers;
- setup blockers that force plugin installation;
- "Install required plugins" flows;
- marketplace redirects required to complete Theme setup;
- Core health warnings/errors caused only by an optional provider being absent;
- disabled core features with messaging that a third-party plugin is required to make the Theme complete.

Provider absence is informational and normal.

Preferred wording is equivalent to:

- `Optional capability: Available`
- `Optional capability: Not present`

and not:

- `Missing dependency`
- `Warning`
- `Error`

unless an explicitly enabled integration itself is malfunctioning and the warning is scoped to that optional integration rather than Core readiness.

### 5.2 Capability-driven optional UI

Optional-provider workflows should be hidden when the relevant public capability is absent. The Theme should not expose an inactive RootProfile/WooCommerce/ConvertFlow workflow merely to advertise installation.

When a provider becomes available, the Theme may capability-detect it and expose the corresponding presentation controls without changing Core ownership.

### 5.3 System Health separation

System Health must distinguish two domains:

**AZnet Theme Core**

- WordPress support status;
- PHP support status;
- active Theme/version;
- core configuration/readiness;
- primary menu/homepage/provisioning readiness as appropriate;
- core presentation health.

This section may produce a clear result such as:

`Standalone Core: READY`

**Optional Integrations**

- WooCommerce — Available / Not present;
- RootProfile — Available / Not present;
- ConvertFlow — Available / Not present / Unknown if the public detector itself is intentionally unavailable;
- other approved optional providers as informational capability rows.

A `Not present` result in Optional Integrations must not downgrade Standalone Core.

## 6. Provisioning runtime independence

Law01 Quick Setup / provisioning must be complete without an external service call.

Starter content/media required for the supported setup path must be shipped in the Theme package and applied through WordPress Core/public APIs.

Provisioning must not require runtime access to:

- an AZnet API;
- a mandatory CDN;
- a remote template repository;
- a license server for starter content;
- an AI service;
- a plugin marketplace;
- a third-party demo importer.

Starter Hero/editorial media may be bundled with the Theme and imported into the WordPress Media Library. After successful provisioning, created Pages/Posts/Categories/Menu/Attachments remain WordPress-owned content as already defined by the Law Site Provisioning architecture.

No external service may become authoritative storage for Theme setup state.

## 7. Standalone Core QA matrix

### L1 — Static dependency gate

Verify that:

- no third-party plugin is declared as a mandatory Theme runtime dependency;
- optional provider code is capability-guarded and isolated from Core execution paths;
- Core admin does not instruct users to install an optional plugin to make the Theme functional;
- no direct private provider storage/API dependency is introduced.

### L2 — Contract/TDD gate

Provider absence is a normal tested state.

Required zero-provider contract coverage includes:

- Quick Setup;
- Homepage Composer;
- `law-01`;
- starter content/media planning and provisioning;
- Header/Footer;
- Control Center;
- System Health Core readiness.

Optional adapters retain present/absent/malformed/version-mismatch tests independently.

### L3 — Full clean WordPress runtime

Environment baseline:

- WordPress support floor;
- PHP support floor;
- zero active third-party plugins.

Path:

1. install WordPress;
2. install and activate AZnet Theme;
3. confirm zero active plugins;
4. open Theme Setup;
5. execute Law01 provisioning with explicit confirmation;
6. create/import native Pages, Categories, Menu, Posts and Attachments through the Theme's WordPress-native setup path;
7. verify rerun/idempotency and user-edit preservation as applicable;
8. render Homepage, Post, Page, Archive/Category, Search/no-results and 404;
9. assert no Theme-caused PHP fatal/warning/uncaught condition within the covered path;
10. assert no external service is required to complete setup.

### L4 — Standalone browser/accessibility

Verify the setup/admin and resulting frontend at representative widths including 1440, 1024, 390 and 320.

Minimum checks:

- usable setup flow;
- complete homepage after provisioning;
- Hero media served from WordPress uploads after import;
- expected homepage sections populated by supported starter/native data;
- no horizontal overflow in covered routes;
- keyboard/focus/landmark/heading/label quality;
- covered axe critical/serious blockers = 0;
- no UX implying that the Theme is incomplete because an optional plugin is absent.

### L5 — Optional compatibility

WooCommerce, RootProfile, ConvertFlow, Rank Math/Yoast and future providers are certified separately.

A compatibility track may be `BLOCKED` or `UNKNOWN` without failing Standalone Core, provided:

- Core remains functional;
- the provider-absent path is safe;
- no ownership/private-contract violation exists in the Theme.

### L6 / P5 — Final-package release gate

The final package bytes, not merely a source-tree approximation, must pass the zero-plugin standalone path before publication/promotion.

Required proof includes:

- deterministic package integrity;
- clean install/activate from the package;
- zero active plugins;
- setup/provisioning;
- representative runtime/browser verification;
- update/theme-switch continuity for the covered WordPress-owned content;
- exact package/version/provenance evidence.

## 8. Zero-plugin first-run acceptance test

The highest-value product acceptance test is:

> On a new WordPress site, the user uploads only the AZnet Theme ZIP, activates it, enters Theme Setup and can produce a complete Law01 starter/demo website without leaving WordPress to obtain another plugin, service, asset pack or importer.

This test does not imply fake authoritative business data. Starter/demo content must continue to follow existing D-025/D-026 safety rules for generic, non-deceptive content and publication/indexing behavior.

## 9. P4 pilot interpretation

P4 Real Pilot QA remains valuable but is not allowed to redefine product dependency.

If the current QA runner cannot resolve or authenticate to the pilot website, the correct status is:

`P4 PILOT ACCESS BLOCKED`

It is not:

`Theme requires a WordPress connector/plugin`.

A third-party connector may never be introduced into the product merely to compensate for limitations of the assistant/test environment.

Pilot installation/update may still be performed through normal WordPress-native site operations under existing approval and rollback rules.

## 10. Development/QA tooling boundary

Allowed development/QA tools include GitHub/GitHub Actions, WP-CLI, Playwright, axe and other test infrastructure when they are used outside the deployed Theme runtime.

The architectural test is simple:

> If all development/CI systems disappeared, the released AZnet Theme ZIP would still install and operate as a complete Theme on supported WordPress without them.

No development tool may be bundled or required as a runtime dependency merely because it is useful for verification.

## 11. Ownership and storage constraints

D-027 does not transfer domain ownership into the Theme.

- WordPress continues to own native Post/Page/Menu/Media/query/publication state.
- WooCommerce continues to own commerce state.
- RootProfile continues to own authoritative identity/Profile semantics.
- ConvertFlow continues to own Journey/conversion semantics.
- Theme remains presentation owner plus bounded Theme-owned configuration and explicit WordPress-native provisioning orchestration.

Standalone behavior must not be achieved by copying provider state, direct-reading private provider storage, inventing authoritative fallback semantics or creating a parallel domain store.

## 12. Non-goals

D-027 does not require:

- implementing WooCommerce-like commerce without WooCommerce;
- reconstructing RootProfile identity without RootProfile;
- cloning ConvertFlow Journey semantics into the Theme;
- implementing SEO-plugin private schemas;
- bundling third-party plugins inside the Theme;
- creating a proprietary app/platform layer;
- eliminating external development/QA tooling.

## 13. Implementation implications

Implementation should prefer the smallest changes necessary to turn the invariant into executable proof.

Likely work areas:

- add/strengthen standalone static/contract tests;
- expand clean WordPress runtime from core route smoke to full Theme setup/provisioning proof;
- add standalone admin/browser/a11y coverage;
- separate Core readiness from Optional Integrations in System Health presentation;
- add regression guards against required-plugin nags/dependency declarations;
- require final package bytes to pass the standalone release path.

Production behavior that already satisfies D-027 should be retained rather than rewritten.

## 14. Acceptance criteria

D-027 implementation is complete only when all of the following are true:

1. A zero-plugin WordPress installation can install/activate AZnet Theme without errors.
2. Core authoring/presentation surfaces remain usable with all optional providers absent.
3. Law01 setup/provisioning completes using only bundled Theme resources plus WordPress public APIs.
4. System Health can report Standalone Core independently from Optional Integrations.
5. Provider absence is informational and never a Core readiness failure by itself.
6. There are no required-plugin nags or mandatory plugin-install flows for Core readiness.
7. Optional adapters continue to respect public contracts and fail-soft behavior.
8. L1-L4 standalone evidence passes on the support floor.
9. The exact final release package passes the defined zero-plugin package verification before P5 publication.
10. P4 access limitations are recorded as environment/pilot-access blockers, not product dependencies.

## 15. Rollback and recovery

D-027 should be implemented as bounded, reversible QA/admin slices.

- Do not delete existing optional integration adapters merely to prove independence.
- Do not change authoritative domain storage.
- Preserve current WordPress-owned provisioning outputs.
- If new standalone QA exposes a Core defect, reproduce it at the shallowest stable layer and fix only the Theme-owned cause.
- If an optional provider track fails, keep the provider track `BLOCKED/UNKNOWN` unless the failure demonstrates a Theme-owned Core regression.

## 16. Exact next after design approval

After this design is written and source-ratified, create an implementation plan that begins with a fresh source/state audit and an explicit RED test proving the currently missing part of the Standalone Core contract. Do not rewrite already-PASS zero-plugin behavior without evidence that it violates D-027.
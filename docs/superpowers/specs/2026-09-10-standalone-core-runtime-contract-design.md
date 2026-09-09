# D-027 Standalone Core Runtime Contract — Design

**Project:** AZnet Theme  
**Date:** 2026-09-10  
**Design status:** Approved in discussion; authoritative AZT source ratification pending  
**Baseline:** `main@fa1df2dd79ee7740298bb2e5685764ed3279584a`  
**Scope:** AZnet Theme runtime/product independence; development and QA tooling remain allowed outside the shipped Theme

## 1. Context

AZT-01 already defines AZnet Theme as a product that must run independently on a clean WordPress installation, while RootProfile, ConvertFlow, WooCommerce and other providers are optional capabilities. AZT-02 already requires public/versioned contracts, capability detection and fail-soft provider absence.

The remaining gap is enforcement. The repository has clean-WordPress smoke/runtime coverage, but “standalone” is not yet expressed as one explicit release invariant covering first-run setup, Law01 provisioning, starter content/media, admin UX, browser/a11y and final-package verification.

This design turns standalone operation from an architectural principle into a testable product contract.

## 2. Decision

Proposed authoritative decision **D-027 — Standalone Core Runtime Contract**:

> AZnet Theme has zero mandatory third-party runtime dependency. A clean WordPress installation meeting the declared WordPress/PHP floors must be able to install, activate, configure, author, provision, render and operate all AZnet Theme Core capabilities without any plugin or external service. Development and QA tooling is explicitly outside the runtime dependency boundary.

No AZnet Theme release may be declared **Core Ready** unless the exact final package passes a zero-plugin WordPress standalone path covering install -> activate -> setup -> provision -> render -> update/theme-switch verification. Optional integration certification is additive and cannot substitute for Standalone Core PASS.

## 3. Chosen approach

Three approaches were considered:

1. **Documentation-only clarification** — rejected because it is easy to regress without automated enforcement.
2. **Standalone Core Contract with release gates** — **chosen**. Preserve optional adapters while making zero-plugin operation an executable invariant.
3. **Remove all integrations from Theme core** — rejected as unnecessarily extreme. Clean optional integrations remain valuable when they consume public contracts and fail soft.

The chosen approach preserves the existing ownership model:

**SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.**

Standalone does not grant Theme ownership of external domain semantics. It guarantees that external domain owners are not required for Theme Core to function.

## 4. Runtime boundary

### 4.1 Included in runtime/product independence

The shipped Theme ZIP must not require any third-party plugin, remote service or external installer for Core operation.

Core operation includes:

- Theme install and activation;
- Design System and Theme-owned visual presets;
- Header/Footer;
- WordPress-native Logo, Menu and Search presentation;
- Homepage Composer and Theme-owned presentation presets, including `law-01`;
- Quick Setup / Law Site Provisioning;
- starter Pages, Posts, Categories, Menu and Media created through WordPress Core APIs;
- native Page/Post/Archive/Search/404 rendering;
- native author fallback;
- Control Center;
- System Health;
- import/export of Theme-owned presentation settings;
- responsive/accessibility presentation;
- surface-aware Theme assets;
- Theme update and theme-switch continuity for WordPress-owned content.

Core visual/admin rendering must not require externally hosted fonts, icons, stylesheets, scripts, analytics or CDN assets. Required Theme assets must ship in the package or come from WordPress Core. User-authored remote media/embeds and optional provider-owned remote content are outside this rule, provided Theme Core does not require them.

### 4.2 Explicitly outside runtime dependency

The following may be used for development and QA because they are not installed on or required by the production WordPress site:

- Git/GitHub;
- GitHub Actions;
- WP-CLI in CI/test environments;
- Playwright;
- axe;
- PHPUnit or other test runners;
- deterministic packaging scripts;
- local development tooling.

The practical test is simple: if all development/CI infrastructure disappeared, the AZnet Theme ZIP must still install and operate fully on WordPress.

## 5. Core capability vs optional integration capability

### 5.1 Core capability

A Core capability is owned by AZnet Theme/WordPress and must work with zero active plugins.

Core is not a “lite” mode. The standalone product must feel complete for a non-commerce WordPress site and for Theme-owned starter/presentation workflows.

### 5.2 Optional integration capability

An optional integration may enhance presentation only when its owner/provider is present through a public supported boundary.

| Provider | Provider absent | Provider present |
| --- | --- | --- |
| WooCommerce | Theme remains a complete non-commerce WordPress theme | Add commerce presentation using public Woo capabilities/state |
| RootProfile | Use WordPress-native author/site fallbacks; do not invent authoritative identity | Enhance Profile/identity presentation from public RootProfile contracts |
| ConvertFlow | Native Homepage Composer remains complete | Render/add Journey capability through ConvertFlow public boundary |
| Rank Math / Yoast | Theme operates normally with WordPress-native behavior | Coexist; Theme does not write/read private SEO storage |

Optional integration absence must not reduce Standalone Core readiness.

## 6. UX and admin rules

### 6.1 No plugin nags

Theme Core must not:

- show “required plugin” notices for optional providers;
- require an “Install required plugins” step;
- redirect users to a plugin marketplace to complete setup;
- leave Quick Setup incomplete because an optional provider is absent;
- classify optional provider absence as a warning/error in Core readiness;
- present disabled provider-dependent features with an installation solicitation.

The correct state vocabulary is informational, for example:

- `Available`
- `Not present`
- `Unsupported version`
- `Provider error`

Only Theme-owned capability/environment failures may lower Standalone Core readiness. User configuration observations such as “primary menu not assigned yet” or “front page not selected yet” may be reported as setup state, but must not be conflated with a broken or externally dependent Theme Core.

### 6.2 Capability-driven UI

Provider-specific workflow UI should appear only when the exact supported public capability exists.

Example: Commerce configuration may appear when WooCommerce is available. Without WooCommerce, the Theme remains complete and no “missing WooCommerce” remediation UI is needed.

This rule does not prohibit an informational **Optional Integrations** diagnostics section from reporting that a provider is `Not present`; it prohibits provider-dependent workflows from being presented as incomplete Core features.

### 6.3 Commerce visual preset distinction

A Theme-owned **commerce visual style** may remain available as presentation vocabulary. Commerce domain state does not.

The Theme may style a site with a commerce aesthetic without WooCommerce, but cart/account/product/stock/order behavior only exists when WooCommerce owns and exposes the corresponding capability.

## 7. System Health redesign semantics

System Health must visually and semantically separate:

### AZnet Theme Core

Examples:

- WordPress floor supported;
- PHP floor supported;
- active Theme/version;
- Theme Core capability health;
- Homepage/provisioning setup state;
- primary menu and other user-configuration observations.

It should be able to conclude:

> **Standalone Core: READY**

without any optional provider installed.

`Standalone Core: READY` means the Theme-owned capabilities are healthy and independently operable on the supported WordPress/PHP environment. It does not require every user-owned configuration item to be populated. A site may therefore be Core READY while also showing setup recommendations such as assigning a primary menu.

### Optional Integrations

Examples:

- WooCommerce — `Available` / `Not present`;
- RootProfile provider versions — `Available` / `Not present` / contract state;
- ConvertFlow — capability state when a public supported detector exists;
- other certified integrations — informational compatibility state.

Optional integration absence must not be aggregated into Core warning/error status.

## 8. Provisioning must be offline-runtime

Law01 Quick Setup / Provisioning must complete using only:

- files shipped in the Theme ZIP;
- WordPress Core public APIs;
- local WordPress database/filesystem/media operations allowed by Core APIs.

Provisioning must not require setup-time calls to:

- AZnet APIs;
- mandatory CDNs;
- remote template repositories;
- licensing servers for starter content;
- AI services;
- plugin marketplaces;
- remote demo-import services.

Starter Hero/editorial assets are packaged with the Theme and imported into the WordPress Media Library through public WordPress APIs. After provisioning, created Pages/Posts/Categories/Menu/Attachments are ordinary WordPress-owned objects.

This does not change existing ownership rules: provisioning may create WordPress-native starter objects after explicit confirmation, but the Theme does not become the long-term authoritative content owner.

## 9. Standalone Core QA matrix

### L1 — Static dependency gate

Verify that:

- no mandatory third-party runtime dependency is declared;
- optional provider calls are guarded/capability-detected;
- Theme production code does not read private provider storage/APIs;
- admin UX contains no required-plugin nag or mandatory remote importer path;
- Core package contains all starter assets needed for supported provisioning presets;
- Core frontend/admin assets contain no mandatory third-party runtime URL dependency for fonts/icons/styles/scripts/analytics/CDNs.

### L2 — Contract/TDD gate

Provider-absent is a first-class normal state.

At minimum, zero-provider tests cover:

- Theme bootstrap;
- Control Center;
- System Health Core readiness;
- Homepage Composer;
- `law-01` presentation;
- Quick Setup / provisioning plan;
- starter content/media operations;
- Header/Footer and native templates.

Optional adapters retain separate present/absent/malformed/version-mismatch contract tests.

### L3 — Full clean WordPress runtime

Environment:

- WordPress minimum floor;
- PHP minimum floor;
- **zero active plugins**.

Flow:

1. install Theme;
2. activate Theme;
3. confirm activation mutation policy remains valid;
4. open/use Quick Setup;
5. run Law01 provisioning through public WordPress APIs;
6. verify created native Pages/Categories/Menu/Posts/Attachments;
7. rerun to verify idempotency;
8. render Homepage/Post/Page/Archive/Search/404;
9. verify no Theme-caused fatal/parse/uncaught error and enforce the project’s scoped warning policy;
10. verify no external service is required to finish setup;
11. verify Theme Core still renders when outbound third-party requests are unavailable, apart from WordPress/user/provider behavior outside Theme Core ownership.

### L4 — Standalone browser/a11y

On the zero-plugin provisioned site, verify at 1440/1024/390/320:

- first-run/admin setup UX;
- Homepage complete enough for the selected starter preset;
- real Hero media served from WordPress uploads after provisioning;
- required Law01 sections and editorial coverage;
- keyboard/focus/landmark/heading/label quality;
- no horizontal overflow;
- covered axe critical/serious blockers = 0;
- optional providers are not presented as missing requirements;
- Theme-owned frontend/admin rendering does not require third-party asset hosts.

### L5 — Optional compatibility

WooCommerce, RootProfile, ConvertFlow, Rank Math/Yoast and other integration tracks are separate certification matrices.

A provider track may be `BLOCKED` or `UNKNOWN` while Standalone Core remains PASS if:

- provider absence is safe;
- Theme does not violate the provider contract;
- no Theme-owned Core regression is present.

### L6 / P5 — exact final package proof

Before Core Ready/release promotion:

1. build deterministic final candidate;
2. verify package hygiene/exact bytes;
3. install the **actual ZIP contents** into a fresh clean WordPress environment;
4. activate with zero plugins;
5. execute minimum standalone setup/provision/runtime/browser path;
6. verify update/theme-switch continuity;
7. verify required Theme assets are self-contained and no mandatory third-party runtime host is needed;
8. only then claim Standalone Core PASS for those exact package bytes.

Source-tree PASS alone is insufficient for final release readiness.

## 10. Zero-plugin first-run acceptance test

A new user must be able to:

1. start with a fresh supported WordPress installation;
2. upload only the AZnet Theme ZIP;
3. activate it;
4. enter AZnet Theme setup;
5. choose a supported starter/presentation preset such as Law01;
6. review and explicitly confirm the change plan;
7. provision the starter site;
8. receive a complete starter/demo website with native WordPress content/media/menu and Theme presentation;
9. edit the resulting content with WordPress-native editors/workflows;
10. do all of the above without installing another plugin or contacting an external service required by the Theme.

This is the primary product proof for D-027.

## 11. P4 pilot interpretation

P4 Real Pilot QA remains valuable, but pilot access and Theme independence are separate concerns.

If a QA runner cannot reach `tamduchanoi.aznet.vn`, record:

`P4 PILOT ACCESS BLOCKED`

Do not record or imply:

`Theme requires a connector/plugin`.

A WordPress admin connector or third-party management plugin must never become part of the product architecture merely to compensate for the test runner’s network/access limitations.

P4 may still use approved development/QA tooling externally. Installation/update on the pilot remains a site-operations action and must respect existing approval/destructive-action gates.

## 12. Source and implementation impact

After written-spec approval, implementation planning should start with source ratification because D-027 changes architecture/release governance.

Expected authoritative source owners:

- **AZT-01** — strengthen product promise/Definition of Done around zero mandatory runtime dependency;
- **AZT-02** — ratify D-027 runtime boundary, capability vocabulary, self-contained Theme asset rule and offline-runtime provisioning rule;
- **AZT-04** — add D-027 to Decision Log and add Standalone Core release/QA gate to roadmap/P5;
- **AZT-EXEC-MAP** — derive exact implementation slices and next action;
- **SOURCE_MANIFEST** — update semantic versions/state after source merge.

Expected implementation/test areas after source ratification:

- `inc/admin/system-health.php` — split Core readiness from optional integration information and user setup observations;
- Control Center/admin copy — remove any wording that could imply optional provider requirement;
- Theme asset inventory/loading — prove required Core assets are package-local/WordPress-native;
- provisioning contracts/runtime/browser tests — extend clean zero-plugin path to full Law01 first-run;
- reusable core verification — add standalone static/contract gate;
- exact-main/final candidate workflows — execute standalone proof against final package bytes;
- docs/evidence — record exact-head/package evidence without replacing historical records.

This list is directional, not permission to modify code before the implementation plan is approved.

## 13. Compatibility and migration

D-027 should be additive to current architecture.

- Existing optional provider adapters remain valid when they already obey public-contract/fail-soft rules.
- No external provider contract is changed by D-027.
- No provider domain data is copied into the Theme.
- Existing WordPress-owned provisioned content remains WordPress-owned.
- Existing System Health support snapshot keys may require backward-compatible evolution if consumed externally; implementation planning must inspect real consumers before changing shape.
- Theme activation remains mutation-free.
- Provisioning remains explicit/confirmed/idempotent and must preserve current rollback/user-edit protections.

## 14. Failure handling

### Theme Core failure

If zero-plugin install/setup/provision/render fails because of Theme-owned code, Standalone Core is **FAIL** and release promotion is blocked.

### Optional provider failure

If an optional provider is absent, malformed or incompatible:

- adapter fails soft;
- provider-dependent enhancement hides/falls back where valid;
- Core continues;
- compatibility track is recorded separately.

### Test environment access failure

If external pilot DNS/admin access is unavailable:

- record the QA access limitation;
- do not change product architecture to solve it;
- continue all independent clean-WordPress/package verification that does not require the pilot.

## 15. Rollback and reversibility

The design does not require destructive product migration.

Implementation must be delivered in bounded slices with regression coverage. If a new standalone gate exposes a Core defect, fix at the shallowest responsible layer rather than weakening the gate. If an admin semantic change causes regression, revert that slice without touching WordPress-owned content or provider data.

No branch merge, tag, GitHub Release, duplicate-theme deletion or production deployment is authorized by this design approval.

## 16. Acceptance criteria

D-027 implementation is complete only when all are true:

1. authoritative sources ratify the standalone runtime contract;
2. Core/optional capability terminology is consistent in source and admin UX;
3. no required-plugin nag/mandatory remote setup dependency exists for Core;
4. System Health can report `Standalone Core: READY` with zero plugins while separately reporting user setup observations;
5. required Core visual/admin assets are self-contained in the Theme package or WordPress Core;
6. Law01 zero-plugin first-run provisioning PASS is proven at L2/L3/L4;
7. provider-absent regressions remain PASS;
8. exact final candidate ZIP passes the standalone install/activate/setup/provision/render path;
9. update/theme-switch continuity is verified on the candidate path;
10. optional compatibility state remains separate from Core readiness;
11. evidence records exact branch/head/run/package SHA and does not overclaim L5 or P5 publication/deployment.

## 17. Non-goals

D-027 does **not**:

- turn Theme into a business/domain engine;
- duplicate RootProfile identity semantics;
- duplicate ConvertFlow Journey state/resolver/conversion logic;
- duplicate WooCommerce commerce state;
- create a proprietary page builder;
- require Theme to implement SEO-plugin storage or schema engines;
- prohibit external development/QA tooling;
- prohibit user-authored external links/embeds or optional provider-owned remote content;
- authorize publication, production deployment or destructive site cleanup;
- make optional provider certification mandatory for Core release unless a future approved source decision explicitly changes that rule.

## 18. Exact next after spec approval

Invoke the implementation-planning workflow and produce a bounded plan in this order:

1. source ratification for D-027;
2. RED standalone contracts against the current baseline;
3. minimal admin/System Health semantic changes required by the contract;
4. self-contained Core asset verification/fixes if RED exposes any;
5. full zero-plugin Law01 runtime/browser path;
6. exact-package standalone verification;
7. regression matrix and evidence/source closure;
8. stop at the next true owner gate for merge/release/deploy.

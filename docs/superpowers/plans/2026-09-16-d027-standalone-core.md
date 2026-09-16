# D-027 Standalone Core Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn D-027 into executable proof that WordPress + AZnet Theme alone can install, activate, configure, provision, author and render the Core product with zero mandatory third-party runtime dependency.

**Architecture:** Preserve existing WordPress-clean behavior and provider adapters. Add the smallest Theme-owned change needed to separate Core readiness from optional integration availability in System Health, then add deterministic L1-L4 and exact-package verification around the already-existing Homepage Composer/Law01/Provisioning/native editorial stack. Do not clone RootProfile, ConvertFlow, WooCommerce or SEO semantics into the Theme.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, existing PHP offline/runtime harnesses, GitHub Actions, WP-CLI, Playwright/axe where already used by repository workflows.

**Spec:** `docs/superpowers/specs/2026-09-16-d027-standalone-core-design.md`

## Global Constraints

- AZnet Theme Core has zero mandatory third-party runtime dependency.
- WordPress + Theme alone must complete install/activate/setup/provision/author/render on the support floor.
- WooCommerce, RootProfile, ConvertFlow and SEO plugins remain optional additive integrations.
- Provider absence is a normal state and must not downgrade Core readiness.
- No required-plugin nags, setup blockers, marketplace redirects or mandatory external runtime services.
- Law01 provisioning may use only Theme-bundled resources plus WordPress public APIs on the supported path.
- WordPress owns Post/Page/Menu/Media/query/publication state; Theme must not create parallel domain truth.
- No direct provider option/meta/CPT/table/private-class access.
- Preserve already-PASS behavior unless fresh evidence demonstrates a D-027 violation.
- Production feature/bugfix work follows RED -> minimal GREEN -> regression.
- Stop before tag, GitHub Release, final production deployment or provider takeover.

---

### Task 1: Fresh L0 audit and focused RED for the missing Core/Optional readiness separation

**Files:**
- Read: `docs/source/AZT-05-product-constitution.md`
- Read: `docs/source/AZT-02-architecture.md`
- Read: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Read: `inc/admin/system-health.php`
- Read: `inc/admin/control-center.php`
- Create: `tests/offline/d027-standalone-core-health-contract.php`

**Interfaces:**
- Consumes: existing `AZnet\Theme\Admin\system_health_report()` and `support_snapshot()`.
- Produces: a failing contract that requires `standalone_core` and `optional_integrations` to be separate report groups and forbids optional absence from becoming a Core failure.

- [ ] **Step 1: Reconcile branch state before implementation**

Run:

```bash
git status --short
git rev-parse HEAD
git log -5 --oneline
```

Expected: clean worktree on the D-027 implementation branch based on the approved D-027 source/spec commit; no unrelated production delta.

- [ ] **Step 2: Write the focused RED contract**

Create `tests/offline/d027-standalone-core-health-contract.php` with assertions equivalent to:

```php
<?php
$health = file_get_contents( __DIR__ . '/../../inc/admin/system-health.php' );
$control = file_get_contents( __DIR__ . '/../../inc/admin/control-center.php' );

$fail = static function ( string $message ): void {
    fwrite( STDERR, "FAIL: {$message}\n" );
    exit( 1 );
};

if ( false === strpos( $health, "'standalone_core'" ) ) {
    $fail( 'system_health_report must expose standalone_core' );
}
if ( false === strpos( $health, "'optional_integrations'" ) ) {
    $fail( 'system_health_report must expose optional_integrations' );
}
if ( false === strpos( $control, 'Standalone Core' ) ) {
    $fail( 'System Health UI must label Standalone Core separately' );
}
if ( false === strpos( $control, 'Optional Integrations' ) ) {
    $fail( 'System Health UI must label Optional Integrations separately' );
}
if ( preg_match( '/required plugin|missing dependency|install required plugins/i', $control ) ) {
    $fail( 'Core admin must not advertise optional providers as required dependencies' );
}

echo "PASS: D-027 standalone Core health contract\n";
```

- [ ] **Step 3: Run the RED contract**

Run:

```bash
php tests/offline/d027-standalone-core-health-contract.php
```

Expected: FAIL specifically because current `system_health_report()` has `capabilities` rather than distinct `standalone_core` / `optional_integrations` groups and the UI does not label those domains separately.

- [ ] **Step 4: Record the RED checkpoint**

Do not edit production code until the failure reason is confirmed to be the intended D-027 gap rather than syntax/path/harness failure.

- [ ] **Step 5: Commit the RED only**

```bash
git add tests/offline/d027-standalone-core-health-contract.php
git commit -m "test: add D-027 standalone health RED"
```

---

### Task 2: Minimal GREEN — separate Standalone Core readiness from Optional Integrations

**Files:**
- Modify: `inc/admin/system-health.php`
- Modify: `inc/admin/control-center.php`
- Test: `tests/offline/d027-standalone-core-health-contract.php`
- Retain: existing R5 Control Center/System Health contracts and settings portability tests.

**Interfaces:**
- Consumes: `settings()`, `wp_get_theme()`, WordPress environment functions and existing public provider availability helpers.
- Produces: `system_health_report()` with stable groups `environment`, `theme`, `standalone_core`, `optional_integrations`; `support_snapshot()` continues to wrap the report without changing settings ownership.

- [ ] **Step 1: Implement Core readiness as Theme-owned/native facts only**

In `inc/admin/system-health.php`, keep environment/theme data and add a Core group with booleans/status derived only from WordPress/Theme-owned state. Use a shape equivalent to:

```php
'standalone_core' => [
    'status' => 'ready',
    'logo' => has_custom_logo(),
    'primary_menu' => has_nav_menu( 'primary' ),
    'visual_preset' => $s['visual_preset'] ?? 'default',
    'header_preset' => $s['header_preset'] ?? 'standard',
],
'optional_integrations' => [
    'woocommerce' => woo_available() ? 'available' : 'not_present',
    'rootprofile_v1' => provider_available() ? 'available' : 'not_present',
    'rootprofile_v2' => profile_provider_available() ? 'available' : 'not_present',
    'rootprofile_current_surface' => current_surface_available() ? 'available' : 'not_present',
    'convertflow' => 'unknown',
],
```

Do not infer RootProfile identity or ConvertFlow state. Do not make optional availability an input to `standalone_core.status`.

- [ ] **Step 2: Render two explicit UI sections**

In the `system-health` branch of `render_control_center()`, render:

```text
AZnet Theme Core
Standalone Core: READY
...
Optional Integrations
WooCommerce — Available / Not present
RootProfile — Available / Not present
ConvertFlow — Unknown / Available only when a public detector exists
```

Use informational wording for optional absence. Keep the Support Snapshot read-only and retain escaping.

- [ ] **Step 3: Run the new contract**

```bash
php tests/offline/d027-standalone-core-health-contract.php
```

Expected: PASS.

- [ ] **Step 4: Run retained admin/System Health regressions**

Run the existing offline contracts that cover Control Center/System Health/settings portability. If the repository provides a retained R5 aggregate command, use it; otherwise run each matching file under `tests/offline/` and PHP lint the changed production files.

```bash
php -l inc/admin/system-health.php
php -l inc/admin/control-center.php
```

Expected: all retained tests PASS; PHP lint PASS.

- [ ] **Step 5: Commit minimal GREEN**

```bash
git add inc/admin/system-health.php inc/admin/control-center.php tests/offline/d027-standalone-core-health-contract.php
git commit -m "feat: separate standalone core health from optional integrations"
```

---

### Task 3: Lock provisioning independence without rewriting already-PASS provisioning

**Files:**
- Read: `inc/admin/provisioning.php`
- Read: `inc/admin/homepage.php`
- Create: `tests/offline/d027-provisioning-independence-contract.php`
- Retain: `tests/runtime/law-site-provisioning-empty.php`
- Retain: `tests/runtime/law-site-provisioning-scenarios.php`
- Retain: `tests/runtime/law-site-provisioning-v1-1.php`

**Interfaces:**
- Consumes: existing Law Site Provisioning v1.1 plan/execute path and bundled Theme resources.
- Produces: a static contract proving the supported provisioning path does not require remote services, plugin installation or private provider storage.

- [ ] **Step 1: Add a no-external-runtime regression contract**

Create `tests/offline/d027-provisioning-independence-contract.php` that scans the provisioning/homepage/admin path and fails on newly introduced mandatory calls/patterns such as:

```php
$forbidden = [
    'wp_remote_get(',
    'wp_remote_post(',
    'plugins_api(',
    'Plugin_Upgrader',
    'activate_plugin(',
    'install_plugin_install_status(',
    'rootprofile_',
    'choiceguide_',
];
```

Scope the assertions to production files involved in the supported Core setup/provisioning path so optional integration adapters elsewhere do not create false positives.

Also assert that provisioning continues to call WordPress public content/media/menu APIs and references Theme-bundled starter resources rather than remote asset URLs.

- [ ] **Step 2: Run the contract before changing production**

```bash
php tests/offline/d027-provisioning-independence-contract.php
```

Expected: PASS if current D-025/D-026 implementation already satisfies D-027. If it fails, inspect the exact call; only a true mandatory Core dependency unlocks production modification.

- [ ] **Step 3: Run retained provisioning runtime harnesses**

In the existing WordPress runtime test environment, run:

```bash
wp eval-file tests/runtime/law-site-provisioning-empty.php
wp eval-file tests/runtime/law-site-provisioning-scenarios.php
wp eval-file tests/runtime/law-site-provisioning-v1-1.php
```

Expected: PASS with zero third-party plugins active.

- [ ] **Step 4: Commit only the regression guard unless a real defect was proven**

```bash
git add tests/offline/d027-provisioning-independence-contract.php
git commit -m "test: lock standalone provisioning independence"
```

If a real dependency defect is found, fix only the Theme-owned cause in a separate RED -> GREEN commit before this checkpoint.

---

### Task 4: Add a full clean-WordPress L3 Standalone Core runtime path

**Files:**
- Create: `tests/runtime/d027-standalone-core.php`
- Create or extend: `.github/workflows/d027-standalone-core.yml`
- Reuse patterns from: `.github/workflows/law-site-provisioning-candidate.yml`
- Reuse patterns from: `.github/workflows/g-core-lifecycle.yml`
- Reuse patterns from: `.github/workflows/f-homepage-native-runtime-browser.yml`

**Interfaces:**
- Consumes: exact Theme source/package under test, WordPress support floor, PHP support floor, existing provisioning functions.
- Produces: deterministic L3 evidence for install -> activate -> zero plugins -> setup/provision -> representative render -> rerun/idempotency.

- [ ] **Step 1: Write the L3 runtime verifier**

`tests/runtime/d027-standalone-core.php` must assert all of the following from a real WordPress bootstrap:

```text
active theme = aznet-theme
active_plugins = []
Theme setup/admin functions load without fatal
Law01 provisioning plan can be built and explicitly executed
front page exists and uses the expected Theme-owned presentation preset/content map
representative starter Page/Post/Category/Menu/Attachment objects exist as WordPress-owned objects
provisioning rerun does not duplicate bounded managed objects
Home, Post, Page, Category/Archive, Search/no-results and 404 return renderable output
System Health reports Standalone Core independently of optional integrations
```

Do not make RootProfile/WooCommerce/ConvertFlow activation part of this test.

- [ ] **Step 2: Build the workflow from existing repository setup patterns**

The workflow must:

```yaml
name: D-027 Standalone Core
on:
  pull_request:
  workflow_dispatch:
```

Use the repository's existing WordPress/MySQL/WP-CLI bootstrap pattern. Install only WordPress + the Theme. Explicitly deactivate all plugins before assertions:

```bash
wp plugin deactivate --all || true
wp plugin list --status=active --field=name
```

Fail if the second command returns any plugin name.

- [ ] **Step 3: Run offline + L3 path**

Expected workflow sequence:

```bash
php tests/offline/d027-standalone-core-health-contract.php
php tests/offline/d027-provisioning-independence-contract.php
wp eval-file tests/runtime/d027-standalone-core.php
```

Expected: all PASS; zero active plugins recorded in logs.

- [ ] **Step 4: Commit L3 harness**

```bash
git add tests/runtime/d027-standalone-core.php .github/workflows/d027-standalone-core.yml
git commit -m "test: prove D-027 clean WordPress runtime"
```

---

### Task 5: Add L4 browser/a11y proof for setup and representative frontend surfaces

**Files:**
- Create: `tests/browser/d027-standalone-core.spec.js`
- Modify: `.github/workflows/d027-standalone-core.yml`
- Reuse browser patterns from: `.github/workflows/law-site-provisioning-browser.yml`
- Reuse browser patterns from: `.github/workflows/homepage-law-01-browser.yml`

**Interfaces:**
- Consumes: the zero-plugin WordPress instance produced by Task 4.
- Produces: L4 evidence at 1440, 1024, 390 and 320 widths for setup/admin plus Homepage/Post/Page/Archive/Search/404.

- [ ] **Step 1: Add browser assertions for admin independence**

The browser test must visit `AZnet Theme -> System Health` and verify visible text equivalent to:

```text
Standalone Core
READY
Optional Integrations
WooCommerce
Not present
RootProfile
Not present
```

It must also assert absence of copy matching:

```regex
/required plugin|missing dependency|install required plugins/i
```

- [ ] **Step 2: Add frontend responsive/a11y coverage**

For each representative route, test widths 1440, 1024, 390 and 320. Assert:

```text
no horizontal overflow
main landmark present
keyboard focus is visible on interactive controls exercised by the test
heading structure has no obvious Theme-created break in the covered surface
browser console has no Theme-caused uncaught error
axe critical/serious blockers = 0 for Theme-owned covered nodes
```

Reuse the repository's established Playwright/axe setup rather than introducing a new browser stack.

- [ ] **Step 3: Verify imported Hero/starter media is local WordPress media**

Assert rendered Hero/media URLs resolve under the WordPress uploads path after provisioning and are not required from an external CDN/template host.

- [ ] **Step 4: Run L4 workflow**

Expected: setup/admin and representative frontend matrix PASS at all four widths with zero optional plugins active.

- [ ] **Step 5: Commit L4 coverage**

```bash
git add tests/browser/d027-standalone-core.spec.js .github/workflows/d027-standalone-core.yml
git commit -m "test: add D-027 standalone browser and a11y gate"
```

---

### Task 6: Wire exact-package zero-plugin verification for P5 without publishing

**Files:**
- Modify: `.github/workflows/d027-standalone-core.yml`
- Reuse package construction from: `.github/workflows/f-homepage-native-regression-package.yml`
- Reuse lifecycle/update/theme-switch patterns from: `.github/workflows/g-core-lifecycle.yml`
- Create: `docs/evidence/D027_STANDALONE_CORE.md`

**Interfaces:**
- Consumes: exact candidate ZIP bytes built by repository release tooling.
- Produces: pre-P5 package evidence only; does not tag, publish, deploy or merge automatically.

- [ ] **Step 1: Build deterministic candidate ZIP**

Use the repository's existing package recipe. Record SHA-256 and unzip into a fresh WordPress install. Do not reuse the source checkout as the runtime Theme directory for this gate.

- [ ] **Step 2: Repeat the zero-plugin path from package bytes**

From the unpacked ZIP:

```text
install Theme ZIP
activate Theme
confirm active plugins = 0
run Quick Setup/Law01 provisioning
run representative runtime verifier
run representative browser/a11y subset
```

- [ ] **Step 3: Verify update/theme-switch continuity**

Using the existing lifecycle pattern, switch to a WordPress default theme and back to AZnet Theme, then verify WordPress-owned Pages/Posts/Menu/Media remain intact. This check proves Theme switching does not become a data-ownership event.

- [ ] **Step 4: Write bounded evidence**

`docs/evidence/D027_STANDALONE_CORE.md` must record:

```text
branch/head SHA
Theme version
WordPress/PHP floor used
zero active plugins proof
L1/L2 commands and results
L3 workflow run
L4 workflow run
candidate ZIP SHA-256
package install/activate/provision/render result
update/theme-switch continuity result
known BLOCKED/UNKNOWN optional integration tracks, if any
explicit statement: no tag, GitHub Release or production deployment performed
```

- [ ] **Step 5: Commit package-gate/evidence changes**

```bash
git add .github/workflows/d027-standalone-core.yml docs/evidence/D027_STANDALONE_CORE.md
git commit -m "test: wire D-027 exact package standalone gate"
```

---

### Task 7: Full regression, source/evidence reconciliation and review PR

**Files:**
- Modify only if state actually changed: `docs/source/AZT-03-baseline-provenance.md`
- Modify only if roadmap/gate state actually changed: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify only if execution state actually changed: `docs/source/AZT-EXEC-MAP.md`
- Evidence: `docs/evidence/D027_STANDALONE_CORE.md`

**Interfaces:**
- Consumes: Tasks 1-6 PASS evidence.
- Produces: reviewable implementation PR; no automatic main merge or release.

- [ ] **Step 1: Run the full relevant regression set**

Run all D-027 tests plus retained suites for:

```text
R5 Control Center/System Health
Homepage Composer/native homepage
Law01 preset
Law Site Provisioning v1.1
native Page/Post/Archive/Search/404
settings portability
provider absence/fail-soft adapters
PHP lint/static forbidden-access scans
```

Do not claim optional provider L5 certification from zero-plugin Core tests.

- [ ] **Step 2: Verify diff scope**

Run:

```bash
git diff --stat <approved-d027-source-base>...HEAD
git diff --name-only <approved-d027-source-base>...HEAD
```

Expected production delta is bounded to the System Health/Control Center change unless a separate RED proved another Theme-owned defect. No provider source, private API, commerce/Journey/identity domain logic or unrelated refactor may appear.

- [ ] **Step 3: Update authoritative state only where warranted**

If L1-L4 is genuinely PASS, record P4-C implementation evidence in the correct source/evidence owners. Do not rewrite historical evidence. Do not mark P5 publication/deployment PASS.

- [ ] **Step 4: Open an implementation PR**

PR description must include:

```text
D-027 scope
RED failure and minimal GREEN
L1-L4 evidence
zero-plugin runtime proof
exact-package proof or its pre-P5 status
ownership checks
rollback
BLOCKED/UNKNOWN optional tracks
explicit non-authorization of tag/release/deploy
```

- [ ] **Step 5: Stop at owner merge/release gate**

The implementation PR may be reviewed and verified, but merge to canonical `main`, tag/GitHub Release and final deployment remain separate explicit gates.

## Self-review result

- Spec coverage: all D-027 sections map to Tasks 1-7: Core/Optional separation, no nags, provisioning independence, L1-L4 clean WordPress path, package bytes, update/theme-switch, ownership and P4 access semantics.
- Placeholder scan: no TBD/TODO/"implement later" steps remain.
- Boundary check: no task requires coding RootProfile, ConvertFlow, WooCommerce or SEO providers; no private storage fallback is authorized.
- TDD check: the known missing System Health separation begins with a focused RED before production change; already-PASS provisioning behavior is protected by regression rather than rewritten.
- Release check: the plan stops before tag, GitHub Release, final production deployment and automatic main merge.

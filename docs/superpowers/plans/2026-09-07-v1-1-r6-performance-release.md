# AZnet Theme v1.1 R6 Performance and Release 2.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Close v1.1 with measured asset-scope/performance evidence, exact-main CI, deterministic package/release workflow and an owner-gated `1.1.0` release candidate.

**Architecture:** Treat performance and release as reusable product infrastructure rather than one-off milestone scripts. Preserve existing G workflows until replacements prove equivalent/deeper coverage; add v1.x workflows that run on PRs and exact `main` pushes, then retire only superseded workflow files with rollback evidence. Provider-specific asset optimization may proceed only through a public capability contract; otherwise mark it BLOCKED and retain safe behavior.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, GitHub Actions/Ubuntu 24.04/MySQL 8.0, WP-CLI, Playwright/axe, deterministic ZIP tooling, SHA-256, vanilla shell/Python standard library where already accepted.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-1-native-product-system-design.md`

## Global Constraints

- R1-R5 must be merged into canonical `main` before final R6 closure.
- Do not claim performance improvement without measured before/after evidence.
- Non-Woo routes must not load Woo presentation assets; optional provider assets must not become global bundles.
- ConvertFlow projection optimization cannot use private classes/constants/storage or heuristic provider detection.
- Exact-main post-merge verification is mandatory before version promotion.
- Version/tag/release remains an explicit owner approval gate.
- Final version metadata must change atomically in `style.css` and `functions.php` to `1.1.0` only after pre-promotion candidate evidence is green.

---

### Task 1: Capture stable v1.0/v1.1-pre-R6 asset and browser performance baseline

**Files:**
- Create: `tests/browser/r6-performance-baseline.mjs`
- Create: `.github/workflows/r6-performance-baseline.yml`
- Create: `docs/evidence/R6_PERFORMANCE_BASELINE.md`

**Interfaces:**
- Produces comparable metrics per route/viewport: stylesheet/script requests, transferred bytes where observable, failed subresources, CLS observation, console/page errors.

- [ ] **Step 1: Define clean-WP route matrix**

Use:
```text
/
/sample-page/
/sample-post/
/category/sample/
/?s=sample
/404-fixture/
```
With Woo installed add product/archive/cart/checkout/account routes in a separate matrix.

- [ ] **Step 2: Record asset handles/URLs in browser**

The Playwright harness should collect `performance.getEntriesByType('resource')` and classify Theme CSS/JS by `/wp-content/themes/aznet-theme/` URL. Output deterministic JSON per route.

- [ ] **Step 3: Record baseline assertions, not marketing targets**

Evidence must state current observed values and enforce qualitative invariants:
```text
non-Woo clean routes: no Woo component CSS
provider absent: no provider-owned asset loaded by Theme
failed subresources: 0
unexpected console/page errors: 0
horizontal overflow: <=1px
```
Do not invent a universal KB target before measurement.

- [ ] **Step 4: Run and commit baseline evidence**

```bash
git add tests/browser/r6-performance-baseline.mjs .github/workflows/r6-performance-baseline.yml docs/evidence/R6_PERFORMANCE_BASELINE.md
git commit -m "test: capture v1.1 performance baseline"
```

---

### Task 2: Harden Theme-owned surface-aware asset loading

**Files:**
- Modify: `inc/theme/assets.php`
- Create: `tests/offline/r6-asset-scope-contract.php`

**Interfaces:**
- Core tokens/base shell always available.
- Header/Footer assets remain core shell.
- Generic/Woo/preset/Header JS/admin/provider assets load only when their Theme-owned condition is true.

- [ ] **Step 1: Write RED full matrix contract**

Stub conditions and assert exact handle sets for:
```text
clean Page default preset
clean Post editorial preset
Woo product
Woo archive
Woo cart
Woo checkout
Woo account
Header sticky-compact vs sticky/off
```
No Woo stylesheet may appear in clean Page/Post sets.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r6-asset-scope-contract.php
```

- [ ] **Step 3: Refactor `enqueue_assets()` by bounded helpers**

Use helpers such as:
```php
function enqueue_core_assets(string|false $version): void { /* tokens, style, header, footer */ }
function enqueue_visual_preset_assets(string|false $version): void { /* selected preset only */ }
function enqueue_surface_assets(string|false $version): void { /* generic/Woo */ }
function enqueue_interaction_assets(string|false $version): void { /* header JS only when needed */ }
```
Do not create a global bundle or new build framework.

- [ ] **Step 4: Run GREEN + retained W contracts**

```bash
php tests/offline/r6-asset-scope-contract.php
php tests/offline/w1-woocommerce-asset-scope-contract.php
bash scripts/verify-g3-core.sh
```

- [ ] **Step 5: Commit**

```bash
git add inc/theme/assets.php tests/offline/r6-asset-scope-contract.php
git commit -m "perf: harden surface aware theme assets"
```

---

### Task 3: Resolve ConvertFlow projection loading only through a public capability gate

**Files:**
- Read: `assets/css/integrations/convertflow.css`
- Read: `tests/integration/w8-convertflow-coexistence.php`
- Potentially create: `inc/integrations/convertflow.php`
- Potentially modify: `inc/theme/assets.php`
- Create: `tests/offline/r6-convertflow-asset-gate-contract.php`
- Create: `docs/evidence/R6_CONVERTFLOW_ASSET_GATE.md`

**Interfaces:**
- Produces either:
  - `PASS_PUBLIC_GATE`: a source-owner/public/versioned capability safely tells Theme the projection is needed; or
  - `BLOCKED_EXTERNAL_CONTRACT`: no such public capability exists, so current safe bridge behavior is retained and no private workaround is added.

- [ ] **Step 1: Inspect the current ConvertFlow public contract evidence**

Verify whether the provider exposes a documented/versioned Theme-consumable presence/surface capability. A CSS handle, class name, plugin constant or private class is not automatically a public capability merely because it exists.

- [ ] **Step 2: Write the decision contract before changing production**

The test must fail if Theme detection contains any of:
```text
ChoiceGuide\\
CHOICEGUIDE_
get_option(
get_post_meta(
$wpdb
slug/page-ID/URL heuristics
```

- [ ] **Step 3A: If a valid public capability exists, implement minimal adapter**

`inc/integrations/convertflow.php` may expose only:
```php
function theme_projection_needed(): bool
```
backed by the exact public/versioned provider contract. `enqueue_assets()` loads `aznet-theme-convertflow-contract` only when true. Update W8/R6 tests for absent/present behavior and run L5 against the exact provider version.

- [ ] **Step 3B: If no valid public capability exists, stop this optimization as BLOCKED**

Write `docs/evidence/R6_CONVERTFLOW_ASSET_GATE.md`:
```text
Status: BLOCKED_EXTERNAL_CONTRACT
Owner: ConvertFlow public integration contract
Theme action: retain current safe projection; no private detection/workaround
Core v1.1 impact: non-blocking if measured cost is bounded and all Theme-owned asset scope remains PASS
Unblock: provider publishes a stable public/versioned presence/surface capability
```
Do not modify production detection logic.

- [ ] **Step 4: Commit the proven outcome**

```bash
git add tests/offline/r6-convertflow-asset-gate-contract.php docs/evidence/R6_CONVERTFLOW_ASSET_GATE.md inc/integrations/convertflow.php inc/theme/assets.php tests/integration/w8-convertflow-coexistence.php
git commit -m "perf: gate ConvertFlow projection by public capability"
```
Only add files actually changed; a BLOCKED outcome should commit evidence/test only.

---

### Task 4: Add reusable PR and exact-main CI workflows

**Files:**
- Create: `.github/workflows/v1-core-ci.yml`
- Create: `.github/workflows/v1-main-verification.yml`
- Create: `scripts/verify-v1-core.sh`
- Create: `tests/offline/r6-ci-trigger-contract.php`

**Interfaces:**
- `v1-core-ci.yml`: runs on pull requests to `main` for production/test/workflow paths.
- `v1-main-verification.yml`: runs on every production-relevant `push` to `main`.

- [ ] **Step 1: Write RED workflow trigger contract**

Parse YAML/text and assert:
```text
v1-core-ci -> pull_request branches: main
v1-main-verification -> push branches: main
both include production paths *.php/inc/template-parts/assets/theme.json/style.css/patterns
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r6-ci-trigger-contract.php
```

- [ ] **Step 3: Build reusable static/contract script**

`verify-v1-core.sh` must run PHP lint across production PHP files, retained E/W/F/G/R offline contracts applicable without external fixtures, forbidden private-storage/naming scans and package-exclusion checks.

- [ ] **Step 4: Build PR CI**

Run static/contracts plus targeted runtime/browser jobs based on changed paths where safe. Do not weaken full release closure because targeted PR CI is faster.

- [ ] **Step 5: Build exact-main verification**

On `push: main`, run clean WordPress runtime and browser/a11y at minimum, plus static/contracts. Record exact `github.sha` in artifacts/logs.

- [ ] **Step 6: Run GREEN and commit**

```bash
php tests/offline/r6-ci-trigger-contract.php
bash scripts/verify-v1-core.sh

git add .github/workflows/v1-core-ci.yml .github/workflows/v1-main-verification.yml scripts/verify-v1-core.sh tests/offline/r6-ci-trigger-contract.php
git commit -m "ci: verify pull requests and exact main commits"
```

---

### Task 5: Add deterministic reusable v1.x release-candidate workflow

**Files:**
- Create: `.github/workflows/v1-release-candidate.yml`
- Create: `scripts/build-release-package.py`
- Create: `tests/offline/r6-release-workflow-contract.php`

**Interfaces:**
- Manual `workflow_dispatch` release-candidate workflow on canonical main.
- Produces a deterministic ZIP named from the exact parsed `AZNET_THEME_VERSION`, using the form `aznet-theme-${AZNET_THEME_VERSION}.zip`, plus SHA-256 and verification artifact; does not publish tag/release by default.

- [ ] **Step 1: Write RED workflow contract**

Assert the workflow:
```text
uses workflow_dispatch
checks out the requested/current main SHA
requires the ref to resolve to canonical main
parses AZNET_THEME_VERSION and confirms style.css matches it
runs full v1 core verification
builds package twice
compares package bytes
unpacks and exact-compares packaged files
lints packaged PHP
activates extracted package on clean WordPress
uploads ZIP + SHA report
has contents: read only for candidate job
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r6-release-workflow-contract.php
```

- [ ] **Step 3: Implement deterministic package builder**

Use sorted paths, fixed ZIP timestamps/permissions and the existing v1.0 exclusion policy:
```text
.git
.github
docs
scripts
tests
README.md
aznet-preview.png
```
The builder must take source dir/output path/version and fail if `style.css`/`AZNET_THEME_VERSION` disagree.

- [ ] **Step 4: Implement candidate workflow and run GREEN**

```bash
php tests/offline/r6-release-workflow-contract.php
python3 scripts/build-release-package.py --help
```
Run the GitHub workflow before merge and retain artifact IDs/SHA.

- [ ] **Step 5: Commit**

```bash
git add .github/workflows/v1-release-candidate.yml scripts/build-release-package.py tests/offline/r6-release-workflow-contract.php
git commit -m "ci: add deterministic v1 release candidate workflow"
```

---

### Task 6: Retire superseded milestone-specific G workflows only after replacement evidence

**Files:**
- Potentially delete/retire: `.github/workflows/g-core-static.yml`
- Potentially delete/retire: `.github/workflows/g-core-runtime.yml`
- Potentially delete/retire: `.github/workflows/g-core-browser.yml`
- Potentially delete/retire: `.github/workflows/g-core-release-candidate.yml`
- Potentially keep lifecycle/version workflows until replacements cover them
- Create: `docs/evidence/R6_WORKFLOW_RETIREMENT.md`

**Interfaces:**
- Consumes successful replacement runs.
- Produces a bounded retirement list with rollback commit.

- [ ] **Step 1: Compare coverage line by line**

Create a table mapping each old G workflow responsibility to its new v1 workflow replacement. Any unmapped responsibility means the old workflow stays.

- [ ] **Step 2: Require fresh replacement PASS before deletion**

Record exact run/job IDs and head SHA. A workflow name alone is not evidence.

- [ ] **Step 3: Delete only fully superseded files**

Do not retire `g-core-lifecycle.yml` or `g8-release-version.yml` until the new release/main workflows explicitly cover lifecycle/version gates.

- [ ] **Step 4: Commit retirement evidence + safe deletions**

```bash
git add .github/workflows docs/evidence/R6_WORKFLOW_RETIREMENT.md
git commit -m "chore: retire superseded v1 milestone workflows"
```

---

### Task 7: Final exact-main R1-R6 regression before version promotion

**Files:**
- Create: `docs/evidence/R6_V1_1_PREPROMOTION.md`

**Interfaces:**
- Consumes canonical main after all production PRs merge.
- Produces immutable pre-promotion evidence on exact main SHA.

- [ ] **Step 1: Run exact-main CI**

Require green results for:
```text
static/contracts
clean WordPress runtime
core browser/a11y
R1 design system
R2 patterns
R3 header
R4 Woo matrix
R5 Control Center
lifecycle/update/theme-switch/rollback
performance asset matrix
release-candidate deterministic package
```
Provider-specific L5 may remain BLOCKED only as recorded by source/evidence and must not be silently called PASS.

- [ ] **Step 2: Record package identity**

Evidence records exact main SHA, package filename, inner ZIP SHA-256, file count, artifact ID/container digest and rollback commit/package.

- [ ] **Step 3: Verify publication/version remains pre-promotion**

At this stage Theme still carries the current canonical main version metadata. The pre-promotion artifact is evidence-only and must not be published as a new release. Do not set `1.1.0` until this task is green.

- [ ] **Step 4: Commit evidence on the bounded release branch**

```bash
git add docs/evidence/R6_V1_1_PREPROMOTION.md
git commit -m "docs: record v1.1 pre-promotion evidence"
```

**Exit:** technical candidate is green before metadata promotion.

---

### Task 8: Promote metadata atomically to 1.1.0 with RED -> GREEN

**Files:**
- Modify: `style.css`
- Modify: `functions.php`
- Create: `tests/offline/r6-v1-1-version-contract.php`

**Interfaces:**
- Produces exact version `1.1.0` in both metadata locations.

- [ ] **Step 1: Write RED exact-version contract**

Assert:
```php
$expected = '1.1.0';
// style.css Version === expected
// AZNET_THEME_VERSION === expected
// no alpha/beta/rc prerelease marker remains
// Requires at least 6.9 / Requires PHP 8.1 unchanged
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r6-v1-1-version-contract.php
```
Expected: FAIL because current version is not `1.1.0`.

- [ ] **Step 3: Change exactly the two production metadata values**

Set `Version: 1.1.0` in `style.css` and `AZNET_THEME_VERSION` to `1.1.0` in `functions.php` in one bounded commit.

- [ ] **Step 4: Run GREEN + full release candidate workflow**

```bash
php tests/offline/r6-v1-1-version-contract.php
bash scripts/verify-v1-core.sh
```
Then run `v1-release-candidate.yml` on the exact promotion commit and require deterministic package/runtime PASS.

- [ ] **Step 5: Commit**

```bash
git add style.css functions.php tests/offline/r6-v1-1-version-contract.php
git commit -m "release: promote AZnet Theme to 1.1.0"
```

---

### Task 9: Final release gate — tag/GitHub Release only after explicit owner approval

**Files:**
- No code changes unless release notes/source state need an approved docs-only update.

**Interfaces:**
- Consumes exact green `1.1.0` main commit/package.
- Produces optional `v1.1.0` tag and GitHub Release; deployment elsewhere is a separate gate.

- [ ] **Step 1: Revalidate canonical main has not moved**

Confirm the release commit SHA and its exact-main workflow/package results. If main moved, rebuild/reverify; do not tag stale bytes.

- [ ] **Step 2: Present owner gate**

Request explicit approval for:
```text
create tag v1.1.0 -> exact verified main SHA
publish GitHub Release v1.1.0 using exact verified package/SHA
```
Do not infer approval from earlier feature merges.

- [ ] **Step 3: After approval, publish through the supported GitHub capability/workflow**

If the current environment lacks tag/release mutation capability, report `BLOCKED_PUBLICATION_CAPABILITY` with exact target SHA and artifact instead of creating a fake branch/tag substitute.

- [ ] **Step 4: Post-publication source reconciliation**

After the tag/release exists, update only the source files whose current-state facts changed (normally AZT-03/AZT-04/manifest/derived map), through a docs-only PR. Do not rewrite historical evidence.

**Exit:** R6 L0-L6 closure; v1.1 release publication is exact-byte/provenance-safe and separately approved.

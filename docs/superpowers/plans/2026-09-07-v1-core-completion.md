# AZnet Theme v1.0 Core Completion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Finish the independent WordPress core of AZnet Theme and produce a fully verified v1.0 release candidate without allowing optional provider defects to redefine core readiness.

**Architecture:** Keep the accepted hybrid PHP + `theme.json` architecture and existing ownership boundaries. Close v1.0 through the source-defined G0-G8 sequence, reusing existing verified code and tests, adding only bounded release verification infrastructure and fixes for concrete Theme-owned defects found by those gates.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, theme.json, PHP templates, CSS, WP-CLI, GitHub Actions on Ubuntu 24.04/MySQL 8.0, Playwright 1.55, axe-core 4.10, Python 3 standard library for deterministic ZIP construction.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-core-completion-design.md`

## Global Constraints

- AZT-05 v1.0 is the highest internal product/release constitution.
- Canonical source lives under `docs/source/` on GitHub `main`.
- AZnet Theme is an independent WordPress theme; optional providers are not core release dependencies.
- WordPress minimum is 6.9+; PHP minimum is 8.1+.
- Theme architecture remains hybrid PHP + `theme.json`.
- Naming family remains `AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.
- Theme owns presentation only; WordPress and external products keep authoritative domain state.
- No private provider API/storage reads, slug/title/Page-ID/URL authoritative heuristics, or parallel domain truth.
- Existing PASS milestones stay closed unless a concrete invalidation is reproduced.
- Production defects/behavior changes require RED -> minimal GREEN -> regression.
- Control Center U0-U6 is outside the v1.0 critical path.
- E5-C RootProfile and F8 ConvertFlow integrated compatibility remain optional compatibility tracks, not core blockers under D-016.
- Release tag/deploy and other explicit takeover gates remain owner approval gates.

---

### Task 1: G0 — Freeze core scope and create the finite blocker inventory

**Files:**
- Create: `docs/evidence/G0_CORE_V1_SCOPE_INVENTORY_20260907.md`
- Read: `docs/source/AZT-05-product-constitution.md`
- Read: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Read: `docs/source/AZT-EXEC-MAP.md`
- Read: `style.css`, `functions.php`, `.github/workflows/*`, `tests/*`, `scripts/*`

**Interfaces:**
- Consumes: canonical source and `main@b7443a96c8687493a0a4994aeca23b5e09a8770c`.
- Produces: a closed classification used by Tasks 2-9: `CORE_BLOCKER`, `ALREADY_PASS`, `OPTIONAL_COMPAT`, `DEFERRED`.

- [ ] **Step 1: Revalidate the branch ancestry and production-byte baseline**

Run:
```bash
git merge-base --is-ancestor b7443a96c8687493a0a4994aeca23b5e09a8770c HEAD
git diff --name-only 815ffcd43653930d1f302a055961ac24cf5ae843..b7443a96c8687493a0a4994aeca23b5e09a8770c
```
Expected: the source merge is documentation/governance only; no PHP/CSS/template/theme.json production byte invalidation is introduced by PR #33.

- [ ] **Step 2: Record the retained PASS set**

The evidence file must explicitly retain, absent new invalidation: Milestone 0, Foundation, Tokens, Header/Footer, Generic Templates, Woo presentation W, and Theme-native Homepage core.

- [ ] **Step 3: Record the non-core tracks**

The evidence file must classify RootProfile E5-C/E5-D and ConvertFlow F8 as `OPTIONAL_COMPAT`, and Control Center U as `DEFERRED`.

- [ ] **Step 4: Record the finite core blockers**

The initial blocker set must contain only work still required by AZT-05/AZT-04:
```text
G1 cleanup/canonical-destination review
G3 fresh static/security/package hygiene
G4 WordPress-clean runtime
G5 browser/responsive/a11y/performance
G6 update/theme-switch/rollback
G7 full regression + deterministic package
G8 v1.0 metadata/evidence closure
```
Also record that `style.css` and `functions.php` still declare `0.1.0-alpha.7`; version promotion is deliberately deferred to G8.

- [ ] **Step 5: Verify the inventory contains no new feature scope**

Run:
```bash
grep -E 'Control Center|page builder|RootProfile source|ConvertFlow source' docs/evidence/G0_CORE_V1_SCOPE_INVENTORY_20260907.md
```
Expected: any matches appear only under `OPTIONAL_COMPAT` or `DEFERRED`, never as core blockers.

- [ ] **Step 6: Commit G0**

```bash
git add docs/evidence/G0_CORE_V1_SCOPE_INVENTORY_20260907.md
git commit -m "docs: freeze core v1 blocker inventory"
```

**Exit:** L0 PASS; v1.0 has a finite Theme-owned critical path.  
**Next:** G1.

---

### Task 2: G1 — Verify cleanup candidates and canonical destinations

**Files:**
- Create: `docs/evidence/G1_CLEANUP_CANONICAL_DESTINATIONS_20260907.md`
- Read: `docs/evidence/L0_SOURCE_GIT_RECONCILIATION_20260907.md`
- Read: current `header.php`, `footer.php`, `index.php`, `page.php`, `single.php`, `archive.php`, `search.php`, `404.php`, `front-page.php`
- Read: `template-parts/**`, `assets/css/components/**`, `inc/theme/**`

**Interfaces:**
- Consumes: G0 classification and prior C/D cleanup evidence.
- Produces: exact safe-retirement list for G2. The expected result is an empty production-deletion list unless current use-site evidence proves otherwise.

- [ ] **Step 1: Inventory current presentation destinations**

Record the canonical destinations for Header/Footer, Generic Templates, Homepage, Profile/Contact presentation, and Woo presentation.

- [ ] **Step 2: Check current repository use-sites before declaring anything dead**

Run targeted searches such as:
```bash
git grep -n "get_template_part" -- '*.php'
git grep -n "site-header\|site-footer\|generic-content\|front-page" -- '*.php' '*.css'
```
Do not classify `index.php` as duplicate; it is the WordPress hierarchy fallback.

- [ ] **Step 3: Reconcile prior retirement findings**

Record that prior Header/Footer and Generic-template retirement reviews found no Theme-local duplicate that was both superseded and safe to delete. External provenance snapshots do not authorize deletion inside this repository.

- [ ] **Step 4: Classify package-only repository material separately**

`aznet-preview.png` is a repository preview artifact, not production Theme behavior. `docs/`, `tests/`, `scripts/`, `.github/` are repository-only. Do not delete them from Git; package exclusion is handled in G3/G7.

- [ ] **Step 5: Commit G1 evidence**

```bash
git add docs/evidence/G1_CLEANUP_CANONICAL_DESTINATIONS_20260907.md
git commit -m "docs: verify v1 cleanup destinations"
```

**Exit:** every potential retirement has destination/use-site/rollback classification.  
**Next:** G2.

---

### Task 3: G2 — Close bounded retirement without speculative deletion

**Files:**
- Create: `docs/evidence/G2_BOUNDED_RETIREMENT_20260907.md`
- Modify production files only if G1 proves a concrete safe candidate; otherwise production tree remains unchanged.

**Interfaces:**
- Consumes: G1 exact retirement list.
- Produces: either a no-op retirement PASS or one independently protected retirement slice.

- [ ] **Step 1: Fail closed on unknown candidates**

If G1 has no candidate satisfying all four conditions — Theme-owned presentation, canonical destination exists, current use-site proves superseded, rollback is known — write `NO_SAFE_PRODUCTION_RETIREMENT` and do not delete production files.

- [ ] **Step 2: If a concrete candidate unexpectedly qualifies, create RED before deletion**

The RED must assert the destination/fallback behavior that the deletion could break. Example shape:
```php
if (!is_file($root . '/index.php')) {
    fail_test('WordPress hierarchy fallback must remain present');
}
```
Run it and prove the intended failure before any behavior-affecting removal.

- [ ] **Step 3: Apply only the minimal retirement, if any**

No mixed/domain/adapter path is deleted in this task. Do not touch RootProfile/ConvertFlow/Woo domain logic.

- [ ] **Step 4: Rerun the protecting regression**

Expected: GREEN plus retained related tests.

- [ ] **Step 5: Commit G2**

```bash
git add docs/evidence/G2_BOUNDED_RETIREMENT_20260907.md tests/offline
git commit -m "chore: close bounded v1 retirement gate"
```

**Exit:** no unprotected Theme-owned retirement remains.  
**Next:** G3.

---

### Task 4: G3 — Add one core static/security/package hygiene gate

**Files:**
- Create: `tests/offline/g3-core-release-static-contract.php`
- Create: `scripts/verify-g3-core.sh`
- Create: `.github/workflows/g-core-static.yml`
- Create: `docs/evidence/G3_CORE_STATIC_HYGIENE.md`
- Read: all production PHP, `style.css`, `theme.json`, `inc/theme/assets.php`

**Interfaces:**
- Consumes: retained E/W/F offline contracts.
- Produces: one repeatable L1-L2 gate callable by later G7 packaging.

- [ ] **Step 1: Write the G3 contract before relying on it**

The contract must assert at minimum:
```php
assert(str_contains($style, 'Requires at least: 6.9'));
assert(str_contains($style, 'Requires PHP: 8.1'));
assert(str_contains($style, 'Text Domain: aznet-theme'));
assert($styleVersion === $constantVersion);
```
It must also scan production PHP for forbidden cross-product storage/private patterns such as `_rootprofile_`, `_choiceguide_`, direct `$wpdb` use for provider data, and private provider namespaces. Do not ban legitimate WordPress native APIs generically.

- [ ] **Step 2: Run the new contract**

```bash
php tests/offline/g3-core-release-static-contract.php
```
Expected: PASS on current behavior, or a precise failure identifying a Theme-owned defect. If it fails, keep the failure as RED and fix only that defect before continuing.

- [ ] **Step 3: Build `scripts/verify-g3-core.sh`**

It must run:
```bash
php tests/offline/g3-core-release-static-contract.php
bash scripts/verify-e5b.sh
bash scripts/verify-w1.sh
bash scripts/verify-w2.sh
bash scripts/verify-w3.sh
bash scripts/verify-w4.sh
bash scripts/verify-w5.sh
bash scripts/verify-w6.sh
php tests/offline/w8-convertflow-theme-contract.php
php tests/offline/f1-front-page-native-shell-contract.php
php tests/offline/f2-front-page-content-boundary-contract.php
php tests/offline/f3-homepage-ownership-static-contract.php
php tests/offline/f5-homepage-absent-failsoft-contract.php
```
Then lint every production PHP file while excluding `.github`, `docs`, `scripts`, and `tests`.

- [ ] **Step 4: Add explicit package-root hygiene assertions**

The static workflow must prove release packaging will exclude at least:
```text
.git/
.github/
docs/
scripts/
tests/
README.md
aznet-preview.png
```
This does not delete repository documentation or preview assets; it prevents them from becoming release payload by accident.

- [ ] **Step 5: Run the G3 workflow and record its run ID**

Expected: all static/contracts/lint PASS on PHP 8.1.

- [ ] **Step 6: Commit G3**

```bash
git add tests/offline/g3-core-release-static-contract.php scripts/verify-g3-core.sh .github/workflows/g-core-static.yml docs/evidence/G3_CORE_STATIC_HYGIENE.md
git commit -m "test: add core v1 static release gate"
```

**Exit:** fresh L1-L2 PASS.  
**Next:** G4.

---

### Task 5: G4 — Prove WordPress-clean runtime across all core routes

**Files:**
- Create: `.github/workflows/g-core-runtime.yml`
- Create: `docs/evidence/G4_WORDPRESS_CLEAN_RUNTIME.md`
- Reuse setup pattern from: `.github/workflows/f-homepage-native-runtime-browser.yml`

**Interfaces:**
- Consumes: exact branch bytes and G3 PASS.
- Produces: WordPress 6.9 / PHP 8.1 L3 evidence with optional ecosystem plugins absent.

- [ ] **Step 1: Provision WordPress 6.9 / PHP 8.1 / MySQL 8.0**

Use WP-CLI exactly as the retained Homepage workflow does. Copy the Theme while excluding repository-only directories/files and activate `aznet-theme` with no ecosystem plugins active.

- [ ] **Step 2: Create native fixtures**

Create a static front page, one Page, one Post in a category, searchable sentinel content, and pretty permalinks. Do not create Product/Profile/Journey domain fixtures.

- [ ] **Step 3: Start the PHP runtime and request all core surfaces**

Request at minimum:
```text
/
/native-page/
/sample-post/
/category/core-test/
/?s=core-sentinel
/definitely-missing/
```
Assert HTTP success/404 semantics as appropriate, Theme Header/Main/Footer markers, expected content sentinel, and no Theme-caused `Fatal error|Warning|Parse error|Uncaught` log markers.

- [ ] **Step 4: Run package-independent runtime from repository bytes**

This gate is runtime evidence, not package evidence. Do not infer G7 from it.

- [ ] **Step 5: Commit G4 evidence/workflow**

```bash
git add .github/workflows/g-core-runtime.yml docs/evidence/G4_WORDPRESS_CLEAN_RUNTIME.md
git commit -m "test: verify WordPress-clean core runtime"
```

**Exit:** L3 PASS on clean WordPress.  
**Next:** G5.

---

### Task 6: G5 — Prove browser, responsive, a11y and asset-scope quality

**Files:**
- Create: `tests/browser/g-core-l4.mjs`
- Create: `.github/workflows/g-core-browser.yml`
- Create: `docs/evidence/G5_CORE_BROWSER_A11Y_PERFORMANCE.md`

**Interfaces:**
- Consumes: the same native fixture model as G4.
- Produces: L4 evidence for desktop/tablet/mobile core surfaces.

- [ ] **Step 1: Write browser assertions before declaring L4**

For each viewport `1440x1000`, `1024x900`, `390x844`, assert:
```text
exactly one main landmark
no duplicate DOM ids
horizontal overflow <= 1px
skip link becomes visible on keyboard focus
primary navigation remains keyboard usable
axe critical/serious violations = 0
console errors = 0
page errors = 0
```
Run against Home, Page, Post, Archive, Search results, Search empty/no-result if fixture allows, and 404.

- [ ] **Step 2: Assert core asset scope**

On generic/core routes, Woo component styles must not load when WooCommerce is absent. Theme base/tokens/header/footer/generic assets may load according to their accepted contracts. Do not reinterpret the already-accepted ConvertFlow token bridge as a new architecture change in this release closure.

- [ ] **Step 3: Run Playwright + axe**

```bash
npm install --no-save --no-package-lock playwright@1.55.0 @axe-core/playwright@4.10.2
npx playwright install --with-deps chromium
node tests/browser/g-core-l4.mjs
```
Expected: all core route/viewports PASS.

- [ ] **Step 4: If browser failure occurs, reduce it before fixing**

Create the smallest stable RED reproduction at L1/L2 where possible, then minimal GREEN and rerun G4/G5. Do not patch CSS blindly in the browser layer.

- [ ] **Step 5: Commit G5**

```bash
git add tests/browser/g-core-l4.mjs .github/workflows/g-core-browser.yml docs/evidence/G5_CORE_BROWSER_A11Y_PERFORMANCE.md
git commit -m "test: verify core browser accessibility"
```

**Exit:** L4 PASS.  
**Next:** G6.

---

### Task 7: G6 — Prove update, theme-switch and rollback lifecycle safety

**Files:**
- Create: `.github/workflows/g-core-lifecycle.yml`
- Create: `docs/evidence/G6_UPDATE_THEME_SWITCH_ROLLBACK.md`
- Read prior rollback references under: `docs/evidence/**`

**Interfaces:**
- Consumes: current candidate branch plus the registered prior alpha.7 rollback reference.
- Produces: lifecycle safety evidence; no source/domain data deletion.

- [ ] **Step 1: Install prior verified alpha package/reference**

Provision WordPress 6.9 and activate the prior Theme package/reference under the stable `aznet-theme/` directory.

- [ ] **Step 2: Create continuity sentinels**

Create native Page/Post/Menu/custom-logo/theme-mod sentinels using public WordPress APIs. Do not create fake external domain truth.

- [ ] **Step 3: Replace in place with current candidate bytes**

Assert all native content/configuration sentinels remain, the Theme activates, and core routes still render.

- [ ] **Step 4: Theme-switch away and back**

Activate a stock WordPress theme, verify native content remains, then reactivate AZnet Theme. If optional provider test fixtures are available through public APIs, assert the Theme switch does not delete them; otherwise do not fabricate provider state.

- [ ] **Step 5: Roll back to the prior reference**

Reinstall/restore the registered alpha.7 rollback bytes and prove activation/runtime succeeds without losing native content.

- [ ] **Step 6: Commit G6**

```bash
git add .github/workflows/g-core-lifecycle.yml docs/evidence/G6_UPDATE_THEME_SWITCH_ROLLBACK.md
git commit -m "test: verify v1 lifecycle rollback safety"
```

**Exit:** lifecycle/rollback PASS.  
**Next:** G7.

---

### Task 8: G7 — Run full regression and build deterministic candidate bytes

**Files:**
- Create: `.github/workflows/g-core-package.yml`
- Create: `docs/evidence/G7_FULL_REGRESSION_PACKAGE.md`
- Reuse: `scripts/verify-g3-core.sh`
- Reuse deterministic ZIP logic from: `.github/workflows/f-homepage-native-regression-package.yml`

**Interfaces:**
- Consumes: G3-G6 PASS and exact candidate branch bytes.
- Produces: deterministic pre-v1 package evidence without yet promoting release metadata.

- [ ] **Step 1: Run all retained core regressions**

Run `bash scripts/verify-g3-core.sh` plus the G4/G5/G6 workflow gates on the exact PR candidate/merge candidate.

- [ ] **Step 2: Build the package twice with an explicit release manifest**

Use deterministic ZipInfo timestamp `(2020, 1, 1, 0, 0, 0)`, compression level 9, stable sorted paths, and root `aznet-theme/`.

Exclude exactly repository-only material, including:
```python
EXCLUDED_DIRS = {'.git', '.github', 'docs', 'scripts', 'tests'}
EXCLUDED_ROOT_FILES = {'README.md', 'aznet-preview.png'}
```
Do not exclude Theme production assets or integration files that are part of the accepted canonical Theme tree.

- [ ] **Step 3: Prove byte-identical rebuild**

```python
assert package_a == package_b, 'candidate package rebuild is not byte-identical'
```
Record SHA-256 and file count.

- [ ] **Step 4: Unzip and exact-byte reverify**

Assert package root is exactly `aznet-theme/`, excluded repository material is absent, every included source file is byte-identical, and all packaged PHP files pass `php -l`.

- [ ] **Step 5: Fresh activation smoke from extracted package bytes**

Install the ZIP into a fresh WordPress 6.9 environment and run activation plus core route smoke. Package smoke is separate from repository-byte G4.

- [ ] **Step 6: Commit G7**

```bash
git add .github/workflows/g-core-package.yml docs/evidence/G7_FULL_REGRESSION_PACKAGE.md
git commit -m "test: build deterministic v1 candidate package"
```

**Exit:** technical L6 candidate PASS, still carrying pre-promotion version metadata.  
**Next:** G8.

---

### Task 9: G8 — Promote v1.0 metadata and close the release candidate

**Files:**
- Modify: `style.css`
- Modify: `functions.php`
- Modify: `README.md`
- Create: `tests/offline/g8-release-metadata-contract.php`
- Create: `docs/evidence/G8_V1_RELEASE_CANDIDATE.md`
- Modify only source files whose owned facts truly change after the final candidate is proven.

**Interfaces:**
- Consumes: G0-G7 PASS and deterministic pre-promotion candidate evidence.
- Produces: final `1.0.0` candidate bytes and rollback reference; stops before release tag/deploy.

- [ ] **Step 1: Write release metadata RED**

Before changing version strings, create `tests/offline/g8-release-metadata-contract.php` that requires:
```text
style.css Version: 1.0.0
functions.php AZNET_THEME_VERSION = 1.0.0
README current version = 1.0.0
all three match exactly
```
Run it and confirm RED specifically because current metadata is `0.1.0-alpha.7`.

- [ ] **Step 2: Apply minimal GREEN metadata promotion**

Change only the three user/release metadata locations to `1.0.0`. Do not redesign or refactor production behavior in the version-promotion commit.

- [ ] **Step 3: Run G8 contract and complete regressions**

```bash
php tests/offline/g8-release-metadata-contract.php
bash scripts/verify-g3-core.sh
```
Then rerun G4, G5, G6 and G7 against the exact final candidate bytes because release/package evidence is byte-specific.

- [ ] **Step 4: Record final package identity**

`docs/evidence/G8_V1_RELEASE_CANDIDATE.md` must contain final commit/PR candidate SHA, package SHA-256, package file count, WordPress/PHP floor, all fresh workflow run IDs, rollback package/commit reference, and known non-core compatibility blockers E/F.

- [ ] **Step 5: Update authoritative source only where its owned fact changed**

Do not bump source documents merely to log progress. Update AZT source only if the final release candidate changes a source-owned current baseline/release fact that the canonical source model requires. Implementation run IDs/hashes stay in GitHub evidence.

- [ ] **Step 6: Commit release candidate metadata**

```bash
git add style.css functions.php README.md tests/offline/g8-release-metadata-contract.php docs/evidence/G8_V1_RELEASE_CANDIDATE.md
git commit -m "release: prepare AZnet Theme 1.0.0 candidate"
```

- [ ] **Step 7: Open the final release-candidate PR and stop at owner gate**

The PR must state: core v1.0 PASS layers, final package SHA, rollback reference, optional compatibility blockers not claimed, and no production deploy performed.

**Exit:** AZnet Theme v1.0 release candidate complete.  
**Hard Gate / Next:** explicit owner approval for final merge/tag/release/deploy.

---

## Plan self-review

- Spec coverage: G0-G8 and all AZT-05 release-critical dimensions are mapped to Tasks 1-9.
- Scope: no Control Center, RootProfile source, ConvertFlow source or page-builder work enters the critical path.
- Evidence separation: L0/L1/L2/L3/L4/L6 are independently proven; optional provider certification remains separate.
- Release metadata is delayed until all behavior/lifecycle/package gates are green.
- Package policy explicitly prevents repository-only preview/docs/test/tooling files from becoming release payload.
- Any newly discovered Theme-owned defect must first become a stable RED reproduction before production code is changed.

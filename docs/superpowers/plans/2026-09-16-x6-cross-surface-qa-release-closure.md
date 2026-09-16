# X6 Cross-surface QA & Release Closure Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Close AZnet Theme v1.2 technically by proving the integrated X1-X5 WordPress-Core experience across static, runtime, browser, deterministic-package and lifecycle gates; then, only after a separate product-owner approval, promote Theme metadata atomically from `1.1.0` to `1.2.0` and verify the exact promoted bytes on canonical `main` without publishing or deploying them.

**Architecture:** X6 is a verification/release-closure slice, not a new feature subsystem. Reuse the existing X1-X5 surface modules, `scripts/verify-v1-core.sh`, the deterministic package builder, exact-main workflow and WordPress-native lifecycle semantics. Add one bounded cross-surface fixture/browser/package workflow plus a reusable version-consistency primitive. Keep provider L5 completely outside this milestone.

**Tech Stack:** WordPress 6.9, PHP 8.1, MySQL 8, WP-CLI, Bash, PHP offline contracts, Python 3 deterministic ZIP builder, Node 22, Playwright 1.55.0, axe-core 4.10.2, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md` — especially X6, acceptance discipline, versioning/release behavior, rollback and hard gates; `docs/source/AZT-04-roadmap-qa-decisions.md` v0.48; `docs/source/AZT-EXEC-MAP.md` v0.40; `docs/source/SOURCE_MANIFEST.md` current X5 closure.

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Re-resolve live canonical `main` before implementation. Plan base is `main@e1d3cf60a930c9a054da7d03e3c2da7b75e7029e`, the owner-approved X5 merge commit whose `V1 Exact Main Verification` run `35090183247` passed both static and clean-runtime/browser jobs.
- Theme remains PRESENTATION OWNER. WordPress owns comments, search/query/routing, content/media, pagination/navigation, form submission/validation semantics and native data lifecycle.
- Support floor remains WordPress `6.9+` and PHP `8.1+`; architecture remains hybrid PHP + `theme.json`.
- No new RootProfile, ConvertFlow, WooCommerce or other provider integration/certification work. Provider L5 is outside X6.
- No private provider storage/API reads, authoritative route heuristics, copied domain logic or parallel domain state.
- No new production presentation feature is planned in X6. If X6 exposes a Theme-owned product defect, reproduce it at the shallowest stable layer and handle it as a separate RED → GREEN bugfix before resuming release closure.
- Theme metadata stays exact `1.1.0` through the pre-promotion technical gate. Changing `style.css` or `AZNET_THEME_VERSION` before explicit product-owner approval is forbidden.
- The exact-`1.2.0` RED contract may be written and committed before that approval because it changes no production metadata. The true hard gate is immediately before editing `style.css` and `functions.php`.
- Promotion, once approved, is atomic: `style.css` `Version:` and `functions.php` `AZNET_THEME_VERSION` both become exact `1.2.0` in the same bounded production commit.
- Git tag, GitHub Release/publication and production deployment remain separate owner gates after technical X6 closure. This plan authorizes none of them.
- Final technical PASS requires fresh evidence on final bytes. Historical X1-X5 PASS supports provenance but cannot substitute for X6 final-candidate or exact-main evidence.

---

## File Structure

### Create

- `scripts/assert-theme-version.sh` — reusable version declaration consistency assertion with optional exact expected version.
- `tests/offline/x6-release-closure-contract.php` — X6 ownership/infrastructure/package/lifecycle static contract.
- `tests/offline/x6-version-promotion-contract.php` — focused exact-`1.2.0` RED/GREEN promotion contract.
- `tests/runtime/x6-cross-surface.php` — one WordPress-native fixture map covering representative X1-X5 surfaces and continuity sentinels.
- `tests/browser/x6-cross-surface-l4.mjs` — integrated responsive/keyboard/focus/overflow/axe matrix.
- `.github/workflows/x6-cross-surface-release.yml` — bounded PR + main X6 L1-L4/L6 workflow.
- `docs/evidence/X6_CROSS_SURFACE_RELEASE_CLOSURE_20260916.md` — durable technical evidence after exact final-head and exact-main verification.

### Modify before promotion

- `scripts/verify-v1-core.sh`
- `tests/offline/x2-search-404-empty-contract.php`
- `tests/offline/x3-media-gallery-embed-contract.php`
- `tests/offline/x4-pagination-navigation-contract.php`
- `tests/offline/x5-native-form-controls-contract.php`
- `.github/workflows/x1-comments-surface.yml`
- `.github/workflows/x2-search-404-empty.yml`
- `.github/workflows/x3-media-gallery-embed.yml`
- `.github/workflows/x4-pagination-navigation.yml`
- `.github/workflows/x5-native-form-controls.yml`
- `.github/workflows/g8-release-version.yml`

The X2-X5 contracts and X1-X5 workflows currently contain implementation-phase exact-`1.1.0` guards. X6 must generalize those live guards to **version consistency** before promotion while preserving every functional/ownership assertion. The live `g8-release-version.yml` also triggers on `style.css`/`functions.php` and currently invokes a v1.1-specific historical contract; update only the workflow to the generic consistency helper so it remains a useful retained metadata gate during and after X6.

### Explicitly leave historical/special-purpose files unchanged

- `tests/offline/g8-release-version-contract.php` — historical proof of the approved `1.0.0 -> 1.1.0` promotion; do not rewrite its history.
- `tests/offline/p1-pilot-identity-inspection-contract.php` — historical/manual pilot identity evidence for v1.1; not part of reusable X6 core verification.
- `tests/offline/homepage-blueprint-asset-contract.php` — its `1.1.0` literal is an explicit argument-passthrough fixture for `enqueue_homepage_blueprint_asset($version)`, not a current Theme metadata assertion.

### Modify only after explicit version-promotion approval

- `style.css`
- `functions.php`

### Modify only after final technical evidence exists

- `docs/source/AZT-03-baseline-provenance.md`
- `docs/source/AZT-04-roadmap-qa-decisions.md`
- `docs/source/AZT-EXEC-MAP.md`
- `docs/source/SOURCE_MANIFEST.md`

---

## Task 1: Generalize live version guards without changing production metadata

**Files:**
- Create: `scripts/assert-theme-version.sh`
- Modify: `tests/offline/x2-search-404-empty-contract.php`
- Modify: `tests/offline/x3-media-gallery-embed-contract.php`
- Modify: `tests/offline/x4-pagination-navigation-contract.php`
- Modify: `tests/offline/x5-native-form-controls-contract.php`
- Modify: `.github/workflows/x1-comments-surface.yml`
- Modify: `.github/workflows/x2-search-404-empty.yml`
- Modify: `.github/workflows/x3-media-gallery-embed.yml`
- Modify: `.github/workflows/x4-pagination-navigation.yml`
- Modify: `.github/workflows/x5-native-form-controls.yml`
- Modify: `.github/workflows/g8-release-version.yml`

**Interfaces:**
- Consumes: current `style.css` `Version:` and `functions.php` `AZNET_THEME_VERSION` declarations.
- Produces: one reusable script that proves both declarations are present, semver-like and equal, with optional exact-version enforcement.

- [ ] **Step 1: inventory and classify fixed-version live guards**

Run:

```bash
grep -RFn "Version: 1.1.0" .github/workflows tests/offline scripts || true
grep -RFn "AZNET_THEME_VERSION', '1.1.0'" .github/workflows tests/offline scripts || true
```

Expected classification:

- generalize X2/X3/X4/X5 offline current-version assertions;
- generalize X1-X5 workflow current-version assertions;
- update `g8-release-version.yml` to call the generic helper;
- leave `g8-release-version-contract.php`, `p1-pilot-identity-inspection-contract.php` and `homepage-blueprint-asset-contract.php` unchanged for the reasons documented above;
- do not rewrite historical docs/evidence merely because they contain `1.1.0`.

- [ ] **Step 2: create the reusable version assertion**

Create `scripts/assert-theme-version.sh` exactly around these semantics:

```bash
#!/usr/bin/env bash
set -euo pipefail

STYLE_VERSION=$(sed -n 's/^Version:[[:space:]]*//p' style.css | head -n 1)
PHP_VERSION=$(sed -n "s/.*define( 'AZNET_THEME_VERSION', '\([^']*\)' );.*/\1/p" functions.php | head -n 1)

test -n "$STYLE_VERSION"
test -n "$PHP_VERSION"
test "$STYLE_VERSION" = "$PHP_VERSION"
[[ "$STYLE_VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]

if [[ ${1:-} != '' ]]; then
  test "$STYLE_VERSION" = "$1"
fi

printf 'PASS: Theme metadata version %s is consistent\n' "$STYLE_VERSION"
```

Make it executable.

- [ ] **Step 3: generalize X2-X5 offline contracts only at the version assertion**

Replace each `must remain 1.1.0 during Xn` check with parsing/equality logic. Preserve all existing surface, ownership, asset-scope and forbidden-marker checks.

Use each file's existing fail helper, with this semantic shape:

```php
preg_match('/^Version:\s*([^\r\n]+)/m', $style, $styleMatch);
preg_match("/define\( 'AZNET_THEME_VERSION', '([^']+)' \);/", $functions, $phpMatch);
$styleVersion = trim($styleMatch[1] ?? '');
$phpVersion = $phpMatch[1] ?? '';
if ($styleVersion === '' || $phpVersion === '' || $styleVersion !== $phpVersion) {
    xN_fail('Theme version declarations must remain present and equal.');
}
```

- [ ] **Step 4: generalize X1-X5 workflow metadata checks**

Replace their exact `grep ... 1.1.0` commands with:

```bash
bash scripts/assert-theme-version.sh
```

Where a workflow validates the installed runtime copy under `/tmp/wp`, parse the installed `style.css` and `functions.php` and require equality to the source `VERSION` value captured by `scripts/assert-theme-version.sh`; do not hard-code `1.1.0`.

- [ ] **Step 5: convert the live G8 workflow from v1.1-specific execution to generic consistency execution**

Keep the workflow file/path and PR triggers, but change its job to run:

```bash
bash scripts/assert-theme-version.sh
```

Rename the displayed workflow/job text to `Release Version Consistency Gate` / `version-consistency` if useful. Do **not** modify `tests/offline/g8-release-version-contract.php`; Git history and that file retain the old `1.0.0 -> 1.1.0` proof.

- [ ] **Step 6: prove production metadata is still exactly 1.1.0**

Run:

```bash
bash scripts/assert-theme-version.sh 1.1.0
git diff -- style.css functions.php
```

Expected: PASS and zero production metadata diff.

- [ ] **Step 7: run the reusable verifier and commit the compatibility refactor**

Run:

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS; X1-X5 behavior unchanged. Commit only helper/tests/workflows, not production metadata.

---

## Task 2: Add the X6 release-closure contract and prove RED for missing X6 infrastructure

**Files:**
- Create: `tests/offline/x6-release-closure-contract.php`
- Modify later in Task 5: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: retained X1-X5 contracts, `scripts/assert-theme-version.sh`, package builder and current release infrastructure.
- Produces: deterministic L2 guard for X6 verification ownership/infrastructure.

- [ ] **Step 1: write the failing X6 contract before runtime/browser/workflow files exist**

Require these files:

```php
$required = [
    'scripts/assert-theme-version.sh',
    'tests/runtime/x6-cross-surface.php',
    'tests/browser/x6-cross-surface-l4.mjs',
    '.github/workflows/x6-cross-surface-release.yml',
    'scripts/build-release-package.py',
    '.github/workflows/v1-release-candidate.yml',
];
```

Require `scripts/verify-v1-core.sh` to retain X1-X5 focused contracts. Require the future X6 workflow to contain WordPress `6.9`, PHP `8.1`, Playwright `1.55.0`, axe `4.10.2`, deterministic double-build/`cmp`, package/source exact comparison, zero-plugin runtime and switch-away/switch-back continuity.

Reject from new X6 runtime/browser/workflow implementation:

```php
$forbidden = [
    'new WP_Query',
    'query_posts(',
    'pre_get_posts',
    '$wpdb',
    'get_post_meta(',
    'choiceguide_',
    'rootprofile_',
    'ConvertFlow',
    'wp_redirect(',
    'wp_safe_redirect(',
    'XMLHttpRequest',
    'fetch(',
];
```

The fixture therefore uses native Post/title/content IDs and Theme Mod continuity sentinels instead of post-meta sentinels.

- [ ] **Step 2: run RED**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x6-release-closure-contract.php
```

Expected: FAIL because `tests/runtime/x6-cross-surface.php`, `tests/browser/x6-cross-surface-l4.mjs` and `.github/workflows/x6-cross-surface-release.yml` are absent. Record exact output and commit test-only RED.

---

## Task 3: Build one native WordPress X6 cross-surface fixture

**Files:**
- Create: `tests/runtime/x6-cross-surface.php`

**Interfaces:**
- Consumes: WordPress public/native APIs only.
- Produces: deterministic JSON URLs/IDs for integrated X1-X5 representative surfaces and lifecycle sentinels.

- [ ] **Step 1: create native fixtures**

Create with native WordPress APIs:

- one published Post with comments open and one approved native comment;
- enough published Posts in one Category to force archive pagination at a low test-only `posts_per_page`;
- one multipage Post using `<!--nextpage-->`;
- one authored-media Post containing representative image/figure, gallery, video/audio and safe embed markup following existing X3 fixture patterns;
- one ordinary Page containing Core Search and Categories controls;
- one password-protected Page;
- one published search target containing unique text `X6 Cross Surface Target`;
- one guaranteed empty-search URL;
- one Home/control URL and one guaranteed missing URL;
- one WordPress-owned sentinel Post with unique title/content;
- one bounded `aznet_theme_settings` Theme Mod sentinel using an allowed existing Theme setting value, to verify Theme-owned settings continuity across theme switching.

- [ ] **Step 2: return exact JSON keys**

Return:

```json
{
  "comment_url": "...",
  "archive_page_1_url": "...",
  "archive_page_2_url": "...",
  "search_result_url": "...",
  "search_empty_url": "...",
  "multipage_url": "...",
  "media_url": "...",
  "controls_url": "...",
  "password_url": "...",
  "missing_url": "...",
  "home_url": "...",
  "sentinel_post_id": 1
}
```

- [ ] **Step 3: keep fixture ownership clean**

Use `wp_insert_post`, `wp_insert_comment`, native taxonomy/content APIs and ordinary test configuration. No provider records, no production data store, no custom query engine and no routing heuristic.

---

## Task 4: Add integrated X1-X5 browser verification

**Files:**
- Create: `tests/browser/x6-cross-surface-l4.mjs`

**Interfaces:**
- Consumes: Task 3 fixture JSON and a running WordPress 6.9 site.
- Produces: screenshots, axe JSON and one 32-case summary.

- [ ] **Step 1: define the 8-route x 4-viewport matrix**

Viewports:

```js
[
  { name: 'desktop', width: 1440, height: 1000 },
  { name: 'tablet', width: 1024, height: 900 },
  { name: 'mobile', width: 390, height: 844 },
  { name: 'narrow', width: 320, height: 760 },
]
```

Routes:

1. Comments Post — X1 comments + native form.
2. Search result — X2 listing + X4 navigation + X5 forms.
3. Search empty — X2 recovery + X5 forms.
4. 404 — native HTTP 404 + X2 recovery + X5 forms.
5. Paginated Archive page 2 — listing + X4 pagination.
6. Multipage Post — content + X4 post-page navigation.
7. Media Post — X3 figures/gallery/audio/video/embed containment.
8. Password/Core-control Page — X5 shared controls.

Total expected: **32** cases.

- [ ] **Step 2: assert shared layout/a11y invariants**

For each case:

```js
if (await page.locator('h1').count() !== 1) {
  throw new Error('expected exactly one H1');
}
const overflow = await page.evaluate(() =>
  document.documentElement.scrollWidth - document.documentElement.clientWidth
);
if (overflow > 1) {
  throw new Error(`horizontal overflow ${overflow}px`);
}
```

Collect `pageerror` and console `error`. Run axe and fail on any `critical` or `serious` violation.

- [ ] **Step 3: assert focused X1-X5 asset/presentation behavior**

Require only where expected:

- Comments: `aznet-theme-comments-css` + `aznet-theme-forms-css`.
- Empty Search/404: `aznet-theme-recovery-css` + forms.
- Media: `aznet-theme-media-css`.
- Archive/Search/Multipage: `aznet-theme-navigation-css` where the corresponding X4 surface requires it.
- Password/Core controls: forms.

The runtime gate, not the 32-case browser count, separately checks Home as a negative control for accidental X6 surface-asset leakage.

- [ ] **Step 4: verify keyboard focus**

On Comments, Search, recovery and password/Core-control routes, tab to meaningful controls and require computed outline or box-shadow to be visible/non-zero using the same method already proven by X1-X5 browser harnesses.

- [ ] **Step 5: persist evidence**

Write one screenshot and one axe JSON per route/viewport plus `/tmp/x6-l4/summary.json` with `total_cases: 32` and `blocking_failures: 0`.

---

## Task 5: Add the bounded X6 PR/main L1-L4 + L6 workflow and make the X6 contract GREEN

**Files:**
- Create: `.github/workflows/x6-cross-surface-release.yml`
- Modify: `scripts/verify-v1-core.sh`
- Complete GREEN: `tests/offline/x6-release-closure-contract.php`

**Interfaces:**
- Consumes: Tasks 1-4 plus `scripts/build-release-package.py`.
- Produces: exact SHA/tree, contracts, integrated browser evidence, deterministic package/source identity and lifecycle evidence.

- [ ] **Step 1: use bounded triggers**

Configure:

```yaml
name: X6 Cross-surface Release Closure

on:
  pull_request:
    branches: [main]
    paths:
      - 'style.css'
      - 'functions.php'
      - 'comments.php'
      - 'search.php'
      - 'archive.php'
      - '404.php'
      - 'single.php'
      - 'inc/theme/**'
      - 'assets/css/components/comments.css'
      - 'assets/css/components/recovery.css'
      - 'assets/css/components/media.css'
      - 'assets/css/components/navigation.css'
      - 'assets/css/components/forms.css'
      - 'scripts/assert-theme-version.sh'
      - 'scripts/verify-v1-core.sh'
      - 'scripts/build-release-package.py'
      - 'tests/offline/x*-*.php'
      - 'tests/runtime/x6-cross-surface.php'
      - 'tests/browser/x6-cross-surface-l4.mjs'
      - '.github/workflows/x*-*.yml'
      - '.github/workflows/g8-release-version.yml'
      - '.github/workflows/x6-cross-surface-release.yml'
  push:
    branches: [main]
    paths:
      - 'style.css'
      - 'functions.php'
      - 'comments.php'
      - 'search.php'
      - 'archive.php'
      - '404.php'
      - 'single.php'
      - 'inc/theme/**'
      - 'assets/css/components/**'
      - 'scripts/assert-theme-version.sh'
      - 'scripts/verify-v1-core.sh'
      - 'scripts/build-release-package.py'
      - 'tests/offline/x*-*.php'
      - 'tests/runtime/x6-cross-surface.php'
      - 'tests/browser/x6-cross-surface-l4.mjs'
      - '.github/workflows/x6-cross-surface-release.yml'
  workflow_dispatch:

permissions:
  contents: read
```

Do not trigger the heavy X6 package/browser job for later docs-only source-closure commits.

- [ ] **Step 2: exact checkout and L1-L2**

Checkout `${{ github.event.pull_request.head.sha || github.sha }}` with `fetch-depth: 0`, record SHA/tree, set up PHP 8.1, then run:

```bash
bash scripts/assert-theme-version.sh
bash scripts/verify-v1-core.sh
```

Add to `scripts/verify-v1-core.sh` immediately after X5:

```bash
printf '%s\n' '==> X6 Cross-surface QA & Release Closure contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x6-release-closure-contract.php
```

- [ ] **Step 3: install WordPress 6.9 / MySQL 8 with zero active plugins**

Use the established X5 WP-CLI setup. Assert:

```bash
wp core version --path=/tmp/wp --allow-root | grep -Fx '6.9'
test -z "$(wp plugin list --status=active --field=name --path=/tmp/wp --allow-root)"
```

Seed Task 3 fixture, start runtime, assert expected HTTP status and surface asset handles, and assert Home negative-control boundaries.

- [ ] **Step 4: run integrated L4**

Install:

```bash
npm install --no-save playwright@1.55.0 @axe-core/playwright@4.10.2
npx playwright install --with-deps chromium
node tests/browser/x6-cross-surface-l4.mjs
```

Expected: 32/32 PASS.

- [ ] **Step 5: build current-version package twice and compare bytes**

Parse matching version declarations into `VERSION`, then:

```bash
mkdir -p /tmp/x6/build-a /tmp/x6/build-b /tmp/x6/out
python3 scripts/build-release-package.py --source . --output "/tmp/x6/build-a/aznet-theme-${VERSION}.zip" --version "$VERSION"
python3 scripts/build-release-package.py --source . --output "/tmp/x6/build-b/aznet-theme-${VERSION}.zip" --version "$VERSION"
cmp "/tmp/x6/build-a/aznet-theme-${VERSION}.zip" "/tmp/x6/build-b/aznet-theme-${VERSION}.zip"
cp "/tmp/x6/build-a/aznet-theme-${VERSION}.zip" /tmp/x6/out/
sha256sum "/tmp/x6/out/aznet-theme-${VERSION}.zip" | tee /tmp/x6/out/SHA256SUMS.txt
```

- [ ] **Step 6: exact package/source identity and PHP lint**

Unzip; require exactly one top-level `aznet-theme/`; build expected production tree with existing exclusions `.git`, `.github`, `docs`, `scripts`, `tests`, `README.md`, `aznet-preview.png`; run `diff -ru`; lint every packaged PHP file and record count.

- [ ] **Step 7: exact-package lifecycle**

Install the extracted package into fresh WordPress 6.9. Re-seed the native sentinels and run package-backed runtime/browser smoke. Ensure `twentytwentyfive` exists:

```bash
wp theme is-installed twentytwentyfive --path=/tmp/wp --allow-root || \
  wp theme install twentytwentyfive --path=/tmp/wp --allow-root
```

Record sentinel Post ID/title/content and the bounded Theme Mod. Activate `twentytwentyfive`, verify WordPress-owned Post data remains; reactivate `aznet-theme`, verify Theme Mod and Post data remain, then rerun package smoke.

- [ ] **Step 8: run focused GREEN then full verifier**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x6-release-closure-contract.php
bash scripts/verify-v1-core.sh
```

Expected: PASS. Commit bounded X6 verification infrastructure.

---

## Task 6: Prove pre-promotion technical readiness while metadata is still 1.1.0

**Files:** no production metadata changes.

**Interfaces:**
- Consumes: Task 5 workflow.
- Produces: pre-promotion exact-head L1-L4/L6 evidence and exact-`1.2.0` RED evidence.

- [ ] **Step 1: require exact 1.1.0 before pre-promotion package verification**

Run locally/CI:

```bash
bash scripts/assert-theme-version.sh 1.1.0
```

- [ ] **Step 2: require X6 workflow PASS on the exact pre-promotion head**

Require integrated 32/32 browser matrix, deterministic current-version package, exact package/source compare, packaged PHP lint and lifecycle continuity while metadata is still `1.1.0`.

- [ ] **Step 3: require all triggered retained workflows GREEN on that same head**

Historical X1-X5 functional-head PASS is supporting evidence only. The active pre-promotion head must be clean.

- [ ] **Step 4: write the exact-1.2.0 promotion contract before asking for promotion approval**

Create `tests/offline/x6-version-promotion-contract.php`:

```php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');
if ($style === false || $functions === false) {
    throw new RuntimeException('Cannot read Theme version declarations.');
}

preg_match('/^Version:\s*([^\r\n]+)/m', $style, $styleMatch);
preg_match("/define\( 'AZNET_THEME_VERSION', '([^']+)' \);/", $functions, $phpMatch);

if (trim($styleMatch[1] ?? '') !== '1.2.0') {
    throw new RuntimeException('style.css Theme Version must be exactly 1.2.0 in X6 promotion.');
}
if (($phpMatch[1] ?? '') !== '1.2.0') {
    throw new RuntimeException('AZNET_THEME_VERSION must be exactly 1.2.0 in X6 promotion.');
}

echo "PASS: X6 Theme metadata promoted atomically to 1.2.0\n";
```

- [ ] **Step 5: run and commit RED**

Run:

```bash
php tests/offline/x6-version-promotion-contract.php
```

Expected: FAIL because both production declarations are still `1.1.0`. Record exact failure and commit only the new test.

- [ ] **Step 6: stop at the true version-promotion hard gate**

Present exact pre-promotion SHA, X6 run/artifact/package digest, retained workflow results and RED promotion-test output. Request explicit product-owner approval to edit production metadata to `1.2.0`.

**Hard stop:** X6 plan approval, X5 approval or the RED test itself does not authorize changing `style.css`/`functions.php`.

---

## Task 7: Apply minimal atomic 1.2.0 GREEN after explicit product-owner approval

**Files:**
- Modify: `style.css`
- Modify: `functions.php`
- Modify after GREEN: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: Task 6 RED + explicit owner approval.
- Produces: exact atomic `1.2.0` metadata and retained promotion contract.

- [ ] **Step 1: change only the two production version declarations**

```diff
-Version: 1.1.0
+Version: 1.2.0
```

and:

```diff
-    define( 'AZNET_THEME_VERSION', '1.1.0' );
+    define( 'AZNET_THEME_VERSION', '1.2.0' );
```

No other production PHP/CSS/JS change belongs in the promotion commit.

- [ ] **Step 2: run focused GREEN**

```bash
bash scripts/assert-theme-version.sh 1.2.0
php tests/offline/x6-version-promotion-contract.php
git diff --check
```

Expected: PASS.

- [ ] **Step 3: retain promotion contract in the reusable verifier**

Add after X6 release-closure contract:

```bash
printf '%s\n' '==> X6 exact 1.2.0 metadata promotion contract'
php tests/offline/x6-version-promotion-contract.php
```

- [ ] **Step 4: verify production diff boundary**

Run:

```bash
git diff <pre-promotion-red-sha>...HEAD -- style.css functions.php
```

Expected production version diff: only the two exact lines above.

- [ ] **Step 5: commit bounded promotion**

Use a message such as:

```bash
git commit -m "release: promote Theme metadata to 1.2.0"
```

---

## Task 8: Verify the exact promoted final head at L1-L4 and L6

**Files:** no new production behavior changes.

**Interfaces:**
- Consumes: exact promoted head from Task 7.
- Produces: frozen technical-candidate evidence.

- [ ] **Step 1: require reusable core and all triggered retained workflows GREEN**

Require:

```bash
bash scripts/assert-theme-version.sh 1.2.0
bash scripts/verify-v1-core.sh
```

and all exact-head triggered workflows SUCCESS.

- [ ] **Step 2: require X6 integrated browser PASS**

Require 32/32 route/viewport cases, zero critical/serious axe violations, no horizontal overflow, visible keyboard focus and no Theme-caused runtime error markers.

- [ ] **Step 3: require deterministic exact 1.2.0 package**

Expected package: `aznet-theme-1.2.0.zip`. Require double-build byte identity, one top-level `aznet-theme/`, exact source/package compare, packaged PHP lint and SHA-256 recording.

- [ ] **Step 4: require package lifecycle PASS**

Switch exact package away to `twentytwentyfive`, verify WordPress-owned sentinel continuity, switch back to `aznet-theme`, verify Theme Mod + WordPress data continuity, rerun package-backed smoke.

- [ ] **Step 5: freeze candidate bytes**

Any later production/test/workflow byte that changes the verified behavior or package inputs invalidates final-head evidence and requires fresh exact-final-head verification before merge.

---

## Task 9: Record pre-merge X6 evidence and authoritative pre-merge state

**Files:**
- Create: `docs/evidence/X6_CROSS_SURFACE_RELEASE_CLOSURE_20260916.md`
- Modify: `docs/source/AZT-03-baseline-provenance.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: pre-promotion PASS, promotion RED/GREEN, promoted exact-final-head workflows/package/lifecycle evidence.
- Produces: authoritative pre-merge state `X6 FINAL CANDIDATE PASS / CANONICAL MERGE + EXACT-MAIN VERIFICATION PENDING`.

- [ ] **Step 1: write durable evidence**

Record:

- pre-promotion exact SHA/run/artifact/current-version package digest;
- promotion RED commit and intended failure;
- explicit owner approval reference/date for `1.2.0` production metadata change;
- promoted exact-final-head SHA/tree;
- all final-head workflow results;
- X6 32/32 browser summary;
- `aznet-theme-1.2.0.zip` SHA-256 + artifact digest;
- exact source/package compare + packaged PHP lint count;
- switch-away/switch-back continuity;
- rollback path;
- explicit non-claims: no provider L5, tag, GitHub Release or deployment.

- [ ] **Step 2: reconcile source ownership**

- AZT-03 owns new baseline/provenance/version facts.
- AZT-04 owns roadmap/release-gate state.
- AZT-EXEC-MAP derives execution checkpoint.
- SOURCE_MANIFEST aligns authoritative versions and exact next.
- Historical v1.1 evidence remains historical; do not rewrite it as current v1.2 evidence.

- [ ] **Step 3: preserve canonical merge gate**

Source must not claim canonical X6 PASS yet. State that canonical integration requires owner-approved merge of the exact verified head plus fresh exact-main verification.

- [ ] **Step 4: rerun fresh exact-final-head workflows after source/evidence closure**

Because docs/source/evidence have changed, observe a new exact final head and require all applicable triggered workflows GREEN. Do not transfer PASS from the earlier promoted head to new bytes.

---

## Task 10: Owner-approved X6 merge and exact-main technical closure

**Files:** canonical Git history only; no new production behavior.

**Interfaces:**
- Consumes: frozen exact final head + separate owner merge approval.
- Produces: canonical `main` technical v1.2 evidence.

- [ ] **Step 1: stop at the X6 production merge hard gate**

Before merge require PR open, non-draft, mergeable, all final-head gates GREEN and exact head unchanged. Request/confirm separate owner approval for canonical merge if not already explicitly granted for that exact X6 PR.

- [ ] **Step 2: merge with expected-head protection**

Use normal merge and exact expected head SHA. Do not squash away TDD/provenance history unless source governance explicitly changes.

- [ ] **Step 3: require fresh V1 Exact Main Verification**

On the exact merge SHA, both `static-contracts` and `clean-runtime-browser` must complete SUCCESS.

- [ ] **Step 4: require X6 push-to-main workflow**

On the same exact merge SHA require:

- exact metadata `1.2.0`;
- integrated 32/32 browser matrix;
- deterministic `aznet-theme-1.2.0.zip`;
- exact package/source identity;
- package PHP lint;
- package lifecycle/switch-away/switch-back PASS.

Record exact-main package SHA/artifact.

- [ ] **Step 5: do not publish or deploy**

Technical integration does not create a tag, GitHub Release or production deployment. Those remain later owner gates.

---

## Task 11: Post-merge authoritative technical-closure checkpoint

**Files:**
- Update: `docs/evidence/X6_CROSS_SURFACE_RELEASE_CLOSURE_20260916.md`
- Modify if exact-main evidence adds provenance: `docs/source/AZT-03-baseline-provenance.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: exact-main merge SHA/tree, exact-main workflow IDs/artifacts/package SHA.
- Produces: authoritative `X6 TECHNICAL PASS / PUBLICATION GATED` and exactly one Next.

- [ ] **Step 1: append exact-main evidence**

Record exact merge SHA/tree, `V1 Exact Main Verification` run, X6 exact-main run, exact package SHA/artifact and lifecycle PASS.

- [ ] **Step 2: set precise source state**

Use `X6 TECHNICAL PASS / PUBLICATION GATED` or the exact equivalent current source terminology. Explicitly distinguish technical v1.2 closure from publication and deployment.

- [ ] **Step 3: set exactly one Next**

```text
NEXT — request owner approval for v1.2.0 publication disposition (annotated tag + GitHub Release only); production deployment remains a later separate approval.
```

- [ ] **Step 4: open a bounded docs/evidence-only closure PR and stop at its merge approval gate**

Final diff must contain no production PHP/CSS/JS and no tag/release/deployment action. If approved and merged, any exact-main verification required by current governance must be observed before claiming the source closure canonical.

---

## Rollback / Recovery

- Before production version promotion: revert X6 test/workflow/helper infrastructure; Theme production metadata remains `1.1.0`.
- After promotion but before merge: revert the bounded promotion commit; both declarations return together to `1.1.0`; WordPress content/state remains untouched.
- After X6 merge but before publication: revert the X6 merge if necessary; this plan has created no tag/Release/deployment.
- Theme switching must preserve WordPress-owned content and external provider/domain data.
- A failed deeper gate returns to the shallowest stable reproduction before any further production edit.

## Self-review

- **Spec coverage:** X6 includes L1 static/dependency/ownership checks, retained X1-X5 L2, real WordPress 6.9 L3, integrated responsive/keyboard/focus/overflow/axe L4, deterministic package/source identity + lifecycle/rollback + exact-main L6, and atomic `1.2.0` promotion.
- **Hard-gate placement:** the exact-`1.2.0` test is written and proven RED before asking for promotion approval; the hard stop is before production metadata edit. X6 canonical merge, publication and deployment each remain separate gates.
- **Version guard audit:** X2-X5 offline contracts, X1-X5 workflows and live G8 workflow are generalized; historical `g8-release-version-contract.php`, P1 pilot identity evidence and homepage argument-passthrough fixture remain unchanged.
- **G8 compatibility:** the live G8 workflow no longer blocks a legitimate X6 promotion, but historical `1.0.0 -> 1.1.0` proof remains intact in Git history and its contract file.
- **Ownership:** no provider integration, private storage read, domain data store, custom query/search/comment/gallery/form engine or routing heuristic is introduced.
- **Final-byte discipline:** pre-promotion, promoted final-head, source-closure final-head and canonical exact-main evidence are distinct; no PASS is silently transferred between them.
- **Release discipline:** no tag, GitHub Release or production deployment occurs during technical X6 closure.
- **Placeholder scan:** no `TBD`, `TODO`, `implement later`, unspecified test or hidden production step remains.

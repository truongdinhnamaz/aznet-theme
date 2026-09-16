# X6 Cross-surface QA & Release Closure Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Close AZnet Theme v1.2 technically by proving the integrated X1-X5 WordPress-Core experience across static/runtime/browser/package/lifecycle gates, then—only after a separate owner approval—promote Theme metadata atomically from `1.1.0` to `1.2.0` and verify the exact promoted bytes on canonical `main` without publishing or deploying them.

**Architecture:** X6 is a verification/release-closure slice, not a new feature subsystem. Reuse the existing X1-X5 surface modules, `scripts/verify-v1-core.sh`, deterministic package builder, exact-main workflow and WordPress-native lifecycle semantics; add one bounded X6 cross-surface fixture/browser/package workflow and version-consistency primitives so historical X1-X5 tests no longer hard-code the pre-X6 `1.1.0` implementation-phase version. Keep provider L5 completely outside this milestone.

**Tech Stack:** WordPress 6.9, PHP 8.1, MySQL 8, WP-CLI, Bash, PHP offline contracts, Python 3 deterministic ZIP builder, Node 22, Playwright 1.55.0, axe-core 4.10.2, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md` — especially §§5 X6, 7, 8, 10 and 11; `docs/source/AZT-04-roadmap-qa-decisions.md` v0.48; `docs/source/AZT-EXEC-MAP.md` v0.40; `docs/source/SOURCE_MANIFEST.md` current X5 closure.

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Implementation base: re-resolve live canonical `main` before execution; approved plan base is `main@e1d3cf60a930c9a054da7d03e3c2da7b75e7029e` after PR #85.
- Theme remains PRESENTATION OWNER. WordPress owns comments, search/query/routing, content/media, pagination/navigation, form submission/validation semantics and native data lifecycle.
- WordPress floor remains `6.9+`; PHP floor remains `8.1+`; architecture remains hybrid PHP + `theme.json`.
- No new RootProfile, ConvertFlow, WooCommerce or other provider integration/certification work. Provider L5 is outside X6.
- No private provider storage/API reads, authoritative route heuristics, copied domain logic or parallel domain state.
- No new production presentation feature is planned in X6. A newly found product defect must be reduced to the shallowest stable reproduction and handled as a separate RED → GREEN bugfix before closure continues.
- Theme metadata stays exactly `1.1.0` through the pre-promotion gate. Changing either `style.css` or `AZNET_THEME_VERSION` before explicit owner approval is forbidden.
- Promotion, when approved, is atomic: `style.css` `Version:` and `functions.php` `AZNET_THEME_VERSION` must both become exact `1.2.0` in the same bounded production commit.
- Git tag, GitHub Release/publication and production deployment remain separate owner gates after technical X6 closure. This plan does not authorize any of them.
- Final technical PASS requires fresh evidence on final bytes; historical X1-X5 PASS supports provenance but cannot substitute for X6 final-candidate evidence.

---

## File Structure

**Create**
- `scripts/assert-theme-version.sh` — reusable metadata consistency assertion with optional exact expected version.
- `tests/offline/x6-release-closure-contract.php` — X6 ownership, retained-surface, workflow/package and no-provider-takeover contract.
- `tests/offline/x6-version-promotion-contract.php` — focused RED/GREEN contract for exact `1.2.0` promotion; created only after the promotion gate is explicitly approved for RED execution.
- `tests/runtime/x6-cross-surface.php` — one WordPress-native fixture map covering representative X1-X5 surfaces.
- `tests/browser/x6-cross-surface-l4.mjs` — integrated responsive/keyboard/focus/overflow/axe matrix.
- `.github/workflows/x6-cross-surface-release.yml` — PR and exact-main X6 L1-L4/L6 workflow.
- `docs/evidence/X6_CROSS_SURFACE_RELEASE_CLOSURE_20260916.md` — durable technical evidence after final promoted-byte verification.

**Modify before promotion**
- `scripts/verify-v1-core.sh` — include X6 release-closure contract.
- `tests/offline/x2-search-404-empty-contract.php`
- `tests/offline/x3-media-gallery-embed-contract.php`
- `tests/offline/x4-pagination-navigation-contract.php`
- `tests/offline/x5-native-form-controls-contract.php`
- `.github/workflows/x1-comments-surface.yml`
- `.github/workflows/x2-search-404-empty.yml`
- `.github/workflows/x3-media-gallery-embed.yml`
- `.github/workflows/x4-pagination-navigation.yml`
- `.github/workflows/x5-native-form-controls.yml`

These retained X1-X5 files may assert version **consistency**, but must stop asserting that current Theme version must forever remain `1.1.0`; that was an X1-X5 implementation-phase guard and becomes obsolete only in X6.

**Modify only after explicit version-promotion approval**
- `style.css`
- `functions.php`
- `scripts/verify-v1-core.sh` — include the exact `1.2.0` promotion contract after GREEN.

**Modify only after final technical evidence exists**
- `docs/source/AZT-03-baseline-provenance.md`
- `docs/source/AZT-04-roadmap-qa-decisions.md`
- `docs/source/AZT-EXEC-MAP.md`
- `docs/source/SOURCE_MANIFEST.md`

---

### Task 1: Remove obsolete fixed-`1.1.0` assumptions without changing production metadata

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

**Interfaces:**
- Consumes: current two Theme-owned version declarations.
- Produces: one reusable assertion that both declarations are non-empty, semver-like and equal; optional argument can require an exact version.

- [ ] **Step 1: inventory live fixed-version guards**

Run from repository root:

```bash
grep -RFn "Version: 1.1.0" .github/workflows tests/offline scripts || true
grep -RFn "AZNET_THEME_VERSION', '1.1.0'" .github/workflows tests/offline scripts || true
```

Classify only live test/workflow assertions. Do **not** rewrite historical docs/evidence that correctly record v1.1.0 history.

- [ ] **Step 2: write the reusable version assertion**

Create `scripts/assert-theme-version.sh`:

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

- [ ] **Step 3: generalize retained X2-X5 offline contracts**

Replace only their historical `must remain 1.1.0 during Xn` checks with parsing/equality checks. Keep every surface ownership, asset and forbidden-marker assertion unchanged.

Expected contract invariant after edit:

```php
preg_match('/^Version:\s*([^\r\n]+)/m', $style, $style_match);
preg_match("/define\( 'AZNET_THEME_VERSION', '([^']+)' \);/", $functions, $php_match);
if (($style_match[1] ?? '') === '' || ($php_match[1] ?? '') === '' || trim($style_match[1]) !== $php_match[1]) {
    throw new RuntimeException('Theme version declarations must remain present and equal.');
}
```

Use each contract's existing failure helper/style rather than changing unrelated code.

- [ ] **Step 4: generalize retained X1-X5 workflows**

Replace exact `grep ... 1.1.0` assertions with:

```bash
bash scripts/assert-theme-version.sh
```

Do not alter the WordPress 6.9, zero-plugin, runtime, browser, ownership or diff gates.

- [ ] **Step 5: prove current metadata did not change**

Run:

```bash
bash scripts/assert-theme-version.sh 1.1.0
git diff -- style.css functions.php
```

Expected: version assertion PASS and zero diff in both production version-declaration files.

- [ ] **Step 6: run retained static contracts and commit**

Run:

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS with X1-X5 functionality unchanged. Commit only the test/workflow/helper refactor.

---

### Task 2: Add the X6 release-closure contract with RED → GREEN infrastructure proof

**Files:**
- Create: `tests/offline/x6-release-closure-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: X1-X5 contracts, the generic version assertion, package builder and current release workflow.
- Produces: one deterministic L2 gate that proves X6 infrastructure exists and forbids provider/domain takeover.

- [ ] **Step 1: write the failing X6 contract before the X6 workflow/runtime/browser files exist**

The contract must read and require these exact files:

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

It must also require references to all focused X1-X5 contracts in `scripts/verify-v1-core.sh`, require the X6 workflow to mention WordPress `6.9`, PHP `8.1`, Playwright `1.55.0`, axe `4.10.2`, deterministic double-build/`cmp`, package exact-compare, zero-plugin runtime and theme-switch continuity, and reject these ownership markers from new X6 runtime/browser/workflow code:

```php
$forbidden = [
    'new WP_Query', 'query_posts(', 'pre_get_posts', '$wpdb',
    'get_post_meta(', 'choiceguide_', 'rootprofile_', 'ConvertFlow',
    'wp_redirect(', 'wp_safe_redirect(', 'XMLHttpRequest', 'fetch(',
];
```

- [ ] **Step 2: run RED**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x6-release-closure-contract.php
```

Expected: FAIL because the X6 runtime/browser/workflow files do not yet exist. Record the exact failure and commit this test-only RED.

- [ ] **Step 3: integrate the contract into the reusable verifier only when its required files are added in Task 3**

Add:

```bash
printf '%s\n' '==> X6 Cross-surface QA & Release Closure contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x6-release-closure-contract.php
```

after the X5 contract in `scripts/verify-v1-core.sh`.

---

### Task 3: Build one native WordPress X6 cross-surface fixture

**Files:**
- Create: `tests/runtime/x6-cross-surface.php`

**Interfaces:**
- Consumes: WordPress public/native APIs only.
- Produces: JSON URLs/IDs for integrated X1-X5 representative surfaces and lifecycle sentinels.

- [ ] **Step 1: create deterministic native fixtures**

The fixture must create:

- one published Post with comments open plus one approved native comment;
- enough published Posts in one Category to force archive pagination at a small `posts_per_page` set through WordPress test configuration;
- one multipage Post using `<!--nextpage-->`;
- one authored-media Post/Page containing Core image/gallery plus native video/audio/embed-safe markup using WordPress content APIs/fixtures already proven by X3;
- one ordinary Page with Core Search/Categories controls;
- one password-protected Page;
- one published search target with unique term `X6 Cross Surface Target`;
- one Home/control URL and one guaranteed missing URL;
- one native content sentinel and one Theme Mod sentinel used only to verify lifecycle continuity.

Return JSON keys exactly:

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

- [ ] **Step 2: keep fixture ownership clean**

Use `wp_insert_post`, `wp_insert_comment`, native taxonomy/menu/content APIs and ordinary WordPress options only where the existing test harness already owns test configuration. Do not create production storage, provider records or Theme runtime heuristics.

---

### Task 4: Add integrated X1-X5 browser verification

**Files:**
- Create: `tests/browser/x6-cross-surface-l4.mjs`

**Interfaces:**
- Consumes: fixture JSON from Task 3 and a running WordPress 6.9 site.
- Produces: per-route/per-viewport screenshots, axe JSON and a summary JSON.

- [ ] **Step 1: define the representative route matrix**

Verify at 1440x1000, 1024x900, 390x844 and 320x760:

1. Comments Post — comments + native form.
2. Search result — listing/navigation/forms.
3. Search empty — recovery/forms.
4. 404 — native HTTP 404/recovery/forms.
5. Paginated Archive page 2 — listing/navigation.
6. Multipage Post — content/navigation.
7. Media content — figure/gallery/audio/video/embed containment.
8. Password/Core-control surface — shared native forms.

This is 32 route/viewport cases.

- [ ] **Step 2: assert cross-surface presentation invariants**

For every case require:

```js
if (await page.locator('h1').count() !== 1) throw new Error('expected exactly one H1');
const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
if (overflow > 1) throw new Error(`horizontal overflow ${overflow}px`);
```

Collect `pageerror` and console `error` events. Run axe and fail on `critical` or `serious` violations.

- [ ] **Step 3: assert focused X1-X5 behaviors**

Require the corresponding Theme handles/classes only where expected:

- comments route: `aznet-theme-comments-css` + `aznet-theme-forms-css`;
- search-empty/404: `aznet-theme-recovery-css` + forms;
- media route: `aznet-theme-media-css`;
- archive/search/multipage navigation routes: `aznet-theme-navigation-css`;
- password/Core-control route: forms;
- Home control route used by runtime gate must not receive X6-only scoped assets without an existing legitimate surface reason.

Tab through the first meaningful interactive controls on comments, search, recovery and password/Core-control routes; require a non-zero visible focus outline/box-shadow state using the same computed-style method already proven in X1-X5.

- [ ] **Step 4: persist evidence**

Write one screenshot and one axe JSON per case plus `/tmp/x6-l4/summary.json` containing `32` total cases and zero blocking failures.

---

### Task 5: Add the X6 PR/exact-main L1-L4 + L6 workflow

**Files:**
- Create: `.github/workflows/x6-cross-surface-release.yml`
- Complete GREEN: `tests/offline/x6-release-closure-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: Tasks 1-4 plus `scripts/build-release-package.py`.
- Produces: exact candidate SHA/tree, reusable contracts, integrated browser evidence, deterministic package, package/source identity and lifecycle evidence.

- [ ] **Step 1: configure triggers and exact checkout**

Use:

```yaml
name: X6 Cross-surface Release Closure
on:
  pull_request:
    branches: [main]
  push:
    branches: [main]
  workflow_dispatch:
permissions:
  contents: read
```

Checkout `${{ github.event.pull_request.head.sha || github.sha }}` with `fetch-depth: 0`, then record exact SHA/tree under `/tmp/x6`.

- [ ] **Step 2: run L1-L2 on PHP 8.1**

Run:

```bash
bash scripts/assert-theme-version.sh
bash scripts/verify-v1-core.sh
```

Expected: production lint + all retained contracts + X6 contract PASS.

- [ ] **Step 3: install WordPress 6.9 / MySQL 8 with zero active plugins**

Use the established WP-CLI setup from X5. Activate exact candidate Theme and assert:

```bash
wp core version --path=/tmp/wp --allow-root | grep -Fx '6.9'
test -z "$(wp plugin list --status=active --field=name --path=/tmp/wp --allow-root)"
```

Seed Task 3 fixture and run the runtime asset/status boundary for all representative routes.

- [ ] **Step 4: run integrated browser L4**

Install exact `playwright@1.55.0` and `@axe-core/playwright@4.10.2`; execute Task 4 and require 32/32 PASS.

- [ ] **Step 5: build the exact candidate twice**

Parse current matching version declarations into `VERSION`. Build twice:

```bash
python3 scripts/build-release-package.py --source . --output "/tmp/x6/build-a/aznet-theme-${VERSION}.zip" --version "$VERSION"
python3 scripts/build-release-package.py --source . --output "/tmp/x6/build-b/aznet-theme-${VERSION}.zip" --version "$VERSION"
cmp "/tmp/x6/build-a/aznet-theme-${VERSION}.zip" "/tmp/x6/build-b/aznet-theme-${VERSION}.zip"
sha256sum "/tmp/x6/build-a/aznet-theme-${VERSION}.zip" | tee /tmp/x6/SHA256SUMS.txt
```

- [ ] **Step 6: verify package/source byte identity**

Unzip, require exactly one top-level `aznet-theme/`, construct the expected production file set using the existing package exclusions, then `diff -ru` expected vs extracted. Lint every packaged PHP file.

- [ ] **Step 7: verify exact-package runtime and lifecycle**

Install the extracted package into a fresh WordPress 6.9 site, rerun the X6 runtime/browser matrix, record WordPress-owned sentinel IDs/values, activate installed `twentytwentyfive`, verify the sentinels remain, reactivate `aznet-theme`, and rerun the package-backed smoke matrix.

Expected: no Theme-owned data loss; native content survives switch-away/switch-back; exact package behaves like source candidate.

- [ ] **Step 8: GREEN the X6 offline contract and commit**

Run focused then full:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x6-release-closure-contract.php
bash scripts/verify-v1-core.sh
```

Expected: PASS. Commit the bounded X6 verification infrastructure.

---

### Task 6: Prove pre-promotion technical readiness while metadata is still 1.1.0

**Files:** no production behavior changes.

**Interfaces:**
- Consumes: X6 workflow from Task 5.
- Produces: pre-promotion exact-head L1-L4/L6 evidence and the promotion hard gate.

- [ ] **Step 1: run the X6 workflow on the exact pre-promotion head**

Require:

```bash
bash scripts/assert-theme-version.sh 1.1.0
```

before the workflow's package stage.

Expected: X6 integrated 32/32 browser matrix, deterministic package, exact source/package compare and lifecycle PASS while metadata is still `1.1.0`.

- [ ] **Step 2: require all triggered retained workflows GREEN on the same exact head**

Do not accept historical workflow results from X1-X5 functional heads as final X6 evidence.

- [ ] **Step 3: stop at the version-promotion hard gate**

Present exact SHA/run/artifact/package digest and request explicit owner approval to promote Theme metadata to `1.2.0`.

**Hard stop:** plan approval or X5 merge approval does not authorize Task 7 production metadata changes.

---

### Task 7: RED → GREEN atomic version promotion after explicit owner approval

**Files:**
- Create: `tests/offline/x6-version-promotion-contract.php`
- Modify after RED: `style.css`
- Modify after RED: `functions.php`
- Modify after GREEN: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: owner approval and pre-promotion PASS from Task 6.
- Produces: exact atomic metadata `1.2.0` and a retained promotion contract.

- [ ] **Step 1: write the focused RED contract**

Use exact parsing, not broad substring replacement:

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

- [ ] **Step 2: run RED and record intended failure**

Run:

```bash
php tests/offline/x6-version-promotion-contract.php
```

Expected: FAIL because both declarations are still `1.1.0`. Commit test-only RED.

- [ ] **Step 3: apply the minimal atomic GREEN**

Change only production version declarations:

```diff
-Version: 1.1.0
+Version: 1.2.0
```

and:

```diff
-    define( 'AZNET_THEME_VERSION', '1.1.0' );
+    define( 'AZNET_THEME_VERSION', '1.2.0' );
```

No other production PHP/CSS/JS change belongs in this promotion commit.

- [ ] **Step 4: run focused GREEN**

Run:

```bash
bash scripts/assert-theme-version.sh 1.2.0
php tests/offline/x6-version-promotion-contract.php
git diff --check
```

Expected: PASS.

- [ ] **Step 5: add promotion contract to the reusable verifier**

Add immediately after X6 release-closure contract:

```bash
printf '%s\n' '==> X6 exact 1.2.0 metadata promotion contract'
php tests/offline/x6-version-promotion-contract.php
```

- [ ] **Step 6: verify production diff boundary and commit**

The GREEN production diff must be exactly `style.css` + `functions.php`; test/workflow/source/evidence changes are non-production support files. Commit with a bounded message such as `release: promote Theme metadata to 1.2.0`.

---

### Task 8: Verify exact promoted final head at L1-L4 and L6

**Files:** no new production behavior changes.

**Interfaces:**
- Consumes: exact `1.2.0` head from Task 7.
- Produces: final-head technical candidate evidence.

- [ ] **Step 1: run reusable core + all triggered retained workflows**

Require `bash scripts/assert-theme-version.sh 1.2.0` and full `scripts/verify-v1-core.sh` PASS on the exact promoted head.

- [ ] **Step 2: require X6 integrated browser matrix PASS**

Require 32/32 route/viewport PASS, zero critical/serious axe, no horizontal overflow, visible keyboard focus and no Theme-caused runtime errors.

- [ ] **Step 3: require deterministic exact `1.2.0` package**

Expected package name: `aznet-theme-1.2.0.zip`. Require double-build byte identity, one top-level `aznet-theme/`, source/package exact compare, packaged PHP lint and SHA-256 recording.

- [ ] **Step 4: require package-backed lifecycle PASS**

Verify WordPress-owned content/state survives switch to `twentytwentyfive` and back; then rerun the package-backed runtime/browser smoke matrix.

- [ ] **Step 5: freeze the final candidate head**

Any subsequent byte change invalidates this final-head evidence and requires fresh X6 final-head verification.

---

### Task 9: Record pre-merge X6 evidence and source state without claiming publication

**Files:**
- Create: `docs/evidence/X6_CROSS_SURFACE_RELEASE_CLOSURE_20260916.md`
- Modify: `docs/source/AZT-03-baseline-provenance.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: final promoted-head SHA/tree, X6 run, retained workflow results, package digest/artifact and lifecycle evidence.
- Produces: source state `X6 final candidate PASS / canonical merge + exact-main verification pending`.

- [ ] **Step 1: write evidence with explicit provenance**

Record:

- pre-promotion PASS SHA/run/artifact;
- RED promotion commit/failure reason;
- exact promoted final-head SHA/tree;
- all final-head workflow results;
- X6 32/32 browser summary;
- `aznet-theme-1.2.0.zip` SHA-256 and artifact digest;
- exact source/package compare and packaged PHP lint count;
- switch-away/switch-back continuity;
- Theme-owned rollback path;
- explicit statement: no provider L5, tag, GitHub Release or deployment inferred.

- [ ] **Step 2: reconcile source ownership**

AZT-03 must own the new baseline/provenance facts and version identity. AZT-04 must own roadmap/release-gate state. AZT-EXEC-MAP may derive the execution checkpoint. SOURCE_MANIFEST aligns versions/current exact next. Do not rewrite historical v1.1 evidence.

- [ ] **Step 3: preserve the merge gate**

Source wording must say canonical X6 integration is not yet PASS until owner-approved merge and fresh exact-main verification succeed.

- [ ] **Step 4: run fresh exact-final-head workflows after documentation closure**

Because source/evidence changed, rerun/reobserve all workflows on the new exact final head. Only a fully GREEN exact final head may enter merge review.

---

### Task 10: Owner-approved merge and exact-main technical closure

**Files:** canonical Git history only; no new production behavior.

**Interfaces:**
- Consumes: owner merge approval and frozen exact final head from Task 9.
- Produces: canonical `main` technical v1.2 evidence.

- [ ] **Step 1: require PR open, non-draft, mergeable and exact head unchanged**

Record expected head SHA immediately before merge.

- [ ] **Step 2: merge with expected-head protection only after explicit approval**

Use normal merge method; do not squash away TDD/provenance history unless source governance separately requires it.

- [ ] **Step 3: require fresh `V1 Exact Main Verification` on the exact merge SHA**

Both static-contracts and clean-runtime-browser jobs must complete SUCCESS.

- [ ] **Step 4: require X6 push-to-main closure workflow on the exact merge SHA**

Require exact-main `1.2.0`, integrated 32/32 browser matrix, deterministic `aznet-theme-1.2.0.zip`, source/package byte identity and package lifecycle PASS. Record exact-main package SHA/artifact.

- [ ] **Step 5: do not publish or deploy**

Technical closure does not create a tag, GitHub Release or production deployment. Stop those actions behind their separate owner gates.

---

### Task 11: Post-merge authoritative closure checkpoint

**Files:**
- Modify if exact-main evidence adds new provenance: `docs/source/AZT-03-baseline-provenance.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Update: `docs/evidence/X6_CROSS_SURFACE_RELEASE_CLOSURE_20260916.md`

**Interfaces:**
- Consumes: exact-main merge SHA, exact-main workflow IDs/artifacts/package digest.
- Produces: authoritative state `X6 TECHNICAL PASS / PUBLICATION GATED` and exactly one next action.

- [ ] **Step 1: record exact-main evidence**

Append exact merge SHA/tree, `V1 Exact Main Verification` run, X6 exact-main run, exact `1.2.0` package SHA/artifact and successful lifecycle result.

- [ ] **Step 2: set the source state precisely**

Use `X6 TECHNICAL PASS / PUBLICATION GATED` (or the exact equivalent source wording already established at execution time). Make clear that technical v1.2 closure is complete but publication and production deployment are not.

- [ ] **Step 3: set exactly one Next**

`NEXT — request owner approval for v1.2.0 publication disposition (tag + GitHub Release only); production deployment remains a later separate approval.`

- [ ] **Step 4: open the bounded docs/evidence-only closure PR and stop at approval**

Final diff must contain no production PHP/CSS/JS and no release/deployment action. This is the next true hard gate after technical X6 integration.

---

## Rollback / Recovery

- Before version promotion: revert X6 test/workflow/helper infrastructure; production Theme bytes and metadata remain `1.1.0`.
- After promotion but before merge: revert the promotion commit; both Theme version declarations return together to `1.1.0`; WordPress content/state is untouched.
- After merge but before publication: revert the X6 merge on `main` if necessary; no tag/Release/deployment has been created by this plan.
- Theme switch rollback must preserve WordPress-owned content and all external provider/domain data.
- Any failed deeper gate returns to the shallowest reproducible failing layer before further production edits.

## Self-review

- **Spec coverage:** X6 includes L1 static/contracts, retained X1-X5 L2, real WordPress 6.9 L3, integrated responsive/keyboard/focus/landmark/overflow/axe L4, deterministic package/source identity + lifecycle/rollback + exact-main L6, atomic `1.2.0` promotion, and separate publication/deployment gates.
- **Ownership:** no provider integration or domain storage is introduced; WordPress native semantics remain authoritative.
- **Versioning:** `1.1.0` is preserved through the pre-promotion gate; exact `1.2.0` changes only `style.css` and `functions.php` production metadata after explicit approval.
- **Retained-test compatibility:** X1-X5 fixed-version implementation guards are generalized before promotion, while all functional/ownership assertions remain.
- **Final-byte discipline:** branch final-head evidence is rerun after source/evidence closure; canonical exact-main verification is separately required after merge.
- **Release discipline:** no tag, GitHub Release or production deployment is performed by technical closure; publication is the next owner-gated action.
- **Placeholder scan:** no TBD/TODO/implement-later instructions; every production change, test command, gate and rollback has an explicit expected outcome.

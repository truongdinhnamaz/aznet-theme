# Roboto Default Typography Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make self-hosted Roboto the default AZnet Theme typography across frontend, WordPress editor, and Law 01 without changing domain data or integration contracts.

**Architecture:** Declare Roboto as a Theme-owned presentation asset through `theme.json` font-face/font-family metadata, expose stable AZnet typography tokens in `assets/css/tokens.css`, and apply those tokens at the global shell level. Law 01 stops owning a separate serif family and inherits the Theme defaults, with only weight/size/spacing adjustments needed for the approved presentation.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, CSS custom properties, self-hosted WOFF2 font assets, PHP offline contracts, GitHub Actions browser/runtime gates.

**Spec:** `docs/superpowers/specs/2026-09-18-roboto-default-typography-design.md`

## Global Constraints

- AZnet Theme is presentation owner only; no RootProfile, ConvertFlow, WooCommerce, Nagavia, or other provider domain logic changes.
- Default family: `Roboto, Arial, sans-serif`.
- Body/UI and headings use the same Roboto family.
- Self-host Roboto; do not load Google Fonts or another external font CDN.
- Required shipped weights: 400, 500, 700. Use the nearest supported real weight for intermediate emphasis; do not relabel a font file as 600.
- Use `font-display: swap`.
- Vietnamese glyph coverage must be present.
- No Quick Setup rerun.
- Existing Law 01 content structure, Content Map, provider boundaries, and domain data remain unchanged.
- WordPress 6.9+ and PHP 8.1+ compatibility remain mandatory.
- Implementation follows RED → intended failure → minimal GREEN → regression.
- Merge and live publish remain separate owner gates.

---

## File Structure

**Create**
- `assets/fonts/roboto/Roboto-Regular.woff2` — self-hosted Roboto 400 with Vietnamese/Latin coverage.
- `assets/fonts/roboto/Roboto-Medium.woff2` — self-hosted Roboto 500.
- `assets/fonts/roboto/Roboto-Bold.woff2` — self-hosted Roboto 700.
- `tests/offline/typography-roboto-contract.php` — static RED/GREEN contract for tokens, theme.json, asset paths, and Law 01 inheritance.

**Modify**
- `theme.json` — register Roboto family/font faces and make it the WordPress-native default.
- `assets/css/tokens.css` — add canonical Theme typography family tokens.
- `assets/css/components/homepage-law-01.css` — remove hardcoded Georgia/Times family and consume Theme heading token.
- `assets/css/components/homepage-law-01-variants.css` — remove Law 01 slogan/quote serif hardcodes and retain approved weights/italic treatment through Theme tokens.
- `inc/theme/design-system.php` — ensure editor styles include the same base typography inputs if runtime verification shows theme.json alone is insufficient for editor parity; do not add duplicate font registration if theme.json already supplies it.
- `scripts/verify-v1-core.sh` — add the new typography contract to the retained V1 verification chain.
- `docs/source/AZT-02-architecture.md` — record Roboto/token ownership as Theme presentation architecture.
- `docs/source/SOURCE_MANIFEST.md` — synchronize AZT-02 version metadata if AZT-02 is bumped.

**Do not modify**
- provisioning Content Map or Quick Setup code;
- RootProfile, ConvertFlow, WooCommerce, Nagavia integration/domain code;
- WordPress posts/pages/options for typography;
- live WPVibe draft until a verified implementation candidate reaches a separate preview/publish gate.

---

### Task 1: Pin the Theme-wide typography contract

**Files:**
- Create: `tests/offline/typography-roboto-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: existing `theme.json`, `assets/css/tokens.css`, `style.css`, Law 01 component CSS.
- Produces: a retained contract that later tasks must satisfy before browser/runtime work.

- [ ] **Step 1: Write the failing typography contract**

Create `tests/offline/typography-roboto-contract.php` with assertions equivalent to:

```php
<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$theme_json_path = $root . '/theme.json';
$tokens_path = $root . '/assets/css/tokens.css';
$style_path = $root . '/style.css';
$law_base_path = $root . '/assets/css/components/homepage-law-01.css';
$law_variant_path = $root . '/assets/css/components/homepage-law-01-variants.css';

foreach ([$theme_json_path, $tokens_path, $style_path, $law_base_path, $law_variant_path] as $path) {
    assert(is_file($path), "Typography contract input missing: {$path}");
}

$theme_json = json_decode((string) file_get_contents($theme_json_path), true, 512, JSON_THROW_ON_ERROR);
$tokens = (string) file_get_contents($tokens_path);
$style = (string) file_get_contents($style_path);
$law_base = (string) file_get_contents($law_base_path);
$law_variant = (string) file_get_contents($law_variant_path);

assert(str_contains($tokens, '--aznet-theme-font-family-base:'));
assert(str_contains($tokens, '--aznet-theme-font-family-heading:'));
assert(str_contains($tokens, 'Roboto'));
assert(str_contains($style, 'font-family: var(--aznet-theme-font-family-base);'));

$families = $theme_json['settings']['typography']['fontFamilies'] ?? [];
$roboto = array_values(array_filter(
    $families,
    static fn(array $family): bool => 'roboto' === ($family['slug'] ?? null)
));
assert(1 === count($roboto), 'theme.json must expose exactly one Roboto family.');
assert('Roboto, Arial, sans-serif' === ($roboto[0]['fontFamily'] ?? null));

$faces = $roboto[0]['fontFace'] ?? [];
$weights = array_map(static fn(array $face): string => (string) ($face['fontWeight'] ?? ''), $faces);
foreach (['400', '500', '700'] as $weight) {
    assert(in_array($weight, $weights, true), "Roboto fontFace missing weight {$weight}.");
}
foreach ($faces as $face) {
    assert('swap' === ($face['fontDisplay'] ?? null), 'Roboto fontFace must use font-display swap.');
    foreach ((array) ($face['src'] ?? []) as $src) {
        assert(! str_contains((string) $src, 'fonts.googleapis.com'));
        assert(! str_contains((string) $src, 'fonts.gstatic.com'));
        assert(str_contains((string) $src, 'assets/fonts/roboto/'));
    }
}

assert(! str_contains($law_base, "Georgia, 'Times New Roman', serif"));
assert(! str_contains($law_variant, "Georgia, 'Times New Roman', serif"));
assert(str_contains($law_base, 'var(--aznet-theme-font-family-heading)'));
assert(str_contains($law_variant, 'var(--aznet-theme-font-family-heading)'));

echo "PASS: Roboto default typography contract\n";
```

- [ ] **Step 2: Run only the new contract and verify intended RED**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-roboto-contract.php
```

Expected: FAIL because Theme typography family tokens/fontFaces do not exist and Law 01 still hardcodes Georgia/Times. The failure must be an assertion from this contract, not a parse/runtime error.

- [ ] **Step 3: Add the contract to the retained verification chain**

In `scripts/verify-v1-core.sh`, add immediately before the Homepage Composer + Law 01 group:

```bash
printf '%s\n' '==> Theme default typography contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-roboto-contract.php
```

- [ ] **Step 4: Commit the RED checkpoint**

```bash
git add tests/offline/typography-roboto-contract.php scripts/verify-v1-core.sh
git commit -m "test: define Roboto default typography contract"
```

---

### Task 2: Add self-hosted Roboto and canonical Theme typography tokens

**Files:**
- Create: `assets/fonts/roboto/Roboto-Regular.woff2`
- Create: `assets/fonts/roboto/Roboto-Medium.woff2`
- Create: `assets/fonts/roboto/Roboto-SemiBold.woff2`
- Create: `assets/fonts/roboto/Roboto-Bold.woff2`
- Modify: `theme.json`
- Modify: `assets/css/tokens.css`

**Interfaces:**
- Consumes: Theme-owned design-token layer and WordPress `theme.json` fontFace support.
- Produces: `--aznet-theme-font-family-base`, `--aznet-theme-font-family-heading`, WordPress `roboto` font-family preset, and local WOFF2 asset URLs.

- [ ] **Step 1: Add verified Roboto WOFF2 files**

Use an official Roboto distribution that permits redistribution. Store only the four required files under `assets/fonts/roboto/`.

Before committing, verify:
- each file is non-empty;
- each is a WOFF2 font;
- Vietnamese characters are covered by the chosen build;
- filenames exactly match the four paths in this task.

Run:

```bash
file assets/fonts/roboto/*.woff2
ls -lh assets/fonts/roboto/*.woff2
```

Expected: four WOFF2 files, no zero-byte file.

Roboto static distributions do not consistently ship a 600 face. This implementation deliberately uses real 400/500/700 faces and maps intermediate emphasis to 500 or 700 rather than relabeling a different binary as 600.

- [ ] **Step 2: Add canonical family tokens**

In `assets/css/tokens.css`, directly before existing font-size tokens, add:

```css
--aznet-theme-font-family-base: Roboto, Arial, sans-serif;
--aznet-theme-font-family-heading: var(--aznet-theme-font-family-base);
```

- [ ] **Step 3: Register Roboto through theme.json**

Under `settings.typography`, add a single family:

```json
"fontFamilies": [
  {
    "slug": "roboto",
    "name": "Roboto",
    "fontFamily": "Roboto, Arial, sans-serif",
    "fontFace": [
      {
        "fontFamily": "Roboto",
        "fontStyle": "normal",
        "fontWeight": "400",
        "fontDisplay": "swap",
        "src": ["file:./assets/fonts/roboto/Roboto-Regular.woff2"]
      },
      {
        "fontFamily": "Roboto",
        "fontStyle": "normal",
        "fontWeight": "500",
        "fontDisplay": "swap",
        "src": ["file:./assets/fonts/roboto/Roboto-Medium.woff2"]
      },
      {
        "fontFamily": "Roboto",
        "fontStyle": "normal",
        "fontWeight": "700",
        "fontDisplay": "swap",
        "src": ["file:./assets/fonts/roboto/Roboto-Bold.woff2"]
      }
    ]
  }
]
```

Set the global `styles.typography.fontFamily` to:

```json
"fontFamily": "var:preset|font-family|roboto"
```

Keep existing font-size and line-height values intact.

- [ ] **Step 4: Keep the release metadata stylesheet untouched**

Do not modify `style.css`. The WordPress-native global font default is supplied by `theme.json`; Law 01 consumes the Theme semantic family token directly. This preserves the retained X6 promoted-release metadata boundary.

- [ ] **Step 5: Run the contract**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-roboto-contract.php
```

Expected at this stage: still FAIL only on the retained Law 01 Georgia/Times assertions. Theme token/theme.json/font asset assertions must pass.

- [ ] **Step 6: Commit Theme-wide typography infrastructure**

```bash
git add theme.json assets/css/tokens.css assets/fonts/roboto
git commit -m "feat: add self-hosted Roboto typography"
```

---

### Task 3: Make Law 01 inherit Roboto and tune typography without restructuring layout

**Files:**
- Modify: `assets/css/components/homepage-law-01.css`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Test: `tests/offline/typography-roboto-contract.php`
- Test: `tests/offline/homepage-law-01-client-ready-contract.php`

**Interfaces:**
- Consumes: `--aznet-theme-font-family-base` and `--aznet-theme-font-family-heading` from Task 2.
- Produces: Law 01 typography that inherits the Theme family while preserving approved burgundy/gold layout and content structure.

- [ ] **Step 1: Replace Law 01 hardcoded heading family**

In `assets/css/components/homepage-law-01.css`, replace:

```css
font-family: Georgia, 'Times New Roman', serif;
```

on the section heading selector with:

```css
font-family: var(--aznet-theme-font-family-heading);
```

Set heading weights deliberately:

```css
.aznet-theme-homepage--law-01 .aznet-theme-law01-section h1 {
    font-weight: 700;
}

.aznet-theme-homepage--law-01 .aznet-theme-law01-section h2,
.aznet-theme-homepage--law-01 .aznet-theme-law01-section h3 {
    font-weight: 600;
}
```

Keep existing responsive font-size clamps unless a browser failure demonstrates a metric-specific adjustment is required.

- [ ] **Step 2: Remove the service-number serif dependency**

Replace the service counter family with:

```css
font-family: var(--aznet-theme-font-family-heading);
font-weight: 600;
```

Do not change the counter content or service data source.

- [ ] **Step 3: Normalize burgundy-gold slogan/quote typography**

In `assets/css/components/homepage-law-01-variants.css`, replace each Georgia/Times declaration used by the slogan and hero quote with:

```css
font-family: var(--aznet-theme-font-family-heading);
```

Preserve `font-style: italic` where already approved. Keep the hero quote at `font-weight: 700`; use 500–600 for supporting UI/labels only where a selector already exists.

- [ ] **Step 4: Run focused GREEN contracts**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-roboto-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-client-ready-contract.php
```

Expected: both PASS.

- [ ] **Step 5: Run the complete retained static verification**

Run:

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS with zero contract failures.

- [ ] **Step 6: Commit Law 01 typography inheritance**

```bash
git add assets/css/components/homepage-law-01.css assets/css/components/homepage-law-01-variants.css
git commit -m "style: align Law01 with Theme Roboto typography"
```

---

### Task 4: Prove WordPress/editor/runtime/browser behavior

**Files:**
- Modify only if needed after an evidenced failure: `inc/theme/design-system.php`
- Evidence paths: use existing CI/browser evidence conventions; do not invent a new runtime framework.

**Interfaces:**
- Consumes: Theme candidate from Tasks 1–3.
- Produces: L3/L4 evidence that the font is local, computed as Roboto, responsive, and accessible.

- [ ] **Step 1: Run clean WordPress runtime**

Use the existing WordPress 6.9 / PHP 8.1 candidate workflow or equivalent retained test harness.

Verify:
- Theme activates;
- homepage renders;
- no PHP warnings/fatals;
- Roboto WOFF2 URLs return HTTP 200;
- no request host matches `fonts.googleapis.com` or `fonts.gstatic.com`.

Expected: L3 PASS.

- [ ] **Step 2: Verify editor parity**

Open a WordPress editor surface covered by current editor-style support and inspect computed typography.

Expected:
- editor body/content uses Roboto when font assets load;
- no duplicate external font registration;
- no missing-font console/network error.

If this fails because editor styles do not receive the Theme token, minimally modify `inc/theme/design-system.php` so `editor_stylesheets()` retains `assets/css/tokens.css` and the WordPress-native theme.json Roboto family is available. Do not create a second fonts stylesheet unless runtime evidence proves theme.json cannot supply the local font face.

- [ ] **Step 3: Run Law 01 desktop/mobile browser matrix**

At minimum verify viewports:
- 1440 × 900;
- 1024 × 768;
- 390 × 844;
- 320 × 800.

Assertions:
- computed `font-family` for body contains `Roboto`;
- computed `font-family` for Law 01 H1 contains `Roboto`;
- no Georgia/Times is computed on Law 01 heading/slogan surfaces;
- `document.documentElement.scrollWidth <= document.documentElement.clientWidth`;
- Vietnamese sample text such as `Luật sư Tâm Đức – Hà Nội hỗ trợ khách hàng` is visible and not clipped;
- axe has zero critical violations.

- [ ] **Step 4: Run performance regression**

Use retained R6/performance workflow.

Acceptance:
- no material regression attributable to font delivery;
- local WOFF2 assets are cacheable;
- no external font connection/preconnect is introduced.

If performance regresses materially, first reduce unnecessary font bytes/weights. Do not switch to an external CDN as a shortcut.

- [ ] **Step 5: Run the complete retained candidate CI set**

Required green gates include:
- V1 Core Pull Request CI;
- Homepage Composer Law 01 Browser Quality;
- F Homepage Native Runtime Browser;
- F Homepage Native Regression Package;
- D-027 Standalone Core L3;
- D-027 Standalone Core L4;
- D-027 Standalone Core Exact Package;
- D-027 Retained Full Regression;
- R6 Performance Baseline;
- X/Y retained regression/release workflows that trigger on the candidate.

Do not claim cross-layer PASS from a smaller subset.

- [ ] **Step 6: Commit any evidence-driven editor/runtime correction**

Only if Step 2–4 required a code correction:

```bash
git add inc/theme/design-system.php
git commit -m "fix: keep Roboto typography consistent in editor runtime"
```

If no correction was needed, do not create an empty commit.

---

### Task 5: Synchronize authoritative architecture source and prepare review PR

**Files:**
- Modify: `docs/source/AZT-02-architecture.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Preserve: `docs/superpowers/specs/2026-09-18-roboto-default-typography-design.md`
- Preserve: this implementation plan.

**Interfaces:**
- Consumes: accepted design decision and verified implementation behavior.
- Produces: durable source-of-truth record plus a reviewable PR; no live deployment.

- [ ] **Step 1: Update AZT-02 in its design-system/presentation section**

Record, without duplicating current-state evidence:
- Theme owns typography tokens and font presentation assets;
- Roboto is the Theme default family;
- body/headings consume Theme-owned tokens;
- self-hosted font delivery is presentation infrastructure;
- providers/domain sources do not own or mutate Theme typography.

Increment AZT-02 version according to the repository's existing versioning convention and date it 18/09/2026.

- [ ] **Step 2: Synchronize SOURCE_MANIFEST**

Update only the AZT-02 version/date/hash or manifest fields required by the current manifest format. Do not rewrite unrelated source entries.

- [ ] **Step 3: Re-run full verification on the exact final candidate**

Run:

```bash
bash scripts/verify-v1-core.sh
```

Then require all exact-head GitHub workflows from Task 4 to be completed with conclusion `success`.

- [ ] **Step 4: Commit source synchronization**

```bash
git add docs/source/AZT-02-architecture.md docs/source/SOURCE_MANIFEST.md
git commit -m "docs: record default Roboto typography architecture"
```

- [ ] **Step 5: Open a PR against main**

PR title:

```text
feat: make Roboto the default Theme typography
```

PR body must summarize:
- self-hosted Roboto;
- Theme typography tokens + theme.json;
- Law 01 inheritance;
- RED → GREEN evidence;
- no external font CDN;
- no domain/provider data changes;
- exact L1–L4/L6 evidence;
- merge and live publish remain separate owner gates.

- [ ] **Step 6: Stop at merge gate**

Do not merge the PR and do not overwrite/publish the existing WPVibe Law 01 draft without explicit owner approval.

Rollback before merge: close PR/delete feature branch if explicitly requested.
Rollback after merge but before live publish: revert the typography merge commit.
Live rollback remains separate and must use the WPVibe/theme backup path only after explicit approval.

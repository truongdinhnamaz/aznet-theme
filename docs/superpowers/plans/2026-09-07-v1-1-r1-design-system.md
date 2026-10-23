# AZnet Theme v1.1 R1 Design System 2.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build Design System 2.0 with a stable semantic token model, one Theme-owned settings interface and Default/Editorial/Commerce visual presets that stay aligned between editor and frontend.

**Architecture:** Preserve hybrid PHP + `theme.json`. Introduce a small Theme settings module, expand CSS tokens, prove native style-variation feasibility on WordPress 6.9, then use either native variations or an explicit Theme-owned visual-preset mapping without changing content ownership.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, `theme.json` v3, CSS custom properties, PHP Theme Mods, WordPress editor styles, WP-CLI, browser checks.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-1-native-product-system-design.md`

## Global Constraints

- R0 source reconciliation must be merged before production R1 begins.
- Existing public `--aznet-theme-*` semantics must not silently change.
- `aznet_theme_settings` is Theme-owned presentation state only.
- No page builder, FSE takeover or proprietary content schema.
- Visual outcome is fixed: Default / Editorial / Commerce; mechanism is evidence-gated on WordPress 6.9.
- RED -> intended failure -> minimal GREEN -> regression for behavior changes.

---

### Task 1: Create the versioned Theme settings foundation

**Files:**
- Create: `inc/theme/settings.php`
- Modify: `inc/theme/bootstrap.php`
- Create: `tests/offline/r1-settings-contract.php`

**Interfaces:**
- Produces:
  - `settings_defaults(): array`
  - `normalize_settings(array $raw): array`
  - `settings(): array`
  - `setting(string $key, mixed $fallback = null): mixed`
  - storage Theme Mod key `aznet_theme_settings`

- [ ] **Step 1: Write the RED contract**

Create `tests/offline/r1-settings-contract.php` that stubs `get_theme_mod()` and asserts:
```php
$defaults = \AZnet\Theme\settings_defaults();
assert($defaults['schema_version'] === 1);
assert($defaults['visual_preset'] === 'default');

$GLOBALS['r1_theme_mod'] = [
    'schema_version' => 1,
    'visual_preset'  => 'commerce',
    'unknown_key'    => 'must-drop',
];
$normalized = \AZnet\Theme\settings();
assert($normalized['visual_preset'] === 'commerce');
assert(!array_key_exists('unknown_key', $normalized));
```
Also assert invalid preset values normalize to `default`.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r1-settings-contract.php
```
Expected: FAIL because `inc/theme/settings.php` and functions do not exist.

- [ ] **Step 3: Implement minimal settings module**

Use:
```php
function settings_defaults(): array {
    return [
        'schema_version' => 1,
        'visual_preset'  => 'default',
    ];
}

function normalize_settings(array $raw): array {
    $defaults = settings_defaults();
    $preset = isset($raw['visual_preset']) && in_array($raw['visual_preset'], ['default', 'editorial', 'commerce'], true)
        ? (string) $raw['visual_preset']
        : 'default';

    return [
        'schema_version' => 1,
        'visual_preset'  => $preset,
    ];
}

function settings(): array {
    $raw = get_theme_mod('aznet_theme_settings', []);
    return normalize_settings(is_array($raw) ? $raw : []);
}

function setting(string $key, mixed $fallback = null): mixed {
    $all = settings();
    return array_key_exists($key, $all) ? $all[$key] : $fallback;
}
```
Require `settings.php` before modules that consume settings.

- [ ] **Step 4: Run GREEN + retained static contracts**

```bash
php tests/offline/r1-settings-contract.php
bash scripts/verify-g3-core.sh
```
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add inc/theme/settings.php inc/theme/bootstrap.php tests/offline/r1-settings-contract.php
git commit -m "feat: add versioned theme presentation settings"
```

---

### Task 2: Expand foundation and semantic tokens without breaking v1.0 aliases

**Files:**
- Modify: `assets/css/tokens.css`
- Create: `tests/offline/r1-token-contract.php`

**Interfaces:**
- Consumes: existing v1.0 token family.
- Produces: expanded foundation/semantic vocabulary while retaining old aliases.

- [ ] **Step 1: Write RED token contract**

Assert the CSS contains new canonical roles:
```text
--aznet-theme-color-primary
--aznet-theme-color-on-primary
--aznet-theme-color-surface-subtle
--aznet-theme-color-success
--aznet-theme-color-warning
--aznet-theme-color-danger
--aznet-theme-font-size-sm
--aznet-theme-font-size-base
--aznet-theme-font-size-lg
--aznet-theme-font-size-xl
--aznet-theme-line-height-body
--aznet-theme-line-height-heading
--aznet-theme-radius-control
--aznet-theme-shadow-card
--aznet-theme-motion-fast
--aznet-theme-motion-base
```
Also assert legacy variables such as `--aznet-theme-accent`, `--aznet-theme-text`, `--aznet-theme-surface`, `--aznet-theme-muted`, `--aznet-theme-border`, `--aznet-theme-focus` still exist.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r1-token-contract.php
```
Expected: FAIL on missing new tokens.

- [ ] **Step 3: Add minimal semantic mapping**

Use old values as compatibility source where possible, for example:
```css
:root {
    --aznet-theme-color-primary: #175cd3;
    --aznet-theme-color-on-primary: #ffffff;
    --aznet-theme-color-surface: #ffffff;
    --aznet-theme-color-surface-subtle: #f7f8fa;
    --aznet-theme-color-text: #17181b;
    --aznet-theme-color-text-muted: #626873;
    --aznet-theme-color-border: #d9dde4;
    --aznet-theme-color-focus: #175cd3;
    --aznet-theme-color-success: #157f3b;
    --aznet-theme-color-warning: #9a6700;
    --aznet-theme-color-danger: #b42318;

    --aznet-theme-accent: var(--aznet-theme-color-primary);
    --aznet-theme-text: var(--aznet-theme-color-text);
    --aznet-theme-muted: var(--aznet-theme-color-text-muted);
    --aznet-theme-border: var(--aznet-theme-color-border);
    --aznet-theme-focus: var(--aznet-theme-color-focus);
}
```
Add the approved typography/control/motion scale with bounded values; do not remove existing container/spacing tokens.

- [ ] **Step 4: Run GREEN and ConvertFlow coexistence regression**

```bash
php tests/offline/r1-token-contract.php
php tests/integration/w8-convertflow-coexistence.php
```
The integration test requires its existing `CONVERTFLOW_ROOT` fixture; if unavailable locally, rely on its GitHub Actions job and do not claim L5 locally.

- [ ] **Step 5: Commit**

```bash
git add assets/css/tokens.css tests/offline/r1-token-contract.php
git commit -m "feat: expand semantic design tokens"
```

---

### Task 3: Prove visual-preset mechanism on WordPress 6.9

**Files:**
- Create: `tests/offline/r1-visual-preset-mechanism-contract.php`
- Create: `.github/workflows/r1-visual-preset-feasibility.yml`
- Create: `docs/evidence/R1_VISUAL_PRESET_FEASIBILITY.md`

**Interfaces:**
- Produces one explicit result: `NATIVE_STYLE_VARIATIONS` or `THEME_VISUAL_PRESET_MAPPING`.

- [ ] **Step 1: Add a WordPress 6.9 fixture probe**

The workflow must install WordPress 6.9, activate the Theme, then query/inspect whether classic/hybrid theme style-variation files can be selected/applied in a way that changes both editor settings and frontend presentation without converting templates to block/FSE ownership.

Record exact commands, including:
```bash
wp core version
wp theme activate aznet-theme
wp eval 'echo wp_get_theme()->get("Name");'
```
The probe must create a temporary variation fixture only inside the CI workspace, not commit production behavior.

- [ ] **Step 2: Define the decision rule in the test/evidence**

```text
PASS_NATIVE only if WordPress 6.9 can select/apply the variation for this hybrid Theme and frontend + editor both observe it.
Otherwise PASS_MAPPING and use Theme-owned visual preset mapping.
```
A probe failure is not permission to change architecture.

- [ ] **Step 3: Run the workflow and record evidence**

Evidence must include WordPress/PHP versions, exact outcome and artifact/log IDs.

- [ ] **Step 4: Commit feasibility evidence**

```bash
git add tests/offline/r1-visual-preset-mechanism-contract.php .github/workflows/r1-visual-preset-feasibility.yml docs/evidence/R1_VISUAL_PRESET_FEASIBILITY.md
git commit -m "test: prove v1.1 visual preset mechanism"
```

---

### Task 4: Implement Default / Editorial / Commerce visual presets

**Files:**
- Modify: `theme.json`
- Create either native `styles/*.json` OR `assets/css/presets/editorial.css` + `assets/css/presets/commerce.css` according to Task 3
- Create: `inc/theme/design-system.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/theme/assets.php`
- Create: `tests/offline/r1-visual-preset-contract.php`

**Interfaces:**
- Consumes: `setting('visual_preset')`.
- Produces:
  - `visual_preset(): string`
  - `visual_preset_body_classes(array $classes): array`
  - editor/frontend preset parity.

- [ ] **Step 1: Write RED contract**

Assert:
```php
assert(\AZnet\Theme\visual_preset() === 'editorial');
$classes = \AZnet\Theme\visual_preset_body_classes([]);
assert(in_array('aznet-theme-preset--editorial', $classes, true));
```
Also assert only the non-default preset asset is enqueued when using mapping mode.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r1-visual-preset-contract.php
```

- [ ] **Step 3: Implement the selected mechanism**

Mapping mode must use:
```php
function visual_preset(): string {
    $value = setting('visual_preset', 'default');
    return in_array($value, ['default', 'editorial', 'commerce'], true) ? $value : 'default';
}
```
Add body class `aznet-theme-preset--{preset}` and enqueue only the corresponding preset CSS for non-default presets. Native mode must keep the same semantic outcome and test contract.

- [ ] **Step 4: Expand `theme.json` curated editor controls**

Expose bounded palette/font-size/spacing/layout settings backed by the semantic vocabulary. Do not turn on unrestricted `appearanceTools` unless the feasibility evidence demonstrates it preserves the guardrails required by the spec.

- [ ] **Step 5: Run GREEN**

```bash
php tests/offline/r1-visual-preset-contract.php
bash scripts/verify-g3-core.sh
```

- [ ] **Step 6: Commit**

```bash
git add theme.json inc/theme/design-system.php inc/theme/bootstrap.php inc/theme/assets.php assets/css/presets tests/offline/r1-visual-preset-contract.php
git commit -m "feat: add native visual preset system"
```

---

### Task 5: Prove editor/frontend parity and responsive accessibility

**Files:**
- Create: `tests/browser/r1-design-system-l4.mjs`
- Create: `.github/workflows/r1-design-system-browser.yml`
- Create: `docs/evidence/R1_DESIGN_SYSTEM_L4.md`

**Interfaces:**
- Consumes: final R1 candidate.
- Produces: L4 evidence for all three visual presets.

- [ ] **Step 1: Build fixture content with headings, paragraph, buttons, columns and media**

Use WordPress core blocks only. For each preset, verify computed frontend variables and editor-visible styles represent the same role hierarchy.

- [ ] **Step 2: Test desktop/tablet/mobile**

Use at least:
```text
1440x1000
1024x900
390x844
```
Assert no horizontal overflow >1px, visible focus, reduced-motion behavior and axe critical/serious = 0 for Theme-controlled fixture surfaces.

- [ ] **Step 3: Verify package/core regressions**

```bash
bash scripts/verify-g3-core.sh
```
Run the R1 browser workflow and retained G core browser/runtime jobs.

- [ ] **Step 4: Commit evidence**

```bash
git add tests/browser/r1-design-system-l4.mjs .github/workflows/r1-design-system-browser.yml docs/evidence/R1_DESIGN_SYSTEM_L4.md
git commit -m "test: verify design system editor frontend parity"
```

**Exit:** R1 L1-L4 PASS on the selected WordPress-6.9-compatible mechanism.  
**Next:** R2/R3/R4 may begin independently on latest merged main.

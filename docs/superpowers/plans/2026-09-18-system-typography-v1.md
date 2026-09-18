# System Typography v1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace AZnet Theme's Roboto-specific typography with a Theme-wide semantic system-font typography architecture and migrate all Theme presentation surfaces to role-based typography tokens.

**Architecture:** `assets/css/tokens.css` becomes the single Theme-owned typography primitive source: one system font stack plus semantic role tokens for size, weight, line-height, and tracking. `theme.json` mirrors that contract into WordPress without any self-hosted fontFace dependency. Component CSS consumes semantic tokens by role; old `sm/base/lg/xl` tokens remain compatibility aliases until a separate removal decision.

**Tech Stack:** WordPress classic PHP theme + `theme.json` v3, CSS custom properties, PHP offline contract tests, existing GitHub Actions/browser QA.

**Spec:** `docs/superpowers/specs/2026-09-18-system-typography-v1-design.md`

## Global Constraints

- Scope is AZnet Theme only; canonical repo is `truongdinhnamaz/aznet-theme`.
- Theme is presentation owner only; do not change RootProfile, ConvertFlow, Nagavia, WooCommerce business/domain state, or WordPress content ownership.
- WordPress floor remains 6.9+ and PHP floor remains 8.1+.
- Default family is exactly: `system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif`.
- No external font CDN and no default self-hosted font binary dependency.
- Body/form baseline remains 1rem; do not shrink standard body/form text to solve layout issues.
- Preferred font weights are 400/500/600/700 only.
- Old `--aznet-theme-font-size-sm/base/lg/xl` tokens remain compatibility aliases where existing consumers still need them.
- Keep generic content-aware asset versioning; do not remove cache busting just because custom fonts are removed.
- Do not rerun Quick Setup.
- Production deployment is a separate approval gate after merge.

---

## File Structure

### Core typography contract
- Modify: `assets/css/tokens.css` — canonical system family + semantic typography primitives + compatibility aliases.
- Modify: `theme.json` — WordPress/editor mirror of the same typography system.
- Delete: `assets/fonts/roboto/Roboto-Regular.woff2`
- Delete: `assets/fonts/roboto/Roboto-Medium.woff2`
- Delete: `assets/fonts/roboto/Roboto-Bold.woff2`
- Replace: `tests/offline/typography-roboto-contract.php` with `tests/offline/typography-system-contract.php`.
- Keep: `tests/offline/typography-token-cache-contract.php`.
- Modify: `scripts/verify-v1-core.sh`.

### Shared/surface presentation
- Modify: `assets/css/components/site-header.css`
- Modify: `assets/css/components/header-utility.css`
- Modify: `assets/css/components/site-footer.css`
- Modify: `assets/css/components/homepage.css`
- Modify: `assets/css/components/homepage-law-01.css`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify: `assets/css/components/generic-content.css`
- Modify: `assets/css/components/page.css`
- Modify: `assets/css/components/article.css`
- Modify: `assets/css/components/comments.css`
- Modify: `assets/css/components/media.css`
- Modify: `assets/css/components/navigation.css`
- Modify: `assets/css/components/recovery.css`
- Modify: `assets/css/components/forms.css`
- Modify: `assets/css/components/contact-surface.css`
- Modify: `assets/css/components/profile-surface.css`
- Modify: `assets/css/components/woocommerce-archive.css`
- Modify: `assets/css/components/woocommerce-product.css`
- Inspect and migrate only where needed: `assets/css/components/woocommerce-cart.css`, `woocommerce-checkout.css`, `woocommerce-account.css`, `woocommerce-blocks.css`.

### Governance/evidence
- Modify: `docs/source/AZT-02-architecture.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Update only after fresh PASS if current-state provenance changes: `docs/source/AZT-03-baseline-provenance.md`, `docs/source/AZT-04-roadmap-qa-decisions.md`, `docs/source/AZT-EXEC-MAP.md`.
- Create final evidence: `docs/evidence/SYSTEM_TYPOGRAPHY_V1_20260918.md`.

---

### Task 1: Establish RED contracts for system typography

**Files:**
- Create: `tests/offline/typography-system-contract.php`
- Create: `tests/offline/typography-hardcode-audit-contract.php`
- Modify: `scripts/verify-v1-core.sh`
- Remove only after replacement test is wired: `tests/offline/typography-roboto-contract.php`

**Interfaces:**
- Consumes: current `theme.json`, `assets/css/tokens.css`, component CSS tree.
- Produces: deterministic RED contract requiring system stack, semantic role tokens, no Roboto fontFace/binaries, no Georgia/Times family, and no unapproved local font-family declarations.

- [ ] **Step 1: Write the new system typography contract**

Create `tests/offline/typography-system-contract.php`:

```php
<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$themeJsonPath = $root . '/theme.json';
$tokensPath = $root . '/assets/css/tokens.css';

assert(is_file($themeJsonPath));
assert(is_file($tokensPath));

$themeJson = json_decode((string) file_get_contents($themeJsonPath), true, 512, JSON_THROW_ON_ERROR);
$tokens = (string) file_get_contents($tokensPath);

$systemStack = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';

assert(str_contains(
    $tokens,
    '--aznet-theme-font-family-base: ' . $systemStack . ';'
));

foreach ([
    '--aznet-theme-text-display:',
    '--aznet-theme-text-h1:',
    '--aznet-theme-text-h2:',
    '--aznet-theme-text-h3:',
    '--aznet-theme-text-h4:',
    '--aznet-theme-text-lead:',
    '--aznet-theme-text-body:',
    '--aznet-theme-text-small:',
    '--aznet-theme-text-navigation:',
    '--aznet-theme-text-button:',
    '--aznet-theme-text-label:',
    '--aznet-theme-text-meta:',
    '--aznet-theme-text-caption:',
    '--aznet-theme-text-form:',
    '--aznet-theme-text-price:',
] as $token) {
    assert(str_contains($tokens, $token), "Missing semantic typography token {$token}");
}

$families = $themeJson['settings']['typography']['fontFamilies'] ?? [];
assert(1 === count($families), 'theme.json must expose one canonical system family.');
assert('system' === ($families[0]['slug'] ?? null));
assert($systemStack === ($families[0]['fontFamily'] ?? null));
assert(empty($families[0]['fontFace'] ?? []), 'System family must not declare fontFace.');

assert(
    'var:preset|font-family|system' === ($themeJson['styles']['typography']['fontFamily'] ?? null),
    'Global theme.json typography must use the system preset.'
);

foreach ([
    $root . '/assets/fonts/roboto/Roboto-Regular.woff2',
    $root . '/assets/fonts/roboto/Roboto-Medium.woff2',
    $root . '/assets/fonts/roboto/Roboto-Bold.woff2',
] as $fontPath) {
    assert(! is_file($fontPath), "Obsolete default font asset remains: {$fontPath}");
}

echo "PASS: system typography architecture contract\n";
```

- [ ] **Step 2: Write the hardcode audit contract**

Create `tests/offline/typography-hardcode-audit-contract.php`:

```php
<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssRoot = $root . '/assets/css';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cssRoot));

$forbiddenFamilies = [
    "Georgia, 'Times New Roman', serif",
    'font-family: Roboto',
    'font-family: Arial',
    'font-family: Helvetica',
];

$forbiddenWeights = [
    'font-weight: 750',
    'font-weight: 800',
];

$violations = [];

foreach ($iterator as $file) {
    if (! $file->isFile() || 'css' !== strtolower($file->getExtension())) {
        continue;
    }

    $path = $file->getPathname();
    $css = (string) file_get_contents($path);

    foreach (array_merge($forbiddenFamilies, $forbiddenWeights) as $needle) {
        if (str_contains($css, $needle)) {
            $violations[] = str_replace($root . '/', '', $path) . ' -> ' . $needle;
        }
    }
}

assert([] === $violations, "Typography hardcode violations:\n" . implode("\n", $violations));

echo "PASS: typography hardcode audit contract\n";
```

- [ ] **Step 3: Wire both contracts into V1 core verification**

Replace the existing Roboto line in `scripts/verify-v1-core.sh`:

```bash
printf '%s\n' '==> Theme system typography contracts'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-system-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-hardcode-audit-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-token-cache-contract.php
```

- [ ] **Step 4: Run RED**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-system-contract.php
```

Expected: FAIL because tokens/theme.json still use Roboto and the Roboto WOFF2 files still exist.

Then run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-hardcode-audit-contract.php
```

Expected: FAIL on current hardcoded family/weight occurrences such as Law 01 / header utility if still present.

- [ ] **Step 5: Commit RED**

```bash
git add tests/offline/typography-system-contract.php tests/offline/typography-hardcode-audit-contract.php scripts/verify-v1-core.sh
git rm tests/offline/typography-roboto-contract.php
git commit -m "test: define system typography contracts"
```

---

### Task 2: Implement the canonical system typography primitives

**Files:**
- Modify: `assets/css/tokens.css`
- Modify: `theme.json`
- Delete: `assets/fonts/roboto/Roboto-Regular.woff2`
- Delete: `assets/fonts/roboto/Roboto-Medium.woff2`
- Delete: `assets/fonts/roboto/Roboto-Bold.woff2`

**Interfaces:**
- Produces canonical family token `--aznet-theme-font-family-base` and semantic size/weight/line-height tokens consumed by all later tasks.
- Preserves compatibility aliases `--aznet-theme-font-size-sm/base/lg/xl`.

- [ ] **Step 1: Replace the family and add semantic tokens**

In `assets/css/tokens.css`, replace the Roboto base family and current small scale block with:

```css
--aznet-theme-font-family-base: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
--aznet-theme-font-family-heading: var(--aznet-theme-font-family-base);

--aznet-theme-text-display: clamp(2.75rem, 5vw, 4.5rem);
--aznet-theme-text-h1: 2.5rem;
--aznet-theme-text-h2: 2rem;
--aznet-theme-text-h3: 1.5rem;
--aznet-theme-text-h4: 1.25rem;
--aznet-theme-text-lead: 1.125rem;
--aznet-theme-text-body: 1rem;
--aznet-theme-text-small: 0.9375rem;
--aznet-theme-text-navigation: 0.9375rem;
--aznet-theme-text-button: 0.9375rem;
--aznet-theme-text-label: 0.8125rem;
--aznet-theme-text-meta: 0.875rem;
--aznet-theme-text-caption: 0.8125rem;
--aznet-theme-text-form: 1rem;
--aznet-theme-text-price: clamp(1.25rem, 2vw, 1.5rem);

--aznet-theme-font-weight-regular: 400;
--aznet-theme-font-weight-medium: 500;
--aznet-theme-font-weight-semibold: 600;
--aznet-theme-font-weight-bold: 700;

--aznet-theme-line-height-display: 1.08;
--aznet-theme-line-height-h1: 1.15;
--aznet-theme-line-height-h2: 1.2;
--aznet-theme-line-height-h3: 1.3;
--aznet-theme-line-height-h4: 1.35;
--aznet-theme-line-height-lead: 1.7;
--aznet-theme-line-height-body: 1.65;
--aznet-theme-line-height-navigation: 1.35;
--aznet-theme-line-height-button: 1.2;
--aznet-theme-line-height-label: 1.3;
--aznet-theme-line-height-meta: 1.45;
--aznet-theme-line-height-caption: 1.45;
--aznet-theme-line-height-form: 1.4;
--aznet-theme-line-height-price: 1.2;

/* Compatibility aliases retained for existing consumers. */
--aznet-theme-font-size-sm: var(--aznet-theme-text-meta);
--aznet-theme-font-size-base: var(--aznet-theme-text-body);
--aznet-theme-font-size-lg: var(--aznet-theme-text-lead);
--aznet-theme-font-size-xl: var(--aznet-theme-text-h3);
--aznet-theme-line-height-heading: var(--aznet-theme-line-height-h2);
```

Add mobile heading overrides in the existing responsive area or a new token-level media query:

```css
@media (max-width: 48rem) {
    :root {
        --aznet-theme-text-h1: 2rem;
        --aznet-theme-text-h2: 1.625rem;
        --aznet-theme-text-h3: 1.25rem;
        --aznet-theme-text-h4: 1.125rem;
        --aznet-theme-text-lead: 1.0625rem;
        --aznet-theme-text-navigation: 1rem;
        --aznet-theme-text-price: clamp(1.125rem, 5vw, 1.375rem);
    }
}
```

- [ ] **Step 2: Replace theme.json Roboto preset with system preset**

Use this typography family entry:

```json
"fontFamilies": [
  {
    "slug": "system",
    "name": "System UI",
    "fontFamily": "system-ui, -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif"
  }
]
```

And set:

```json
"fontFamily": "var:preset|font-family|system"
```

Keep existing WordPress size presets compatible by pointing them at the retained CSS aliases.

- [ ] **Step 3: Remove obsolete font binaries**

Run:

```bash
git rm assets/fonts/roboto/Roboto-Regular.woff2
git rm assets/fonts/roboto/Roboto-Medium.woff2
git rm assets/fonts/roboto/Roboto-Bold.woff2
```

If `assets/fonts/roboto/` becomes empty, remove the directory from the working tree.

- [ ] **Step 4: Run the system architecture contract**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-system-contract.php
```

Expected: PASS.

- [ ] **Step 5: Confirm cache-busting regression still passes**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-token-cache-contract.php
```

Expected: PASS.

- [ ] **Step 6: Commit primitives**

```bash
git add assets/css/tokens.css theme.json
git add -u assets/fonts/roboto
git commit -m "feat: add system typography primitives"
```

---

### Task 3: Migrate shell, native content, and form surfaces to semantic roles

**Files:**
- Modify: `assets/css/components/site-header.css`
- Modify: `assets/css/components/header-utility.css`
- Modify: `assets/css/components/site-footer.css`
- Modify: `assets/css/components/generic-content.css`
- Modify: `assets/css/components/page.css`
- Modify: `assets/css/components/article.css`
- Modify: `assets/css/components/comments.css`
- Modify: `assets/css/components/media.css`
- Modify: `assets/css/components/navigation.css`
- Modify: `assets/css/components/recovery.css`
- Modify: `assets/css/components/forms.css`
- Modify: `assets/css/components/contact-surface.css`
- Modify: `assets/css/components/profile-surface.css`

**Interfaces:**
- Consumes semantic tokens from Task 2.
- Produces Theme-wide role mapping for shell/native surfaces without changing markup or data flow.

- [ ] **Step 1: Convert Header navigation and utilities**

For the desktop menu in `site-header.css`, replace literal typography with:

```css
font-size: var(--aznet-theme-text-navigation);
font-weight: var(--aznet-theme-font-weight-semibold);
line-height: var(--aznet-theme-line-height-navigation);
```

For header utility text use Metadata:

```css
font-size: var(--aznet-theme-text-meta);
font-weight: var(--aznet-theme-font-weight-medium);
line-height: var(--aznet-theme-line-height-meta);
```

For buttons/search controls, use Button/Form role tokens where the selector is an actual action/control.

In `header-utility.css`, replace `.82rem` / `750` with:

```css
font-size: var(--aznet-theme-text-button);
font-weight: var(--aznet-theme-font-weight-semibold);
line-height: var(--aznet-theme-line-height-button);
```

- [ ] **Step 2: Convert Footer role mapping**

In `site-footer.css`:
- brand text → H4;
- footer headings → H4;
- menu links/body copy → Small body;
- copyright/policy/social meta → Metadata.

Example:

```css
.aznet-theme-site-footer__heading {
    font-size: var(--aznet-theme-text-h4);
    font-weight: var(--aznet-theme-font-weight-semibold);
    line-height: var(--aznet-theme-line-height-h4);
}

.aznet-theme-site-footer__copyright,
.aznet-theme-site-footer__policy-menu a,
.aznet-theme-site-footer__social-menu a {
    font-size: var(--aznet-theme-text-meta);
    line-height: var(--aznet-theme-line-height-meta);
}
```

- [ ] **Step 3: Convert Page/Post/card/meta/caption/form roles**

Use the following mapping:
- page/post titles → `--aznet-theme-text-h1`;
- section headings → H2/H3/H4 by actual document hierarchy;
- lede/excerpt → Lead;
- card meta/date/comment meta → Metadata;
- captions → Caption;
- navigation summaries/recovery helper copy → Metadata/Small;
- form controls → Form;
- action labels → Button;
- profile/contact eyebrow → Label;
- profile/contact hero title → Display or H1 according to markup hierarchy.

Do not change HTML heading levels merely to match a visual size.

- [ ] **Step 4: Run hardcode audit**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/typography-hardcode-audit-contract.php
```

Expected: only Homepage/Law01/Woo-related violations may remain for Tasks 4-5; if the contract scans the whole tree, keep RED until those tasks are done but verify no new shell/native violations appear with a targeted grep:

```bash
grep -RInE "font-family:|font-weight: (750|800)|font-size: ([0-9]+px|\.[0-9]+rem)" assets/css/components/site-header.css assets/css/components/header-utility.css assets/css/components/site-footer.css assets/css/components/generic-content.css assets/css/components/page.css assets/css/components/article.css assets/css/components/comments.css assets/css/components/media.css assets/css/components/navigation.css assets/css/components/recovery.css assets/css/components/forms.css assets/css/components/contact-surface.css assets/css/components/profile-surface.css
```

Expected: no unapproved typography family/weight hardcodes; remaining literal sizes must be layout dimensions, not text role sizes.

- [ ] **Step 5: Run retained native contracts**

Run:

```bash
bash scripts/verify-v1-core.sh
```

Expected at this intermediate point: typography hardcode audit may still fail only on explicitly deferred Homepage/Woo files; unrelated native contracts must remain green.

- [ ] **Step 6: Commit shell/native migration**

```bash
git add assets/css/components/site-header.css assets/css/components/header-utility.css assets/css/components/site-footer.css assets/css/components/generic-content.css assets/css/components/page.css assets/css/components/article.css assets/css/components/comments.css assets/css/components/media.css assets/css/components/navigation.css assets/css/components/recovery.css assets/css/components/forms.css assets/css/components/contact-surface.css assets/css/components/profile-surface.css
git commit -m "refactor: map native surfaces to semantic typography"
```

---

### Task 4: Migrate Homepage and Law 01; remove local typography ownership

**Files:**
- Modify: `assets/css/components/homepage.css`
- Modify: `assets/css/components/homepage-law-01.css`
- Modify: `assets/css/components/homepage-law-01-variants.css`
- Modify tests that currently assert literal Law 01 font sizes: `tests/offline/homepage-law-01-premium-presentation-contract.php`, and any client-ready contract that asserts those literals.

**Interfaces:**
- Consumes Display/H1/H2/H3/H4/Lead/Label/Button tokens.
- Produces a Law 01 surface whose typography comes from Theme semantics, while retaining burgundy/gold color/layout identity.

- [ ] **Step 1: Replace generic homepage hero literals**

Map:
- homepage hero H1 → Display;
- hero supporting copy → Lead;
- section titles → H2;
- card titles → H3/H4;
- CTA labels → Button.

Example:

```css
.aznet-theme-homepage__hero h1 {
    font-size: var(--aznet-theme-text-display);
    font-weight: var(--aznet-theme-font-weight-bold);
    line-height: var(--aznet-theme-line-height-display);
}

.aznet-theme-homepage__hero .aznet-theme-homepage__lede {
    font-size: var(--aznet-theme-text-lead);
    line-height: var(--aznet-theme-line-height-lead);
}
```

Use the actual selectors present in the file; do not invent new markup.

- [ ] **Step 2: Make Law 01 consume semantic roles**

Replace the Law 01 heading block with role-specific selectors:

```css
.aznet-theme-homepage--law-01 .aznet-theme-law01-hero h1 {
    font-family: var(--aznet-theme-font-family-heading);
    font-size: var(--aznet-theme-text-display);
    font-weight: var(--aznet-theme-font-weight-bold);
    line-height: var(--aznet-theme-line-height-display);
    letter-spacing: normal;
}

.aznet-theme-homepage--law-01 .aznet-theme-law01-section h2 {
    font-family: var(--aznet-theme-font-family-heading);
    font-size: var(--aznet-theme-text-h2);
    font-weight: var(--aznet-theme-font-weight-bold);
    line-height: var(--aznet-theme-line-height-h2);
    letter-spacing: normal;
}

.aznet-theme-homepage--law-01 .aznet-theme-law01-section h3 {
    font-family: var(--aznet-theme-font-family-heading);
    font-size: var(--aznet-theme-text-h3);
    font-weight: var(--aznet-theme-font-weight-semibold);
    line-height: var(--aznet-theme-line-height-h3);
    letter-spacing: normal;
}
```

Map:
- `.aznet-theme-law01-eyebrow` → Label;
- `.aznet-theme-law01-lede` → Lead;
- `.aznet-theme-law01-button` → Button;
- article/service/card supporting copy → Body/Small/Metadata by actual function.

- [ ] **Step 3: Remove variant size overrides that only restyle typography**

In `homepage-law-01-variants.css`, remove declarations such as:

```css
font-size: clamp(2.85rem, 5vw, 5rem);
```

when they merely restyle the same hero H1 role. The burgundy/gold variant may continue to change color, background, spacing, border, composition, and imagery.

- [ ] **Step 4: Update Law 01 contracts from literal sizes to semantic tokens**

For example, replace an assertion like:

```php
'font-size: clamp(1.55rem'
```

with a semantic assertion tied to the correct selector:

```php
'font-size: var(--aznet-theme-text-h3);'
```

Do not weaken the test to “contains font-size”.

- [ ] **Step 5: Run Homepage/Law 01 contracts**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-premium-presentation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-client-ready-contract.php
```

Expected: PASS.

- [ ] **Step 6: Commit Homepage/Law 01 migration**

```bash
git add assets/css/components/homepage.css assets/css/components/homepage-law-01.css assets/css/components/homepage-law-01-variants.css tests/offline/homepage-law-01-premium-presentation-contract.php tests/offline/homepage-law-01-client-ready-contract.php tests/offline/homepage-law-01-contract.php
git commit -m "refactor: make homepage consume semantic typography"
```

---

### Task 5: Migrate WooCommerce presentation roles

**Files:**
- Modify: `assets/css/components/woocommerce-archive.css`
- Modify: `assets/css/components/woocommerce-product.css`
- Inspect/migrate: `woocommerce-cart.css`, `woocommerce-checkout.css`, `woocommerce-account.css`, `woocommerce-blocks.css`
- Update Woo static contracts only where they assert old typography literals.

**Interfaces:**
- Consumes H1/H3/H4/Body/Form/Button/Metadata/Price tokens.
- Produces typography-only Woo presentation changes; WooCommerce remains commerce state owner.

- [ ] **Step 1: Map archive/product-card typography**

In `woocommerce-archive.css`:
- product card title → H4 by default, H3 only where editorial hierarchy truly requires it;
- price → Price;
- ordering/select control → Form;
- result count → Metadata/Small;
- button → Button.

Example:

```css
.woocommerce ul.products li.product .woocommerce-loop-product__title {
    font-size: var(--aznet-theme-text-h4);
    font-weight: var(--aznet-theme-font-weight-semibold);
    line-height: var(--aznet-theme-line-height-h4);
}

.woocommerce ul.products li.product .price {
    font-size: var(--aznet-theme-text-price);
    font-weight: var(--aznet-theme-font-weight-bold);
    line-height: var(--aznet-theme-line-height-price);
}
```

- [ ] **Step 2: Map single-product typography**

In `woocommerce-product.css`:
- product title → H1;
- main price → Price;
- related/upsell card title → H4;
- short description → Body/Lead only if the existing semantics support lead emphasis;
- product metadata → Metadata;
- variation controls → Form;
- add-to-cart/reset action labels → Button/Metadata according to role.

- [ ] **Step 3: Inspect cart/checkout/account/blocks**

Run:

```bash
grep -RInE "font-family:|font-size:|font-weight:|line-height:" assets/css/components/woocommerce-cart.css assets/css/components/woocommerce-checkout.css assets/css/components/woocommerce-account.css assets/css/components/woocommerce-blocks.css
```

For each text declaration:
- replace literal role styling with semantic tokens;
- leave non-text dimensions alone;
- do not alter Woo state, hooks, markup ownership, price arithmetic, stock logic, or checkout behavior.

- [ ] **Step 4: Run Woo contracts**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/w3-archive-css-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r4-product-card-catalog-contract.php
bash scripts/verify-v1-core.sh
```

Expected: PASS after all Theme-wide typography files have been migrated.

- [ ] **Step 5: Commit Woo migration**

```bash
git add assets/css/components/woocommerce-*.css tests/offline
git commit -m "refactor: map Woo presentation to typography roles"
```

---

### Task 6: Complete hardcode audit and cross-surface browser verification

**Files:**
- Modify only if failures reveal a real Theme presentation bug.
- Evidence generated by existing workflows; do not hand-edit historical evidence.

**Interfaces:**
- Consumes completed semantic migration.
- Produces fresh L1-L4 confidence before source closure.

- [ ] **Step 1: Run complete static/core chain**

Run:

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS including:
- system typography architecture contract;
- hardcode audit;
- token cache contract;
- all retained V1 contracts.

- [ ] **Step 2: Verify no Roboto binary/reference remains**

Run:

```bash
find assets -type f | grep -i roboto && exit 1 || true
grep -RInE "assets/fonts/roboto|fontFace|Georgia|Times New Roman" theme.json assets/css tests/offline
```

Expected:
- no Roboto asset path;
- no default fontFace block;
- no Georgia/Times family declaration.

A plain word “Roboto” is allowed only inside the approved system fallback stack.

- [ ] **Step 3: Run exact branch PR workflow set**

Push the implementation branch and open/update its PR. Wait for all triggered workflows.

Required named checks include at minimum:
- V1 Core Pull Request CI;
- R1 Design System Static Contracts;
- R1 Design System Browser Parity;
- Homepage Composer Law 01 Browser Quality;
- F Homepage Native Runtime Browser;
- P4 Homepage Blueprint Browser Quality;
- X1-X6 retained workflows;
- Y1-Y5 retained workflows;
- R4 WooCommerce Presentation Browser Quality;
- R6 Performance Baseline.

Expected: all triggered workflows complete SUCCESS.

- [ ] **Step 4: Inspect browser failures by root cause**

If any workflow fails:
- reproduce at the narrowest deterministic layer;
- fix only Theme presentation code;
- rerun the failed job once if failure is infrastructure-only;
- do not suppress axe/overflow assertions to obtain green.

- [ ] **Step 5: Confirm target browser semantics**

At browser layer, verify:
- 1440×900;
- 1024×900;
- 390×844;
- 320×800;
- body/form text remains at least 1rem-equivalent;
- headings follow semantic hierarchy;
- Vietnamese strings such as `Đất đai & Bất động sản`, `Dân sự & Tranh chấp`, `Hôn nhân & Gia đình` render without split combining marks or clipping;
- no default-font network request is required;
- no horizontal overflow;
- no blocking axe violations.

- [ ] **Step 6: Commit any regression-only fixes separately**

Example:

```bash
git add <only files changed by the regression fix>
git commit -m "fix: preserve typography cross-surface regression"
```

Do not mix source-governance closure into this commit.

---

### Task 7: Reconcile authoritative Theme architecture and closure evidence

**Files:**
- Modify: `docs/source/AZT-02-architecture.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Modify only if state/provenance actually changes: `docs/source/AZT-03-baseline-provenance.md`, `docs/source/AZT-04-roadmap-qa-decisions.md`, `docs/source/AZT-EXEC-MAP.md`
- Create: `docs/evidence/SYSTEM_TYPOGRAPHY_V1_20260918.md`

**Interfaces:**
- Consumes exact verified implementation head and workflow evidence.
- Produces durable source/evidence closure without rewriting historical evidence.

- [ ] **Step 1: Update AZT-02 typography architecture**

Add a concise authoritative rule under the semantic design-token section:

```markdown
### System typography contract

AZnet Theme owns a semantic typography system as presentation architecture.

- Default family: `system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif`.
- The Theme does not require a default external or self-hosted webfont.
- Typography is mapped by semantic display role (Display, H1-H4, Lead, Body, Navigation, Button, Label, Metadata, Caption, Form, Price).
- Component/surface CSS consumes semantic Theme tokens and does not become an independent typography owner.
- Existing public typography aliases remain compatible until separately versioned for removal.
```

Bump AZT-02 version according to current source-version discipline.

- [ ] **Step 2: Synchronize SOURCE_MANIFEST**

Update the AZT-02 row to the new version and append the approved System Typography v1 spec/plan/evidence pointers without overwriting historical release facts.

Do not claim a new Theme release/version unless a separate release decision exists.

- [ ] **Step 3: Create exact evidence record**

Create `docs/evidence/SYSTEM_TYPOGRAPHY_V1_20260918.md` with:

```markdown
# System Typography v1 Evidence — 2026-09-18

## Scope
AZnet Theme presentation typography only.

## Ownership
Theme-owned design tokens, theme.json mapping, component presentation and tests.
No RootProfile, ConvertFlow, Nagavia, WooCommerce domain ownership change.

## Exact implementation head
<replace at execution time with the verified exact PR head SHA>

## PASS
- L1 static contracts: system family, semantic tokens, no obsolete default font binaries.
- L2 retained contracts: PASS.
- L3 clean WordPress/runtime checks: PASS where evidenced by the retained workflow set.
- L4 browser/a11y checks: PASS where evidenced by the retained workflow set.
- R6 performance: PASS.

## Retained boundaries
- Theme version remains unchanged unless separately approved.
- Production deployment is not implied by merge.
- RootProfile Team blocker #112 remains independent.

## Rollback
Revert the bounded typography merge commit or restore the previous verified Theme package.
```

During execution, replace the exact SHA placeholder before commit; the final evidence file must contain no placeholder.

- [ ] **Step 4: Re-run source-sensitive verification**

Run the source/version checks already retained by the repository, then:

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS.

- [ ] **Step 5: Commit source/evidence closure**

```bash
git add docs/source/AZT-02-architecture.md docs/source/SOURCE_MANIFEST.md docs/source/AZT-03-baseline-provenance.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md docs/evidence/SYSTEM_TYPOGRAPHY_V1_20260918.md
git commit -m "docs: close system typography v1 evidence"
```

Only add AZT-03/AZT-04/AZT-EXEC-MAP if they were actually changed.

---

### Task 8: Final exact-head verification and merge handoff

**Files:**
- No production changes unless verification exposes a real regression.

**Interfaces:**
- Consumes final implementation/source head.
- Produces a merge-ready PR checkpoint; merge remains user approval-gated.

- [ ] **Step 1: Push final head and wait for all PR workflows**

Record the exact head SHA.

Expected: every triggered workflow completed SUCCESS.

- [ ] **Step 2: Verify no package contains obsolete default font binaries**

Use the Y5/packaging artifact or equivalent retained package workflow and inspect the ZIP contents.

Expected:
- no `assets/fonts/roboto/*.woff2`;
- `theme.json` has system family and no fontFace;
- semantic tokens are present;
- Theme metadata version is unchanged unless separately approved.

- [ ] **Step 3: Compare package/source provenance**

Confirm the package was built from the exact PR head and record its SHA-256 in evidence/checkpoint output.

- [ ] **Step 4: Present merge gate**

Report:

```text
PASS — System Typography v1 implementation, L1-L4 + retained regression at exact PR head.
BLOCKED/UNKNOWN — production deployment not yet performed.
EVIDENCE — PR, exact head SHA, workflow count, package artifact/SHA-256, evidence file.
NEXT — owner approval to merge the PR.
```

Do not merge without explicit user approval.

- [ ] **Step 5: After merge, stop at deployment gate**

After an approved merge:
- verify merge tree equals tested head tree;
- if exact-main workflows exist, run/wait for them;
- prepare the verified package;
- ask for explicit production deployment approval before replacing the live Theme.

Production deployment is not part of this plan's automatic execution.

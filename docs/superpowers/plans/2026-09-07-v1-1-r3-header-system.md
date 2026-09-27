# AZnet Theme v1.1 R3 Header System 2.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the single fixed Header composition with four bounded, accessible presentation presets while preserving WordPress/Woo data ownership and no-builder architecture.

**Architecture:** Split Header responsibilities into settings/preset resolution, reusable primitive template parts, one composer and small progressive-enhancement scripts. Presets change composition/visibility only; data still comes from WordPress custom logo/menu/search and public Woo functions. The `overlay` request is deliberately fail-safe: it is effective only on the WordPress front-page surface; every other route resolves to `standard`, and the front-page overlay itself keeps a contrast-safe translucent surface instead of assuming a specific Hero implementation.

**Tech Stack:** PHP 8.1+, WordPress 6.9+, PHP template parts, CSS, vanilla JS, Theme Mod settings interface from R1, WP-CLI, Playwright/axe.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-1-native-product-system-design.md`

## Global Constraints

- R1 settings/token interfaces must be merged first.
- Requested presets: `standard`, `compact`, `commerce`, `overlay`; effective `overlay` is limited to `is_front_page()` and otherwise degrades to `standard`.
- Sticky modes: `off`, `sticky`, `sticky-compact`.
- No drag/drop Header Builder, mega-menu builder or public extension hook without a real consumer/source decision.
- Mobile navigation must remain usable if JS fails.
- Woo account/cart actions use public functions only; no cart/session storage reads.
- No slug/title/Page-ID/URL heuristic is used to decide Header preset behavior.

---

### Task 1: Extend Theme settings for Header presets

**Files:**
- Modify: `inc/theme/settings.php`
- Create: `tests/offline/r3-header-settings-contract.php`

**Interfaces:**
- Adds normalized keys:
  - `header_preset`: `standard|compact|commerce|overlay`
  - `header_sticky`: `off|sticky|sticky-compact`
  - `header_search`: boolean
  - `header_utilities`: boolean

- [ ] **Step 1: Write RED**

Assert defaults:
```php
$defaults = \AZnet\Theme\settings_defaults();
assert($defaults['header_preset'] === 'standard');
assert($defaults['header_sticky'] === 'sticky');
assert($defaults['header_search'] === true);
assert($defaults['header_utilities'] === true);
```
Assert invalid enum values normalize to defaults and string booleans are not accepted as truthy unless explicitly normalized.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r3-header-settings-contract.php
```

- [ ] **Step 3: Add keys to the existing allow-list normalizer**

Keep `schema_version` stable for additive compatible keys. Use strict enum lists and `(bool)` only on actual bool/int `0|1` values.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/r3-header-settings-contract.php
php tests/offline/r1-settings-contract.php
```

- [ ] **Step 5: Commit**

```bash
git add inc/theme/settings.php tests/offline/r3-header-settings-contract.php
git commit -m "feat: add header presentation settings"
```

---

### Task 2: Introduce Header preset resolver and reusable primitives

**Files:**
- Create: `inc/theme/header.php`
- Modify: `inc/theme/bootstrap.php`
- Create: `template-parts/header/brand.php`
- Create: `template-parts/header/primary-navigation.php`
- Create: `template-parts/header/search.php`
- Create: `template-parts/header/commerce-actions.php`
- Create: `template-parts/header/mobile-panel.php`
- Modify: `template-parts/header/site-header.php`
- Create: `tests/offline/r3-header-preset-contract.php`

**Interfaces:**
- Produces:
  - `header_preset(): string` — normalized requested preset from Theme settings.
  - `effective_header_preset(): string` — requested preset after safe surface fallback.
  - `header_sticky_mode(): string`
  - `header_context(): array`
  - primitive parts that consume context, not provider storage.

- [ ] **Step 1: Write RED preset/context contract**

Stub WordPress/Woo functions and assert requested/effective behavior:
```php
assert(\AZnet\Theme\header_preset() === 'overlay');
$GLOBALS['r3_is_front_page'] = false;
assert(\AZnet\Theme\effective_header_preset() === 'standard');
$GLOBALS['r3_is_front_page'] = true;
assert(\AZnet\Theme\effective_header_preset() === 'overlay');

$context = \AZnet\Theme\header_context();
assert(array_key_exists('site_title', $context));
assert(array_key_exists('logo_id', $context));
assert(array_key_exists('primary_menu', $context));
assert(array_key_exists('account_url', $context));
assert(array_key_exists('cart_url', $context));
```
When Woo functions are absent, account/cart URLs must be empty strings and no error occurs.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r3-header-preset-contract.php
```

- [ ] **Step 3: Implement requested/effective resolver and context**

Use:
```php
function header_preset(): string {
    $value = setting('header_preset', 'standard');
    return in_array($value, ['standard', 'compact', 'commerce', 'overlay'], true) ? $value : 'standard';
}

function effective_header_preset(): string {
    $requested = header_preset();
    if ('overlay' === $requested && !(function_exists('is_front_page') && \is_front_page())) {
        return 'standard';
    }
    return $requested;
}
```
The context function may call public WordPress functions and `wc_get_page_permalink()/wc_get_cart_url()` when available. No global cart/session reads and no URL/slug/Page-ID inference.

- [ ] **Step 4: Split primitive markup**

Each part receives `$args` from `get_template_part()` and owns one role only. Example brand primitive:
```php
<a class="aznet-theme-site-header__brand" href="<?php echo esc_url($args['home_url']); ?>" rel="home" aria-label="<?php echo esc_attr($args['site_title']); ?>">
    <?php if ('' !== $args['logo_html']) : ?>
        <?php echo $args['logo_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment HTML. ?>
    <?php else : ?>
        <?php echo esc_html($args['site_title']); ?>
    <?php endif; ?>
</a>
```

- [ ] **Step 5: Convert `site-header.php` into one composer**

Render one `<header>` with classes derived from the **effective** preset:
```text
aznet-theme-site-header
aznet-theme-site-header--{effective-preset}
aznet-theme-site-header--{sticky-mode}
```
Compose primitives according to preset using conditionals, not duplicated full header templates.

- [ ] **Step 6: Run GREEN + retained core contracts**

```bash
php tests/offline/r3-header-preset-contract.php
bash scripts/verify-g3-core.sh
```

- [ ] **Step 7: Commit**

```bash
git add inc/theme/header.php inc/theme/bootstrap.php template-parts/header tests/offline/r3-header-preset-contract.php
git commit -m "feat: compose header from reusable presets"
```

---

### Task 3: Implement preset styling and no-JS fallback

**Files:**
- Modify: `assets/css/components/site-header.css`
- Create: `tests/offline/r3-header-css-contract.php`

**Interfaces:**
- Consumes effective Header classes from Task 2.
- Produces responsive CSS for Standard/Compact/Commerce/Overlay.

- [ ] **Step 1: Write RED CSS contract**

Assert selectors exist for all four presets, mobile layout has a usable fallback before JS enhancement, and the overlay style contains an explicit contrast-safe background rather than pure transparency. Reject fixed pixel widths that force horizontal overflow for nav labels.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r3-header-css-contract.php
```

- [ ] **Step 3: Implement preset CSS using semantic tokens**

Use token-driven rules such as:
```css
.aznet-theme-site-header--compact {
    --aznet-theme-header-current-height: var(--aznet-theme-header-height-compact);
}
.aznet-theme-site-header--commerce .aznet-theme-site-header__search {
    flex: 1 1 24rem;
}
.aznet-theme-site-header--overlay {
    background: color-mix(in srgb, var(--aznet-theme-color-surface-inverse) 72%, transparent);
    color: var(--aznet-theme-color-on-inverse);
    backdrop-filter: blur(0.5rem);
}
```
If `color-mix()` or `backdrop-filter` is unsupported, the preceding/fallback declaration must remain readable; do not rely on transparent text over unknown media.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/r3-header-css-contract.php
```

- [ ] **Step 5: Commit**

```bash
git add assets/css/components/site-header.css tests/offline/r3-header-css-contract.php
git commit -m "feat: style header presentation presets"
```

---

### Task 4: Add accessible mobile navigation progressive enhancement

**Files:**
- Create: `assets/js/header-navigation.js`
- Modify: `inc/theme/assets.php`
- Modify: `template-parts/header/mobile-panel.php`
- Create: `tests/offline/r3-header-navigation-asset-contract.php`

**Interfaces:**
- JS consumes `[data-aznet-theme-nav-trigger]` and `[data-aznet-theme-nav-panel]`.
- JS provides open/close, Escape, focus containment and focus return.

- [ ] **Step 1: Write RED asset contract**

Assert JS is enqueued only when mobile panel markup is enabled and no JS framework dependency is declared.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r3-header-navigation-asset-contract.php
```

- [ ] **Step 3: Implement semantic fallback markup**

Use a real button trigger and a panel that remains reachable without JS. JS may add `hidden`/state attributes only after initialization. Do not ship markup where all navigation is hidden by default solely by JS.

- [ ] **Step 4: Implement focus behavior**

Core shape:
```js
const focusable = panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
// Escape closes; Tab wraps only while modal-like panel is open; close returns focus to trigger.
```
Use `aria-expanded`, `aria-controls`, and body scroll locking that is removed on close/pagehide.

- [ ] **Step 5: Run GREEN**

```bash
php tests/offline/r3-header-navigation-asset-contract.php
```

- [ ] **Step 6: Commit**

```bash
git add assets/js/header-navigation.js inc/theme/assets.php template-parts/header/mobile-panel.php tests/offline/r3-header-navigation-asset-contract.php
git commit -m "feat: add accessible mobile navigation"
```

---

### Task 5: Add sticky-compact enhancement without layout-shift dependency

**Files:**
- Create: `assets/js/sticky-header.js`
- Modify: `inc/theme/assets.php`
- Modify: `assets/css/components/site-header.css`
- Create: `tests/offline/r3-sticky-header-contract.php`

**Interfaces:**
- Only runs for `header_sticky === 'sticky-compact'`.
- Toggles `is-aznet-theme-header-compact` based on bounded scroll threshold.

- [ ] **Step 1: Write RED**

Assert no sticky JS is enqueued for `off` or ordinary `sticky`, and it has no dependency array entries.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r3-sticky-header-contract.php
```

- [ ] **Step 3: Implement minimal enhancement**

Use passive scroll plus `requestAnimationFrame`; CSS transitions must be disabled under `prefers-reduced-motion: reduce`. Header remains sticky/usable if JS fails.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/r3-sticky-header-contract.php
```

- [ ] **Step 5: Commit**

```bash
git add assets/js/sticky-header.js inc/theme/assets.php assets/css/components/site-header.css tests/offline/r3-sticky-header-contract.php
git commit -m "feat: add sticky compact header enhancement"
```

---

### Task 6: Browser/a11y matrix for all Header presets

**Files:**
- Create: `tests/browser/r3-header-l4.mjs`
- Create: `.github/workflows/r3-header-browser.yml`
- Create: `docs/evidence/R3_HEADER_SYSTEM_L4.md`

**Interfaces:**
- Consumes final R3 candidate.
- Produces fresh L3/L4 evidence.

- [ ] **Step 1: Create fixtures**

Test each requested preset with long site title, depth-2 menu, search enabled, and with Woo absent/present for Commerce. Include both front-page and non-front-page routes while the stored preset is `overlay`.

- [ ] **Step 2: Test viewports**

Use 1440x1000, 1024x900, 390x844 and an additional 320x800 edge case. Assert horizontal overflow <=1px.

- [ ] **Step 3: Keyboard tests**

Verify skip link, desktop navigation, mobile trigger, Escape close, focus containment/return and visible focus. Disable JS and verify primary navigation remains reachable in fallback form.

- [ ] **Step 4: A11y/runtime tests**

Axe critical/serious = 0 for Theme-controlled header markup; exactly one banner landmark; no duplicate IDs; no unexpected console/page errors.

- [ ] **Step 5: Sticky/overlay behavior**

Check compact transition does not cause meaningful CLS in the harness; reduced-motion removes animation. With stored `overlay`, front page uses the contrast-safe overlay class while Page/Post routes resolve to `standard`. No route/slug/title heuristic is used.

- [ ] **Step 6: Commit evidence**

```bash
git add tests/browser/r3-header-l4.mjs .github/workflows/r3-header-browser.yml docs/evidence/R3_HEADER_SYSTEM_L4.md
git commit -m "test: verify header preset accessibility"
```

**Exit:** R3 L1-L4 PASS; Header flexibility increases without a builder or new data owner.

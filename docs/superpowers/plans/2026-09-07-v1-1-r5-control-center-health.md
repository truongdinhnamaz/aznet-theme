# AZnet Theme v1.1 R5 Control Center and System Health Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Provide a small, presentation-only AZnet Theme Control Center with Quick Setup, safe settings save/reset/import/export, and read-only System Health diagnostics.

**Architecture:** Reuse the single `aznet_theme_settings` Theme Mod schema established by R1 and extended by R3/R4. Add admin-only modules under `inc/admin/`, use native WordPress capability/nonce/admin-post flows, link to WordPress-native Logo/Menu/editor surfaces instead of cloning them, and detect optional providers only through public capabilities.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, WordPress admin APIs, Theme Mods, JSON import/export, screen-scoped CSS, minimal/no admin JS, WP-CLI, Playwright/axe.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-1-native-product-system-design.md`

## Global Constraints

- R1 settings module and final R3/R4 setting keys must be merged before R5.
- Only users with `edit_theme_options` may mutate Theme settings.
- No Page/Menu/Product/Profile/Journey creation.
- Logo/Menu remain native WordPress data; Control Center links to native controls.
- System Health is diagnostic only and cannot write provider state.
- Import/export contains only normalized Theme presentation settings; no secrets/private provider data.
- Historical PR #16/#22 may be read for provenance/lessons but must not be wholesale-merged.

---

### Task 1: Add isolated admin bootstrap and Control Center shell

**Files:**
- Create: `inc/admin/bootstrap.php`
- Create: `inc/admin/control-center.php`
- Modify: `inc/theme/bootstrap.php`
- Create: `assets/css/admin/control-center.css`
- Create: `tests/offline/r5-admin-bootstrap-contract.php`

**Interfaces:**
- Produces top-level admin page slug `aznet-theme`.
- Admin CSS enqueues only on that screen.

- [ ] **Step 1: Write RED bootstrap contract**

Stub `is_admin()`, `add_menu_page()`, `wp_enqueue_style()` and assert:
```text
frontend request -> admin modules do not register menu/assets
admin request -> one AZnet Theme menu page registered
other admin screen -> control-center.css not enqueued
AZnet Theme screen -> control-center.css enqueued
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r5-admin-bootstrap-contract.php
```

- [ ] **Step 3: Implement admin bootstrap**

`inc/theme/bootstrap.php` should require `inc/admin/bootstrap.php`; the module itself must register hooks only when `is_admin()` is true. Use `add_menu_page()` with capability `edit_theme_options` and slug `aznet-theme`.

- [ ] **Step 4: Implement screen-scoped CSS**

Use `$hook_suffix` returned by `add_menu_page()` and enqueue only when it matches the Control Center screen.

- [ ] **Step 5: Run GREEN**

```bash
php tests/offline/r5-admin-bootstrap-contract.php
bash scripts/verify-g3-core.sh
```

- [ ] **Step 6: Commit**

```bash
git add inc/admin/bootstrap.php inc/admin/control-center.php inc/theme/bootstrap.php assets/css/admin/control-center.css tests/offline/r5-admin-bootstrap-contract.php
git commit -m "feat: add isolated AZnet Theme control center shell"
```

---

### Task 2: Render Overview / Design / Header / Commerce / System Health sections

**Files:**
- Modify: `inc/admin/control-center.php`
- Create: `inc/admin/system-health.php`
- Create: `tests/offline/r5-control-center-sections-contract.php`

**Interfaces:**
- Sections:
  - `overview`
  - `design`
  - `header`
  - `commerce` only when public Woo capability exists
  - `system-health`

- [ ] **Step 1: Write RED section contract**

Assert the section allow-list is fixed and unknown `section` query values normalize to `overview`. With Woo absent, `commerce` navigation is omitted; with public Woo available, it appears.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r5-control-center-sections-contract.php
```

- [ ] **Step 3: Implement section resolver**

Use:
```php
function control_center_section(): string {
    $allowed = ['overview', 'design', 'header', 'commerce', 'system-health'];
    $value = isset($_GET['section']) ? sanitize_key(wp_unslash($_GET['section'])) : 'overview';
    return in_array($value, $allowed, true) ? $value : 'overview';
}
```
Commerce rendering must also check `Integrations\\WooCommerce\\available()`.

- [ ] **Step 4: Render fields from the single settings schema**

Design renders visual preset. Header renders preset/sticky/search/utilities. Commerce renders catalog/card/product/sticky-summary settings only when Woo is available. Do not duplicate defaults in admin code; read `settings_defaults()`.

- [ ] **Step 5: Add Overview native-action links**

Use WordPress admin URLs for:
```text
custom logo / Site Identity
Menus
Patterns/editor entry point where supported
```
Do not implement parallel logo/menu editors.

- [ ] **Step 6: Run GREEN**

```bash
php tests/offline/r5-control-center-sections-contract.php
```

- [ ] **Step 7: Commit**

```bash
git add inc/admin/control-center.php inc/admin/system-health.php tests/offline/r5-control-center-sections-contract.php
git commit -m "feat: add control center presentation sections"
```

---

### Task 3: Add nonce/capability-protected save and explicit reset

**Files:**
- Create: `inc/admin/settings-actions.php`
- Modify: `inc/admin/bootstrap.php`
- Modify: `inc/admin/control-center.php`
- Create: `tests/offline/r5-settings-actions-contract.php`

**Interfaces:**
- `admin_post_aznet_theme_save_settings`
- `admin_post_aznet_theme_reset_settings`
- Both call the canonical `normalize_settings()` before persistence.

- [ ] **Step 1: Write RED security contract**

Assert missing capability, bad nonce and invalid payload cannot call `set_theme_mod()`. Assert valid save writes exactly the normalized allow-listed array. Assert reset requires separate nonce plus explicit `confirm_reset=1`.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r5-settings-actions-contract.php
```

- [ ] **Step 3: Implement save handler**

Core shape:
```php
function handle_save_settings(): void {
    if (!current_user_can('edit_theme_options')) {
        wp_die(esc_html__('Bạn không có quyền thay đổi thiết lập Theme.', 'aznet-theme'));
    }
    check_admin_referer('aznet_theme_save_settings');
    $raw = isset($_POST['aznet_theme_settings']) && is_array($_POST['aznet_theme_settings'])
        ? wp_unslash($_POST['aznet_theme_settings'])
        : [];
    set_theme_mod('aznet_theme_settings', normalize_settings($raw));
    wp_safe_redirect(add_query_arg(['page' => 'aznet-theme', 'updated' => '1'], admin_url('admin.php')));
    exit;
}
```

- [ ] **Step 4: Implement destructive reset safely**

Require `check_admin_referer('aznet_theme_reset_settings')`, capability and explicit confirmation; reset only `aznet_theme_settings`, never WordPress logo/menu/content or provider data.

- [ ] **Step 5: Run GREEN**

```bash
php tests/offline/r5-settings-actions-contract.php
php tests/offline/r1-settings-contract.php
php tests/offline/r3-header-settings-contract.php
php tests/offline/r4-commerce-settings-contract.php
```

- [ ] **Step 6: Commit**

```bash
git add inc/admin/settings-actions.php inc/admin/bootstrap.php inc/admin/control-center.php tests/offline/r5-settings-actions-contract.php
git commit -m "feat: save and reset theme presentation settings safely"
```

---

### Task 4: Add safe JSON export/import for Theme presentation settings only

**Files:**
- Create: `inc/admin/settings-portability.php`
- Modify: `inc/admin/bootstrap.php`
- Modify: `inc/admin/control-center.php`
- Create: `tests/offline/r5-settings-portability-contract.php`

**Interfaces:**
- Export payload shape:
```json
{
  "product": "aznet-theme",
  "schema_version": 1,
  "theme_version": "1.x",
  "settings": {}
}
```
- Import accepts only `product=aznet-theme`, supported schema and normalized `settings`.

- [ ] **Step 1: Write RED export/import contract**

Assert export contains no keys matching:
```text
password
secret
api_key
token
product_data
journey
rootprofile
cart
order
```
Assert import drops unknown setting keys and rejects a different product marker or unsupported schema.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r5-settings-portability-contract.php
```

- [ ] **Step 3: Implement export**

Generate JSON from `settings()` only. Use `wp_json_encode()` and a nonce/capability-protected admin action. Do not export `get_theme_mods()` wholesale.

- [ ] **Step 4: Implement import**

Read uploaded JSON with bounded size, decode to array, validate marker/schema, pass only `payload['settings']` into `normalize_settings()`, then persist via `set_theme_mod()`.

- [ ] **Step 5: Run GREEN**

```bash
php tests/offline/r5-settings-portability-contract.php
```

- [ ] **Step 6: Commit**

```bash
git add inc/admin/settings-portability.php inc/admin/bootstrap.php inc/admin/control-center.php tests/offline/r5-settings-portability-contract.php
git commit -m "feat: add portable theme presentation settings"
```

---

### Task 5: Implement read-only System Health and support snapshot

**Files:**
- Modify: `inc/admin/system-health.php`
- Modify: `inc/admin/control-center.php`
- Create: `tests/offline/r5-system-health-contract.php`

**Interfaces:**
- Produces a normalized report array with groups:
  - `environment`
  - `theme`
  - `configuration`
  - `capabilities`
- Produces sanitized support-snapshot JSON/text.

- [ ] **Step 1: Write RED report contract**

Stub environment and public provider functions. Assert report includes WordPress/PHP/Theme versions, visual/header/commerce presets, logo/menu presence, Woo availability, RootProfile public capability status if callable, and ConvertFlow only if a public capability contract is available. Unknown provider state is `unknown`, not guessed.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r5-system-health-contract.php
```

- [ ] **Step 3: Implement report collection**

Use public APIs only:
```php
get_bloginfo('version');
PHP_VERSION;
wp_get_theme();
has_custom_logo();
has_nav_menu('primary');
Integrations\WooCommerce\available();
```
RootProfile uses its existing public adapter/capability functions. Do not add ConvertFlow private detection; if no public Theme-consumable capability exists, report `unknown/not-detected` and state that limitation.

- [ ] **Step 4: Implement support snapshot**

Return only allow-listed scalar/array values from the normalized report and Theme settings. Never include full `$_SERVER`, database info, salts, credentials, user emails or provider private payloads.

- [ ] **Step 5: Run GREEN + forbidden leakage scan**

```bash
php tests/offline/r5-system-health-contract.php
! grep -R -E 'DB_PASSWORD|AUTH_KEY|SECURE_AUTH|\$_SERVER|wpdb->' inc/admin/system-health.php
```

- [ ] **Step 6: Commit**

```bash
git add inc/admin/system-health.php inc/admin/control-center.php tests/offline/r5-system-health-contract.php
git commit -m "feat: add safe theme system health report"
```

---

### Task 6: Implement Quick Setup as guidance, not a content wizard

**Files:**
- Modify: `inc/admin/control-center.php`
- Create: `tests/offline/r5-quick-setup-contract.php`

**Interfaces:**
- Flow: Style -> Logo -> Menu -> Header preset -> Patterns.
- No content/product/page creation side effects.

- [ ] **Step 1: Write RED no-side-effect contract**

Assert Quick Setup code contains no calls to:
```text
wp_insert_post
wp_update_post
wp_create_nav_menu
wc_create_order
register_post_type
```
Assert steps link to native controls and save only Theme settings.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r5-quick-setup-contract.php
```

- [ ] **Step 3: Implement Overview checklist**

Render five ordered setup cards. Style/Header selections post through the save handler; Logo/Menu/Patterns links navigate to native WordPress screens. User may skip every step.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/r5-quick-setup-contract.php
```

- [ ] **Step 5: Commit**

```bash
git add inc/admin/control-center.php tests/offline/r5-quick-setup-contract.php
git commit -m "feat: add bounded quick setup guidance"
```

---

### Task 7: Authenticated browser/a11y and update continuity gate

**Files:**
- Create: `tests/browser/r5-control-center-l4.mjs`
- Create: `.github/workflows/r5-control-center-browser.yml`
- Create: `docs/evidence/R5_CONTROL_CENTER_L4.md`

**Interfaces:**
- Consumes final R5 candidate.
- Produces L3/L4 + continuity evidence.

- [ ] **Step 1: Test authenticated Control Center**

At 1440x1000 and 1024x768 verify section navigation, focus visibility, save, reset confirmation, export and import. Axe critical/serious = 0 and horizontal overflow <=1px.

- [ ] **Step 2: Verify native action links**

Logo link reaches a visible native `custom_logo` control or its WordPress-6.9 equivalent. Menu link reaches native menu/navigation management. Pattern link reaches the supported native inserter/editor path.

- [ ] **Step 3: Verify settings continuity**

Install prior stable Theme package/commit, set logo/menu/content and `aznet_theme_settings`, update in place to R5 candidate, then assert all native data and normalized Theme settings remain.

- [ ] **Step 4: Verify theme switch safety**

Switch to a stock theme and back. WordPress content/logo/menu and external provider data sentinel remain; only AZnet presentation becomes inactive while switched away.

- [ ] **Step 5: Commit evidence**

```bash
git add tests/browser/r5-control-center-l4.mjs .github/workflows/r5-control-center-browser.yml docs/evidence/R5_CONTROL_CENTER_L4.md
git commit -m "test: verify control center accessibility and continuity"
```

**Exit:** R5 L1-L4/continuity PASS; Control Center is a presentation settings console, not a domain/admin platform.

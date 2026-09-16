# X5 Native Form Controls Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver one shared, production-ready AZnet Theme presentation layer for WordPress-native frontend form controls used by Search, Comments, password-protected content and Core form-like blocks, without changing WordPress submission/validation semantics or skinning plugin-private forms.

**Architecture:** Add one bounded shared stylesheet, `assets/css/components/forms.css`, and a surface-aware Theme enqueue gate for native Search/404/Post/Page surfaces. The shared primitive owns control geometry, typography, labels, focus-visible, disabled and legitimate invalid/notice presentation; existing `comments.css` and `recovery.css` retain only surface-specific layout/spacing overrides and depend on the shared primitive. WordPress remains authoritative for form actions, submitted values, validation, comments/search/password behavior and Core block semantics; no JavaScript form engine or plugin-form contract is introduced.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, plain CSS, Bash/PHP offline contracts, real WordPress 6.9 runtime, Node 22, Playwright 1.55, `@axe-core/playwright` 4.10.2, GitHub Actions/MySQL 8.

**Spec:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`

## Global Constraints

- Product scope is **AZnet Theme only**; do not implement or modify RootProfile, ConvertFlow, WooCommerce or another provider product.
- WordPress owns form submission and validation semantics, Search semantics, comments lifecycle/state, password protection, native block semantics and all submitted data.
- Theme owns presentation only: control geometry, labels/notice hierarchy, typography, borders/surfaces, responsive behavior and keyboard-visible focus.
- Use shared primitives before surface overrides; do not create a monolithic Core Experience framework.
- Do not introduce private plugin selectors/contracts or a generic plugin-form skinning engine.
- Do not read provider options/meta/CPT/tables/classes or infer authoritative state from slug/title/Page ID/URL.
- Do not add a custom validation/submission engine, AJAX form framework, JavaScript form state, external runtime dependency or new build stack.
- Keep WooCommerce presentation on its existing dedicated CSS/contracts; X5 shared controls must not leak onto WooCommerce current surfaces.
- Support floor remains WordPress `6.9+` and PHP `8.1+`.
- Theme runtime metadata remains exactly `1.1.0` through X5. Promotion to `1.2.0` belongs to X6 only.
- Every production behavior change follows RED -> intended failure -> minimal GREEN -> retained regression -> L3 -> L4.
- Provider L5 certification, X6 release closure, publication and production deployment are outside X5 and must not be inferred.

---

## File Structure

### Create

- `assets/css/components/forms.css` — shared WordPress-native frontend control primitive scoped only to known Theme/Core form roots.
- `tests/offline/x5-native-form-controls-contract.php` — L1/L2 ownership, selector, asset-scope and version contract.
- `tests/runtime/x5-native-form-controls.php` — WordPress 6.9 fixture for Search, Comments, password form, Core Search block and Core Categories dropdown.
- `tests/browser/x5-native-form-controls-l4.mjs` — responsive/keyboard/focus/disabled/invalid/overflow/a11y evidence.
- `.github/workflows/x5-native-form-controls.yml` — exact-head X5 L1-L4 workflow.
- `docs/evidence/X5_NATIVE_FORM_CONTROLS_20260916.md` — create only after a verified X5 functional head is GREEN.

### Modify

- `inc/theme/assets.php` — add the X5 surface gate/enqueue and make Comments/Recovery depend on the shared primitive.
- `assets/css/components/comments.css` — remove form-control rules now owned by `forms.css`; retain comments-specific layout/readability.
- `assets/css/components/recovery.css` — remove control/focus rules now owned by `forms.css`; retain Search/404 recovery layout.
- `tests/offline/x1-comments-surface-contract.php` — preserve X1 outcome while moving shared control/focus ownership to `forms.css`.
- `tests/offline/x2-search-404-empty-contract.php` — preserve X2 outcome while moving shared Search control/focus ownership to `forms.css`.
- `tests/offline/r6-asset-scope-contract.php` — update exact Page/Post asset expectations for the intentionally added `aznet-theme-forms` handle.
- `scripts/verify-v1-core.sh` — add the X5 contract after X4.
- `docs/source/AZT-04-roadmap-qa-decisions.md` — only after fresh X5 PASS; advance X5 PASS / X6 NEXT.
- `docs/source/AZT-EXEC-MAP.md` — only after fresh X5 PASS; advance the derived execution checkpoint.
- `docs/source/SOURCE_MANIFEST.md` — only after fresh X5 PASS; align versions/current exact next.

### Explicitly unchanged

- `theme.json` — existing tokens are sufficient; X5 does not need a new editor schema or block-style takeover.
- `search.php`, `404.php`, `comments.php`, `page.php`, `single.php` — current native markup/API boundaries are sufficient for X5; do not change them unless RED evidence proves a Theme-owned accessibility defect that cannot be solved at the shared presentation layer.
- WooCommerce CSS/templates/hooks — remain outside X5.
- Any provider integration source — remains outside X5.

---

### Task 1: Establish the X5 RED ownership and shared-form contract

**Files:**
- Create: `tests/offline/x5-native-form-controls-contract.php`
- Test: `tests/offline/x5-native-form-controls-contract.php`

**Interfaces:**
- Consumes: current `inc/theme/assets.php`, `comments.css`, `recovery.css`, `style.css`, `functions.php` and the approved X5 design boundary.
- Produces: executable requirements for `should_enqueue_form_assets(): bool`, `enqueue_form_assets(?string $version = null): void`, handle `aznet-theme-forms` and `assets/css/components/forms.css`.

- [ ] **Step 1: Create the failing X5 contract before production code**

Create `tests/offline/x5-native-form-controls-contract.php` with this structure:

```php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function x5_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function x5_source(string $relative): string {
    $path = dirname(__DIR__, 2) . '/' . $relative;
    if (! is_file($path)) {
        x5_fail('missing required file: ' . $relative);
    }

    $source = file_get_contents($path);
    if (false === $source) {
        x5_fail('cannot read required file: ' . $relative);
    }

    return $source;
}

$assets    = x5_source('inc/theme/assets.php');
$comments  = x5_source('assets/css/components/comments.css');
$recovery  = x5_source('assets/css/components/recovery.css');
$forms     = x5_source('assets/css/components/forms.css');
$style     = x5_source('style.css');
$functions = x5_source('functions.php');

foreach ([
    'should_enqueue_form_assets',
    'enqueue_form_assets',
    "'aznet-theme-forms'",
    "'/assets/css/components/forms.css'",
    "is_search()",
    "is_404()",
    "is_singular( 'post' )",
    "is_page()",
] as $marker) {
    if (! str_contains($assets, $marker)) {
        x5_fail('X5 asset boundary missing marker: ' . $marker);
    }
}

foreach ([
    '.aznet-theme-comments .comment-form',
    '.aznet-theme-search-header__form .search-form',
    '.aznet-theme-recovery .search-form',
    '.aznet-theme-entry__content .post-password-form',
    '.aznet-theme-entry__content .wp-block-search',
    '.wp-block-categories-dropdown',
    'input:not([type="checkbox"])',
    'input[type="checkbox"]',
    'input[type="radio"]',
    'select',
    'textarea',
    'button',
    ':focus-visible',
    ':disabled',
    '[aria-invalid="true"]',
    'var(--aznet-theme-focus)',
    'var(--aznet-theme-color-danger)',
] as $marker) {
    if (! str_contains($forms, $marker)) {
        x5_fail('forms.css missing shared native-control marker: ' . $marker);
    }
}

foreach ([
    'woocommerce',
    'wc-block',
    'choiceguide_',
    'RootProfile',
    'ConvertFlow',
] as $marker) {
    if (stripos($forms, $marker) !== false) {
        x5_fail('forms.css contains forbidden provider/plugin-form selector marker: ' . $marker);
    }
}

$production = $assets . "\n" . $forms . "\n" . $comments . "\n" . $recovery;
foreach ([
    'new WP_Query',
    'WP_Query(',
    'query_posts(',
    'get_posts(',
    'pre_get_posts',
    '$wpdb',
    'get_post_meta(',
    'wp_redirect(',
    'wp_safe_redirect(',
    'fetch(',
    'XMLHttpRequest',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        x5_fail('forbidden ownership/submission/routing marker: ' . $forbidden);
    }
}

if (! str_contains($functions, "define( 'AZNET_THEME_VERSION', '1.1.0' );")) {
    x5_fail('AZNET_THEME_VERSION must remain 1.1.0 during X5');
}
if (! str_contains($style, 'Version: 1.1.0')) {
    x5_fail('Theme stylesheet version must remain 1.1.0 during X5');
}

echo "PASS: X5 native form controls ownership and presentation contract\n";
```

- [ ] **Step 2: Run the focused contract and prove the intended RED reason**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x5-native-form-controls-contract.php
```

Expected result before any production change:

```text
FAIL: missing required file: assets/css/components/forms.css
```

Do not proceed if the failure is syntax, filesystem corruption, a stale branch or an unrelated retained contract.

- [ ] **Step 3: Record the RED commit**

Run:

```bash
git add tests/offline/x5-native-form-controls-contract.php
git diff --cached --check
git commit -m "test: define X5 native form controls contract"
```

Record the exact RED SHA and failing output for the later evidence document/PR body.

---

### Task 2: Add the minimal shared form primitive and surface-aware asset gate

**Files:**
- Create: `assets/css/components/forms.css`
- Modify: `inc/theme/assets.php`
- Modify: `assets/css/components/comments.css`
- Modify: `assets/css/components/recovery.css`
- Modify: `tests/offline/r6-asset-scope-contract.php`
- Modify: `tests/offline/x1-comments-surface-contract.php`
- Modify: `tests/offline/x2-search-404-empty-contract.php`
- Modify: `scripts/verify-v1-core.sh`
- Test: `tests/offline/x5-native-form-controls-contract.php`

**Interfaces:**
- Produces: `AZnet\Theme\should_enqueue_form_assets(): bool`.
- Produces: `AZnet\Theme\enqueue_form_assets(?string $version = null): void`.
- Produces: frontend style handle `aznet-theme-forms`.
- Consumes: WordPress conditionals only; optional Woo exclusion uses the already-existing public Theme-side current-surface bridge exactly as current Theme asset gates do.
- Downstream: `enqueue_recovery_assets()` and `enqueue_comments_assets()` depend on `aznet-theme-forms` where those surfaces render.

- [ ] **Step 1: Add the shared CSS with known Core/Theme roots only**

Create `assets/css/components/forms.css` with the following minimal architecture. Keep selectors scoped to native Theme/Core roots; do not add bare global `input`, `select`, `textarea`, `button` rules.

```css
:is(
    .aznet-theme-comments .comment-form,
    .aznet-theme-search-header__form .search-form,
    .aznet-theme-recovery .search-form,
    .aznet-theme-entry__content .post-password-form,
    .aznet-theme-entry__content .wp-block-search,
    .aznet-theme-entry__content .wp-block-categories-dropdown,
    .aznet-theme-entry__content .wp-block-archives-dropdown
) {
    color: var(--aznet-theme-text);
}

:is(
    .aznet-theme-comments .comment-form,
    .aznet-theme-search-header__form .search-form,
    .aznet-theme-recovery .search-form,
    .aznet-theme-entry__content .post-password-form,
    .aznet-theme-entry__content .wp-block-search,
    .aznet-theme-entry__content .wp-block-categories-dropdown,
    .aznet-theme-entry__content .wp-block-archives-dropdown
) :where(label:not(.screen-reader-text)) {
    display: block;
    margin-block-end: var(--aznet-theme-space-1);
    color: var(--aznet-theme-text);
    font-weight: 600;
}

:is(
    .aznet-theme-comments .comment-form,
    .aznet-theme-search-header__form .search-form,
    .aznet-theme-recovery .search-form,
    .aznet-theme-entry__content .post-password-form,
    .aznet-theme-entry__content .wp-block-search,
    .aznet-theme-entry__content .wp-block-categories-dropdown,
    .aznet-theme-entry__content .wp-block-archives-dropdown
) :where(
    input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="submit"]):not([type="button"]):not([type="reset"]),
    select,
    textarea
) {
    max-width: 100%;
    min-height: 2.75rem;
    padding: 0.65rem 0.8rem;
    color: var(--aznet-theme-text);
    background: var(--aznet-theme-surface);
    border: 1px solid var(--aznet-theme-border);
    border-radius: var(--aznet-theme-radius-control);
    font: inherit;
    line-height: 1.35;
}

:is(
    .aznet-theme-comments .comment-form,
    .aznet-theme-search-header__form .search-form,
    .aznet-theme-recovery .search-form,
    .aznet-theme-entry__content .post-password-form,
    .aznet-theme-entry__content .wp-block-search,
    .aznet-theme-entry__content .wp-block-categories-dropdown,
    .aznet-theme-entry__content .wp-block-archives-dropdown
) textarea {
    width: 100%;
    min-height: 9rem;
    resize: vertical;
}

:is(
    .aznet-theme-comments .comment-form,
    .aznet-theme-search-header__form .search-form,
    .aznet-theme-recovery .search-form,
    .aznet-theme-entry__content .post-password-form,
    .aznet-theme-entry__content .wp-block-search
) :where(button, input[type="submit"], input[type="button"], input[type="reset"]) {
    min-height: 2.75rem;
    max-width: 100%;
    padding: 0.65rem var(--aznet-theme-space-3);
    color: var(--aznet-theme-surface);
    background: var(--aznet-theme-accent);
    border: 1px solid var(--aznet-theme-accent);
    border-radius: var(--aznet-theme-radius-control);
    font: inherit;
    font-weight: 700;
    cursor: pointer;
}

:is(
    .aznet-theme-comments .comment-form,
    .aznet-theme-entry__content .post-password-form,
    .aznet-theme-entry__content .wp-block-search,
    .aznet-theme-entry__content .wp-block-categories-dropdown,
    .aznet-theme-entry__content .wp-block-archives-dropdown
) :where(input[type="checkbox"], input[type="radio"]) {
    inline-size: 1.125rem;
    block-size: 1.125rem;
    margin: 0 var(--aznet-theme-space-1) 0 0;
    accent-color: var(--aznet-theme-accent);
}

:is(
    .aznet-theme-comments .comment-form,
    .aznet-theme-search-header__form .search-form,
    .aznet-theme-recovery .search-form,
    .aznet-theme-entry__content .post-password-form,
    .aznet-theme-entry__content .wp-block-search,
    .aznet-theme-entry__content .wp-block-categories-dropdown,
    .aznet-theme-entry__content .wp-block-archives-dropdown
) :where(input, select, textarea, button):focus-visible {
    outline: 2px solid var(--aznet-theme-focus);
    outline-offset: 3px;
}

:is(
    .aznet-theme-comments .comment-form,
    .aznet-theme-search-header__form .search-form,
    .aznet-theme-recovery .search-form,
    .aznet-theme-entry__content .post-password-form,
    .aznet-theme-entry__content .wp-block-search,
    .aznet-theme-entry__content .wp-block-categories-dropdown,
    .aznet-theme-entry__content .wp-block-archives-dropdown
) :where(input, select, textarea, button):disabled {
    opacity: 0.58;
    cursor: not-allowed;
}

:is(
    .aznet-theme-comments .comment-form,
    .aznet-theme-search-header__form .search-form,
    .aznet-theme-recovery .search-form,
    .aznet-theme-entry__content .post-password-form,
    .aznet-theme-entry__content .wp-block-search,
    .aznet-theme-entry__content .wp-block-categories-dropdown,
    .aznet-theme-entry__content .wp-block-archives-dropdown
) :where(input, select, textarea)[aria-invalid="true"] {
    border-color: var(--aznet-theme-color-danger);
}

.aznet-theme-comments :where(.comment-notes, .required-field-message, .must-log-in, .logged-in-as) {
    color: var(--aznet-theme-muted);
    font-size: var(--aznet-theme-font-size-sm);
}

@media (max-width: 30rem) {
    :is(
        .aznet-theme-search-header__form .search-form,
        .aznet-theme-recovery .search-form,
        .aznet-theme-entry__content .post-password-form,
        .aznet-theme-entry__content .wp-block-search
    ) :where(input:not([type="checkbox"]):not([type="radio"]), button) {
        max-width: 100%;
    }
}
```

Implementation may compress repeated selector roots with CSS nesting only if the repository already supports it without a build step. Since it currently does not, keep plain standards-based CSS and no preprocessor.

- [ ] **Step 2: Add the exact Theme-side asset gate**

Add these functions to `inc/theme/assets.php` beside the existing X1-X4 gates:

```php
/** Determine whether shared native form presentation can render. */
function should_enqueue_form_assets(): bool {
    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    $is_search = function_exists( 'is_search' ) && is_search();
    $is_404    = function_exists( 'is_404' ) && is_404();
    $is_post   = function_exists( 'is_singular' ) && is_singular( 'post' );
    $is_page   = function_exists( 'is_page' ) && is_page();

    return $is_search || $is_404 || $is_post || $is_page;
}

/** Enqueue shared WordPress-native form presentation only on X5 surfaces. */
function enqueue_form_assets( ?string $version = null ): void {
    if ( ! should_enqueue_form_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-forms',
        get_theme_file_uri( '/assets/css/components/forms.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/forms.css', $version )
    );
}
```

In `enqueue_assets()`, call it after the existing generic-content enqueue and before X4/X3/X2 surface modules:

```php
    enqueue_form_assets( $version );
    enqueue_navigation_assets( $version );
    enqueue_media_assets( $version );
    enqueue_recovery_assets( $version );
```

Update Comments and Recovery dependencies so shared primitives load first:

```php
[ 'aznet-theme-tokens', 'aznet-theme-generic-content', 'aznet-theme-forms' ]
```

Do not make `aznet-theme-forms` a dependency of Woo styles.

- [ ] **Step 3: Move only duplicated control primitives out of surface styles**

In `comments.css`, retain comment hierarchy/avatar/thread/body layout. Remove the rules whose sole responsibility is generic form control sizing/focus, including the current generic `button/input/textarea:focus-visible`, full-width text/email/url/textarea rule and generic textarea sizing once their outcome is provided by `forms.css`.

In `recovery.css`, retain recovery panel/search-row layout and mobile flex behavior. Remove generic control focus and min-height rules now guaranteed by `forms.css`. Keep Search-specific flex basis/width rules such as `.search-field { flex: 1 1 16rem; }` because those are surface layout rather than shared control primitives.

- [ ] **Step 4: Update retained X1/X2 contracts for the intentional ownership move**

In `tests/offline/x1-comments-surface-contract.php`, read `forms.css` in addition to `comments.css` and verify the focus/form markers across the two files rather than requiring `:focus-visible` to remain in `comments.css` itself. Preserve every native comment lifecycle/API assertion.

Use this pattern:

```php
$forms = source_x1_comments($root . '/assets/css/components/forms.css');

foreach ([
    '.aznet-theme-comments',
    '.comment-list',
    '.children',
    '.comment-body',
    '.comment-content',
    '.comment-reply-link',
    '.comment-respond',
    'overflow-wrap',
] as $marker) {
    if (! str_contains($css, $marker)) {
        fail_x1_comments('comments.css missing presentation marker: ' . $marker);
    }
}

foreach (['.aznet-theme-comments .comment-form', ':focus-visible'] as $marker) {
    if (! str_contains($forms, $marker)) {
        fail_x1_comments('forms.css missing retained X1 control marker: ' . $marker);
    }
}
```

In `tests/offline/x2-search-404-empty-contract.php`, load `forms.css`; keep Recovery-layout markers in `recovery.css`, and move the native Search control/focus assertion to `forms.css`:

```php
$forms = x2_source('assets/css/components/forms.css');

foreach ([
    '.aznet-theme-recovery',
    '.aznet-theme-recovery__actions',
] as $marker) {
    if (!str_contains($recovery, $marker)) {
        x2_fail('recovery.css missing presentation marker: ' . $marker);
    }
}

foreach ([
    '.aznet-theme-recovery .search-form',
    '.aznet-theme-search-header__form .search-form',
    ':focus-visible',
] as $marker) {
    if (!str_contains($forms, $marker)) {
        x2_fail('forms.css missing retained X2 control marker: ' . $marker);
    }
}
```

Do not weaken X1/X2 ownership, native API, routing, query or provider-takeover prohibitions.

- [ ] **Step 5: Update the exact R6 asset matrix**

In `tests/offline/r6-asset-scope-contract.php`, add the new handle only to native Page/Post cases:

```php
'clean-page-default' => [
    ['generic' => true, 'is_page' => true],
    [...$coreStyles, 'aznet-theme-generic-content', 'aznet-theme-forms', 'aznet-theme-media'],
    [],
],
'clean-post-editorial' => [
    ['generic' => true, 'preset' => 'editorial', 'is_post' => true],
    [...$coreStyles, 'aznet-theme-generic-content', 'aznet-theme-preset-editorial', 'aznet-theme-forms', 'aznet-theme-navigation', 'aznet-theme-media', 'aznet-theme-article'],
    [],
],
```

Leave all Woo cases unchanged to prove X5 does not bleed into Woo current surfaces.

- [ ] **Step 6: Add X5 to the reusable core verifier**

Immediately after X4 in `scripts/verify-v1-core.sh`, add:

```bash
printf '%s\n' '==> X5 Native Form Controls contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x5-native-form-controls-contract.php
```

- [ ] **Step 7: Run focused GREEN and retained shallow regressions**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x5-native-form-controls-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x1-comments-surface-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x2-search-404-empty-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r6-asset-scope-contract.php
bash scripts/verify-v1-core.sh
```

Expected: all commands PASS; reusable verifier still reports Theme metadata `1.1.0`.

If a retained contract fails, reduce the failure to the shallowest stable cause before changing production behavior. Do not suppress retained checks merely to obtain GREEN.

- [ ] **Step 8: Commit minimal GREEN**

Run:

```bash
git add assets/css/components/forms.css inc/theme/assets.php \
  assets/css/components/comments.css assets/css/components/recovery.css \
  tests/offline/x1-comments-surface-contract.php \
  tests/offline/x2-search-404-empty-contract.php \
  tests/offline/r6-asset-scope-contract.php \
  scripts/verify-v1-core.sh
git diff --cached --check
git commit -m "feat: add shared native form controls"
```

---

### Task 3: Add a real WordPress 6.9 X5 runtime fixture and asset-boundary proof

**Files:**
- Create: `tests/runtime/x5-native-form-controls.php`
- Test: real WordPress 6.9 with zero active plugins.

**Interfaces:**
- Produces JSON keys: `comment_post_url`, `native_blocks_url`, `protected_page_url`, `search_url`, `not_found_url`, `archive_url`, `home_url`, `post_id`, `page_id`, `protected_page_id`, `category_id`.
- Uses WordPress public APIs only in test setup.

- [ ] **Step 1: Create native fixture content**

Create `tests/runtime/x5-native-form-controls.php`:

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

update_option( 'comment_registration', 0 );
update_option( 'require_name_email', 1 );
update_option( 'show_comments_cookies_opt_in', 1 );

$category = wp_insert_term( 'X5 Native Controls Category', 'category' );
if ( is_wp_error( $category ) ) {
    WP_CLI::error( 'Unable to create X5 category fixture: ' . $category->get_error_message() );
}
$category_id = (int) $category['term_id'];

$post_id = wp_insert_post(
    [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'post_title'     => 'X5 Native Comment Form',
        'post_content'   => '<p id="x5-comment-content">Native Post for the WordPress comment form.</p>',
        'post_category'  => [ $category_id ],
        'comment_status' => 'open',
    ],
    true
);
if ( is_wp_error( $post_id ) || 0 >= (int) $post_id ) {
    WP_CLI::error( 'Unable to create X5 comment Post.' );
}

$native_blocks = <<<'HTML'
<!-- wp:paragraph -->
<p id="x5-block-controls">Native Core form-like block controls.</p>
<!-- /wp:paragraph -->
<!-- wp:search {"label":"Search the site","showLabel":true,"buttonText":"Search"} /-->
<!-- wp:categories {"displayAsDropdown":true} /-->
HTML;

$page_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'X5 Native Block Controls',
        'post_content' => $native_blocks,
    ],
    true
);
if ( is_wp_error( $page_id ) || 0 >= (int) $page_id ) {
    WP_CLI::error( 'Unable to create X5 native-block Page.' );
}

$protected_page_id = wp_insert_post(
    [
        'post_type'     => 'page',
        'post_status'   => 'publish',
        'post_title'    => 'X5 Password Form',
        'post_content'  => '<p>Protected X5 content.</p>',
        'post_password' => 'x5-native-form',
    ],
    true
);
if ( is_wp_error( $protected_page_id ) || 0 >= (int) $protected_page_id ) {
    WP_CLI::error( 'Unable to create X5 protected Page.' );
}

$post_url      = get_permalink( (int) $post_id );
$page_url      = get_permalink( (int) $page_id );
$protected_url = get_permalink( (int) $protected_page_id );
$archive_url   = get_category_link( $category_id );
$search_url    = get_search_link( 'X5 Native' );

if ( ! is_string( $post_url ) || ! is_string( $page_url ) || ! is_string( $protected_url )
    || ! is_string( $search_url ) || is_wp_error( $archive_url ) ) {
    WP_CLI::error( 'Unable to resolve X5 fixture URLs.' );
}

$result = [
    'comment_post_url'   => $post_url,
    'native_blocks_url'  => $page_url,
    'protected_page_url' => $protected_url,
    'search_url'         => $search_url,
    'not_found_url'      => home_url( '/?p=999999999' ),
    'archive_url'        => $archive_url,
    'home_url'           => home_url( '/' ),
    'post_id'            => (int) $post_id,
    'page_id'            => (int) $page_id,
    'protected_page_id'  => (int) $protected_page_id,
    'category_id'        => $category_id,
];

echo wp_json_encode( $result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
```

The Core Categories dropdown is intentionally chosen to exercise a real WordPress-generated `<select>` without inventing a Theme or plugin form schema.

- [ ] **Step 2: Define runtime route/asset assertions**

The X5 workflow must request these routes and assert:

```text
comment_post_url   -> HTTP 200, aznet-theme-forms-css present, aznet-theme-comments-css present
native_blocks_url  -> HTTP 200, aznet-theme-forms-css present, Core Search input/button + Categories select present
protected_page_url -> HTTP 200, aznet-theme-forms-css present, .post-password-form present
search_url         -> HTTP 200, aznet-theme-forms-css present, native .search-form present
not_found_url      -> HTTP 404, aznet-theme-forms-css present, recovery native .search-form present
archive_url        -> HTTP 200, aznet-theme-forms-css absent
home_url           -> HTTP 200, aznet-theme-forms-css absent when Home is the latest-posts route
```

Also assert no active plugins and no Theme-caused `PHP Fatal error`, `PHP Warning`, `Parse error` or `Uncaught` markers in the disposable runtime log.

- [ ] **Step 3: Commit the runtime fixture**

Run:

```bash
git add tests/runtime/x5-native-form-controls.php
git diff --cached --check
git commit -m "test: add X5 native form runtime fixture"
```

---

### Task 4: Add responsive, keyboard, state and accessibility browser verification

**Files:**
- Create: `tests/browser/x5-native-form-controls-l4.mjs`
- Test: Search, Comment Form, Core block controls and protected Page at 1440/1024/390/320 widths.

**Interfaces:**
- Consumes environment variables `X5_COMMENT_URL`, `X5_BLOCKS_URL`, `X5_PASSWORD_URL`, `X5_SEARCH_URL`, `X5_FORM_STATE_DIR`.
- Produces screenshots, axe JSON and `summary.json` under `/tmp/x5-form-l4` by default.

- [ ] **Step 1: Implement a four-route/four-viewport matrix**

Use this route/viewport shape in `tests/browser/x5-native-form-controls-l4.mjs`:

```js
const routes = {
  comments: process.env.X5_COMMENT_URL,
  blocks: process.env.X5_BLOCKS_URL,
  password: process.env.X5_PASSWORD_URL,
  search: process.env.X5_SEARCH_URL,
};

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};
```

Fail immediately if any required URL is missing.

- [ ] **Step 2: Add reusable control and keyboard-focus evidence**

Implement helpers equivalent to:

```js
async function controlStyle(page, selector) {
  return page.locator(selector).evaluate((element) => {
    const style = getComputedStyle(element);
    const rect = element.getBoundingClientRect();
    return {
      width: rect.width,
      height: rect.height,
      borderStyle: style.borderStyle,
      borderWidth: Number.parseFloat(style.borderWidth || '0'),
      borderRadius: style.borderRadius,
      opacity: Number.parseFloat(style.opacity || '1'),
      cursor: style.cursor,
      outlineStyle: style.outlineStyle,
      outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
      outlineColor: style.outlineColor,
    };
  });
}

async function keyboardFocusEvidence(page, selector) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });

  for (let index = 0; index < 200; index += 1) {
    await page.keyboard.press('Tab');
    const evidence = await page.evaluate((target) => {
      const element = document.activeElement;
      if (!(element instanceof HTMLElement) || !element.matches(target)) return null;
      const style = getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      return {
        focusVisible: element.matches(':focus-visible'),
        visible: rect.width > 1 && rect.height > 1,
        outlineStyle: style.outlineStyle,
        outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
      };
    }, selector);
    if (evidence) return evidence;
  }

  return null;
}
```

Treat a focused control as PASS only when it is visible, matches `:focus-visible`, and has a non-`none` outline with width >= 1px.

- [ ] **Step 3: Assert native controls per route**

Use production-output selectors, not invented plugin selectors:

```js
const routeSelectors = {
  comments: {
    text: '#respond input[name="author"]',
    email: '#respond input[name="email"]',
    textarea: '#respond textarea#comment',
    submit: '#respond input[type="submit"]',
  },
  blocks: {
    searchInput: '.aznet-theme-entry__content .wp-block-search input[type="search"]',
    searchButton: '.aznet-theme-entry__content .wp-block-search button',
    select: '.aznet-theme-entry__content .wp-block-categories-dropdown select',
  },
  password: {
    password: '.aznet-theme-entry__content .post-password-form input[type="password"]',
    submit: '.aznet-theme-entry__content .post-password-form input[type="submit"]',
  },
  search: {
    input: '.aznet-theme-search-header__form .search-field',
    submit: '.aznet-theme-search-header__form .search-submit',
  },
};
```

For every route/viewport:

- response status must be `200`;
- exactly one `<main>` landmark must exist;
- document horizontal overflow must be `0`;
- `forms.css` must appear in `document.styleSheets`;
- every listed control must be present and stay within its container/viewport;
- text-like controls and action controls must have a visible border and at least `2.75rem`/44px target height where that height applies;
- the native Categories `<select>` must be visible and non-overflowing;
- keyboard Tab must expose visible focus on at least one text/select/textarea control and one action control on each relevant route;
- axe critical/serious violations must be `0` for the main form region;
- console/page/subresource errors must be `0`.

- [ ] **Step 4: Prove disabled and invalid presentation without changing WordPress semantics**

On the Core-block Page, use test-only DOM state changes after initial render:

```js
const select = page.locator('.aznet-theme-entry__content .wp-block-categories-dropdown select');
await select.evaluate((element) => { element.disabled = true; });
const disabled = await controlStyle(page, '.aznet-theme-entry__content .wp-block-categories-dropdown select');
if (!(disabled.opacity < 1) || disabled.cursor !== 'not-allowed') {
  throw new Error(`disabled select presentation missing: ${JSON.stringify(disabled)}`);
}

const searchInput = page.locator('.aznet-theme-entry__content .wp-block-search input[type="search"]');
await searchInput.evaluate((element) => element.setAttribute('aria-invalid', 'true'));
const invalidBorder = await searchInput.evaluate((element) => getComputedStyle(element).borderColor);
const danger = await searchInput.evaluate(() => getComputedStyle(document.documentElement).getPropertyValue('--aznet-theme-color-danger').trim());
if (!invalidBorder || !danger) throw new Error('invalid-state presentation tokens unavailable');
```

This validates CSS state presentation only; it must not submit data, invent validation logic or mutate server-side state.

On the Comments route, if `#wp-comment-cookies-consent` renders, verify its rendered box remains compact (for example <= 32px in both dimensions) so shared text-input rules never stretch checkbox/radio controls.

- [ ] **Step 5: Record browser artifacts**

For each case, write:

```text
/tmp/x5-form-l4/axe-<route>-<viewport>.json
/tmp/x5-form-l4/x5-<route>-<viewport>.png
```

Write `/tmp/x5-form-l4/summary.json` and print exactly:

```text
PASS: X5 native form controls browser matrix 16/16
```

when all four routes x four viewports pass.

- [ ] **Step 6: Commit browser verification**

Run:

```bash
git add tests/browser/x5-native-form-controls-l4.mjs
git diff --cached --check
git commit -m "test: add X5 native form browser matrix"
```

---

### Task 5: Add the exact-head X5 GitHub Actions gate

**Files:**
- Create: `.github/workflows/x5-native-form-controls.yml`
- Test: exact candidate SHA in GitHub Actions.

**Interfaces:**
- Consumes the X5 offline/runtime/browser files from Tasks 1-4.
- Produces one `x5-native-form-controls-evidence` artifact with exact checkout SHA, runtime HTML/logs, browser screenshots, axe JSON and summary.

- [ ] **Step 1: Create a bounded X5 workflow trigger**

Create `.github/workflows/x5-native-form-controls.yml` with `pull_request` paths for exactly the files X5 may modify plus `workflow_dispatch`:

```yaml
name: X5 Native Form Controls

on:
  pull_request:
    paths:
      - 'assets/css/components/forms.css'
      - 'assets/css/components/comments.css'
      - 'assets/css/components/recovery.css'
      - 'inc/theme/assets.php'
      - 'scripts/verify-v1-core.sh'
      - 'tests/offline/x1-comments-surface-contract.php'
      - 'tests/offline/x2-search-404-empty-contract.php'
      - 'tests/offline/r6-asset-scope-contract.php'
      - 'tests/offline/x5-native-form-controls-contract.php'
      - 'tests/runtime/x5-native-form-controls.php'
      - 'tests/browser/x5-native-form-controls-l4.mjs'
      - '.github/workflows/x5-native-form-controls.yml'
      - 'docs/evidence/X5_NATIVE_FORM_CONTROLS_20260916.md'
      - 'docs/source/AZT-04-roadmap-qa-decisions.md'
      - 'docs/source/AZT-EXEC-MAP.md'
      - 'docs/source/SOURCE_MANIFEST.md'
  workflow_dispatch:

permissions:
  contents: read

jobs:
  x5-quality:
    runs-on: ubuntu-24.04
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: wordpress
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping -h 127.0.0.1 -proot"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=10
```

- [ ] **Step 2: Check out the exact candidate and run the complete static/contract gate**

Use an exact checkout ref:

```yaml
      - name: Checkout exact X5 candidate
        uses: actions/checkout@v4
        with:
          ref: ${{ github.event.pull_request.head.sha || github.sha }}
          fetch-depth: 0

      - name: Record exact candidate SHA
        run: |
          set -euo pipefail
          mkdir -p /tmp/x5 /tmp/x5-form-l4
          git rev-parse HEAD | tee /tmp/x5/checkout-sha.txt

      - name: Set up PHP 8.1
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mysqli
          coverage: none

      - name: Run X5 static and retained contract gate
        run: |
          set -euo pipefail
          bash scripts/verify-v1-core.sh
          grep -F 'Version: 1.1.0' style.css
          grep -F "define( 'AZNET_THEME_VERSION', '1.1.0' );" functions.php
```

- [ ] **Step 3: Install real WordPress 6.9 with zero active plugins**

Follow the existing X4/X3 disposable runtime pattern:

```bash
rm -rf /tmp/wp
mkdir -p /tmp/wp/wp-content/themes/aznet-theme
curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /tmp/wp-cli.phar
chmod +x /tmp/wp-cli.phar
sudo mv /tmp/wp-cli.phar /usr/local/bin/wp
wp core download --version=6.9 --path=/tmp/wp --force --allow-root
wp config create --path=/tmp/wp --dbname=wordpress --dbuser=root --dbpass=root --dbhost=127.0.0.1 --skip-check --allow-root
wp core install --path=/tmp/wp --url=http://127.0.0.1:8080 --title='AZnet X5 Runtime' --admin_user=admin --admin_password='x5-form-password' --admin_email=ci@example.test --skip-email --allow-root
rsync -a --delete --exclude='.git' --exclude='.github' --exclude='docs' --exclude='scripts' --exclude='tests' --exclude='README.md' --exclude='aznet-preview.png' ./ /tmp/wp/wp-content/themes/aznet-theme/
wp theme activate aznet-theme --path=/tmp/wp --allow-root
wp core version --path=/tmp/wp --allow-root | grep -Fx '6.9'
test -z "$(wp plugin list --status=active --field=name --path=/tmp/wp --allow-root)"
```

Seed the fixture:

```bash
wp eval-file "$GITHUB_WORKSPACE/tests/runtime/x5-native-form-controls.php" --path=/tmp/wp --allow-root > /tmp/x5/fixture.json
jq -e '.post_id > 0 and .page_id > 0 and .protected_page_id > 0 and .category_id > 0' /tmp/x5/fixture.json
```

- [ ] **Step 4: Verify runtime asset scope before browser testing**

Start the PHP server and request each fixture route. Persist HTML under `/tmp/x5/*.html`. Require `aznet-theme-forms-css` on Comment Post, native-block Page, protected Page, Search and 404; reject it on archive and latest-posts Home.

Use exact status expectations:

```text
comment=200
blocks=200
password=200
search=200
missing=404
archive=200
home=200
```

Then scan `/tmp/x5/server.log` for Theme-caused PHP fatal/warning/parse/uncaught markers.

- [ ] **Step 5: Run Playwright/axe at pinned major/minor versions**

```yaml
      - name: Set up Node 22
        uses: actions/setup-node@v4
        with:
          node-version: '22'

      - name: Install Playwright and axe
        run: |
          npm install --no-save playwright@1.55.0 @axe-core/playwright@4.10.2
          npx playwright install --with-deps chromium

      - name: Run X5 browser verification
        env:
          X5_COMMENT_URL: ${{ env.X5_COMMENT_URL }}
          X5_BLOCKS_URL: ${{ env.X5_BLOCKS_URL }}
          X5_PASSWORD_URL: ${{ env.X5_PASSWORD_URL }}
          X5_SEARCH_URL: ${{ env.X5_SEARCH_URL }}
          X5_FORM_STATE_DIR: /tmp/x5-form-l4
        run: node tests/browser/x5-native-form-controls-l4.mjs
```

Because step-level environment persistence across GitHub Actions requires `$GITHUB_ENV`, write fixture URLs there after parsing `/tmp/x5/fixture.json`; do not rely on shell-local variables crossing steps.

- [ ] **Step 6: Enforce ownership and diff boundaries**

Run `git diff --check` and compare against `origin/main`. Reject any changed path outside the X5 list above. Also reject production markers for private storage/provider takeover or form-engine behavior.

The allowed-file regex must include only:

```text
assets/css/components/forms.css
assets/css/components/comments.css
assets/css/components/recovery.css
inc/theme/assets.php
scripts/verify-v1-core.sh
tests/offline/x1-comments-surface-contract.php
tests/offline/x2-search-404-empty-contract.php
tests/offline/r6-asset-scope-contract.php
tests/offline/x5-native-form-controls-contract.php
tests/runtime/x5-native-form-controls.php
tests/browser/x5-native-form-controls-l4.mjs
.github/workflows/x5-native-form-controls.yml
docs/evidence/X5_NATIVE_FORM_CONTROLS_20260916.md
docs/source/AZT-04-roadmap-qa-decisions.md
docs/source/AZT-EXEC-MAP.md
docs/source/SOURCE_MANIFEST.md
```

Do not whitelist Woo/provider source or unrelated homepage/admin/provisioning code.

- [ ] **Step 7: Upload exact evidence even on failure**

Use:

```yaml
      - name: Upload X5 evidence
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: x5-native-form-controls-evidence
          path: |
            /tmp/x5
            /tmp/x5-form-l4
          if-no-files-found: error
```

- [ ] **Step 8: Commit the workflow**

Run:

```bash
git add .github/workflows/x5-native-form-controls.yml
git diff --cached --check
git commit -m "ci: verify X5 native form controls"
```

Push the feature branch and let the exact-head workflow run. Do not claim L3/L4 before this workflow is actually GREEN.

---

### Task 6: Close functional X5 evidence and authoritative source only after fresh PASS

**Files:**
- Create: `docs/evidence/X5_NATIVE_FORM_CONTROLS_20260916.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: exact functional-head SHA, RED SHA/failure, dedicated X5 run ID, artifact ID/digest, full settled retained workflow set and actual runtime/browser counts.
- Produces: authoritative checkpoint **X5 PASS / X6 NEXT** without promoting Theme version.

- [ ] **Step 1: Require a fully settled functional head**

Before editing evidence/source, verify all of the following from GitHub, not from assumption:

```text
X5 dedicated workflow = completed/success
X5 runtime asset boundary = PASS
X5 browser matrix = 16/16 PASS
axe blocking critical/serious = 0 in every recorded case
Theme metadata = 1.1.0
all triggered retained PR workflows on the same functional head = completed/success
```

If one retained workflow fails, inspect that exact job/log and reduce the cause before touching production or source status.

- [ ] **Step 2: Write factual evidence only**

Create `docs/evidence/X5_NATIVE_FORM_CONTROLS_20260916.md` with these headings and replace each evidence token with the actual verified value from the settled run:

```markdown
# X5 Native Form Controls — Closure Evidence

**Status:** PASS at L1-L4 on verified functional head; canonical merge pending owner approval.
**Date:** 2026-09-16
**Functional head:** `<exact-sha>`
**PR:** `<production-pr-number>`

## Scope and ownership
## RED -> GREEN evidence
## Shared primitive and asset boundary
## L1-L2 static and retained contracts
## L3 WordPress 6.9 zero-plugin runtime
## L4 responsive / keyboard / state / accessibility
## Retained regression
## Rollback
## Not inferred
## Exact next
```

The content must explicitly state that WordPress owns Search, Comments, password and Core-block submission/validation semantics; Woo/provider form skinning is not included; Theme metadata remains `1.1.0`.

- [ ] **Step 3: Advance source only if its preconditions still match**

Re-read canonical source immediately before editing. If AZT-04 still owns X5 as the exact next and no newer checkpoint superseded it:

- bump AZT-04 `v0.47` -> `v0.48` and set v1.2 state to `X5 PASS / X6 NEXT`;
- bump AZT-EXEC-MAP `v0.39` -> `v0.40` and set derived exact next to an X6 Cross-surface QA & Release Closure implementation plan/review gate;
- align SOURCE_MANIFEST to AZT-04 v0.48 / EXEC-MAP v0.40 and add the verified X5 evidence path/run/artifact.

Do not modify AZT-05, AZT-01 or AZT-02 unless implementation discovered a genuinely new product/ownership/public-contract decision. This X5 plan is designed not to require one.

- [ ] **Step 4: Keep X6/version claims strictly closed**

Source closure must say:

```text
X5 PASS at L1-L4.
Theme metadata remains 1.1.0.
X6 L6 package/source identity, lifecycle/rollback, 1.2.0 promotion, publication and deployment are not yet PASS.
Provider L5 remains outside v1.2.
Exact next: write/review the X6 Cross-surface QA & Release Closure plan before X6 production/release changes.
```

- [ ] **Step 5: Commit source/evidence closure and re-run final-head CI**

Run:

```bash
git add docs/evidence/X5_NATIVE_FORM_CONTROLS_20260916.md \
  docs/source/AZT-04-roadmap-qa-decisions.md \
  docs/source/AZT-EXEC-MAP.md \
  docs/source/SOURCE_MANIFEST.md
git diff --cached --check
git commit -m "docs: close X5 native form controls"
```

Then require a fresh exact-final-head workflow set because source/evidence bytes changed after the functional PASS. Documentation-only changes do not inherit a prior head's PASS automatically.

---

### Task 7: Prepare X5 for owner merge gate and require exact-main verification after approval

**Files:**
- Modify: PR body/state only; no additional production file is expected.
- Test: final PR head and, after owner-approved merge, exact canonical `main`.

**Interfaces:**
- Consumes: fresh exact-final-head GREEN evidence from Task 6.
- Produces: review-ready X5 PR; after separate owner approval, canonical merge plus exact-main verification.

- [ ] **Step 1: Self-review against the approved design before Ready for Review**

Confirm the diff proves all of the following:

```text
shared primitives before surface overrides
known WordPress Core/Theme selectors only
no private plugin selectors/contracts
no custom submission/validation/query/routing state
no production JavaScript added
Woo dedicated form presentation untouched and X5 asset excluded on Woo current surfaces
Search/Comments retained outcomes still PASS
Theme metadata still 1.1.0
rollback leaves WordPress data/state untouched
```

Also run:

```bash
git diff --check origin/main...HEAD
bash scripts/verify-v1-core.sh
```

- [ ] **Step 2: Update the production PR with exact evidence**

The PR body must include:

- RED SHA and intended failure;
- functional head SHA;
- final head SHA;
- dedicated X5 run ID;
- exact X5 artifact ID/digest;
- 16/16 browser matrix result;
- settled retained workflow count/status;
- source state `X5 PASS / X6 NEXT`;
- metadata `1.1.0`;
- rollback and non-inferred claims.

Mark the PR Ready for Review only after the exact final head is fully GREEN.

- [ ] **Step 3: Stop at the X5 production merge hard gate**

Do **not** merge the X5 production PR from implementation-plan approval alone. X5 production merge requires a separate owner approval after the final evidence is reviewable.

At this checkpoint report exactly:

```text
PASS — X5 production candidate L1-L4 on exact final head.
BLOCKED — canonical merge awaiting owner approval.
EVIDENCE — branch/head/PR/run/artifact.
NEXT — owner approval to merge the exact final head.
```

- [ ] **Step 4: After explicit owner approval, merge with expected-head protection**

Immediately before merge re-fetch PR state and require:

```text
state=open
draft=false
mergeable=true
head=<the verified final head>
all required final-head workflows=completed/success
```

Merge using the exact expected head SHA. Do not force-update or merge a later unverified head.

- [ ] **Step 5: Require fresh exact-main evidence before canonical PASS**

After merge, find the `V1 Exact Main Verification` push run whose `head_sha` equals the X5 merge commit. Require both jobs to complete SUCCESS:

```text
static-contracts
clean-runtime-browser
```

Fetch/record their exact-main artifacts and digests. Only then claim canonical X5 integration PASS.

- [ ] **Step 6: Advance only to the X6 plan gate**

After exact-main PASS, create the bounded X6 Cross-surface QA & Release Closure implementation plan from the latest authoritative source. Do not promote `1.2.0`, build/publish a release, deploy, or start provider L5 work merely because X5 integrated successfully.

---

## Self-Review

### 1. Spec coverage

- **Consistent native controls:** Tasks 2-4 cover input/select/textarea/button and labels across actual WordPress Search, Comments, password form and Core block output.
- **Focus-visible:** shared primitive plus keyboard browser evidence.
- **Disabled:** CSS `:disabled` plus computed browser evidence on a real Core-generated select.
- **Error/notice where Theme legitimately controls it:** `[aria-invalid="true"]` presentation only, plus native comment helper/required notice typography; no validation engine.
- **Shared primitives before overrides:** `forms.css` loads before Comments/Recovery and those styles retain surface layout only.
- **No plugin skinning:** selectors are bounded to Theme wrappers and WordPress Core classes; Woo asset scope remains dedicated and X5 is excluded from Woo current surfaces.
- **L1-L4:** Tasks 1-5 explicitly create RED/GREEN, retained regression, WordPress 6.9 runtime and 16-case browser/a11y evidence.
- **Rollback:** removing presentation assets leaves WordPress-owned state/data untouched.
- **Version discipline:** every contract/workflow/source step retains `1.1.0`; X6 owns promotion.

No approved X5 requirement requires a new architecture/public integration decision.

### 2. Placeholder scan

The implementation steps contain concrete file paths, APIs, selectors, commands, expected RED cause, runtime routes, viewport matrix, workflow versions and source preconditions. Evidence values are intentionally required to be populated from the future verified run rather than fabricated in advance.

### 3. Type/name consistency

The plan consistently uses:

```text
should_enqueue_form_assets(): bool
enqueue_form_assets(?string $version = null): void
aznet-theme-forms
assets/css/components/forms.css
X5_COMMENT_URL
X5_BLOCKS_URL
X5_PASSWORD_URL
X5_SEARCH_URL
X5_FORM_STATE_DIR
```

No later task uses an alternate function, handle, asset path or environment-variable spelling.

---

## Review Gate

This plan is intentionally **plan-only**. It introduces no X5 production PHP/CSS/JS behavior. After this plan is committed and opened as a PR, X5 production implementation must not begin until the owner reviews/approves the plan. On approval, execute it with `superpowers:executing-plans` (or the recommended subagent-driven workflow) and preserve RED -> intended failure -> minimal GREEN -> regression -> L3 -> L4.

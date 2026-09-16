# X3 Media / Gallery / Embed Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make native WordPress authored media robust across Post, Page and static Front Page content while preserving WordPress media/content ownership and keeping Theme behavior presentation-only.

**Architecture:** Add one dedicated `media.css` presentation module scoped to Theme authored-content wrappers and load it only on native Post/Page requests. Reuse the existing WordPress `responsive-embeds`, HTML5 gallery/caption support, `theme.json` content/wide sizes and `add_editor_style()` path; do not change template markup unless a fresh test proves Theme-owned markup is the blocker. Runtime/browser fixtures cover modern Core output plus approved legacy caption/gallery output without adding JavaScript, lightbox state or media data ownership.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP Theme + `theme.json`, Theme CSS, WP-CLI fixtures, Classic Editor under the accepted native Post/Page editor policy, Playwright 1.55, axe-core Playwright 4.10, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-09-16-x3-media-gallery-embed-design.md`

## Global Constraints

- Product scope is AZnet Theme only. Do not modify RootProfile, ConvertFlow, WooCommerce or another provider product.
- WordPress owns Media Library data, attachment metadata, image sizes/URLs, block/shortcode content, caption text, gallery ordering, embed output and content lifecycle.
- Theme owns presentation only: responsive containment, gallery/caption styling, safe alignment geometry, focus presentation and surface-aware Theme assets.
- Supported frontend surfaces are native single Post, native Page and a Page used as static Front Page. Search, 404, Archive cards, WooCommerce, provider surfaces and Theme-generated Homepage Composer output are outside X3.
- Every frontend X3 selector must be rooted in `.aznet-theme-entry__content`; editor-only equivalents may use `.editor-styles-wrapper` and `body#tinymce` / `.mce-content-body` because those are editor contexts, not public frontend scope.
- Support modern Core/Gutenberg media output and approved legacy WordPress caption/gallery output. Do not create a parallel legacy renderer.
- No lightbox/modal/gallery application engine, JavaScript media subsystem, duplicate gallery state, direct attachment/private storage reads, CDN/image optimizer/proxy or provider-specific selectors.
- Do not infer media semantics from URL, filename, slug or attachment heuristics.
- `alignfull` means safe Theme-shell width, not `100vw` viewport breakout. No page-level horizontal overflow.
- Retain `theme.json` `contentSize`/`wideSize` and `appearanceTools=false`; change `theme.json` only if fresh RED/L4 evidence proves a missing native declaration.
- Preserve accepted Classic Editor policy for native Posts/Pages. Do not enable Gutenberg to manufacture an editor-parity claim.
- Theme metadata remains exactly `1.1.0` in `style.css` and `AZNET_THEME_VERSION` until X6.
- Production behavior follows RED -> intended failure -> minimal GREEN -> regression.
- Source closure occurs only after fresh X3 L1-L4 evidence on the exact feature head. Merge to `main` remains a separate owner gate.

---

## File Structure

### New files

- `assets/css/components/media.css` — shared scoped authored-media presentation for frontend and editor contexts.
- `tests/offline/x3-media-gallery-embed-contract.php` — X3 ownership, selector-scope, asset-gate and version contract.
- `tests/runtime/x3-media-gallery-embed.php` — deterministic WordPress 6.9 fixture with modern + legacy media on Post/Page/static Front Page.
- `tests/browser/x3-media-gallery-embed-l4.mjs` — four-viewport media/layout/a11y/browser matrix plus Classic Editor parity smoke.
- `.github/workflows/x3-media-gallery-embed.yml` — exact-candidate X3 L1-L4 workflow.
- `docs/evidence/X3_MEDIA_GALLERY_EMBED_20260916.md` — closure evidence created only after fresh functional PASS.

### Existing files expected to change

- `inc/theme/assets.php` — add `should_enqueue_media_assets(): bool` and `enqueue_media_assets(?string $version = null): void`, then call it from `enqueue_assets()`.
- `inc/theme/design-system.php` — include `assets/css/components/media.css` in the existing `editor_stylesheets()` list so Classic Editor receives the same presentation module; keep preset ordering deterministic.
- `tests/offline/r1-visual-preset-contract.php` — update retained exact editor stylesheet expectations to include `media.css`.
- `tests/offline/r6-asset-scope-contract.php` — model X3 authored-media state and prove no asset leak onto Woo/non-authored routes.
- `scripts/verify-v1-core.sh` — retain X3 contract after GREEN.
- `docs/source/AZT-04-roadmap-qa-decisions.md` — final closure only: X3 PASS / X4 NEXT and version bump.
- `docs/source/AZT-EXEC-MAP.md` — final closure only: derived X3 PASS / X4 NEXT and version bump.
- `docs/source/SOURCE_MANIFEST.md` — final closure only: synchronize source versions and exact next.

### Files intentionally unchanged unless fresh evidence requires otherwise

- `page.php`, `front-page.php`, `single.php`, `template-parts/content/content-single.php` — existing `.aznet-theme-entry__content` boundary is sufficient by design.
- `theme.json` — current content/wide sizes are the first-choice native layout authority.
- `inc/admin/editor-policy.php` — native Post/Page Classic Editor policy is retained.

---

### Task 1: Establish X3 RED contract before production behavior

**Files:**
- Create: `tests/offline/x3-media-gallery-embed-contract.php`
- Modify: `scripts/verify-v1-core.sh`
- Read/retain: `tests/offline/editorial-single-post-contract.php`
- Read/retain: `tests/offline/homepage-blueprint-asset-contract.php`
- Read/retain: `tests/offline/classic-editor-policy-contract.php`

**Interfaces:**
- Consumes: existing authored-content wrappers, `inc/theme/assets.php`, `inc/theme/design-system.php`, Theme version declarations.
- Produces: an executable contract that cannot PASS before the dedicated X3 media capability exists.

- [ ] **Step 1: Create the failing X3 contract**

Create `tests/offline/x3-media-gallery-embed-contract.php` with this structure:

```php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function x3_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function x3_source(string $relative): string {
    $path = dirname(__DIR__, 2) . '/' . $relative;
    if (! is_file($path)) {
        x3_fail('missing required file: ' . $relative);
    }
    $source = file_get_contents($path);
    if (false === $source) {
        x3_fail('cannot read required file: ' . $relative);
    }
    return $source;
}

$assets = x3_source('inc/theme/assets.php');
$design = x3_source('inc/theme/design-system.php');
$page = x3_source('page.php');
$front = x3_source('front-page.php');
$content = x3_source('template-parts/content/content-single.php');
$style = x3_source('style.css');
$functions = x3_source('functions.php');
$media = x3_source('assets/css/components/media.css');

foreach ([$page, $front, $content] as $surface) {
    if (! str_contains($surface, 'aznet-theme-entry__content')) {
        x3_fail('authored-content wrapper missing from an X3 surface');
    }
    if (! str_contains($surface, 'the_content()')) {
        x3_fail('WordPress the_content() boundary missing from an X3 surface');
    }
}

foreach ([
    'should_enqueue_media_assets',
    'enqueue_media_assets',
    "is_singular( 'post' )",
    'is_page()',
    "assets/css/components/media.css",
    "'aznet-theme-media'",
] as $marker) {
    if (! str_contains($assets, $marker)) {
        x3_fail('X3 asset boundary missing marker: ' . $marker);
    }
}

if (! str_contains($design, "'assets/css/components/media.css'")) {
    x3_fail('media.css must use the existing editor_stylesheets path');
}

foreach ([
    '.aznet-theme-entry__content',
    '.wp-block-image',
    'figcaption',
    '.wp-block-gallery',
    '.wp-block-video',
    '.wp-block-audio',
    '.wp-block-embed',
    '.wp-caption',
    '.wp-caption-text',
    '.gallery',
    '.gallery-item',
    '.gallery-caption',
    '.alignwide',
    '.alignfull',
    ':focus-visible',
    'body#tinymce',
] as $marker) {
    if (! str_contains($media, $marker)) {
        x3_fail('media.css missing required modern/legacy/editor marker: ' . $marker);
    }
}

foreach (preg_split('/\R/', $media) ?: [] as $line) {
    $selector = trim($line);
    if ('' === $selector || ! str_ends_with($selector, '{') || str_starts_with($selector, '@')) {
        continue;
    }
    if (str_contains($selector, '.editor-styles-wrapper') || str_contains($selector, 'body#tinymce') || str_contains($selector, '.mce-content-body')) {
        continue;
    }
    if (! str_contains($selector, '.aznet-theme-entry__content')) {
        x3_fail('unscoped frontend media selector: ' . $selector);
    }
}

$production = $assets . "\n" . $design . "\n" . $media;
foreach ([
    'new WP_Query', 'WP_Query(', 'query_posts(', 'get_posts(', '$wpdb',
    'get_post_meta(', 'get_attached_file(', 'wp_get_attachment_metadata(',
    'lightbox', 'fancybox', 'photoswipe', 'swiper', 'javascript:', '100vw',
] as $forbidden) {
    if (str_contains(strtolower($production), strtolower($forbidden))) {
        x3_fail('forbidden X3 ownership/application marker: ' . $forbidden);
    }
}

if (! str_contains($functions, "define( 'AZNET_THEME_VERSION', '1.1.0' );")) {
    x3_fail('AZNET_THEME_VERSION must remain 1.1.0 during X3');
}
if (! str_contains($style, 'Version: 1.1.0')) {
    x3_fail('Theme stylesheet version must remain 1.1.0 during X3');
}

echo "PASS: X3 Media/Gallery/Embed ownership and presentation contract\n";
```

- [ ] **Step 2: Add X3 contract to the reusable verifier while still RED**

Add immediately after the X2 contract in `scripts/verify-v1-core.sh`:

```bash
printf '%s\n' '==> X3 Media / Gallery / Embed contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x3-media-gallery-embed-contract.php
```

This is test infrastructure, not production behavior.

- [ ] **Step 3: Run/trigger RED and retain exact failure evidence**

Local command when a checkout is available:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x3-media-gallery-embed-contract.php
```

Expected: `FAIL: missing required file: assets/css/components/media.css` on the pre-GREEN branch.

In the GitHub-only execution environment, open a Draft PR targeting `main` after committing Task 1 so `V1 Core Pull Request CI` executes `scripts/verify-v1-core.sh`; record the exact branch SHA and failing X3 line. Retained failures must be attributable only to the intended missing X3 media capability.

- [ ] **Step 4: Confirm retained pre-change contracts are not the RED cause**

Run or inspect the same CI for:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/editorial-single-post-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-asset-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/classic-editor-policy-contract.php
```

Expected: PASS.

- [ ] **Step 5: Commit only tests/verifier**

```bash
git add tests/offline/x3-media-gallery-embed-contract.php scripts/verify-v1-core.sh
git commit -m "test: define X3 media presentation contract"
```

---

### Task 2: Add minimal scoped media presentation and asset gates

**Files:**
- Create: `assets/css/components/media.css`
- Modify: `inc/theme/assets.php`
- Modify: `inc/theme/design-system.php`
- Modify: `tests/offline/r1-visual-preset-contract.php`
- Modify: `tests/offline/r6-asset-scope-contract.php`
- Test: `tests/offline/x3-media-gallery-embed-contract.php`

**Interfaces:**
- Consumes: native conditionals `is_singular('post')`, `is_page()`, existing Woo normalized surface, existing tokens/layout variables and `editor_stylesheets()`.
- Produces: `should_enqueue_media_assets(): bool`, `enqueue_media_assets(?string $version = null): void`, `aznet-theme-media` stylesheet handle and editor media stylesheet inclusion.

- [ ] **Step 1: Add a strict X3 frontend surface predicate**

In `inc/theme/assets.php` add:

```php
/** Determine whether native authored Page/Post media presentation can render. */
function should_enqueue_media_assets(): bool {
    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    $is_post = function_exists( 'is_singular' ) && is_singular( 'post' );
    $is_page = function_exists( 'is_page' ) && is_page();

    return $is_post || $is_page;
}
```

Do not include generic CPTs, Search, Archive or 404.

- [ ] **Step 2: Add the dedicated media enqueue helper and call it after generic content**

```php
/** Enqueue authored-media presentation only on native Post/Page surfaces. */
function enqueue_media_assets( ?string $version = null ): void {
    if ( ! should_enqueue_media_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-media',
        get_theme_file_uri( '/assets/css/components/media.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/media.css', $version )
    );
}
```

In `enqueue_assets()`, invoke `enqueue_media_assets( $version );` immediately after the generic-content enqueue block and before recovery/article/comments. This preserves dependency ordering without making X3 global.

- [ ] **Step 3: Add the media module to the existing editor stylesheet list**

Change `editor_stylesheets()` to deterministic order:

```php
$stylesheets = [
    'assets/css/tokens.css',
    'assets/css/components/media.css',
];
```

Then append the active non-default preset exactly as today. Do not add a new editor hook.

- [ ] **Step 4: Create minimal `media.css` with authored-content and editor-context roots**

Use existing tokens only. The implementation must include these behaviors:

```css
.aznet-theme-entry__content :where(figure, .wp-caption, .wp-block-image, .wp-block-video, .wp-block-audio, .wp-block-embed) {
    max-width: 100%;
}

.aznet-theme-entry__content :where(img, video, audio, iframe) {
    max-width: 100%;
}

.aznet-theme-entry__content :where(img, video) {
    height: auto;
}

.aznet-theme-entry__content audio {
    width: 100%;
}

.aznet-theme-entry__content :where(figcaption, .wp-caption-text, .gallery-caption) {
    margin-block-start: var(--aznet-theme-space-2);
    color: var(--aznet-theme-muted);
    font-size: var(--aznet-theme-font-size-sm);
    line-height: var(--aznet-theme-line-height-body);
    overflow-wrap: anywhere;
}

.aznet-theme-entry__content .wp-block-gallery,
.aznet-theme-entry__content .gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 12rem), 1fr));
    gap: var(--aznet-theme-space-3);
    max-width: 100%;
}

.aznet-theme-entry__content :where(.gallery-item, .wp-block-gallery > figure) {
    min-width: 0;
    margin: 0;
}

.aznet-theme-entry__content :where(.gallery-item img, .wp-block-gallery img) {
    display: block;
    width: 100%;
    height: auto;
}

.aznet-theme-entry__content :where(.wp-block-embed, .wp-block-embed__wrapper) {
    min-width: 0;
    max-width: 100%;
}

.aznet-theme-entry__content iframe {
    display: block;
    width: 100%;
    max-width: 100%;
}

.aznet-theme-entry__content :where(.alignwide, .alignfull) {
    width: min(100%, var(--aznet-theme-container-wide));
    max-width: 100%;
}

.aznet-theme-entry__content :where(a, button):focus-visible {
    outline: 2px solid var(--aznet-theme-focus);
    outline-offset: 2px;
}
```

Add equivalent editor-context declarations using `.editor-styles-wrapper` and `body#tinymce` / `.mce-content-body` for image/video/audio containment, captions, gallery spacing and legacy caption/gallery output. Editor rules may share comma-separated declarations but frontend selectors must never lose the `.aznet-theme-entry__content` root.

Do not apply `overflow: hidden` to figure/gallery wrappers because that can clip focus rings. Do not force a universal `aspect-ratio` or `object-fit` on authored images/embeds.

- [ ] **Step 5: Update retained R1 editor stylesheet expectations**

Expect:

```php
assert([
    'assets/css/tokens.css',
    'assets/css/components/media.css',
    'assets/css/presets/editorial.css',
] === \AZnet\Theme\editor_stylesheets());
```

and default:

```php
assert([
    'assets/css/tokens.css',
    'assets/css/components/media.css',
] === \AZnet\Theme\editor_stylesheets());
```

Do not weaken the existing Classic Editor/bootstrap assertions.

- [ ] **Step 6: Update retained R6 asset matrix for X3 instead of masking it**

Add explicit X3 state to the R6 harness so `is_page()` and `is_singular('post')` can model authored routes. Expected behavior:

- clean native Page -> generic-content + media;
- clean native Post -> generic-content + media + article;
- Woo product/archive/cart/checkout/account/block routes -> no media handle;
- Header-only cases -> no media handle.

Preserve exact-handle comparisons.

- [ ] **Step 7: Run GREEN static/contract chain**

```bash
php -l inc/theme/assets.php
php -l inc/theme/design-system.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x3-media-gallery-embed-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r1-visual-preset-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r6-asset-scope-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/editorial-single-post-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-asset-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/classic-editor-policy-contract.php
bash scripts/verify-v1-core.sh
```

Expected: all PASS. Do not edit `theme.json` or templates merely to make static markers easier.

- [ ] **Step 8: Commit minimal GREEN presentation**

```bash
git add assets/css/components/media.css inc/theme/assets.php inc/theme/design-system.php tests/offline/r1-visual-preset-contract.php tests/offline/r6-asset-scope-contract.php
git commit -m "feat: add scoped native media presentation"
```

---

### Task 3: Prove real WordPress 6.9 runtime and asset boundaries

**Files:**
- Create: `tests/runtime/x3-media-gallery-embed.php`
- Create: `.github/workflows/x3-media-gallery-embed.yml` (runtime portions)

**Interfaces:**
- Consumes: WordPress 6.9 native Posts/Pages, Media Library, gallery shortcode, Core block markup, current Theme candidate.
- Produces: deterministic fixture JSON containing `post_url`, `page_url`, `front_url`, `search_url`, `archive_url`, `not_found_url`, fixture IDs and media IDs.

- [ ] **Step 1: Create deterministic media fixture**

`tests/runtime/x3-media-gallery-embed.php` must create two small attachment images using files already prepared by the workflow, then create content that includes:

- Core image figure with a long Vietnamese caption and a focusable image link;
- `.wp-block-gallery` with at least two images;
- legacy `[gallery ids="..."]` output;
- legacy `.wp-caption` / `.wp-caption-text` markup;
- `<video controls>` using a local fixture data/file URL or WordPress upload fixture;
- `<audio controls>` using a local fixture data/file URL or WordPress upload fixture;
- `.wp-block-embed` wrapper with a deterministic same-origin iframe URL to avoid external network/provider dependency;
- `.alignwide` and `.alignfull` figures.

Create one native Post and two native Pages. Configure one Page as static Front Page with:

```php
update_option('show_on_front', 'page');
update_option('page_on_front', $front_id);
```

Return JSON with all route URLs. Do not write Theme settings or provider state.

- [ ] **Step 2: Add exact-candidate workflow scaffold**

Create `.github/workflows/x3-media-gallery-embed.yml` following the X2 workflow conventions:

```yaml
name: X3 Media Gallery Embed

on:
  push:
    branches:
      - feat/v1.2-x3-media
    paths:
      - 'assets/css/components/media.css'
      - 'inc/theme/assets.php'
      - 'inc/theme/design-system.php'
      - 'tests/offline/x3-media-gallery-embed-contract.php'
      - 'tests/runtime/x3-media-gallery-embed.php'
      - 'tests/browser/x3-media-gallery-embed-l4.mjs'
      - 'tests/offline/r1-visual-preset-contract.php'
      - 'tests/offline/r6-asset-scope-contract.php'
      - 'scripts/verify-v1-core.sh'
      - '.github/workflows/x3-media-gallery-embed.yml'
      - 'docs/evidence/X3_MEDIA_GALLERY_EMBED_20260916.md'
      - 'docs/source/AZT-04-roadmap-qa-decisions.md'
      - 'docs/source/AZT-EXEC-MAP.md'
      - 'docs/source/SOURCE_MANIFEST.md'
  pull_request:
    branches:
      - main
  workflow_dispatch:

permissions:
  contents: read
```

Use `actions/checkout@v4` with:

```yaml
ref: ${{ github.event.pull_request.head.sha || github.sha }}
fetch-depth: 0
```

Set up PHP 8.1, MySQL 8.0, WordPress 6.9, zero active plugins and the exact candidate Theme.

- [ ] **Step 3: Verify runtime output and scoped media asset**

After seeding fixture and starting WordPress:

- Post, Page and static Front Page must contain `aznet-theme-media-css` and their fixture markers.
- Search result, Category Archive and native HTTP 404 must not contain `aznet-theme-media-css`.
- Fixture output must include modern Core gallery/caption classes and legacy `.gallery` / `.wp-caption` output.
- `wp plugin list --status=active` must be empty.
- Theme metadata remains 1.1.0.
- No fatal/parse/uncaught runtime marker.

Do not use live external embed providers in this workflow.

- [ ] **Step 4: Commit fixture/workflow runtime layer**

```bash
git add tests/runtime/x3-media-gallery-embed.php .github/workflows/x3-media-gallery-embed.yml
git commit -m "test: add X3 WordPress media runtime"
```

---

### Task 4: Add responsive/a11y/browser and accepted editor-parity proof

**Files:**
- Create: `tests/browser/x3-media-gallery-embed-l4.mjs`
- Modify: `.github/workflows/x3-media-gallery-embed.yml`

**Interfaces:**
- Consumes: fixture JSON from Task 3 and WordPress admin credentials created by the workflow.
- Produces: screenshots, axe JSON and `summary.json` for 12 frontend route/viewport cases plus Classic Editor parity evidence.

- [ ] **Step 1: Build the four-viewport frontend matrix**

Use:

```js
const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

const routes = {
  post: fixture.post_url,
  page: fixture.page_url,
  front: fixture.front_url,
};
```

For every route/viewport assert:

- HTTP 200;
- `.aznet-theme-entry__content` visible;
- exactly one page-level H1 when the surface normally emits one; static Front Page may legitimately omit a Theme-added H1, so assert only the expected template structure rather than inventing a heading;
- `document.documentElement.scrollWidth - clientWidth <= 1`;
- Core gallery + legacy gallery visible and each item has positive width;
- long caption width does not exceed its content wrapper and text wraps;
- image/video/audio widths do not exceed authored wrapper/safe alignment wrapper;
- audio/video `controls` remain present;
- iframe/embed width remains within safe wrapper;
- `.alignwide`/`.alignfull` right/left edges remain within the Theme main/shell geometry and viewport gutters;
- image link keyboard focus is visible and not clipped;
- Axe critical/serious = 0 for Theme-owned presentation;
- unexpected console/page/subresource HTTP errors = 0.

Write screenshots and axe JSON per case.

- [ ] **Step 2: Verify selector isolation from Homepage Composer on static Front Page**

If Homepage Composer is active in the fixture, place a composer-like sentinel outside `.aznet-theme-entry__content` with a matching generic class only when generated by existing Theme code; otherwise verify structurally from the DOM that every media element checked by X3 is inside `.aznet-theme-entry__content` and that `media.css` selectors in the contract are wrapper-scoped. Do not add fake production composer markup just for a test.

- [ ] **Step 3: Verify current Classic Editor parity without changing editor policy**

Log into `/wp-admin/post.php?post=<page_id>&action=edit`. Assert:

- `#post`, `#title`, `#content` exist;
- block editor shell `.edit-post-layout, .interface-interface-skeleton` count is zero;
- TinyMCE iframe is available after switching to Visual mode if needed;
- inside the TinyMCE document, `media.css` is loaded through `add_editor_style()` and representative image/caption/gallery content has responsive max-width/caption treatment materially consistent with frontend;
- if TinyMCE is unavailable in the CI image, the test must prove the accepted Classic Editor textarea retains the media markup and report editor visual parity as UNKNOWN rather than silently claiming Gutenberg parity. This condition must be explicit in `summary.json` and cannot be used to claim a stronger editor layer than observed.

Do not alter `inc/admin/editor-policy.php` to make this test easier.

- [ ] **Step 4: Wire Playwright/axe into X3 workflow**

Use Node 22, `playwright@1.55.0`, `@axe-core/playwright@4.10.2`, Chromium, then run:

```bash
X3_STATE_DIR=/tmp/x3 X3_L4_DIR=/tmp/x3-l4 node tests/browser/x3-media-gallery-embed-l4.mjs
```

Upload `/tmp/x3` and `/tmp/x3-l4` as `x3-media-gallery-embed-evidence` even on failure.

- [ ] **Step 5: Run X3 L1-L4 and diagnose at the shallowest stable layer**

Expected: X3 workflow SUCCESS. If L4 reveals a defect, reproduce it with the narrowest CSS/runtime assertion before modifying production. Do not broaden `theme.json`, templates or JS without a demonstrated blocker and spec-compatible reason.

- [ ] **Step 6: Commit browser verification**

```bash
git add tests/browser/x3-media-gallery-embed-l4.mjs .github/workflows/x3-media-gallery-embed.yml
git commit -m "test: verify X3 media browser quality"
```

---

### Task 5: Final regression, evidence and source closure

**Files:**
- Create: `docs/evidence/X3_MEDIA_GALLERY_EMBED_20260916.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- PR: `feat/v1.2-x3-media` -> `main`

**Interfaces:**
- Consumes: exact functional head, X3 workflow evidence, reusable V1 CI and retained PR workflows.
- Produces: source-backed X3 PASS / X4 NEXT checkpoint and reviewable PR. Does not merge.

- [ ] **Step 1: Verify exact functional head before source closure**

Require fresh evidence on one exact SHA:

```text
X3 Media Gallery Embed — SUCCESS
V1 Core Pull Request CI — SUCCESS
```

Also require all other workflows triggered by the touched shared files (`inc/theme/assets.php`, `inc/theme/design-system.php`, editor styles, verifier) to complete successfully. In particular retain R1/R6/D-027/Homepage/Woo/Provisioning/X1/X2 checks when GitHub path filters trigger them.

Do not claim “all workflows PASS” until the exact head has no queued/in-progress/failure result among triggered PR workflows.

- [ ] **Step 2: Write immutable X3 evidence**

`docs/evidence/X3_MEDIA_GALLERY_EMBED_20260916.md` must record:

- base main SHA and exact functional head SHA;
- RED SHA/run and exact intended failure;
- GREEN SHA/run;
- WordPress 6.9 / PHP 8.1 / zero mandatory plugins;
- Post/Page/static Front Page asset presence and excluded-route absence;
- modern + legacy media coverage;
- viewport matrix results and horizontal overflow result;
- Axe serious/critical counts;
- editor observation exactly as observed (Classic/TinyMCE PASS or explicitly UNKNOWN, never inferred Gutenberg parity);
- retained regression workflow names/run IDs;
- no provider L5 claim;
- rollback: revert X3 presentation/test/source commits, no WordPress content/media mutation.

- [ ] **Step 3: Advance authoritative roadmap only after evidence exists**

Update AZT-04 from v0.45 to v0.46 (unless another canonical change has already consumed that version at execution time; re-read latest source first). Set X workstream to `X3 PASS / X4 NEXT`. Record the exact X3 evidence and preserve Theme metadata 1.1.0 / Core-only milestone boundaries.

Update AZT-EXEC-MAP from v0.37 to v0.38 under the same revalidation rule. Mirror X3 PASS / X4 NEXT without creating new ownership decisions.

Update SOURCE_MANIFEST to the actual resulting versions and exact next. Add the X3 design/evidence references without duplicating authoritative current-state facts unnecessarily.

- [ ] **Step 4: Run final-head verification after source changes**

Source changes create a new exact head. Wait for all triggered workflows and rerun/dispatch X3 verification if necessary so the final PR head itself has fresh X3 evidence. `git diff --check` must PASS and Theme metadata must still be 1.1.0.

- [ ] **Step 5: Update Draft PR to reviewable state**

PR body must include:

- ownership boundary;
- RED -> GREEN evidence;
- exact final head;
- X3 L1-L4 run/artifact details;
- retained regression state;
- source closure X3 PASS / X4 NEXT;
- non-claims: provider L5, X4 implementation, v1.2 promotion/package/publication/deploy;
- rollback.

Mark PR Ready only when the final head is clean and verified. Do not merge.

- [ ] **Step 6: Stop at the merge hard gate**

Report:

```text
PASS — X3 L1-L4 + retained exact-head regression + source closure.
BLOCKED/UNKNOWN — only explicitly unverified deeper layers, if any.
EVIDENCE — branch/head/PR/run/artifact/evidence file.
NEXT — owner approval to merge the X3 PR into main.
```

Merge remains a separate explicit owner action.

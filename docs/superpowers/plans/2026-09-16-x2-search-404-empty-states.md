# X2 Search / 404 / Empty States Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn the existing native WordPress Search, 404 and listing-empty fallbacks into clear, responsive recovery/discovery experiences while preserving WordPress query and routing authority.

**Architecture:** Keep the existing PHP template hierarchy and native WordPress main query/routing intact. Add one bounded Theme-owned recovery presentation layer shared by `search.php`, `404.php` and archive empty states, with a dedicated surface-aware stylesheet rather than a new query/routing subsystem. Reuse existing content-shell/listing/card primitives; do not create a generic form framework in X2 because X5 owns broad native-form completion.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP Theme + `theme.json`, Theme CSS, WP-CLI runtime fixtures, Playwright 1.55, axe-core Playwright 4.10, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`

## Global Constraints

- Product scope is AZnet Theme only; do not implement or modify RootProfile, ConvertFlow, WooCommerce or any other provider product.
- WordPress owns search query semantics, the main query, 404 routing, archive state and native search-form submission semantics.
- Theme owns only markup composition, visual hierarchy, responsive presentation, focus presentation and generic recovery presentation.
- No `WP_Query`, `query_posts()`, `get_posts()`, `pre_get_posts`, direct `$wpdb`, private option/meta/storage reads or custom search/ranking engine.
- No redirect heuristics based on slug, title, Page ID or URL; X2 must never turn 404 recovery into authoritative routing inference.
- No SEO canonical/meta/schema/OG output.
- No new page builder, FSE takeover, build stack or JavaScript application layer.
- Theme metadata stays exactly `1.1.0`; do not modify `style.css` Version or `AZNET_THEME_VERSION` during X2.
- Keep assets surface-aware. X2-specific CSS may load only on Search, 404 and native archive/listing recovery surfaces; it must not leak into unrelated routes or WooCommerce surfaces.
- Preserve existing P3 Search/Archive native-loop contract and X1/D-027 retained regressions.
- Rollback must be presentation-only: reverting X2 files must leave WordPress queries, routes and content unchanged.
- Production implementation starts only after this plan is reviewed under the normal X2 gate.

---

## File Structure

### New files

- `assets/css/components/recovery.css` — Search/404/listing-empty presentation only: recovery panel, actions and scoped native search-form styling.
- `tests/offline/x2-search-404-empty-contract.php` — L1/L2 ownership, native-query/routing and asset-scope contract.
- `tests/runtime/x2-search-404-empty.php` — deterministic WordPress-native fixture for result, no-result, 404 and empty-archive routes.
- `tests/browser/x2-search-404-empty-l4.mjs` — responsive/keyboard/focus/overflow/a11y browser matrix.
- `.github/workflows/x2-search-404-empty.yml` — exact-candidate X2 L1-L4 workflow.
- `docs/evidence/X2_SEARCH_404_EMPTY_20260916.md` — final evidence checkpoint created only after fresh L1-L4 PASS.

### Existing files to modify

- `search.php` — retain native main loop; improve heading/context and no-results recovery composition.
- `404.php` — retain native 404 routing; improve recovery hierarchy with native search and Home action.
- `archive.php` — retain native archive loop; replace the one-line empty fallback with the shared recovery presentation.
- `inc/theme/assets.php` — add explicit X2 recovery-asset predicate/enqueue function and call it from `enqueue_assets()`.
- `scripts/verify-v1-core.sh` — add the X2 offline contract after GREEN so reusable core verification retains it.
- `docs/source/AZT-04-roadmap-qa-decisions.md` — only at final closure, move X2 to PASS and X3 to NEXT if fresh evidence supports it.
- `docs/source/AZT-EXEC-MAP.md` — only at final closure, mirror the authoritative roadmap transition.
- `docs/source/SOURCE_MANIFEST.md` — only at final closure, align source versions/exact next.

`index.php` is intentionally not redesigned in X2. Its broad fallback-loop/navigation behavior belongs to generic fallback/X4 navigation work; X2's empty-state scope is the already-established Search and Archive listing surfaces plus the 404 recovery surface. Do not widen X2 merely to make every fallback template visually identical.

---

### Task 1: Establish the X2 RED ownership and presentation contract

**Files:**
- Create: `tests/offline/x2-search-404-empty-contract.php`
- Read/retain: `tests/offline/editorial-listing-search-contract.php`
- Read/retain: `tests/offline/d027-provisioning-independence-contract.php`

**Interfaces:**
- Consumes: current `search.php`, `404.php`, `archive.php`, `inc/theme/assets.php` and Theme version declarations.
- Produces: an executable contract that defines the allowed X2 surface before production code changes.

- [ ] **Step 1: Create the failing X2 contract**

Create `tests/offline/x2-search-404-empty-contract.php` with explicit source checks. The contract must require the new recovery layer while forbidding query/routing/SEO takeover:

```php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function x2_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function x2_source(string $relative): string {
    $path = dirname(__DIR__, 2) . '/' . $relative;
    if (!is_file($path)) {
        x2_fail('missing required file: ' . $relative);
    }
    $source = file_get_contents($path);
    if (false === $source) {
        x2_fail('cannot read required file: ' . $relative);
    }
    return $source;
}

$search = x2_source('search.php');
$error404 = x2_source('404.php');
$archive = x2_source('archive.php');
$assets = x2_source('inc/theme/assets.php');
$style = x2_source('style.css');
$functions = x2_source('functions.php');
$recovery = x2_source('assets/css/components/recovery.css');

foreach ([
    'have_posts()',
    'the_post()',
    "get_template_part( 'template-parts/content/card' )",
    'the_posts_pagination()',
    'get_search_form()',
    'aznet-theme-recovery',
] as $marker) {
    if (!str_contains($search, $marker)) {
        x2_fail('search.php missing native/recovery marker: ' . $marker);
    }
}

foreach ([
    "home_url( '/' )",
    'get_search_form()',
    'aznet-theme-recovery',
    'aznet-theme-recovery__code',
    'aznet-theme-recovery__actions',
] as $marker) {
    if (!str_contains($error404, $marker)) {
        x2_fail('404.php missing recovery marker: ' . $marker);
    }
}

foreach ([
    'have_posts()',
    'the_post()',
    "get_template_part( 'template-parts/content/card' )",
    'the_posts_pagination()',
    'aznet-theme-recovery',
] as $marker) {
    if (!str_contains($archive, $marker)) {
        x2_fail('archive.php missing native/recovery marker: ' . $marker);
    }
}

foreach ([
    'should_enqueue_recovery_assets',
    'enqueue_recovery_assets',
    "is_search()",
    "is_404()",
    "is_archive()",
    "assets/css/components/recovery.css",
    "aznet-theme-recovery",
] as $marker) {
    if (!str_contains($assets . "\n" . $recovery, $marker)) {
        x2_fail('X2 asset boundary missing marker: ' . $marker);
    }
}

$production = $search . "\n" . $error404 . "\n" . $archive . "\n" . $assets;
foreach ([
    'new WP_Query', 'WP_Query(', 'query_posts(', 'get_posts(', 'pre_get_posts',
    '$wpdb', 'get_post_meta(', 'wp_redirect(', 'wp_safe_redirect(',
    '<meta name=', 'rel="canonical"', 'application/ld+json', 'schema.org',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        x2_fail('forbidden query/routing/storage/SEO marker: ' . $forbidden);
    }
}

if (!str_contains($functions, "define( 'AZNET_THEME_VERSION', '1.1.0' );")) {
    x2_fail('AZNET_THEME_VERSION must remain 1.1.0 during X2');
}
if (!str_contains($style, 'Version: 1.1.0')) {
    x2_fail('Theme stylesheet version must remain 1.1.0 during X2');
}

foreach ([
    '.aznet-theme-recovery',
    '.aznet-theme-recovery__actions',
    '.aznet-theme-recovery .search-form',
    ':focus-visible',
] as $marker) {
    if (!str_contains($recovery, $marker)) {
        x2_fail('recovery.css missing presentation marker: ' . $marker);
    }
}

echo "PASS: X2 Search/404/empty ownership and presentation contract\n";
```

- [ ] **Step 2: Run RED and prove the intended pre-change failure**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x2-search-404-empty-contract.php
```

Expected: FAIL because `assets/css/components/recovery.css` does not exist (or, if file creation order differs, because the new `aznet-theme-recovery`/asset helper markers are absent). Record the exact failing message and candidate SHA as RED evidence.

- [ ] **Step 3: Run retained P3/D-027 contracts before production edits**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/editorial-listing-search-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/d027-provisioning-independence-contract.php
```

Expected: PASS. If either is already failing, stop X2 and diagnose that unrelated regression before touching production behavior.

- [ ] **Step 4: Commit the RED contract only**

```bash
git add tests/offline/x2-search-404-empty-contract.php
git commit -m "test: define X2 search 404 empty contract"
```

---

### Task 2: Implement minimal native Search and shared recovery markup

**Files:**
- Modify: `search.php`
- Modify: `404.php`
- Modify: `archive.php`

**Interfaces:**
- Consumes: WordPress main query, `get_search_query()`, `get_search_form()`, `home_url('/')`, existing card template and pagination.
- Produces: shared `.aznet-theme-recovery*` markup with no additional query or redirect behavior.

- [ ] **Step 1: Harden Search heading and no-results hierarchy without altering the loop**

Keep the existing `have_posts()` / `the_post()` / card / `the_posts_pagination()` flow unchanged. Replace only the header/no-results composition with this shape:

```php
<header class="aznet-theme-listing__header aznet-theme-search-header">
    <p class="aznet-theme-listing__eyebrow"><?php esc_html_e( 'Search', 'aznet-theme' ); ?></p>
    <h1 id="aznet-theme-search-title" class="aznet-theme-listing__title">
        <?php
        printf(
            esc_html__( 'Search results for “%s”', 'aznet-theme' ),
            esc_html( get_search_query() )
        );
        ?>
    </h1>
    <div class="aznet-theme-search-header__form">
        <?php get_search_form(); ?>
    </div>
</header>
```

For the no-results branch, use:

```php
<div class="aznet-theme-recovery aznet-theme-recovery--search" role="status">
    <h2 class="aznet-theme-recovery__title"><?php esc_html_e( 'No matching results found', 'aznet-theme' ); ?></h2>
    <p class="aznet-theme-recovery__description">
        <?php esc_html_e( 'Try a shorter phrase or different keywords.', 'aznet-theme' ); ?>
    </p>
    <div class="aznet-theme-recovery__search">
        <?php get_search_form(); ?>
    </div>
</div>
```

Do not calculate or claim result counts. Do not alter search terms or run a second query.

- [ ] **Step 2: Replace the 404 numeric-heading-only layout with recovery semantics**

Keep WordPress's 404 request/status untouched. Use the existing shell but compose:

```php
<section class="aznet-theme-recovery aznet-theme-recovery--404" aria-labelledby="aznet-theme-404-title">
    <p class="aznet-theme-recovery__code" aria-hidden="true">404</p>
    <h1 id="aznet-theme-404-title" class="aznet-theme-recovery__title">
        <?php esc_html_e( 'Page not found', 'aznet-theme' ); ?>
    </h1>
    <p class="aznet-theme-recovery__description">
        <?php esc_html_e( 'The address may have changed, or the page may no longer be available. You can search the site or return to the homepage.', 'aznet-theme' ); ?>
    </p>
    <div class="aznet-theme-recovery__search">
        <?php get_search_form(); ?>
    </div>
    <div class="aznet-theme-recovery__actions">
        <a class="aznet-theme-recovery__action" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php esc_html_e( 'Back to home', 'aznet-theme' ); ?>
        </a>
    </div>
</section>
```

Do not add automatic redirect logic or guessed destination links.

- [ ] **Step 3: Upgrade only the Archive empty branch**

Preserve the current archive header, loop, cards and pagination. Replace the one-line empty paragraph with:

```php
<div class="aznet-theme-recovery aznet-theme-recovery--empty" role="status">
    <h2 class="aznet-theme-recovery__title"><?php esc_html_e( 'No content is available here yet', 'aznet-theme' ); ?></h2>
    <p class="aznet-theme-recovery__description"><?php esc_html_e( 'You can return to the homepage and continue browsing from there.', 'aznet-theme' ); ?></p>
    <div class="aznet-theme-recovery__actions">
        <a class="aznet-theme-recovery__action" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php esc_html_e( 'Back to home', 'aznet-theme' ); ?>
        </a>
    </div>
</div>
```

- [ ] **Step 4: Run PHP lint and the retained native-loop contract**

```bash
php -l search.php
php -l 404.php
php -l archive.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/editorial-listing-search-contract.php
```

Expected: all PASS. If the retained P3 contract fails, fix the markup without weakening or deleting its native-loop assertions.

- [ ] **Step 5: Commit the bounded markup change**

```bash
git add search.php 404.php archive.php
git commit -m "feat: improve native search and recovery markup"
```

---

### Task 3: Add the surface-aware X2 recovery stylesheet

**Files:**
- Create: `assets/css/components/recovery.css`
- Modify: `inc/theme/assets.php`
- Test: `tests/offline/x2-search-404-empty-contract.php`

**Interfaces:**
- Consumes: existing `--aznet-theme-*` tokens and WordPress conditionals `is_search()`, `is_404()`, `is_archive()`.
- Produces: `should_enqueue_recovery_assets(): bool` and `enqueue_recovery_assets(?string $version = null): void`.

- [ ] **Step 1: Add the recovery asset predicate**

In `inc/theme/assets.php`, add:

```php
/** Determine whether the native Search/404/archive recovery presentation can render. */
function should_enqueue_recovery_assets(): bool {
    if ( ! function_exists( 'is_search' ) || ! function_exists( 'is_404' ) || ! function_exists( 'is_archive' ) ) {
        return false;
    }

    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    return is_search() || is_404() || is_archive();
}

/** Enqueue Search/404/archive recovery presentation only on its native surfaces. */
function enqueue_recovery_assets( ?string $version = null ): void {
    if ( ! should_enqueue_recovery_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-recovery',
        get_theme_file_uri( '/assets/css/components/recovery.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/recovery.css', $version )
    );
}
```

Call `enqueue_recovery_assets( $version );` immediately after the generic-content enqueue block so its dependency is guaranteed on these surfaces.

Do not add `is_home()` in X2; `index.php` is intentionally not part of this bounded slice.

- [ ] **Step 2: Add the scoped recovery CSS**

Create `assets/css/components/recovery.css` with token-based presentation only:

```css
.aznet-theme-listing__eyebrow,
.aznet-theme-recovery__code {
    margin: 0 0 var(--aznet-theme-space-2);
    color: var(--aznet-theme-accent);
    font-size: var(--aznet-theme-font-size-sm);
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.aznet-theme-search-header__form {
    width: min(100%, 42rem);
    margin-block-start: var(--aznet-theme-space-4);
}

.aznet-theme-recovery {
    width: min(100%, var(--aznet-theme-container-content));
    padding: clamp(var(--aznet-theme-space-4), 5vw, var(--aznet-theme-space-7));
    color: var(--aznet-theme-text);
    background: var(--aznet-theme-surface);
    border: 1px solid var(--aznet-theme-border);
    border-radius: var(--aznet-theme-radius-card);
}

.aznet-theme-recovery--404 {
    margin-inline: auto;
    margin-block: var(--aznet-theme-space-section);
    text-align: center;
}

.aznet-theme-recovery__title {
    margin: 0;
    overflow-wrap: anywhere;
}

.aznet-theme-recovery__description {
    max-width: 42rem;
    margin: var(--aznet-theme-space-3) auto 0;
    color: var(--aznet-theme-muted);
}

.aznet-theme-recovery__search {
    width: min(100%, 36rem);
    margin: var(--aznet-theme-space-4) auto 0;
}

.aznet-theme-recovery__actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: var(--aznet-theme-space-2);
    margin-block-start: var(--aznet-theme-space-4);
}

.aznet-theme-recovery__action {
    display: inline-flex;
    align-items: center;
    min-height: 2.75rem;
    padding-inline: var(--aznet-theme-space-4);
    color: var(--aznet-theme-surface);
    background: var(--aznet-theme-accent);
    border-radius: var(--aznet-theme-radius-control);
    text-decoration: none;
}

.aznet-theme-recovery__action:focus-visible,
.aznet-theme-recovery .search-form :is(input, button):focus-visible,
.aznet-theme-search-header__form .search-form :is(input, button):focus-visible {
    outline: 2px solid var(--aznet-theme-focus);
    outline-offset: 3px;
}

.aznet-theme-recovery .search-form,
.aznet-theme-search-header__form .search-form {
    display: flex;
    flex-wrap: wrap;
    gap: var(--aznet-theme-space-2);
}

.aznet-theme-recovery .search-field,
.aznet-theme-search-header__form .search-field {
    min-width: min(100%, 16rem);
    flex: 1 1 16rem;
    min-height: 2.75rem;
    max-width: 100%;
}

.aznet-theme-recovery .search-submit,
.aznet-theme-search-header__form .search-submit {
    min-height: 2.75rem;
}

@media (max-width: 30rem) {
    .aznet-theme-recovery .search-field,
    .aznet-theme-recovery .search-submit,
    .aznet-theme-search-header__form .search-field,
    .aznet-theme-search-header__form .search-submit {
        width: 100%;
        flex-basis: 100%;
    }
}
```

If `--aznet-theme-radius-control` does not exist in the current token set, use an existing verified radius token instead; do not add a new design-token decision inside X2 solely for this control.

- [ ] **Step 3: Run GREEN contract and asset-scope checks**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x2-search-404-empty-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/editorial-listing-search-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/d027-provisioning-independence-contract.php
php -l inc/theme/assets.php
```

Expected: PASS.

- [ ] **Step 4: Add the X2 contract to reusable core verification**

Append after P3 listing/search verification in `scripts/verify-v1-core.sh`:

```bash
printf '%s\n' '==> X2 Search / 404 / Empty States contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x2-search-404-empty-contract.php
```

Then run:

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS. Do not weaken retained tests to reach GREEN.

- [ ] **Step 5: Commit the recovery asset layer**

```bash
git add assets/css/components/recovery.css inc/theme/assets.php scripts/verify-v1-core.sh
git commit -m "feat: add scoped X2 recovery presentation"
```

---

### Task 4: Add real WordPress 6.9 runtime fixtures and route assertions

**Files:**
- Create: `tests/runtime/x2-search-404-empty.php`

**Interfaces:**
- Consumes: WordPress native post/category APIs only to seed disposable test data.
- Produces: JSON with `result_url`, `empty_search_url`, `empty_archive_url`, `not_found_url`, and seeded object IDs.

- [ ] **Step 1: Seed deterministic native content without changing query behavior**

Create a runtime fixture that inserts one published native Post titled `X2 Recovery Search Target`, creates one empty native Category named `X2 Empty Archive`, and returns normal WordPress URLs:

```php
<?php

declare(strict_types=1);

$post_id = wp_insert_post([
    'post_type' => 'post',
    'post_status' => 'publish',
    'post_title' => 'X2 Recovery Search Target',
    'post_content' => 'Native WordPress content for the X2 search result fixture.',
], true);

if (is_wp_error($post_id) || (int) $post_id <= 0) {
    fwrite(STDERR, "FAIL: unable to create X2 Post\n");
    exit(1);
}

$term = wp_insert_term('X2 Empty Archive', 'category');
if (is_wp_error($term)) {
    fwrite(STDERR, "FAIL: unable to create X2 empty Category\n");
    exit(1);
}

$term_id = (int) $term['term_id'];

$result = [
    'post_id' => (int) $post_id,
    'term_id' => $term_id,
    'result_url' => home_url('/?s=' . rawurlencode('X2 Recovery Search Target')),
    'empty_search_url' => home_url('/?s=' . rawurlencode('X2 Definitely Missing Phrase')),
    'empty_archive_url' => get_category_link($term_id),
    'not_found_url' => home_url('/x2-definitely-not-a-real-route/'),
];

if (is_wp_error($result['empty_archive_url'])) {
    fwrite(STDERR, "FAIL: unable to resolve X2 Category URL\n");
    exit(1);
}

echo wp_json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
```

- [ ] **Step 2: Lint the fixture**

```bash
php -l tests/runtime/x2-search-404-empty.php
```

Expected: PASS.

- [ ] **Step 3: On clean WordPress 6.9, assert native route truth**

After installing/activating the exact candidate with zero active plugins, run:

```bash
wp eval-file tests/runtime/x2-search-404-empty.php --path=/tmp/wp-site --allow-root > /tmp/x2/fixture.json
jq -e '.post_id > 0 and .term_id > 0 and (.result_url | length > 0) and (.empty_search_url | length > 0) and (.empty_archive_url | length > 0) and (.not_found_url | length > 0)' /tmp/x2/fixture.json
```

Start the PHP server and verify:

```bash
curl -fsSL "$(jq -r '.result_url' /tmp/x2/fixture.json)" > /tmp/x2/search-result.html
curl -fsSL "$(jq -r '.empty_search_url' /tmp/x2/fixture.json)" > /tmp/x2/search-empty.html
curl -fsSL "$(jq -r '.empty_archive_url' /tmp/x2/fixture.json)" > /tmp/x2/archive-empty.html
curl -sS -o /tmp/x2/404.html -w '%{http_code}' "$(jq -r '.not_found_url' /tmp/x2/fixture.json)" > /tmp/x2/404-status.txt

grep -F 'X2 Recovery Search Target' /tmp/x2/search-result.html
grep -F 'aznet-theme-recovery--search' /tmp/x2/search-empty.html
grep -F 'aznet-theme-recovery--empty' /tmp/x2/archive-empty.html
grep -F 'aznet-theme-recovery--404' /tmp/x2/404.html
test "$(cat /tmp/x2/404-status.txt)" = '404'
```

Also assert `aznet-theme-recovery-css` appears on Search/404/Archive routes and does not appear on the homepage or a singular Post outside those surfaces.

- [ ] **Step 4: Commit runtime fixture**

```bash
git add tests/runtime/x2-search-404-empty.php
git commit -m "test: add X2 native runtime fixtures"
```

---

### Task 5: Add responsive, keyboard and accessibility browser verification

**Files:**
- Create: `tests/browser/x2-search-404-empty-l4.mjs`

**Interfaces:**
- Consumes: four URLs from `X2_STATE_DIR/fixture.json` and a running zero-plugin WordPress site.
- Produces: screenshots, axe JSON and `summary.json` under `X2_L4_DIR`.

- [ ] **Step 1: Build a four-surface browser matrix**

Use Playwright Chromium and `@axe-core/playwright`. Test at least:

```js
const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};
```

For every viewport:

- result Search route returns HTTP 200, has exactly one page H1, native result card title is present, recovery empty panel is absent;
- empty Search route returns HTTP 200, has exactly one H1, `.aznet-theme-recovery--search` and a usable native search form;
- empty Archive route returns HTTP 200, has exactly one H1 and `.aznet-theme-recovery--empty`;
- 404 route returns HTTP 404, has exactly one H1 with `Page not found`, visible `404` code, native search form and Home action;
- `document.documentElement.scrollWidth - clientWidth <= 1`;
- no console errors or `pageerror` events;
- axe has zero `critical` or `serious` violations.

- [ ] **Step 2: Verify keyboard/focus on recovery controls**

On the 404 and empty Search routes, Tab through the native search input/button and Home action. Use `page.evaluate()` or locator checks to confirm each focusable control receives focus and is not clipped outside the viewport. Capture a focused screenshot at one desktop and one mobile viewport.

Do not prescribe the global form system here. X2 verifies only the scoped search/recovery controls that X2 renders; X5 remains owner of broad native-form primitives.

- [ ] **Step 3: Write machine-readable evidence**

Write `summary.json` containing route, viewport, HTTP status, H1 count, horizontal overflow, axe blocking count and browser-error count for each case. Save axe JSON per route/viewport.

- [ ] **Step 4: Run the browser matrix against the local WordPress fixture**

```bash
X2_STATE_DIR=/tmp/x2 \
X2_L4_DIR=/tmp/x2-l4 \
node tests/browser/x2-search-404-empty-l4.mjs
```

Expected: PASS for all 16 route/viewport combinations, zero critical/serious axe findings, zero horizontal overflow and zero browser errors.

- [ ] **Step 5: Commit browser coverage**

```bash
git add tests/browser/x2-search-404-empty-l4.mjs
git commit -m "test: cover X2 browser recovery surfaces"
```

---

### Task 6: Add exact-candidate X2 CI and close the feature gate

**Files:**
- Create: `.github/workflows/x2-search-404-empty.yml`
- Modify: none outside X2 test/workflow files in this task.

**Interfaces:**
- Consumes: Task 1-5 contract/runtime/browser files and current reusable core verifier.
- Produces: fresh L1-L4 evidence on the exact candidate SHA.

- [ ] **Step 1: Create an X2 workflow modeled on the proven X1 structure**

Use `ubuntu-24.04`, MySQL 8, PHP 8.1, WordPress 6.9, Node 22, Playwright 1.55 and axe-core 4.10.2. Trigger on the X2 feature branch and pull requests to `main` when X2 files change.

The static step must include:

```bash
set -euo pipefail
git fetch origin main:refs/remotes/origin/main
git diff --check origin/main...HEAD
changed="$(git diff --name-only origin/main...HEAD)"
if printf '%s\n' "$changed" | grep -E '^(inc/integrations/|assets/css/integrations/)'; then
  echo 'FAIL: X2 must not change provider integration files' >&2
  exit 1
fi
if printf '%s\n' "$changed" | grep -E '^(functions\.php|style\.css)$'; then
  echo 'FAIL: X2 must not change Theme version declarations' >&2
  exit 1
fi
grep -F "define( 'AZNET_THEME_VERSION', '1.1.0' );" functions.php
grep -F 'Version: 1.1.0' style.css
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x2-search-404-empty-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/editorial-listing-search-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/d027-provisioning-independence-contract.php
php -l search.php
php -l 404.php
php -l archive.php
php -l inc/theme/assets.php
php -l tests/runtime/x2-search-404-empty.php
```

- [ ] **Step 2: Install zero-plugin WordPress 6.9 and run runtime/browser gates**

Reuse the X1 workflow installation pattern, changing only test names/state directories. Required checks:

- zero active plugins;
- exact Theme candidate activated;
- runtime fixture generated successfully;
- Search result/empty, empty Archive and 404 HTTP semantics match Task 4;
- X2 CSS is scoped to expected routes and absent from Home/singular Post;
- Playwright/axe matrix from Task 5 passes;
- server log contains no PHP fatal/parse/uncaught errors and no unexpected warnings.

- [ ] **Step 3: Upload all X2 evidence even on failure**

Upload `/tmp/x2`, `/tmp/x2-l4` and the exact candidate SHA as `x2-search-404-empty-evidence` using `if: always()`.

- [ ] **Step 4: Run the full reusable core regression on the exact functional head**

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS, including the new X2 contract and all retained prior contracts.

- [ ] **Step 5: Commit workflow**

```bash
git add .github/workflows/x2-search-404-empty.yml
git commit -m "ci: verify X2 search 404 empty surfaces"
```

---

### Task 7: Record evidence, reconcile source state and stop at the merge gate

**Files:**
- Create: `docs/evidence/X2_SEARCH_404_EMPTY_20260916.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: final functional SHA plus fresh exact-head L1-L4 workflow IDs/artifacts.
- Produces: canonical review checkpoint `X2 PASS / X3 NEXT` only if every required layer has evidence.

- [ ] **Step 1: Write the evidence record from actual runs only**

Record:

- RED SHA/run and exact intended failure;
- GREEN/final functional SHA;
- L1/L2 contract results;
- L3 WordPress 6.9 zero-plugin route/runtime result;
- L4 route/viewport matrix, keyboard/focus, overflow, axe and browser/runtime-log results;
- retained P3/D-027/full-core regression result;
- artifact IDs/digests;
- rollback statement: reverting X2 templates/CSS/assets does not mutate WordPress query/routing/content state;
- explicit UNKNOWN: provider L5, X3 implementation, v1.2 final package/release/deployment.

Do not write a PASS for a layer that lacks fresh evidence.

- [ ] **Step 2: Update only the roadmap owners of current execution state**

If and only if X2 L1-L4 and retained regressions are fresh PASS:

- AZT-04: change `X1 PASS / X2 NEXT` to `X2 PASS / X3 NEXT`, add X2 evidence checkpoint, and set Exact Next to the X3 Media/Gallery/Embed implementation plan.
- AZT-EXEC-MAP: mirror `X2 PASS` and exact Next `X3 plan`.
- SOURCE_MANIFEST: update the listed source versions and exact-next statement.

Do **not** edit AZT-01/AZT-02/AZT-05 unless implementation uncovered a genuinely new ownership/architecture/public-contract decision. This plan does not predict such a change.

- [ ] **Step 3: Verify source consistency and no accidental scope expansion**

```bash
git diff --check
rg -n "TODO|TBD|provider L5 PASS|Version: 1\.2\.0|AZNET_THEME_VERSION.*1\.2\.0" \
  docs/evidence/X2_SEARCH_404_EMPTY_20260916.md \
  docs/source/AZT-04-roadmap-qa-decisions.md \
  docs/source/AZT-EXEC-MAP.md \
  docs/source/SOURCE_MANIFEST.md \
  style.css functions.php || true
```

Review every match. The Theme version must remain `1.1.0`; provider L5 must remain unclaimed.

- [ ] **Step 4: Commit evidence/source closure separately from functional code**

```bash
git add \
  docs/evidence/X2_SEARCH_404_EMPTY_20260916.md \
  docs/source/AZT-04-roadmap-qa-decisions.md \
  docs/source/AZT-EXEC-MAP.md \
  docs/source/SOURCE_MANIFEST.md
git commit -m "docs: close X2 search 404 empty evidence"
```

- [ ] **Step 5: Run final exact-head verification before asking for merge**

Trigger/observe the X2 workflow and the repository's retained PR workflows on the final evidence head. Confirm the final head SHA and all required jobs are SUCCESS. Do not reuse an earlier functional-head PASS after documentation/workflow changes if the exit gate requires exact-final-head evidence.

- [ ] **Step 6: Stop at the explicit merge gate**

Open/update the PR with:

- bounded X2 scope;
- RED → GREEN evidence;
- exact final head SHA;
- L1-L4 workflow IDs;
- retained regression result;
- ownership/rollback statement;
- explicit non-claims for provider L5/release/deploy.

Do not merge `main` without product-owner approval.

---

## Self-Review

### Spec coverage

- Search query semantics/main query remain WordPress-owned: Tasks 1, 2, 4 and 6 explicitly retain the native loop and forbid query replacement.
- 404 routing remains WordPress-owned: Tasks 1, 2, 4 and 5 require real HTTP 404 and forbid redirect heuristics.
- Clear headings/layout/search-form/result-empty hierarchy/recovery actions: Tasks 2 and 3.
- Focus/responsive/a11y: Tasks 3 and 5.
- Surface-aware assets: Tasks 1, 3, 4 and 6.
- No custom search engine/provider/private storage/SEO takeover: Global Constraints and Tasks 1/6.
- RED → GREEN + retained regression: Tasks 1, 3 and 6.
- Real WordPress L3 + browser L4: Tasks 4 and 5.
- Rollback/recovery checkpoint: Global Constraints and Task 7 evidence requirements.
- Exactly one next after PASS: Task 7 sets X3 Media/Gallery/Embed implementation plan as Next.
- Theme metadata remains 1.1.0: Global Constraints, Tasks 1 and 6.

### Placeholder scan

No `TODO`, `TBD`, “implement later”, unspecified test instruction or unnamed error-handling step is part of this plan. Each production behavior has an explicit file, interface, verification command and acceptance condition.

### Type/name consistency

- Asset predicate: `should_enqueue_recovery_assets(): bool`.
- Asset enqueuer: `enqueue_recovery_assets(?string $version = null): void`.
- CSS handle/class family: `aznet-theme-recovery` / `.aznet-theme-recovery*`.
- Runtime state: `/tmp/x2/fixture.json`.
- Browser evidence: `/tmp/x2-l4/summary.json`.
- Workflow/evidence names use `x2-search-404-empty` consistently.

## Review Gate

This document is the X2 implementation plan required by canonical AZT-04/AZT-EXEC-MAP. Plan approval is the next gate. Production X2 code must not begin before that review gate is cleared.

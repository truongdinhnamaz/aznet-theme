# X4 Pagination & Navigation Completion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete production-ready native pagination/navigation presentation for archive/search result pagination, native Post page links, adjacent Post navigation, and comment pagination while WordPress remains authoritative for pagination state, URLs, queries, comments, and content lifecycle.

**Architecture:** Add one bounded shared Theme presentation module, `assets/css/components/navigation.css`, loaded only on native archive, search, and single Post surfaces. Keep WordPress Core APIs as the sole source of links/state: `the_posts_pagination()`, `wp_link_pages()`, `the_post_navigation()`, and `the_comments_pagination()`. X4 adds only labels, Theme-owned CSS, focus/responsive presentation, and verification; it adds no query engine, URL builder, redirect heuristic, JavaScript navigation engine, provider integration, or parallel state.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, CSS, Bash/PHP contracts, WP-CLI fixtures, Node 22, `playwright@1.55.0`, `@axe-core/playwright@4.10.2`, MySQL 8, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Plan base: canonical `main@358a54e8e549fb79bf645ea51f35ed8e86fe4aab`, the owner-approved PR #81 merge commit.
- Fresh exact-main verification: run `35080332135` SUCCESS on that exact SHA; static artifact `10440285818` (`sha256:86ef5733e95d87762f1e006085c46132f9390f63f3e925142ad2d7a175c05820`) and clean runtime/browser artifact `10439528105` (`sha256:f309b8ebe27236de166e687d059f0c3622a0b5a9812f599fb4e510ec2c43d626`).
- Governing rule: **SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.**
- WordPress owns pagination/navigation state, URLs, main-query semantics, Post content pagination, adjacent-Post semantics, comments, and comment pagination.
- Theme owns presentation only: labels where WordPress expects Theme arguments, CSS, responsive geometry, focus-visible presentation, and accessible visual hierarchy.
- WordPress floor remains `6.9+`; PHP floor remains `8.1+`.
- Theme metadata remains exactly `1.1.0` during X4. Promotion to `1.2.0` belongs only to X6.
- Provider L5 is outside X4. No RootProfile, ConvertFlow, WooCommerce, SEO-provider, private provider selectors/storage/API logic, or provider certification claims.
- Forbidden production behavior: `WP_Query`, `query_posts()`, `get_posts()`, `pre_get_posts`, custom pagination state, request-derived URL construction, redirects, REST/AJAX pagination, JavaScript pagination, infinite scroll, or a navigation application framework.
- Do not redesign `index.php`, `page.php`, Header navigation, WooCommerce pagination, or provider-owned surfaces in X4.
- No new build/bundler stack.
- New Theme assets must be surface-aware; no global X4 bundle.
- Production work follows RED -> intended failure -> minimal GREEN -> retained regression -> L3 -> L4.
- Deeper failures must be reduced to the shallowest stable reproduction before production changes.

---

## File Map

### Create
- `assets/css/components/navigation.css` — shared X4 native pagination/navigation presentation.
- `tests/offline/x4-pagination-navigation-contract.php` — RED/GREEN ownership/presentation contract.
- `tests/runtime/x4-pagination-navigation.php` — deterministic native WordPress fixture.
- `tests/browser/x4-pagination-navigation-l4.mjs` — 20-case responsive/keyboard/focus/overflow/a11y matrix.
- `.github/workflows/x4-pagination-navigation.yml` — focused X4 support-floor workflow.
- `docs/evidence/X4_PAGINATION_NAVIGATION_20260916.md` — only after fresh final functional-head PASS.

### Modify
- `archive.php` — retain native Loop and `the_posts_pagination()`; add explicit labels/text only.
- `search.php` — retain native main query and `the_posts_pagination()`; add explicit labels/text only.
- `template-parts/content/content-single.php` — retain `wp_link_pages()` and `the_post_navigation()`; add visible page-link summary and labels.
- `comments.php` — retain `the_comments_pagination()`; add label only.
- `inc/theme/assets.php` — X4 surface detection/enqueue.
- `scripts/verify-v1-core.sh` — add X4 contract.
- Closure only after fresh PASS: `docs/source/AZT-04-roadmap-qa-decisions.md`, `docs/source/AZT-EXEC-MAP.md`, `docs/source/SOURCE_MANIFEST.md`.

### Intentionally unchanged
- `index.php`, `page.php`, `assets/js/*`, WooCommerce templates/assets, provider adapters.

---

## Task 1: RED — define the X4 ownership and presentation contract

**Files:**
- Create: `tests/offline/x4-pagination-navigation-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes current native navigation calls and Theme asset registry.
- Produces a deterministic contract requiring the new X4 CSS/helper surface while forbidding query/URL/provider takeover.

- [ ] **Step 1: Create the failing contract**

Create `tests/offline/x4-pagination-navigation-contract.php`:

```php
<?php
$root = dirname( __DIR__, 2 );

$read = static function ( string $path ) use ( $root ): string {
    $full = $root . '/' . $path;
    if ( ! is_file( $full ) ) {
        throw new RuntimeException( 'Missing required X4 file: ' . $path );
    }
    $source = file_get_contents( $full );
    if ( false === $source ) {
        throw new RuntimeException( 'Unable to read X4 file: ' . $path );
    }
    return $source;
};

$archive  = $read( 'archive.php' );
$search   = $read( 'search.php' );
$single   = $read( 'template-parts/content/content-single.php' );
$comments = $read( 'comments.php' );
$assets   = $read( 'inc/theme/assets.php' );
$css      = $read( 'assets/css/components/navigation.css' );
$style    = $read( 'style.css' );
$funcs    = $read( 'functions.php' );

$required = [
    'archive.php' => [ 'have_posts()', 'the_post()', 'the_posts_pagination(', "'aria_label'", "esc_attr__( 'Archive pagination', 'aznet-theme' )" ],
    'search.php' => [ 'have_posts()', 'the_post()', 'the_posts_pagination(', "'aria_label'", "esc_attr__( 'Search results pagination', 'aznet-theme' )" ],
    'template-parts/content/content-single.php' => [ 'the_content()', 'wp_link_pages(', 'aznet-theme-article__page-links', 'aznet-theme-navigation__summary', 'the_post_navigation(', "esc_attr__( 'Post navigation', 'aznet-theme' )" ],
    'comments.php' => [ 'wp_list_comments(', 'the_comments_pagination(', "esc_attr__( 'Comments pagination', 'aznet-theme' )" ],
    'inc/theme/assets.php' => [ 'function should_enqueue_navigation_assets(): bool', 'function enqueue_navigation_assets( ?string $version = null ): void', "'aznet-theme-navigation'", "'/assets/css/components/navigation.css'", 'enqueue_navigation_assets( $version );' ],
];

$sources = [
    'archive.php' => $archive,
    'search.php' => $search,
    'template-parts/content/content-single.php' => $single,
    'comments.php' => $comments,
    'inc/theme/assets.php' => $assets,
];

foreach ( $required as $path => $needles ) {
    foreach ( $needles as $needle ) {
        if ( false === strpos( $sources[ $path ], $needle ) ) {
            throw new RuntimeException( $path . ' missing X4 token: ' . $needle );
        }
    }
}

foreach ( [
    '.aznet-theme-listing__pagination .page-numbers',
    '.aznet-theme-article__page-links .post-page-numbers',
    '.aznet-theme-comments .comments-pagination .page-numbers',
    '.aznet-theme-navigation__summary',
    ':focus-visible',
    '.current',
    '@media (max-width: 47.999rem)',
] as $needle ) {
    if ( false === strpos( $css, $needle ) ) {
        throw new RuntimeException( 'navigation.css missing X4 selector/token: ' . $needle );
    }
}

$production = implode( "\n", [ $archive, $search, $single, $comments, $assets ] );
foreach ( [
    'new WP_Query',
    'query_posts(',
    'get_posts(',
    'pre_get_posts',
    'wp_redirect(',
    'wp_safe_redirect(',
    '$_GET',
    '$_SERVER[\'REQUEST_URI\']',
    '$wpdb',
    'choiceguide_',
    'rootprofile_',
] as $needle ) {
    if ( false !== strpos( $production, $needle ) ) {
        throw new RuntimeException( 'X4 ownership violation: ' . $needle );
    }
}

if ( false === strpos( $style, 'Version: 1.1.0' ) ) {
    throw new RuntimeException( 'X4 must not promote style.css metadata before X6.' );
}
if ( false === strpos( $funcs, "define( 'AZNET_THEME_VERSION', '1.1.0' );" ) ) {
    throw new RuntimeException( 'X4 must not promote AZNET_THEME_VERSION before X6.' );
}

echo "PASS: X4 pagination/navigation ownership and presentation contract\n";
```

- [ ] **Step 2: Wire the contract into the reusable verifier**

Immediately after X3 in `scripts/verify-v1-core.sh` add:

```bash
printf '%s\n' '==> X4 Pagination & Navigation Completion contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x4-pagination-navigation-contract.php
```

- [ ] **Step 3: Run RED and require the intended reason**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x4-pagination-navigation-contract.php
```

Expected pre-GREEN result: FAIL because `assets/css/components/navigation.css` is missing. A syntax error, provider failure, or unrelated retained failure is not valid RED evidence.

- [ ] **Step 4: Commit RED only**

```bash
git add tests/offline/x4-pagination-navigation-contract.php scripts/verify-v1-core.sh
git commit -m "test: define X4 pagination navigation contract"
```

Record exact RED SHA/output for closure evidence.

---

## Task 2: Minimal native markup labels

**Files:** `archive.php`, `search.php`, `template-parts/content/content-single.php`, `comments.php`

**Interfaces:** WordPress still generates every target URL/current state; X4 only supplies labels/presentation hooks.

- [ ] **Step 1: Archive pagination**

Replace the zero-argument pagination call with:

```php
the_posts_pagination(
    [
        'mid_size'   => 1,
        'prev_text'  => esc_html__( 'Previous', 'aznet-theme' ),
        'next_text'  => esc_html__( 'Next', 'aznet-theme' ),
        'aria_label' => esc_attr__( 'Archive pagination', 'aznet-theme' ),
    ]
);
```

Do not touch the Loop/query/card/empty state.

- [ ] **Step 2: Search pagination**

Use the same native call with:

```php
'aria_label' => esc_attr__( 'Search results pagination', 'aznet-theme' ),
```

Do not add a custom search query or URL builder.

- [ ] **Step 3: Paginated native Post page links**

Retain `wp_link_pages()` and change only its wrapper arguments:

```php
wp_link_pages(
    [
        'before' => '<nav class="aznet-theme-article__page-links" aria-label="' . esc_attr__( 'Article pages', 'aznet-theme' ) . '"><span class="aznet-theme-navigation__summary">' . esc_html__( 'Pages:', 'aznet-theme' ) . '</span>',
        'after'  => '</nav>',
    ]
);
```

Do not construct page URLs or read request globals.

- [ ] **Step 4: Adjacent Post navigation**

Keep the existing `prev_text`/`next_text` and add:

```php
'aria_label' => esc_attr__( 'Post navigation', 'aznet-theme' ),
```

Retain `the_post_navigation()` as the only generator.

- [ ] **Step 5: Comment pagination**

Keep the current `prev_text`/`next_text` and add:

```php
'aria_label' => esc_attr__( 'Comments pagination', 'aznet-theme' ),
```

Retain `the_comments_pagination()`; do not build `cpage` URLs.

- [ ] **Step 6: Lint and commit**

```bash
php -l archive.php
php -l search.php
php -l template-parts/content/content-single.php
php -l comments.php
git add archive.php search.php template-parts/content/content-single.php comments.php
git commit -m "feat: clarify native pagination navigation labels"
```

Expected: four lint PASS results.

---

## Task 3: Minimal GREEN shared navigation asset

**Files:**
- Create: `assets/css/components/navigation.css`
- Modify: `inc/theme/assets.php`

**Interfaces:**
- Produces `should_enqueue_navigation_assets(): bool` and `enqueue_navigation_assets(?string $version = null): void`.
- True only for native archive/search/single Post; false for Woo current surfaces and other routes.

- [ ] **Step 1: Create the CSS module**

```css
.aznet-theme-listing__pagination .nav-links,
.aznet-theme-comments .comments-pagination .nav-links,
.aznet-theme-article__page-links {
    display: flex;
    flex-wrap: wrap;
    gap: var(--aznet-theme-space-2);
    align-items: center;
    min-width: 0;
}

.aznet-theme-navigation__summary {
    color: var(--aznet-theme-muted);
    font-size: var(--aznet-theme-font-size-sm);
    font-weight: 600;
}

.aznet-theme-listing__pagination .page-numbers,
.aznet-theme-comments .comments-pagination .page-numbers,
.aznet-theme-article__page-links .post-page-numbers {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.75rem;
    min-height: 2.75rem;
    max-width: 100%;
    padding: var(--aznet-theme-space-2) var(--aznet-theme-space-3);
    color: var(--aznet-theme-text);
    text-decoration: none;
    overflow-wrap: anywhere;
    background: var(--aznet-theme-surface);
    border: 1px solid var(--aznet-theme-border);
    border-radius: var(--aznet-theme-radius-control);
}

.aznet-theme-listing__pagination a.page-numbers:hover,
.aznet-theme-comments .comments-pagination a.page-numbers:hover,
.aznet-theme-article__page-links a.post-page-numbers:hover {
    color: var(--aznet-theme-accent);
    border-color: var(--aznet-theme-accent);
}

.aznet-theme-listing__pagination .page-numbers.current,
.aznet-theme-comments .comments-pagination .page-numbers.current,
.aznet-theme-article__page-links .post-page-numbers.current {
    font-weight: 700;
    border-color: var(--aznet-theme-accent);
}

.aznet-theme-listing__pagination a.page-numbers:focus-visible,
.aznet-theme-comments .comments-pagination a.page-numbers:focus-visible,
.aznet-theme-article__page-links a.post-page-numbers:focus-visible,
.aznet-theme-article .post-navigation a:focus-visible {
    outline: 2px solid var(--aznet-theme-focus);
    outline-offset: 2px;
}

.aznet-theme-listing__pagination .prev,
.aznet-theme-listing__pagination .next,
.aznet-theme-comments .comments-pagination .prev,
.aznet-theme-comments .comments-pagination .next {
    min-width: 0;
}

@media (max-width: 47.999rem) {
    .aznet-theme-listing__pagination .nav-links,
    .aznet-theme-comments .comments-pagination .nav-links,
    .aznet-theme-article__page-links {
        gap: var(--aznet-theme-space-1) var(--aznet-theme-space-2);
    }

    .aznet-theme-listing__pagination .page-numbers,
    .aznet-theme-comments .comments-pagination .page-numbers,
    .aznet-theme-article__page-links .post-page-numbers {
        min-width: 2.5rem;
        min-height: 2.5rem;
        padding-inline: var(--aznet-theme-space-2);
    }
}
```

Do not refactor retained P2 article-navigation rules from `article.css`; X4 does not reopen them absent a failing regression.

- [ ] **Step 2: Add surface detection/enqueue**

Add before `enqueue_assets()` in `inc/theme/assets.php`:

```php
/** Determine whether native pagination/navigation presentation can render. */
function should_enqueue_navigation_assets(): bool {
    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    $is_archive = function_exists( 'is_archive' ) && is_archive();
    $is_search  = function_exists( 'is_search' ) && is_search();
    $is_post    = function_exists( 'is_singular' ) && is_singular( 'post' );

    return $is_archive || $is_search || $is_post;
}

/** Enqueue shared native pagination/navigation presentation only on X4 surfaces. */
function enqueue_navigation_assets( ?string $version = null ): void {
    if ( ! should_enqueue_navigation_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-navigation',
        get_theme_file_uri( '/assets/css/components/navigation.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/navigation.css', $version )
    );
}
```

Call `enqueue_navigation_assets( $version );` after the generic-content enqueue block and before media/recovery/article/comments additions.

- [ ] **Step 3: Prove GREEN and retained static regression**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x4-pagination-navigation-contract.php
bash scripts/verify-v1-core.sh
```

Expected: both PASS.

- [ ] **Step 4: Commit**

```bash
git add assets/css/components/navigation.css inc/theme/assets.php
git commit -m "feat: add native pagination navigation presentation"
```

---

## Task 4: Real WordPress 6.9 runtime fixture

**File:** Create `tests/runtime/x4-pagination-navigation.php`

**Interfaces:** Creates only test state through WordPress APIs. Produces base route JSON; browser tests follow WordPress-generated pagination links instead of constructing pagination URLs.

- [ ] **Step 1: Create deterministic fixture state**

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

update_option( 'posts_per_page', 3 );
update_option( 'page_comments', 1 );
update_option( 'comments_per_page', 2 );
update_option( 'default_comments_page', 'oldest' );

$term = wp_insert_term( 'X4 Navigation Archive', 'category', [ 'description' => 'Native X4 pagination fixture.' ] );
if ( is_wp_error( $term ) ) {
    WP_CLI::error( 'Unable to create X4 category fixture: ' . $term->get_error_message() );
}
$category_id = (int) $term['term_id'];

$post_ids = [];
for ( $index = 1; $index <= 8; $index++ ) {
    $content = '<p id="x4-listing-marker-' . $index . '">X4 navigation fixture result ' . $index . '</p>';
    if ( 4 === $index ) {
        $content = '<p id="x4-post-page-one">X4 post page one marker.</p><!--nextpage--><p id="x4-post-page-two">X4 post page two marker.</p>';
    }

    $post_id = wp_insert_post(
        [
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'post_title'    => 'X4 Navigation Fixture Post ' . $index . ' — A deliberately long adjacent navigation title for responsive wrapping',
            'post_content'  => $content,
            'post_category' => [ $category_id ],
            'post_date'     => sprintf( '2026-09-%02d 09:00:00', $index ),
            'post_date_gmt' => sprintf( '2026-09-%02d 02:00:00', $index ),
        ],
        true
    );
    if ( is_wp_error( $post_id ) || 0 >= (int) $post_id ) {
        WP_CLI::error( 'Unable to create X4 Post fixture ' . $index . '.' );
    }
    $post_ids[] = (int) $post_id;
}

$target_post_id = $post_ids[3];
for ( $index = 1; $index <= 5; $index++ ) {
    $comment_id = wp_insert_comment(
        [
            'comment_post_ID'      => $target_post_id,
            'comment_author'       => 'X4 Commenter ' . $index,
            'comment_author_email' => 'x4-' . $index . '@example.test',
            'comment_content'      => 'X4 paginated comment marker ' . $index,
            'comment_approved'     => 1,
            'comment_date'         => sprintf( '2026-09-10 10:%02d:00', $index ),
            'comment_date_gmt'     => sprintf( '2026-09-10 03:%02d:00', $index ),
        ]
    );
    if ( ! $comment_id ) {
        WP_CLI::error( 'Unable to create X4 comment fixture ' . $index . '.' );
    }
}

$page_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'X4 Asset Absence Page',
        'post_content' => '<p id="x4-page-control">X4 Page control route.</p>',
    ],
    true
);
if ( is_wp_error( $page_id ) || 0 >= (int) $page_id ) {
    WP_CLI::error( 'Unable to create X4 Page control fixture.' );
}

$category_url = get_category_link( $category_id );
$post_url     = get_permalink( $target_post_id );
$page_url     = get_permalink( (int) $page_id );
if ( is_wp_error( $category_url ) || ! is_string( $post_url ) || ! is_string( $page_url ) ) {
    WP_CLI::error( 'Unable to resolve X4 fixture URLs.' );
}

$result = [
    'archive_url' => $category_url,
    'search_url'  => add_query_arg( 's', 'X4 Navigation Fixture Post', home_url( '/' ) ),
    'post_url'    => $post_url,
    'page_url'    => $page_url,
    'home_url'    => home_url( '/' ),
    'missing_url' => home_url( '/x4-navigation-definitely-missing/' ),
    'post_id'     => $target_post_id,
    'category_id' => $category_id,
];

echo wp_json_encode( $result );
```

The fixture intentionally uses test-only WordPress options. Production Theme code must never copy/own that state.

- [ ] **Step 2: Commit**

```bash
git add tests/runtime/x4-pagination-navigation.php
git commit -m "test: add X4 WordPress pagination fixture"
```

---

## Task 5: L4 browser matrix

**File:** Create `tests/browser/x4-pagination-navigation-l4.mjs`

**Interfaces:** Reads `X4_STATE_DIR/fixture.json`; follows native links generated by WordPress; writes screenshots, Axe JSON, and `summary.json` to `X4_L4_DIR`.

- [ ] **Step 1: Define imports, fixtures, helpers, and viewports**

Start the file with these concrete helpers:

```js
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const stateDir = process.env.X4_STATE_DIR || '/tmp/x4';
const outputDir = process.env.X4_L4_DIR || '/tmp/x4-l4';
const fixturePath = path.join(stateDir, 'fixture.json');
if (!fs.existsSync(fixturePath)) throw new Error(`X4 fixture is required: ${fixturePath}`);
const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
fs.mkdirSync(outputDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

function collectBrowserErrors(page) {
  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  return { consoleErrors, pageErrors };
}

async function assertCountAtLeast(locator, minimum, label) {
  const count = await locator.count();
  if (count < minimum) throw new Error(`${label}: expected at least ${minimum}, got ${count}`);
}

async function assertVisibleFocus(page, selector) {
  const target = page.locator(selector).first();
  await target.focus();
  const state = await target.evaluate((element) => {
    const style = window.getComputedStyle(element);
    const rect = element.getBoundingClientRect();
    return {
      focusVisible: element.matches(':focus-visible'),
      outlineStyle: style.outlineStyle,
      outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
      inViewport: rect.left >= -1 && rect.right <= window.innerWidth + 1,
    };
  });
  if (!state.focusVisible || state.outlineStyle === 'none' || state.outlineWidth < 1 || !state.inViewport) {
    throw new Error(`focus presentation failed for ${selector}: ${JSON.stringify(state)}`);
  }
}

async function assertPageQuality(page, caseName, viewportName) {
  const overflow = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
  if (overflow > 1) throw new Error(`${caseName}/${viewportName}: horizontal overflow ${overflow}px`);
  const axe = await new AxeBuilder({ page }).analyze();
  const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
  fs.writeFileSync(path.join(outputDir, `axe-${caseName}-${viewportName}.json`), JSON.stringify(axe, null, 2));
  if (blocking.length) throw new Error(`${caseName}/${viewportName}: blocking axe ${blocking.map((item) => item.id).join(', ')}`);
  return { overflow, blockingAxeViolations: blocking.length };
}
```

This deliberately matches the proven X3 pattern: `playwright` + `@axe-core/playwright`, not an undeclared helper/package.

- [ ] **Step 2: Implement archive-pagination case**

For each viewport, open `fixture.archive_url`, require one `.navigation.pagination` under `.aznet-theme-listing__pagination`, at least two `.page-numbers`, `aria-label="Archive pagination"`, visible focus on an anchor, then click the WordPress-generated `.next.page-numbers`. Assert URL changes and exactly one `.page-numbers.current` remains. Finish with `assertPageQuality()` and a full-page screenshot.

Concrete core:

```js
const next = page.locator('.aznet-theme-listing__pagination a.next.page-numbers');
const beforeUrl = page.url();
await assertCountAtLeast(page.locator('.aznet-theme-listing__pagination .page-numbers'), 2, 'archive page numbers');
await assertVisibleFocus(page, '.aznet-theme-listing__pagination a.page-numbers');
await next.click();
await page.waitForLoadState('networkidle');
if (page.url() === beforeUrl) throw new Error('archive native pagination URL did not change');
if (await page.locator('.aznet-theme-listing__pagination .page-numbers.current').count() !== 1) throw new Error('archive current-page state missing');
```

Do not assert a handcrafted `/page/2/` pattern.

- [ ] **Step 3: Implement search-pagination case**

Repeat the archive behavior against `fixture.search_url`, requiring `aria-label="Search results pagination"`. Follow the generated `.next.page-numbers` anchor; do not construct `paged` query args.

- [ ] **Step 4: Implement Post page-link case**

Open `fixture.post_url`, require `#x4-post-page-one`, `.aznet-theme-article__page-links[aria-label="Article pages"]`, visible focus on `a.post-page-numbers`, click the anchor whose text is `2`, then assert `#x4-post-page-two` exists after navigation. Do not construct the page-2 URL.

```js
await page.locator('.aznet-theme-article__page-links a.post-page-numbers').filter({ hasText: /^2$/ }).click();
await page.waitForLoadState('networkidle');
if (await page.locator('#x4-post-page-two').count() !== 1) throw new Error('native Post page two marker missing');
```

- [ ] **Step 5: Implement adjacent-Post case**

Open `fixture.post_url`, require `.aznet-theme-article .post-navigation[aria-label="Post navigation"]`, at least two links, positive `.nav-title` widths, no page overflow at every viewport, and visible focus. Follow the first generated adjacent link and assert the URL changes.

- [ ] **Step 6: Implement comment-pagination case**

Open `fixture.post_url`, require `.aznet-theme-comments .comments-pagination[aria-label="Comments pagination"]`, at least two page-number controls, one current state, and visible focus. Capture comment texts, click the first generated pagination anchor, wait for navigation, capture texts again, and require the set to change.

```js
const before = await page.locator('.aznet-theme-comments .comment-content').allTextContents();
await page.locator('.aznet-theme-comments .comments-pagination a.page-numbers').first().click();
await page.waitForLoadState('networkidle');
const after = await page.locator('.aznet-theme-comments .comment-content').allTextContents();
if (JSON.stringify(before) === JSON.stringify(after)) throw new Error('native comment pagination did not change comment page');
```

- [ ] **Step 7: Assert X4 asset scope**

Across the 20 behavior/viewport cases, require `#aznet-theme-navigation-css` on archive/search/Post. Separately run one control check at 1440px requiring it absent on `fixture.home_url`, `fixture.page_url`, and `fixture.missing_url`.

- [ ] **Step 8: Persist summary and fail deterministically**

Use a `summary = { fixture, cases: [] }` plus `failures = []`. Each case records behavior, viewport, URL-change result, overflow, blocking Axe count, browser-error count, and status. Save screenshots per case and `summary.json`. At end:

```js
fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length) {
  failures.forEach((failure) => console.error(`FAIL: ${failure}`));
  process.exit(1);
}
console.log(`PASS: X4 browser pagination/navigation matrix ${summary.cases.length}/${summary.cases.length}`);
```

Expected matrix count: exactly `20` behavior/viewport cases. Control asset-absence checks are recorded separately and do not inflate the 20-case count.

- [ ] **Step 9: Commit**

```bash
git add tests/browser/x4-pagination-navigation-l4.mjs
git commit -m "test: add X4 pagination navigation browser matrix"
```

---

## Task 6: Focused X4 GitHub Actions workflow

**File:** Create `.github/workflows/x4-pagination-navigation.yml`

**Interfaces:** Exact-head support-floor verification for Tasks 1-5.

- [ ] **Step 1: Define triggers/environment**

Use this header/path set:

```yaml
name: X4 Pagination Navigation

on:
  push:
    branches: [feat/v1.2-x4-navigation]
    paths:
      - 'archive.php'
      - 'search.php'
      - 'comments.php'
      - 'template-parts/content/content-single.php'
      - 'assets/css/components/navigation.css'
      - 'inc/theme/assets.php'
      - 'scripts/verify-v1-core.sh'
      - 'tests/offline/x4-pagination-navigation-contract.php'
      - 'tests/runtime/x4-pagination-navigation.php'
      - 'tests/browser/x4-pagination-navigation-l4.mjs'
      - '.github/workflows/x4-pagination-navigation.yml'
      - 'docs/evidence/X4_PAGINATION_NAVIGATION_20260916.md'
      - 'docs/source/AZT-04-roadmap-qa-decisions.md'
      - 'docs/source/AZT-EXEC-MAP.md'
      - 'docs/source/SOURCE_MANIFEST.md'
  pull_request:
    branches: [main]
  workflow_dispatch:

permissions:
  contents: read
```

Use `ubuntu-24.04`, MySQL `8.0`, PHP `8.1`, Node `22`, WordPress exactly `6.9`, zero active plugins.

- [ ] **Step 2: Checkout exact candidate and run static gate**

```yaml
- uses: actions/checkout@v4
  with:
    ref: ${{ github.event.pull_request.head.sha || github.sha }}
    fetch-depth: 0
- run: |
    set -euo pipefail
    mkdir -p /tmp/x4 /tmp/x4-l4
    git rev-parse HEAD | tee /tmp/x4/checkout-sha.txt
- run: bash scripts/verify-v1-core.sh
```

Also assert `Version: 1.1.0` and `AZNET_THEME_VERSION = 1.1.0` before runtime.

- [ ] **Step 3: Install WordPress 6.9 and seed fixture**

Follow the retained X3 pattern: install WP-CLI, WordPress 6.9, exact production Theme bytes, activate Theme, assert zero active plugins, then:

```bash
wp eval-file "$GITHUB_WORKSPACE/tests/runtime/x4-pagination-navigation.php" --path=/tmp/wp --allow-root > /tmp/x4/fixture.json
jq -e '.post_id > 0 and .category_id > 0 and (.archive_url | length > 0) and (.search_url | length > 0) and (.post_url | length > 0)' /tmp/x4/fixture.json
```

- [ ] **Step 4: Verify runtime asset boundary**

Start disposable PHP runtime exactly as X3. `curl` archive/search/Post and require HTTP 200 plus `aznet-theme-navigation-css`; `curl` Home/Page and require HTTP 200 with no X4 stylesheet; `curl` missing route and require HTTP 404 with no X4 stylesheet. Fail on Theme-caused `PHP Fatal error|Warning|Parse error|Uncaught` markers in the runtime log.

- [ ] **Step 5: Install browser dependencies matching the script**

```yaml
- uses: actions/setup-node@v4
  with:
    node-version: '22'
- run: |
    set -euo pipefail
    npm install --no-save playwright@1.55.0 @axe-core/playwright@4.10.2
    npx playwright install --with-deps chromium
```

- [ ] **Step 6: Run L4 and upload evidence**

```yaml
- name: Run X4 browser verification
  env:
    X4_STATE_DIR: /tmp/x4
    X4_L4_DIR: /tmp/x4-l4
  run: node tests/browser/x4-pagination-navigation-l4.mjs

- name: Upload X4 evidence
  if: always()
  uses: actions/upload-artifact@v4
  with:
    name: x4-pagination-navigation-evidence
    path: |
      /tmp/x4
      /tmp/x4-l4
    if-no-files-found: error
```

Expected: 20/20 behavior/viewport cases PASS, Axe critical/serious 0, no page-level overflow, visible focus, native navigation state changes.

- [ ] **Step 7: Add ownership/diff guard**

Reject production JavaScript, provider integration files, Woo templates, version promotion, or query/redirect additions. Compare against PR base and permit only planned X4 files plus closure docs.

- [ ] **Step 8: Commit workflow**

```bash
git add .github/workflows/x4-pagination-navigation.yml
git commit -m "ci: verify X4 pagination navigation quality"
```

---

## Task 7: Final functional evidence and source closure

**Files:**
- Create after PASS: `docs/evidence/X4_PAGINATION_NAVIGATION_20260916.md`
- Modify after PASS only: AZT-04, AZT-EXEC-MAP, SOURCE_MANIFEST.

**Interfaces:** Consumes exact final functional-head L1-L4 evidence. Produces `X4 PASS / X5 NEXT`, not `1.2.0`, provider L5, release, or deployment.

- [ ] **Step 1: Verify exact functional head before docs**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x4-pagination-navigation-contract.php
bash scripts/verify-v1-core.sh
```

Require the focused X4 workflow on the same exact SHA to be `completed/success`, WordPress 6.9/PHP 8.1/zero-plugin, 20/20 browser cases, and an evidence artifact with digest.

- [ ] **Step 2: Require retained PR workflows to settle**

Do not create PASS closure while a triggered retained workflow is pending/failing. A retry counts only if evidence shows infrastructure transience and the exact same SHA passes without code change; record the first failure and successful retry.

- [ ] **Step 3: Create evidence with actual values**

The evidence document must contain actual final SHA, PR number, focused run ID, artifact ID/digest, RED SHA/intended failure, GREEN/regression commands, runtime routes/statuses, 20/20 L4 result, retained workflow count, rollback, and Not Inferred section. Do not commit placeholder angle-bracket values.

Required final sections:

```markdown
# X4 Pagination & Navigation Completion — Closure Evidence

## Scope and ownership
WordPress remains authoritative for main-query pagination, Post page splitting, adjacent-Post targets, comment pagination state/URLs and content/comment lifecycle. AZnet Theme adds presentation only through native WordPress APIs and surface-aware navigation CSS.

## RED -> GREEN
<replace with actual evidence before commit>

## L1-L2
<replace with actual evidence before commit>

## L3
<replace with actual evidence before commit>

## L4
<replace with actual evidence before commit>

## Artifact and retained regression
<replace with actual evidence before commit>

## Not inferred
Provider L5, X5 implementation, Theme metadata 1.2.0, package/release and production deployment are not inferred.

## Exact next
Write and review the bounded X5 Native Form Controls implementation plan after X4 canonical integration and exact-main verification.
```

The angle-bracket markers above are instructions in this plan only; they must be replaced with actual evidence in the evidence document.

- [ ] **Step 4: Advance source only under version preconditions**

If canonical source still has AZT-04 v0.46 and EXEC-MAP v0.38, move them to v0.47/v0.39, mark X4 PASS / X5 NEXT, and add exact evidence. If either source version has moved, stop and reconcile rather than overwriting newer state. Update SOURCE_MANIFEST consistently. Preserve all X1-X3/v1.1 provenance and Theme metadata 1.1.0.

- [ ] **Step 5: Verify the documentation head**

```bash
bash scripts/verify-v1-core.sh
```

Then require final PR workflows on the documentation head to settle GREEN before merge review.

- [ ] **Step 6: Update PR and stop at merge gate**

PR body must contain final head, exact X4 run/artifact/digest, retained workflow status, ownership boundary, Theme metadata 1.1.0, rollback, and X5 plan as next. Mark Ready for Review only after those are true. Do not merge X4 production without explicit owner approval. After approval, merge with `expected_head_sha`; then require fresh `V1 Exact Main Verification` on the merge SHA before claiming canonical X4 integration.

---

## Rollback / Recovery

- Revert bounded X4 commits/merge.
- Removing `navigation.css` and its enqueue restores prior presentation without touching WordPress Posts, comments, main-query state, pagination URLs, or adjacent-Post semantics.
- Theme switching preserves WordPress-owned content/comments and all external domain data.
- No X4 production code mutates provider data.

## Self-Review Results

1. **Spec coverage:** Archive/search pagination, Post page links, adjacent Post navigation, and comment pagination each have implementation plus L3/L4 evidence; WordPress ownership and no-navigation-framework constraints are explicit.
2. **Placeholder scan:** No production implementation step contains TBD/TODO or an undefined helper. Evidence placeholders appear only inside the plan's evidence-template instruction and are explicitly forbidden from the committed evidence artifact.
3. **Dependency consistency:** Browser code imports `playwright` and `@axe-core/playwright`; workflow installs exactly `playwright@1.55.0` and `@axe-core/playwright@4.10.2`, matching the retained X3 pattern.
4. **URL/state consistency:** Search fixture passes the raw search phrase to `add_query_arg()` (no pre-encoding); page 2 and comment page links are discovered by following WordPress-generated anchors rather than constructed in tests or production.
5. **Interface consistency:** `should_enqueue_navigation_assets(): bool`, `enqueue_navigation_assets(?string $version = null): void`, handle `aznet-theme-navigation`, and `assets/css/components/navigation.css` are consistent throughout.
6. **Version/ownership:** Theme metadata stays 1.1.0; no production query/redirect/JS/provider/private-state ownership is introduced.

## Review Gate

This plan is the X4 review checkpoint. Production implementation must not begin until the plan is reviewed/approved. After approval, revalidate canonical `main`, create `feat/v1.2-x4-navigation` from that exact main, and execute Task 1 RED before any production PHP/CSS change.

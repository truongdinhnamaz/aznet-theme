# X4 Pagination & Navigation Completion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete production-ready native pagination/navigation presentation for archive/search result pagination, native Post page links, adjacent Post navigation, and comment pagination while WordPress remains authoritative for pagination state, URLs, queries, comments, and content lifecycle.

**Architecture:** Add one bounded shared Theme presentation module, `assets/css/components/navigation.css`, and enqueue it only on native archive, search, and single Post surfaces. Existing WordPress APIs remain the source of all navigation markup/state (`the_posts_pagination()`, `wp_link_pages()`, `the_post_navigation()`, `the_comments_pagination()`); PHP changes only supply clearer labels and Theme-owned wrapper presentation, with no custom query, URL builder, pagination state store, redirect heuristic, JavaScript navigation engine, or provider integration.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, CSS, Bash/PHP static contracts, WP-CLI runtime fixtures, Node 22, Playwright 1.55.0, axe-core 4.10.2, MySQL 8, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`

## Global Constraints

- Canonical repository: `truongdinhnamaz/aznet-theme`.
- Plan base: canonical `main@358a54e8e549fb79bf645ea51f35ed8e86fe4aab`, the owner-approved PR #81 merge commit.
- Fresh exact-main verification: `V1 Exact Main Verification` run `35080332135` completed SUCCESS on that exact SHA; static artifact `10440285818` (`sha256:86ef5733e95d87762f1e006085c46132f9390f63f3e925142ad2d7a175c05820`) and clean runtime/browser artifact `10439528105` (`sha256:f309b8ebe27236de166e687d059f0c3622a0b5a9812f599fb4e510ec2c43d626`).
- Governing rule: **SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.**
- WordPress owns pagination/navigation state, URLs, the main query, Post content pagination, adjacent-Post semantics, comments and comment pagination.
- Theme owns only markup composition where WordPress expects Theme markup, CSS presentation, responsive geometry, visible focus, and accessibility presentation.
- WordPress support floor remains `6.9+`; PHP support floor remains `8.1+`.
- Theme runtime metadata remains exactly `1.1.0` during X4. Promotion to `1.2.0` belongs only to X6.
- Provider L5 work remains outside X4. Do not add RootProfile, ConvertFlow, WooCommerce, SEO-provider, or private provider selectors/storage/API logic.
- Do not add `WP_Query`, `query_posts()`, `get_posts()`, `pre_get_posts`, custom pagination state, URL heuristics, redirects, REST/AJAX navigation, JavaScript pagination, infinite scroll, or a separate navigation application framework.
- Do not redesign `index.php`, `page.php`, the Header navigation system, WooCommerce pagination, or provider-owned surfaces in X4.
- Do not add a build/bundler stack.
- All new Theme assets must remain surface-aware and must not load globally.
- Production feature work must follow RED -> intended failure -> minimal GREEN -> retained regression -> L3 runtime -> L4 browser/a11y.
- A deeper failure must first be reduced to the shallowest stable reproduction before production behavior changes.
- Do not claim X4 PASS, X5 readiness, provider certification, `1.2.0`, release, or deployment without the corresponding fresh evidence.

---

## File Structure

### Create

- `assets/css/components/navigation.css` — shared X4 presentation for native archive/search pagination, Post page links, adjacent Post navigation state refinements, and comment pagination.
- `tests/offline/x4-pagination-navigation-contract.php` — RED/GREEN ownership and markup/CSS/asset contract.
- `tests/runtime/x4-pagination-navigation.php` — deterministic WordPress fixture that creates multi-page listings, a paginated Post, adjacent Posts, and paginated comments using WordPress-owned state.
- `tests/browser/x4-pagination-navigation-l4.mjs` — responsive/keyboard/focus/overflow/a11y verification for the X4 surfaces.
- `.github/workflows/x4-pagination-navigation.yml` — focused WordPress 6.9 / PHP 8.1 / zero-plugin X4 workflow.
- `docs/evidence/X4_PAGINATION_NAVIGATION_20260916.md` — create only after the final X4 functional head has fresh L1-L4 evidence.

### Modify

- `archive.php` — keep the native main loop and `the_posts_pagination()`, adding only explicit native pagination labels/text.
- `search.php` — keep the native main query and `the_posts_pagination()`, adding only explicit native pagination labels/text.
- `template-parts/content/content-single.php` — keep `wp_link_pages()` and `the_post_navigation()`, adding a visible page-link summary and explicit native navigation labels.
- `comments.php` — keep `the_comments_pagination()`, adding an explicit native pagination label.
- `inc/theme/assets.php` — add X4 surface detection and enqueue for `navigation.css`.
- `scripts/verify-v1-core.sh` — add the X4 offline contract to the reusable verification chain.
- `docs/source/AZT-04-roadmap-qa-decisions.md` — closure only, after fresh X4 evidence; expected current source move is v0.46 -> v0.47 if canonical source version is still v0.46.
- `docs/source/AZT-EXEC-MAP.md` — closure only, after fresh X4 evidence; expected current source move is v0.38 -> v0.39 if canonical source version is still v0.38.
- `docs/source/SOURCE_MANIFEST.md` — closure only, after fresh X4 evidence; record X4 PASS / X5 NEXT and exact evidence.

### Intentionally unchanged

- `index.php` — broad fallback navigation is outside the source-defined X4 acceptance set.
- `page.php` — X4 source specifically names native Post page links; generic Page pagination is not expanded in this slice.
- `assets/js/*` — no production JavaScript is needed.
- WooCommerce templates/assets — Woo owns commerce pagination semantics and R4 presentation remains retained.
- Provider integration adapters — provider L5 remains separate.

---

### Task 1: Define the X4 RED ownership and presentation contract

**Files:**
- Create: `tests/offline/x4-pagination-navigation-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: current native calls in `archive.php`, `search.php`, `template-parts/content/content-single.php`, `comments.php`, plus `inc/theme/assets.php`.
- Produces: a deterministic contract requiring `assets/css/components/navigation.css`, `should_enqueue_navigation_assets(): bool`, `enqueue_navigation_assets(?string $version = null): void`, explicit navigation labels, and native WordPress APIs while forbidding state/query/redirect takeover.

- [ ] **Step 1: Create the failing X4 contract**

Create `tests/offline/x4-pagination-navigation-contract.php` with this structure:

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

$must_have = [
    'archive.php' => [
        'have_posts()',
        'the_post()',
        'the_posts_pagination(',
        "'aria_label'",
        "esc_attr__( 'Archive pagination', 'aznet-theme' )",
    ],
    'search.php' => [
        'have_posts()',
        'the_post()',
        'the_posts_pagination(',
        "'aria_label'",
        "esc_attr__( 'Search results pagination', 'aznet-theme' )",
    ],
    'template-parts/content/content-single.php' => [
        'the_content()',
        'wp_link_pages(',
        'aznet-theme-article__page-links',
        'aznet-theme-navigation__summary',
        'the_post_navigation(',
        "'aria_label'",
        "esc_attr__( 'Post navigation', 'aznet-theme' )",
    ],
    'comments.php' => [
        'wp_list_comments(',
        'the_comments_pagination(',
        "'aria_label'",
        "esc_attr__( 'Comments pagination', 'aznet-theme' )",
    ],
    'inc/theme/assets.php' => [
        'function should_enqueue_navigation_assets(): bool',
        'function enqueue_navigation_assets( ?string $version = null ): void',
        "'aznet-theme-navigation'",
        "'/assets/css/components/navigation.css'",
        'enqueue_navigation_assets( $version );',
    ],
];

$sources = [
    'archive.php' => $archive,
    'search.php' => $search,
    'template-parts/content/content-single.php' => $single,
    'comments.php' => $comments,
    'inc/theme/assets.php' => $assets,
];

foreach ( $must_have as $path => $needles ) {
    foreach ( $needles as $needle ) {
        if ( false === strpos( $sources[ $path ], $needle ) ) {
            throw new RuntimeException( $path . ' missing X4 contract token: ' . $needle );
        }
    }
}

$css_needles = [
    '.aznet-theme-listing__pagination .page-numbers',
    '.aznet-theme-article__page-links .post-page-numbers',
    '.aznet-theme-comments .comments-pagination .page-numbers',
    '.aznet-theme-navigation__summary',
    ':focus-visible',
    '.current',
    '@media (max-width: 47.999rem)',
];
foreach ( $css_needles as $needle ) {
    if ( false === strpos( $css, $needle ) ) {
        throw new RuntimeException( 'navigation.css missing X4 selector/token: ' . $needle );
    }
}

$production = implode( "\n", [ $archive, $search, $single, $comments, $assets ] );
$forbidden = [
    'new WP_Query',
    'query_posts(',
    'pre_get_posts',
    'wp_redirect(',
    'wp_safe_redirect(',
    '$_GET',
    '$_SERVER[\'REQUEST_URI\']',
    '$wpdb',
    'choiceguide_',
    'rootprofile_',
];
foreach ( $forbidden as $needle ) {
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

- [ ] **Step 2: Add the X4 contract to the reusable verifier**

Insert immediately after the X3 contract in `scripts/verify-v1-core.sh`:

```bash
printf '%s\n' '==> X4 Pagination & Navigation Completion contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x4-pagination-navigation-contract.php
```

- [ ] **Step 3: Run the focused RED contract and verify the intended failure**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x4-pagination-navigation-contract.php
```

Expected result before production changes: **FAIL** specifically because `assets/css/components/navigation.css` does not exist. Do not accept a syntax error, unrelated retained-test failure, missing existing template, or provider failure as valid RED evidence.

- [ ] **Step 4: Commit the RED checkpoint**

```bash
git add tests/offline/x4-pagination-navigation-contract.php scripts/verify-v1-core.sh
git commit -m "test: define X4 pagination navigation contract"
```

Record the exact RED commit SHA and failing command/output for the X4 evidence document. Do not change production behavior in this commit.

---

### Task 2: Add minimal native markup labels without taking over WordPress state

**Files:**
- Modify: `archive.php`
- Modify: `search.php`
- Modify: `template-parts/content/content-single.php`
- Modify: `comments.php`

**Interfaces:**
- Consumes: WordPress native pagination/navigation APIs already present.
- Produces: stable Theme wrapper/label hooks consumed by `navigation.css` and browser tests; WordPress still generates all links, current-page state, adjacent-Post targets, and comment-page targets.

- [ ] **Step 1: Make archive pagination explicit but native**

Replace the zero-argument call inside `.aznet-theme-listing__pagination` with:

```php
<?php

the_posts_pagination(
    [
        'mid_size'   => 1,
        'prev_text'  => esc_html__( 'Previous', 'aznet-theme' ),
        'next_text'  => esc_html__( 'Next', 'aznet-theme' ),
        'aria_label' => esc_attr__( 'Archive pagination', 'aznet-theme' ),
    ]
);
?>
```

Do not change the main Loop, query, card template, archive routing, or empty-state logic.

- [ ] **Step 2: Make search pagination explicit but native**

Replace the zero-argument call inside `.aznet-theme-listing__pagination` with:

```php
<?php

the_posts_pagination(
    [
        'mid_size'   => 1,
        'prev_text'  => esc_html__( 'Previous', 'aznet-theme' ),
        'next_text'  => esc_html__( 'Next', 'aznet-theme' ),
        'aria_label' => esc_attr__( 'Search results pagination', 'aznet-theme' ),
    ]
);
?>
```

Do not add a custom search query, result count store, redirect, or URL builder.

- [ ] **Step 3: Give paginated native Posts a visible page-link summary**

Change the existing `wp_link_pages()` call in `template-parts/content/content-single.php` to:

```php
wp_link_pages(
    [
        'before' => '<nav class="aznet-theme-article__page-links" aria-label="' . esc_attr__( 'Article pages', 'aznet-theme' ) . '"><span class="aznet-theme-navigation__summary">' . esc_html__( 'Pages:', 'aznet-theme' ) . '</span>',
        'after'  => '</nav>',
    ]
);
```

Do not supply custom page URLs or read the current page from request globals. `wp_link_pages()` remains the only page-link generator.

- [ ] **Step 4: Make adjacent Post navigation label explicit**

Keep the existing `prev_text` and `next_text`, adding only:

```php
'aria_label' => esc_attr__( 'Post navigation', 'aznet-theme' ),
```

The complete call remains `the_post_navigation()`; do not replace it with manual adjacent-Post queries or custom links.

- [ ] **Step 5: Make comment pagination label explicit**

Keep the existing `prev_text` and `next_text` in `comments.php`, adding:

```php
'aria_label' => esc_attr__( 'Comments pagination', 'aznet-theme' ),
```

Do not calculate comment page numbers or build `cpage` URLs in production code.

- [ ] **Step 6: Run PHP lint on the four changed templates**

```bash
php -l archive.php
php -l search.php
php -l template-parts/content/content-single.php
php -l comments.php
```

Expected: four `No syntax errors detected` results.

- [ ] **Step 7: Commit the native markup slice**

```bash
git add archive.php search.php template-parts/content/content-single.php comments.php
git commit -m "feat: clarify native pagination navigation labels"
```

---

### Task 3: Add the bounded shared navigation presentation asset

**Files:**
- Create: `assets/css/components/navigation.css`
- Modify: `inc/theme/assets.php`

**Interfaces:**
- Produces: `should_enqueue_navigation_assets(): bool` and `enqueue_navigation_assets(?string $version = null): void`.
- Consumed by: `enqueue_assets()` and X4 runtime/browser tests.
- Surface contract: true only for native archive, native search, or native single Post requests; false for WooCommerce current surfaces and all other requests.

- [ ] **Step 1: Create `navigation.css` with shared native control presentation**

Use this exact initial CSS shape:

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

Do not move retained article/navigation rules out of `article.css` in X4; the existing P2 article presentation is already PASS and does not need a refactor to satisfy X4.

- [ ] **Step 2: Add native X4 surface detection to `inc/theme/assets.php`**

Add before `enqueue_assets()`:

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

- [ ] **Step 3: Enqueue the asset after generic content is available**

In `enqueue_assets()`, call:

```php
enqueue_navigation_assets( $version );
```

Place it after the generic-content enqueue block and before surface-specific media/recovery/article/comments additions. This preserves dependency order and keeps the asset bounded.

- [ ] **Step 4: Run the focused contract**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x4-pagination-navigation-contract.php
```

Expected: PASS.

- [ ] **Step 5: Run the full reusable static/contract chain**

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS, including X1-X3 retained contracts and the new X4 contract.

- [ ] **Step 6: Commit minimal GREEN presentation**

```bash
git add assets/css/components/navigation.css inc/theme/assets.php
git commit -m "feat: add native pagination navigation presentation"
```

---

### Task 4: Build a deterministic real-WordPress X4 runtime fixture

**Files:**
- Create: `tests/runtime/x4-pagination-navigation.php`

**Interfaces:**
- Consumes: WordPress Core Post, Category, comment, option, permalink, and content-pagination APIs only.
- Produces: JSON with `archive_url`, `search_url`, `post_url`, `home_url`, `page_url`, `missing_url`, `post_id`, `category_id` and fixture marker strings. Browser tests discover page-2/comment-page URLs by following WordPress-generated links rather than constructing them.

- [ ] **Step 1: Create the fixture with enough native state to paginate**

Implement `tests/runtime/x4-pagination-navigation.php` so it:

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

update_option( 'posts_per_page', 3 );
update_option( 'page_comments', 1 );
update_option( 'comments_per_page', 2 );
update_option( 'default_comments_page', 'oldest' );

$category_id = wp_insert_category(
    [
        'cat_name'             => 'X4 Navigation Archive',
        'category_description' => 'Native X4 pagination fixture.',
    ]
);
if ( is_wp_error( $category_id ) || 0 >= (int) $category_id ) {
    WP_CLI::error( 'Unable to create X4 category fixture.' );
}

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
            'post_category' => [ (int) $category_id ],
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

$category_url = get_category_link( (int) $category_id );
$post_url     = get_permalink( $target_post_id );
$page_url     = get_permalink( (int) $page_id );
if ( is_wp_error( $category_url ) || ! is_string( $category_url ) || '' === $category_url || ! is_string( $post_url ) || '' === $post_url || ! is_string( $page_url ) || '' === $page_url ) {
    WP_CLI::error( 'Unable to resolve X4 fixture URLs.' );
}

$result = [
    'archive_url' => $category_url,
    'search_url'  => add_query_arg( 's', rawurlencode( 'X4 Navigation Fixture Post' ), home_url( '/' ) ),
    'post_url'    => $post_url,
    'page_url'    => $page_url,
    'home_url'    => home_url( '/' ),
    'missing_url' => home_url( '/x4-navigation-definitely-missing/' ),
    'post_id'     => $target_post_id,
    'category_id' => (int) $category_id,
];

echo wp_json_encode( $result );
```

The fixture may use WordPress native option APIs because it is test-only state setup. Production Theme code must not mirror these settings or own them.

- [ ] **Step 2: Keep URL discovery native in the browser test**

Do not output handcrafted `paged=2`, Post page-2, or comment-page-2 URLs. The browser test must follow the links generated by WordPress Core and verify that navigation changes the URL/content state.

- [ ] **Step 3: Commit the runtime fixture**

```bash
git add tests/runtime/x4-pagination-navigation.php
git commit -m "test: add X4 WordPress pagination fixture"
```

---

### Task 5: Add X4 L4 responsive, keyboard, focus, overflow and a11y verification

**Files:**
- Create: `tests/browser/x4-pagination-navigation-l4.mjs`

**Interfaces:**
- Consumes: JSON fixture path in `X4_FIXTURE_JSON`, runtime base URLs generated by Task 4, and WordPress-generated navigation links.
- Produces: deterministic 20-case matrix across five X4 behaviors and four viewports plus screenshots/JSON evidence.

- [ ] **Step 1: Define the viewport matrix and shared assertions**

Use exactly:

```js
const viewports = {
  desktop: { width: 1440, height: 1000 },
  tablet: { width: 1024, height: 900 },
  mobile: { width: 390, height: 844 },
  narrow: { width: 320, height: 760 },
};
```

For every case, assert:

```js
const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
if (overflow > 1) throw new Error(`horizontal overflow: ${overflow}`);

const results = await new AxeBuilder({ page }).analyze();
const blocking = results.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
if (blocking.length) throw new Error(`axe blocking violations: ${JSON.stringify(blocking)}`);
```

Capture unexpected `pageerror` and console error events and fail on them, excluding only already-documented browser noise if an existing repository helper explicitly does so.

- [ ] **Step 2: Verify archive pagination uses WordPress-generated links**

For each viewport:

```js
await page.goto(fixture.archive_url, { waitUntil: 'networkidle' });
const pagination = page.locator('.aznet-theme-listing__pagination .navigation.pagination');
await pagination.waitFor();
await expectCountAtLeast(page.locator('.aznet-theme-listing__pagination .page-numbers'), 2);
await assertVisibleFocus(page, '.aznet-theme-listing__pagination a.page-numbers');
const nextHref = await page.locator('.aznet-theme-listing__pagination a.next.page-numbers').getAttribute('href');
if (!nextHref) throw new Error('archive next link missing native href');
await page.locator('.aznet-theme-listing__pagination a.next.page-numbers').click();
await page.waitForLoadState('networkidle');
if (page.url() === fixture.archive_url) throw new Error('archive pagination did not change native URL state');
if (await page.locator('.aznet-theme-listing__pagination .page-numbers.current').count() !== 1) throw new Error('archive current page state missing');
```

Do not assert a handcrafted URL pattern; the test should trust WordPress-generated navigation and verify state changed.

- [ ] **Step 3: Verify search pagination the same way**

Repeat the archive behavior against `fixture.search_url`, using the Search surface selectors and requiring the `aria-label="Search results pagination"` navigation landmark.

- [ ] **Step 4: Verify native Post page links**

For each viewport:

```js
await page.goto(fixture.post_url, { waitUntil: 'networkidle' });
const pageLinks = page.locator('.aznet-theme-article__page-links');
await pageLinks.waitFor();
if (!(await pageLinks.getAttribute('aria-label'))?.includes('Article pages')) throw new Error('Article pages aria-label missing');
if (await page.locator('#x4-post-page-one').count() !== 1) throw new Error('Post page-one marker missing');
await assertVisibleFocus(page, '.aznet-theme-article__page-links a.post-page-numbers');
await page.locator('.aznet-theme-article__page-links a.post-page-numbers').filter({ hasText: '2' }).click();
await page.waitForLoadState('networkidle');
if (await page.locator('#x4-post-page-two').count() !== 1) throw new Error('Post page-two marker missing after native page-link navigation');
```

The test follows the native `wp_link_pages()` anchor rather than constructing a page URL.

- [ ] **Step 5: Verify adjacent Post navigation**

On `fixture.post_url`, require:

```js
const postNav = page.locator('.aznet-theme-article .post-navigation');
await postNav.waitFor();
if (!(await postNav.getAttribute('aria-label'))?.includes('Post navigation')) throw new Error('Post navigation aria-label missing');
const adjacentLinks = postNav.locator('a');
if (await adjacentLinks.count() < 2) throw new Error('Expected both previous and next native Post links');
await assertVisibleFocus(page, '.aznet-theme-article .post-navigation a');
const firstHref = await adjacentLinks.first().getAttribute('href');
await adjacentLinks.first().click();
await page.waitForLoadState('networkidle');
if (!firstHref || page.url() === fixture.post_url) throw new Error('Adjacent Post navigation did not follow native link');
```

Also assert each `.nav-title` has positive width and does not cause page-level overflow at 320px.

- [ ] **Step 6: Verify comment pagination**

Return to `fixture.post_url`, require `.comments-pagination`, a `Comments pagination` aria-label, at least two page-number controls, one current page, and visible focus. Click a WordPress-generated comments pagination link and confirm that:

```js
const before = await page.locator('.aznet-theme-comments .comment-content').allTextContents();
await page.locator('.aznet-theme-comments .comments-pagination a.page-numbers').first().click();
await page.waitForLoadState('networkidle');
const after = await page.locator('.aznet-theme-comments .comment-content').allTextContents();
if (JSON.stringify(before) === JSON.stringify(after)) throw new Error('Comment pagination did not change the native comment page');
```

Do not construct `cpage` parameters in test code.

- [ ] **Step 7: Verify X4 stylesheet presence/absence in browser evidence**

Require `aznet-theme-navigation-css` or an href ending in `/assets/css/components/navigation.css` on archive, search, and native Post routes. Require it absent on `fixture.home_url`, `fixture.page_url`, and `fixture.missing_url`.

- [ ] **Step 8: Save evidence per viewport**

Write one screenshot and one concise JSON result record per route/viewport into the workflow evidence directory. The JSON must record route, viewport, overflow, Axe critical/serious count, focus target, and clicked-native-link outcome.

- [ ] **Step 9: Commit the L4 test**

```bash
git add tests/browser/x4-pagination-navigation-l4.mjs
git commit -m "test: add X4 pagination navigation browser matrix"
```

---

### Task 6: Add the focused X4 GitHub Actions workflow

**Files:**
- Create: `.github/workflows/x4-pagination-navigation.yml`

**Interfaces:**
- Consumes: Tasks 1-5 and the existing reusable verifier.
- Produces: exact-head L1-L4 workflow evidence and artifact for X4.

- [ ] **Step 1: Define exact triggers and support-floor environment**

Use:

```yaml
name: X4 Pagination Navigation

on:
  push:
    branches:
      - feat/v1.2-x4-navigation
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
    branches:
      - main
  workflow_dispatch:

permissions:
  contents: read
```

Use `ubuntu-24.04`, MySQL `8.0`, PHP `8.1`, Node `22`, WordPress exactly `6.9`, Playwright `1.55.0`, axe-core `4.10.2`, and zero active plugins.

- [ ] **Step 2: Checkout and record the exact candidate SHA**

Use the same exact-head pattern as X3:

```yaml
- uses: actions/checkout@v4
  with:
    ref: ${{ github.event.pull_request.head.sha || github.sha }}
    fetch-depth: 0

- run: |
    set -euo pipefail
    mkdir -p /tmp/x4 /tmp/x4-l4
    git rev-parse HEAD | tee /tmp/x4/checkout-sha.txt
```

- [ ] **Step 3: Run the reusable static/contract gate before WordPress runtime**

```yaml
- run: bash scripts/verify-v1-core.sh
```

Also assert Theme metadata remains `1.1.0` in both `style.css` and `AZNET_THEME_VERSION`.

- [ ] **Step 4: Install WordPress 6.9 and the exact Theme candidate**

Follow the retained X3 support-floor pattern: download WP-CLI, install WordPress 6.9, copy only production Theme files into `/tmp/wp/wp-content/themes/aznet-theme`, activate the Theme, and assert no active plugins.

- [ ] **Step 5: Seed the X4 fixture**

Run:

```bash
wp eval-file "$GITHUB_WORKSPACE/tests/runtime/x4-pagination-navigation.php" --path=/tmp/wp --allow-root > /tmp/x4/fixture.json
jq -e '.post_id > 0 and .category_id > 0 and (.archive_url | length > 0) and (.search_url | length > 0) and (.post_url | length > 0)' /tmp/x4/fixture.json
```

- [ ] **Step 6: Start runtime and verify surface-aware asset boundaries before browser testing**

Start the same disposable PHP runtime pattern retained by X1-X3. With `curl`, assert `navigation.css` is present on the fixture archive/search/Post HTML and absent from Home/Page/404 control routes. Assert archive/search/Post routes return `200` and the missing route retains native HTTP `404`.

- [ ] **Step 7: Install browser dependencies and run the X4 matrix**

```bash
npm install --no-save @playwright/test@1.55.0 axe-core@4.10.2
npx playwright install chromium --with-deps
X4_FIXTURE_JSON=/tmp/x4/fixture.json X4_EVIDENCE_DIR=/tmp/x4-l4 node tests/browser/x4-pagination-navigation-l4.mjs
```

Expected: 20/20 route/behavior viewport cases PASS, no blocking Axe critical/serious findings, no page-level horizontal overflow, and visible focus for all tested native navigation controls.

- [ ] **Step 8: Add a bounded diff/ownership check**

For the production implementation PR, reject changes outside the planned X4 paths plus closure docs. At minimum, fail if the diff adds production JavaScript, provider integration files, WooCommerce templates, version promotion, or new query/redirect code.

- [ ] **Step 9: Upload exact evidence**

Upload `/tmp/x4`, `/tmp/x4-l4`, runtime log, candidate SHA, and fixture JSON as artifact `x4-pagination-navigation-evidence`.

- [ ] **Step 10: Commit the workflow**

```bash
git add .github/workflows/x4-pagination-navigation.yml
git commit -m "ci: verify X4 pagination navigation quality"
```

---

### Task 7: Run final-head regression, record closure evidence, and advance source to X5 only after proof

**Files:**
- Create after PASS: `docs/evidence/X4_PAGINATION_NAVIGATION_20260916.md`
- Modify after PASS: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify after PASS: `docs/source/AZT-EXEC-MAP.md`
- Modify after PASS: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: exact final X4 functional head, X4 workflow run/artifact, retained PR workflows, Theme version state.
- Produces: evidence-backed `X4 PASS / X5 NEXT`. It does not promote `1.2.0`, claim provider L5, or authorize release/deployment.

- [ ] **Step 1: Run the local/focused verification before closure docs**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/x4-pagination-navigation-contract.php
bash scripts/verify-v1-core.sh
```

Both must PASS on the exact functional head that will be cited.

- [ ] **Step 2: Require fresh X4 workflow success on that same exact SHA**

Do not reuse a prior commit's browser evidence. Require the final X4 workflow run to report `status=completed`, `conclusion=success`, exact candidate SHA match, zero active plugins, WordPress 6.9, 20/20 browser matrix PASS, and a non-expired evidence artifact with digest.

- [ ] **Step 3: Require all retained PR workflows triggered on the final functional head to settle without Theme regression**

If any retained workflow fails, reproduce/reduce it before changing production code. A rerun is acceptable only when evidence proves a transient infrastructure failure and the exact same commit passes without code change; record that explicitly rather than silently treating the first failure as irrelevant.

- [ ] **Step 4: Create the X4 evidence document with exact values only**

`docs/evidence/X4_PAGINATION_NAVIGATION_20260916.md` must include:

```markdown
# X4 Pagination & Navigation Completion — Closure Evidence

**Status:** PASS at L1-L4 on verified functional head.
**Date:** 2026-09-16
**Functional head:** `<exact final X4 SHA from the successful workflow>`
**PR:** `<X4 production PR number>`

## Scope and ownership
WordPress remains authoritative for main-query pagination, Post page splitting, adjacent-Post targets, comment pagination state/URLs and all content/comment lifecycle. AZnet Theme adds presentation only through native WordPress APIs plus surface-aware `navigation.css`.

## RED -> GREEN
Record the exact RED commit, intended missing-navigation-capability failure, GREEN commits and final regression commands.

## L1-L2
Record focused contract + `verify-v1-core.sh` result.

## L3
Record WordPress 6.9 / PHP 8.1 / zero-plugin runtime, tested routes, HTTP semantics and stylesheet scope.

## L4
Record 20/20 matrix, 1440/1024/390/320 widths, keyboard/focus, no page-level overflow, Axe critical/serious = 0 and native-link click outcomes.

## Artifact
Record exact run ID, artifact ID and SHA-256 digest.

## Retained regression
Record final-head retained workflow count and any evidence-backed transient retry.

## Not inferred
Provider L5, X5 implementation, Theme metadata 1.2.0, package/release and production deployment are not inferred.

## Exact next
Write and review the bounded X5 Native Form Controls implementation plan before production code, after X4 canonical integration and exact-main verification.
```

Replace angle-bracketed values with the actual final evidence before committing. If any value is unavailable, do not create a PASS evidence document.

- [ ] **Step 5: Advance AZT-04 only if the source version precondition still holds**

If canonical main still has `AZT-04 v0.46`, update to `v0.47`, change the v1.2 state to `X4 PASS / X5 NEXT`, add exact X4 functional evidence, and make X5 plan the exact next. If the source version has moved for another accepted change, stop and re-resolve the source update instead of overwriting that newer state.

- [ ] **Step 6: Advance AZT-EXEC-MAP only if the source version precondition still holds**

If canonical main still has `AZT-EXEC-MAP v0.38`, update to `v0.39`, mark X4 L1-L4 PASS, and set X5 Native Form Controls planning as next. Preserve all retained X1-X3 and v1.1 provenance.

- [ ] **Step 7: Reconcile SOURCE_MANIFEST**

Add the exact X4 evidence path/run/head/artifact and change the manifest's current statement from X3 closure/X4 next to X4 closure/X5 next. Keep Theme metadata `1.1.0`, provider L5 separate, and v1.2 package/release/deployment unproven.

- [ ] **Step 8: Re-run the reusable verifier after docs/source closure**

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS on the final documentation head. Because docs/source closure creates a new commit, allow the PR workflows to run again and require the final PR head to be GREEN before requesting canonical merge.

- [ ] **Step 9: Update the PR body with final evidence and mark Ready for Review**

The PR body must state the final head SHA, exact X4 run/artifact/digest, retained workflow result, ownership boundary, Theme metadata `1.1.0`, rollback, and `X5 plan` as the next action.

- [ ] **Step 10: Stop at the canonical merge gate**

Do **not** merge the X4 production PR without explicit owner approval. After approval, merge with `expected_head_sha` and run fresh `V1 Exact Main Verification` on the merge SHA before claiming canonical X4 integration.

---

## Rollback / Recovery

- Revert the bounded X4 production commits or merge if needed.
- Removing `navigation.css` and its enqueue restores previous presentation without touching WordPress Posts, comments, main queries, pagination state, URLs, or adjacent-Post semantics.
- Theme switching must preserve all WordPress-owned content/comment state.
- No X4 production code owns or mutates provider data.
- If L3/L4 exposes a defect, reduce it to the smallest stable contract/runtime/browser reproduction before modifying production behavior.

## Self-Review Results

1. **Spec coverage:** Archive/search pagination, native Post page links, adjacent Post navigation, and comment pagination are each assigned a production task plus L3/L4 evidence. WordPress ownership and no-navigation-framework constraints are explicit. X5 form controls and X6 release/version promotion remain outside scope.
2. **Placeholder scan:** No `TBD`, `TODO`, `implement later`, generic “add tests”, or unspecified error-handling step remains. Evidence-only angle-bracket values are explicitly forbidden from being committed until replaced with actual final-run values.
3. **Type/name consistency:** `should_enqueue_navigation_assets(): bool`, `enqueue_navigation_assets(?string $version = null): void`, asset handle `aznet-theme-navigation`, file `assets/css/components/navigation.css`, and X4 test/workflow names are consistent across all tasks.
4. **Ownership check:** No production query, URL builder, redirect, JS navigation engine, provider/private read, Woo takeover, or parallel state store is introduced.
5. **Version check:** Theme metadata remains `1.1.0`; source-version increments are closure-only and guarded against overwriting a newer canonical source state.

## Review Gate

This plan is the X4 design-review checkpoint. Production implementation must not begin until the plan is reviewed/approved. After approval, create `feat/v1.2-x4-navigation` from the then-current canonical `main`, revalidate the source/base SHA, and execute Task 1 RED before any production PHP/CSS change.

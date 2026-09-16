# X1 Comments Surface Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a complete, deeply polished native WordPress comments presentation surface to AZnet Theme without taking ownership of comment data, moderation, threading, reply state, or lifecycle.

**Architecture:** Keep WordPress Core authoritative for all comment semantics and state. Add a native `comments.php` template, call it only from the native Post path, and load a dedicated `comments.css` plus WordPress Core `comment-reply` only when the comments surface is active. Use the existing shared tokens/content shell; do not create a comment engine, provider adapter, custom datastore, custom query, or new build stack.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, existing `AZnet\Theme` helpers, CSS components, PHP offline contracts, WP-CLI disposable runtime fixtures, Playwright + `@axe-core/playwright`, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`

## Global Constraints

- Canonical base for this plan: `main@70b85d7afb647048ab9a0b0f284522297ef5cb68` after PR #74; revalidate live `main` before implementation.
- Governing accepted decision: D-028 in AZT-04 v0.43.
- Product scope is AZnet Theme only. Do not open RootProfile, ConvertFlow, WooCommerce, SEO-provider, or other provider work.
- WordPress owns comment data, moderation, threading, reply state, pagination semantics, and lifecycle.
- Theme owns only markup composition expected of the Theme, visual hierarchy, responsive/accessibility presentation, and Theme-owned asset loading.
- Use WordPress native public APIs only: `comments_template()`, `wp_list_comments()`, `comment_form()`, native comment pagination/reply APIs, and WordPress Core options where required for Core behavior.
- No `WP_Query`, `get_posts()`, direct `$wpdb`, private plugin option/meta/CPT/table/class reads, custom comment persistence, custom moderation, custom reply engine, or provider identity takeover.
- Naming remains `AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.
- Theme metadata stays exactly `1.1.0` during X1. Do not promote to `1.2.0`.
- Assets must be surface-aware. `comments.css` must not load globally; `comment-reply` must only load when native threaded replies can actually be used.
- Production behavior uses RED -> intended failure -> minimal GREEN -> retained regression before L3/L4 claims.
- No PASS may be inferred across QA layers.

---

## File Map

**Create:**
- `comments.php` — native WordPress comments list/form/pagination presentation.
- `assets/css/components/comments.css` — X1-scoped visual, responsive, focus, nesting, and form presentation.
- `tests/offline/x1-comments-surface-contract.php` — L2 ownership/template/asset/static contract and RED/GREEN guard.
- `tests/runtime/x1-comments-surface.php` — disposable WordPress fixture seeding and L3 native-state assertions.
- `tests/browser/x1-comments-surface-l4.mjs` — three-viewport browser/a11y/overflow/focus checks.
- `.github/workflows/x1-comments-surface.yml` — retained X1 L2-L4 CI harness.
- `docs/evidence/X1_COMMENTS_SURFACE_20260916.md` — final X1 evidence after fresh verified runs.

**Modify:**
- `single.php` — invoke `comments_template()` only for native Posts when comments are open or existing comments are present.
- `inc/theme/assets.php` — add comments-surface detection/enqueue helpers and Core `comment-reply` gate.
- `docs/source/AZT-04-roadmap-qa-decisions.md` — after X1 gates pass, advance X1 to PASS and Exact Next to X2.
- `docs/source/AZT-EXEC-MAP.md` — after X1 gates pass, mark X1 PASS and X2 exact next.
- `docs/source/SOURCE_MANIFEST.md` — align source versions/state after X1 closure.

**Do not modify:**
- provider adapters under `inc/integrations/`;
- WooCommerce component styles/templates;
- Theme version declarations;
- WordPress content/comment storage outside disposable CI fixtures.

---

### Task 1: Create the X1 RED ownership/template/asset contract

**Files:**
- Create: `tests/offline/x1-comments-surface-contract.php`

**Interfaces:**
- Consumes: current `single.php`, `inc/theme/assets.php`, and the expected future `comments.php` / `assets/css/components/comments.css` paths.
- Produces: a deterministic L2 contract that fails on the current pre-X1 tree because `comments.php` does not exist.

- [ ] **Step 1: Add the failing contract**

Create `tests/offline/x1-comments-surface-contract.php` with this structure:

```php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function fail_x1_comments(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function source_x1_comments(string $path): string {
    if (! is_file($path)) {
        fail_x1_comments('missing required file: ' . $path);
    }

    $source = file_get_contents($path);
    if (false === $source) {
        fail_x1_comments('cannot read required file: ' . $path);
    }

    return $source;
}

$single   = source_x1_comments($root . '/single.php');
$comments = source_x1_comments($root . '/comments.php');
$assets   = source_x1_comments($root . '/inc/theme/assets.php');
$css      = source_x1_comments($root . '/assets/css/components/comments.css');

foreach ([
    "is_singular( 'post' )",
    'comments_template()',
    'comments_open()',
    'get_comments_number()',
] as $marker) {
    if (! str_contains($single, $marker)) {
        fail_x1_comments('single.php missing comments-surface marker: ' . $marker);
    }
}

foreach ([
    'post_password_required()',
    'have_comments()',
    'wp_list_comments(',
    'the_comments_pagination(',
    'comments_open()',
    'get_comments_number()',
    'comment_form(',
    'id="comments"',
    'aznet-theme-comments',
] as $marker) {
    if (! str_contains($comments, $marker)) {
        fail_x1_comments('comments.php missing native comments marker: ' . $marker);
    }
}

foreach ([
    'should_enqueue_comments_assets',
    "'aznet-theme-comments'",
    "'/assets/css/components/comments.css'",
    "wp_enqueue_script( 'comment-reply' )",
    "get_option( 'thread_comments' )",
] as $marker) {
    if (! str_contains($assets, $marker)) {
        fail_x1_comments('comments asset gate missing marker: ' . $marker);
    }
}

foreach ([
    '.aznet-theme-comments',
    '.comment-list',
    '.children',
    '.comment-body',
    '.comment-content',
    '.comment-reply-link',
    '.comment-respond',
    ':focus-visible',
    'overflow-wrap',
] as $marker) {
    if (! str_contains($css, $marker)) {
        fail_x1_comments('comments.css missing presentation marker: ' . $marker);
    }
}

$sources = $single . "\n" . $comments . "\n" . $assets;
foreach ([
    'new WP_Query',
    'WP_Query(',
    'get_posts(',
    '$wpdb',
    'get_post_meta(',
    'choiceguide_',
    'RootProfile',
    'ConvertFlow',
] as $marker) {
    if (str_contains($sources, $marker)) {
        fail_x1_comments('forbidden ownership/provider marker found: ' . $marker);
    }
}

echo "PASS: X1 native comments presentation contract\n";
```

- [ ] **Step 2: Run the RED contract**

Run:

```bash
php tests/offline/x1-comments-surface-contract.php
```

Expected result on the pre-X1 tree:

```text
FAIL: missing required file: .../comments.php
```

The failure must be caused by the missing X1 comments surface, not by PHP syntax, path errors, or unrelated retained regressions.

- [ ] **Step 3: Commit the RED checkpoint**

```bash
git add tests/offline/x1-comments-surface-contract.php
git commit -m "test: define X1 comments surface contract"
```

---

### Task 2: Implement the minimal GREEN native comments surface

**Files:**
- Create: `comments.php`
- Create: `assets/css/components/comments.css`
- Modify: `single.php`
- Modify: `inc/theme/assets.php`
- Test: `tests/offline/x1-comments-surface-contract.php`
- Regression: `tests/offline/editorial-single-post-contract.php`

**Interfaces:**
- Consumes: WordPress native Post query/context and existing Theme token/content-shell styles.
- Produces: `comments.php`; `AZnet\Theme\should_enqueue_comments_assets(): bool`; `AZnet\Theme\enqueue_comments_assets(?string $version = null): void`; conditional `comment-reply` enqueue.

- [ ] **Step 1: Add native `comments.php`**

Use the WordPress-native list/form flow and no custom persistence callback:

```php
<?php
/**
 * Native WordPress comments template.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( post_password_required() ) {
    return;
}
?>
<section id="comments" class="aznet-theme-comments">
    <?php if ( have_comments() ) : ?>
        <h2 id="aznet-theme-comments-title" class="aznet-theme-comments__title">
            <?php
            printf(
                esc_html( _n( '%s comment', '%s comments', get_comments_number(), 'aznet-theme' ) ),
                esc_html( number_format_i18n( get_comments_number() ) )
            );
            ?>
        </h2>

        <ol class="comment-list aznet-theme-comments__list">
            <?php
            wp_list_comments(
                [
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 56,
                ]
            );
            ?>
        </ol>

        <?php
        the_comments_pagination(
            [
                'prev_text' => esc_html__( 'Previous comments', 'aznet-theme' ),
                'next_text' => esc_html__( 'Next comments', 'aznet-theme' ),
            ]
        );
        ?>
    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() ) : ?>
        <p class="aznet-theme-comments__closed"><?php esc_html_e( 'Comments are closed.', 'aznet-theme' ); ?></p>
    <?php endif; ?>

    <?php
    comment_form(
        [
            'class_container' => 'comment-respond aznet-theme-comments__respond',
            'class_submit'    => 'submit aznet-theme-comments__submit',
        ]
    );
    ?>
</section>
```

Do not add a custom `callback` to `wp_list_comments()` in X1; Core remains responsible for comment semantic rendering.

- [ ] **Step 2: Invoke comments only from the native Post branch**

In `single.php`, directly after:

```php
<?php get_template_part( 'template-parts/content/content', 'single' ); ?>
```

add:

```php
<?php if ( comments_open() || get_comments_number() ) : ?>
    <?php comments_template(); ?>
<?php endif; ?>
```

Keep this inside the existing `$is_native_post` branch. Do not attach comments to arbitrary CPT single fallbacks.

- [ ] **Step 3: Add a surface-aware asset gate**

In `inc/theme/assets.php`, add:

```php
/** Determine whether the native Post comments surface will render. */
function should_enqueue_comments_assets(): bool {
    if ( ! function_exists( 'is_singular' ) || ! is_singular( 'post' ) ) {
        return false;
    }

    if ( ! function_exists( 'comments_open' ) || ! function_exists( 'get_comments_number' ) ) {
        return false;
    }

    return comments_open() || 0 < get_comments_number();
}

/** Enqueue native comments presentation and Core threaded-reply enhancement. */
function enqueue_comments_assets( ?string $version = null ): void {
    if ( ! should_enqueue_comments_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-comments',
        get_theme_file_uri( '/assets/css/components/comments.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        $version
    );

    if ( comments_open() && function_exists( 'get_option' ) && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
```

Call `enqueue_comments_assets( $version );` from `enqueue_assets()` after the native article stylesheet block. This keeps CSS and Core reply JS off non-comment surfaces.

- [ ] **Step 4: Add minimal production-safe `comments.css`**

Create `assets/css/components/comments.css` with X1-scoped selectors only:

```css
.aznet-theme-comments {
    width: 100%;
    max-width: var(--aznet-theme-container-content);
    margin: var(--aznet-theme-space-6) auto 0;
    color: var(--aznet-theme-text);
}

.aznet-theme-comments__title,
.aznet-theme-comments .comment-reply-title {
    margin-block: 0 var(--aznet-theme-space-4);
}

.aznet-theme-comments .comment-list,
.aznet-theme-comments .children {
    list-style: none;
    margin: 0;
    padding: 0;
}

.aznet-theme-comments .comment-list {
    display: grid;
    gap: var(--aznet-theme-space-4);
}

.aznet-theme-comments .children {
    margin-block-start: var(--aznet-theme-space-3);
    margin-inline-start: clamp(0.75rem, 3vw, 2rem);
}

.aznet-theme-comments .comment-body {
    min-width: 0;
    padding: var(--aznet-theme-space-4);
    border: 1px solid var(--aznet-theme-border);
    border-radius: var(--aznet-theme-radius-card);
    background: var(--aznet-theme-surface);
}

.aznet-theme-comments .comment-author {
    display: flex;
    flex-wrap: wrap;
    gap: var(--aznet-theme-space-2);
    align-items: center;
}

.aznet-theme-comments .avatar {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
}

.aznet-theme-comments .comment-meta {
    margin-block: var(--aznet-theme-space-2);
    color: var(--aznet-theme-muted);
    font-size: var(--aznet-theme-font-size-sm);
}

.aznet-theme-comments .comment-content {
    min-width: 0;
    overflow-wrap: anywhere;
}

.aznet-theme-comments .comment-reply-link:focus-visible,
.aznet-theme-comments a:focus-visible,
.aznet-theme-comments button:focus-visible,
.aznet-theme-comments input:focus-visible,
.aznet-theme-comments textarea:focus-visible {
    outline: 2px solid var(--aznet-theme-focus);
    outline-offset: 2px;
}

.aznet-theme-comments .comment-respond {
    margin-block-start: var(--aznet-theme-space-6);
}

.aznet-theme-comments :where(input[type="text"], input[type="email"], input[type="url"], textarea) {
    box-sizing: border-box;
    width: 100%;
    max-width: 100%;
}

.aznet-theme-comments textarea {
    min-height: 9rem;
    resize: vertical;
}

.aznet-theme-comments__closed {
    color: var(--aznet-theme-muted);
}

@media (max-width: 47.999rem) {
    .aznet-theme-comments .children {
        margin-inline-start: var(--aznet-theme-space-2);
    }
}
```

Do not generalize these form rules into global form primitives during X1; X5 owns that later extraction/normalization decision.

- [ ] **Step 5: Run GREEN + retained static regressions**

Run:

```bash
php tests/offline/x1-comments-surface-contract.php
php tests/offline/editorial-single-post-contract.php
php -l comments.php
php -l single.php
php -l inc/theme/assets.php
```

Expected:
- X1 contract: `PASS: X1 native comments presentation contract`
- editorial single-post contract: PASS
- every `php -l`: `No syntax errors detected`

- [ ] **Step 6: Commit the minimal GREEN implementation**

```bash
git add comments.php single.php inc/theme/assets.php assets/css/components/comments.css tests/offline/x1-comments-surface-contract.php
git commit -m "feat: add native comments presentation surface"
```

---

### Task 3: Add disposable WordPress L3 fixtures and retained X1 workflow

**Files:**
- Create: `tests/runtime/x1-comments-surface.php`
- Create: `.github/workflows/x1-comments-surface.yml`

**Interfaces:**
- Consumes: WordPress Core comment APIs and the Theme implementation from Task 2.
- Produces: deterministic fixture URL plus real WordPress evidence for threaded comments, existing-comment rendering, scoped CSS, and Core `comment-reply` loading.

- [ ] **Step 1: Add the idempotent disposable fixture script**

Create `tests/runtime/x1-comments-surface.php` to run under `wp eval-file`:

```php
<?php

declare(strict_types=1);

$slug = 'x1-comments-surface';
$post = get_page_by_path($slug, OBJECT, 'post');

$post_id = wp_insert_post([
    'ID'             => $post instanceof WP_Post ? $post->ID : 0,
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'post_title'     => 'X1 Comments Surface',
    'post_name'      => $slug,
    'post_content'   => '<p>X1 comments surface body.</p>',
    'comment_status' => 'open',
], true);

if (is_wp_error($post_id)) {
    fwrite(STDERR, 'FAIL: cannot create X1 post: ' . $post_id->get_error_message() . "\n");
    exit(1);
}

foreach (get_comments(['post_id' => $post_id, 'status' => 'all']) as $comment) {
    wp_delete_comment($comment->comment_ID, true);
}

$parent_id = wp_insert_comment([
    'comment_post_ID'      => $post_id,
    'comment_author'       => 'X1 Parent',
    'comment_author_email' => 'x1-parent@example.test',
    'comment_content'      => 'X1 parent comment',
    'comment_approved'     => 1,
]);

$child_id = wp_insert_comment([
    'comment_post_ID'      => $post_id,
    'comment_parent'       => $parent_id,
    'comment_author'       => 'X1 Child',
    'comment_author_email' => 'x1-child@example.test',
    'comment_content'      => 'X1 nested reply',
    'comment_approved'     => 1,
]);

if (! $parent_id || ! $child_id) {
    fwrite(STDERR, "FAIL: cannot create X1 comments\n");
    exit(1);
}

update_option('thread_comments', 1);
update_option('page_comments', 0);

if (2 !== (int) get_comments_number($post_id)) {
    fwrite(STDERR, "FAIL: expected two approved X1 comments\n");
    exit(1);
}

$url = get_permalink($post_id);
if (! is_string($url) || '' === $url) {
    fwrite(STDERR, "FAIL: missing X1 permalink\n");
    exit(1);
}

echo wp_json_encode([
    'post_id'   => $post_id,
    'parent_id' => $parent_id,
    'child_id'  => $child_id,
    'url'       => $url,
], JSON_UNESCAPED_SLASHES) . "\n";
```

This mutates only the disposable CI WordPress instance, never a pilot/production site.

- [ ] **Step 2: Add the retained GitHub Actions harness**

Create `.github/workflows/x1-comments-surface.yml` with `pull_request` + `workflow_dispatch`, scoped to X1-relevant paths. Follow the repository's existing PHP 8.1 / WordPress 6.9 / MySQL service pattern from the retained D-027 runtime/browser workflows. The workflow must perform these exact logical gates in order:

```yaml
- name: Run X1 offline RED-GREEN contract and retained editorial regression
  run: |
    php tests/offline/x1-comments-surface-contract.php
    php tests/offline/editorial-single-post-contract.php
    php -l comments.php
    php -l single.php
    php -l inc/theme/assets.php

- name: Seed native comments fixture
  run: |
    wp eval-file tests/runtime/x1-comments-surface.php --path=/tmp/wordpress > /tmp/x1-fixture.json
    jq -e '.post_id > 0 and .parent_id > 0 and .child_id > 0 and (.url | length > 0)' /tmp/x1-fixture.json
    jq -r '.url' /tmp/x1-fixture.json > /tmp/x1-url.txt

- name: Verify real WordPress comments HTML and scoped assets
  run: |
    URL="$(cat /tmp/x1-url.txt)"
    curl -fsSL "$URL" > /tmp/x1-comments.html
    grep -F 'id="comments"' /tmp/x1-comments.html
    grep -F 'comment-list' /tmp/x1-comments.html
    grep -F 'X1 parent comment' /tmp/x1-comments.html
    grep -F 'X1 nested reply' /tmp/x1-comments.html
    grep -F 'comment-reply-link' /tmp/x1-comments.html
    grep -F 'id="respond"' /tmp/x1-comments.html
    grep -F 'aznet-theme-comments-css' /tmp/x1-comments.html
    grep -F 'comment-reply-js' /tmp/x1-comments.html
```

Also curl a non-Post route (for example `/`) and assert `aznet-theme-comments-css` is absent there. This proves surface-aware loading instead of only proving presence.

- [ ] **Step 3: Run the workflow on the implementation branch**

Trigger through the PR or `workflow_dispatch` after the branch contains Tasks 1-3.

Expected L3 result:
- native Post renders two approved comments including one child reply;
- comment form is present because comments are open;
- X1 stylesheet loads on the Post and not on Home;
- Core `comment-reply` loads because threaded comments are enabled;
- no Theme-caused PHP fatal/warning appears in the workflow's runtime checks.

- [ ] **Step 4: Commit L3 harness**

```bash
git add tests/runtime/x1-comments-surface.php .github/workflows/x1-comments-surface.yml
git commit -m "test: add X1 comments runtime harness"
```

---

### Task 4: Add X1 L4 responsive, keyboard, focus, overflow, and a11y verification

**Files:**
- Create: `tests/browser/x1-comments-surface-l4.mjs`
- Modify: `.github/workflows/x1-comments-surface.yml`

**Interfaces:**
- Consumes: `X1_COMMENTS_URL` from Task 3 and existing Playwright/axe dependencies already used by `tests/browser/g-core-l4.mjs`.
- Produces: viewport-by-viewport evidence and screenshots for 1440x1000, 1024x900, and 390x844.

- [ ] **Step 1: Add the browser quality test**

Create `tests/browser/x1-comments-surface-l4.mjs` using the same `playwright` and `@axe-core/playwright` imports as the retained core browser test. Use these viewports exactly:

```js
const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
};
```

For each viewport, navigate to `process.env.X1_COMMENTS_URL`, then assert all of the following:

```js
const comments = page.locator('#comments.aznet-theme-comments');
await comments.waitFor({ state: 'visible' });

if (await page.locator('main').count() !== 1) {
  throw new Error('expected exactly one main landmark');
}
if (await page.locator('.comment-list').count() !== 1) {
  throw new Error('expected one native comment list');
}
if (await page.getByText('X1 parent comment', { exact: true }).count() !== 1) {
  throw new Error('missing parent comment');
}
if (await page.getByText('X1 nested reply', { exact: true }).count() !== 1) {
  throw new Error('missing nested reply');
}
if (await page.locator('#respond textarea#comment').count() !== 1) {
  throw new Error('missing native comment textarea');
}

const overflow = await page.evaluate(() =>
  Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth)
);
if (overflow !== 0) {
  throw new Error(`horizontal overflow: ${overflow}px`);
}
```

Verify visible keyboard focus by focusing `.comment-reply-link` and `#respond textarea#comment`, reading computed outline/box-shadow, and requiring a non-transparent visible indicator. Do not rely only on element focusability.

Run axe on `#comments` and fail on any violation whose impact is `critical` or `serious`:

```js
const axe = await new AxeBuilder({ page }).include('#comments').analyze();
const blocking = axe.violations.filter((violation) =>
  ['critical', 'serious'].includes(violation.impact)
);
if (blocking.length) {
  throw new Error(`blocking axe violations: ${blocking.length}`);
}
```

Capture console errors, `pageerror`, failed non-document subresources, stylesheet URLs, and a full-page screenshot for each viewport. Require zero console/page errors and zero failed subresources.

- [ ] **Step 2: Extend the X1 workflow with Node/Playwright execution**

After the runtime HTML gate, install the repository's Node dependencies and Chromium using the same mechanism as retained browser workflows, then run:

```bash
X1_COMMENTS_URL="$(cat /tmp/x1-url.txt)" \
X1_COMMENTS_STATE_DIR="/tmp/x1-comments-l4" \
node tests/browser/x1-comments-surface-l4.mjs
```

Upload `/tmp/x1-comments-l4` as the X1 browser evidence artifact even on failure.

- [ ] **Step 3: Verify L4 on all three viewports**

Expected fresh output:
- 3/3 viewport cases PASS;
- exactly one `main` landmark per case;
- parent and nested reply present;
- comment form present;
- horizontal overflow = 0;
- visible focus indicator for reply link and textarea;
- blocking axe violations = 0;
- console errors = 0;
- page errors = 0;
- failed subresources = 0.

- [ ] **Step 4: Commit the L4 harness**

```bash
git add tests/browser/x1-comments-surface-l4.mjs .github/workflows/x1-comments-surface.yml
git commit -m "test: verify X1 comments browser quality"
```

---

### Task 5: Run retained regressions, close X1 evidence/source, and prepare the owner-gated implementation PR

**Files:**
- Create: `docs/evidence/X1_COMMENTS_SURFACE_20260916.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: exact implementation head and fresh X1 L2-L4 workflow evidence.
- Produces: recoverable X1 PASS checkpoint with one Exact Next: X2 Search / 404 / Empty States.

- [ ] **Step 1: Run the complete X1 pre-closure verification**

On the exact final implementation head, require:

```bash
php tests/offline/x1-comments-surface-contract.php
php tests/offline/editorial-single-post-contract.php
php tests/offline/editorial-listing-search-contract.php
php tests/offline/d027-provisioning-independence-contract.php
php -l comments.php
php -l single.php
php -l inc/theme/assets.php
git diff --check main...HEAD
```

Then require the exact-head `X1 Comments Surface` GitHub Actions run to complete SUCCESS at L3/L4. Do not use a workflow result from an older head SHA.

Also inspect the final diff and confirm no changes under:
- `inc/integrations/`;
- provider-specific storage/contracts;
- Theme version declarations.

- [ ] **Step 2: Write the evidence record from actual run IDs/artifacts**

Create `docs/evidence/X1_COMMENTS_SURFACE_20260916.md` containing only facts supported by the fresh final-head run:

```markdown
# X1 Comments Surface — Verification Evidence

**Date:** 16/09/2026
**Scope:** AZnet Theme v1.2 X1 — Comments Surface
**Final verified head:** `<exact final implementation SHA from the successful run>`

## PASS

- L2: X1 native comments presentation contract PASS after verified RED on the pre-change tree.
- L3: real WordPress 6.9 native Post/comments/threaded-reply fixture PASS.
- L4: 1440x1000, 1024x900, 390x844 comments surface PASS with zero horizontal overflow, zero blocking axe violations, zero console/page errors, and zero failed subresources.
- Asset scope: `comments.css` present on the X1 Post fixture and absent on Home; Core `comment-reply` present only under native threaded-comment conditions.
- Ownership: no custom comment datastore/query/moderation/reply engine and no provider integration expansion.

## EVIDENCE

- X1 workflow run: `<actual run id>`
- Browser/runtime artifact: `<actual artifact id and digest if available>`
- RED checkpoint commit: `<actual RED commit>`
- GREEN implementation commit: `<actual GREEN commit>`

## UNKNOWN / NOT CLAIMED

- Provider L5 compatibility/certification is outside X1.
- `1.2.0` release/package/publication/deployment is not claimed.

## NEXT

X2 — Search / 404 / Empty States.
```

Replace each angle-bracket field with the actual verified value before committing; never commit the evidence document while those markers remain.

- [ ] **Step 3: Advance authoritative roadmap state only after evidence exists**

Revalidate the current source versions before editing. If they remain AZT-04 v0.43 and EXEC v0.35, make these bounded source changes:

- AZT-04 -> v0.44; change milestone X meaning to `X1 PASS / X2 NEXT`; add X1 PASS evidence reference; set Exact Next to X2 Search / 404 / Empty States.
- AZT-EXEC-MAP -> v0.36; mark X1 PASS, X2 NEXT; retain X3-X6 planned and all provider tracks separate.
- SOURCE_MANIFEST -> align AZT-04 v0.44 / EXEC v0.36 and X1 evidence/current state.

If live canonical source has advanced beyond those versions, stop the source edit, rebase/reconcile against the new owner rather than overwriting a newer source state.

- [ ] **Step 4: Run source/evidence consistency checks**

```bash
git diff --check
! grep -R -n -E '<actual |<exact |<verified |<run id>|<artifact id>|TBD|TODO|PLACEHOLDER' docs/evidence/X1_COMMENTS_SURFACE_20260916.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
```

Expected: exit code 0 and no placeholder markers.

- [ ] **Step 5: Commit X1 closure**

```bash
git add docs/evidence/X1_COMMENTS_SURFACE_20260916.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
git commit -m "docs: close X1 comments surface"
```

- [ ] **Step 6: Open the implementation PR and verify exact head**

Open a PR from the X1 implementation branch to `main` with:

```text
Title: feat: complete X1 native comments surface
```

The PR body must state:
- WordPress Core comments only;
- no provider integration expansion;
- RED -> GREEN evidence;
- exact X1 workflow run ID;
- L3/L4 result summary;
- final changed-file list;
- rollback: revert the bounded X1 template/assets/tests/source commits; WordPress comments remain untouched.

Before requesting owner merge approval, verify:
- PR is open, non-draft, mergeable;
- exact head SHA equals the verified workflow head;
- X1 workflow SUCCESS on that exact SHA;
- all retained automatically triggered workflows on that exact SHA are SUCCESS;
- no unresolved review threads;
- no unexpected production files outside the X1 scope;
- Theme metadata is still `1.1.0`.

Do not merge the production implementation PR without the explicit owner gate required by project governance.

---

## Self-Review Checklist

- Spec coverage: X1 native comments list, form, reply, pagination presentation, surface-aware assets, fail-soft password/no-surface behavior, responsive/a11y, rollback, and ownership boundaries are all mapped to Tasks 1-5.
- TDD: Task 1 establishes an intended RED; Task 2 is the minimal GREEN; Tasks 3-4 deepen evidence without changing domain ownership.
- File responsibility: comments markup, comments CSS, offline contract, runtime fixture, browser QA, CI harness, evidence, and source state each have one bounded role.
- No provider work: RootProfile/ConvertFlow/Woo/SEO behavior is not added or modified.
- No custom comment engine: WordPress native list/form/threading/pagination remain authoritative.
- Version discipline: no `1.2.0` promotion in X1.
- Rollback: reverting X1 Theme files leaves all WordPress-owned comments/data intact.
- Exact Next after X1 PASS: X2 Search / 404 / Empty States only.

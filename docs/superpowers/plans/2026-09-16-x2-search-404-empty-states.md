# X2 Search / 404 / Empty States Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn AZnet Theme's existing native Search, 404, and listing-empty fallbacks into clear, responsive, accessible recovery/discovery experiences without taking over WordPress query or routing semantics.

**Architecture:** Reuse the existing generic content shell and `assets/css/components/generic-content.css`; do not add a new runtime subsystem or a new stylesheet. `search.php`, `404.php`, and the archive no-content branch receive bounded recovery markup using only WordPress Core APIs (`get_search_form()`, `home_url()`, the native main Loop). A focused X2 contract, real WordPress 6.9 fixture, and Playwright/axe matrix prove query/routing ownership, HTTP semantics, keyboard/focus behavior, responsive layout, and regression safety.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP + `theme.json`, existing AZnet Theme tokens/components, WP-CLI, MySQL 8.0, GitHub Actions, Node 22, Playwright 1.55.0, `@axe-core/playwright` 4.10.2.

**Spec:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`

## Global Constraints

- Product scope is **AZnet Theme only**; canonical repository is `truongdinhnamaz/aznet-theme`.
- Governing rule: **SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.**
- WordPress owns search query semantics, the WordPress main query, 404 routing, pagination state, URLs, and native form submission semantics.
- Theme owns markup composition where WordPress expects Theme markup, presentation, responsive behavior, focus-visible states, and generic recovery presentation.
- X2 must not add a custom search query/ranking engine, redirect heuristics, `WP_Query`, `query_posts()`, `pre_get_posts`, private provider reads, SEO canonical/meta/schema/OG output, or provider integration changes.
- Theme metadata remains exactly `1.1.0`; `1.2.0` promotion is X6-only.
- Do not add a new CSS/JS asset for X2. Extend the already-scoped `generic-content.css`, which is already loaded on `is_archive()`, `is_search()`, and `is_404()` generic WordPress surfaces.
- Keep the existing Search results Loop, result cards, and `the_posts_pagination()` authoritative; X4 owns broader pagination/navigation completion.
- Keep `get_search_form()` native. Do not introduce a custom `searchform.php` in X2; X5 owns shared native form-control completion.
- Production behavior must follow RED -> intended failure -> minimal GREEN -> regression before L3/L4 claims.
- Provider L5 is outside v1.2 and must not be inferred.
- Merge to canonical `main` remains an explicit owner gate. Branch deletion remains a separate destructive gate.

---

## File map

**Production files to modify**

- `search.php` — preserve the native main Search Loop; improve the no-results recovery block only.
- `404.php` — preserve WordPress 404 routing/status; improve Theme-owned recovery markup only.
- `archive.php` — preserve the native archive Loop; improve only the existing no-content branch so generic empty-state presentation is consistent.
- `assets/css/components/generic-content.css` — extend existing generic-content/recovery presentation; no new asset handle.

**Verification files to create/modify**

- Create `tests/offline/x2-search-recovery-contract.php` — X2 ownership and presentation contract.
- Create `tests/runtime/x2-search-recovery.php` — native Search hit/miss, empty category, and WordPress 404 fixture.
- Create `tests/browser/x2-search-recovery-l4.mjs` — 12-case Search/404/empty-state browser/a11y matrix.
- Create `.github/workflows/x2-search-recovery.yml` — exact-candidate L1-L4 harness and artifact upload.
- Retain `tests/offline/editorial-listing-search-contract.php` unchanged unless a narrow, demonstrated contract invalidation requires a test-only correction.
- Retain `tests/browser/g-core-l4.mjs` unchanged; PR CI remains the broader WordPress-clean route regression.

**Closure files after functional PASS**

- Create `docs/evidence/X2_SEARCH_404_EMPTY_STATES_20260916.md`.
- Modify `docs/source/AZT-04-roadmap-qa-decisions.md` — X2 PASS / X3 NEXT and version bump from v0.44 to v0.45.
- Modify `docs/source/AZT-EXEC-MAP.md` — X2 PASS / X3 NEXT and version bump from v0.36 to v0.37.
- Modify `docs/source/SOURCE_MANIFEST.md` — align source versions/current exact next.

---

### Task 1: Establish X2 RED and minimal Search/404/empty-state GREEN

**Files:**
- Create: `tests/offline/x2-search-recovery-contract.php`
- Modify: `search.php`
- Modify: `404.php`
- Modify: `archive.php`
- Modify: `assets/css/components/generic-content.css`

**Interfaces:**
- Consumes: native WordPress main query/Loop, `get_search_query()`, `get_search_form()`, `home_url( '/' )`, existing `aznet-theme-content-shell`, `aznet-theme-listing`, `aznet-theme-entry`, and design tokens.
- Produces: `.aznet-theme-recovery`, `.aznet-theme-recovery__message`, `.aznet-theme-recovery__search`, `.aznet-theme-recovery__actions`, `.aznet-theme-recovery__home`, `.aznet-theme-recovery-page__code` markup/classes for runtime/browser verification.

- [ ] **Step 1: Create the failing X2 offline contract**

Create `tests/offline/x2-search-recovery-contract.php` with exactly this content:

```php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function fail_x2_recovery(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function source_x2_recovery(string $path): string {
    if (! is_file($path)) {
        fail_x2_recovery('missing required file: ' . $path);
    }

    $source = file_get_contents($path);
    if (false === $source) {
        fail_x2_recovery('cannot read required file: ' . $path);
    }

    return $source;
}

$search  = source_x2_recovery($root . '/search.php');
$notFound = source_x2_recovery($root . '/404.php');
$archive = source_x2_recovery($root . '/archive.php');
$css     = source_x2_recovery($root . '/assets/css/components/generic-content.css');

foreach ([
    'have_posts()',
    'the_post()',
    "get_template_part( 'template-parts/content/card' )",
    'the_posts_pagination()',
    'get_search_query()',
    'get_search_form()',
    'aznet-theme-recovery',
    'aznet-theme-recovery__search',
    'aznet-theme-recovery__home',
] as $marker) {
    if (! str_contains($search, $marker)) {
        fail_x2_recovery('search.php missing native/recovery marker: ' . $marker);
    }
}

foreach ([
    "home_url( '/' )",
    'get_search_form()',
    'aznet-theme-recovery-page__code',
    'aznet-theme-recovery',
    'aznet-theme-recovery__search',
    'aznet-theme-recovery__home',
] as $marker) {
    if (! str_contains($notFound, $marker)) {
        fail_x2_recovery('404.php missing recovery marker: ' . $marker);
    }
}

foreach ([
    'have_posts()',
    'the_post()',
    "get_template_part( 'template-parts/content/card' )",
    'the_posts_pagination()',
    'get_search_form()',
    'aznet-theme-recovery',
    'aznet-theme-recovery__home',
] as $marker) {
    if (! str_contains($archive, $marker)) {
        fail_x2_recovery('archive.php missing native/empty-state marker: ' . $marker);
    }
}

foreach ([
    '.aznet-theme-recovery',
    '.aznet-theme-recovery__message',
    '.aznet-theme-recovery__search',
    '.aznet-theme-recovery__actions',
    '.aznet-theme-recovery__home',
    '.aznet-theme-recovery-page__code',
    '.aznet-theme-recovery .search-form',
    '.aznet-theme-recovery .search-field',
    '.aznet-theme-recovery .search-submit',
    ':focus-visible',
    'overflow-wrap: anywhere',
] as $marker) {
    if (! str_contains($css, $marker)) {
        fail_x2_recovery('generic-content.css missing X2 presentation marker: ' . $marker);
    }
}

$templates = $search . "\n" . $notFound . "\n" . $archive;
$forbidden = [
    'new WP_Query',
    'WP_Query(',
    'query_posts(',
    'get_posts(',
    'pre_get_posts',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'wp_redirect(',
    'wp_safe_redirect(',
    'template_redirect',
    '<meta name=',
    'rel="canonical"',
    'application/ld+json',
    'schema.org',
    'choiceguide_',
    'RootProfile',
    'ConvertFlow',
];

foreach ($forbidden as $marker) {
    if (str_contains($templates, $marker)) {
        fail_x2_recovery('forbidden query/routing/storage/SEO/provider marker found: ' . $marker);
    }
}

echo "PASS: X2 native Search/404/empty-state presentation contract\n";
```

- [ ] **Step 2: Run the contract and verify intended RED**

Run:

```bash
php tests/offline/x2-search-recovery-contract.php
```

Expected result on the pre-X2 baseline:

```text
FAIL: search.php missing native/recovery marker: aznet-theme-recovery
```

If it errors for syntax/file reasons or fails on another unrelated prerequisite, fix the test only and rerun until the failure is specifically the missing X2 presentation behavior.

- [ ] **Step 3: Replace only the Search no-results branch with bounded recovery markup**

In `search.php`, keep the existing heading, `have_posts()` Loop, cards, and `the_posts_pagination()` unchanged. Replace the current `else` block with:

```php
<?php else : ?>
    <div class="aznet-theme-listing__empty aznet-theme-recovery">
        <p class="aznet-theme-recovery__message">
            <?php esc_html_e( 'No results found. Try another search or return to the homepage.', 'aznet-theme' ); ?>
        </p>
        <div class="aznet-theme-recovery__search">
            <?php get_search_form(); ?>
        </div>
        <p class="aznet-theme-recovery__actions">
            <a class="aznet-theme-recovery__home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php esc_html_e( 'Back to home', 'aznet-theme' ); ?>
            </a>
        </p>
    </div>
<?php endif; ?>
```

Do not add result-count queries, custom ranking, redirect logic, or alternate query objects.

- [ ] **Step 4: Replace the minimal 404 copy with a semantic recovery surface**

In `404.php`, keep `get_header()`, `content_shell_classes( false )`, one `main#main`, and `get_footer()`. Replace the section contents with:

```php
<section class="aznet-theme-entry aznet-theme-entry--error" aria-labelledby="aznet-theme-404-title">
    <header class="aznet-theme-entry__header">
        <p class="aznet-theme-recovery-page__code" aria-hidden="true">404</p>
        <h1 id="aznet-theme-404-title" class="aznet-theme-entry__title">
            <?php esc_html_e( 'Page not found', 'aznet-theme' ); ?>
        </h1>
    </header>

    <div class="aznet-theme-entry__content aznet-theme-recovery">
        <p class="aznet-theme-recovery__message">
            <?php esc_html_e( 'The page you were looking for could not be found. Try a search or return to the homepage.', 'aznet-theme' ); ?>
        </p>
        <div class="aznet-theme-recovery__search">
            <?php get_search_form(); ?>
        </div>
        <p class="aznet-theme-recovery__actions">
            <a class="aznet-theme-recovery__home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php esc_html_e( 'Back to home', 'aznet-theme' ); ?>
            </a>
        </p>
    </div>
</section>
```

Do not add redirects or infer a replacement URL from slug/title/Page ID/current URL.

- [ ] **Step 5: Upgrade only the archive no-content branch to the shared recovery class vocabulary**

In `archive.php`, keep the archive heading/description, native Loop, card template, and pagination unchanged. Replace the existing no-content paragraph with:

```php
<div class="aznet-theme-listing__empty aznet-theme-recovery">
    <p class="aznet-theme-recovery__message">
        <?php esc_html_e( 'No content was found in this archive. Try a search or return to the homepage.', 'aznet-theme' ); ?>
    </p>
    <div class="aznet-theme-recovery__search">
        <?php get_search_form(); ?>
    </div>
    <p class="aznet-theme-recovery__actions">
        <a class="aznet-theme-recovery__home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php esc_html_e( 'Back to home', 'aznet-theme' ); ?>
        </a>
    </p>
</div>
```

This is presentation only. Do not add an archive fallback query.

- [ ] **Step 6: Extend the existing generic-content stylesheet; do not create a new X2 asset**

Append the following rules to `assets/css/components/generic-content.css` after the existing `.aznet-theme-listing__empty` rule:

```css
.aznet-theme-recovery {
    display: grid;
    gap: var(--aznet-theme-space-4);
    min-width: 0;
    max-width: var(--aznet-theme-container-content);
}

.aznet-theme-recovery__message,
.aznet-theme-recovery__actions {
    margin: 0;
    overflow-wrap: anywhere;
}

.aznet-theme-recovery__search {
    width: 100%;
    max-width: 42rem;
}

.aznet-theme-recovery .search-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: var(--aznet-theme-space-2);
    align-items: end;
    margin: 0;
}

.aznet-theme-recovery .search-form label {
    display: block;
    min-width: 0;
}

.aznet-theme-recovery .search-field,
.aznet-theme-recovery .search-submit {
    min-height: 2.75rem;
    font: inherit;
}

.aznet-theme-recovery .search-field {
    width: 100%;
    min-width: 0;
    padding: var(--aznet-theme-space-2) var(--aznet-theme-space-3);
    color: var(--aznet-theme-text);
    background: var(--aznet-theme-surface);
    border: 1px solid var(--aznet-theme-border);
    border-radius: var(--aznet-theme-radius-control);
}

.aznet-theme-recovery .search-submit,
.aznet-theme-recovery__home {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: var(--aznet-theme-space-2) var(--aznet-theme-space-3);
    color: var(--aznet-theme-text);
    background: var(--aznet-theme-surface);
    border: 1px solid var(--aznet-theme-border);
    border-radius: var(--aznet-theme-radius-control);
    text-decoration: none;
    cursor: pointer;
}

.aznet-theme-recovery .search-submit:hover,
.aznet-theme-recovery__home:hover {
    border-color: var(--aznet-theme-accent);
}

.aznet-theme-recovery .search-field:focus-visible,
.aznet-theme-recovery .search-submit:focus-visible,
.aznet-theme-recovery__home:focus-visible {
    outline: 2px solid var(--aznet-theme-focus);
    outline-offset: 2px;
}

.aznet-theme-recovery-page__code {
    margin: 0 0 var(--aznet-theme-space-2);
    color: var(--aznet-theme-muted);
    font-size: var(--aznet-theme-font-size-sm);
    font-weight: 700;
    letter-spacing: 0.08em;
}

.aznet-theme-entry--error {
    padding-block: var(--aznet-theme-space-section);
}

@media (max-width: 30rem) {
    .aznet-theme-recovery .search-form {
        grid-template-columns: minmax(0, 1fr);
    }

    .aznet-theme-recovery .search-submit {
        width: 100%;
    }
}
```

Before implementing, verify that `--aznet-theme-radius-control` exists in the current token set. If the exact token does not exist, use the already-existing control/button radius token from `assets/css/tokens.css`; do **not** invent a new public token inside X2. This is the only permitted substitution in this CSS block and must be resolved from the canonical token file before commit.

- [ ] **Step 7: Verify GREEN plus retained listing regression**

Run:

```bash
php tests/offline/x2-search-recovery-contract.php
php tests/offline/editorial-listing-search-contract.php
php -l search.php
php -l 404.php
php -l archive.php
git diff --check
```

Expected:

```text
PASS: X2 native Search/404/empty-state presentation contract
PASS: editorial listing/search presentation contract
```

All PHP lint commands and `git diff --check` must succeed.

- [ ] **Step 8: Commit the bounded production slice**

```bash
git add search.php 404.php archive.php assets/css/components/generic-content.css tests/offline/x2-search-recovery-contract.php
git commit -m "feat: harden native search and recovery surfaces"
```

---

### Task 2: Prove X2 in real WordPress 6.9 runtime

**Files:**
- Create: `tests/runtime/x2-search-recovery.php`
- Create: `.github/workflows/x2-search-recovery.yml`

**Interfaces:**
- Consumes: X2 CSS/markup classes from Task 1 and WordPress Core search/category/404 routing.
- Produces: deterministic JSON fixture with `post_id`, `category_id`, `search_hit_url`, `search_miss_url`, `empty_archive_url`, and `not_found_url`; L3 HTML/runtime evidence under `/tmp/x2-search-recovery`.

- [ ] **Step 1: Create the native WordPress runtime fixture**

Create `tests/runtime/x2-search-recovery.php` with exactly this content. Do not add `declare(strict_types=1)` because WP-CLI `eval-file` evaluates this file in an existing context.

```php
<?php

$slug = 'x2-recovery-sentinel';
$post = get_page_by_path($slug, OBJECT, 'post');

$post_id = wp_insert_post([
    'ID'           => $post instanceof WP_Post ? $post->ID : 0,
    'post_type'    => 'post',
    'post_status'  => 'publish',
    'post_title'   => 'X2 Recovery Sentinel',
    'post_name'    => $slug,
    'post_content' => '<p>X2 recovery sentinel body.</p>',
], true);

if (is_wp_error($post_id)) {
    fwrite(STDERR, 'FAIL: cannot create X2 sentinel post: ' . $post_id->get_error_message() . "\n");
    exit(1);
}

$term = term_exists('x2-empty-category', 'category');
if (0 === $term || null === $term) {
    $term = wp_insert_term('X2 Empty Category', 'category', [
        'slug' => 'x2-empty-category',
    ]);
}

if (is_wp_error($term)) {
    fwrite(STDERR, 'FAIL: cannot create X2 empty category: ' . $term->get_error_message() . "\n");
    exit(1);
}

$category_id = is_array($term) ? (int) $term['term_id'] : (int) $term;
if ($category_id < 1) {
    fwrite(STDERR, "FAIL: invalid X2 empty category ID\n");
    exit(1);
}

wp_remove_object_terms($post_id, $category_id, 'category');

$home = home_url('/');
$payload = [
    'post_id'           => (int) $post_id,
    'category_id'       => $category_id,
    'search_hit_url'    => add_query_arg('s', 'Recovery Sentinel', $home),
    'search_miss_url'   => add_query_arg('s', 'No Such X2 Result 98431', $home),
    'empty_archive_url' => add_query_arg('cat', (string) $category_id, $home),
    'not_found_url'     => add_query_arg('p', '999999', $home),
];

echo wp_json_encode($payload, JSON_UNESCAPED_SLASHES) . "\n";
```

- [ ] **Step 2: Create the initial X2 workflow with L1-L3 gates**

Create `.github/workflows/x2-search-recovery.yml` with this content:

```yaml
name: X2 Search Recovery

on:
  push:
    branches:
      - feat/v1.2-x2-search-recovery
    paths:
      - 'search.php'
      - '404.php'
      - 'archive.php'
      - 'assets/css/components/generic-content.css'
      - 'tests/offline/x2-search-recovery-contract.php'
      - 'tests/offline/editorial-listing-search-contract.php'
      - 'tests/runtime/x2-search-recovery.php'
      - 'tests/browser/x2-search-recovery-l4.mjs'
      - '.github/workflows/x2-search-recovery.yml'
      - 'docs/evidence/X2_SEARCH_404_EMPTY_STATES_20260916.md'
      - 'docs/source/AZT-04-roadmap-qa-decisions.md'
      - 'docs/source/AZT-EXEC-MAP.md'
      - 'docs/source/SOURCE_MANIFEST.md'
  pull_request:
    branches:
      - main
    paths:
      - 'search.php'
      - '404.php'
      - 'archive.php'
      - 'assets/css/components/generic-content.css'
      - 'tests/offline/x2-search-recovery-contract.php'
      - 'tests/offline/editorial-listing-search-contract.php'
      - 'tests/runtime/x2-search-recovery.php'
      - 'tests/browser/x2-search-recovery-l4.mjs'
      - '.github/workflows/x2-search-recovery.yml'
      - 'docs/evidence/X2_SEARCH_404_EMPTY_STATES_20260916.md'
      - 'docs/source/AZT-04-roadmap-qa-decisions.md'
      - 'docs/source/AZT-EXEC-MAP.md'
      - 'docs/source/SOURCE_MANIFEST.md'
  workflow_dispatch:

permissions:
  contents: read

jobs:
  x2-search-recovery:
    runs-on: ubuntu-24.04
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: wordpress
        ports: ['3306:3306']
        options: >-
          --health-cmd="mysqladmin ping -h 127.0.0.1 -proot"
          --health-interval=10s --health-timeout=5s --health-retries=10

    steps:
      - name: Checkout exact X2 candidate
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Record exact identity
        run: |
          set -euo pipefail
          mkdir -p /tmp/x2-search-recovery
          git rev-parse HEAD | tee /tmp/x2-search-recovery/head-sha.txt

      - name: Set up PHP 8.1
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mysqli, mbstring
          coverage: none

      - name: Verify X2 ownership, diff and retained static regressions
        run: |
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
          if printf '%s\n' "$changed" | grep -F 'inc/theme/assets.php'; then
            echo 'FAIL: X2 must reuse the existing generic-content asset gate rather than add asset-loading behavior' >&2
            exit 1
          fi
          grep -F "define( 'AZNET_THEME_VERSION', '1.1.0' );" functions.php
          grep -F 'Version: 1.1.0' style.css
          php tests/offline/x2-search-recovery-contract.php
          php tests/offline/editorial-listing-search-contract.php
          php tests/offline/d027-provisioning-independence-contract.php
          php -l search.php
          php -l 404.php
          php -l archive.php
          php -l tests/runtime/x2-search-recovery.php
          echo 'PASS: X2 ownership, diff and retained static regressions'

      - name: Install clean WordPress 6.9 and exact Theme candidate
        run: |
          set -euo pipefail
          curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /tmp/wp-cli.phar
          chmod +x /tmp/wp-cli.phar
          sudo mv /tmp/wp-cli.phar /usr/local/bin/wp
          rm -rf /tmp/wp-site
          mkdir -p /tmp/wp-site
          wp core download --version=6.9 --path=/tmp/wp-site --force --allow-root
          wp config create --path=/tmp/wp-site --dbname=wordpress --dbuser=root --dbpass=root --dbhost=127.0.0.1 --skip-check --allow-root
          ADMIN_PASS=$(openssl rand -hex 16)
          wp core install --path=/tmp/wp-site --url=http://127.0.0.1:8080 --title='AZnet X2 Search Recovery' --admin_user=admin --admin_password="$ADMIN_PASS" --admin_email=x2@example.test --skip-email --allow-root
          mkdir -p /tmp/wp-site/wp-content/themes/aznet-theme
          rsync -a --delete \
            --exclude='.git' --exclude='.github' --exclude='docs' --exclude='scripts' --exclude='tests' \
            --exclude='README.md' --exclude='aznet-preview.png' \
            ./ /tmp/wp-site/wp-content/themes/aznet-theme/
          wp theme activate aznet-theme --path=/tmp/wp-site --allow-root
          wp plugin deactivate --all --path=/tmp/wp-site --allow-root || true
          test -z "$(wp plugin list --status=active --field=name --path=/tmp/wp-site --allow-root)"
          test "$(wp core version --path=/tmp/wp-site --allow-root)" = '6.9'

      - name: Seed native Search and recovery fixture
        run: |
          set -euo pipefail
          wp eval-file tests/runtime/x2-search-recovery.php --path=/tmp/wp-site --allow-root > /tmp/x2-search-recovery/fixture.json
          jq -e '.post_id > 0 and .category_id > 0 and (.search_hit_url | length > 0) and (.search_miss_url | length > 0) and (.empty_archive_url | length > 0) and (.not_found_url | length > 0)' /tmp/x2-search-recovery/fixture.json

      - name: Start WordPress runtime
        run: |
          set -euo pipefail
          nohup php -S 127.0.0.1:8080 -t /tmp/wp-site > /tmp/x2-search-recovery/server.log 2>&1 &
          echo $! > /tmp/x2-search-recovery/server.pid
          for attempt in $(seq 1 30); do
            if curl -fsS http://127.0.0.1:8080/wp-login.php >/tmp/x2-search-recovery/login-smoke.html; then break; fi
            sleep 1
          done
          grep -F 'user_login' /tmp/x2-search-recovery/login-smoke.html

      - name: Verify native Search hit miss archive-empty and 404 HTML
        run: |
          set -euo pipefail
          HIT_URL="$(jq -r '.search_hit_url' /tmp/x2-search-recovery/fixture.json)"
          MISS_URL="$(jq -r '.search_miss_url' /tmp/x2-search-recovery/fixture.json)"
          ARCHIVE_URL="$(jq -r '.empty_archive_url' /tmp/x2-search-recovery/fixture.json)"
          NOT_FOUND_URL="$(jq -r '.not_found_url' /tmp/x2-search-recovery/fixture.json)"

          curl -fsSL "$HIT_URL" > /tmp/x2-search-recovery/search-hit.html
          curl -fsSL "$MISS_URL" > /tmp/x2-search-recovery/search-miss.html
          curl -fsSL "$ARCHIVE_URL" > /tmp/x2-search-recovery/archive-empty.html
          code="$(curl -sS -o /tmp/x2-search-recovery/not-found.html -w '%{http_code}' "$NOT_FOUND_URL")"
          test "$code" = '404'

          grep -F 'X2 Recovery Sentinel' /tmp/x2-search-recovery/search-hit.html
          grep -F 'aznet-theme-listing__grid' /tmp/x2-search-recovery/search-hit.html

          grep -F 'aznet-theme-recovery' /tmp/x2-search-recovery/search-miss.html
          grep -F 'search-form' /tmp/x2-search-recovery/search-miss.html
          grep -F 'aznet-theme-recovery__home' /tmp/x2-search-recovery/search-miss.html

          grep -F 'aznet-theme-recovery' /tmp/x2-search-recovery/archive-empty.html
          grep -F 'search-form' /tmp/x2-search-recovery/archive-empty.html

          grep -F 'aznet-theme-recovery-page__code' /tmp/x2-search-recovery/not-found.html
          grep -F 'aznet-theme-recovery' /tmp/x2-search-recovery/not-found.html
          grep -F 'search-form' /tmp/x2-search-recovery/not-found.html
          grep -F 'aznet-theme-recovery__home' /tmp/x2-search-recovery/not-found.html

          for html in search-hit search-miss archive-empty not-found; do
            grep -F 'aznet-theme-generic-content-css' "/tmp/x2-search-recovery/${html}.html"
            if grep -E 'aznet-theme-(comments|woocommerce-(product|archive|cart|checkout|account))-css' "/tmp/x2-search-recovery/${html}.html"; then
              echo "FAIL: unrelated component stylesheet leaked to X2 ${html}" >&2
              exit 1
            fi
          done
          echo 'PASS: X2 WordPress runtime Search/404/empty-state behavior'

      - name: Verify runtime boundary
        run: |
          set -euo pipefail
          test -z "$(wp plugin list --status=active --field=name --path=/tmp/wp-site --allow-root)"
          if grep -E 'PHP (Fatal error|Parse error)|Uncaught' /tmp/x2-search-recovery/server.log; then
            echo 'FAIL: Theme runtime fatal/uncaught marker found during X2' >&2
            exit 1
          fi
          unexpected="$(grep 'PHP Warning:' /tmp/x2-search-recovery/server.log | grep -v 'Undefined array key 0 in /tmp/wp-site/wp-includes/class-wp-query.php on line 3872' || true)"
          test -z "$unexpected"
          echo 'PASS: X2 runtime boundary'

      - name: Stop WordPress runtime
        if: always()
        run: test ! -f /tmp/x2-search-recovery/server.pid || kill "$(cat /tmp/x2-search-recovery/server.pid)" || true

      - name: Upload X2 runtime evidence
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: x2-search-recovery-runtime
          path: /tmp/x2-search-recovery
          if-no-files-found: warn
```

- [ ] **Step 3: Push the feature branch and verify L3 GREEN**

Execute from a branch forked from current canonical `main`:

```bash
git add tests/runtime/x2-search-recovery.php .github/workflows/x2-search-recovery.yml
git commit -m "test: add X2 WordPress runtime gate"
git push -u origin feat/v1.2-x2-search-recovery
```

Expected GitHub Actions result: `X2 Search Recovery` succeeds through the runtime boundary on WordPress 6.9/PHP 8.1/zero active plugins.

If the empty-category route does not return the native archive surface, debug WordPress routing first; do not replace it with a custom query or redirect.

---

### Task 3: Add X2 browser/responsive/accessibility L4 matrix

**Files:**
- Create: `tests/browser/x2-search-recovery-l4.mjs`
- Modify: `.github/workflows/x2-search-recovery.yml`

**Interfaces:**
- Consumes: fixture URLs written by Task 2 and X2 classes from Task 1.
- Produces: `/tmp/x2-search-recovery-l4/summary.json`, screenshots, axe JSON, and a 12/12 browser matrix at desktop/tablet/mobile widths.

- [ ] **Step 1: Create the browser matrix**

Create `tests/browser/x2-search-recovery-l4.mjs` with exactly this content:

```javascript
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const fixturePath = process.env.X2_FIXTURE_PATH || '/tmp/x2-search-recovery/fixture.json';
const outputDir = process.env.X2_STATE_DIR || '/tmp/x2-search-recovery-l4';
const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');
fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
};

const routes = {
  'search-hit': { url: fixture.search_hit_url, status: 200, mode: 'hit' },
  'search-miss': { url: fixture.search_miss_url, status: 200, mode: 'recovery' },
  'archive-empty': { url: fixture.empty_archive_url, status: 200, mode: 'recovery' },
  'not-found': { url: fixture.not_found_url, status: 404, mode: 'recovery-404' },
};

const summary = { cases: [] };
const failures = [];

async function focusEvidence(page, selector) {
  const target = page.locator(selector).first();
  await target.waitFor({ state: 'visible' });
  await page.keyboard.press('Tab');
  await target.focus();
  return target.evaluate((element) => {
    const style = window.getComputedStyle(element);
    const outlineWidth = Number.parseFloat(style.outlineWidth || '0');
    return {
      visible: element.getBoundingClientRect().width > 1 && element.getBoundingClientRect().height > 1,
      indicator: style.outlineStyle !== 'none' && outlineWidth > 0 && style.outlineColor !== 'transparent',
    };
  });
}

async function inspect(browser, routeName, route, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  const failedSubresources = [];

  page.on('console', (message) => {
    if (message.type() !== 'error') return;
    const text = message.text();
    const expected404DocumentNoise = route.status === 404 && /^Failed to load resource: the server responded with a status of 404\b/.test(text);
    if (! expected404DocumentNoise) consoleErrors.push(text);
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('response', (response) => {
    if (response.status() < 400 || response.request().resourceType() === 'document') return;
    failedSubresources.push(`${response.status()} ${response.request().resourceType()} ${response.url()}`);
  });

  const key = `${routeName}-${viewportName}`;
  const result = {
    route: routeName,
    viewport: viewportName,
    status: 'failed',
    actualStatus: null,
    mainCount: null,
    overflowPx: null,
    blockingAxeViolations: null,
    error: null,
  };

  try {
    const response = await page.goto(route.url, { waitUntil: 'networkidle' });
    result.actualStatus = response?.status() ?? null;
    if (! response || response.status() !== route.status) {
      throw new Error(`expected HTTP ${route.status}, got ${response?.status() ?? 'missing'}`);
    }

    await page.locator('main#main').waitFor({ state: 'visible' });
    result.mainCount = await page.locator('main#main').count();
    if (result.mainCount !== 1) throw new Error(`expected one main#main, got ${result.mainCount}`);

    if (route.mode === 'hit') {
      await page.getByText('X2 Recovery Sentinel', { exact: false }).first().waitFor({ state: 'visible' });
      if (await page.locator('.aznet-theme-listing__grid').count() !== 1) throw new Error('search hit listing grid missing');
    } else {
      const recovery = page.locator('.aznet-theme-recovery').first();
      await recovery.waitFor({ state: 'visible' });
      await recovery.locator('.search-form').waitFor({ state: 'visible' });
      await recovery.locator('.aznet-theme-recovery__home').waitFor({ state: 'visible' });

      const fieldFocus = await focusEvidence(page, '.aznet-theme-recovery .search-field');
      const submitFocus = await focusEvidence(page, '.aznet-theme-recovery .search-submit');
      const homeFocus = await focusEvidence(page, '.aznet-theme-recovery__home');
      if (! fieldFocus.visible || ! fieldFocus.indicator) throw new Error('search field lacks visible focus evidence');
      if (! submitFocus.visible || ! submitFocus.indicator) throw new Error('search submit lacks visible focus evidence');
      if (! homeFocus.visible || ! homeFocus.indicator) throw new Error('home recovery link lacks visible focus evidence');
    }

    if (route.mode === 'recovery-404') {
      await page.locator('.aznet-theme-recovery-page__code').waitFor({ state: 'visible' });
      const heading = await page.locator('#aznet-theme-404-title').innerText();
      if (! /Page not found/i.test(heading)) throw new Error(`unexpected 404 heading: ${heading}`);
    }

    result.overflowPx = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (result.overflowPx > 1) throw new Error(`horizontal overflow: ${result.overflowPx}px`);

    const stylesheets = await page.locator('link[rel="stylesheet"]').evaluateAll((links) => links.map((link) => link.href));
    if (! stylesheets.some((href) => /generic-content\.css/i.test(href))) throw new Error('generic-content stylesheet missing');
    const unrelated = stylesheets.filter((href) => /comments\.css|woocommerce-(product|archive|cart|checkout|account)\.css/i.test(href));
    if (unrelated.length > 0) throw new Error(`unrelated component styles loaded: ${unrelated.join(', ')}`);

    const axeResults = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(axeDir, `${key}.json`), JSON.stringify(axeResults, null, 2));
    const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    if (blocking.length > 0) throw new Error(`blocking axe violations: ${blocking.map((item) => `${item.id}:${item.impact}`).join(', ')}`);

    if (failedSubresources.length > 0) throw new Error(`failed subresources: ${failedSubresources.join(' | ')}`);
    if (consoleErrors.length > 0) throw new Error(`console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`page errors: ${pageErrors.join(' | ')}`);

    await page.screenshot({ path: path.join(screenshotDir, `${key}.png`), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: X2 ${key}`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${key}: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, `${key}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

const browser = await chromium.launch({ headless: true });
try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    for (const [routeName, route] of Object.entries(routes)) {
      await inspect(browser, routeName, route, viewportName, viewport);
    }
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));

if (summary.cases.length !== 12) failures.push(`expected 12 cases, got ${summary.cases.length}`);
if (failures.length > 0) throw new Error(`X2 browser quality failed:\n${failures.join('\n')}`);
console.log('PASS: X2 Search/404/empty-state browser quality 12/12');
```

- [ ] **Step 2: Extend the X2 workflow with Node/Playwright/L4**

In `.github/workflows/x2-search-recovery.yml`, insert these steps immediately after the runtime HTML verification and before `Verify runtime boundary`:

```yaml
      - name: Set up Node 22
        uses: actions/setup-node@v4
        with:
          node-version: '22'

      - name: Install Playwright and axe
        run: |
          set -euo pipefail
          npm install --no-save --no-package-lock playwright@1.55.0 @axe-core/playwright@4.10.2
          npx playwright install --with-deps chromium

      - name: Run X2 browser matrix
        run: |
          set -euo pipefail
          X2_FIXTURE_PATH=/tmp/x2-search-recovery/fixture.json \
          X2_STATE_DIR=/tmp/x2-search-recovery-l4 \
          node tests/browser/x2-search-recovery-l4.mjs
```

Then add this assertion to the existing `Verify runtime boundary` step before the PASS echo:

```bash
test -f /tmp/x2-search-recovery-l4/summary.json
```

Finally replace the upload step with:

```yaml
      - name: Upload X2 evidence
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: x2-search-recovery-evidence
          path: |
            /tmp/x2-search-recovery
            /tmp/x2-search-recovery-l4
          if-no-files-found: warn
```

- [ ] **Step 3: Run and require the full focused X2 L1-L4 gate**

Commit and push:

```bash
git add tests/browser/x2-search-recovery-l4.mjs .github/workflows/x2-search-recovery.yml
git commit -m "test: add X2 search recovery browser matrix"
git push
```

Expected workflow outcome:

```text
PASS: X2 native Search/404/empty-state presentation contract
PASS: editorial listing/search presentation contract
PASS: X2 WordPress runtime Search/404/empty-state behavior
PASS: X2 Search/404/empty-state browser quality 12/12
PASS: X2 runtime boundary
```

Do not claim L4 PASS unless the exact current feature head has a completed-success X2 workflow and uploaded evidence artifact.

---

### Task 4: Open X2 PR and clear retained exact-head regression gates

**Files:**
- No production file should be added by default in this task.
- Modify test-only retained contracts only if a fresh PR failure is reduced to a stale/broad test assumption rather than a production defect.

**Interfaces:**
- Consumes: focused X2 PASS from Tasks 1-3 and the repository's retained PR workflows.
- Produces: an open PR with no unexplained red checks, no unresolved review threads, and a stable functional head eligible for source/evidence closure.

- [ ] **Step 1: Open the implementation PR**

Open a PR from `feat/v1.2-x2-search-recovery` to `main` titled:

```text
feat: complete X2 native search recovery surfaces
```

Use this PR body:

```markdown
## Scope
Complete X2 — Search / 404 / Empty States for v1.2 WordPress Experience Completion.

## Theme-owned changes
- preserve native WordPress Search/archive main queries and 404 routing
- improve Search no-result, archive-empty, and 404 recovery presentation
- reuse the existing generic-content asset; no new asset-loading subsystem
- add responsive/focus-visible recovery/search-form presentation

## Boundaries
- no custom query/ranking engine
- no redirect heuristics
- no provider integration changes
- no SEO/domain ownership
- Theme metadata remains `1.1.0`

## Verification
- RED -> GREEN X2 offline contract
- retained editorial listing/search contract
- WordPress 6.9 / PHP 8.1 / zero-plugin runtime
- Playwright + axe 12-case Search/404/empty-state matrix

## Next after merge
X3 — Media / Gallery / Embed. Provider L5 is not implied.
```

- [ ] **Step 2: Require all applicable exact-head PR workflows to finish**

Use GitHub commit/workflow status for the exact PR head. Do not merge while any applicable workflow is queued, in-progress, failed, cancelled, or unexplained.

- [ ] **Step 3: Debug any retained failure at the shallowest reproducible layer**

For every red retained workflow:

1. read the failing job log;
2. identify the first semantic failure;
3. reproduce it with the smallest retained contract if possible;
4. if X2 production violates ownership or behavior, add/strengthen the relevant X2 test first, watch it fail, then make the minimal production fix;
5. if the failure is only a stale historical test that scans a shared file too broadly, narrow that test to the exact allowed native WordPress behavior without weakening unrelated forbidden markers;
6. rerun the exact-head suite.

Do not convert a real production regression into a test exception.

- [ ] **Step 4: Review the final PR diff boundary**

The final production delta must remain limited to:

```text
search.php
404.php
archive.php
assets/css/components/generic-content.css
```

Verification/docs/workflow files may also change. Any change under `inc/integrations/`, `assets/css/integrations/`, provider source, `functions.php`, `style.css`, or new runtime asset-loading code is a blocker unless separately ratified.

- [ ] **Step 5: Commit any test-only retained-gate repairs separately**

If needed, use a bounded commit message such as:

```bash
git add tests/offline/<exact-retained-contract>.php
git commit -m "test: scope retained contract for X2 native recovery"
git push
```

Then require a fresh all-green exact PR head.

---

### Task 5: Record X2 evidence, reconcile source, and stop at the owner merge gate

**Files:**
- Create: `docs/evidence/X2_SEARCH_404_EMPTY_STATES_20260916.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: final functional PR head, focused X2 artifact IDs/digests, retained exact-head PR workflow results.
- Produces: canonical-review-ready source state `X2 PASS / X3 NEXT`; no release/version promotion.

- [ ] **Step 1: Write evidence from observed run IDs only**

Create `docs/evidence/X2_SEARCH_404_EMPTY_STATES_20260916.md` using this structure and replace each angle-bracket field only with a value read from the actual successful run/artifact/API response:

```markdown
# X2 Search / 404 / Empty States Closure Evidence — 16/09/2026

## Scope

X2 hardens native WordPress Search, 404, and listing-empty recovery presentation only. WordPress remains authoritative for Search/archive query semantics, the main query, HTTP 404 routing/status, URLs, and search-form submission semantics.

## RED -> GREEN

- RED contract: `tests/offline/x2-search-recovery-contract.php`.
- RED run: `<RED_RUN_ID>` failed for the intended missing `aznet-theme-recovery` presentation marker.
- Minimal GREEN preserved the native Search/archive Loops and added only recovery markup plus scoped generic-content presentation.
- Theme metadata remained `1.1.0`.

## L3 — WordPress runtime

- Exact functional head: `<FUNCTIONAL_HEAD_SHA>`.
- Successful X2 workflow: `<X2_RUN_ID>`.
- WordPress `6.9`, PHP `8.1`, zero active plugins.
- Native Search hit: PASS.
- Native Search miss/recovery: PASS.
- Empty category archive recovery: PASS.
- WordPress-owned 404 response remained HTTP `404`: PASS.
- No unrelated comments/Woo component stylesheet leaked onto X2 routes.

## L4 — Browser / responsive / accessibility

- Matrix: 4 routes x 3 viewports = **12/12 PASS** at `1440x1000`, `1024x900`, `390x844`.
- One `main#main`, zero horizontal overflow, visible recovery controls/focus indicators, zero critical/serious axe violations, zero unexpected console/page/subresource errors.
- Evidence artifact: `<ARTIFACT_ID>`.
- Artifact digest: `<ARTIFACT_DIGEST>`.

## Retained regression / ownership

- `editorial-listing-search-contract.php`: PASS.
- reusable v1 core PR gate: PASS.
- retained applicable PR workflows on exact final head: PASS.
- no provider integration file changed.
- no Theme version declaration changed.
- no custom search/ranking query or redirect logic was introduced.

## Rollback

Reverting X2 template/CSS commits restores the prior minimal presentation without changing WordPress posts, categories, Search state, routes, or provider data.

## PASS / UNKNOWN / NEXT

**PASS:** X2 Search / 404 / Empty States at L1-L4 on `<FINAL_VERIFIED_HEAD_SHA>`.

**UNKNOWN / not claimed:** provider L5, X3 behavior, merge-SHA post-merge runtime/browser state, v1.2 release/package closure.

**NEXT:** X3 — Media / Gallery / Embed implementation plan and review before production implementation.
```

If any required evidence value is not available, leave X2 **UNKNOWN** in source rather than inventing it. Do not commit literal `<...>` placeholders.

- [ ] **Step 2: Reconcile AZT-04 to X2 PASS / X3 NEXT**

In `docs/source/AZT-04-roadmap-qa-decisions.md`:

1. change `**Version:** v0.44` to `**Version:** v0.45`;
2. change the X workstream state from `X1 PASS / X2 NEXT` to `X2 PASS / X3 NEXT`;
3. add an X2 closure paragraph citing the exact final functional head, successful X2 run, evidence file, and WordPress ownership boundary;
4. set Exact Next to a reviewed implementation plan for **X3 — Media / Gallery / Embed**;
5. keep provider L5 explicitly separate.

- [ ] **Step 3: Reconcile the derived execution map**

In `docs/source/AZT-EXEC-MAP.md`:

1. change `**Version:** v0.36` to `**Version:** v0.37`;
2. mark X2 **PASS at L1-L4** with exact evidence reference;
3. mark X3 **NEXT**;
4. keep X1 retained PASS and X4-X6 pending;
5. set Exact Next to the reviewed X3 implementation plan.

- [ ] **Step 4: Align the source manifest**

In `docs/source/SOURCE_MANIFEST.md`:

- set AZT-04 to `v0.45`;
- set AZT-EXEC-MAP to `v0.37`;
- state that X1 and X2 are PASS at their verified scopes and X3 is exact next;
- add `docs/evidence/X2_SEARCH_404_EMPTY_STATES_20260916.md` as the X2 evidence companion;
- retain Theme metadata `1.1.0`, WordPress floor `6.9+`, PHP floor `8.1+`.

- [ ] **Step 5: Validate source/evidence consistency before commit**

Run:

```bash
git diff --check
grep -F '**Version:** v0.45' docs/source/AZT-04-roadmap-qa-decisions.md
grep -F '**Version:** v0.37' docs/source/AZT-EXEC-MAP.md
grep -F 'X2' docs/evidence/X2_SEARCH_404_EMPTY_STATES_20260916.md
grep -F 'X3' docs/source/SOURCE_MANIFEST.md
! grep -R '<RED_RUN_ID>\|<X2_RUN_ID>\|<ARTIFACT_ID>\|<ARTIFACT_DIGEST>\|<FUNCTIONAL_HEAD_SHA>\|<FINAL_VERIFIED_HEAD_SHA>' docs/evidence/X2_SEARCH_404_EMPTY_STATES_20260916.md
```

All commands must pass.

- [ ] **Step 6: Commit source/evidence closure and rerun exact-head PR checks**

```bash
git add docs/evidence/X2_SEARCH_404_EMPTY_STATES_20260916.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
git commit -m "docs: close X2 search recovery surfaces"
git push
```

Require the focused X2 workflow and every applicable retained PR workflow to complete successfully on this **final exact PR head**. Re-check that PR head SHA has not moved, the PR is mergeable, there are no unresolved review threads, and final changed filenames remain ownership-safe.

- [ ] **Step 7: Stop at the explicit owner merge gate**

Report:

```text
PASS — X2 L1-L4 on exact final PR head; source/evidence aligned to X2 PASS / X3 NEXT.
BLOCKED/GATE — merge to canonical main requires explicit owner approval.
NEXT — owner-approved merge of the verified X2 PR; after merge, re-read canonical main and only then start the X3 implementation-plan workflow.
```

Do not merge, delete the branch, promote version metadata, publish, deploy, or open provider L5 work without the corresponding explicit approval.

---

## Self-review results

- **Spec coverage:** Search query/main-loop ownership, WordPress 404 routing, Search/404/empty recovery presentation, focus/responsive/a11y, fail-soft behavior, no provider work, no custom query/ranking, version discipline, L1-L4 evidence, rollback, and exact Next are each mapped to a task.
- **Asset discipline:** X2 adds no new runtime asset; it extends the existing generic-content component already scoped to Search/archive/404 surfaces.
- **Placeholder discipline:** the implementation instructions contain no unresolved implementation placeholder. The evidence template intentionally uses angle-bracket evidence fields, and Task 5 explicitly forbids committing them; they must be replaced only by observed GitHub run/artifact values.
- **Interface consistency:** the runtime fixture keys exactly match the browser script and workflow (`search_hit_url`, `search_miss_url`, `empty_archive_url`, `not_found_url`). The CSS class names exactly match the offline contract, production markup, and browser selectors.
- **Ownership check:** no task requires provider code, private APIs, direct storage, a new query, a redirect heuristic, or version promotion.

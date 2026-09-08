# Native Editable Homepage Blueprint Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship a complete, native, editable professional-services Homepage blueprint for AZnet Theme so a static WordPress Front Page can be populated with one full-page Block Pattern and then edited as normal WordPress content.

**Architecture:** Keep `front-page.php` as the existing presentation shell and `the_content()` boundary. Add a Theme-owned full-page core-block pattern, a dedicated Front Page stylesheet, editor parity, and non-mutating Control Center setup guidance. WordPress remains owner of Page content/query/media; no provider-private data, SEO metadata, custom query engine, or automatic Page mutation is introduced.

**Tech Stack:** WordPress 6.9+ hybrid PHP theme, PHP 8.1+, `theme.json`, native Block Patterns/core blocks, PHP offline contracts, GitHub Actions, Playwright + axe for browser verification.

**Spec:** `docs/superpowers/specs/2026-09-08-native-editable-homepage-blueprint-design.md`

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Theme is PRESENTATION OWNER only; WordPress owns Page content/query/media.
- Minimum WordPress `6.9`, PHP `8.1`.
- Pattern slug: `aznet-theme/homepage-professional-services`.
- Pattern category: `aznet-theme-pages` -> `AZnet — Pages`.
- No auto-create, auto-publish, `page_on_front` mutation, or `post_content` overwrite.
- No RootProfile/ConvertFlow/Woo private storage/API reads or copied domain logic.
- No Theme-generated SEO title/meta/canonical/schema/OG.
- No custom `WP_Query` or related-content ranking engine.
- Frontend Homepage CSS must load only on `is_front_page()` and be scoped under `.aznet-theme-homepage-blueprint`.
- `front-page.php` remains unchanged unless a new failing test proves an independent shell defect.
- Production work uses RED -> minimal GREEN -> retained regression.
- Tag, GitHub Release, and final deployment remain P5 gates.

---

### Task 1: Lock Homepage blueprint ownership and structure with RED contracts

**Files:**
- Create: `tests/offline/homepage-blueprint-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: existing `patterns/`, `inc/theme/patterns.php`, `front-page.php` ownership boundary.
- Produces: a reusable offline gate that requires the approved full-page pattern and prevents domain/content mutation shortcuts.

- [ ] **Step 1: Add the failing Homepage blueprint contract**

Create `tests/offline/homepage-blueprint-contract.php` with checks equivalent to:

```php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$patternPath = $root . '/patterns/homepage-professional-services.php';
$patternsPath = $root . '/inc/theme/patterns.php';
$frontPagePath = $root . '/front-page.php';

function homepage_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

if (!is_file($patternPath)) {
    homepage_fail('homepage professional-services pattern missing');
}

$pattern = (string) file_get_contents($patternPath);
$patterns = (string) file_get_contents($patternsPath);
$frontPage = (string) file_get_contents($frontPagePath);

$required = [
    'Title: Homepage — Professional Services',
    'Slug: aznet-theme/homepage-professional-services',
    'Categories: aznet-theme-pages',
    'aznet-theme-homepage-blueprint',
    'aznet-theme-homepage-blueprint__hero',
    'aznet-theme-homepage-blueprint__trust',
    'id="services"',
    'aznet-theme-homepage-blueprint__about',
    'aznet-theme-homepage-blueprint__process',
    'aznet-theme-homepage-blueprint__articles',
    '<!-- wp:query ',
    '"postType":"post"',
    'aznet-theme-homepage-blueprint__faq',
    '<!-- wp:details -->',
    'id="contact"',
];
foreach ($required as $needle) {
    if (!str_contains($pattern, $needle)) {
        homepage_fail("pattern missing required marker: {$needle}");
    }
}

if (1 !== substr_count($pattern, '<h1')) {
    homepage_fail('homepage blueprint must contain exactly one H1');
}

if (!str_contains($patterns, "'aznet-theme-pages'")) {
    homepage_fail('AZnet Pages pattern category missing');
}

$forbidden = [
    'WP_Query', 'query_posts(', 'get_option(', 'update_option(',
    'get_post_meta(', 'update_post_meta(', '$wpdb', 'page_on_front',
    'wp_insert_post(', 'wp_update_post(', 'template_redirect',
    'choiceguide_', 'journey_', 'schema.org', 'application/ld+json',
    'rel="canonical"', 'og:',
];
foreach ($forbidden as $needle) {
    if (str_contains($pattern, $needle)) {
        homepage_fail("pattern contains forbidden coupling/mutation: {$needle}");
    }
}

if (!str_contains($frontPage, 'the_content();')) {
    homepage_fail('front-page.php must preserve WordPress Page content boundary');
}

echo "PASS: Homepage blueprint ownership/structure contract\n";
```

- [ ] **Step 2: Wire the contract into reusable core verification**

Append to `scripts/verify-v1-core.sh` after P3:

```bash
printf '%s\n' '==> P4 Native Editable Homepage blueprint contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-contract.php
```

- [ ] **Step 3: Run RED on the branch**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-contract.php
```

Expected: FAIL specifically with `homepage professional-services pattern missing`.

- [ ] **Step 4: Commit RED evidence**

```bash
git add tests/offline/homepage-blueprint-contract.php scripts/verify-v1-core.sh
git commit -m "test: define homepage blueprint contract"
```

---

### Task 2: Add the full-page native Block Pattern and Pages category

**Files:**
- Create: `patterns/homepage-professional-services.php`
- Modify: `inc/theme/patterns.php`
- Test: `tests/offline/homepage-blueprint-contract.php`

**Interfaces:**
- Consumes: WordPress core Group/Columns/Buttons/Query/Details blocks and existing `theme.json` presets.
- Produces: `aznet-theme/homepage-professional-services`, inserted by the user into a normal WordPress Page.

- [ ] **Step 1: Register the Pages pattern category**

Add to `register_pattern_categories()`:

```php
'aznet-theme-pages' => __( 'AZnet — Pages', 'aznet-theme' ),
```

Do not add a second registration system; native Theme pattern discovery remains responsible for `patterns/*.php`.

- [ ] **Step 2: Implement the minimal complete pattern**

Create `patterns/homepage-professional-services.php` using only core blocks. Its serialized content must use one root Group:

```php
<!-- wp:group {"align":"full","className":"aznet-theme-homepage-blueprint","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull aznet-theme-homepage-blueprint">
```

Within it, inline these sections in order:

1. Hero: class `aznet-theme-homepage-blueprint__hero`, exactly one `<h1>`, eyebrow, supporting paragraph, buttons linking to `#services` and `#contact`.
2. Trust: class `aznet-theme-homepage-blueprint__trust`, three editable columns with neutral non-claim starter copy.
3. Services: `id="services"`, class `aznet-theme-homepage-blueprint__services`, three editable service/expertise cards.
4. About/authority: class `aznet-theme-homepage-blueprint__about`, two columns and no RootProfile data reads.
5. Process: class `aznet-theme-homepage-blueprint__process`, three editable steps.
6. Articles: class `aznet-theme-homepage-blueprint__articles`, core Query block with `postType:"post"`, six latest Posts, native featured-image/title/date/excerpt blocks.
7. FAQ: class `aznet-theme-homepage-blueprint__faq`, at least three native Details blocks.
8. Final CTA: `id="contact"`, class `aznet-theme-homepage-blueprint__contact`, editable copy and core Button block.

Starter copy may describe generic professional-service concepts but must not claim client counts, certifications, outcomes, rankings, or legal authority. Do not place external URLs or numeric Page/Post IDs in the pattern.

- [ ] **Step 3: Run GREEN contract and PHP lint**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-contract.php
php -l patterns/homepage-professional-services.php
php -l inc/theme/patterns.php
```

Expected: all PASS.

- [ ] **Step 4: Commit the pattern GREEN**

```bash
git add patterns/homepage-professional-services.php inc/theme/patterns.php
git commit -m "feat: add native editable homepage blueprint"
```

---

### Task 3: Add Front Page-only presentation assets and Block Editor parity

**Files:**
- Create: `tests/offline/homepage-blueprint-asset-contract.php`
- Create: `assets/css/components/homepage.css`
- Modify: `inc/theme/assets.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: `AZNET_THEME_VERSION`, `is_front_page()`, WordPress enqueue APIs, design tokens.
- Produces: `enqueue_homepage_blueprint_asset(?string $version = null): void` and `enqueue_homepage_blueprint_editor_asset(): void`.

- [ ] **Step 1: Add the failing asset-scope contract**

Create `tests/offline/homepage-blueprint-asset-contract.php` that stubs `wp_enqueue_style()`, `get_theme_file_uri()`, and `is_front_page()` and asserts:

```php
$GLOBALS['homepage_is_front_page'] = false;
AZnet\Theme\enqueue_homepage_blueprint_asset('1.1.0');
assert([] === $GLOBALS['homepage_enqueued']);

$GLOBALS['homepage_is_front_page'] = true;
AZnet\Theme\enqueue_homepage_blueprint_asset('1.1.0');
assert(isset($GLOBALS['homepage_enqueued']['aznet-theme-homepage']));
assert(
    '/assets/css/components/homepage.css'
    === $GLOBALS['homepage_enqueued']['aznet-theme-homepage']['path']
);
```

The same contract must inspect `homepage.css` and fail unless all non-`@media` selectors are rooted in or target descendants of `.aznet-theme-homepage-blueprint`, and inspect `bootstrap.php` for `enqueue_block_editor_assets` wiring.

- [ ] **Step 2: Wire asset contract into reusable verification and prove RED**

Add:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-asset-contract.php
```

Run it before implementation. Expected: FAIL because `enqueue_homepage_blueprint_asset()`/`homepage.css` do not exist.

- [ ] **Step 3: Add frontend and editor enqueue helpers**

In `inc/theme/assets.php` add:

```php
function enqueue_homepage_blueprint_asset( ?string $version = null ): void {
    if ( ! function_exists( 'is_front_page' ) || ! is_front_page() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-homepage',
        get_theme_file_uri( '/assets/css/components/homepage.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}

function enqueue_homepage_blueprint_editor_asset(): void {
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;

    wp_enqueue_style(
        'aznet-theme-homepage-editor',
        get_theme_file_uri( '/assets/css/components/homepage.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}
```

Call `enqueue_homepage_blueprint_asset( $version );` from `enqueue_assets()`.

In `inc/theme/bootstrap.php` add:

```php
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_homepage_blueprint_editor_asset' );
```

Editor loading is acceptable because every presentation rule is class-scoped and inert without the blueprint root class.

- [ ] **Step 4: Implement scoped Homepage CSS**

Create `assets/css/components/homepage.css` with only blueprint-scoped selectors. Minimum presentation requirements:

```css
.aznet-theme-homepage-blueprint {
    overflow: clip;
}

.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__hero,
.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__trust,
.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__services,
.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__about,
.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__process,
.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__articles,
.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__faq,
.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__contact {
    padding-block: clamp(3rem, 7vw, 6rem);
}

.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__hero h1 {
    max-width: 18ch;
    text-wrap: balance;
}

.aznet-theme-homepage-blueprint .wp-block-post-template {
    gap: clamp(1rem, 2vw, 2rem);
}

.aznet-theme-homepage-blueprint .wp-block-post-featured-image img {
    width: 100%;
    height: auto;
    aspect-ratio: 3 / 2;
    object-fit: cover;
}

.aznet-theme-homepage-blueprint :where(h1, h2, h3, p, a) {
    overflow-wrap: anywhere;
}

.aznet-theme-homepage-blueprint .wp-block-table {
    overflow-x: auto;
}

@media (max-width: 782px) {
    .aznet-theme-homepage-blueprint .wp-block-columns {
        gap: 1.5rem;
    }
}
```

Expand the file only as needed to provide finished section/card hierarchy, CTA surfaces, Query cards and FAQ spacing using existing tokens; do not introduce JS.

- [ ] **Step 5: Run GREEN and retained asset regressions**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-asset-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r6-asset-scope-contract.php
php -l inc/theme/assets.php
php -l inc/theme/bootstrap.php
```

Expected: all PASS.

- [ ] **Step 6: Commit asset GREEN**

```bash
git add tests/offline/homepage-blueprint-asset-contract.php assets/css/components/homepage.css inc/theme/assets.php inc/theme/bootstrap.php scripts/verify-v1-core.sh
git commit -m "feat: scope homepage blueprint presentation assets"
```

---

### Task 4: Add non-mutating Homepage Setup guidance to Control Center

**Files:**
- Create: `tests/offline/homepage-blueprint-control-center-contract.php`
- Modify: `inc/admin/control-center.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: `get_option('show_on_front')`, `get_option('page_on_front')`, `get_edit_post_link()`, WordPress admin URLs.
- Produces: `render_homepage_setup_card(): void`, read-only guidance only.

- [ ] **Step 1: Add RED Control Center contract**

The contract must inspect `inc/admin/control-center.php` and require:

```text
render_homepage_setup_card
Homepage Setup
Homepage — Professional Services
AZnet — Pages
get_edit_post_link
show_on_front
page_on_front
```

It must fail if the Homepage setup implementation contains any of:

```text
wp_insert_post(
wp_update_post(
update_option(
set_theme_mod(
page_on_front',
post_content
```

The existing settings save/reset handlers elsewhere are not in scope; this guard applies specifically to the new Homepage setup helper body.

- [ ] **Step 2: Prove RED**

Run the new contract. Expected: FAIL with missing `render_homepage_setup_card`.

- [ ] **Step 3: Implement read-only setup helper**

Add a focused helper similar to:

```php
function render_homepage_setup_card(): void {
    $show_on_front = (string) get_option( 'show_on_front', 'posts' );
    $front_page_id = (int) get_option( 'page_on_front', 0 );

    echo '<div class="aznet-theme-panel"><h2>' . esc_html__( 'Homepage Setup', 'aznet-theme' ) . '</h2>';

    if ( 'page' === $show_on_front && $front_page_id > 0 ) {
        $edit_link = get_edit_post_link( $front_page_id, 'raw' );
        echo '<p>' . esc_html__( 'Trang chủ tĩnh đã được cấu hình. Mở trang này rồi chèn Patterns → AZnet — Pages → Homepage — Professional Services.', 'aznet-theme' ) . '</p>';
        if ( is_string( $edit_link ) && '' !== $edit_link ) {
            echo '<p><a class="button button-primary" href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Sửa Trang chủ', 'aznet-theme' ) . '</a></p>';
        }
    } else {
        echo '<p>' . esc_html__( 'Blueprint trang chủ được thiết kế cho một Trang tĩnh. Hãy tạo/chọn Page bằng WordPress rồi cấu hình nó làm Trang chủ.', 'aznet-theme' ) . '</p>';
        echo '<p><a class="button" href="' . esc_url( admin_url( 'options-reading.php' ) ) . '">' . esc_html__( 'Mở Cài đặt đọc', 'aznet-theme' ) . '</a></p>';
    }

    echo '<p class="description">' . esc_html__( 'Theme không tự tạo, ghi đè hoặc xuất bản nội dung Trang chủ.', 'aznet-theme' ) . '</p></div>';
}
```

Call it in the Overview after Quick Setup and before the generic card grid. Replace the old generic `Patterns` card with wording that points to the native Page editor but do not add a write action.

- [ ] **Step 4: Run GREEN plus retained R5 contract**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-control-center-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r5-control-center-static-contract.php
php -l inc/admin/control-center.php
```

Expected: all PASS.

- [ ] **Step 5: Commit Control Center GREEN**

```bash
git add tests/offline/homepage-blueprint-control-center-contract.php inc/admin/control-center.php scripts/verify-v1-core.sh
git commit -m "feat: guide native homepage setup"
```

---

### Task 5: Prove clean-WordPress runtime/browser quality and retained regressions

**Files:**
- Create: `tests/browser/homepage-blueprint-l4.mjs`
- Create: `.github/workflows/p4-homepage-blueprint-browser.yml`
- Create/Update: `docs/evidence/P4_HOMEPAGE_BLUEPRINT_L4.md` only after fresh evidence exists.

**Interfaces:**
- Consumes: registered Theme pattern content, `front-page.php`, Homepage CSS, native Query block.
- Produces: automated L3/L4 evidence for the complete Homepage blueprint on minimum-supported WordPress.

- [ ] **Step 1: Add browser assertions before production is considered complete**

`tests/browser/homepage-blueprint-l4.mjs` must test 1440x1000, 1024x900, 390x844 and 320x760. For each viewport assert:

```js
const mainCount = await page.locator('main#main').count();
if (mainCount !== 1) throw new Error(`expected one main#main, got ${mainCount}`);

const h1Count = await page.locator('.aznet-theme-homepage-blueprint h1').count();
if (h1Count !== 1) throw new Error(`expected one blueprint H1, got ${h1Count}`);

for (const selector of [
  '.aznet-theme-homepage-blueprint__hero',
  '.aznet-theme-homepage-blueprint__trust',
  '#services',
  '.aznet-theme-homepage-blueprint__about',
  '.aznet-theme-homepage-blueprint__process',
  '.aznet-theme-homepage-blueprint__articles',
  '.aznet-theme-homepage-blueprint__faq',
  '#contact',
]) {
  if (await page.locator(selector).count() < 1) throw new Error(`missing ${selector}`);
}

const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
if (overflow > 1) throw new Error(`horizontal overflow ${overflow}px`);
```

Also assert at least one Query article card after fixture Posts are created, at least three `<details>`, keyboard skip-link focus, no browser console/page errors, and axe critical/serious count `0`.

- [ ] **Step 2: Add disposable WordPress workflow**

Create `.github/workflows/p4-homepage-blueprint-browser.yml` triggered on pull requests touching Theme production/tests/workflow paths plus `workflow_dispatch`.

The workflow must:

1. checkout exact PR candidate;
2. install PHP 8.1 and MySQL;
3. download/install WordPress `6.9`;
4. activate exact Theme candidate;
5. locate registered pattern `aznet-theme/homepage-professional-services` via `WP_Block_Patterns_Registry`;
6. create a static Page using that registered pattern `content` in the disposable fixture;
7. set `show_on_front=page` and `page_on_front=<fixture id>` in the test environment only;
8. create at least six representative Vietnamese Posts with long titles/excerpts and featured-image fixtures where practical;
9. install Playwright/axe using the repo's retained browser dependency versions/pattern;
10. run `tests/browser/homepage-blueprint-l4.mjs`;
11. scan server/runtime logs for `PHP Fatal error`, `PHP Warning`, `PHP Parse error`, and `Uncaught`;
12. upload screenshots/summary/log evidence.

Production Theme code must not contain any of the fixture mutation steps.

- [ ] **Step 3: Run reusable core verification on final branch head**

Run:

```bash
bash scripts/verify-v1-core.sh
```

Expected: production PHP lint PASS and all retained R1-R6/P2/P3/Homepage contracts PASS.

- [ ] **Step 4: Require fresh GitHub workflow success**

On the final exact branch head require at minimum:

- `V1 Core Pull Request CI` SUCCESS;
- `F Homepage Native Regression Package` SUCCESS;
- new `P4 Homepage Blueprint Browser Quality` SUCCESS;
- retained Pattern/Header/Control Center/Woo workflows triggered by the touched paths must not regress.

Do not treat one workflow as evidence for another QA layer.

- [ ] **Step 5: Write evidence only from actual run IDs/artifacts**

Create `docs/evidence/P4_HOMEPAGE_BLUEPRINT_L4.md` with exact head SHA, workflow run IDs, environment versions, viewport matrix, axe/overflow/runtime-log results and candidate artifact/SHA. Do not pre-write PASS claims.

- [ ] **Step 6: Commit verification/evidence changes**

```bash
git add tests/browser/homepage-blueprint-l4.mjs .github/workflows/p4-homepage-blueprint-browser.yml docs/evidence/P4_HOMEPAGE_BLUEPRINT_L4.md
git commit -m "test: verify homepage blueprint runtime quality"
```

---

### Task 6: PR/package/pilot handoff without crossing P5 gates

**Files:**
- No additional production files unless verification exposes a concrete defect.
- Update P4 checkpoint evidence only with observed facts.

**Interfaces:**
- Consumes: final verified feature head.
- Produces: reviewable PR and installable pilot package; merge remains owner-gated.

- [ ] **Step 1: Review final diff against ownership boundary**

Verify no changes outside the approved Theme/docs/tests/workflow scope, and explicitly inspect for private-provider reads, Page mutation, custom query engines, SEO metadata generation, or hard-coded pilot-specific claims.

- [ ] **Step 2: Open feature PR to `main`**

PR body must state:

- concrete P4 invalidation: empty Page renders only shell;
- architecture: native editable full-page Pattern;
- RED->GREEN evidence;
- no automatic Page mutation;
- exact CI/runtime evidence;
- real-site pilot step after merge/package;
- no tag/GitHub Release/final deployment authorization.

- [ ] **Step 3: Stop at owner merge gate**

Do not merge production PR until the owner explicitly approves that PR.

- [ ] **Step 4: After approved merge, verify exact `main` and build pilot package**

Require fresh `V1 Exact Main Verification` on the merge SHA, then deterministic package build from exact merged bytes. Verify top-level `aznet-theme/`, `1.1.0` metadata consistency, PHP lint, exact-byte unzip, and compute SHA-256.

- [ ] **Step 5: Real pilot acceptance**

After the user replaces the Theme package on Tâm Đức Hà Nội:

1. open the existing static Front Page;
2. insert `Patterns -> AZnet — Pages -> Homepage — Professional Services` once;
3. publish the native Page content;
4. capture desktop/mobile real-site evidence;
5. replace starter copy/media with site-authoritative content as a WordPress content operation;
6. continue the remaining P4 route matrix.

Do not claim P4 complete until the real Homepage and remaining required pilot surfaces pass.

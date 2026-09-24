# Homepage Map Admin UX Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the AZnet Theme Homepage Control Center read like the actual live Homepage, using one shared effective-surface model so admin order, status and visible section summaries cannot drift from frontend composition.

**Architecture:** Reconcile the live 1.3.38 source into canonical Git first, then extract a request-local Law 01 effective-surface model from the existing WordPress-owned sources and Theme composition rules. Frontend renderers and the Homepage admin both consume that model; the primary admin surface becomes a one-column map of effective sections, while source mapping and migration controls move into a collapsed advanced disclosure.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, AZnet Theme hybrid PHP + `theme.json`, existing WordPress public APIs, scoped Control Center CSS, Node 22 + Playwright 1.55 + axe-core 4.10.2, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-09-24-homepage-map-admin-ux-design.md`

## Global Constraints

- Production implementation MUST begin from reconciled `lstamduchn.vn` AZnet Theme **1.3.38** bytes, not stale GitHub `main` metadata **1.3.32**.
- WordPress owns Pages, Posts, Categories, publication state, media, synced `wp_block` Hero content and native edit lifecycle.
- AZnet Theme owns Homepage composition, typed source references, presentation, request-local read models, Control Center presentation and frontend section anchors.
- Do not create a parallel content store, Theme CPT, provider-private read, slug/title/URL ownership heuristic, generic page-builder schema or DOM-scraping dependency.
- RootProfile, ConvertFlow and WooCommerce ownership/public-contract boundaries remain unchanged.
- Primary Homepage Map shows only **effective visible surfaces**; configured-but-not-effective state belongs under **Nguồn & cài đặt nâng cao**.
- `✓ Đang dùng` means the effective source is actually rendering, not merely that a configured WordPress object is published.
- About + Team remain one composite Profile map node while they are one composite frontend surface.
- Source switching/migration is secondary; editing the visible content is the primary path.
- All production-code tasks use RED → GREEN and preserve existing fail-soft behavior.
- Version promotion, packaging and production deployment remain separate approval gates after implementation/QA.

## Review Focus

1. **Configured draft Hero + public fallback Hero** — map must mark the fallback as active and the draft only as an advanced “Đang soạn” candidate.
2. **Services parent with more children than Homepage limit** — admin preview must list exactly the same bounded children/order as frontend, not every child Page.
3. **One valid and one invalid About/Team source** — Profile surface must remain visible with only the valid subregion and must not invent a missing half.
4. **Mapped optional sections with no renderable content** — they must disappear from the primary map but remain diagnosable under advanced controls.
5. **Narrow wp-admin viewport / keyboard-only use** — one-column map, disclosures, focus indicators and 44px practical targets must remain usable without horizontal overflow.

---

### Task 1: Reconcile the exact live 1.3.38 baseline into canonical source

**Files:**
- Modify: every production Theme path whose live 1.3.38 content differs from current canonical source.
- Create: `docs/evidence/HOMEPAGE_MAP_1_3_38_BASELINE_RECONCILIATION_20260924.md`
- Modify: `docs/source/AZT-03-baseline-provenance.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: active `lstamduchn.vn` Theme `aznet-theme` version 1.3.38 through the connected WPVibe site.
- Produces: one Git branch whose production tree is byte/content-equivalent to the verified live 1.3.38 source for every retrievable file, plus explicit evidence for any non-text file that cannot be reproduced exactly.

- [ ] **Step 1: Create an isolated implementation branch only after cloning the live Theme state**

Use the connected site to clone the active Theme into a WPVibe draft first:

```text
WPVibe create_draft_theme(site_url="https://lstamduchn.vn")
```

Verify with:

```text
WPVibe site_info(site_url="https://lstamduchn.vn")
Expected: active_theme.version = 1.3.38
```

Then create the Git implementation branch from the newest canonical `main`:

```bash
git fetch origin
git checkout -b work/homepage-map-v1-3-38-baseline origin/main
```

- [ ] **Step 2: Inventory the entire live Theme tree before writing any new Homepage Map code**

List the full WPVibe draft tree. Compare every live path against the Git worktree.

Required reconciliation rule:

```text
for each live Theme file:
  if Git file is missing -> add exact live file
  if Git file differs -> replace Git file with exact live content
  if Git has a production file absent from live -> record and remove only after proving it is not an intentionally excluded repository-only path
```

Repository-only paths such as `.github/`, `docs/`, `scripts/` and `tests/` are not expected in the installed Theme package and are not deleted during this comparison.

If a differing binary production file cannot be retrieved as exact bytes through the available site connector, stop this task as **BLOCKED** rather than recreating it.

- [ ] **Step 3: Verify the reconstructed worktree declares the live version**

Run:

```bash
grep -F 'Version: 1.3.38' style.css
grep -F "define( 'AZNET_THEME_VERSION', '1.3.38' );" functions.php
```

Expected: both commands return exactly one matching version declaration.

- [ ] **Step 4: Verify the known 1.3.35 → 1.3.38 production fixes are present**

Run:

```bash
grep -F 'max-height: calc(100dvh - 9rem);' assets/css/components/header-law-01.css
grep -F "Sửa nhanh chỉnh nội dung nguồn đã ánh xạ; Đổi nguồn chọn nguồn WordPress khác." inc/admin/homepage.php
grep -F "'READY' => [ 'label' => __( 'Đang dùng'" inc/admin/homepage.php
if grep -F 'Chỉnh đầy đủ' inc/admin/homepage.php; then exit 1; fi
```

Expected: first three checks match; the forbidden `Chỉnh đầy đủ` check returns no match.

- [ ] **Step 5: Run the full retained static/core chain on the reconciled source**

Run:

```bash
bash scripts/verify-v1-core.sh
```

Expected: exit 0. Any existing failing contract is named in the evidence and must be understood before proceeding.

- [ ] **Step 6: Record baseline evidence and update AZT-03**

Create `docs/evidence/HOMEPAGE_MAP_1_3_38_BASELINE_RECONCILIATION_20260924.md` containing:
- live site and verified active version;
- Git parent SHA;
- exact changed production paths introduced by reconciliation;
- verification command results;
- explicit statement that no Homepage Map production code has been added yet.

Update `docs/source/AZT-03-baseline-provenance.md` so 1.3.38 becomes the canonical implementation baseline for this workstream. Update `docs/source/SOURCE_MANIFEST.md` to point at the new AZT-03 version/evidence.

- [ ] **Step 7: Commit the reconciliation checkpoint**

```bash
git add .
git commit -m "chore: reconcile live 1.3.38 Theme baseline"
```

Do not start Task 2 unless Task 1 is PASS.

---

### Task 2: Ratify Homepage Map in authoritative source governance

**Files:**
- Modify: `docs/source/AZT-02-architecture.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: approved spec and Task 1 canonical 1.3.38 baseline.
- Produces: an Accepted internal architecture/UX decision authorizing one shared request-local effective-surface model and the Homepage Map execution slices.

- [ ] **Step 1: Add the architecture rule to AZT-02**

Add a bounded Control Center/Homepage composition rule stating:

```text
Homepage frontend composition and Homepage Control Center MUST consume the same
Theme-owned request-local effective-surface model. The model stores no content,
creates no provider/domain authority, and is not a public integration contract.
Configured-but-not-effective sources remain diagnostics/advanced state.
```

Also state explicitly that DOM scraping/iframe reverse-reading is forbidden for this model.

- [ ] **Step 2: Add an Accepted decision to AZT-04**

Record the owner-approved 2026-09-24 decision with these acceptance points:
- primary admin UX mirrors effective frontend reading order;
- source mapping moves to advanced disclosure;
- Profile groups About + Team while frontend does;
- stable frontend section anchors are Theme presentation only;
- L0 provenance reconciliation is a hard gate;
- release/deployment stay separate gates.

Use the next unused Decision Log identifier after the reconciled 1.3.38 baseline, not a number copied from stale history.

- [ ] **Step 3: Add execution slices to AZT-EXEC-MAP**

Add ordered slices:

```text
HM0 provenance/source ratification
HM1 effective-surface model
HM2 frontend model consumption + anchors
HM3 Homepage Map primary admin
HM4 advanced controls + authoring return anchors
HM5 runtime/browser/a11y/integration regression
HM6 release candidate closure
```

- [ ] **Step 4: Update SOURCE_MANIFEST references**

Register the approved spec and this implementation plan. Keep AZT-03 as provenance owner and AZT-04 as decision/roadmap owner.

- [ ] **Step 5: Commit source ratification**

```bash
git add docs/source
git commit -m "docs: ratify Homepage Map execution"
```

---

### Task 3: Build the shared Law 01 effective-surface model

**Files:**
- Create: `inc/theme/homepage-surface-map.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/theme/homepage-content-map.php`
- Test: `tests/offline/homepage-surface-map-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes:
  - `settings(): array`
  - `homepage_source_value(string $preset, string $slot, ?array $settings = null): mixed`
  - existing exact-ID WordPress source resolvers in `homepage-content-map.php`
  - existing Hero published/draft/fallback precedence.
- Produces:
  - `homepage_law01_surface_specs(string $variant): array`
  - `homepage_law01_surface_model(string $key, array $settings): ?array`
  - `homepage_effective_surface_map(?array $settings = null): array`

Each returned surface has this stable internal shape:

```php
[
    'key'      => 'services',
    'label'    => 'Dịch vụ',
    'region'   => 'before_content',
    'template' => 'services',
    'anchor'   => 'aznet-homepage-services',
    'status'   => 'active',
    'source'   => [
        'type'  => 'page',
        'id'    => 123,
        'title' => 'Dịch vụ',
    ],
    'model'    => [
        'summary' => [],
        'items'   => [],
    ],
]
```

- [ ] **Step 1: Write the RED contract for order, visibility and no parallel store**

Create `tests/offline/homepage-surface-map-contract.php` with:

```php
<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$source = file_get_contents($root . '/inc/theme/homepage-surface-map.php');
$composer = file_get_contents($root . '/inc/theme/homepage-composer.php');

assert(false !== $source);
foreach ([
    'function homepage_law01_surface_specs',
    'function homepage_law01_surface_model',
    'function homepage_effective_surface_map',
    "'hero'",
    "'services'",
    "'profile'",
    "'latest'",
    "'topics'",
    "'final-cta'",
    "'before_content'",
    "'after_content'",
] as $needle) {
    assert(str_contains($source, $needle), "Missing effective-surface contract: {$needle}");
}

foreach (['set_theme_mod(', 'update_option(', 'add_option(', 'wp_insert_post(', 'wp_update_post('] as $forbidden) {
    assert(! str_contains($source, $forbidden), "Surface model must remain read-only: {$forbidden}");
}

assert(! str_contains($composer, "[ 'hero', 'services', 'profile' ]"), 'Composer must stop owning a duplicate hard-coded pre-content order.');
echo "PASS: Homepage effective-surface model contract\n";
```

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-surface-map-contract.php
```

Expected: FAIL because `inc/theme/homepage-surface-map.php` and the three functions do not exist.

- [ ] **Step 3: Add the model file and bootstrap it before the composer**

In `inc/theme/bootstrap.php`, require the new file before `homepage-composer.php`:

```php
require_once __DIR__ . '/homepage-content-map.php';
require_once __DIR__ . '/homepage-surface-map.php';
require_once __DIR__ . '/homepage-composer.php';
```

Start `homepage-surface-map.php` with the ordered candidate manifest:

```php
<?php
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function homepage_law01_surface_specs( string $variant ): array {
    $after = 'burgundy-gold' === $variant
        ? [ 'latest', 'topics', 'analysis', 'news', 'process', 'faq', 'final-cta' ]
        : [ 'topics', 'latest', 'analysis', 'news', 'process', 'faq', 'final-cta' ];

    $specs = [
        'hero' => [ 'label' => 'Hero', 'region' => 'before_content', 'template' => 'hero', 'anchor' => 'aznet-homepage-hero' ],
        'services' => [ 'label' => 'Dịch vụ', 'region' => 'before_content', 'template' => 'services', 'anchor' => 'aznet-homepage-services' ],
        'profile' => [ 'label' => 'Giới thiệu & Đội ngũ', 'region' => 'before_content', 'template' => 'profile', 'anchor' => 'aznet-homepage-profile' ],
        'latest' => [ 'label' => 'Bài viết', 'region' => 'after_content', 'template' => 'latest', 'anchor' => 'aznet-homepage-articles' ],
        'topics' => [ 'label' => 'Chủ đề', 'region' => 'after_content', 'template' => 'topics', 'anchor' => 'aznet-homepage-topics' ],
        'analysis' => [ 'label' => 'Phân tích vụ việc', 'region' => 'after_content', 'template' => 'analysis', 'anchor' => 'aznet-homepage-analysis' ],
        'news' => [ 'label' => 'Tin pháp luật', 'region' => 'after_content', 'template' => 'news', 'anchor' => 'aznet-homepage-news' ],
        'process' => [ 'label' => 'Quy trình', 'region' => 'after_content', 'template' => 'process', 'anchor' => 'aznet-homepage-process' ],
        'faq' => [ 'label' => 'Câu hỏi thường gặp', 'region' => 'after_content', 'template' => 'faq', 'anchor' => 'aznet-homepage-faq' ],
        'final-cta' => [ 'label' => 'Liên hệ', 'region' => 'after_content', 'template' => 'final-cta', 'anchor' => 'aznet-homepage-contact' ],
    ];

    $order = array_merge( [ 'hero', 'services', 'profile' ], $after );
    return array_values( array_map( static fn( string $key ): array => [ 'key' => $key ] + $specs[ $key ], $order ) );
}
```

- [ ] **Step 4: Extract source resolution into read models instead of duplicating template guards**

Move the existing “is there renderable data?” logic from the Law 01 templates into `homepage_law01_surface_model()`.

The function must return `null` when the section would not render. For Services, use the exact existing bounded child resolver:

```php
$children = homepage_direct_published_children( $parent_id, 6 );
if ( [] === $children ) { return null; }
```

For Profile, return one model if either About or Team is valid:

```php
if ( ! $about instanceof \WP_Post && ! $team instanceof \WP_Post ) { return null; }
return [
    'about' => $about,
    'team'  => $team,
];
```

For Latest/Topics/Analysis/News/Process/FAQ/Final CTA, reuse the exact existing source/query helpers and limits already used by their templates. Do not broaden query scope.

For Hero, preserve the current published synced Hero → legacy/fallback precedence. A draft candidate is diagnostic state and must not replace the effective public model.

For Final CTA/contact, preserve the current mapped Contact Page plus accepted public provider enhancement rules. Provider absence must remove provider-only fields without removing a valid WordPress-owned contact fallback.

- [ ] **Step 5: Implement `homepage_effective_surface_map()`**

```php
function homepage_effective_surface_map( ?array $settings = null ): array {
    $settings = is_array( $settings ) ? $settings : settings();
    if ( 'law-01' !== (string) ( $settings['homepage_preset'] ?? 'off' ) ) { return []; }

    $variant = (string) ( $settings['homepage_law01_variant'] ?? 'navy-gold' );
    if ( ! in_array( $variant, [ 'navy-gold', 'burgundy-gold' ], true ) ) {
        $variant = 'navy-gold';
    }
    $surfaces = [];

    foreach ( homepage_law01_surface_specs( $variant ) as $spec ) {
        $model = homepage_law01_surface_model( (string) $spec['key'], $settings );
        if ( null === $model ) { continue; }
        $surfaces[] = $spec + [ 'status' => 'active', 'model' => $model ];
    }

    return $surfaces;
}
```

- [ ] **Step 6: Add Review Focus tests to the model contract**

Add fixture-level or source-level assertions that pin:
- draft Hero does not produce active Hero candidate when fallback is effective;
- Services uses limit `6`;
- Profile remains present when only About or Team resolves;
- null model removes optional surfaces.

Where a real WordPress object is required, add those assertions in Task 7 runtime fixtures rather than mocking `WP_Post`.

- [ ] **Step 7: Register the contract in the core verifier and run GREEN**

Add to `scripts/verify-v1-core.sh`:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-surface-map-contract.php
```

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-surface-map-contract.php
bash scripts/verify-v1-core.sh
```

Expected: both exit 0.

- [ ] **Step 8: Commit**

```bash
git add inc/theme tests/offline/homepage-surface-map-contract.php scripts/verify-v1-core.sh
git commit -m "feat: add Homepage effective surface model"
```

---

### Task 4: Make frontend composition consume the shared model and add stable anchors

**Files:**
- Modify: `inc/theme/homepage-composer.php`
- Modify: `template-parts/homepage/law-01/hero.php`
- Modify: `template-parts/homepage/law-01/services.php`
- Modify: `template-parts/homepage/law-01/profile.php`
- Modify: `template-parts/homepage/law-01/latest.php`
- Modify: `template-parts/homepage/law-01/topics.php`
- Modify: `template-parts/homepage/law-01/analysis.php`
- Modify: `template-parts/homepage/law-01/news.php`
- Modify: `template-parts/homepage/law-01/process.php`
- Modify: `template-parts/homepage/law-01/faq.php`
- Modify: `template-parts/homepage/law-01/final-cta.php`
- Test: `tests/offline/homepage-composer-contract.php`
- Test: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes: `homepage_effective_surface_map(): array`.
- Produces: frontend rendering whose order, visibility and anchors are sourced from the same surface entries the admin will consume.

- [ ] **Step 1: Write RED assertions that reject duplicate composer order**

In `tests/offline/homepage-composer-contract.php`, add:

```php
assert(str_contains($composer, 'homepage_effective_surface_map('));
assert(str_contains($composer, "\$surface['region']"));
assert(str_contains($composer, "\$surface['template']"));
assert(! str_contains($composer, "[ 'hero', 'services', 'profile' ]"));
assert(! str_contains($composer, "[ 'latest', 'topics', 'analysis', 'news', 'process', 'faq', 'final-cta' ]"));
```

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-composer-contract.php
```

Expected: FAIL because the current composer still owns hard-coded Law 01 section arrays.

- [ ] **Step 2: Render pre-content and post-content regions from the shared model**

Replace the Law 01 hard-coded loops with:

```php
$surfaces = homepage_effective_surface_map();

foreach ( $surfaces as $surface ) {
    if ( 'before_content' !== $surface['region'] ) { continue; }
    render_law01_part( (string) $surface['template'], [ 'surface' => $surface ] );
}
```

and in the after-content path:

```php
foreach ( homepage_effective_surface_map() as $surface ) {
    if ( 'after_content' !== $surface['region'] ) { continue; }
    render_law01_part( (string) $surface['template'], [ 'surface' => $surface ] );
}
```

Curtain 01 remains unchanged.

- [ ] **Step 3: Make Law 01 templates render the model passed by the composer**

At the top of each changed Law 01 template:

```php
$surface = is_array( $args['surface'] ?? null ) ? $args['surface'] : [];
$model = is_array( $surface['model'] ?? null ) ? $surface['model'] : [];
$anchor = (string) ( $surface['anchor'] ?? '' );
if ( [] === $model ) { return; }
```

Use model objects already resolved by Task 3. Remove duplicate source queries/guards that now belong to the shared model.

Do not change visible copy/layout in this task except the new wrapper IDs.

- [ ] **Step 4: Add the stable presentation anchors**

Expected wrapper IDs:

```text
aznet-homepage-hero
aznet-homepage-services
aznet-homepage-profile
aznet-homepage-articles
aznet-homepage-topics
aznet-homepage-analysis
aznet-homepage-news
aznet-homepage-process
aznet-homepage-faq
aznet-homepage-contact
```

Each template applies the model anchor and the shared surface key to its outer section:

```php
<section
    id="<?php echo esc_attr( $anchor ); ?>"
    data-aznet-homepage-surface="<?php echo esc_attr( (string) $surface['key'] ); ?>"
    class="..."
>
```

The `data-aznet-homepage-surface` attribute is a Theme-owned presentation/testing marker only; it is not a routing or domain contract.

- [ ] **Step 5: Extend the browser test to assert anchor/order parity**

In `tests/browser/homepage-law-01-l4.mjs`, add:

```js
const mapAnchors = [
  '#aznet-homepage-hero',
  '#aznet-homepage-services',
  '#aznet-homepage-profile',
  '#aznet-homepage-articles',
  '#aznet-homepage-topics',
  '#aznet-homepage-contact',
];

for (const selector of mapAnchors) {
  if (await page.locator(selector).count() !== 1) throw new Error(`missing Homepage Map frontend anchor ${selector}`);
}
```

Keep existing optional-section checks in the fixture where those sources are populated.

- [ ] **Step 6: Run GREEN and retained frontend regression**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-composer-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-surface-map-contract.php
bash scripts/verify-v1-core.sh
```

Then run the existing Homepage Law 01 browser workflow locally/CI on WordPress 6.9.

Expected: no composition, content, layout or a11y regression; only stable IDs and shared model ownership change.

- [ ] **Step 7: Commit**

```bash
git add inc/theme/homepage-composer.php template-parts/homepage/law-01 tests
git commit -m "refactor: drive Law 01 frontend from surface model"
```

---

### Task 5: Replace the source-first admin console with the primary Homepage Map

**Files:**
- Modify: `inc/admin/homepage.php`
- Modify: `assets/css/admin/control-center.css`
- Modify: `tests/offline/homepage-control-center-contract.php`
- Modify: `tests/browser/r5-control-center-l4.mjs`
- Modify: `.github/workflows/r5-control-center-browser.yml`

**Interfaces:**
- Consumes: `\AZnet\Theme\homepage_effective_surface_map(): array`.
- Produces:
  - `homepage_map_active_preset_label(): string`
  - `render_homepage_map(array $surfaces): void`
  - `render_homepage_map_card(array $surface): void`
  - primary map section `#aznet-theme-homepage-map`
  - one advanced disclosure `#aznet-theme-homepage-advanced`.

- [ ] **Step 1: Write RED admin contract**

Extend `tests/offline/homepage-control-center-contract.php`:

```php
foreach ([
    'render_homepage_map',
    'render_homepage_map_card',
    'Trang chủ đang hiển thị',
    'Chỉnh các phần theo đúng thứ tự đang hiển thị trên website.',
    'Luật 01',
    'Burgundy + Gold',
    'Nguồn & cài đặt nâng cao',
    'Xem trên trang chủ',
    'aznet-theme-homepage-map',
] as $required) {
    assert(str_contains($source, $required), "Missing Homepage Map admin contract: {$required}");
}

assert(str_contains($source, 'homepage_effective_surface_map'));
assert(! str_contains($source, "esc_html( (string) \$summary['status'] )"));
```

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-control-center-contract.php
```

Expected: FAIL because the Homepage Map functions/copy do not exist yet.

- [ ] **Step 2: Import the shared model into the admin module**

Add:

```php
use function AZnet\Theme\homepage_effective_surface_map;
```

Keep existing typed source/mapping helpers for the advanced disclosure.

- [ ] **Step 3: Render the active preset summary and the map before advanced settings**

Implement the preset label helper:

```php
function homepage_map_active_preset_label(): string {
    $settings = settings();
    $preset = (string) ( $settings['homepage_preset'] ?? 'off' );

    if ( 'law-01' === $preset ) {
        $variant = (string) ( $settings['homepage_law01_variant'] ?? 'navy-gold' );
        $variant_label = 'burgundy-gold' === $variant ? 'Burgundy + Gold' : 'Navy + Gold';
        return sprintf( __( 'Luật 01 · %s', 'aznet-theme' ), $variant_label );
    }

    if ( 'curtain-01' === $preset ) {
        return __( 'Rèm 01', 'aznet-theme' );
    }

    return __( 'WordPress mặc định', 'aznet-theme' );
}
```

Then render the map:

```php
function render_homepage_map( array $surfaces ): void {
    echo '<section id="aznet-theme-homepage-map" class="aznet-theme-homepage-map">';
    echo '<div class="aznet-theme-homepage-map__intro">';
    echo '<div><h2>' . esc_html__( 'Trang chủ đang hiển thị', 'aznet-theme' ) . '</h2>';
    echo '<p class="description">' . esc_html__( 'Chỉnh các phần theo đúng thứ tự đang hiển thị trên website.', 'aznet-theme' ) . '</p>';
    echo '<p class="aznet-theme-homepage-map__preset">' . esc_html( homepage_map_active_preset_label() ) . '</p></div>';
    echo '<a class="button" href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Xem trang chủ', 'aznet-theme' ) . '</a>';
    echo '</div>';

    foreach ( $surfaces as $index => $surface ) {
        render_homepage_map_card( $surface + [ 'position' => $index + 1 ] );
    }

    echo '</section>';
}
```

- [ ] **Step 4: Render one semantic card per effective frontend surface**

Each card starts with stable identity markup:

```php
$key = sanitize_html_class( (string) $surface['key'] );
echo '<article id="homepage-map-' . esc_attr( $key ) . '" class="aznet-theme-homepage-map__card" data-surface-key="' . esc_attr( $key ) . '">';
```

Each card includes:
- position number;
- label;
- `✓ Đang dùng`;
- source summary in muted text;
- current visible data summary;
- primary bounded edit action;
- deep link `home_url('/#' . $surface['anchor'])`.

Collection rows that are compared with frontend output use:

```php
echo '<li data-homepage-map-item>' . esc_html( $item_title ) . '</li>';
```

For Latest Articles, show the bounded rule in human wording such as `3 bài mới nhất` plus exactly the selected titles from the model. For Topics, show exactly the ordered category names currently rendered.

Do not show `Đổi nguồn` as a primary card action.

For the Profile surface, render two subregions from `$surface['model']['about']` and `$surface['model']['team']`; do not split them into two top-level cards.

For Services, render exactly `$surface['model']['items']` from the shared model.

Use these primary actions in the map:

| Surface | Primary authoring path |
| --- | --- |
| Hero | Render the existing bounded Hero simple form in the Hero card; button label **Sửa Hero**. Keep Hero variant/source library controls in Advanced. |
| Dịch vụ | Parent and visible child Pages use `render_homepage_quick_edit_form('law-01', 'services', $id, 'Chỉnh nội dung')`. |
| Giới thiệu & Đội ngũ | About uses slot `about`; Team uses slot `team`; each subregion gets its own **Chỉnh nội dung** disclosure. |
| Bài viết | Link to `admin_url('edit.php')` with label **Quản lý bài viết**; do not invent a Theme post editor. |
| Chủ đề | Each mapped term uses slot `knowledge` and the bounded category quick-edit form. |
| Phân tích vụ việc | Use slot `case_analysis` with the bounded category quick-edit form when this surface is effective. |
| Tin pháp luật | Use slot `legal_news` with the bounded category quick-edit form when this surface is effective. |
| Quy trình | Use slot `process` with the Page quick-edit form. |
| Câu hỏi thường gặp | Use slot `faq` with the Page quick-edit form. |
| Liên hệ | Use slot `contact` with the Page quick-edit form. |

Extend the existing helper without changing its current callers:

```php
function render_homepage_quick_edit_form(
    string $preset,
    string $slot,
    int $source_id,
    string $summary_label = ''
): void {
    $summary_label = '' !== $summary_label ? $summary_label : __( 'Sửa nhanh', 'aznet-theme' );
    // Existing bounded form body remains unchanged.
}
```

The map passes `__( 'Chỉnh nội dung', 'aznet-theme' )`; advanced/legacy callers may retain the default **Sửa nhanh** label.

- [ ] **Step 5: Move existing preset/mapping/migration UI into one advanced disclosure**

Wrap the current source-centric controls:

```php
echo '<details id="aznet-theme-homepage-advanced" class="aznet-theme-homepage-advanced">';
echo '<summary>' . esc_html__( 'Nguồn & cài đặt nâng cao', 'aznet-theme' ) . '</summary>';
// Existing preset, typed mapping, migration, shared-source diagnostics.
echo '</details>';
```

Once initialization is complete, do not show a migration action again; inside the advanced disclosure render at most a compact read-only “Đã khởi tạo mapping riêng” note.

If `homepage_preset` is `off` or unsupported, do not fabricate map cards. Render a primary native-state message:

```text
Trang chủ hiện đang dùng luồng WordPress mặc định. Chọn một mẫu trong Nguồn & cài đặt nâng cao để dùng Homepage Composer.
```

Keep the advanced disclosure available so the administrator can choose a supported preset.

- [ ] **Step 6: Add scoped one-column map CSS**

Append focused rules to `assets/css/admin/control-center.css`:

```css
.aznet-theme-homepage-map{display:grid;gap:16px;margin-top:20px}
.aznet-theme-homepage-map__intro{display:flex;justify-content:space-between;gap:16px;align-items:start}
.aznet-theme-homepage-map__card{display:grid;gap:14px;padding:18px;border:1px solid #dcdcde;border-radius:10px;background:#fff}
.aznet-theme-homepage-map__heading{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.aznet-theme-homepage-map__position{display:inline-grid;place-items:center;width:28px;height:28px;border-radius:999px;background:#f0f0f1;font-weight:600}
.aznet-theme-homepage-map__status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid #a9ddbc;border-radius:999px;background:#dff3e7;color:#116b3a;font-size:12px;font-weight:600}
.aznet-theme-homepage-map__actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.aznet-theme-homepage-map__actions .button{min-height:44px;display:inline-flex;align-items:center}
.aznet-theme-homepage-map__source{color:#646970}
.aznet-theme-homepage-advanced{margin-top:20px;padding:16px;border:1px solid #dcdcde;border-radius:10px;background:#f6f7f7}
.aznet-theme-homepage-advanced>summary{cursor:pointer;font-weight:600;min-height:44px;display:flex;align-items:center}
@media(max-width:782px){.aznet-theme-homepage-map__intro{display:grid}.aznet-theme-homepage-map__card{padding:14px}}
```

- [ ] **Step 7: Add browser assertions for primary UX**

In `verifyHomepageHeroEditingBridge()` or a new focused `verifyHomepageMap()` helper in `tests/browser/r5-control-center-l4.mjs`, assert:

```js
await gotoCenter(page, 'homepage');

const map = page.locator('#aznet-theme-homepage-map');
await map.waitFor({ state: 'visible' });

const labels = await map.locator('.aznet-theme-homepage-map__card h3').allTextContents();
if (labels[0] !== 'Hero' || labels[1] !== 'Dịch vụ') {
  throw new Error(`Homepage Map order does not start with frontend order: ${JSON.stringify(labels)}`);
}

if (await map.getByText('READY', { exact: true }).count()) throw new Error('raw READY leaked into primary Homepage Map');
if (await map.getByText('DRAFT', { exact: true }).count()) throw new Error('raw DRAFT leaked into primary Homepage Map');
if (await map.getByRole('link', { name: 'Đổi nguồn' }).count()) throw new Error('Đổi nguồn must remain advanced-only');
```

Also assert Profile appears once as `Giới thiệu & Đội ngũ`, the active preset summary is visible, and a separate fixture with `homepage_preset=off` shows the native-state explanation with zero `.aznet-theme-homepage-map__card` elements.

- [ ] **Step 8: Extend R5 workflow triggers**

Add these paths to `.github/workflows/r5-control-center-browser.yml`:

```yaml
      - 'inc/theme/homepage-surface-map.php'
      - 'inc/theme/homepage-composer.php'
      - 'template-parts/homepage/law-01/**'
      - 'tests/offline/homepage-surface-map-contract.php'
```

- [ ] **Step 9: Run GREEN**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-control-center-contract.php
bash scripts/verify-v1-core.sh
```

Then run the R5 authenticated Control Center workflow in clean WordPress and Woo-present matrices.

- [ ] **Step 10: Commit**

```bash
git add inc/admin/homepage.php assets/css/admin/control-center.css tests .github/workflows/r5-control-center-browser.yml
git commit -m "feat: add Homepage Map admin experience"
```

---

### Task 6: Make bounded authoring return to the edited map section and keep candidate diagnostics advanced

**Files:**
- Modify: `inc/admin/homepage-authoring.php`
- Modify: `inc/admin/homepage.php`
- Modify: `inc/admin/homepage-hero.php`
- Test: `tests/offline/homepage-map-authoring-contract.php`
- Modify: `tests/browser/r5-control-center-l4.mjs`

**Interfaces:**
- Consumes: map surface keys/anchors from Task 3.
- Produces:
  - `homepage_map_admin_anchor(string $slot): string`
  - post-save redirects to `#homepage-map-<surface>`
  - advanced diagnostics for configured draft/invalid/hidden sources.

- [ ] **Step 1: Write RED redirect/status contract**

Create `tests/offline/homepage-map-authoring-contract.php`:

```php
<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$authoring = file_get_contents($root . '/inc/admin/homepage-authoring.php');
$homepage = file_get_contents($root . '/inc/admin/homepage.php');

assert(str_contains($authoring, 'function homepage_map_admin_anchor'));
assert(str_contains($authoring, '#homepage-map-'));
assert(str_contains($homepage, 'Ẩn trên trang chủ'));
assert(str_contains($homepage, 'Đang soạn'));
assert(str_contains($homepage, 'Nguồn & cài đặt nâng cao'));
echo "PASS: Homepage Map authoring return/diagnostic contract\n";
```

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-map-authoring-contract.php
```

Expected: FAIL because the return-anchor helper and advanced status wording are not implemented yet.

- [ ] **Step 2: Add one slot-to-map anchor helper**

In `inc/admin/homepage-authoring.php`:

```php
function homepage_map_admin_anchor( string $slot ): string {
    return match ( $slot ) {
        'about', 'team' => 'homepage-map-profile',
        'knowledge' => 'homepage-map-topics',
        'contact' => 'homepage-map-final-cta',
        default => 'homepage-map-' . sanitize_html_class( $slot ),
    };
}
```

Use the actual reconciled slot names from Task 1; keep this map aligned with the shared surface keys, not display labels.

- [ ] **Step 3: Preserve location after Quick Edit**

Replace the current plain redirect with:

```php
$redirect = add_query_arg(
    [ 'page' => 'aznet-theme', 'section' => 'homepage', 'homepage_quick_edited' => '1' ],
    admin_url( 'admin.php' )
);
wp_safe_redirect( $redirect . '#' . homepage_map_admin_anchor( $slot ) );
exit;
```

Apply the same pattern to explicit source separation and simple Hero saves, returning to the corresponding map node.

- [ ] **Step 4: Keep candidate state in advanced diagnostics**

The primary map reads only `homepage_effective_surface_map()`.

In advanced diagnostics:
- configured draft Hero => `• Đang soạn`;
- configured valid but currently unrendered source => `Ẩn trên trang chủ`;
- invalid reference => `! Nguồn lỗi`;
- unmapped => `• Chưa chọn`.

Do not promote those candidate states into the primary map.

- [ ] **Step 5: Browser-test save-and-return behavior**

In R5:

```js
const servicesCard = page.locator('#homepage-map-services');
await servicesCard.getByText('Sửa nhanh').first().click();
// change a bounded field in the fixture
await Promise.all([
  page.waitForURL(/section=homepage.*#homepage-map-services|#homepage-map-services/),
  servicesCard.getByRole('button', { name: 'Lưu sửa nhanh' }).click(),
]);
if (!page.url().endsWith('#homepage-map-services')) throw new Error('Quick Edit did not return to Services map node');
```

For Hero draft-first fixture, assert the public fallback card remains `✓ Đang dùng` while the draft candidate is visible only in advanced diagnostics as `Đang soạn`.

- [ ] **Step 6: Run GREEN**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-map-authoring-contract.php
bash scripts/verify-v1-core.sh
```

Run R5 browser workflow and verify both desktop and narrow admin viewports.

- [ ] **Step 7: Commit**

```bash
git add inc/admin tests
git commit -m "feat: preserve Homepage Map authoring context"
```

---

### Task 7: Prove runtime parity between admin map and frontend

**Files:**
- Create: `tests/runtime/homepage-map-runtime.php`
- Modify: `tests/browser/r5-control-center-l4.mjs`
- Modify: `tests/browser/homepage-law-01-l4.mjs`
- Modify: `.github/workflows/r5-control-center-browser.yml`
- Modify: `.github/workflows/homepage-law-01-browser.yml`
- Create: `docs/evidence/HOMEPAGE_MAP_RUNTIME_BROWSER_20260924.md`

**Interfaces:**
- Consumes: shared model + frontend anchors + admin map.
- Produces: L3/L4 evidence that effective section order and visible items match on both surfaces.

- [ ] **Step 1: Create a real WordPress runtime fixture test**

Create `tests/runtime/homepage-map-runtime.php` as a `wp eval-file` fixture/assertion script:

```php
<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$insert_page = static function ( string $title, int $parent = 0, string $excerpt = '', string $content = '' ): int {
    $id = wp_insert_post(
        [
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_parent'  => $parent,
            'post_excerpt' => $excerpt,
            'post_content' => $content,
        ],
        true
    );
    if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
    return (int) $id;
};

$front_id = $insert_page( 'Homepage Map Front', 0, 'Fallback Hero excerpt', '<p>Fallback Hero body</p>' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $front_id );

$services_id = $insert_page( 'Dịch vụ', 0, 'Dịch vụ cho cá nhân và doanh nghiệp' );
for ( $i = 1; $i <= 8; $i++ ) {
    $child_id = $insert_page( 'Dịch vụ ' . $i, $services_id, 'Mô tả dịch vụ ' . $i );
    wp_update_post( [ 'ID' => $child_id, 'menu_order' => $i ] );
}

$about_id = $insert_page( 'Giới thiệu', 0, 'Giới thiệu văn phòng' );
$team_id = $insert_page( 'Đội ngũ', 0, 'Đội ngũ tư vấn' );
$contact_id = $insert_page( 'Liên hệ', 0, 'Thông tin liên hệ' );
$faq_id = $insert_page( 'Câu hỏi thường gặp', 0, '', '' );

$term = wp_insert_term( 'Kiến thức pháp luật', 'category' );
if ( is_wp_error( $term ) ) { throw new RuntimeException( $term->get_error_message() ); }
$term_id = (int) $term['term_id'];

for ( $i = 1; $i <= 3; $i++ ) {
    $post_id = wp_insert_post(
        [
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => 'Bài viết ' . $i,
            'post_content' => '<p>Nội dung bài viết</p>',
        ],
        true
    );
    if ( is_wp_error( $post_id ) ) { throw new RuntimeException( $post_id->get_error_message() ); }
    wp_set_post_categories( (int) $post_id, [ $term_id ] );
}

$draft_hero_id = wp_insert_post(
    [
        'post_type' => 'wp_block',
        'post_status' => 'draft',
        'post_title' => 'Hero draft candidate',
        'post_content' => '<!-- wp:heading {"level":1} --><h1>Draft Hero must not be public</h1><!-- /wp:heading -->',
    ],
    true
);
if ( is_wp_error( $draft_hero_id ) ) { throw new RuntimeException( $draft_hero_id->get_error_message() ); }

set_theme_mod(
    'aznet_theme_settings',
    \AZnet\Theme\normalize_settings(
        [
            'schema_version' => 3,
            'homepage_preset' => 'law-01',
            'homepage_law01_variant' => 'burgundy-gold',
            'homepage_hero_block' => (int) $draft_hero_id,
            'homepage_services_page' => $services_id,
            'homepage_about_page' => $about_id,
            'homepage_team_page' => $team_id,
            'homepage_knowledge_terms' => [ $term_id ],
            'homepage_faq_page' => $faq_id,
            'homepage_contact_page' => $contact_id,
        ]
    )
);

$surfaces = \AZnet\Theme\homepage_effective_surface_map();
$keys = array_column( $surfaces, 'key' );

assert( 'hero' === $keys[0] );
assert( 'services' === $keys[1] );
assert( in_array( 'profile', $keys, true ) );
assert( ! in_array( 'faq', $keys, true ), 'Empty FAQ must not enter the effective primary map.' );

$services = current( array_filter( $surfaces, static fn( array $surface ): bool => 'services' === $surface['key'] ) );
assert( 6 === count( $services['model']['items'] ) );

$hero = current( array_filter( $surfaces, static fn( array $surface ): bool => 'hero' === $surface['key'] ) );
assert( (int) $draft_hero_id !== (int) ( $hero['source']['id'] ?? 0 ), 'Draft Hero candidate must not become the effective public Hero.' );

echo wp_json_encode(
    [
        'keys' => $keys,
        'services' => count( $services['model']['items'] ),
    ],
    JSON_PRETTY_PRINT
) . PHP_EOL;
```

If reconciled 1.3.38 uses different normalized key names for the same accepted slots, change only those keys to the exact canonical names discovered in Task 1; do not introduce aliases or a second settings schema.

- [ ] **Step 2: Run the runtime fixture through the existing WordPress 6.9 harness**

After the workflow has installed WordPress and activated the exact candidate Theme, run:

```bash
wp eval-file "$GITHUB_WORKSPACE/tests/runtime/homepage-map-runtime.php" --path=/tmp/wp --allow-root | tee /tmp/homepage-map-runtime.json
jq -e '.services == 6 and (.keys[0] == "hero") and (.keys[1] == "services")' /tmp/homepage-map-runtime.json
```

Expected: both commands exit 0; no direct plugin/provider storage is required.

- [ ] **Step 3: Compare frontend DOM order to admin map order in Playwright**

After authenticating and loading both pages:

```js
const adminLabels = await page.locator('#aznet-theme-homepage-map .aznet-theme-homepage-map__card').evaluateAll(
  nodes => nodes.map(node => node.getAttribute('data-surface-key'))
);

const publicPage = await context.newPage();
await publicPage.goto(`${baseUrl}/`, { waitUntil: 'networkidle' });
const publicKeys = await publicPage.locator('[data-aznet-homepage-surface]').evaluateAll(
  nodes => nodes.map(node => node.getAttribute('data-aznet-homepage-surface'))
);

if (JSON.stringify(adminLabels) !== JSON.stringify(publicKeys)) {
  throw new Error(`Homepage admin/frontend surface order mismatch: admin=${JSON.stringify(adminLabels)} public=${JSON.stringify(publicKeys)}`);
}
```

The frontend wrapper already carries `data-aznet-homepage-surface="<key>"` from Task 4. Treat it only as a Theme-owned presentation/testing marker; it is not a routing/domain contract.

- [ ] **Step 4: Verify Services item parity**

Compare service titles in admin and frontend:

```js
const adminServices = await page.locator('#homepage-map-services [data-homepage-map-item]').allTextContents();
const publicServices = await publicPage.locator('#aznet-homepage-services .aznet-theme-law01-card h3').allTextContents();
if (JSON.stringify(adminServices.map(s => s.trim())) !== JSON.stringify(publicServices.map(s => s.trim()))) {
  throw new Error('Homepage Map Services items do not match frontend.');
}
```

This pins Review Focus item 2.

- [ ] **Step 5: Verify keyboard/a11y/narrow viewport**

Run R5 at:
- 1440×1000;
- 1024×768;
- 782×900 or narrower WordPress admin width;
- 390×844 if the WordPress admin shell is usable in the fixture.

Assert:
- no horizontal overflow;
- advanced `<summary>` focus indicator visible;
- each primary action practical target height ≥ 44px;
- axe critical/serious count = 0 inside `.aznet-theme-control-center`.

- [ ] **Step 6: Extend workflow test invocations**

In the two existing workflows, invoke the new runtime/parity tests without creating a separate CI stack. Keep WordPress 6.9 and PHP 8.1 floors unchanged.

- [ ] **Step 7: Record evidence**

Create `docs/evidence/HOMEPAGE_MAP_RUNTIME_BROWSER_20260924.md` with:
- exact head SHA;
- L3 runtime result;
- admin/frontend parity result;
- viewports;
- axe result;
- retained Homepage Law 01 and R5 workflow run IDs;
- explicit ownership statement.

- [ ] **Step 8: Commit**

```bash
git add tests .github/workflows docs/evidence/HOMEPAGE_MAP_RUNTIME_BROWSER_20260924.md
git commit -m "test: verify Homepage Map frontend parity"
```

---

### Task 8: Whole-branch regression, review, and release-candidate gate

**Files:**
- Modify only if verification finds a real regression.
- Create: `docs/evidence/HOMEPAGE_MAP_TECHNICAL_CLOSURE_20260924.md`
- Release promotion files are changed only after explicit product-owner approval.

**Interfaces:**
- Consumes: Tasks 1–7 exact branch head.
- Produces: technical closure evidence; no production deployment.

- [ ] **Step 1: Run complete retained core verification**

```bash
bash scripts/verify-v1-core.sh
```

Expected: exit 0.

- [ ] **Step 2: Run all affected CI gates on the exact final head**

Required successful gates:
- V1 Core;
- R5 Control Center Browser Quality;
- Homepage Composer Law 01 Browser Quality;
- any retained release/version consistency gate triggered by the diff;
- relevant ConvertFlow/RootProfile coexistence gates already attached to Homepage changes.

Do not infer PASS from a non-executed/pre-runner CI failure.

- [ ] **Step 3: Review the final diff against ownership and spec**

Run:

```bash
git diff --check
git diff origin/main...HEAD -- inc/theme inc/admin template-parts/homepage/law-01 assets/css/admin tests docs
```

Confirm:
- no provider-private API/storage reads;
- no new persistent Homepage content snapshot;
- no manual admin-only section order;
- no source-switch action in primary cards;
- frontend visible copy/layout unchanged except anchors/testing attributes;
- one shared effective-surface model is the source for both frontend and admin.

- [ ] **Step 4: Create technical-closure evidence**

Record exact final SHA, successful workflow IDs/artifacts, file delta, ownership boundary and remaining release/deployment gates in `docs/evidence/HOMEPAGE_MAP_TECHNICAL_CLOSURE_20260924.md`.

- [ ] **Step 5: Commit closure evidence**

```bash
git add docs/evidence/HOMEPAGE_MAP_TECHNICAL_CLOSURE_20260924.md
git commit -m "docs: record Homepage Map technical closure"
```

- [ ] **Step 6: Stop at release approval gate**

Do **not** change Theme version or deploy production automatically.

After explicit owner approval for a new installable version, create the next patch release from live/canonical 1.3.38 lineage — expected version **1.3.39** unless another immutable package identity has already consumed that number — then run the repository's existing RED → GREEN release-promotion, deterministic package, exact-main, publication and deployment gates.


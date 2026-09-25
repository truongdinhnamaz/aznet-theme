# WordPress-native Team Directory Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a WordPress-native Team directory where the mapped Team parent Page owns the directory, direct child Pages own individual members, Homepage shows the first four published members, and Law 01 provisioning creates/reuses the Team parent without fabricating people.

**Architecture:** Keep WordPress as the content owner. Add one focused Team read-model/helper module that resolves the exact mapped parent and direct child Pages; reuse that model in Homepage presentation, Homepage admin preview, and the public Team directory Page. Add-member authoring creates only draft child Pages, while provisioning creates/reuses only the parent Page.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, AZnet Theme hybrid PHP + `theme.json`, native Pages/Featured Images, existing Homepage authoring/provisioning framework, scoped CSS, existing Playwright/axe browser harness.

**Spec:** `docs/superpowers/specs/2026-09-25-wordpress-native-team-directory-design.md`

## Global Constraints

- WordPress owns Team parent/child Pages, names, roles, portraits, biographies, publication state, permalinks and `menu_order`.
- AZnet Theme owns only typed mapping, presentation, bounded authoring UI and provisioning orchestration.
- No Team CPT, personnel repeater/meta store, JSON personnel blob, second Theme Mod store or private provider storage.
- No RootProfile private reads or inference that a Team child Page is an authoritative RootProfile Person.
- No title/slug/URL heuristic for detecting the Team parent Page; use the exact mapped Team Page ID only.
- Homepage shows at most 4 published direct child members.
- Team directory Page shows all published direct child members.
- Add Member creates a draft Page only; it never auto-publishes or fabricates person data.
- Missing portrait means text-only member presentation; no generated/reference/AI person portrait fallback.
- Existing mapped Team Pages are reused and are not auto-renamed or duplicated.
- Release version bump/package/deployment remains a separate gate after technical closure.

## Review Focus

1. **Existing mapped Team Page titled “Đội ngũ”** — must remain the mapped parent and must not be renamed to “Đội ngũ của chúng tôi”.
2. **More than four published members** — Homepage/admin preview show the same first four in `menu_order title` order, while the Team directory shows all.
3. **Draft/private child mixed with published children** — only published children render publicly; draft created from admin stays hidden.
4. **Member without portrait or excerpt** — card remains valid with name only; no fake image or invented role.
5. **Unrelated child/non-child Page ID submitted to Team quick edit/create flow** — mutation is rejected unless the Page is a direct child of the exact mapped Team parent.

---

### Task 1: Ratify Team directory ownership and execution slices

**Files:**
- Modify: `docs/source/AZT-02-architecture.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: approved Team directory spec.
- Produces: accepted internal architecture/roadmap decision allowing WordPress-native Team parent/child authoring and presentation.

- [ ] **Step 1: Add the ownership rule to AZT-02**

Add a bounded rule under Theme/WordPress native content architecture:

```text
Law 01 Team directory uses the explicitly mapped WordPress Team parent Page.
Each team member is a direct child Page. WordPress owns title/excerpt/
Featured Image/content/publication/menu_order. AZnet Theme owns presentation,
bounded admin authoring and typed mapping only. No Team CPT or parallel
personnel store is allowed. Team child Pages are editorial website content
and are not authoritative RootProfile Person identity.
```

- [ ] **Step 2: Record the accepted decision in AZT-04**

Record the 2026-09-25 accepted decision:
- Team parent Page is required for Law 01 recommended setup;
- new Team parent title is **Đội ngũ của chúng tôi**;
- existing mapped Team Page is reused unchanged;
- Homepage limit = 4 published direct child Pages;
- Team directory = all published direct child Pages;
- no generated/reference person fallback;
- add-member = draft-only;
- L1-L4 evidence required before release.

Use the next available Decision Log identifier after the current branch state; do not reuse a stale main identifier.

- [ ] **Step 3: Add execution slices to AZT-EXEC-MAP**

Add:

```text
TD0 source ratification
TD1 Team read model + shared card
TD2 Homepage Team frontend
TD3 Homepage Team authoring
TD4 Team directory Page
TD5 Law 01 provisioning
TD6 runtime/browser/a11y parity
TD7 technical closure/release gate
```

- [ ] **Step 4: Register spec/plan in SOURCE_MANIFEST**

Register:
- `docs/superpowers/specs/2026-09-25-wordpress-native-team-directory-design.md`
- `docs/superpowers/plans/2026-09-25-wordpress-native-team-directory.md`

- [ ] **Step 5: Commit governance checkpoint**

```bash
git add docs/source
git commit -m "docs: ratify WordPress-native Team directory"
```

---

### Task 2: Add one shared Team read model and card primitive

**Files:**
- Create: `inc/theme/team-directory.php`
- Modify: `inc/theme/bootstrap.php`
- Create: `template-parts/team/card.php`
- Create: `assets/css/components/team-card.css`
- Test: `tests/offline/team-directory-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes:
  - `homepage_source_value('law-01', 'team'): mixed`
  - WordPress `get_post()`, `get_posts()`, Featured Image APIs.
- Produces:
  - `team_directory_parent(): ?\WP_Post`
  - `team_directory_members(int $limit = 0): array<int,\WP_Post>`
  - `team_directory_member_is_child(int $post_id): bool`
  - `team_directory_next_menu_order(): int`
  - shared `template-parts/team/card.php`.

- [ ] **Step 1: Write the RED offline contract**

Create `tests/offline/team-directory-contract.php`:

```php
<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$module = file_get_contents($root . '/inc/theme/team-directory.php');
$bootstrap = file_get_contents($root . '/inc/theme/bootstrap.php');
$card = file_get_contents($root . '/template-parts/team/card.php');

if (false === $module || false === $bootstrap || false === $card) {
    fwrite(STDERR, "FAIL: Team directory files missing\n");
    exit(1);
}

foreach ([
    'function team_directory_parent',
    'function team_directory_members',
    'function team_directory_member_is_child',
    'function team_directory_next_menu_order',
    "homepage_source_value( 'law-01', 'team'",
    "'post_parent'",
    "'post_status'    => 'publish'",
    "'orderby'        => 'menu_order title'",
] as $needle) {
    if (! str_contains($module, $needle)) {
        fwrite(STDERR, "FAIL: missing Team directory contract: {$needle}\n");
        exit(1);
    }
}

foreach (['register_post_type(', 'update_post_meta(', 'add_post_meta(', 'get_page_by_path(', 'post_name'] as $forbidden) {
    if (str_contains($module, $forbidden)) {
        fwrite(STDERR, "FAIL: forbidden Team ownership shortcut: {$forbidden}\n");
        exit(1);
    }
}

if (! str_contains($bootstrap, "require_once __DIR__ . '/team-directory.php';")) {
    fwrite(STDERR, "FAIL: Team directory module not bootstrapped\n");
    exit(1);
}

foreach (['team_member', 'member_image', 'member_role', 'get_permalink'] as $needle) {
    if (! str_contains($card, $needle)) {
        fwrite(STDERR, "FAIL: shared Team card missing {$needle}\n");
        exit(1);
    }
}

echo "PASS: Team directory ownership/read-model contract\n";
```

- [ ] **Step 2: Run RED**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-directory-contract.php
```

Expected: FAIL because the Team module/card do not exist.

- [ ] **Step 3: Implement the read model**

Create `inc/theme/team-directory.php`:

```php
<?php
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function team_directory_parent(): ?\WP_Post {
    $id = (int) homepage_source_value( 'law-01', 'team' );
    return $id > 0 ? homepage_page_reference( $id ) : null;
}

/** @return array<int,\WP_Post> */
function team_directory_members( int $limit = 0 ): array {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post ) { return []; }

    $args = [
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'post_parent'    => (int) $parent->ID,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'posts_per_page' => $limit > 0 ? min( 24, $limit ) : -1,
        'no_found_rows'  => true,
    ];
    $posts = get_posts( $args );
    return is_array( $posts ) ? array_values( array_filter( $posts, static fn( $post ): bool => $post instanceof \WP_Post ) ) : [];
}

function team_directory_member_is_child( int $post_id ): bool {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post || $post_id <= 0 ) { return false; }
    $post = get_post( $post_id );
    return $post instanceof \WP_Post
        && 'page' === $post->post_type
        && (int) $post->post_parent === (int) $parent->ID;
}

function team_directory_next_menu_order(): int {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post ) { return 0; }

    $children = get_posts( [
        'post_type'      => 'page',
        'post_status'    => 'any',
        'post_parent'    => (int) $parent->ID,
        'orderby'        => 'menu_order',
        'order'          => 'DESC',
        'posts_per_page' => 1,
        'no_found_rows'  => true,
    ] );

    $last = is_array( $children ) && isset( $children[0] ) && $children[0] instanceof \WP_Post
        ? (int) $children[0]->menu_order
        : -1;

    return $last + 1;
}
```

Require it in `inc/theme/bootstrap.php` after `homepage-content-map.php`.

- [ ] **Step 4: Create the shared Team card**

Create `template-parts/team/card.php`:

```php
<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$team_member = $args['team_member'] ?? null;
if ( ! $team_member instanceof \WP_Post ) { return; }

$member_image = has_post_thumbnail( $team_member )
    ? get_the_post_thumbnail( $team_member, 'medium_large', [ 'class' => 'aznet-theme-team-card__image' ] )
    : '';
$member_role = trim( (string) $team_member->post_excerpt );
$url = get_permalink( $team_member );
$url = is_string( $url ) ? $url : '';
?>
<article class="aznet-theme-team-card">
    <?php if ( '' !== $member_image && '' !== $url ) : ?>
        <a class="aznet-theme-team-card__media" href="<?php echo esc_url( $url ); ?>">
            <?php echo wp_kses_post( $member_image ); ?>
        </a>
    <?php endif; ?>
    <div class="aznet-theme-team-card__body">
        <h3 class="aznet-theme-team-card__name">
            <?php if ( '' !== $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php endif; ?>
            <?php echo esc_html( get_the_title( $team_member ) ); ?>
            <?php if ( '' !== $url ) : ?></a><?php endif; ?>
        </h3>
        <?php if ( '' !== $member_role ) : ?>
            <p class="aznet-theme-team-card__role"><?php echo esc_html( $member_role ); ?></p>
        <?php endif; ?>
    </div>
</article>
```

- [ ] **Step 5: Add focused Team card CSS**

Create `assets/css/components/team-card.css` with:

```css
.aznet-theme-team-card{min-width:0}
.aznet-theme-team-card__media{display:block;overflow:hidden;border-radius:.35rem;background:var(--aznet-theme-color-surface-muted)}
.aznet-theme-team-card__image{display:block;width:100%;aspect-ratio:4/5;height:auto;object-fit:cover}
.aznet-theme-team-card__body{padding-top:.85rem}
.aznet-theme-team-card__name{margin:0;font-size:var(--aznet-theme-text-h4);line-height:var(--aznet-theme-line-height-h4)}
.aznet-theme-team-card__name a{color:inherit;text-decoration:none}
.aznet-theme-team-card__role{margin:.35rem 0 0;color:var(--aznet-theme-color-text-muted)}
```

- [ ] **Step 6: Register the offline test and run GREEN**

Add to `scripts/verify-v1-core.sh`:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-directory-contract.php
```

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-directory-contract.php
bash scripts/verify-v1-core.sh
```

Expected: both exit 0.

- [ ] **Step 7: Commit**

```bash
git add inc/theme/team-directory.php inc/theme/bootstrap.php template-parts/team/card.php assets/css/components/team-card.css tests/offline/team-directory-contract.php scripts/verify-v1-core.sh
git commit -m "feat: add WordPress-native Team read model"
```

---

### Task 3: Make Homepage Team render real members only

**Files:**
- Modify: `template-parts/homepage/law-01/profile.php`
- Modify: `inc/theme/assets.php`
- Modify: `assets/css/components/homepage-law-01.css`
- Modify: `tests/offline/law01-reference-team-illustration-contract.php`
- Create: `tests/offline/homepage-team-directory-contract.php`

**Interfaces:**
- Consumes: `team_directory_members(4)`, shared Team card.
- Produces: Homepage Team section with at most four real published member children and **Xem tất cả** CTA.

- [ ] **Step 1: Write RED contract for no fake people and shared model use**

Create `tests/offline/homepage-team-directory-contract.php`:

```php
<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = file_get_contents($root . '/template-parts/homepage/law-01/profile.php');
$assets = file_get_contents($root . '/inc/theme/assets.php');

foreach ([
    'team_directory_members( 4 )',
    "'template-parts/team/card'",
    "esc_html_e( 'Xem tất cả'",
] as $needle) {
    if (! str_contains((string) $profile, $needle)) {
        fwrite(STDERR, "FAIL: Homepage Team missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    'law01_reference_art_urls',
    'team-illustration--generated',
    'team_illustration_url',
] as $forbidden) {
    if (str_contains((string) $profile, $forbidden)) {
        fwrite(STDERR, "FAIL: Homepage Team still exposes fake-person fallback {$forbidden}\n");
        exit(1);
    }
}

if (! str_contains((string) $assets, "'aznet-theme-team-card'")) {
    fwrite(STDERR, "FAIL: Homepage Team does not enqueue shared Team card CSS\n");
    exit(1);
}

echo "PASS: Homepage Team uses only real WordPress child Pages\n";
```

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-team-directory-contract.php
```

Expected: FAIL because the current Profile template still uses `homepage_direct_published_children()` plus illustration/reference fallbacks.

- [ ] **Step 3: Replace member/fallback logic**

In `profile.php`:

```php
$members = $has_team ? team_directory_members( 4 ) : [];
```

Render each member using:

```php
get_template_part(
    'template-parts/team/card',
    null,
    [ 'team_member' => $member ]
);
```

Delete the parent-image repeated portrait grid and `law01_reference_art_urls('team')` generated-person fallback.

If `$members` is empty, render no member grid.

- [ ] **Step 4: Change CTA copy**

Keep destination `get_permalink( $team )`, but render:

```php
<?php esc_html_e( 'Xem tất cả', 'aznet-theme' ); ?> <span aria-hidden="true">→</span>
```

- [ ] **Step 5: Enqueue shared Team card CSS on Law 01 Homepage**

In `enqueue_homepage_law01_asset()`, enqueue `aznet-theme-team-card` before the Law 01 Homepage stylesheet and add it as a dependency.

Do not enqueue Team card CSS globally.

- [ ] **Step 6: Update the old illustration regression**

Change `tests/offline/law01-reference-team-illustration-contract.php` so it asserts generated/reference portraits are absent from `profile.php`. Preserve the assertion that no fake member record is created.

- [ ] **Step 7: Run GREEN and retained regression**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-team-directory-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/law01-reference-team-illustration-contract.php
bash scripts/verify-v1-core.sh
```

Expected: all exit 0.

- [ ] **Step 8: Commit**

```bash
git add template-parts/homepage/law-01/profile.php inc/theme/assets.php assets/css/components/homepage-law-01.css tests/offline
git commit -m "feat: render real Team members on Homepage"
```

---

### Task 4: Add bounded Team member authoring to Homepage admin

**Files:**
- Modify: `inc/admin/homepage.php`
- Modify: `inc/admin/homepage-authoring.php`
- Modify: `inc/admin/bootstrap.php`
- Modify: `assets/css/admin/control-center.css`
- Create: `tests/offline/homepage-team-authoring-contract.php`
- Create: `tests/runtime/homepage-team-authoring.php`

**Interfaces:**
- Consumes:
  - `team_directory_parent(): ?\WP_Post`
  - `team_directory_members(4): array`
  - `team_directory_member_is_child(int): bool`
  - `team_directory_next_menu_order(): int`
  - existing `render_homepage_quick_edit_form('law-01','team',int)`.
- Produces:
  - `render_homepage_team_authoring(): void`
  - `handle_homepage_team_member_create(): void`
  - admin-post action `aznet_theme_create_team_member`.

- [ ] **Step 1: Write RED static authoring contract**

Create `tests/offline/homepage-team-authoring-contract.php`:

```php
<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$homepage = file_get_contents($root . '/inc/admin/homepage.php');
$authoring = file_get_contents($root . '/inc/admin/homepage-authoring.php');
$bootstrap = file_get_contents($root . '/inc/admin/bootstrap.php');

foreach ([
    'function render_homepage_team_authoring',
    "team_directory_members( 4 )",
    'Thêm nhân sự',
    'Xem tất cả trên website',
] as $needle) {
    if (! str_contains((string) $homepage, $needle)) {
        fwrite(STDERR, "FAIL: Team admin missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    'function handle_homepage_team_member_create',
    "'post_status' => 'draft'",
    "'post_parent'",
    'team_directory_next_menu_order()',
    'wp_insert_post(',
] as $needle) {
    if (! str_contains((string) $authoring, $needle)) {
        fwrite(STDERR, "FAIL: Team create action missing {$needle}\n");
        exit(1);
    }
}

if (! str_contains((string) $bootstrap, "admin_post_aznet_theme_create_team_member")) {
    fwrite(STDERR, "FAIL: Team create action not registered\n");
    exit(1);
}

echo "PASS: Homepage Team bounded authoring contract\n";
```

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-team-authoring-contract.php
```

Expected: FAIL because Team-specific authoring functions/actions do not exist.

- [ ] **Step 3: Strengthen Team child authorization**

In `homepage_quick_edit_target_allowed()`, keep the generic child behavior but add a Team-specific exact guard before returning true:

```php
if ( 'law-01' === $preset && 'team' === $slot ) {
    return \AZnet\Theme\team_directory_member_is_child( $source_id );
}
```

This pins Review Focus item 5.

- [ ] **Step 4: Implement Team member create action**

Add to `inc/admin/homepage-authoring.php`:

```php
function handle_homepage_team_member_create(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thêm nhân sự.', 'aznet-theme' ) );
    }
    check_admin_referer( 'aznet_theme_create_team_member' );

    $parent = \AZnet\Theme\team_directory_parent();
    if ( ! $parent instanceof \WP_Post || ! current_user_can( 'edit_post', $parent->ID ) ) {
        wp_die( esc_html__( 'Page Đội ngũ chưa hợp lệ.', 'aznet-theme' ) );
    }

    $name = isset( $_POST['team_member_name'] )
        ? sanitize_text_field( wp_unslash( $_POST['team_member_name'] ) )
        : '';
    $role = isset( $_POST['team_member_role'] )
        ? sanitize_textarea_field( wp_unslash( $_POST['team_member_role'] ) )
        : '';
    $image_id = isset( $_POST['homepage_featured_image_id'] )
        ? absint( $_POST['homepage_featured_image_id'] )
        : 0;

    if ( '' === $name ) {
        wp_die( esc_html__( 'Tên nhân sự không được để trống.', 'aznet-theme' ) );
    }
    if ( $image_id > 0 && ! wp_attachment_is_image( $image_id ) ) {
        wp_die( esc_html__( 'Ảnh nhân sự không hợp lệ.', 'aznet-theme' ) );
    }

    $id = wp_insert_post(
        [
            'post_type'    => 'page',
            'post_status'  => 'draft',
            'post_parent'  => (int) $parent->ID,
            'post_title'   => $name,
            'post_excerpt' => $role,
            'menu_order'   => \AZnet\Theme\team_directory_next_menu_order(),
        ],
        true
    );
    if ( is_wp_error( $id ) ) {
        wp_die( esc_html( $id->get_error_message() ) );
    }
    if ( $image_id > 0 ) {
        set_post_thumbnail( (int) $id, $image_id );
    }

    $url = add_query_arg(
        [ 'page' => 'aznet-theme', 'section' => 'homepage', 'team_member_created' => '1' ],
        admin_url( 'admin.php' )
    );
    wp_safe_redirect( $url . '#homepage-team' );
    exit;
}
```

- [ ] **Step 5: Register the admin-post action**

In `inc/admin/bootstrap.php`:

```php
add_action( 'admin_post_aznet_theme_create_team_member', __NAMESPACE__ . '\handle_homepage_team_member_create' );
```

- [ ] **Step 6: Render Team-specific admin UI**

Add `render_homepage_team_authoring()` in `homepage.php`.

It must:
- use exact mapped parent;
- show parent title and published member count;
- list exactly `team_directory_members(4)`;
- for each member show thumbnail, name, excerpt/role and existing quick-edit disclosure;
- show **Xem tất cả trên website** to the parent permalink;
- show an **Thêm nhân sự** disclosure/form with fields `team_member_name`, `team_member_role`, and the existing media selector markup/classes;
- never list unrelated Pages.

In `render_homepage_authoring_console()`, special-case `law-01/team` so the generic children rendering does not duplicate the Team UI.

- [ ] **Step 7: Add Team admin CSS**

Add scoped rules:

```css
.aznet-theme-homepage-team-members{display:grid;gap:12px;margin-top:14px}
.aznet-theme-homepage-team-member{display:grid;grid-template-columns:64px minmax(0,1fr) auto;gap:12px;align-items:center;padding:12px;border:1px solid #dcdcde;border-radius:8px;background:#fff}
.aznet-theme-homepage-team-member__image img{display:block;width:64px;height:80px;object-fit:cover;border-radius:6px}
.aznet-theme-homepage-team-member__role{margin:.25rem 0 0;color:#646970}
@media(max-width:782px){.aznet-theme-homepage-team-member{grid-template-columns:52px minmax(0,1fr)}.aznet-theme-homepage-team-member__actions{grid-column:1/-1}}
```

- [ ] **Step 8: Write runtime fixture for draft-only creation and authorization**

Create `tests/runtime/homepage-team-authoring.php` for `wp eval-file`.

Seed:
- mapped published Team parent;
- one published child;
- one unrelated Page.

Call the production helpers to assert:

```php
assert( \AZnet\Theme\Admin\homepage_quick_edit_target_allowed(
    'law-01',
    'team',
    $published_child_id,
    \AZnet\Theme\homepage_source_descriptor( 'law-01', 'team' )
) );

assert( ! \AZnet\Theme\Admin\homepage_quick_edit_target_allowed(
    'law-01',
    'team',
    $unrelated_page_id,
    \AZnet\Theme\homepage_source_descriptor( 'law-01', 'team' )
) );
```

For the create path, extract the insert payload into a small production helper:

```php
function homepage_team_member_insert_data( \WP_Post $parent, string $name, string $role ): array
```

and assert:

```php
$data = \AZnet\Theme\Admin\homepage_team_member_insert_data( $parent, 'Nguyễn An', 'Luật sư' );
assert( 'draft' === $data['post_status'] );
assert( (int) $parent->ID === (int) $data['post_parent'] );
assert( 'Nguyễn An' === $data['post_title'] );
assert( 'Luật sư' === $data['post_excerpt'] );
```

The HTTP handler must call this helper before `wp_insert_post()`.

- [ ] **Step 9: Run GREEN**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-team-authoring-contract.php
wp eval-file tests/runtime/homepage-team-authoring.php --allow-root
bash scripts/verify-v1-core.sh
```

Expected: all exit 0.

- [ ] **Step 10: Commit**

```bash
git add inc/admin assets/css/admin tests
git commit -m "feat: add bounded Team member authoring"
```

---

### Task 5: Add mapped Team directory Page presentation

**Files:**
- Modify: `inc/theme/page-experience.php`
- Modify: `template-parts/content/page.php`
- Create: `template-parts/team/page.php`
- Modify: `inc/theme/assets.php`
- Create: `assets/css/components/team-directory.css`
- Create: `tests/offline/team-page-presentation-contract.php`
- Modify: `tests/browser/y1-page-experience-l4.mjs`
- Modify: `.github/workflows/y1-page-experience.yml`

**Interfaces:**
- Consumes: exact mapped Team parent + `team_directory_members()`.
- Produces:
  - `team_page_is_mapped(?int $post_id = null): bool`
  - `team_page_presentation_active(?int $post_id = null): bool`
  - mapped Team Page template surface using all published members.

- [ ] **Step 1: Write RED presentation contract**

Create `tests/offline/team-page-presentation-contract.php` asserting:
- `page-experience.php` contains `team_page_is_mapped` and `team_page_presentation_active`;
- mapping uses `homepage_source_value( 'law-01', 'team' )`;
- no `get_page_by_path`, Team slug literal, or title heuristic exists;
- `template-parts/content/page.php` routes mapped Team parent to `template-parts/team/page`;
- Team page template calls `team_directory_members()` and shared Team card;
- Team CSS is scoped and asset enqueue is gated by `team_page_presentation_active()`.

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-page-presentation-contract.php
```

Expected: FAIL because mapped Team Page presentation does not exist.

- [ ] **Step 3: Add explicit mapped Team Page detection**

In `page-experience.php`:

```php
function team_page_is_mapped( ?int $post_id = null ): bool {
    $post_id = $post_id ?: (int) get_queried_object_id();
    $team_id = (int) homepage_source_value( 'law-01', 'team' );
    if ( $post_id <= 0 || $team_id <= 0 || $post_id !== $team_id ) { return false; }

    $post = get_post( $post_id );
    return $post instanceof \WP_Post
        && 'page' === $post->post_type
        && 'publish' === $post->post_status;
}

function team_page_presentation_active( ?int $post_id = null ): bool {
    return team_page_is_mapped( $post_id )
        && function_exists( __NAMESPACE__ . '\header_law01_active' )
        && header_law01_active();
}
```

- [ ] **Step 4: Route the mapped Team parent through the shared Page template**

In `template-parts/content/page.php`, compute:

```php
$is_team_page = \AZnet\Theme\team_page_presentation_active( (int) get_the_ID() );
```

Add class `aznet-theme-page--team-directory`.

Before the generic Page branch:

```php
<?php elseif ( $is_team_page ) : ?>
    <?php
    get_template_part(
        'template-parts/team/page',
        null,
        [ 'excerpt' => $excerpt ]
    );
    ?>
```

- [ ] **Step 5: Create Team directory Page template**

Create `template-parts/team/page.php`:

```php
<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$excerpt = isset( $args['excerpt'] ) ? trim( (string) $args['excerpt'] ) : '';
$members = \AZnet\Theme\team_directory_members();
?>
<section class="aznet-theme-team-directory">
    <div class="aznet-theme-page__section-inner">
        <header class="aznet-theme-team-directory__header">
            <p class="aznet-theme-team-directory__eyebrow"><?php esc_html_e( 'Đội ngũ', 'aznet-theme' ); ?></p>
            <h1><?php the_title(); ?></h1>
            <?php if ( '' !== $excerpt ) : ?><p class="aznet-theme-team-directory__lead"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
        </header>

        <?php if ( trim( (string) get_the_content() ) !== '' ) : ?>
            <div class="aznet-theme-team-directory__intro aznet-theme-entry__content"><?php the_content(); ?></div>
        <?php endif; ?>

        <?php if ( [] !== $members ) : ?>
            <div class="aznet-theme-team-directory__grid">
                <?php foreach ( $members as $member ) : ?>
                    <?php get_template_part( 'template-parts/team/card', null, [ 'team_member' => $member ] ); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
```

- [ ] **Step 6: Add scoped assets**

Add `should_enqueue_team_page_assets()` and `enqueue_team_page_assets()` in `assets.php`.

The Team directory stylesheet depends on:
- `aznet-theme-page`;
- `aznet-theme-team-card`.

Add `assets/css/components/team-directory.css` with a responsive 4→2→1 grid and Law 01-compatible typography/surfaces. Do not load it globally.

- [ ] **Step 7: Browser-test mapped Team Page**

Extend Y1 fixture to create:
- mapped Team parent;
- 5 published direct child members with known `menu_order`;
- one draft direct child.

Assert:
- mapped parent has `.aznet-theme-page--team-directory`;
- directory grid has exactly 5 cards;
- draft member name absent;
- cards ordered by `menu_order title`;
- no horizontal overflow;
- axe critical/serious = 0.

- [ ] **Step 8: Run GREEN**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-page-presentation-contract.php
bash scripts/verify-v1-core.sh
```

Then run the existing Y1 Page Experience workflow.

- [ ] **Step 9: Commit**

```bash
git add inc/theme/page-experience.php inc/theme/assets.php template-parts/content/page.php template-parts/team assets/css/components/team-directory.css tests .github/workflows/y1-page-experience.yml
git commit -m "feat: add mapped Team directory Page"
```

---

### Task 6: Make Law 01 provisioning require/create/reuse the Team parent

**Files:**
- Modify: `inc/theme/provisioning-blueprints.php`
- Modify: `inc/admin/provisioning.php`
- Modify: `inc/theme/provisioning-readiness.php`
- Modify: `tests/offline/provisioning-plan-contract.php`
- Create: `tests/offline/team-provisioning-contract.php`
- Modify: `tests/runtime/provisioning-runtime.php` or the current Law 01 runtime provisioning fixture used by the repository.
- Modify: `tests/browser/y4-professional-services-provisioning-l4.mjs` only if shared wizard assertions require adaptation; do not change Professional Services semantics.

**Interfaces:**
- Consumes: existing `team` Page role and existing provisioning runner mapping.
- Produces: Law 01 recommended setup where Team is a required parent Page, new creation title is **Đội ngũ của chúng tôi**, and no member children are auto-created.

- [ ] **Step 1: Write RED provisioning contract**

Create `tests/offline/team-provisioning-contract.php`:

```php
<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$blueprints = file_get_contents($root . '/inc/theme/provisioning-blueprints.php');
$admin = file_get_contents($root . '/inc/admin/provisioning.php');

if (! str_contains((string) $blueprints, "'team' => \$page('Đội ngũ của chúng tôi'")) {
    fwrite(STDERR, "FAIL: Law 01 Team parent title not updated\n");
    exit(1);
}

if (! str_contains((string) $admin, "[ 'home','about','services','team','contact' ]")) {
    fwrite(STDERR, "FAIL: Team is not required for Law 01 provisioning\n");
    exit(1);
}

foreach (['team_member', 'starter_team_member', 'sample_lawyer'] as $forbidden) {
    if (str_contains((string) $blueprints, $forbidden)) {
        fwrite(STDERR, "FAIL: provisioning fabricates Team people: {$forbidden}\n");
        exit(1);
    }
}

echo "PASS: Law 01 provisions Team parent only\n";
```

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-provisioning-contract.php
```

Expected: FAIL because current title is “Đội ngũ luật sư” and Team is not required for Law 01.

- [ ] **Step 3: Update the Law 01 Team parent definition**

Change only the Law 01 blueprint Team definition:

```php
'team' => $page(
    'Đội ngũ của chúng tôi',
    'Giới thiệu đội ngũ và vai trò chuyên môn thực tế của tổ chức.',
    '<p>Hãy thêm nhân sự thật bằng các Page con của trang này. AZnet Theme không tạo người, chức vụ hoặc thành tích mẫu.</p>'
),
```

Do not add member child roles to the blueprint.

- [ ] **Step 4: Make Team required in Law 01 wizard**

In `inc/admin/provisioning.php`:

```php
$required_page_roles = $is_law
    ? [ 'home','about','services','team','contact' ]
    : [ 'home','about','services','team','contact' ];
```

Keep the branch if it helps future divergence, or simplify to one shared array only if no blueprint-specific behavior is lost.

- [ ] **Step 5: Update readiness semantics**

In `provisioning-readiness.php`, Team must no longer be reported as optional for Law 01 recommended setup.

Remove `team` from the optional list while preserving optional `process` and `faq` behavior.

- [ ] **Step 6: Add runtime reuse/no-duplicate test**

In the existing Law 01 provisioning runtime fixture:
1. seed a published mapped Team Page with title **Đội ngũ**;
2. run recommendation/plan;
3. assert Team action is `reuse` with that exact Page ID;
4. apply plan;
5. assert no second Team parent Page was created;
6. assert mapped Team Page ID remains the original ID;
7. assert there are zero direct child Pages created under Team by provisioning.

Then run a fresh-site case:
1. no Team mapping;
2. apply recommended plan;
3. assert exactly one Team parent exists with title **Đội ngũ của chúng tôi**;
4. assert zero Team member children.

- [ ] **Step 7: Run GREEN**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-provisioning-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-plan-contract.php
bash scripts/verify-v1-core.sh
```

Run the repository's Law 01 provisioning runtime/browser workflow and confirm reuse/create paths.

- [ ] **Step 8: Commit**

```bash
git add inc/theme/provisioning-blueprints.php inc/theme/provisioning-readiness.php inc/admin/provisioning.php tests
git commit -m "feat: provision Team directory parent for Law 01"
```

---

### Task 7: Prove Homepage/admin/directory parity on real WordPress

**Files:**
- Create: `tests/runtime/team-directory-parity.php`
- Modify: `tests/browser/homepage-law-01-l4.mjs`
- Modify: `tests/browser/r5-control-center-l4.mjs`
- Modify: `.github/workflows/homepage-law-01-browser.yml`
- Modify: `.github/workflows/r5-control-center-browser.yml`
- Create: `docs/evidence/TEAM_DIRECTORY_RUNTIME_BROWSER_20260925.md`

**Interfaces:**
- Consumes: shared Team read model, Homepage Team, Team admin, Team directory Page.
- Produces: L3/L4 evidence that all three surfaces read the same WordPress-owned member set/order.

- [ ] **Step 1: Create runtime parity fixture**

Create `tests/runtime/team-directory-parity.php` to seed:
- mapped published Team parent;
- 6 published members with menu orders 10,20,30,40,50,60;
- 1 draft member with menu order 5;
- member #2 without portrait;
- member #3 without excerpt.

Assert:

```php
$homepage = \AZnet\Theme\team_directory_members( 4 );
$all = \AZnet\Theme\team_directory_members();

assert( 4 === count( $homepage ) );
assert( 6 === count( $all ) );
assert( 'Draft Member' !== get_the_title( $all[0] ) );
assert( array_map( fn( $p ) => (int) $p->menu_order, $homepage ) === [10,20,30,40] );
```

Also assert missing image/excerpt does not remove the member from the result.

- [ ] **Step 2: Extend Homepage browser test**

On the Law 01 fixture:
- query Homepage Team card names/roles;
- assert 4 cards maximum;
- assert **Xem tất cả** href equals the exact Team parent permalink;
- assert no old reference-art caption and no generated portrait class exists.

- [ ] **Step 3: Extend Control Center browser test**

Authenticate and load Homepage admin.

Assert:
- Team section lists the same first four member names in the same order;
- one member without portrait still appears;
- **Thêm nhân sự** is keyboard reachable;
- invalid/unrelated Page is not exposed as a Team member;
- 390px and 782px widths have no horizontal overflow.

- [ ] **Step 4: Cross-surface parity assertion**

In one browser run, compare normalized member names:

```js
const adminNames = await adminPage.locator('#homepage-team [data-team-member-name]').allTextContents();
const publicNames = await publicPage.locator('.aznet-theme-law01-profile__members .aznet-theme-team-card__name').allTextContents();

if (JSON.stringify(adminNames.map(s => s.trim())) !== JSON.stringify(publicNames.map(s => s.trim()))) {
  throw new Error('Team admin/Homepage member order mismatch');
}
```

- [ ] **Step 5: Run accessibility checks**

Run axe on:
- Homepage Team section;
- Team directory Page;
- Homepage Team admin area.

Expected: 0 new serious/critical violations.

- [ ] **Step 6: Record evidence**

Create `docs/evidence/TEAM_DIRECTORY_RUNTIME_BROWSER_20260925.md` with:
- exact branch SHA;
- WordPress/PHP test matrix;
- runtime fixture result;
- Homepage/admin parity result;
- Team directory result;
- viewport checks;
- axe result;
- explicit note that no RootProfile/private provider storage was touched.

- [ ] **Step 7: Commit**

```bash
git add tests .github/workflows docs/evidence/TEAM_DIRECTORY_RUNTIME_BROWSER_20260925.md
git commit -m "test: verify Team directory cross-surface parity"
```

---

### Task 8: Whole-branch verification and release gate

**Files:**
- Create: `docs/evidence/TEAM_DIRECTORY_TECHNICAL_CLOSURE_20260925.md`
- Version/package files are modified only after explicit release approval.

**Interfaces:**
- Consumes: exact final implementation head.
- Produces: technical closure evidence; no automatic deployment.

- [ ] **Step 1: Run full retained core suite**

```bash
bash scripts/verify-v1-core.sh
```

Expected: exit 0.

- [ ] **Step 2: Run affected browser/runtime workflows**

Required successful gates:
- Homepage Law 01 Browser Quality;
- R5 Control Center Browser Quality;
- Y1 Page Experience;
- Law 01 provisioning runtime/browser workflow;
- retained integration/coexistence gates already triggered by these paths.

A workflow that never receives a runner is **UNKNOWN**, not PASS.

- [ ] **Step 3: Review ownership diff**

Run:

```bash
git diff --check
git diff origin/main...HEAD -- inc/theme inc/admin template-parts assets/css tests docs/source docs/evidence
```

Confirm:
- no Team CPT;
- no new personnel meta/repeater store;
- no RootProfile private storage;
- no slug/title/URL Team Page heuristic;
- add-member is draft-only;
- provisioning creates Team parent only;
- reference/AI portraits no longer impersonate Team members.

- [ ] **Step 4: Write closure evidence**

Create `docs/evidence/TEAM_DIRECTORY_TECHNICAL_CLOSURE_20260925.md` with:
- exact final SHA;
- PASS/UNKNOWN by QA layer;
- changed production paths;
- workflow IDs;
- ownership proof;
- remaining release gate.

- [ ] **Step 5: Commit closure evidence**

```bash
git add docs/evidence/TEAM_DIRECTORY_TECHNICAL_CLOSURE_20260925.md
git commit -m "docs: record Team directory technical closure"
```

- [ ] **Step 6: Stop at release approval**

Do not bump version, build the final installable ZIP, publish or deploy automatically.

After explicit release approval, use the next unused patch identity after the current 1.3.44 candidate lineage — expected **1.3.45** unless that version has already been consumed — then run version consistency, package integrity and final smoke checks before handing off the ZIP.

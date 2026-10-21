<?php
/**
 * D-043 WordPress 6.9 clean Theme-native Curtain/Industrial provisioning scenarios.
 * Run only in disposable CI WordPress with wp eval-file.
 * AZNET_D043_TEMPLATE=curtain|industrial
 * AZNET_D043_SCENARIO=clean|rerun|active|rollback
 */
declare(strict_types=1);

function aznet_d043_assert(bool $ok, string $why): void {
    if (!$ok) { throw new RuntimeException("D-043: {$why}"); }
}

function aznet_d043_reset(): void {
    foreach (get_posts([
        'post_type' => ['post', 'page', 'attachment'], 'post_status' => 'any',
        'posts_per_page' => -1, 'fields' => 'ids',
    ]) as $id) { wp_delete_post((int)$id, true); }
    foreach ((array)wp_get_nav_menus() as $menu) {
        wp_delete_nav_menu((int)$menu->term_id);
    }
    $terms = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
    foreach (is_wp_error($terms) ? [] : $terms as $term) {
        if ('uncategorized' !== $term->slug) {
            wp_delete_term((int)$term->term_id, 'category');
        }
    }
    update_option('show_on_front', 'posts');
    update_option('page_on_front', 0);
    set_theme_mod('nav_menu_locations', []);
    set_theme_mod('aznet_theme_settings', AZnet\Theme\normalize_settings([]));
}

function aznet_d043_seed_page(string $title, string $content): int {
    $id = wp_insert_post([
        'post_type' => 'page', 'post_status' => 'publish',
        'post_title' => $title, 'post_content' => $content,
    ], true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    return (int)$id;
}

function aznet_d043_selection(string $key, string $menu_action = 'create'): array {
    $recipe = AZnet\Theme\provisioning_blueprint($key);
    aznet_d043_assert(is_array($recipe), 'recipe unavailable');
    $pages = [];
    foreach ($recipe['pages'] as $role => $_page) {
        $pages[$role] = ['action' => 'create', 'object_id' => 0];
    }
    $categories = [];
    foreach ($recipe['categories'] as $role => $_category) {
        $categories[$role] = ['action' => 'create', 'object_id' => 0];
    }
    return [
        'pages' => $pages, 'categories' => $categories,
        'menu' => ['action' => $menu_action, 'menu_id' => 0],
        'front_page' => ['action' => 'set_to_role', 'role' => 'home'],
    ];
}

function aznet_d043_plan(string $key, array $selection): array {
    $state = AZnet\Theme\provisioning_discovery();
    $plan = AZnet\Theme\provisioning_build_plan($key, $selection, $state);
    $valid = AZnet\Theme\provisioning_validate_plan($plan);
    aznet_d043_assert(true === ($valid['ok'] ?? false), 'invalid plan: ' . implode('; ', $valid['errors'] ?? []));
    return $plan;
}

function aznet_d043_owned(string $key, string $type): array {
    if ('page' === $type) {
        return get_posts([
            'post_type' => 'page', 'post_status' => 'any',
            'posts_per_page' => -1, 'meta_query' => [
                ['key' => '_aznet_theme_provisioned_by', 'value' => $key],
            ],
        ]);
    }
    return get_terms([
        'taxonomy' => 'category', 'hide_empty' => false,
        'meta_query' => [['key' => '_aznet_theme_provisioned_by', 'value' => $key]],
    ]);
}

$template = (string)getenv('AZNET_D043_TEMPLATE');
$scenario = (string)getenv('AZNET_D043_SCENARIO');
$presets = ['curtain' => ['curtain01-v1', 'curtain-01'], 'industrial' => ['industrial01-v1', 'industrial-01']];
aznet_d043_assert(isset($presets[$template]), 'invalid template fixture');
[$key, $preset] = $presets[$template];
aznet_d043_assert($key === AZnet\Theme\template_provisioning_blueprint_key($preset), 'manifest recipe missing');
aznet_d043_reset();

if (in_array($scenario, ['clean', 'rerun'], true)) {
    aznet_d043_assert('NEW_OR_MOSTLY_EMPTY' === AZnet\Theme\provisioning_site_mode(AZnet\Theme\provisioning_discovery()), 'fresh site not classified new');
    $selection = aznet_d043_selection($key);
    $run = AZnet\Theme\provisioning_apply_plan(aznet_d043_plan($key, $selection));
    aznet_d043_assert(true === ($run['ok'] ?? false), 'first run failed: ' . implode('; ', $run['errors'] ?? []));
    $pages = aznet_d043_owned($key, 'page');
    $terms = aznet_d043_owned($key, 'category');
    aznet_d043_assert(count($pages) === 6 && count($terms) === 2, 'native Page/Category counts invalid');
    foreach ($pages as $page) {
        aznet_d043_assert('publish' === $page->post_status, 'fresh-site Page must publish');
        aznet_d043_assert('' !== trim((string)$page->post_content), 'WordPress Page content empty');
    }
    aznet_d043_assert('page' === get_option('show_on_front') && (int)get_option('page_on_front') > 0, 'fresh front page not set');
    aznet_d043_assert($preset === AZnet\Theme\homepage_preset(), 'template Homepage preset not active');
    $nav = get_nav_menu_locations();
    $menu_id = (int)($nav['primary'] ?? 0);
    $items = wp_get_nav_menu_items($menu_id);
    aznet_d043_assert($menu_id > 0 && is_array($items) && count($items) === 6, 'confirmed native primary menu missing six Page links');
    aznet_d043_assert('PREVIOUSLY_PROVISIONED' === AZnet\Theme\provisioning_site_mode(AZnet\Theme\provisioning_discovery()), 'first run provenance not detected');
    if ('clean' === $scenario) {
        echo "PASS: D-043 {$template} fresh-site native Pages/Categories/Menu/front\n";
        return;
    }
    $before = array_map(static fn(WP_Post $p): int => (int)$p->ID, $pages);
    sort($before);
    $run2 = AZnet\Theme\provisioning_apply_plan(aznet_d043_plan($key, $selection));
    aznet_d043_assert(true === ($run2['ok'] ?? false), 'second run failed');
    $after = array_map(static fn(WP_Post $p): int => (int)$p->ID, aznet_d043_owned($key, 'page'));
    sort($after);
    aznet_d043_assert($before === $after, 're-run duplicated WordPress Page roles');
    aznet_d043_assert(count(aznet_d043_owned($key, 'category')) === 2, 're-run duplicated WordPress terms');
    aznet_d043_assert((int)get_nav_menu_locations()['primary'] === $menu_id, 're-run replaced primary menu');
    echo "PASS: D-043 {$template} same-key idempotent rerun\n";
    return;
}

$home = aznet_d043_seed_page('Existing Home', 'KEEP EXISTING CONTENT');
wp_insert_post(['post_type'=>'post', 'post_status'=>'publish', 'post_title'=>'Existing Article', 'post_content'=>'KEEP EXISTING POST'], true);
update_option('show_on_front', 'page');
update_option('page_on_front', $home);
$menu = wp_create_nav_menu('Existing Customer Menu');
aznet_d043_assert(!is_wp_error($menu), 'could not create existing menu fixture');
set_theme_mod('nav_menu_locations', ['primary' => (int)$menu]);
aznet_d043_assert('EXISTING_ACTIVE' === AZnet\Theme\provisioning_site_mode(AZnet\Theme\provisioning_discovery()), 'active site misclassified');
$selection = aznet_d043_selection($key);
$plan = aznet_d043_plan($key, $selection);
$types = array_column($plan['operations'], 'type');
aznet_d043_assert(!in_array('set_front_page', $types, true) && !in_array('create_menu', $types, true), 'existing site plans would replace front/menu with draft content');

if ('rollback' === $scenario) {
    define('AZNET_THEME_TEST_PROVISIONING_FAILURE', 'page:projects:create');
    $run = AZnet\Theme\provisioning_apply_plan($plan);
    aznet_d043_assert(false === ($run['ok'] ?? false) && true === ($run['rollback_ok'] ?? false), 'injected failure did not fully roll back');
    aznet_d043_assert(0 === count(aznet_d043_owned($key, 'page')), 'current-run Pages survived rollback');
    aznet_d043_assert($home === (int)get_option('page_on_front'), 'rollback modified existing Front Page');
    aznet_d043_assert((int)get_nav_menu_locations()['primary'] === (int)$menu, 'rollback modified existing primary menu');
    aznet_d043_assert('KEEP EXISTING CONTENT' === get_post($home)->post_content, 'rollback modified customer Page');
    echo "PASS: D-043 {$template} injected failure rollback and preexisting WP ownership\n";
    return;
}

if ('active' === $scenario) {
    $run = AZnet\Theme\provisioning_apply_plan($plan);
    aznet_d043_assert(true === ($run['ok'] ?? false), 'existing-site confirmed apply failed: ' . implode('; ', $run['errors'] ?? []));
    $owned = aznet_d043_owned($key, 'page');
    aznet_d043_assert(6 === count($owned), 'existing site did not create six Page drafts');
    foreach ($owned as $page) {
        aznet_d043_assert('draft' === $page->post_status, 'existing website Page unexpectedly published: ' . $page->ID);
    }
    aznet_d043_assert($home === (int)get_option('page_on_front'), 'existing Front Page displaced');
    aznet_d043_assert('KEEP EXISTING CONTENT' === get_post($home)->post_content, 'customer Front Page overwritten');
    aznet_d043_assert((int)get_nav_menu_locations()['primary'] === (int)$menu, 'customer menu displaced');
    $posts = get_posts(['post_type'=>'post', 'post_status'=>'publish', 'posts_per_page'=>-1]);
    aznet_d043_assert(count($posts) === 1 && $posts[0]->post_content === 'KEEP EXISTING POST', 'customer Post rewritten');
    echo "PASS: D-043 {$template} active-site draft-only native provisioning\n";
    return;
}
throw new RuntimeException("Unknown D-043 scenario: {$scenario}");

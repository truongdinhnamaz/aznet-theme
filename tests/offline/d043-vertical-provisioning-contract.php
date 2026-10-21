<?php
/**
 * D-043 accepted Curtain 01 + Industrial 01 WordPress-native provisioning contracts.
 * Proposal-only discovery; no provider/private store or automatic activation writes.
 */
declare(strict_types=1);

namespace AZnet\Theme {
    // Simulate a previous Law recipe with matching WordPress Page role; a different
    // template may not borrow that authoritative provenance.
    function provisioning_find_owned_role(string $blueprint, string $kind, string $role): int {
        if ('law01-v1-2' === $blueprint && 'page' === $kind && 'about' === $role) {
            return 991;
        }
        return 0;
    }
}

namespace {
    if (!defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__, 2) . '/'); }
    $root = dirname(__DIR__, 2);
    require_once $root . '/inc/theme/template-registry.php';
    require_once $root . '/inc/theme/template-loader.php';
    require_once $root . '/inc/theme/provisioning-professional-services.php';
    require_once $root . '/inc/theme/provisioning-blueprints.php';
    require_once $root . '/inc/theme/provisioning-plan.php';
    require_once $root . '/inc/theme/provisioning-recommendations.php';

    $store =& \AZnet\Theme\template_manifest_store();
    $store = [];
    $summary = \AZnet\Theme\load_local_template_manifests();
    if ([] !== ($summary['rejected'] ?? [])) {
        fwrite(STDERR, "FAIL: registered pilot manifests invalid\n");
        exit(1);
    }

    $expected = [
        'curtain-01' => [
            'key' => 'curtain01-v1',
            'page_roles' => ['home', 'about', 'collection', 'projects', 'knowledge', 'contact'],
            'category_roles' => ['knowledge', 'projects'],
        ],
        'industrial-01' => [
            'key' => 'industrial01-v1',
            'page_roles' => ['home', 'about', 'solutions', 'projects', 'knowledge', 'contact'],
            'category_roles' => ['knowledge', 'projects'],
        ],
    ];

    foreach ($expected as $preset => $spec) {
        $key = \AZnet\Theme\template_provisioning_blueprint_key($preset);
        if ($key !== $spec['key']) {
            fwrite(STDERR, "FAIL: {$preset} not linked to real approved native blueprint\n");
            exit(1);
        }
        $blueprint = \AZnet\Theme\provisioning_blueprint($key);
        if (!is_array($blueprint)
            || $blueprint['key'] !== $key
            || ($blueprint['homepage_preset'] ?? '') !== $preset
            || array_keys($blueprint['pages'] ?? []) !== $spec['page_roles']
            || array_keys($blueprint['categories'] ?? []) !== $spec['category_roles']
            || ($blueprint['existing_site_page_status'] ?? '') !== 'draft') {
            fwrite(STDERR, "FAIL: {$preset} WordPress Page/Category recipe shape invalid\n");
            exit(1);
        }
        if (\AZnet\Theme\provisioning_required_page_roles($key) !== ['home', 'about', 'contact']) {
            fwrite(STDERR, "FAIL: {$preset} required roles are not template-native\n");
            exit(1);
        }
        foreach ($blueprint['pages'] as $role => $page) {
            if (!isset($page['title'], $page['excerpt'], $page['content'], $page['parent_role'])
                || '' === trim((string)$page['content'])
                || !in_array($page['status'] ?? '', ['publish', 'draft'], true)) {
                fwrite(STDERR, "FAIL: missing WordPress-owned editable Page stub: {$role}\n");
                exit(1);
            }
        }
        if (($blueprint['menu']['location'] ?? '') !== 'primary'
            || ($blueprint['menu']['roles'] ?? []) !== $spec['page_roles']) {
            fwrite(STDERR, "FAIL: {$preset} native menu roles mismatch\n");
            exit(1);
        }
        $serialized = json_encode($blueprint, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        foreach (['choiceguide_', 'woocommerce_update_', 'wp_insert_product', 'RootProfile', 'ConvertFlow', '@example', '1000+', 'giải thưởng', 'danh hiệu hàng đầu'] as $forbidden) {
            if (str_contains((string)$serialized, $forbidden)) {
                fwrite(STDERR, "FAIL: {$preset} recipe contains provider/fabricated truth: {$forbidden}\n");
                exit(1);
            }
        }

        $discovery = [
            'show_on_front' => 'page', 'front_page_id' => 42, 'homepage_preset' => $preset,
            'primary_menu_id' => 55, 'page_count' => 18, 'published_post_count' => 7,
            'pages' => [
                ['id' => 42, 'title' => 'Trang chủ', 'status' => 'publish'],
                ['id' => 991, 'title' => 'Unrelated previous site', 'status' => 'publish'],
            ],
            'categories' => [], 'prior_blueprints' => [],
            'homepage_slots' => [
                'about' => 991, // Law mapping is NOT current for a Curtain/Industrial site.
            ],
        ];
        $recommended = \AZnet\Theme\provisioning_recommendations($key, $discovery);
        if (($recommended['pages']['home']['object_id'] ?? 0) !== 42
            || ($recommended['pages']['about']['state'] ?? '') !== 'SUGGESTED_CREATE') {
            fwrite(STDERR, "FAIL: {$preset} chose unrelated Law provenance/mapping\n");
            exit(1);
        }

        $selections = ['pages' => [], 'categories' => [], 'menu' => ['action' => 'reuse', 'menu_id' => 55],
            'front_page' => ['action' => 'set_to_role', 'role' => 'home']];
        foreach ($spec['page_roles'] as $role) {
            $selections['pages'][$role] = [
                'action' => 'home' === $role ? 'reuse' : 'create',
                'object_id' => 'home' === $role ? 42 : 0,
            ];
        }
        foreach ($spec['category_roles'] as $role) {
            $selections['categories'][$role] = ['action' => 'create', 'object_id' => 0];
        }
        $plan = \AZnet\Theme\provisioning_build_plan($key, $selections, $discovery);
        $validation = \AZnet\Theme\provisioning_validate_plan($plan);
        if (true !== ($validation['ok'] ?? false)) {
            fwrite(STDERR, "FAIL: {$preset} plan invalid: " . implode(', ', $validation['errors'] ?? []) . "\n");
            exit(1);
        }
        $types = array_column($plan['operations'], 'type');
        foreach (['reuse_page', 'create_page', 'create_term', 'reuse_menu', 'set_homepage_preset'] as $must) {
            if (!in_array($must, $types, true)) {
                fwrite(STDERR, "FAIL: {$preset} plan missing {$must}\n");
                exit(1);
            }
        }
        if (in_array('map_homepage_sources', $types, true)
            || in_array('create_post', $types, true)
            || in_array('set_starter_site_defaults', $types, true)
            || in_array('set_search_visibility', $types, true)) {
            fwrite(STDERR, "FAIL: {$preset} writes forbidden Law/provider state\n");
            exit(1);
        }
        // Existing active site: don't point the front page to a newly created draft.
        $selections['pages']['home'] = ['action' => 'create', 'object_id' => 0];
        $draft_plan = \AZnet\Theme\provisioning_build_plan($key, $selections, $discovery);
        if (in_array('set_front_page', array_column($draft_plan['operations'], 'type'), true)
            || !str_contains((string)json_encode($draft_plan, JSON_UNESCAPED_UNICODE), 'Draft')) {
            fwrite(STDERR, "FAIL: {$preset} overwrites active front page with a draft\n");
            exit(1);
        }
        $discovery['prior_blueprints'] = [$key];
        if ('PREVIOUSLY_PROVISIONED' !== \AZnet\Theme\provisioning_site_mode($discovery)) {
            fwrite(STDERR, "FAIL: {$preset} previous-run mode not recognized\n");
            exit(1);
        }
    }

    // Existing public Law and generic service recipes stay valid.
    if (\AZnet\Theme\provisioning_required_page_roles('law01-v1-2')
        !== ['home', 'about', 'services', 'contact']
        || \AZnet\Theme\provisioning_blueprint('law01-v1-2') === null
        || \AZnet\Theme\provisioning_blueprint('professional-services-v1') === null) {
        fwrite(STDERR, "FAIL: existing Law/Professional provisioning was invalidated\n");
        exit(1);
    }

    foreach (['inc/theme/templates/curtain-01/provisioning.php', 'inc/theme/templates/industrial-01/provisioning.php'] as $path) {
        $recipe_source = is_file($root . '/' . $path) ? (string)file_get_contents($root . '/' . $path) : '';
        if ('' === $recipe_source) {
            fwrite(STDERR, "FAIL: missing local Theme-owned recipe: {$path}\n");
            exit(1);
        }
        foreach (['$wpdb', 'get_option(', 'update_option(', 'wp_insert_', 'wp_update_', 'sanitize_title(', 'url_to_postid('] as $forbidden) {
            if (str_contains($recipe_source, $forbidden)) {
                fwrite(STDERR, "FAIL: {$path} contains direct/domain runtime operation {$forbidden}\n");
                exit(1);
            }
        }
    }
    echo "PASS: D-043 Curtain/Industrial native Page/Category/Menu recipes, distinct provenance, guarded existing-site plans\n";
}

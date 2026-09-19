<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$settings = (string) file_get_contents($root . '/inc/theme/settings.php');
$map = (string) file_get_contents($root . '/inc/theme/homepage-content-map.php');
$admin = (string) file_get_contents($root . '/inc/admin/homepage.php');
$hero_action = (string) file_get_contents($root . '/inc/admin/homepage-hero.php');
$bootstrap = (string) file_get_contents($root . '/inc/admin/bootstrap.php');
$hero = (string) file_get_contents($root . '/template-parts/homepage/law-01/hero.php');
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-variants.css');
$pattern = $root . '/patterns/homepage-hero-content.php';

foreach ([
    "'homepage_hero_block'",
    "'homepage_hero_variant'",
    "'split'",
    "'centered'",
    "'inverse'",
    "'media-left'",
] as $needle) {
    assert(str_contains($settings, $needle), "D-030 settings contract missing: {$needle}");
}

assert(str_contains($map, 'function homepage_block_reference('), 'D-030 requires a typed WordPress wp_block resolver.');
assert(str_contains($map, "'wp_block' !== \$post->post_type"), 'Hero block resolver must accept only native wp_block posts.');
assert(str_contains($map, "'publish' !== \$post->post_status"), 'Hero block resolver must require published WordPress content.');

foreach ([
    'render_homepage_hero_library',
    'Thư viện Hero',
    'Dùng mẫu này',
    'Sửa nội dung Hero',
    'aznet_theme_apply_homepage_hero',
] as $needle) {
    assert(str_contains($admin, $needle), "D-030 Hero Library UI contract missing: {$needle}");
}
foreach ([
    'handle_homepage_hero_apply',
    'homepage_hero_candidate_reference',
    "'post_status'  => 'draft'",
    'wp_insert_post(',
    'current_user_can(',
    'check_admin_referer(',
    'WP_Block_Patterns_Registry',
    'homepage_hero_block',
    'homepage_hero_variant',
] as $needle) {
    assert(str_contains($hero_action, $needle), "D-030 explicit Hero action contract missing: {$needle}");
}
assert(str_contains($bootstrap, "admin_post_aznet_theme_apply_homepage_hero"), 'D-030 Hero action must be wired explicitly.');
assert(str_contains($admin, 'Hero WordPress đang soạn'), 'Control Center must expose draft-first Hero state.');
assert(str_contains($admin, 'website hiện tại chưa đổi cho đến khi Hero mới được xuất bản'), 'Draft-first Hero UX must preserve current public output.');

assert(str_contains($settings, "'schema_version'                => 3"), 'D-030 additive Hero settings must retain Theme settings schema v3.');
foreach (['wp_update_post(', 'wp_delete_post(', 'wp_trash_post('] as $forbidden_mutation) {
    assert(! str_contains($hero_action, $forbidden_mutation), "Hero variant action must not rewrite/delete WordPress Hero content: {$forbidden_mutation}");
}

foreach ([
    "setting( 'homepage_hero_block', 0 )",
    "setting( 'homepage_hero_variant', 'split' )",
    'homepage_block_reference',
    'aznet-theme-law01-hero--library',
] as $needle) {
    assert(str_contains($hero, $needle), "D-030 Hero render contract missing: {$needle}");
}

assert(strpos($hero, "setting( 'homepage_hero_block', 0 )") < strpos($hero, "setting( 'homepage_hero_page', 0 )"), 'Synced Hero block must take precedence over legacy Hero Page.');
assert(str_contains($hero, "setting( 'homepage_hero_page', 0 )"), 'Legacy Hero Page compatibility must remain.');
assert(str_contains($hero, "get_bloginfo( 'name' )"), 'Pre-v1.3.4 Site/Front Page fallback must remain.');

assert(is_file($pattern), 'D-030 requires a portable Core-block Hero content scaffold pattern.');
$pattern_source = is_file($pattern) ? (string) file_get_contents($pattern) : '';
foreach (['wp:group', 'wp:heading', 'wp:paragraph', 'wp:buttons', 'wp:image'] as $needle) {
    assert(str_contains($pattern_source, $needle), "Hero content pattern must use Core blocks: {$needle}");
}
assert(! preg_match('/<!--\s+wp:aznet-theme\//', $pattern_source), 'Hero content pattern must not introduce a proprietary AZnet block type.');

foreach ([
    'aznet-theme-law01-hero--split',
    'aznet-theme-law01-hero--centered',
    'aznet-theme-law01-hero--inverse',
    'aznet-theme-law01-hero--media-left',
] as $needle) {
    assert(str_contains($css, $needle), "Hero Library presentation variant missing: {$needle}");
}

foreach (['homepage_hero_title', 'homepage_hero_subtitle', 'homepage_hero_slogan', 'homepage_hero_body', 'homepage_hero_image'] as $forbidden) {
    assert(! str_contains($settings, $forbidden), "Theme must not store Hero copy/media: {$forbidden}");
    assert(! str_contains($admin, $forbidden), "Control Center must not create a Theme Hero content store: {$forbidden}");
}

echo "PASS: D-030 Homepage Hero Library contract\n";

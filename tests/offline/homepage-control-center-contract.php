<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$admin = $root . '/inc/admin/homepage.php';
if (! is_file($admin)) { fwrite(STDERR, "FAIL: Homepage admin module missing\n"); exit(1); }
$source = file_get_contents($admin);
$control = file_get_contents($root . '/inc/admin/control-center.php');
$bootstrap = file_get_contents($root . '/inc/admin/bootstrap.php');
$css = file_get_contents($root . '/assets/css/admin/control-center.css');
$settingsSource = file_get_contents($root . '/inc/theme/settings.php');

foreach (['render_homepage_settings', 'homepage_slot_statuses', 'READY', 'DRAFT', 'EMPTY', 'UNMAPPED', 'INVALID', 'PROVIDER_UNAVAILABLE', 'law-01', 'homepage_source_descriptor', 'homepage_source_key', 'homepage_source_value', 'homepage_hero_variant', 'get_pages(', 'get_categories('] as $required) {
    assert(str_contains($source, $required), "Missing Homepage admin contract: {$required}");
}

foreach ([
    'render_homepage_hero_library',
    'Thư viện Hero',
    'Nguồn Hero hiện tại',
    'Hero WordPress',
    'Hero WordPress đang soạn',
    'Hero Page cũ',
    'Dùng mẫu này',
    'Sửa nội dung Hero',
    'Tiếp tục sửa Hero',
    'không cần tạo Page',
    'get_edit_post_link',
    'aznet_theme_apply_homepage_hero',
] as $required) {
    assert(str_contains($source, $required), "Missing Homepage Hero Library UX contract: {$required}");
}

foreach ([
    '.aznet-theme-homepage-hero-editor',
    '.aznet-theme-homepage-hero-editor__status',
    '.aznet-theme-homepage-hero-library__grid',
    '.aznet-theme-homepage-hero-library__card',
    '.aznet-theme-homepage-hero-editor__actions',
] as $required) {
    assert(str_contains($css, $required), "Missing Homepage Hero editing UX presentation: {$required}");
}

assert(str_contains($control, "'homepage'"));
assert(str_contains($control, "'Trang chủ'"));
assert(str_contains($control, 'render_homepage_settings()'));
assert(str_contains($bootstrap, "require_once __DIR__ . '/homepage-hero.php';"));
assert(str_contains($bootstrap, "require_once __DIR__ . '/homepage.php';"));
assert(str_contains($bootstrap, 'admin_post_aznet_theme_apply_homepage_hero'));
assert(str_contains($source, "AZnet\\\\Theme\\\\Integrations\\\\RootProfile\\\\provider_available"), 'Homepage diagnostics must use the accepted RootProfile v1 public provider capability');
assert(! str_contains($source, "AZnet\\\\Theme\\\\Integrations\\\\RootProfile\\\\available"), 'Homepage diagnostics must not probe a nonexistent RootProfile capability');

foreach (['homepage_hero_title', 'homepage_hero_subtitle', 'homepage_hero_slogan', 'homepage_hero_body'] as $forbidden) {
    assert(! str_contains((string) $settingsSource, "'" . $forbidden . "' =>"), "Hero copy must remain WordPress-owned instead of becoming persisted Theme settings: {$forbidden}");
}

foreach (['wp_insert_post(', 'wp_update_post(', 'wp_delete_post(', 'wp_insert_term(', 'wp_delete_term('] as $forbidden) {
    assert(! str_contains($source, $forbidden), "Admin must not mutate native content: {$forbidden}");
}

echo "PASS: Homepage Control Center contract\n";
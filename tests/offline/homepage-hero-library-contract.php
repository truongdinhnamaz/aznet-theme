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
    'Chọn mẫu Hero',
    '<fieldset class="aznet-theme-homepage-hero-library__fieldset">',
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
    "current_user_can( 'publish_posts' )",
    'check_admin_referer(',
    'WP_Block_Patterns_Registry',
    'homepage_hero_block',
    'homepage_hero_variant',
] as $needle) {
    assert(str_contains($hero_action, $needle), "D-030 explicit Hero action contract missing: {$needle}");
}
assert(str_contains($bootstrap, "admin_post_aznet_theme_apply_homepage_hero"), 'D-030 Hero action must be wired explicitly.');
assert(str_contains($admin, 'Hero WordPress đang soạn'), 'Control Center must expose draft-first Hero state.');
assert(str_contains($admin, 'Hero WordPress chưa có nội dung'), 'Control Center must distinguish an empty published Hero from a render-ready Hero.');
assert(str_contains($admin, "'' !== trim( (string) \$hero_block->post_content ) ? 'READY' : 'EMPTY'"), 'Homepage diagnostics must report an empty published Hero as EMPTY.');

assert(str_contains($admin, 'chuyển Hero WordPress này về trạng thái Bản nháp'), 'Control Center must expose the non-destructive Hero rollback path.');
assert(str_contains($admin, 'website hiện tại chưa đổi cho đến khi Hero mới được xuất bản'), 'Draft-first Hero UX must preserve current public output.');
assert(! str_contains($hero_action, 'wp_safe_redirect( $edit_link )'), 'Hero Library apply must return to Control Center instead of auto-opening the native block editor.');
assert(str_contains($hero_action, "'hero'    => \$hero instanceof \\WP_Post && 'draft' === \$hero->post_status ? 'draft' : 'ready'"), 'Hero Library redirect state must reflect the actual draft/published Hero state.');

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
foreach (['wp:group', 'wp:columns', 'wp:heading', 'wp:paragraph', 'wp:buttons'] as $needle) {
    assert(str_contains($pattern_source, $needle), "Hero content pattern must use Core blocks: {$needle}");
}
assert(! preg_match('/<!--\s+wp:aznet-theme\//', $pattern_source), 'Hero content pattern must not introduce a proprietary AZnet block type.');
assert(! str_contains($pattern_source, '<!-- wp:image'), 'Hero scaffold must not serialize an empty Core Image block; users insert a valid Core Image through the editor.');
assert(str_contains($pattern_source, 'Chèn block Ảnh vào cột này để chọn ảnh Hero.'), 'Hero scaffold must explain where to insert the WordPress Core Image block.');
foreach ([
    'aznet-theme-homepage-hero-content__value',
    'aznet-theme-homepage-hero-content__secondary',
    'aznet-theme-homepage-hero-content__trust',
    'aznet-theme-homepage-hero-content__trust-grid',
    'aznet-theme-homepage-hero-content__trust-item',
    'Liên hệ tư vấn',
    'Xem dịch vụ',
    'Tư vấn rõ ràng',
    'Giải pháp thực tiễn',
    'Bảo mật thông tin',
    'Đồng hành tận tâm',
] as $needle) {
    assert(str_contains($pattern_source, $needle), "Complete WordPress-owned Hero authoring scaffold missing: {$needle}");
}
assert(str_contains($admin, 'Một nơi để sửa toàn bộ Hero'), 'Control Center must explain the single WordPress-owned Hero authoring surface.');
assert(str_contains($admin, 'chữ, nút, ảnh và cam kết'), 'Control Center must identify the complete editable Hero content scope.');
assert(str_contains($hero, "if ( '' === $hero_block_html )"), 'Legacy hard-coded trust copy must be isolated to the fallback path.');
assert(str_contains($hero, 'Legacy compatibility only.'), 'Hero template must document legacy-only hard-coded trust copy.');
foreach ([
    '.aznet-theme-homepage-hero-content__value',
    '.aznet-theme-homepage-hero-content__secondary',
    '.aznet-theme-homepage-hero-content__trust-grid',
    '.aznet-theme-homepage-hero-content__trust-item',
] as $needle) {
    assert(str_contains($css, $needle), "Complete Hero Library presentation missing: {$needle}");
}


assert(1 === preg_match('/homepage-hero-content__eyebrow\\s*\\{[^}]*color:\\s*var\\(--law01-gold-text,\\s*#8a632b\\);/s', $css), 'Hero Library eyebrow must use an accessible dark-gold text token on the light Hero surface.');
assert(1 === preg_match('/law01-hero--inverse \\.aznet-theme-homepage-hero-content__eyebrow\\s*\\{[^}]*color:\\s*var\\(--law01-client-cream,\\s*#fffaf1\\);/s', $css), 'Inverse Hero Library eyebrow must use a light accessible color on the dark surface.');

foreach ([
    'aznet-theme-law01-hero--split',
    'aznet-theme-law01-hero--centered',
    'aznet-theme-law01-hero--inverse',
    'aznet-theme-law01-hero--media-left',
    'aznet-theme-homepage-hero-content__media .wp-block-image',
    '.aznet-theme-homepage-hero-content__media-help',
    'display: none;',
] as $needle) {
    assert(str_contains($css, $needle), "Hero Library presentation variant missing: {$needle}");
}

foreach (['homepage_hero_title', 'homepage_hero_subtitle', 'homepage_hero_slogan', 'homepage_hero_body', 'homepage_hero_image'] as $forbidden) {
    assert(! str_contains($settings, $forbidden), "Theme must not store Hero copy/media: {$forbidden}");
    assert(! str_contains($admin, $forbidden), "Control Center must not create a Theme Hero content store: {$forbidden}");
}

echo "PASS: D-030 Homepage Hero Library contract\n";

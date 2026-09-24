<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$pattern = (string) file_get_contents($root . '/patterns/homepage-hero-content.php');
$heroAdmin = (string) file_get_contents($root . '/inc/admin/homepage-hero.php');
$adminBootstrap = (string) file_get_contents($root . '/inc/admin/bootstrap.php');
$homepageAdmin = (string) file_get_contents($root . '/inc/admin/homepage.php');

$roles = [
    'aznet-hero-eyebrow',
    'aznet-hero-title',
    'aznet-hero-value',
    'aznet-hero-lead',
    'aznet-hero-primary-cta',
    'aznet-hero-secondary-cta',
    'aznet-hero-media',
    'aznet-hero-trust-1',
    'aznet-hero-trust-2',
    'aznet-hero-trust-3',
    'aznet-hero-trust-4',
];
foreach ($roles as $role) {
    assert(str_contains($pattern, '"name":"' . $role . '"'), "Missing managed Hero role metadata: {$role}");
}

foreach ([
    'homepage_hero_form_model_from_content',
    'homepage_hero_form_managed_content',
    'homepage_hero_form_is_managed_content',
    'handle_homepage_hero_form_save',
    'handle_homepage_hero_form_upgrade',
    'homepage_hero_form_content_hash',
] as $function) {
    assert(str_contains($heroAdmin, 'function ' . $function . '('), "Missing Hero simple-form function: {$function}");
}

foreach ([
    'admin_post_aznet_theme_save_homepage_hero_form',
    'admin_post_aznet_theme_upgrade_homepage_hero_form',
] as $hook) {
    assert(str_contains($adminBootstrap, $hook), "Missing Hero simple-form action wiring: {$hook}");
}

foreach ([
    'Thông điệp mở đầu',
    'Tiêu đề Hero',
    'Mô tả chính',
    'Thông điệp hỗ trợ',
    'Nút chính',
    'Liên kết nút chính',
    'Nút phụ',
    'Liên kết nút phụ',
    'Ảnh Hero',
    'Cam kết %d',
    'homepage_hero_trust_',
    'Chỉnh nâng cao bằng WordPress',
] as $label) {
    assert(str_contains($homepageAdmin, $label), "Missing Hero simple-form UI: {$label}");
}

foreach ([
    'homepage_law01_hero_title',
    'homepage_law01_hero_description',
    'homepage_law01_hero_image',
    'homepage_law01_hero_primary_label',
] as $forbiddenKey) {
    assert(! str_contains($heroAdmin . $homepageAdmin, $forbiddenKey), "Hero editorial copy must not be stored in Theme settings: {$forbiddenKey}");
}

echo "PASS: Law 01 Hero simple-form contract\n";

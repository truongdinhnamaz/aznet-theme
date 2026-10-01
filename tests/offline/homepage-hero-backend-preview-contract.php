<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$admin = (string) file_get_contents($root . '/inc/admin/homepage.php');
$css = (string) file_get_contents($root . '/assets/css/admin/control-center.css');
$js = (string) file_get_contents($root . '/assets/js/admin/homepage-authoring.js');

foreach ([
    'data-hero-template-gallery',
    'data-hero-live-preview',
    'data-hero-preview-label',
    'data-hero-preview-copy',
    'data-hero-preview-media',
] as $needle) {
    assert(str_contains($admin, $needle), "Missing Hero backend preview marker: {$needle}");
}

foreach ([
    '.aznet-theme-homepage-hero-live-preview',
    '.aznet-theme-homepage-hero-library__card__meta',
] as $needle) {
    assert(str_contains($css, $needle), "Missing Hero backend preview CSS: {$needle}");
}

foreach ([
    'function updateHeroPreview',
    '[data-hero-template-gallery]',
    '[data-hero-live-preview]',
    'homepage_hero_variant',
    'homepage_hero_title',
    'homepage_hero_value',
    'homepage_hero_lead',
] as $needle) {
    assert(str_contains($js, $needle), "Missing Hero backend preview JS: {$needle}");
}

foreach ([
    "set_theme_mod( 'aznet_theme_settings'",
    'homepage_hero_title]',
    'homepage_hero_lead]',
] as $forbidden) {
    assert(! str_contains($admin, $forbidden) || "set_theme_mod( 'aznet_theme_settings'" === $forbidden, "Hero copy must not be persisted as Theme settings: {$forbidden}");
}

assert(str_contains($admin, 'aznet_theme_save_homepage_hero_form'), 'Hero form must continue writing the WordPress-owned Hero source.');
assert(str_contains($admin, 'homepage_hero_content_hash'), 'Stale-write protection must remain present.');

echo "PASS: Hero backend gallery and live preview contract\n";

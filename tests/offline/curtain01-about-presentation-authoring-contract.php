<?php
/** Curtain 01 About presentation copy/image must be authorable from the Homepage Map. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$admin = file_get_contents($root . '/inc/admin/homepage.php');
$authoring = file_get_contents($root . '/inc/admin/homepage-authoring.php');
$bootstrap = file_get_contents($root . '/inc/admin/bootstrap.php');

foreach ([
    [$admin, 'aznet_theme_save_curtain_about_presentation', 'Curtain About presentation form action missing'],
    [$admin, 'homepage_about_kicker', 'Curtain About kicker input missing'],
    [$admin, 'homepage_about_heading', 'Curtain About heading input missing'],
    [$admin, 'homepage_about_quote', 'Curtain About quote input missing'],
    [$admin, 'homepage_featured_image_id', 'Curtain About presentation image input missing'],
    [$admin, 'Sửa phần giới thiệu', 'Curtain About presentation authoring control missing'],
    [$authoring, 'function handle_curtain_about_presentation_save()', 'Curtain About presentation save handler missing'],
    [$authoring, "homepage_effective_source_key( 'curtain-01', 'about_image' )", 'Curtain About image does not save through scoped source key'],
    [$authoring, "normalize_settings( \$theme_settings )", 'Curtain About presentation save does not normalize Theme state'],
    [$bootstrap, 'admin_post_aznet_theme_save_curtain_about_presentation', 'Curtain About admin-post hook missing'],
] as [$haystack, $needle, $message]) {
    if (! is_string($haystack) || ! str_contains($haystack, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

echo "PASS: Curtain 01 About presentation authoring contract\n";

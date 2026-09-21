<?php
/** Curtain 01 C2 preset foundation contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/settings.php';

$normalized = AZnet\Theme\normalize_settings([
    'homepage_preset' => 'curtain-01',
]);

if (($normalized['homepage_preset'] ?? null) !== 'curtain-01') {
    fwrite(STDERR, "FAIL: Curtain 01 homepage preset is not accepted by strict settings normalization.\n");
    exit(1);
}

$composerPath = $root . '/inc/theme/homepage-composer.php';
$assetsPath   = $root . '/inc/theme/assets.php';
$cssPath      = $root . '/assets/css/components/homepage-curtain-01.css';

$composer = file_get_contents($composerPath);
$assets   = file_get_contents($assetsPath);

if (! is_string($composer) || ! str_contains($composer, "'curtain-01'")) {
    fwrite(STDERR, "FAIL: Homepage Composer does not recognize Curtain 01 as a Theme-owned preset.\n");
    exit(1);
}

if (! is_string($assets) || ! str_contains($assets, 'enqueue_homepage_curtain01_asset')) {
    fwrite(STDERR, "FAIL: Curtain 01 scoped Homepage asset loader is missing.\n");
    exit(1);
}

if (! str_contains($assets, '/assets/css/components/homepage-curtain-01.css')) {
    fwrite(STDERR, "FAIL: Curtain 01 Homepage asset path is not registered.\n");
    exit(1);
}

if (! is_file($cssPath)) {
    fwrite(STDERR, "FAIL: Curtain 01 Homepage stylesheet is missing.\n");
    exit(1);
}

$css = file_get_contents($cssPath);
if (! is_string($css) || ! str_contains($css, '.aznet-theme-homepage--curtain-01')) {
    fwrite(STDERR, "FAIL: Curtain 01 stylesheet is not scoped to the Curtain 01 Homepage surface.\n");
    exit(1);
}

$front = file_get_contents($root . '/front-page.php');
if (substr_count((string) $front, 'the_content();') !== 1) {
    fwrite(STDERR, "FAIL: Front Page must retain exactly one native the_content() boundary.\n");
    exit(1);
}

foreach (['wp_insert_post(', 'wp_update_post(', 'wp_delete_post(', 'update_post_meta(', 'update_option('] as $forbidden) {
    if (str_contains((string) $composer, $forbidden)) {
        fwrite(STDERR, "FAIL: Homepage Composer must remain content-mutation free: {$forbidden}\n");
        exit(1);
    }
}

echo "PASS: Curtain 01 C2 preset foundation contract\n";

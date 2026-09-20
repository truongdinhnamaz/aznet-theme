<?php
declare(strict_types=1);

namespace AZnet\Theme;

define('ABSPATH', __DIR__);

$GLOBALS['aznet_test_homepage_preset'] = 'law-01';
$GLOBALS['aznet_test_law01_variant'] = 'burgundy-gold';
$GLOBALS['aznet_test_enqueued_styles'] = [];

function setting($key, $default = null) {
    if ('homepage_preset' === $key) {
        return $GLOBALS['aznet_test_homepage_preset'];
    }
    return $default;
}

function homepage_preset(): string {
    return (string) $GLOBALS['aznet_test_homepage_preset'];
}

function homepage_law01_variant(): string {
    return (string) $GLOBALS['aznet_test_law01_variant'];
}

function get_theme_file_uri($path): string {
    return 'https://example.test/theme/' . ltrim((string) $path, '/');
}

function get_theme_file_path($path): string {
    return dirname(__DIR__, 2) . '/' . ltrim((string) $path, '/');
}

function wp_enqueue_style(...$args): void {
    $GLOBALS['aznet_test_enqueued_styles'][] = $args;
}

$root = dirname(__DIR__, 2);
require $root . '/inc/theme/header.php';
require $root . '/inc/theme/assets.php';

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

if (!function_exists(__NAMESPACE__ . '\\header_law01_active')) {
    $fail('Header needs a request-independent Law 01 site-shell activation helper.');
}

$GLOBALS['aznet_test_homepage_preset'] = 'law-01';
if (!header_law01_active()) {
    $fail('Law 01 site Header must stay active outside the Front Page when the site preset is Law 01.');
}

$GLOBALS['aznet_test_homepage_preset'] = 'off';
if (header_law01_active()) {
    $fail('Generic sites must not inherit the Law 01 Header.');
}

$siteHeader = (string) file_get_contents($root . '/template-parts/header/site-header.php');
$brand = (string) file_get_contents($root . '/template-parts/header/brand.php');
$topbar = (string) file_get_contents($root . '/template-parts/header/law01-topbar.php');
$mobile = (string) file_get_contents($root . '/template-parts/header/mobile-panel.php');

if (
    str_contains($siteHeader, 'homepage_composer_active()') ||
    !str_contains($siteHeader, "['law01_header']")
) {
    $fail('Header composer must derive Law 01 shell state from the site-level Header helper, not the current request.');
}

foreach ([
    'brand' => $brand,
    'topbar' => $topbar,
    'mobile panel' => $mobile,
] as $label => $source) {
    if (!str_contains($source, 'law01_header')) {
        $fail("{$label} must consume the site-wide Law 01 Header context.");
    }
}

if (!function_exists(__NAMESPACE__ . '\\enqueue_header_law01_asset')) {
    $fail('Law 01 Header needs its own surface-aware stylesheet enqueue.');
}

$GLOBALS['aznet_test_homepage_preset'] = 'law-01';
$GLOBALS['aznet_test_law01_variant'] = 'burgundy-gold';
$GLOBALS['aznet_test_enqueued_styles'] = [];
enqueue_header_law01_asset('test');
if (1 !== count($GLOBALS['aznet_test_enqueued_styles'])) {
    $fail('Burgundy Law 01 sites must load exactly one dedicated Header stylesheet on every request.');
}

$style = $GLOBALS['aznet_test_enqueued_styles'][0];
if (
    'aznet-theme-header-law-01' !== ($style[0] ?? '') ||
    !str_contains((string) ($style[1] ?? ''), 'header-law-01.css') ||
    ['aznet-theme-site-header'] !== ($style[2] ?? [])
) {
    $fail('Dedicated Law 01 Header stylesheet must depend only on the base Theme Header presentation.');
}

$GLOBALS['aznet_test_homepage_preset'] = 'off';
$GLOBALS['aznet_test_enqueued_styles'] = [];
enqueue_header_law01_asset('test');
if ([] !== $GLOBALS['aznet_test_enqueued_styles']) {
    $fail('Law 01 Header stylesheet must not load on non-Law01 sites.');
}

if (!is_file($root . '/assets/css/components/header-law-01.css')) {
    $fail('Dedicated Law 01 Header stylesheet is missing.');
}

echo "PASS: Law 01 Header is site-wide, request-independent and surface-aware.\n";

<?php
declare(strict_types=1);
namespace {
function get_theme_file_path($path): string { return dirname(__DIR__, 2) . '/' . ltrim($path, '/'); }
}
namespace AZnet\Theme {
define('ABSPATH', __DIR__);
$root = dirname(__DIR__, 2);
$module = $root . '/inc/theme/law01-reference.php';
if (!is_file($module)) { fwrite(STDERR, "FAIL: scoped reference asset module is missing.\n"); exit(1); }
$GLOBALS['law01_active'] = true;
$GLOBALS['law01_variant'] = 'burgundy-gold';
$GLOBALS['law01_enqueued'] = [];
function homepage_composer_active(): bool { return $GLOBALS['law01_active']; }
function homepage_law01_variant(): string { return $GLOBALS['law01_variant']; }
function get_theme_file_uri($path): string { return 'https://example.test/theme/' . ltrim($path, '/'); }
function wp_enqueue_style(...$args): void { $GLOBALS['law01_enqueued'][] = $args; }
require $root . '/inc/theme/assets.php';
require $module;
foreach ([[true, 'burgundy-gold', 1], [true, 'navy-gold', 0], [false, 'burgundy-gold', 0]] as [$active, $variant, $expected]) {
    $GLOBALS['law01_active'] = $active;
    $GLOBALS['law01_variant'] = $variant;
    $GLOBALS['law01_enqueued'] = [];
    enqueue_law01_reference_asset('test');
    if (count($GLOBALS['law01_enqueued']) !== $expected) { fwrite(STDERR, "FAIL: reference styles escaped their surface.\n"); exit(1); }
    if ($expected && ($GLOBALS['law01_enqueued'][0][2] !== ['aznet-theme-homepage-law-01-variants'] || !str_starts_with($GLOBALS['law01_enqueued'][0][3], 'test-'))) { fwrite(STDERR, "FAIL: missing style dependency or content fingerprint.\n"); exit(1); }
}
$bootstrap = file_get_contents($root . '/inc/theme/bootstrap.php');
if (!str_contains($bootstrap, "require_once __DIR__ . '/law01-reference.php';") || !str_contains($bootstrap, 'enqueue_law01_reference_asset')) {
    fwrite(STDERR, "FAIL: reference module is not wired through Theme bootstrap.\n"); exit(1);
}
echo "PASS: reference styles are scoped, ordered, fingerprinted and wired.\n";
}

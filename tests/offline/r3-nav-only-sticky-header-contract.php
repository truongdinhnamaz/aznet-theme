<?php
/**
 * Opt-in navigation-only sticky Header: setting, presentation and legacy safety.
 *
 * @package AZnetTheme
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

$GLOBALS['r3_nav_only_theme_mods'] = [
    'aznet_theme_settings' => [
        'header_preset' => 'standard',
        'header_sticky' => 'nav-only',
        'header_catalog_mode' => 'homepage-rail',
    ],
];

if (! function_exists('get_theme_mod')) {
    function get_theme_mod(string $name, mixed $default = false): mixed {
        return $GLOBALS['r3_nav_only_theme_mods'][$name] ?? $default;
    }
}

require_once $root . '/inc/theme/settings.php';
require_once $root . '/inc/theme/header.php';

$fail = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$defaults = AZnet\Theme\settings_defaults();
$fail('sticky' === $defaults['header_sticky'], 'existing Header sticky default changed');
$normalized = AZnet\Theme\normalize_settings(['header_sticky' => 'nav-only']);
$fail('nav-only' === $normalized['header_sticky'], 'nav-only mode is not normalized');
$fail('nav-only' === AZnet\Theme\header_sticky_mode(), 'runtime nav-only setting is not preserved');

foreach (['off', 'sticky', 'sticky-compact'] as $old) {
    $fail($old === AZnet\Theme\normalize_settings(['header_sticky' => $old])['header_sticky'],
        "legacy {$old} mode changed");
}
$fail('sticky' === AZnet\Theme\normalize_settings(['header_sticky' => 'unsupported'])['header_sticky'],
    'invalid Header mode should fail safe to existing default');

$css = (string) file_get_contents($root . '/assets/css/components/site-header.css');
$catalog = (string) file_get_contents($root . '/assets/css/components/catalog-navigation.css');
$admin = (string) file_get_contents($root . '/inc/admin/control-center.php');
$markup = (string) file_get_contents($root . '/template-parts/header/site-header.php');

foreach ([
    '.aznet-theme-site-header--nav-only',
    '--aznet-theme-header-top-row-height',
    'position: sticky',
    '.admin-bar .aznet-theme-site-header--nav-only',
    '.aznet-theme-site-header__brand',
    '.aznet-theme-site-header__actions',
    '.aznet-theme-site-header__mobile',
    '@media (max-width: 1100px)',
] as $marker) {
    $fail(str_contains($css, $marker), "Header nav-only CSS missing {$marker}");
}
foreach ([
    '.aznet-theme-site-header--catalog.aznet-theme-site-header--nav-only',
    '.aznet-theme-site-header__catalog-rail-label',
    '.aznet-theme-site-header__nav',
] as $marker) {
    $fail(str_contains($catalog, $marker), "catalog nav-only alignment missing {$marker}");
}
$fail(str_contains($admin, "'nav-only' =>"), 'Header Control Center does not expose nav-only');
$fail(str_contains($markup, "'aznet-theme-site-header--' . $sticky_mode"),
    'native Header composer no longer emits normalized mode class');

echo "PASS: R3 nav-only opt-in Header, legacy modes and ownership-safe CSS contract\n";

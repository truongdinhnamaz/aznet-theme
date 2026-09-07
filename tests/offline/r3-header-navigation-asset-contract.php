<?php
/**
 * R3 accessible mobile Header navigation asset contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root      = dirname(__DIR__, 2);
$js_path   = $root . '/assets/js/header-navigation.js';
$panel_php = $root . '/template-parts/header/mobile-panel.php';

if (! is_file($js_path)) {
    fwrite(STDERR, "FAIL: missing assets/js/header-navigation.js\n");
    exit(1);
}

$js    = file_get_contents($js_path);
$panel = file_get_contents($panel_php);
$asset = file_get_contents($root . '/inc/theme/assets.php');
$head  = file_get_contents($root . '/inc/theme/header.php');
$composer = file_get_contents($root . '/template-parts/header/site-header.php');

if (false === $js || false === $panel || false === $asset || false === $head || false === $composer) {
    fwrite(STDERR, "FAIL: unable to read R3 mobile navigation implementation\n");
    exit(1);
}

foreach ([
    'function header_mobile_panel_enabled(): bool',
    "setting( 'header_search', true )",
    "has_nav_menu( 'primary' )",
] as $needle) {
    if (! str_contains($head, $needle)) {
        fwrite(STDERR, "FAIL: mobile-panel capability resolver missing {$needle}\n");
        exit(1);
    }
}

if (! str_contains($composer, 'header_mobile_panel_enabled()')) {
    fwrite(STDERR, "FAIL: Header composer must use the same mobile-panel capability resolver as asset loading\n");
    exit(1);
}

foreach ([
    'data-aznet-theme-nav-trigger',
    'data-aznet-theme-nav-panel',
    'aria-expanded="false"',
    'aria-controls="aznet-theme-mobile-panel"',
    'id="aznet-theme-mobile-panel"',
    '<button',
] as $needle) {
    if (! str_contains($panel, $needle)) {
        fwrite(STDERR, "FAIL: mobile panel markup missing {$needle}\n");
        exit(1);
    }
}

if (preg_match('/<[^>]+data-aznet-theme-nav-panel[^>]+\shidden(?:\s|=|>)/i', $panel)) {
    fwrite(STDERR, "FAIL: server-rendered mobile navigation must remain reachable when JavaScript fails\n");
    exit(1);
}

foreach ([
    "'[data-aznet-theme-nav-trigger]'",
    "'[data-aznet-theme-nav-panel]'",
    "event.key === 'Escape'",
    "event.key === 'Tab'",
    'aria-expanded',
    '.hidden = true',
    '.hidden = false',
    '.focus()',
    'pagehide',
    'button:not([disabled])',
] as $needle) {
    if (! str_contains($js, $needle)) {
        fwrite(STDERR, "FAIL: Header navigation enhancement missing {$needle}\n");
        exit(1);
    }
}

foreach (['jQuery', '$(', 'React', 'Vue'] as $needle) {
    if (str_contains($js, $needle)) {
        fwrite(STDERR, "FAIL: Header navigation enhancement must remain framework-free: {$needle}\n");
        exit(1);
    }
}

foreach ([
    'function enqueue_header_navigation_asset(',
    'header_mobile_panel_enabled()',
    "'aznet-theme-header-navigation'",
    "'/assets/js/header-navigation.js'",
] as $needle) {
    if (! str_contains($asset, $needle)) {
        fwrite(STDERR, "FAIL: Header navigation enqueue path missing {$needle}\n");
        exit(1);
    }
}

if (! preg_match("/wp_enqueue_script\(\s*'aznet-theme-header-navigation'\s*,[\s\S]*?\[\s*\]\s*,/", $asset)) {
    fwrite(STDERR, "FAIL: Header navigation script must declare no JavaScript framework dependencies\n");
    exit(1);
}

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
$GLOBALS['r3_theme_mods'] = [
    'aznet_theme_settings' => [
        'header_preset'    => 'standard',
        'header_sticky'    => 'sticky',
        'header_search'    => false,
        'header_utilities' => false,
    ],
];
$GLOBALS['r3_has_primary_menu'] = false;
$GLOBALS['r3_enqueued_scripts'] = [];

if (! function_exists('get_theme_mod')) {
    function get_theme_mod(string $name, mixed $default = false): mixed {
        return $GLOBALS['r3_theme_mods'][$name] ?? $default;
    }
}
if (! function_exists('has_nav_menu')) {
    function has_nav_menu(string $location): bool {
        return 'primary' === $location && (bool) $GLOBALS['r3_has_primary_menu'];
    }
}
if (! function_exists('wc_get_page_permalink')) {
    function wc_get_page_permalink(string $page): string|false {
        return false;
    }
}
if (! function_exists('wc_get_cart_url')) {
    function wc_get_cart_url(): string {
        return '';
    }
}
if (! function_exists('get_theme_file_uri')) {
    function get_theme_file_uri(string $file = ''): string {
        return 'https://example.test/theme' . $file;
    }
}
if (! function_exists('wp_enqueue_script')) {
    function wp_enqueue_script(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, array|bool $args = false): void {
        $GLOBALS['r3_enqueued_scripts'][$handle] = compact('src', 'deps', 'ver', 'args');
    }
}

require_once $root . '/inc/theme/settings.php';
require_once $root . '/inc/theme/header.php';
require_once $root . '/inc/theme/assets.php';

if (AZnet\Theme\header_mobile_panel_enabled()) {
    fwrite(STDERR, "FAIL: empty disabled mobile Header capability must resolve false\n");
    exit(1);
}
AZnet\Theme\enqueue_header_navigation_asset('test');
if ([] !== $GLOBALS['r3_enqueued_scripts']) {
    fwrite(STDERR, "FAIL: navigation script enqueued when mobile panel is not rendered\n");
    exit(1);
}

$GLOBALS['r3_has_primary_menu'] = true;
if (! AZnet\Theme\header_mobile_panel_enabled()) {
    fwrite(STDERR, "FAIL: primary menu must enable mobile Header panel\n");
    exit(1);
}
AZnet\Theme\enqueue_header_navigation_asset('test');
$script = $GLOBALS['r3_enqueued_scripts']['aznet-theme-header-navigation'] ?? null;
if (! is_array($script) || $script['deps'] !== []) {
    fwrite(STDERR, "FAIL: enabled mobile Header panel did not enqueue dependency-free navigation enhancement\n");
    exit(1);
}

$GLOBALS['r3_theme_mods']['aznet_theme_settings']['header_search'] = true;
$GLOBALS['r3_has_primary_menu'] = false;
if (! AZnet\Theme\header_mobile_panel_enabled()) {
    fwrite(STDERR, "FAIL: enabled search must keep mobile Header panel available without a menu\n");
    exit(1);
}

echo "PASS: R3 Header navigation progressive-enhancement asset contract\n";

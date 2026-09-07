<?php
/**
 * R3 sticky-compact Header enhancement contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root    = dirname(__DIR__, 2);
$js_path = $root . '/assets/js/sticky-header.js';

if (! is_file($js_path)) {
    fwrite(STDERR, "FAIL: missing assets/js/sticky-header.js\n");
    exit(1);
}

$js     = file_get_contents($js_path);
$asset  = file_get_contents($root . '/inc/theme/assets.php');
$css    = file_get_contents($root . '/assets/css/components/site-header.css');

if (false === $js || false === $asset || false === $css) {
    fwrite(STDERR, "FAIL: unable to read sticky Header implementation\n");
    exit(1);
}

foreach ([
    "'[data-aznet-theme-site-header].aznet-theme-site-header--sticky-compact'",
    'requestAnimationFrame',
    "addEventListener('scroll'",
    'passive: true',
    "classList.toggle('is-aznet-theme-header-compact'",
    'window.scrollY',
] as $needle) {
    if (! str_contains($js, $needle)) {
        fwrite(STDERR, "FAIL: sticky Header enhancement missing {$needle}\n");
        exit(1);
    }
}

foreach (['jQuery', '$(', 'React', 'Vue', 'style.height', 'offsetHeight'] as $needle) {
    if (str_contains($js, $needle)) {
        fwrite(STDERR, "FAIL: sticky Header enhancement uses forbidden dependency/layout mutation {$needle}\n");
        exit(1);
    }
}

foreach ([
    'function enqueue_sticky_header_asset(',
    "'sticky-compact' !== header_sticky_mode()",
    "'aznet-theme-sticky-header'",
    "'/assets/js/sticky-header.js'",
] as $needle) {
    if (! str_contains($asset, $needle)) {
        fwrite(STDERR, "FAIL: sticky Header enqueue path missing {$needle}\n");
        exit(1);
    }
}

if (! preg_match("/wp_enqueue_script\(\s*'aznet-theme-sticky-header'\s*,[\s\S]*?\[\s*\]\s*,/", $asset)) {
    fwrite(STDERR, "FAIL: sticky Header script must declare no JavaScript framework dependencies\n");
    exit(1);
}

foreach ([
    '.aznet-theme-site-header--sticky-compact',
    '.aznet-theme-site-header--sticky-compact.is-aznet-theme-header-compact',
] as $selector) {
    if (! str_contains($css, $selector)) {
        fwrite(STDERR, "FAIL: sticky Header stylesheet missing {$selector}\n");
        exit(1);
    }
}

if (! preg_match('/@media\s*\(prefers-reduced-motion:\s*reduce\)[\s\S]*?\.aznet-theme-site-header--sticky-compact[\s\S]*?transition\s*:\s*none/s', $css)) {
    fwrite(STDERR, "FAIL: sticky-compact transition must be disabled under reduced motion\n");
    exit(1);
}

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
$GLOBALS['r3_theme_mods'] = [
    'aznet_theme_settings' => [
        'header_preset'    => 'standard',
        'header_sticky'    => 'off',
        'header_search'    => false,
        'header_utilities' => false,
    ],
];
$GLOBALS['r3_enqueued_scripts'] = [];

if (! function_exists('get_theme_mod')) {
    function get_theme_mod(string $name, mixed $default = false): mixed {
        return $GLOBALS['r3_theme_mods'][$name] ?? $default;
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

foreach (['off', 'sticky'] as $mode) {
    $GLOBALS['r3_theme_mods']['aznet_theme_settings']['header_sticky'] = $mode;
    $GLOBALS['r3_enqueued_scripts'] = [];
    AZnet\Theme\enqueue_sticky_header_asset('test');
    if ([] !== $GLOBALS['r3_enqueued_scripts']) {
        fwrite(STDERR, "FAIL: sticky enhancement enqueued for {$mode} mode\n");
        exit(1);
    }
}

$GLOBALS['r3_theme_mods']['aznet_theme_settings']['header_sticky'] = 'sticky-compact';
$GLOBALS['r3_enqueued_scripts'] = [];
AZnet\Theme\enqueue_sticky_header_asset('test');
$script = $GLOBALS['r3_enqueued_scripts']['aznet-theme-sticky-header'] ?? null;
if (! is_array($script) || $script['deps'] !== []) {
    fwrite(STDERR, "FAIL: sticky-compact did not enqueue a dependency-free enhancement\n");
    exit(1);
}

echo "PASS: R3 sticky-compact Header enhancement contract\n";

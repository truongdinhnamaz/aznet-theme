<?php
/**
 * R3 Header preset/context and primitive-composition contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root        = dirname(__DIR__, 2);
$header_file = $root . '/inc/theme/header.php';

if (! is_file($header_file)) {
    fwrite(STDERR, "FAIL: R3 header resolver missing inc/theme/header.php\n");
    exit(1);
}

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

$GLOBALS['r3_theme_mods'] = [
    'aznet_theme_settings' => [
        'schema_version'   => 1,
        'visual_preset'    => 'default',
        'header_preset'    => 'overlay',
        'header_sticky'    => 'sticky-compact',
        'header_search'    => true,
        'header_utilities' => true,
    ],
];
$GLOBALS['r3_is_front_page'] = false;
$GLOBALS['r3_woo_enabled']   = false;

if (! function_exists('get_theme_mod')) {
    function get_theme_mod(string $name, mixed $default = false): mixed {
        return $GLOBALS['r3_theme_mods'][$name] ?? $default;
    }
}
if (! function_exists('is_front_page')) {
    function is_front_page(): bool {
        return (bool) $GLOBALS['r3_is_front_page'];
    }
}
if (! function_exists('get_bloginfo')) {
    function get_bloginfo(string $show = ''): string {
        return 'AZnet Header Fixture';
    }
}
if (! function_exists('home_url')) {
    function home_url(string $path = ''): string {
        return 'https://example.test' . $path;
    }
}
if (! function_exists('wp_get_attachment_image')) {
    function wp_get_attachment_image(int $attachment_id, string|array $size = 'thumbnail', bool $icon = false, array $attr = []): string {
        return $attachment_id > 0 ? '<img class="aznet-theme-site-header__logo" src="logo.png" alt="">' : '';
    }
}
if (! function_exists('wp_nav_menu')) {
    function wp_nav_menu(array $args = []): string {
        $id = isset($args['menu_id']) ? (string) $args['menu_id'] : 'menu';
        return '<ul id="' . $id . '" class="aznet-theme-site-header__menu"><li><a href="#">Menu</a></li></ul>';
    }
}
if (! function_exists('wc_get_page_permalink')) {
    function wc_get_page_permalink(string $page): string|false {
        return $GLOBALS['r3_woo_enabled'] ? 'https://example.test/my-account/' : false;
    }
}
if (! function_exists('wc_get_cart_url')) {
    function wc_get_cart_url(): string {
        return $GLOBALS['r3_woo_enabled'] ? 'https://example.test/cart/' : '';
    }
}

require_once $root . '/inc/theme/settings.php';
require_once $header_file;

if (AZnet\Theme\header_preset() !== 'overlay') {
    fwrite(STDERR, "FAIL: requested Header preset was not read from normalized Theme settings\n");
    exit(1);
}
if (AZnet\Theme\header_sticky_mode() !== 'sticky-compact') {
    fwrite(STDERR, "FAIL: Header sticky mode was not read from normalized Theme settings\n");
    exit(1);
}
if (AZnet\Theme\effective_header_preset() !== 'standard') {
    fwrite(STDERR, "FAIL: overlay must degrade to standard away from the WordPress front page\n");
    exit(1);
}

$GLOBALS['r3_is_front_page'] = true;
if (AZnet\Theme\effective_header_preset() !== 'overlay') {
    fwrite(STDERR, "FAIL: overlay must remain effective on the WordPress front page\n");
    exit(1);
}

$context = AZnet\Theme\header_context();
foreach ([
    'site_title',
    'home_url',
    'logo_id',
    'logo_html',
    'primary_menu',
    'mobile_menu',
    'account_url',
    'cart_url',
    'search_enabled',
    'utilities_enabled',
    'requested_preset',
    'effective_preset',
    'sticky_mode',
] as $key) {
    if (! array_key_exists($key, $context)) {
        fwrite(STDERR, "FAIL: Header context missing {$key}\n");
        exit(1);
    }
}

if ($context['site_title'] !== 'AZnet Header Fixture') {
    fwrite(STDERR, "FAIL: Header context did not consume WordPress site title\n");
    exit(1);
}
if ($context['account_url'] !== '' || $context['cart_url'] !== '') {
    fwrite(STDERR, "FAIL: Woo-absent Header context must fail soft with empty account/cart URLs\n");
    exit(1);
}
if ($context['search_enabled'] !== true || $context['utilities_enabled'] !== true) {
    fwrite(STDERR, "FAIL: Header context did not preserve normalized presentation visibility settings\n");
    exit(1);
}

$GLOBALS['r3_woo_enabled'] = true;
$woo_context = AZnet\Theme\header_context();
if ($woo_context['account_url'] !== 'https://example.test/my-account/' || $woo_context['cart_url'] !== 'https://example.test/cart/') {
    fwrite(STDERR, "FAIL: Header context did not consume public Woo account/cart URLs\n");
    exit(1);
}

$required_parts = [
    'template-parts/header/brand.php',
    'template-parts/header/primary-navigation.php',
    'template-parts/header/search.php',
    'template-parts/header/commerce-actions.php',
    'template-parts/header/mobile-panel.php',
];
foreach ($required_parts as $relative) {
    if (! is_file($root . '/' . $relative)) {
        fwrite(STDERR, "FAIL: missing Header primitive {$relative}\n");
        exit(1);
    }
}

$composer = file_get_contents($root . '/template-parts/header/site-header.php');
if (false === $composer) {
    fwrite(STDERR, "FAIL: unable to read Header composer\n");
    exit(1);
}
foreach ([
    'effective_header_preset()',
    'header_sticky_mode()',
    'header_context()',
    "get_template_part( 'template-parts/header/brand'",
    "get_template_part( 'template-parts/header/primary-navigation'",
    "get_template_part( 'template-parts/header/mobile-panel'",
] as $needle) {
    if (! str_contains($composer, $needle)) {
        fwrite(STDERR, "FAIL: Header composer missing {$needle}\n");
        exit(1);
    }
}

$scan = file_get_contents($header_file) . $composer;
foreach ([
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'WC()->',
    '$_SESSION',
    'choiceguide_',
    'rootprofile_',
] as $needle) {
    if (str_contains($scan, $needle)) {
        fwrite(STDERR, "FAIL: Header crosses ownership boundary via {$needle}\n");
        exit(1);
    }
}
foreach (['is_page(', 'get_queried_object_id(', 'REQUEST_URI', 'the_slug', 'post_name'] as $needle) {
    if (str_contains($scan, $needle)) {
        fwrite(STDERR, "FAIL: Header preset routing uses forbidden heuristic {$needle}\n");
        exit(1);
    }
}

$bootstrap = file_get_contents($root . '/inc/theme/bootstrap.php');
if (false === $bootstrap || substr_count($bootstrap, "require_once __DIR__ . '/header.php';") !== 1) {
    fwrite(STDERR, "FAIL: bootstrap must load Header resolver exactly once\n");
    exit(1);
}

echo "PASS: R3 Header preset/context composition contract\n";

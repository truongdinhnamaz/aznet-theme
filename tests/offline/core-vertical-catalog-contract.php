<?php
/**
 * Core vertical catalog navigation: WordPress-menu ownership, opt-in and
 * Industrial 01 Hero presentation regression (WordPress-clean harness).
 */
declare(strict_types=1);

namespace {
    if (!defined('ABSPATH')) {
        define('ABSPATH', __DIR__ . '/');
    }
    $GLOBALS['azt_catalog_settings'] = [];
    $GLOBALS['azt_catalog_location'] = false;
    $GLOBALS['azt_catalog_front'] = false;
    $GLOBALS['azt_catalog_preset'] = 'industrial-01';
    $GLOBALS['azt_catalog_composer'] = true;
    $GLOBALS['azt_catalog_menu_calls'] = [];

    function get_theme_mod(string $key, mixed $fallback = false): mixed {
        return 'aznet_theme_settings' === $key ? $GLOBALS['azt_catalog_settings'] : $fallback;
    }
    function has_nav_menu(string $location): bool {
        return 'product-catalog' === $location && (bool) $GLOBALS['azt_catalog_location'];
    }
    function wp_nav_menu(array $args): string {
        $GLOBALS['azt_catalog_menu_calls'][] = $args;
        $id = (string) ($args['menu_id'] ?? 'missing');
        return '<ul id="' . htmlspecialchars($id, ENT_QUOTES) . '"><li><a href="/catalog/">Danh mục</a></li></ul>';
    }
    function get_bloginfo(string $field): string { return 'AZnet Catalog Test'; }
    function home_url(string $path = ''): string { return 'https://example.test' . $path; }
    function is_front_page(): bool { return (bool) $GLOBALS['azt_catalog_front']; }
}
namespace AZnet\Theme {
    function homepage_preset(): string { return $GLOBALS['azt_catalog_preset']; }
    function homepage_composer_active(): bool { return (bool) $GLOBALS['azt_catalog_composer']; }
}
namespace {
    $root = dirname(__DIR__, 2);
    require_once $root . '/inc/theme/settings.php';
    require_once $root . '/inc/theme/header.php';
    $check = static function (bool $condition, string $message): void {
        if (!$condition) {
            fwrite(STDERR, "FAIL: {$message}\n");
            exit(1);
        }
    };

    $check((AZnet\Theme\settings_defaults()['header_catalog_mode'] ?? null) === 'off', 'opt-in default must be off');
    foreach (['off', 'dropdown', 'homepage-rail'] as $mode) {
        $check(
            AZnet\Theme\normalize_settings(['header_catalog_mode' => $mode])['header_catalog_mode'] === $mode,
            "normalized catalog mode {$mode}"
        );
    }
    $check(
        AZnet\Theme\normalize_settings(['header_catalog_mode' => 'unsafe'])['header_catalog_mode'] === 'off',
        'invalid mode must fail closed'
    );

    $GLOBALS['azt_catalog_settings'] = ['header_catalog_mode' => 'dropdown'];
    $check(!AZnet\Theme\header_catalog_enabled(), 'unassigned native menu does not render');
    $check(AZnet\Theme\header_catalog_menu_html('catalog-fixture') === '', 'missing menu returns empty markup');

    $GLOBALS['azt_catalog_location'] = true;
    $check(AZnet\Theme\header_catalog_enabled(), 'assigned native menu enables opt-in catalog');
    $html = AZnet\Theme\header_catalog_menu_html('catalog-fixture');
    $check(str_contains($html, 'catalog-fixture'), 'native wp_nav_menu output is consumed');
    $last_call = end($GLOBALS['azt_catalog_menu_calls']);
    $check(($last_call['theme_location'] ?? '') === 'product-catalog', 'only WordPress menu location is authoritative');
    $check(($last_call['fallback_cb'] ?? true) === false, 'never fabricate fallback categories');

    $check(!AZnet\Theme\header_catalog_home_rail(), 'dropdown mode never forces a homepage rail');
    $GLOBALS['azt_catalog_settings'] = ['header_catalog_mode' => 'homepage-rail'];
    $check(!AZnet\Theme\header_catalog_home_rail(), 'non-front-page cannot show rail');
    $GLOBALS['azt_catalog_front'] = true;
    $check(AZnet\Theme\header_catalog_home_rail(), 'Industrial 01 front page may show menu rail');
    $GLOBALS['azt_catalog_preset'] = 'law-01';
    $check(!AZnet\Theme\header_catalog_home_rail(), 'other homepage presets are untouched');
    $GLOBALS['azt_catalog_preset'] = 'industrial-01';
    $GLOBALS['azt_catalog_composer'] = false;
    $check(!AZnet\Theme\header_catalog_home_rail(), 'inactive composer fails soft');
    $GLOBALS['azt_catalog_composer'] = true;
    $GLOBALS['azt_catalog_location'] = false;
    $check(!AZnet\Theme\header_catalog_home_rail(), 'unassigned menu does not change Hero');
    $GLOBALS['azt_catalog_location'] = true;

    $setup = (string) file_get_contents($root . '/inc/theme/setup.php');
    $admin = (string) file_get_contents($root . '/inc/admin/control-center.php');
    $header = (string) file_get_contents($root . '/template-parts/header/site-header.php');
    $mobile = (string) file_get_contents($root . '/template-parts/header/mobile-panel.php');
    $hero = (string) file_get_contents($root . '/template-parts/homepage/industrial-01/hero.php');
    $assets = (string) file_get_contents($root . '/inc/theme/assets.php');
    $css_path = $root . '/assets/css/components/catalog-navigation.css';
    $check(str_contains($setup, "'product-catalog'"), 'register WordPress product-catalog menu location');
    $check(str_contains($admin, "field_select( 'header_catalog_mode'"), 'Core Header settings expose the feature');
    $check(str_contains($header, 'catalog-navigation'), 'Header composes catalog disclosure');
    $check(str_contains($mobile, 'catalog_mobile_menu'), 'mobile panel retains catalog accessibility');
    $check(str_contains($hero, 'hero--with-catalog'), 'Industrial 01 Hero has menu-rail composition');
    $check(str_contains($hero, 'header_catalog_menu_html'), 'Hero reuses WordPress navigation menu');
    $check(str_contains($assets, 'enqueue_header_catalog_asset'), 'only active menu surfaces enqueue new asset');
    $check(is_file($css_path), 'catalog navigation stylesheet exists');
    $css = is_file($css_path) ? (string) file_get_contents($css_path) : '';
    $check(str_contains($css, '@media (max-width: 1100px)'), 'mobile must suppress desktop catalog rail');
    $check(str_contains($css, 'overflow-y: auto'), 'long WordPress menus must scroll without page overflow');
    $check(!str_contains($css, 'choiceguide_') && !str_contains($css, 'get_terms('), 'Theme presentation cannot duplicate catalog domain logic');
    echo "PASS: Core vertical catalog menu + Industrial 01 Hero contract\n";
}
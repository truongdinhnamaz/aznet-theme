<?php
/**
 * Y1 Page Experience ownership/settings contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);

define('ABSPATH', $root . '/');

require_once $root . '/inc/theme/settings.php';

$defaults = AZnet\Theme\settings_defaults();
assert(array_key_exists('page_breadcrumbs', $defaults));
assert(true === $defaults['page_breadcrumbs']);

$normalized = AZnet\Theme\normalize_settings(['page_breadcrumbs' => '0']);
assert(false === $normalized['page_breadcrumbs']);

$helper_path = $root . '/inc/theme/page-experience.php';
assert(is_file($helper_path), 'Y1 helper must exist');
require_once $helper_path;

assert(function_exists('AZnet\\Theme\\page_breadcrumbs_enabled'));
assert(function_exists('AZnet\\Theme\\page_variant'));
assert(function_exists('AZnet\\Theme\\page_breadcrumb_items'));

$page = file_get_contents($root . '/page.php');
assert(is_string($page));
foreach (['get_post_meta(', 'parse_url(', '$_SERVER['] as $forbidden) {
    assert(!str_contains($page, $forbidden));
}

$helper = file_get_contents($helper_path);
assert(is_string($helper));
foreach (['get_post_ancestors', 'get_permalink', 'get_the_title'] as $needle) {
    assert(str_contains($helper, $needle));
}
foreach (['get_post_meta(', 'get_option(', 'parse_url(', 'REQUEST_URI'] as $forbidden) {
    assert(!str_contains($helper, $forbidden));
}

$shared_path = $root . '/template-parts/content/page.php';
$wide_path = $root . '/page-templates/wide.php';
$landing_path = $root . '/page-templates/landing.php';
$css_path = $root . '/assets/css/components/page.css';
foreach ([$shared_path, $wide_path, $landing_path, $css_path] as $required) {
    assert(is_file($required), 'Y1 renderer/template/CSS file must exist: ' . $required);
}

$shared = file_get_contents($shared_path);
$wide = file_get_contents($wide_path);
$landing = file_get_contents($landing_path);
assert(is_string($shared) && is_string($wide) && is_string($landing));

foreach ([$page, $wide, $landing] as $wrapper) {
    assert(str_contains($wrapper, "get_template_part( 'template-parts/content/page' )"));
    assert(substr_count($wrapper, '<main id="main"') === 1);
}
assert(!str_contains($shared, '<main id="main"'));
assert(str_contains($shared, 'aznet-theme-page__breadcrumbs'));
assert(str_contains($shared, 'wp_link_pages'));
assert(!str_contains($shared, 'application/ld+json'));
assert(!str_contains($shared, 'schema.org'));

$assets = file_get_contents($root . '/inc/theme/assets.php');
assert(is_string($assets));
assert(str_contains($assets, 'function should_enqueue_page_assets(): bool'));
assert(str_contains($assets, "'aznet-theme-page'"));
assert(str_contains($assets, "'/assets/css/components/page.css'"));

$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');
assert(is_string($style) && preg_match('/^Version:\s*1\.2\.0\s*$/mi', $style) === 1);
assert(is_string($functions) && preg_match("/define\(\s*'AZNET_THEME_VERSION'\s*,\s*'1\.2\.0'\s*\)/", $functions) === 1);

echo "PASS: Y1 Page Experience ownership/settings/renderer contract\n";

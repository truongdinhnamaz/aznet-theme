<?php
/**
 * Y1 Page Experience ownership and settings contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);

if (! defined('ABSPATH')) {
    define('ABSPATH', $root . '/');
}

$GLOBALS['y1_theme_mod'] = [];

if (! function_exists('get_theme_mod')) {
    function get_theme_mod(string $name, mixed $default = false): mixed {
        if ('aznet_theme_settings' !== $name) {
            return $default;
        }

        return $GLOBALS['y1_theme_mod'] ?? $default;
    }
}

require_once $root . '/inc/theme/settings.php';

$defaults = \AZnet\Theme\settings_defaults();
if (! array_key_exists('page_breadcrumbs', $defaults)) {
    fwrite(STDERR, "FAIL: page_breadcrumbs default is missing\n");
    exit(1);
}
assert(true === $defaults['page_breadcrumbs']);

$normalized = \AZnet\Theme\normalize_settings(['page_breadcrumbs' => '0']);
assert(false === $normalized['page_breadcrumbs']);

$helper_file = $root . '/inc/theme/page-experience.php';
if (! file_exists($helper_file)) {
    fwrite(STDERR, "FAIL: inc/theme/page-experience.php does not exist\n");
    exit(1);
}
require_once $helper_file;

foreach ([
    'AZnet\\Theme\\page_breadcrumbs_enabled',
    'AZnet\\Theme\\page_variant',
    'AZnet\\Theme\\page_breadcrumb_items',
] as $function) {
    if (! function_exists($function)) {
        fwrite(STDERR, "FAIL: missing function {$function}\n");
        exit(1);
    }
}

$page = (string) file_get_contents($root . '/page.php');
foreach (['get_post_meta(', 'parse_url(', '$_SERVER['] as $forbidden) {
    assert(! str_contains($page, $forbidden));
}

$helper = (string) file_get_contents($helper_file);
foreach (['get_post_ancestors', 'get_permalink', 'get_the_title'] as $required) {
    assert(str_contains($helper, $required));
}
foreach (['get_post_meta(', 'get_option(', 'parse_url(', 'REQUEST_URI'] as $forbidden) {
    assert(! str_contains($helper, $forbidden));
}

$style = (string) file_get_contents($root . '/style.css');
$functions = (string) file_get_contents($root . '/functions.php');
assert(1 === preg_match('/^Version:\s*1\.2\.0\s*$/m', $style));
assert(str_contains($functions, "define( 'AZNET_THEME_VERSION', '1.2.0' );"));

echo "PASS: Y1 Page Experience ownership and settings contract\n";

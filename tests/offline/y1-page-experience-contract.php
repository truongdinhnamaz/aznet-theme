<?php
/**
 * Y1 Page Experience ownership and presentation contract.
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

$renderer_file = $root . '/template-parts/content/page.php';
$wide_file = $root . '/page-templates/wide.php';
$landing_file = $root . '/page-templates/landing.php';
$page_css_file = $root . '/assets/css/components/page.css';
foreach ([$renderer_file, $wide_file, $landing_file, $page_css_file] as $required_file) {
    if (! is_file($required_file)) {
        fwrite(STDERR, 'FAIL: missing Y1 renderer asset ' . str_replace($root . '/', '', $required_file) . "\n");
        exit(1);
    }
}

foreach ([$root . '/page.php', $wide_file, $landing_file] as $wrapper_file) {
    $wrapper = (string) file_get_contents($wrapper_file);
    if (! str_contains($wrapper, "get_template_part( 'template-parts/content/page' );")) {
        fwrite(STDERR, 'FAIL: Y1 wrapper must use shared Page template part: ' . basename($wrapper_file) . "\n");
        exit(1);
    }
    if (1 !== substr_count($wrapper, '<main id="main" class="aznet-theme-main">')) {
        fwrite(STDERR, 'FAIL: Y1 wrapper must own exactly one Theme main: ' . basename($wrapper_file) . "\n");
        exit(1);
    }
}

$renderer = (string) file_get_contents($renderer_file);
if (str_contains($renderer, '<main')) {
    fwrite(STDERR, "FAIL: shared Page renderer must not create a second main landmark\n");
    exit(1);
}
foreach (['page_variant(', 'page_breadcrumb_items(', "get_post_field( 'post_excerpt'", 'has_post_thumbnail()', 'the_post_thumbnail(', 'the_content()', 'wp_link_pages()'] as $required) {
    if (! str_contains($renderer, $required)) {
        fwrite(STDERR, "FAIL: shared Page renderer missing {$required}\n");
        exit(1);
    }
}
if (str_contains($renderer, 'get_the_excerpt(')) {
    fwrite(STDERR, "FAIL: Y1 must render only explicitly-authored Page excerpt, not generated excerpt\n");
    exit(1);
}
foreach (['application/ld+json', 'BreadcrumbList', 'schema.org'] as $forbidden) {
    assert(! str_contains($renderer, $forbidden));
}

$assets = (string) file_get_contents($root . '/inc/theme/assets.php');
foreach (['function should_enqueue_page_assets(): bool', 'function enqueue_page_assets( ?string $version = null ): void', "'/assets/css/components/page.css'", 'enqueue_page_assets( $version );'] as $required) {
    if (! str_contains($assets, $required)) {
        fwrite(STDERR, "FAIL: Y1 Page asset boundary missing {$required}\n");
        exit(1);
    }
}
foreach (['REQUEST_URI', 'parse_url(', 'post_name', "is_page( '"] as $forbidden) {
    assert(! str_contains($assets, $forbidden));
}

$design = (string) file_get_contents($root . '/inc/theme/design-system.php');
if (! str_contains($design, "'assets/css/components/page.css'")) {
    fwrite(STDERR, "FAIL: page.css must use the existing editor_stylesheets path\n");
    exit(1);
}

$css = (string) file_get_contents($page_css_file);
foreach (['.aznet-theme-page--standard', '.aznet-theme-page--wide', '.aznet-theme-page--landing', '.aznet-theme-page__breadcrumbs', '.aznet-theme-page__featured img'] as $selector) {
    if (! str_contains($css, $selector)) {
        fwrite(STDERR, "FAIL: page.css missing {$selector}\n");
        exit(1);
    }
}

$style = (string) file_get_contents($root . '/style.css');
$functions = (string) file_get_contents($root . '/functions.php');
assert(1 === preg_match('/^Version:\s*1\.2\.0\s*$/m', $style));
assert(str_contains($functions, "define( 'AZNET_THEME_VERSION', '1.2.0' );"));

echo "PASS: Y1 Page Experience ownership and presentation contract\n";

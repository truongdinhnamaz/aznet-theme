<?php
/**
 * R1 visual preset presentation contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (! defined('AZNET_THEME_VERSION')) {
    define('AZNET_THEME_VERSION', '1.0.0');
}

$GLOBALS['r1_theme_mod'] = [
    'schema_version' => 1,
    'visual_preset'  => 'editorial',
];
$GLOBALS['r1_enqueued_styles'] = [];

function get_theme_mod(string $name, mixed $default = false): mixed {
    return 'aznet_theme_settings' === $name ? ($GLOBALS['r1_theme_mod'] ?? $default) : $default;
}

function wp_enqueue_style(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, string $media = 'all'): void {
    $GLOBALS['r1_enqueued_styles'][$handle] = [
        'src'  => $src,
        'deps' => $deps,
        'ver'  => $ver,
    ];
}

function get_theme_file_uri(string $path = ''): string {
    return 'https://example.test/wp-content/themes/aznet-theme' . $path;
}

function get_stylesheet_uri(): string {
    return 'https://example.test/wp-content/themes/aznet-theme/style.css';
}

function should_enqueue_generic_content_assets(): bool { return false; }
function should_enqueue_woocommerce_product_assets(): bool { return false; }
function should_enqueue_woocommerce_archive_assets(): bool { return false; }
function should_enqueue_woocommerce_cart_assets(): bool { return false; }
function should_enqueue_woocommerce_checkout_assets(): bool { return false; }
function should_enqueue_woocommerce_account_assets(): bool { return false; }

$root = dirname(__DIR__, 2);
$design_system = $root . '/inc/theme/design-system.php';

if (! file_exists($design_system)) {
    fwrite(STDERR, "FAIL: inc/theme/design-system.php does not exist\n");
    exit(1);
}

require_once $root . '/inc/theme/settings.php';
require_once $design_system;
require_once $root . '/inc/theme/assets.php';

assert('editorial' === \AZnet\Theme\visual_preset());
assert(in_array('aznet-theme-preset--editorial', \AZnet\Theme\visual_preset_body_classes([]), true));
assert([
    'assets/css/tokens.css',
    'assets/css/presets/editorial.css',
] === \AZnet\Theme\editor_stylesheets());

\AZnet\Theme\enqueue_assets();
assert(isset($GLOBALS['r1_enqueued_styles']['aznet-theme-preset-editorial']));
assert(! isset($GLOBALS['r1_enqueued_styles']['aznet-theme-preset-commerce']));
assert(str_ends_with($GLOBALS['r1_enqueued_styles']['aznet-theme-preset-editorial']['src'], '/assets/css/presets/editorial.css'));
assert(in_array('aznet-theme-tokens', $GLOBALS['r1_enqueued_styles']['aznet-theme-preset-editorial']['deps'], true));

$GLOBALS['r1_theme_mod']['visual_preset'] = 'default';
$GLOBALS['r1_enqueued_styles'] = [];
assert(['assets/css/tokens.css'] === \AZnet\Theme\editor_stylesheets());
\AZnet\Theme\enqueue_assets();
foreach (array_keys($GLOBALS['r1_enqueued_styles']) as $handle) {
    assert(! str_starts_with($handle, 'aznet-theme-preset-'));
}

$theme_json = json_decode((string) file_get_contents($root . '/theme.json'), true, 512, JSON_THROW_ON_ERROR);
assert(false === $theme_json['settings']['appearanceTools']);
assert(['primary', 'surface', 'text', 'muted', 'success', 'warning', 'danger'] === array_column($theme_json['settings']['color']['palette'], 'slug'));
assert(['sm', 'base', 'lg', 'xl'] === array_column($theme_json['settings']['typography']['fontSizes'], 'slug'));
assert(['1', '2', '3', '4', '6', 'section'] === array_column($theme_json['settings']['spacing']['spacingSizes'], 'slug'));

$bootstrap = (string) file_get_contents($root . '/inc/theme/bootstrap.php');
assert(str_contains($bootstrap, "require_once __DIR__ . '/design-system.php';"));
assert(str_contains($bootstrap, "add_filter( 'body_class', __NAMESPACE__ . '\\\\visual_preset_body_classes' );"));
assert(! str_contains($bootstrap, 'enqueue_block_editor_assets'));

$setup = (string) file_get_contents($root . '/inc/theme/setup.php');
assert(str_contains($setup, "add_theme_support( 'editor-styles' );"));
assert(str_contains($setup, 'add_editor_style( editor_stylesheets() );'));

echo "PASS: R1 visual preset presentation contract\n";

<?php
/**
 * Y5 integrated client-delivery promoted release contract.
 *
 * @package AZnetTheme
 */

declare( strict_types=1 );

$root = dirname( __DIR__, 2 );

$must = static function ( bool $condition, string $message ): void {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
};

$required_retained = [
    'tests/offline/y1-page-experience-contract.php',
    'tests/runtime/y1-page-experience.php',
    'tests/browser/y1-page-experience-l4.mjs',
    '.github/workflows/y1-page-experience.yml',
    'tests/offline/y2-professional-page-kits-contract.php',
    'tests/browser/y2-professional-page-kits-l4.mjs',
    '.github/workflows/y2-professional-page-kits.yml',
    'tests/offline/y3-footer-system-contract.php',
    'tests/runtime/y3-footer-system.php',
    'tests/browser/y3-footer-system-l4.mjs',
    '.github/workflows/y3-footer-system.yml',
    'tests/offline/y4-professional-services-provisioning-contract.php',
    'tests/runtime/y4-professional-services-provisioning.php',
    'tests/browser/y4-professional-services-provisioning-l4.mjs',
    '.github/workflows/y4-professional-services-provisioning.yml',
];

foreach ( $required_retained as $relative ) {
    $must( is_file( $root . '/' . $relative ), 'Y5 requires retained Y1-Y4 artifact: ' . $relative );
}

$required_y5 = [
    'tests/runtime/y5-client-delivery.php',
    'tests/browser/y5-client-delivery-l4.mjs',
    '.github/workflows/y5-client-delivery-release.yml',
    'scripts/build-release-package.py',
];

foreach ( $required_y5 as $relative ) {
    $must( is_file( $root . '/' . $relative ), 'Y5 integrated fixture missing: ' . $relative );
}

$style = (string) file_get_contents( $root . '/style.css' );
$functions = (string) file_get_contents( $root . '/functions.php' );
$style_match = [];
$function_match = [];
$must( 1 === preg_match( '/^Version:\s*([0-9]+\\.[0-9]+\\.[0-9]+)\s*$/m', $style, $style_match ), 'Current Theme style.css version must be readable' );
$must( 1 === preg_match( "/define\\( 'AZNET_THEME_VERSION', '([0-9]+\\.[0-9]+\\.[0-9]+)' \\);/", $functions, $function_match ), 'Current AZNET_THEME_VERSION must be readable' );
$current_version = (string) ( $style_match[1] ?? '' );
$must( $current_version === (string) ( $function_match[1] ?? '' ), 'Current Theme version markers must remain synchronized' );
$must( version_compare( $current_version, '1.3.32', '>=' ), 'Current Theme must not regress below the historical Y5 promoted release 1.3.32' );

$industrial_required = [
    'inc/theme/preset-lexicon.php',
    'inc/theme/templates/industrial-01/manifest.php',
    'assets/css/presets/industrial-01.css',
    'assets/css/components/homepage-industrial-01.css',
    'template-parts/homepage/industrial-01/hero.php',
    'template-parts/homepage/industrial-01/categories.php',
    'template-parts/homepage/industrial-01/products.php',
    'template-parts/homepage/industrial-01/cta.php',
    'tests/offline/industrial01-registration-contract.php',
    'tests/browser/industrial01-pilot-l4.mjs',
];

foreach ( $industrial_required as $relative ) {
    $must( is_file( $root . '/' . $relative ), 'Y5 convergence package requires Industrial 01 retained artifact: ' . $relative );
}

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', $root . '/' );
}
require_once $root . '/inc/theme/settings.php';
$industrial_composer = (string) file_get_contents( $root . '/inc/theme/homepage-composer.php' );
$industrial_woo = (string) file_get_contents( $root . '/inc/integrations/woocommerce.php' );

$must( in_array( 'industrial-01', AZnet\Theme\homepage_preset_ids(), true ), 'Y5 convergence package must retain Industrial 01 Homepage registration through the Template Registry' );
$must( in_array( 'industrial-01', AZnet\Theme\visual_preset_ids(), true ), 'Y5 convergence package must retain Industrial 01 visual registration through the Template Registry' );
$must( str_contains( $industrial_composer, 'render_industrial01_part' ), 'Y5 convergence package must retain Industrial 01 Homepage composition' );
foreach ( [
    'homepage_product_category_showcase_terms',
    'homepage_products',
    'shop_url',
] as $adapter ) {
    $must( str_contains( $industrial_woo, $adapter ), 'Y5 convergence package must retain public Woo adapter: ' . $adapter );
}

$runtime = (string) file_get_contents( $root . '/tests/runtime/y5-client-delivery.php' );
$must( str_contains( $runtime, "wp_get_theme()->get( 'Version' ) === AZNET_THEME_VERSION" ), 'Y5 runtime fixture must verify current Theme version synchronization' );

$workflow = (string) file_get_contents( $root . '/.github/workflows/y5-client-delivery-release.yml' );
foreach ( [
    'WordPress 6.9',
    'PHP 8.1',
    'zero active third-party plugins',
    'build-release-package.py',
    'THEME_VERSION',
    'aznet-theme-$THEME_VERSION.zip',
    'twentytwentyfive',
    'y5-client-delivery.php',
    'y5-client-delivery-l4.mjs',
] as $needle ) {
    $must( str_contains( $workflow, $needle ), 'Y5 workflow missing required promoted-release marker: ' . $needle );
}

$production_files = [];
foreach ( glob( $root . '/*.php' ) ?: [] as $file ) {
    $production_files[] = $file;
}
foreach ( [ 'inc', 'template-parts', 'patterns', 'page-templates' ] as $directory ) {
    $base = $root . '/' . $directory;
    if ( ! is_dir( $base ) ) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $base, FilesystemIterator::SKIP_DOTS ) );
    foreach ( $iterator as $file ) {
        if ( $file instanceof SplFileInfo && 'php' === strtolower( $file->getExtension() ) ) {
            $production_files[] = $file->getPathname();
        }
    }
}

$forbidden_private_or_mandatory = [
    '$wpdb->',
    'wp_options',
    "get_option( 'rootprofile_",
    "get_option('rootprofile_",
    "get_option( 'convertflow_",
    "get_option('convertflow_",
    "get_option( 'choiceguide_",
    "get_option('choiceguide_",
    'is_plugin_active(',
    'activate_plugin(',
    'deactivate_plugins(',
    "wp-admin/includes/plugin.php",
];

foreach ( array_unique( $production_files ) as $file ) {
    $contents = (string) file_get_contents( $file );
    foreach ( $forbidden_private_or_mandatory as $needle ) {
        $must( ! str_contains( $contents, $needle ), 'Forbidden private-storage/mandatory-plugin marker in ' . substr( $file, strlen( $root ) + 1 ) . ': ' . $needle );
    }
}

$core = (string) file_get_contents( $root . '/scripts/verify-v1-core.sh' );
foreach ( [
    'y1-page-experience-contract.php',
    'y2-professional-page-kits-contract.php',
    'y3-footer-system-contract.php',
    'y4-professional-services-provisioning-contract.php',
    'y5-client-delivery-release-contract.php',
] as $needle ) {
    $must( str_contains( $core, $needle ), 'Reusable Core verifier missing retained v1.3 contract: ' . $needle );
}

echo "PASS: Y5 integrated client-delivery promoted release contract\n";

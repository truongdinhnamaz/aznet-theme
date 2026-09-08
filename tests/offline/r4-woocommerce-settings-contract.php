<?php
/**
 * R4 WooCommerce presentation settings and projection contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

$root = dirname(__DIR__, 2);
$settings_file = $root . '/inc/theme/settings.php';
$presentation_file = $root . '/inc/theme/woocommerce-presentation.php';
$bootstrap_file = $root . '/inc/theme/bootstrap.php';

if (! is_file($presentation_file)) {
    fwrite(STDERR, "FAIL: R4 Woo presentation module missing inc/theme/woocommerce-presentation.php\n");
    exit(1);
}

require_once $settings_file;

$defaults = AZnet\Theme\settings_defaults();
$expected_defaults = [
    'woo_catalog_preset'       => 'grid',
    'woo_product_card_density' => 'balanced',
    'woo_product_preset'       => 'classic',
];

foreach ($expected_defaults as $key => $expected) {
    if (! array_key_exists($key, $defaults)) {
        fwrite(STDERR, "FAIL: missing R4 setting default {$key}\n");
        exit(1);
    }
    if ($defaults[$key] !== $expected) {
        fwrite(STDERR, "FAIL: R4 default {$key} mismatch\n");
        exit(1);
    }
}

$normalized = AZnet\Theme\normalize_settings([
    'visual_preset'            => 'commerce',
    'header_preset'            => 'commerce',
    'header_sticky'            => 'sticky',
    'header_search'            => true,
    'header_utilities'         => true,
    'woo_catalog_preset'       => 'compact-grid',
    'woo_product_card_density' => 'compact',
    'woo_product_preset'       => 'story',
    'unknown_woo_key'          => 'must-drop',
]);

foreach ([
    'woo_catalog_preset'       => 'compact-grid',
    'woo_product_card_density' => 'compact',
    'woo_product_preset'       => 'story',
] as $key => $expected) {
    if (($normalized[$key] ?? null) !== $expected) {
        fwrite(STDERR, "FAIL: valid R4 setting {$key} was not normalized\n");
        exit(1);
    }
}
if (array_key_exists('unknown_woo_key', $normalized)) {
    fwrite(STDERR, "FAIL: unknown Woo key escaped the Theme settings allow-list\n");
    exit(1);
}
if (($normalized['schema_version'] ?? null) !== 1) {
    fwrite(STDERR, "FAIL: additive R4 settings must retain schema_version 1\n");
    exit(1);
}

$invalid = AZnet\Theme\normalize_settings([
    'woo_catalog_preset'       => 'masonry-builder',
    'woo_product_card_density' => 'ultra-dense',
    'woo_product_preset'       => 'custom-builder',
]);
foreach ($expected_defaults as $key => $expected) {
    if (($invalid[$key] ?? null) !== $expected) {
        fwrite(STDERR, "FAIL: invalid {$key} did not fall back to {$expected}\n");
        exit(1);
    }
}

$presentation = file_get_contents($presentation_file);
$bootstrap = file_get_contents($bootstrap_file);
if (false === $presentation || false === $bootstrap) {
    fwrite(STDERR, "FAIL: unable to inspect R4 presentation/bootstrap files\n");
    exit(1);
}

foreach ([
    'function woo_catalog_preset(): string',
    'function woo_product_card_density(): string',
    'function woo_product_preset(): string',
    'function woocommerce_presentation_body_classes( array $classes ): array',
    'aznet-theme-woo-catalog--',
    'aznet-theme-woo-card--',
    'aznet-theme-woo-product--',
] as $needle) {
    if (! str_contains($presentation, $needle)) {
        fwrite(STDERR, "FAIL: R4 Woo presentation module missing {$needle}\n");
        exit(1);
    }
}

if (! str_contains($bootstrap, "require_once __DIR__ . '/woocommerce-presentation.php';")) {
    fwrite(STDERR, "FAIL: bootstrap does not load R4 Woo presentation module\n");
    exit(1);
}
if (! str_contains($bootstrap, "add_filter( 'body_class', __NAMESPACE__ . '\\\\woocommerce_presentation_body_classes' );")) {
    fwrite(STDERR, "FAIL: R4 Woo presentation classes are not wired through body_class\n");
    exit(1);
}

foreach ([
    'get_post_meta(',
    'get_option(',
    '$wpdb',
    'WC()->',
    'wc_get_orders(',
    '_woocommerce_',
    'choiceguide_',
] as $needle) {
    if (str_contains($presentation, $needle)) {
        fwrite(STDERR, "FAIL: forbidden commerce/storage token {$needle} in R4 presentation module\n");
        exit(1);
    }
}

if (is_dir($root . '/woocommerce')) {
    fwrite(STDERR, "FAIL: R4 must not introduce a woocommerce/ template override directory\n");
    exit(1);
}

echo "PASS: R4 WooCommerce presentation settings contract\n";

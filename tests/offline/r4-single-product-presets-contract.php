<?php
/**
 * R4 Single Product Classic/Focus/Story presentation contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css_file = $root . '/assets/css/components/woocommerce-product.css';
$product_file = $root . '/inc/theme/woocommerce-product.php';
$presentation_file = $root . '/inc/theme/woocommerce-presentation.php';

foreach ([$css_file, $product_file, $presentation_file] as $file) {
    if (! is_file($file)) {
        fwrite(STDERR, "FAIL: missing R4 single-product dependency " . basename($file) . "\n");
        exit(1);
    }
}

$css = file_get_contents($css_file);
$product = file_get_contents($product_file);
$presentation = file_get_contents($presentation_file);
if (false === $css || false === $product || false === $presentation) {
    fwrite(STDERR, "FAIL: unable to inspect R4 single-product files\n");
    exit(1);
}

foreach ([
    '.aznet-theme-woo-product--classic',
    '.aznet-theme-woo-product--focus',
    '.aznet-theme-woo-product--story',
    '.woocommerce-product-gallery',
    '.summary',
    '.product_title',
    '.woocommerce-product-rating',
    '.woocommerce-product-details__short-description',
    '.variations',
    '.single_variation_wrap',
    '.product_meta',
] as $selector) {
    if (! str_contains($css, $selector)) {
        fwrite(STDERR, "FAIL: R4 single-product CSS missing {$selector}\n");
        exit(1);
    }
}

$focus_pattern = '/\.aznet-theme-woo-product--focus[^\{]*\.summary\s*\{[^\}]*position\s*:\s*sticky[^\}]*top\s*:/is';
if (1 !== preg_match($focus_pattern, $css)) {
    fwrite(STDERR, "FAIL: Focus preset lacks desktop sticky native summary\n");
    exit(1);
}

$mobile_start = strpos($css, '@media (max-width: 767px)');
if (false === $mobile_start) {
    fwrite(STDERR, "FAIL: R4 single-product CSS lacks mobile fallback media query\n");
    exit(1);
}
$mobile_css = substr($css, $mobile_start);
if (! str_contains($mobile_css, '.aznet-theme-woo-product--focus') || ! str_contains($mobile_css, 'position: static')) {
    fwrite(STDERR, "FAIL: Focus sticky summary does not fall back to normal mobile flow\n");
    exit(1);
}

foreach ([$product, $presentation] as $php) {
    foreach ([
        'get_post_meta(',
        'get_option(',
        '$wpdb',
        'WC()->',
        'remove_action( \'woocommerce_',
        'add_action( \'woocommerce_',
        'remove_action("woocommerce_',
        'add_action("woocommerce_',
    ] as $needle) {
        if (str_contains($php, $needle)) {
            fwrite(STDERR, "FAIL: R4 single-product takes commerce/layout ownership via {$needle}\n");
            exit(1);
        }
    }
}

foreach (['sticky_add_to_cart', 'single_add_to_cart_button(', 'variation_id'] as $needle) {
    if (str_contains($product, $needle)) {
        fwrite(STDERR, "FAIL: R4 single-product duplicates Woo add-to-cart state via {$needle}\n");
        exit(1);
    }
}

if (is_dir($root . '/woocommerce')) {
    fwrite(STDERR, "FAIL: R4 single-product must not create Woo template overrides\n");
    exit(1);
}

echo "PASS: R4 Single Product Classic/Focus/Story contract\n";

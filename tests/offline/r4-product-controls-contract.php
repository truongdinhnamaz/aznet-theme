<?php
/**
 * R4 native product gallery and purchase-control presentation contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css_file = $root . '/assets/css/components/woocommerce-product.css';

if (! is_file($css_file)) {
    fwrite(STDERR, "FAIL: missing Woo product CSS\n");
    exit(1);
}

$css = file_get_contents($css_file);
if (false === $css) {
    fwrite(STDERR, "FAIL: unable to read Woo product CSS\n");
    exit(1);
}

foreach ([
    '.woocommerce-product-gallery__wrapper',
    '.woocommerce-product-gallery__image',
    '.flex-control-thumbs',
    '.variations select',
    '.quantity .qty',
    '.single_add_to_cart_button',
    '.reset_variations',
    '.single_variation_wrap',
    ':focus-visible',
] as $selector) {
    if (! str_contains($css, $selector)) {
        fwrite(STDERR, "FAIL: R4 native product-control CSS missing {$selector}\n");
        exit(1);
    }
}

foreach ([
    '--aznet-theme-woo-gallery-gap',
    '--aznet-theme-woo-gallery-thumb-size',
    '--aznet-theme-woo-purchase-control-height',
] as $token) {
    if (! str_contains($css, $token)) {
        fwrite(STDERR, "FAIL: R4 product controls missing component token {$token}\n");
        exit(1);
    }
}

$mobile_start = strpos($css, '@media (max-width: 767px)');
if (false === $mobile_start) {
    fwrite(STDERR, "FAIL: R4 product controls lack mobile fallback\n");
    exit(1);
}
$mobile_css = substr($css, $mobile_start);
foreach (['.woocommerce-product-gallery__wrapper', 'display: block'] as $needle) {
    if (! str_contains($mobile_css, $needle)) {
        fwrite(STDERR, "FAIL: native gallery does not retain a normal mobile flow marker {$needle}\n");
        exit(1);
    }
}

foreach (glob($root . '/assets/js/*') ?: [] as $file) {
    $name = strtolower(basename($file));
    if (str_contains($name, 'variation') || str_contains($name, 'gallery')) {
        fwrite(STDERR, "FAIL: R4 must not add a Theme variation/gallery engine: {$name}\n");
        exit(1);
    }
}

$production_files = [
    $root . '/inc/theme/woocommerce-product.php',
    $root . '/inc/theme/woocommerce-presentation.php',
];
foreach ($production_files as $file) {
    $content = file_get_contents($file);
    if (false === $content) {
        fwrite(STDERR, "FAIL: unable to inspect " . basename($file) . "\n");
        exit(1);
    }
    foreach ([
        'VariationForm',
        'wc_add_to_cart_variation_params',
        'variation_id',
        'get_post_meta(',
        'WC()->',
        'attribute_pa_',
        'swatch',
    ] as $needle) {
        if (stripos($content, $needle) !== false) {
            fwrite(STDERR, "FAIL: R4 duplicates Woo gallery/variation/domain behavior via {$needle}\n");
            exit(1);
        }
    }
}

if (is_dir($root . '/woocommerce')) {
    fwrite(STDERR, "FAIL: R4 product controls must not add Woo template overrides\n");
    exit(1);
}

echo "PASS: R4 native product gallery and purchase controls contract\n";

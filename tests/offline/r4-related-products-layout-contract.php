<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css_file = $root . '/assets/css/components/woocommerce-product.css';
$product_file = $root . '/inc/theme/woocommerce-product.php';

if (!is_file($css_file) || !is_file($product_file)) {
    fwrite(STDERR, "missing Woo product presentation source\n");
    exit(1);
}

$css = file_get_contents($css_file);
$product_php = file_get_contents($product_file);

$required_css = [
    '.single-product #main :is(.related, .upsells) ul.products',
    'display: grid',
    'grid-template-columns: repeat(auto-fill, minmax(min(15rem, 100%), 1fr))',
    '.single-product #main :is(.related, .upsells) ul.products li.product',
    'float: none',
    'width: auto',
    '.single-product #main :is(.related, .upsells) ul.products li.product a img',
    '@media (max-width: 767px)',
];

foreach ($required_css as $needle) {
    if (strpos($css, $needle) === false) {
        fwrite(STDERR, "missing related-products presentation contract: {$needle}\n");
        exit(2);
    }
}

$forbidden_php = [
    'woocommerce_output_related_products_args',
    'woocommerce_related_products',
    'woocommerce_related_products_args',
    'woocommerce_after_single_product_summary',
];

foreach ($forbidden_php as $needle) {
    if (strpos($product_php, $needle) !== false) {
        fwrite(STDERR, "Theme must not take over Woo related-product selection/count/order: {$needle}\n");
        exit(3);
    }
}

if (strpos($css, '.single-product #main :is(.related, .upsells) ul.products li.product .button') !== false) {
    fwrite(STDERR, "Theme must not invent a related-product CTA/button treatment\n");
    exit(4);
}

echo "PASS: R4 related products presentation-only layout contract\n";

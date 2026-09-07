<?php
/**
 * R4 Product Card System and catalog preset contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css_file = $root . '/assets/css/components/woocommerce-archive.css';
$archive_file = $root . '/inc/theme/woocommerce-archive.php';
$presentation_file = $root . '/inc/theme/woocommerce-presentation.php';

foreach ([$css_file, $archive_file, $presentation_file] as $file) {
    if (! is_file($file)) {
        fwrite(STDERR, "FAIL: missing R4 catalog dependency " . basename($file) . "\n");
        exit(1);
    }
}

$css = file_get_contents($css_file);
$archive = file_get_contents($archive_file);
$presentation = file_get_contents($presentation_file);
if (false === $css || false === $archive || false === $presentation) {
    fwrite(STDERR, "FAIL: unable to read R4 Product Card/Catalog files\n");
    exit(1);
}

$required_selectors = [
    '.aznet-theme-woo-catalog--grid',
    '.aznet-theme-woo-catalog--compact-grid',
    '.aznet-theme-woo-catalog--editorial',
    '.aznet-theme-woo-card--comfortable',
    '.aznet-theme-woo-card--balanced',
    '.aznet-theme-woo-card--compact',
    '.woocommerce-loop-product__link',
    '.woocommerce-loop-product__title',
    '.price',
    '.star-rating',
    '.button',
    ':focus-visible',
];

foreach ($required_selectors as $selector) {
    if (! str_contains($css, $selector)) {
        fwrite(STDERR, "FAIL: R4 catalog CSS missing selector {$selector}\n");
        exit(1);
    }
}

foreach ([
    '--aznet-theme-woo-card-gap',
    '--aznet-theme-woo-card-padding',
    '--aznet-theme-woo-card-media-ratio',
    '--aznet-theme-woo-catalog-columns',
] as $token) {
    if (! str_contains($css, $token)) {
        fwrite(STDERR, "FAIL: R4 Product Card system missing component token {$token}\n");
        exit(1);
    }
}

if (! str_contains($presentation, "'archive' === $surface")) {
    fwrite(STDERR, "FAIL: R4 archive presentation classes are not surface-scoped\n");
    exit(1);
}

$forbidden_php = [
    'WP_Query',
    'pre_get_posts',
    'woocommerce_product_query',
    'get_post_meta(',
    'get_option(',
    '$wpdb',
    'WC()->',
];
foreach ([$archive, $presentation] as $php) {
    foreach ($forbidden_php as $needle) {
        if (str_contains($php, $needle)) {
            fwrite(STDERR, "FAIL: R4 catalog takes commerce/query ownership via {$needle}\n");
            exit(1);
        }
    }
}

foreach (['sale', 'stock', 'price'] as $word) {
    if (preg_match('/content\s*:\s*["\'][^"\']*' . preg_quote($word, '/') . '/i', $css)) {
        fwrite(STDERR, "FAIL: R4 CSS fabricates Woo {$word} truth with generated content\n");
        exit(1);
    }
}

if (is_dir($root . '/woocommerce')) {
    fwrite(STDERR, "FAIL: R4 catalog must not introduce Woo template overrides\n");
    exit(1);
}

echo "PASS: R4 Product Card System and catalog preset contract\n";

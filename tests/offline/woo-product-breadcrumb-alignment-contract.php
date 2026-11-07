<?php
/**
 * WooCommerce Single Product breadcrumb must use the product container geometry.
 *
 * Woo owns the breadcrumb output; the Theme owns only its presentation.
 * The breadcrumb and product are direct siblings of #main in the Woo template.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$path = $argv[1] ?? dirname(__DIR__, 2) . '/assets/css/components/woocommerce-product.css';
$css  = is_file($path) ? file_get_contents($path) : false;

if (! is_string($css)) {
    fwrite(STDERR, "FAIL: missing Woo Single Product CSS\n");
    exit(1);
}

$rule = static function (string $selector) use ($css): ?string {
    $pattern = '/(?:^|\})\s*' . preg_quote($selector, '/') . '\s*\{([^{}]*)\}/m';
    return preg_match($pattern, $css, $match) === 1 ? $match[1] : null;
};

$declaration = static function (string $block, string $name): ?string {
    $pattern = '/(?:^|;)\s*' . preg_quote($name, '/') . '\s*:\s*([^;]+);/m';
    return preg_match($pattern, $block, $match) === 1
        ? preg_replace('/\s+/', '', trim($match[1]))
        : null;
};

$product    = $rule('.single-product #main .product');
$breadcrumb = $rule('.single-product #main > .woocommerce-breadcrumb');

if (null === $product || null === $breadcrumb) {
    fwrite(STDERR, "FAIL: missing product or direct-sibling breadcrumb container rule\n");
    exit(1);
}

foreach (['width', 'margin-inline'] as $property) {
    $expected = $declaration($product, $property);
    $actual   = $declaration($breadcrumb, $property);
    if (null === $expected || $actual !== $expected) {
        fwrite(STDERR, "FAIL: breadcrumb {$property} differs from product container\n");
        exit(1);
    }
}

if (false === strpos($breadcrumb, '--aznet-theme-container-shell')) {
    fwrite(STDERR, "FAIL: breadcrumb does not use Theme shell token\n");
    exit(1);
}

echo "PASS: Woo Single Product breadcrumb shares product container geometry\n";

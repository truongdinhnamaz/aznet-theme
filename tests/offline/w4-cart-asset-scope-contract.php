<?php
namespace {
    define('ABSPATH', __DIR__);
    define('AZNET_THEME_VERSION', 'test');
    $GLOBALS['aznet_test_styles'] = [];
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = null) { $GLOBALS['aznet_test_styles'][] = $handle; }
    function get_theme_file_uri($path) { return 'https://example.test/theme' . $path; }
    function get_stylesheet_uri() { return 'https://example.test/theme/style.css'; }
}
namespace AZnet\Theme {
    function should_enqueue_generic_content_assets(): bool { return false; }
    function should_enqueue_woocommerce_product_assets(): bool { return false; }
    function should_enqueue_woocommerce_archive_assets(): bool { return false; }
}
namespace AZnet\Theme\Integrations\WooCommerce {
    function current_surface(): ?string { return $GLOBALS['aznet_test_woo_surface'] ?? null; }
}
namespace {
    $root = dirname(__DIR__, 2);
    if (!is_file($root . '/inc/theme/woocommerce-cart.php')) { fwrite(STDERR, "missing cart helper\n"); exit(1); }
    require $root . '/inc/theme/woocommerce-cart.php';
    require $root . '/inc/theme/assets.php';

    $cases = ['cart' => true, 'product' => false, 'archive' => false, 'checkout' => false, 'account' => false, '' => false];
    foreach ($cases as $surface => $expected) {
        $GLOBALS['aznet_test_woo_surface'] = '' === $surface ? null : $surface;
        $GLOBALS['aznet_test_styles'] = [];
        if (\AZnet\Theme\should_enqueue_woocommerce_cart_assets() !== $expected) { fwrite(STDERR, "eligibility mismatch: {$surface}\n"); exit(2); }
        \AZnet\Theme\enqueue_assets();
        $loaded = in_array('aznet-theme-woocommerce-cart', $GLOBALS['aznet_test_styles'], true);
        if ($loaded !== $expected) { fwrite(STDERR, "enqueue mismatch: {$surface}\n"); exit(3); }
    }
    echo "PASS: W4 cart-only asset scope\n";
}

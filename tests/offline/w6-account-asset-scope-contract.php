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
    function should_enqueue_woocommerce_cart_assets(): bool { return false; }
    function should_enqueue_woocommerce_checkout_assets(): bool { return false; }
}
namespace AZnet\Theme\Integrations\WooCommerce {
    function current_surface(): ?string { return $GLOBALS['aznet_test_woo_surface'] ?? null; }
}
namespace {
    $root = dirname(__DIR__, 2);
    if (!is_file($root . '/inc/theme/woocommerce-account.php')) { fwrite(STDERR, "missing account helper\n"); exit(1); }
    require $root . '/inc/theme/woocommerce-account.php';
    require $root . '/inc/theme/assets.php';

    $cases = ['account' => true, 'checkout' => false, 'cart' => false, 'product' => false, 'archive' => false, '' => false];
    foreach ($cases as $surface => $expected) {
        $GLOBALS['aznet_test_woo_surface'] = '' === $surface ? null : $surface;
        $GLOBALS['aznet_test_styles'] = [];
        if (\AZnet\Theme\should_enqueue_woocommerce_account_assets() !== $expected) { fwrite(STDERR, "eligibility mismatch: {$surface}\n"); exit(2); }
        \AZnet\Theme\enqueue_assets();
        $loaded = in_array('aznet-theme-woocommerce-account', $GLOBALS['aznet_test_styles'], true);
        if ($loaded !== $expected) { fwrite(STDERR, "enqueue mismatch: {$surface}\n"); exit(3); }
    }
    echo "PASS: W6 account-only asset scope\n";
}

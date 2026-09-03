<?php
namespace {
    define( 'ABSPATH', __DIR__ );
    $GLOBALS['aznet_test_styles'] = [];
    function wp_enqueue_style( $handle, $src = '', $deps = [], $ver = null ) { $GLOBALS['aznet_test_styles'][] = $handle; }
    function get_theme_file_uri( $path ) { return 'https://example.test/theme' . $path; }
    function get_stylesheet_uri() { return 'https://example.test/theme/style.css'; }
    function is_page() { return false; }
    function is_singular() { return false; }
    function is_archive() { return false; }
    function is_search() { return false; }
    function is_404() { return false; }
}
namespace AZnet\Theme\Integrations\WooCommerce {
    function current_surface(): ?string { return $GLOBALS['aznet_test_woo_surface'] ?? null; }
}
namespace {
    require __DIR__ . '/../../inc/theme/content-shell.php';
    require __DIR__ . '/../../inc/theme/woocommerce-product.php';
    require __DIR__ . '/../../inc/theme/woocommerce-archive.php';
    require __DIR__ . '/../../inc/theme/assets.php';

    $cases = [ 'product' => true, 'archive' => false, 'cart' => false, 'checkout' => false, 'account' => false, '' => false ];
    foreach ( $cases as $surface => $expected ) {
        $GLOBALS['aznet_test_woo_surface'] = '' === $surface ? null : $surface;
        $GLOBALS['aznet_test_styles'] = [];
        $actual = \AZnet\Theme\should_enqueue_woocommerce_product_assets();
        if ( $actual !== $expected ) { fwrite( STDERR, "eligibility mismatch: {$surface}\n" ); exit( 1 ); }
        \AZnet\Theme\enqueue_assets();
        $loaded = in_array( 'aznet-theme-woocommerce-product', $GLOBALS['aznet_test_styles'], true );
        if ( $loaded !== $expected ) { fwrite( STDERR, "enqueue mismatch: {$surface}\n" ); exit( 2 ); }
    }
    echo "PASS: W2 product-only asset scope\n";
}

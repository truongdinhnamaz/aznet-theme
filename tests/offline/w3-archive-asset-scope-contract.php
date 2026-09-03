<?php
namespace {
    define('ABSPATH', __DIR__);
    define('AZNET_THEME_VERSION', 'test');
    $GLOBALS['aznet_test_styles'] = [];
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = null) { $GLOBALS['aznet_test_styles'][] = $handle; }
    function get_theme_file_uri($path) { return 'https://example.test/theme' . $path; }
    function get_stylesheet_uri() { return 'https://example.test/theme/style.css'; }
    function is_page() { return false; }
    function is_singular($type = '') { return false; }
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

    $cases = [
        'archive' => true,
        'product' => false,
        'cart' => false,
        'checkout' => false,
        'account' => false,
        '' => false,
    ];

    foreach ($cases as $surface => $expected) {
        $GLOBALS['aznet_test_woo_surface'] = '' === $surface ? null : $surface;
        $GLOBALS['aznet_test_styles'] = [];
        $actual = \AZnet\Theme\should_enqueue_woocommerce_archive_assets();
        if ($actual !== $expected) { fwrite(STDERR, "wrong archive eligibility for {$surface}\n"); exit(1); }
        \AZnet\Theme\enqueue_assets();
        $loaded = in_array('aznet-theme-woocommerce-archive', $GLOBALS['aznet_test_styles'], true);
        if ($loaded !== $expected) { fwrite(STDERR, "wrong archive enqueue for {$surface}\n"); exit(2); }
    }

    echo "PASS: W3 archive-only asset scope\n";
}

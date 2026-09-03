<?php
$root = dirname(__DIR__, 2);

if (is_dir($root . '/woocommerce')) {
    fwrite(STDERR, "forbidden Woo template override directory\n");
    exit(1);
}

$paths = [
    'inc/theme/woocommerce-cart.php',
    'inc/theme/assets.php',
    'inc/theme/bootstrap.php',
    'assets/css/components/woocommerce-cart.css',
];

$forbidden = [
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'Automattic\\WooCommerce\\Internal',
    'WC()->cart',
    'calculate_totals',
    'set_quantity',
    'apply_coupon',
    'remove_coupon',
    'wp_ajax_',
    'admin-ajax.php',
    'fetch(',
    'XMLHttpRequest',
    'choiceguide_',
    'convertflow',
    'position: sticky',
];

foreach ($paths as $relative) {
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        fwrite(STDERR, "missing W4 production path: {$relative}\n");
        exit(2);
    }
    $contents = file_get_contents($path);
    foreach ($forbidden as $needle) {
        if (false !== stripos($contents, $needle)) {
            fwrite(STDERR, "forbidden token {$needle} in {$relative}\n");
            exit(3);
        }
    }
    if (preg_match("/['\"]_woocommerce_[^'\"]*['\"]/i", $contents)) {
        fwrite(STDERR, "forbidden Woo storage-key literal in {$relative}\n");
        exit(4);
    }
}

foreach (['assets/js/woocommerce-cart.js', 'assets/js/components/woocommerce-cart.js'] as $relative) {
    if (is_file($root . '/' . $relative)) {
        fwrite(STDERR, "forbidden W4 JavaScript asset: {$relative}\n");
        exit(5);
    }
}

$bootstrap = file_get_contents($root . '/inc/theme/bootstrap.php');
$required_once = [
    "require_once __DIR__ . '/../integrations/woocommerce.php';",
    "require_once __DIR__ . '/woocommerce-product.php';",
    "require_once __DIR__ . '/woocommerce-archive.php';",
    "require_once __DIR__ . '/woocommerce-cart.php';",
    "require_once __DIR__ . '/rootprofile-current-surface.php';",
];
foreach ($required_once as $needle) {
    if (substr_count($bootstrap, $needle) !== 1) {
        fwrite(STDERR, "bootstrap dependency count mismatch: {$needle}\n");
        exit(6);
    }
}
if (substr_count($bootstrap, 'add_action(') !== 2 || false !== strpos($bootstrap, 'add_filter(')) {
    fwrite(STDERR, "bootstrap lifecycle registrations drifted\n");
    exit(7);
}
if (false !== strpos($bootstrap, 'render_current_rootprofile_surface')) {
    fwrite(STDERR, "RootProfile dormant dispatcher must remain unwired\n");
    exit(8);
}

echo "PASS: W4 Cart ownership / no-commerce-behavior / no-override contract\n";

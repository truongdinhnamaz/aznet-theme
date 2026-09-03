<?php
$root = dirname(__DIR__, 2);

if (is_dir($root . '/woocommerce')) {
    fwrite(STDERR, "forbidden Woo template override directory\n");
    exit(1);
}

$paths = [
    'inc/theme/woocommerce-checkout.php',
    'inc/theme/assets.php',
    'inc/theme/bootstrap.php',
    'assets/css/components/woocommerce-checkout.css',
];

$forbidden = [
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'Automattic\\WooCommerce\\Internal',
    'WC()->cart',
    'WC_Order',
    'wc_create_order',
    'calculate_totals',
    'set_total',
    'set_payment_method',
    'woocommerce_checkout_fields',
    'woocommerce_default_address_fields',
    'woocommerce_available_payment_gateways',
    'woocommerce_checkout_process',
    'woocommerce_checkout_create_order',
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
        fwrite(STDERR, "missing W5 production path: {$relative}\n");
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

foreach (['assets/js/woocommerce-checkout.js', 'assets/js/components/woocommerce-checkout.js'] as $relative) {
    if (is_file($root . '/' . $relative)) {
        fwrite(STDERR, "forbidden W5 JavaScript asset: {$relative}\n");
        exit(5);
    }
}

$bootstrap = file_get_contents($root . '/inc/theme/bootstrap.php');
$required_once = [
    "require_once __DIR__ . '/../integrations/woocommerce.php';",
    "require_once __DIR__ . '/woocommerce-product.php';",
    "require_once __DIR__ . '/woocommerce-archive.php';",
    "require_once __DIR__ . '/woocommerce-cart.php';",
    "require_once __DIR__ . '/woocommerce-checkout.php';",
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

echo "PASS: W5 Checkout ownership / no-payment-order-behavior / no-override contract\n";

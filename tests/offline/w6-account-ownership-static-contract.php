<?php
$root = dirname(__DIR__, 2);

if (is_dir($root . '/woocommerce')) {
    fwrite(STDERR, "forbidden Woo template override directory\n");
    exit(1);
}

$paths = [
    'inc/theme/woocommerce-account.php',
    'inc/theme/assets.php',
    'inc/theme/bootstrap.php',
    'assets/css/components/woocommerce-account.css',
];

$forbidden = [
    'get_option(',
    'get_user_meta(',
    'get_post_meta(',
    '$wpdb',
    'WP_Query',
    'wc_get_orders(',
    'WC_Order',
    'wp_signon(',
    'wp_logout(',
    'wp_set_auth_cookie(',
    'add_rewrite_endpoint(',
    'woocommerce_account_menu_items',
    'woocommerce_get_endpoint_url',
    'woocommerce_save_account_details',
    'Automattic\\WooCommerce\\Internal',
    'choiceguide_',
];

foreach ($paths as $relative) {
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        fwrite(STDERR, "missing W6 production path: {$relative}\n");
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

foreach (['inc/theme/woocommerce-account.php', 'assets/css/components/woocommerce-account.css'] as $relative) {
    $contents = file_get_contents($root . '/' . $relative);
    if (false !== stripos($contents, 'convertflow')) {
        fwrite(STDERR, "W6 account presentation must not couple to ConvertFlow in {$relative}\n");
        exit(9);
    }
}

foreach (['assets/js/woocommerce-account.js', 'assets/js/components/woocommerce-account.js'] as $relative) {
    if (is_file($root . '/' . $relative)) {
        fwrite(STDERR, "forbidden W6 JavaScript asset: {$relative}\n");
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
    "require_once __DIR__ . '/woocommerce-account.php';",
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

echo "PASS: W6 My Account ownership / no-auth-data-endpoint / no-override contract\n";

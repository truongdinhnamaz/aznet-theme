<?php
/**
 * R4 Cart, Checkout and My Account presentation contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    'cart' => $root . '/assets/css/components/woocommerce-cart.css',
    'checkout' => $root . '/assets/css/components/woocommerce-checkout.css',
    'account' => $root . '/assets/css/components/woocommerce-account.css',
];

foreach ($files as $surface => $file) {
    if (! is_file($file)) {
        fwrite(STDERR, "FAIL: missing R4 {$surface} stylesheet\n");
        exit(1);
    }
}

$cart = file_get_contents($files['cart']);
$checkout = file_get_contents($files['checkout']);
$account = file_get_contents($files['account']);
if (false === $cart || false === $checkout || false === $account) {
    fwrite(STDERR, "FAIL: unable to read R4 commerce surface CSS\n");
    exit(1);
}

foreach ([
    '.woocommerce-cart .shop_table .product-name',
    '.woocommerce-cart .shop_table .product-quantity',
    '.woocommerce-cart .shop_table .product-subtotal',
    '.woocommerce-cart .coupon',
    '.woocommerce-cart .cart-collaterals .cart_totals',
    '.woocommerce-cart .checkout-button',
    ':focus-visible',
    '@media (max-width: 640px)',
] as $needle) {
    if (! str_contains($cart, $needle)) {
        fwrite(STDERR, "FAIL: R4 Cart presentation missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    '.woocommerce form.checkout .woocommerce-invalid input.input-text',
    '.woocommerce form.checkout .woocommerce-invalid select',
    '.woocommerce-error',
    '.wc-block-components-validation-error',
    '.woocommerce-checkout #payment .place-order',
    '.wc-block-components-checkout-place-order-button',
    '--aznet-theme-color-danger',
    ':focus-visible',
    '@media (max-width: 640px)',
] as $needle) {
    if (! str_contains($checkout, $needle)) {
        fwrite(STDERR, "FAIL: R4 Checkout presentation missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    '.woocommerce-account .woocommerce-MyAccount-navigation a:focus-visible',
    '.woocommerce-account .woocommerce-orders-table',
    '.woocommerce-account .woocommerce-orders-table .woocommerce-button',
    '.woocommerce-account .woocommerce-EditAccountForm',
    '.woocommerce-account form.login',
    ':focus-visible',
    '@media (max-width: 640px)',
] as $needle) {
    if (! str_contains($account, $needle)) {
        fwrite(STDERR, "FAIL: R4 My Account presentation missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    $root . '/inc/theme/woocommerce-cart.php',
    $root . '/inc/theme/woocommerce-checkout.php',
    $root . '/inc/theme/woocommerce-account.php',
] as $file) {
    $php = file_get_contents($file);
    if (false === $php) {
        fwrite(STDERR, "FAIL: unable to inspect " . basename($file) . "\n");
        exit(1);
    }

    foreach ([
        'WC()->cart',
        'wc_create_order',
        'woocommerce_checkout_process',
        'woocommerce_checkout_create_order',
        'woocommerce_account_menu_items',
        'add_rewrite_endpoint(',
        'wp_ajax_',
        'get_post_meta(',
        'get_user_meta(',
        '$wpdb',
    ] as $needle) {
        if (str_contains($php, $needle)) {
            fwrite(STDERR, "FAIL: R4 commerce surface takes Woo behavior/data ownership via {$needle}\n");
            exit(1);
        }
    }
}

if (is_dir($root . '/woocommerce')) {
    fwrite(STDERR, "FAIL: R4 commerce surfaces must not add Woo template overrides\n");
    exit(1);
}

echo "PASS: R4 Cart Checkout My Account presentation contract\n";

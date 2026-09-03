<?php
$path = __DIR__ . '/../../assets/css/components/woocommerce-cart.css';
if (!is_file($path)) { fwrite(STDERR, "missing cart CSS\n"); exit(1); }
$css = file_get_contents($path);
$required = [
    '.woocommerce-cart-form',
    '.shop_table',
    '.cart_totals',
    '.coupon',
    '.checkout-button',
    '.wp-block-woocommerce-cart',
    '.wc-block-cart-items',
    '.wc-block-components-quantity-selector',
    '.wc-block-cart__submit-button',
    '@media (max-width: 767px)',
    ':focus-visible',
    '--aznet-theme-',
];
foreach ($required as $needle) {
    if (false === strpos($css, $needle)) { fwrite(STDERR, "missing: {$needle}\n"); exit(2); }
}
foreach (['display: none !important', 'position: sticky', 'white-space: nowrap', 'overflow-x: auto'] as $needle) {
    if (false !== strpos($css, $needle)) { fwrite(STDERR, "forbidden: {$needle}\n"); exit(3); }
}
echo "PASS: W4 Cart CSS contract\n";

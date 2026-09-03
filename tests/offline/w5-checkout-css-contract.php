<?php
$path = __DIR__ . '/../../assets/css/components/woocommerce-checkout.css';
if (!is_file($path)) { fwrite(STDERR, "missing checkout CSS\n"); exit(1); }
$css = file_get_contents($path);
$required = [
    '.woocommerce form.checkout',
    '#customer_details',
    '#order_review_heading',
    '#order_review',
    '#payment',
    '.wc-block-checkout',
    '.wc-block-components-text-input',
    '.wc-block-components-checkout-place-order-button',
    '.wc-block-components-notice-banner',
    'display: grid',
    'grid-template-columns: minmax(0, 1.25fr) minmax(20rem, 0.75fr)',
    '@media (max-width: 767px)',
    'grid-template-columns: 1fr',
    ':focus-visible',
    '--aznet-theme-',
];
foreach ($required as $needle) {
    if (false === strpos($css, $needle)) { fwrite(STDERR, "missing: {$needle}\n"); exit(2); }
}
foreach (['display: none !important', 'position: sticky', 'overflow-x: scroll', 'visibility: hidden'] as $needle) {
    if (false !== strpos($css, $needle)) { fwrite(STDERR, "forbidden: {$needle}\n"); exit(3); }
}
if (preg_match('/(?:^|[;{])\s*order\s*:/mi', $css)) {
    fwrite(STDERR, "forbidden CSS order property\n");
    exit(4);
}
echo "PASS: W5 checkout CSS contract\n";

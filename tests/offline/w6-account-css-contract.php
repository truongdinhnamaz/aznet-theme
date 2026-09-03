<?php
$path = __DIR__ . '/../../assets/css/components/woocommerce-account.css';
if (!is_file($path)) { fwrite(STDERR, "missing account CSS\n"); exit(1); }
$css = file_get_contents($path);
$required = [
    '.woocommerce-account',
    '.woocommerce-MyAccount-navigation',
    '.woocommerce-MyAccount-content',
    '.woocommerce-orders-table',
    '.woocommerce-Addresses',
    '.woocommerce-EditAccountForm',
    'display: grid',
    'grid-template-columns: minmax(12rem, 0.32fr) minmax(0, 0.68fr)',
    '@media (max-width: 767px)',
    'grid-template-columns: 1fr',
    ':focus-visible',
    '--aznet-theme-',
];
foreach ($required as $needle) {
    if (false === strpos($css, $needle)) { fwrite(STDERR, "missing: {$needle}\n"); exit(2); }
}
foreach (['display: none !important', 'position: sticky', 'overflow-x: scroll', 'visibility: hidden', 'aznet-account-toggle'] as $needle) {
    if (false !== strpos($css, $needle)) { fwrite(STDERR, "forbidden: {$needle}\n"); exit(3); }
}
echo "PASS: W6 account CSS contract\n";

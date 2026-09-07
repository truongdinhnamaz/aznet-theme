<?php
$path = __DIR__ . '/../../assets/css/components/woocommerce-archive.css';
if (!is_file($path)) { fwrite(STDERR, "missing archive CSS\n"); exit(1); }
$css = file_get_contents($path);
$required = [
    '.woocommerce ul.products',
    'grid-template-columns: repeat(var(--aznet-theme-woo-catalog-columns, 4), minmax(0, 1fr))',
    '@media (max-width: 1023px)',
    '--aznet-theme-woo-catalog-columns: 3;',
    '@media (max-width: 767px)',
    '--aznet-theme-woo-catalog-columns: 2;',
    '@media (max-width: 479px)',
    '--aznet-theme-woo-catalog-columns: 1;',
    '.woocommerce-result-count',
    '.woocommerce-ordering',
    '.woocommerce-loop-product__title',
    '.price',
    ':focus-visible',
    '--aznet-theme-',
];
foreach ($required as $needle) {
    if (false === strpos($css, $needle)) { fwrite(STDERR, "missing: {$needle}\n"); exit(2); }
}
foreach (['display: none !important', 'position: sticky', 'transform: scale'] as $needle) {
    if (false !== strpos($css, $needle)) { fwrite(STDERR, "forbidden: {$needle}\n"); exit(3); }
}
echo "PASS: W3 archive CSS contract\n";

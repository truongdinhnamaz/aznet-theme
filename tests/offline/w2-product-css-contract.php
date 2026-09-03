<?php
$path = __DIR__ . '/../../assets/css/components/woocommerce-product.css';
if ( ! is_file( $path ) ) { fwrite( STDERR, "missing product CSS\n" ); exit( 1 ); }
$css = file_get_contents( $path );
$required = [
    '.single-product',
    '.woocommerce-product-gallery',
    '.summary',
    '.woocommerce-tabs',
    'display: grid',
    'minmax(0, 1.25fr)',
    'minmax(0, 1fr)',
    '@media (max-width: 767px)',
    'grid-template-columns: 1fr',
    '--aznet-theme-',
    ':focus-visible',
];
foreach ( $required as $needle ) {
    if ( false === strpos( $css, $needle ) ) { fwrite( STDERR, "missing: {$needle}\n" ); exit( 2 ); }
}
$forbidden = [ 'position: sticky', 'display: none !important' ];
foreach ( $forbidden as $needle ) {
    if ( false !== strpos( $css, $needle ) ) { fwrite( STDERR, "forbidden: {$needle}\n" ); exit( 3 ); }
}
echo "PASS: W2 product CSS contract\n";

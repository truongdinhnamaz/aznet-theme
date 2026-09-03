<?php
$root = dirname( __DIR__, 2 );

if ( is_dir( $root . '/woocommerce' ) ) {
    fwrite( STDERR, "forbidden Woo template override directory\n" );
    exit( 1 );
}

$paths = [
    'inc/theme/woocommerce-product.php',
    'inc/theme/assets.php',
    'inc/theme/bootstrap.php',
    'assets/css/components/woocommerce-product.css',
];

$forbidden = [
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'Automattic\\WooCommerce\\Internal',
    'choiceguide_',
    'convertflow',
    'position: sticky',
    'sticky_add_to_cart',
];

foreach ( $paths as $relative ) {
    $path = $root . '/' . $relative;
    if ( ! is_file( $path ) ) {
        fwrite( STDERR, "missing W2 production path: {$relative}\n" );
        exit( 2 );
    }

    $contents = file_get_contents( $path );
    foreach ( $forbidden as $needle ) {
        if ( false !== stripos( $contents, $needle ) ) {
            fwrite( STDERR, "forbidden token {$needle} in {$relative}\n" );
            exit( 3 );
        }
    }

    if ( preg_match( "/['\"]_woocommerce_[^'\"]*['\"]/i", $contents ) ) {
        fwrite( STDERR, "forbidden Woo storage-key literal in {$relative}\n" );
        exit( 4 );
    }
}

echo "PASS: W2 product ownership / no-override contract\n";

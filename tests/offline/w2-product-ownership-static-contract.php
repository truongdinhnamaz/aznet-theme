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

foreach ( [ 'inc/theme/woocommerce-product.php', 'assets/css/components/woocommerce-product.css' ] as $relative ) {
    $contents = file_get_contents( $root . '/' . $relative );
    if ( false !== stripos( $contents, 'convertflow' ) ) {
        fwrite( STDERR, "W2 product presentation must not couple to ConvertFlow in {$relative}\n" );
        exit( 5 );
    }
}

$css = file_get_contents( $root . '/assets/css/components/woocommerce-product.css' );
if ( false !== stripos( $css, 'position: sticky' ) ) {
    if ( ! preg_match( '/aznet-theme-woo-product--focus[^\{]*\.summary\s*\{[^\}]*position\s*:\s*sticky/is', $css ) ) {
        fwrite( STDERR, "sticky product presentation is allowed only for the R4 Focus native summary\n" );
        exit( 6 );
    }

    foreach ( [ '.single_add_to_cart_button', 'form.cart', '.variations_form' ] as $selector ) {
        $pattern = '/' . preg_quote( $selector, '/' ) . '[^\{]*\{[^\}]*position\s*:\s*sticky/is';
        if ( preg_match( $pattern, $css ) ) {
            fwrite( STDERR, "forbidden sticky commerce-control projection: {$selector}\n" );
            exit( 7 );
        }
    }
}

echo "PASS: W2 product ownership / no-override contract\n";

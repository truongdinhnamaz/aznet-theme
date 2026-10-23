<?php
/**
 * Build disposable WooCommerce fixtures for the AZnet Theme L3 runtime smoke test.
 */

if ( ! defined( 'ABSPATH' ) ) {
    fwrite( STDERR, "WordPress runtime is not loaded.\n" );
    exit( 1 );
}

if ( ! class_exists( 'WC_Product_Simple' ) ) {
    fwrite( STDERR, "WooCommerce runtime is not loaded.\n" );
    exit( 2 );
}

/**
 * Create or update a public test page.
 */
function aznet_l3_upsert_page( string $slug, string $title, string $content ): int {
    $existing = get_page_by_path( $slug, OBJECT, 'page' );
    $postarr = [
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ];

    if ( $existing instanceof WP_Post ) {
        $postarr['ID'] = $existing->ID;
    }

    $page_id = wp_insert_post( $postarr, true );
    if ( is_wp_error( $page_id ) ) {
        throw new RuntimeException( $page_id->get_error_message() );
    }

    return (int) $page_id;
}

$shop_id = aznet_l3_upsert_page( 'shop', 'Shop', '' );
$cart_id = aznet_l3_upsert_page( 'cart', 'Cart', '[woocommerce_cart]' );
$checkout_id = aznet_l3_upsert_page( 'checkout', 'Checkout', '[woocommerce_checkout]' );
$account_id = aznet_l3_upsert_page( 'my-account', 'My Account', '[woocommerce_my_account]' );
$generic_id = aznet_l3_upsert_page( 'runtime-generic-page', 'Runtime Generic Page', 'AZnet Theme runtime generic surface.' );

update_option( 'woocommerce_shop_page_id', $shop_id );
update_option( 'woocommerce_cart_page_id', $cart_id );
update_option( 'woocommerce_checkout_page_id', $checkout_id );
update_option( 'woocommerce_myaccount_page_id', $account_id );

$product = new WC_Product_Simple();
$product->set_name( 'AZnet Runtime Product' );
$product->set_slug( 'aznet-runtime-product' );
$product->set_status( 'publish' );
$product->set_catalog_visibility( 'visible' );
$product->set_regular_price( '49.00' );
$product->set_price( '49.00' );
$product->set_stock_status( 'instock' );
$product_id = (int) $product->save();

if ( $product_id <= 0 ) {
    throw new RuntimeException( 'Failed to create WooCommerce runtime product.' );
}

$state_dir = getenv( 'WOO_L3_STATE_DIR' ) ?: sys_get_temp_dir() . '/woo-l3';
if ( ! is_dir( $state_dir ) && ! mkdir( $state_dir, 0777, true ) && ! is_dir( $state_dir ) ) {
    throw new RuntimeException( 'Failed to create runtime state directory.' );
}

$state = sprintf(
    "PRODUCT_ID=%d\nSHOP_PAGE_ID=%d\nCART_PAGE_ID=%d\nCHECKOUT_PAGE_ID=%d\nACCOUNT_PAGE_ID=%d\nGENERIC_PAGE_ID=%d\n",
    $product_id,
    $shop_id,
    $cart_id,
    $checkout_id,
    $account_id,
    $generic_id
);

file_put_contents( $state_dir . '/runtime-ids.env', $state );
echo $state;

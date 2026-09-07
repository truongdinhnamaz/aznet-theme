<?php
/**
 * Create R4 WooCommerce browser fixtures through public Woo APIs.
 * Executed only by CI through wp eval-file.
 */

declare(strict_types=1);

if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Product_Simple' ) ) {
    fwrite( STDERR, "FAIL: WooCommerce public product APIs unavailable\n" );
    exit( 1 );
}

if ( class_exists( 'WC_Install' ) ) {
    WC_Install::create_pages();
}

update_option(
    'woocommerce_cod_settings',
    [
        'enabled'            => 'yes',
        'title'              => 'Cash on delivery',
        'description'        => 'Pay when delivered',
        'instructions'       => '',
        'enable_for_methods' => [],
        'enable_for_virtual' => 'yes',
    ]
);

$media_id   = (int) getenv( 'R4_MEDIA_ID' );
$gallery_id = (int) getenv( 'R4_GALLERY_ID' );
if ( $media_id < 1 || $gallery_id < 1 ) {
    fwrite( STDERR, "FAIL: R4 media fixtures are missing\n" );
    exit( 1 );
}

$simple = new WC_Product_Simple();
$simple->set_name( 'R4 Simple Product' );
$simple->set_slug( 'r4-simple-product' );
$simple->set_status( 'publish' );
$simple->set_regular_price( '99' );
$simple->set_virtual( true );
$simple->set_image_id( $media_id );
$simple->set_gallery_image_ids( [ $gallery_id ] );
$simple->set_average_rating( '4.50' );
$simple->set_review_count( 2 );
$simple_id = $simple->save();

$variable = new WC_Product_Variable();
$variable->set_name( 'R4 Variable Product' );
$variable->set_slug( 'r4-variable-product' );
$variable->set_status( 'publish' );
$variable->set_image_id( $media_id );
$variable->set_gallery_image_ids( [ $gallery_id ] );
$variable->set_average_rating( '4.50' );
$variable->set_review_count( 2 );

$attribute = new WC_Product_Attribute();
$attribute->set_id( 0 );
$attribute->set_name( 'Size' );
$attribute->set_options( [ 'Small', 'Large' ] );
$attribute->set_visible( true );
$attribute->set_variation( true );
$variable->set_attributes( [ $attribute ] );
$variable_id = $variable->save();

foreach ( [ 'Small' => '120', 'Large' => '140' ] as $label => $price ) {
    $variation = new WC_Product_Variation();
    $variation->set_parent_id( $variable_id );
    $variation->set_status( 'publish' );
    $variation->set_virtual( true );
    $variation->set_regular_price( $price );
    $variation->set_attributes( [ 'size' => $label ] );
    $variation->save();
}

WC_Product_Variable::sync( $variable_id );
wc_delete_product_transients( $variable_id );

$block_registry = WP_Block_Type_Registry::get_instance();
foreach ( [ 'woocommerce/product-collection', 'woocommerce/product-categories' ] as $block_name ) {
    if ( ! $block_registry->is_registered( $block_name ) ) {
        fwrite( STDERR, "FAIL: required public Woo block capability missing: {$block_name}\n" );
        exit( 1 );
    }
    echo "PUBLIC_WOO_BLOCK={$block_name}\n";
}

$pattern_slug = 'aznet-theme/commerce-featured-products';
$pattern      = WP_Block_Patterns_Registry::get_instance()->get_registered( $pattern_slug );
if ( ! is_array( $pattern ) || empty( $pattern['content'] ) ) {
    fwrite( STDERR, "FAIL: R2 certified Woo pattern unavailable: {$pattern_slug}\n" );
    exit( 1 );
}

$page_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'R4 Blocks Fixture',
        'post_name'    => 'r4-blocks-fixture',
        'post_content' => (string) $pattern['content'],
    ],
    true
);
if ( is_wp_error( $page_id ) ) {
    fwrite( STDERR, "FAIL: unable to create R4 Woo Blocks fixture\n" );
    exit( 1 );
}

echo "SIMPLE_ID={$simple_id}\n";
echo "VARIABLE_ID={$variable_id}\n";
echo "BLOCKS_PAGE_ID={$page_id}\n";

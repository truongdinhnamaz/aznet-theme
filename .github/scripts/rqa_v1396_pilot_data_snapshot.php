<?php
/**
 * RQA isolated upgrade/rollback data-integrity snapshot.
 * READ ONLY, WordPress/WooCommerce public APIs only. No Theme or plugin private storage.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit( 1 );
}

function rqa_upgrade_ids( string $type ): array {
    $ids = get_posts(
        [
            'post_type' => $type,
            'post_status' => [ 'publish' ],
            'posts_per_page' => -1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
        ]
    );
    $result = [];
    foreach ( $ids as $id ) {
        $id = (int) $id;
        $row = [ 'id' => $id, 'slug' => (string) get_post_field( 'post_name', $id ), 'title' => (string) get_the_title( $id ) ];
        if ( 'product' === $type ) {
            $terms = wp_get_post_terms( $id, 'product_cat', [ 'fields' => 'ids' ] );
            if ( is_wp_error( $terms ) ) {
                throw new RuntimeException( 'Woo public product categories unavailable.' );
            }
            $terms = array_map( 'intval', $terms );
            sort( $terms );
            $row['categories'] = $terms;
            $wc_product = wc_get_product( $id );
            $row['type'] = $wc_product instanceof WC_Product ? $wc_product->get_type() : 'unavailable';
            $row['gallery'] = $wc_product instanceof WC_Product ? $wc_product->get_gallery_image_ids() : [];
        }
        $result[] = $row;
    }
    return $result;
}

if ( ! function_exists( 'wc_get_product' ) ) {
    throw new RuntimeException( 'WooCommerce public query API unavailable.' );
}
$menus = [];
foreach ( wp_get_nav_menus() as $menu ) {
    $items = wp_get_nav_menu_items( (int) $menu->term_id );
    $menus[] = [
        'id' => (int) $menu->term_id,
        'name' => (string) $menu->name,
        'items' => is_array( $items ) ? array_map( static fn( $item ): int => (int) $item->ID, $items ) : [],
    ];
}
usort( $menus, static fn( $a, $b ): int => $a['id'] <=> $b['id'] );
$loc = get_nav_menu_locations();
ksort( $loc );
$settings = get_theme_mod( 'aznet_theme_settings', [] );
if ( ! is_array( $settings ) ) {
    throw new RuntimeException( 'Theme settings did not remain a Theme presentation settings array.' );
}
ksort( $settings );
$state = [
    'homepage' => [
        'show_on_front' => (string) get_option( 'show_on_front' ),
        'page_on_front' => (int) get_option( 'page_on_front' ),
    ],
    'theme_presentation_settings' => $settings,
    'logo_media_id' => (int) get_theme_mod( 'custom_logo', 0 ),
    'theme_custom_css_post_id' => (int) get_theme_mod( 'custom_css_post_id', -1 ),
    'menu_locations' => $loc,
    'menus' => $menus,
    'pages' => rqa_upgrade_ids( 'page' ),
    'posts' => rqa_upgrade_ids( 'post' ),
    'products' => rqa_upgrade_ids( 'product' ),
];
echo wp_json_encode( $state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n";

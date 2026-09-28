<?php
/**
 * Industrial 01 latest products.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$products = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_products' )
    ? \AZnet\Theme\Integrations\WooCommerce\homepage_products( 8 )
    : [];

if ( ! is_array( $products ) || [] === $products ) {
    return;
}
?>
<section class="aznet-theme-industrial01-section aznet-theme-industrial01-products">
    <div class="aznet-theme-industrial01-shell">
        <div class="aznet-theme-industrial01-heading">
            <div>
                <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Sản phẩm', 'aznet-theme' ); ?></p>
                <h2><?php echo esc_html( \AZnet\Theme\preset_term( 'catalog', __( 'Thiết bị & phụ kiện', 'aznet-theme' ), 'industrial-01' ) ); ?></h2>
            </div>
        </div>
        <div class="aznet-theme-industrial01-grid aznet-theme-industrial01-grid--products">
            <?php foreach ( $products as $product ) : ?>
                <?php
                if ( ! is_object( $product ) || ! method_exists( $product, 'get_permalink' ) ) {
                    continue;
                }
                $product_id = method_exists( $product, 'get_id' ) ? (int) $product->get_id() : 0;
                ?>
                <article class="aznet-theme-industrial01-card aznet-theme-industrial01-product-card">
                    <a class="aznet-theme-industrial01-product-card__media" href="<?php echo esc_url( $product->get_permalink() ); ?>">
                        <?php
                        if ( $product_id > 0 && has_post_thumbnail( $product_id ) ) {
                            echo get_the_post_thumbnail( $product_id, 'woocommerce_thumbnail', [ 'loading' => 'lazy' ] );
                        }
                        ?>
                    </a>
                    <div class="aznet-theme-industrial01-product-card__body">
                        <h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
                        <?php if ( method_exists( $product, 'get_price_html' ) ) : ?>
                            <div class="aznet-theme-industrial01-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

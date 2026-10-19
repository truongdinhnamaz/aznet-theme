<?php
/**
 * Industrial 01 latest public WooCommerce products.
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

$shop_url = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\shop_url' )
    ? \AZnet\Theme\Integrations\WooCommerce\shop_url()
    : '';
?>
<section class="aznet-theme-industrial01-section aznet-theme-industrial01-products" aria-labelledby="aznet-industrial01-products-title">
    <div class="aznet-theme-industrial01-shell">
        <div class="aznet-theme-industrial01-products__heading">
            <div>
                <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Sản phẩm', 'aznet-theme' ); ?></p>
                <h2 id="aznet-industrial01-products-title"><?php echo esc_html( \AZnet\Theme\preset_term( 'catalog', __( 'Thiết bị & phụ kiện', 'aznet-theme' ), 'industrial-01' ) ); ?></h2>
            </div>
            <?php if ( is_string( $shop_url ) && '' !== $shop_url ) : ?>
                <a class="aznet-theme-industrial01-products__all" href="<?php echo esc_url( $shop_url ); ?>">
                    <?php esc_html_e( 'Xem tất cả sản phẩm', 'aznet-theme' ); ?>
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 17 17 7M8 7h9v9"/></svg>
                </a>
            <?php endif; ?>
        </div>
        <div class="aznet-theme-industrial01-grid aznet-theme-industrial01-grid--products">
            <?php foreach ( $products as $index => $product ) : ?>
                <?php
                if ( ! is_object( $product ) || ! method_exists( $product, 'get_permalink' ) || ! method_exists( $product, 'get_name' ) ) {
                    continue;
                }
                $product_id  = method_exists( $product, 'get_id' ) ? (int) $product->get_id() : 0;
                $product_url = $product->get_permalink();
                if ( ! is_string( $product_url ) || '' === $product_url ) {
                    continue;
                }
                $has_image = $product_id > 0 && has_post_thumbnail( $product_id );
                ?>
                <article class="aznet-theme-industrial01-card aznet-theme-industrial01-product-card">
                    <a class="aznet-theme-industrial01-product-card__media<?php echo $has_image ? '' : ' aznet-theme-industrial01-product-card__media--empty'; ?>"
                       href="<?php echo esc_url( $product_url ); ?>"
                       aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
                        <?php if ( $has_image ) : ?>
                            <?php echo get_the_post_thumbnail( $product_id, 'woocommerce_thumbnail', [ 'loading' => 'lazy' ] ); ?>
                        <?php else : ?>
                            <span class="aznet-theme-industrial01-product-card__empty-symbol" aria-hidden="true">
                                <svg viewBox="0 0 64 64" width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="8" y="12" width="48" height="40" rx="2"/><path d="M8 42l13-12 11 9 9-8 15 13M23 22h.01"/></svg>
                            </span>
                            <span class="aznet-theme-industrial01-product-card__empty-label"><?php esc_html_e( 'Xem thiết bị', 'aznet-theme' ); ?></span>
                        <?php endif; ?>
                        <span class="aznet-theme-industrial01-product-card__index" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
                    </a>
                    <div class="aznet-theme-industrial01-product-card__body">
                        <h3><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
                        <?php if ( method_exists( $product, 'get_price_html' ) && '' !== trim( (string) $product->get_price_html() ) ) : ?>
                            <div class="aznet-theme-industrial01-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
                        <?php endif; ?>
                        <a class="aznet-theme-industrial01-product-card__action" href="<?php echo esc_url( $product_url ); ?>">
                            <?php esc_html_e( 'Xem chi tiết', 'aznet-theme' ); ?>
                            <span class="aznet-theme-industrial01-product-card__arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 17 17 7M8 7h9v9"/></svg>
                            </span>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

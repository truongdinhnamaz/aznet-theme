<?php
/**
 * Curtain 01 WooCommerce catalogue projection.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$categories = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_product_categories' )
    ? \AZnet\Theme\Integrations\WooCommerce\homepage_product_categories( 4 )
    : [];

$products = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_products' )
    ? \AZnet\Theme\Integrations\WooCommerce\homepage_products( 6 )
    : [];

if ( [] === $categories && [] === $products ) {
    return;
}

$shop_url = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\shop_url' )
    ? \AZnet\Theme\Integrations\WooCommerce\shop_url()
    : '';
?>
<section class="aznet-theme-curtain01-section aznet-theme-curtain01-catalogue" aria-labelledby="aznet-curtain01-catalogue-title">
    <div class="aznet-theme-curtain01-shell">
        <div class="aznet-theme-curtain01-section-heading">
            <div>
                <p class="aznet-theme-curtain01-kicker"><?php esc_html_e( 'Danh mục', 'aznet-theme' ); ?></p>
                <h2 id="aznet-curtain01-catalogue-title"><?php esc_html_e( 'Sản phẩm & giải pháp rèm', 'aznet-theme' ); ?></h2>
            </div>
            <?php if ( '' !== $shop_url ) : ?>
                <a class="aznet-theme-curtain01-text-link" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Xem tất cả', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
            <?php endif; ?>
        </div>

        <?php if ( [] !== $categories ) : ?>
            <nav class="aznet-theme-curtain01-catalogue__categories" aria-label="<?php echo esc_attr__( 'Danh mục sản phẩm', 'aznet-theme' ); ?>">
                <?php foreach ( $categories as $term ) : ?>
                    <?php
                    if ( ! $term instanceof \WP_Term ) {
                        continue;
                    }
                    $term_url = get_term_link( $term );
                    if ( is_wp_error( $term_url ) ) {
                        continue;
                    }
                    ?>
                    <a class="aznet-theme-curtain01-category-chip" href="<?php echo esc_url( $term_url ); ?>">
                        <span><?php echo esc_html( $term->name ); ?></span>
                        <?php if ( (int) $term->count > 0 ) : ?><small><?php echo esc_html( (string) $term->count ); ?></small><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ( [] !== $products ) : ?>
            <div class="aznet-theme-curtain01-product-grid">
                <?php foreach ( $products as $product ) : ?>
                    <?php
                    if ( ! is_object( $product ) || ! method_exists( $product, 'get_name' ) || ! method_exists( $product, 'get_permalink' ) ) {
                        continue;
                    }
                    $name = trim( (string) $product->get_name() );
                    $url = (string) $product->get_permalink();
                    if ( '' === $name || '' === $url ) {
                        continue;
                    }
                    $image = method_exists( $product, 'get_image' )
                        ? (string) $product->get_image(
                            'woocommerce_thumbnail',
                            [ 'class' => 'aznet-theme-curtain01-product-card__image' ]
                        )
                        : '';
                    ?>
                    <article class="aznet-theme-curtain01-product-card">
                        <?php if ( '' !== $image ) : ?>
                            <a class="aznet-theme-curtain01-product-card__media" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $name ); ?>">
                                <?php echo wp_kses_post( $image ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="aznet-theme-curtain01-product-card__body">
                            <h3><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a></h3>
                            <a class="aznet-theme-curtain01-text-link" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Xem chi tiết', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

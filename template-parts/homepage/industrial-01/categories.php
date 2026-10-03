<?php
/**
 * Industrial 01 product categories.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$terms = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_product_category_showcase_terms' )
    ? \AZnet\Theme\Integrations\WooCommerce\homepage_product_category_showcase_terms( 8 )
    : [];

if ( [] === $terms ) {
    return;
}
?>
<section class="aznet-theme-industrial01-section aznet-theme-industrial01-categories">
    <div class="aznet-theme-industrial01-shell">
        <div class="aznet-theme-industrial01-heading">
            <div>
                <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Danh mục', 'aznet-theme' ); ?></p>
                <h2><?php echo esc_html( \AZnet\Theme\preset_term( 'primary_group', __( 'Danh mục thiết bị', 'aznet-theme' ), 'industrial-01' ) ); ?></h2>
            </div>
        </div>
        <div class="aznet-theme-industrial01-grid aznet-theme-industrial01-grid--categories">
            <?php foreach ( $terms as $term ) : ?>
                <?php $link = get_term_link( $term ); ?>
                <?php if ( is_wp_error( $link ) ) { continue; } ?>
                <?php
                ob_start();
                if ( function_exists( 'woocommerce_subcategory_thumbnail' ) ) {
                    woocommerce_subcategory_thumbnail( $term );
                }
                $thumbnail = trim( (string) ob_get_clean() );
                ?>
                <a class="aznet-theme-industrial01-card aznet-theme-industrial01-category-card" href="<?php echo esc_url( $link ); ?>">
                    <?php if ( '' !== $thumbnail && str_contains( $thumbnail, '<img' ) ) : ?>
                        <span class="aznet-theme-industrial01-category-card__media"><?php echo wp_kses_post( $thumbnail ); ?></span>
                    <?php endif; ?>
                    <span class="aznet-theme-industrial01-category-card__body">
                        <strong><?php echo esc_html( $term->name ); ?></strong>
                        <?php if ( ! empty( $term->description ) ) : ?>
                            <span><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $term->description ), 18 ) ); ?></span>
                        <?php endif; ?>
                        <span class="aznet-theme-industrial01-category-card__link"><?php esc_html_e( 'Xem danh mục', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

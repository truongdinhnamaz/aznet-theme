<?php
/**
 * Curtain 01 visual product-category gateway.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (
    ! function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_product_category_showcase_terms' )
    || ! function_exists( 'woocommerce_subcategory_thumbnail' )
) {
    return;
}

$terms = \AZnet\Theme\Integrations\WooCommerce\homepage_product_category_showcase_terms( 12 );
if ( [] === $terms ) {
    return;
}

$cards = [];
foreach ( $terms as $term ) {
    if ( ! $term instanceof \WP_Term ) {
        continue;
    }

    $url = get_term_link( $term );
    if ( is_wp_error( $url ) ) {
        continue;
    }

    ob_start();
    woocommerce_subcategory_thumbnail( $term );
    $image = trim( (string) ob_get_clean() );

    if (
        '' === $image
        || ! str_contains( $image, '<img' )
        || str_contains( $image, 'woocommerce-placeholder' )
    ) {
        continue;
    }

    $cards[] = [
        'term'  => $term,
        'url'   => (string) $url,
        'image' => $image,
    ];

}

if ( [] === $cards ) {
    return;
}

$has_carousel = 4 < count( $cards );
$track_id     = 'aznet-curtain01-category-track';
?>
<section class="aznet-theme-curtain01-section aznet-theme-curtain01-category-showcase" aria-labelledby="aznet-curtain01-category-showcase-title" data-aznet-curtain-category-carousel>
    <div class="aznet-theme-curtain01-shell">
        <div class="aznet-theme-curtain01-section-heading">
            <div>
                <p class="aznet-theme-curtain01-kicker"><?php esc_html_e( 'Bộ sưu tập', 'aznet-theme' ); ?></p>
                <h2 id="aznet-curtain01-category-showcase-title"><?php esc_html_e( 'Khám phá theo dòng rèm', 'aznet-theme' ); ?></h2>
            </div>
            <?php if ( $has_carousel ) : ?>
                <div class="aznet-theme-curtain01-category-showcase__controls" role="group" aria-label="<?php esc_attr_e( 'Điều hướng dòng rèm', 'aznet-theme' ); ?>">
                    <button class="aznet-theme-curtain01-category-showcase__control aznet-theme-curtain01-category-showcase__control--prev" type="button" aria-controls="<?php echo esc_attr( $track_id ); ?>" aria-label="<?php esc_attr_e( 'Xem các dòng rèm phía trước', 'aznet-theme' ); ?>" aria-disabled="true" disabled>
                        <span aria-hidden="true">&larr;</span>
                    </button>
                    <button class="aznet-theme-curtain01-category-showcase__control aznet-theme-curtain01-category-showcase__control--next" type="button" aria-controls="<?php echo esc_attr( $track_id ); ?>" aria-label="<?php esc_attr_e( 'Xem thêm dòng rèm phía sau', 'aznet-theme' ); ?>" aria-disabled="false">
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div id="<?php echo esc_attr( $track_id ); ?>" class="aznet-theme-curtain01-category-showcase__grid" tabindex="0">
            <?php foreach ( $cards as $card ) : ?>
                <?php $term = $card['term']; ?>
                <article class="aznet-theme-curtain01-category-showcase__card">
                    <a class="aznet-theme-curtain01-category-showcase__link" href="<?php echo esc_url( $card['url'] ); ?>">
                        <figure class="aznet-theme-curtain01-category-showcase__media">
                            <?php echo wp_kses_post( $card['image'] ); ?>
                        </figure>
                        <div class="aznet-theme-curtain01-category-showcase__meta">
                            <h3><?php echo esc_html( $term->name ); ?></h3>
                            <?php if ( (int) $term->count > 0 ) : ?>
                                <p><?php echo esc_html( sprintf( _n( '%d mẫu', '%d mẫu', (int) $term->count, 'aznet-theme' ), (int) $term->count ) ); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

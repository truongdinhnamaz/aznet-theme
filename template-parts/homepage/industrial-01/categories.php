<?php
/**
 * Industrial 01 product category gallery, presented from WooCommerce's
 * existing public consumer adapter. WooCommerce owns membership/media/URLs.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$terms = function_exists( 'AZnet\Theme\Integrations\WooCommerce\homepage_product_category_showcase_terms' )
    ? \AZnet\Theme\Integrations\WooCommerce\homepage_product_category_showcase_terms( 8 )
    : [];

if ( [] === $terms ) {
    return;
}

$category_cards = [];
foreach ( $terms as $term ) {
    if ( ! $term instanceof \WP_Term ) {
        continue;
    }

    $link = get_term_link( $term );
    if ( is_wp_error( $link ) || '' === trim( (string) $link ) ) {
        continue;
    }

    ob_start();
    if ( function_exists( 'woocommerce_subcategory_thumbnail' ) ) {
        woocommerce_subcategory_thumbnail( $term );
    }
    $thumbnail = trim( (string) ob_get_clean() );

    $category_cards[] = [
        'term'      => $term,
        'url'       => (string) $link,
        'thumbnail' => '' !== $thumbnail && str_contains( $thumbnail, '<img' ) ? $thumbnail : '',
    ];
}

if ( [] === $category_cards ) {
    return;
}

$category_count = count( $category_cards );
$shop_url = function_exists( 'AZnet\Theme\Integrations\WooCommerce\shop_url' )
    ? (string) \AZnet\Theme\Integrations\WooCommerce\shop_url()
    : '';

// Purely visual balancing: 7 native categories + an editorial tile form a
// complete 4x2 gallery. Eight native categories take all eight cells instead.
$show_intro = 3 === $category_count % 4;
?>
<section class="aznet-theme-industrial01-section aznet-theme-industrial01-categories" aria-labelledby="aznet-industrial01-categories-title">
    <div class="aznet-theme-industrial01-shell">
        <div class="aznet-theme-industrial01-categories__heading">
            <div>
                <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Khám phá danh mục', 'aznet-theme' ); ?></p>
                <h2 id="aznet-industrial01-categories-title"><?php echo esc_html( \AZnet\Theme\preset_term( 'primary_group', __( 'Danh mục thiết bị', 'aznet-theme' ), 'industrial-01' ) ); ?></h2>
            </div>
            <p class="aznet-theme-industrial01-categories__lede"><?php esc_html_e( 'Tìm nhanh nhóm thiết bị và phụ kiện phù hợp với nhu cầu của bạn.', 'aznet-theme' ); ?></p>
        </div>

        <div class="aznet-theme-industrial01-grid aznet-theme-industrial01-grid--categories">
            <?php if ( $show_intro ) : ?>
                <div class="aznet-theme-industrial01-categories__intro-card">
                    <span class="aznet-theme-industrial01-categories__intro-count"><?php echo esc_html( sprintf( '%02d danh mục', $category_count ) ); ?></span>
                    <span class="aznet-theme-industrial01-categories__intro-mark" aria-hidden="true">
                        <svg viewBox="0 0 60 60" width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M12 12h14v14H12zM34 12h14v14H34zM12 34h14v14H12zM34 34h14v14H34z"/></svg>
                    </span>
                    <strong><?php esc_html_e( 'Khám phá theo từng nhóm thiết bị', 'aznet-theme' ); ?></strong>
                    <?php if ( '' !== $shop_url ) : ?>
                        <a class="aznet-theme-industrial01-categories__intro-link" href="<?php echo esc_url( $shop_url ); ?>">
                            <?php esc_html_e( 'Xem toàn bộ sản phẩm', 'aznet-theme' ); ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php foreach ( $category_cards as $index => $card ) : ?>
                <?php $term = $card['term']; ?>
                <a class="aznet-theme-industrial01-card aznet-theme-industrial01-category-card" href="<?php echo esc_url( $card['url'] ); ?>">
                    <?php if ( '' !== $card['thumbnail'] ) : ?>
                        <span class="aznet-theme-industrial01-category-card__media" aria-hidden="true"><?php echo wp_kses_post( $card['thumbnail'] ); ?></span>
                    <?php endif; ?>
                    <span class="aznet-theme-industrial01-category-card__veil" aria-hidden="true"></span>
                    <span class="aznet-theme-industrial01-category-card__index" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
                    <span class="aznet-theme-industrial01-category-card__body">
                        <strong><?php echo esc_html( $term->name ); ?></strong>
                        <?php if ( ! empty( $term->description ) ) : ?>
                            <span class="aznet-theme-industrial01-category-card__description"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $term->description ), 18 ) ); ?></span>
                        <?php endif; ?>
                        <span class="aznet-theme-industrial01-category-card__link">
                            <?php esc_html_e( 'Khám phá danh mục', 'aznet-theme' ); ?>
                            <span class="aznet-theme-industrial01-category-card__arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 17 17 7M8 7h9v9"/></svg>
                            </span>
                        </span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

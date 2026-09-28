<?php
/**
 * Industrial 01 product categories.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$terms = [];
if ( taxonomy_exists( 'product_cat' ) ) {
    $result = get_terms(
        [
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'number'     => 8,
            'parent'     => 0,
        ]
    );
    if ( ! is_wp_error( $result ) && is_array( $result ) ) {
        $terms = $result;
    }
}

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
                <a class="aznet-theme-industrial01-card aznet-theme-industrial01-category-card" href="<?php echo esc_url( $link ); ?>">
                    <strong><?php echo esc_html( $term->name ); ?></strong>
                    <?php if ( ! empty( $term->description ) ) : ?>
                        <span><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $term->description ), 18 ) ); ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

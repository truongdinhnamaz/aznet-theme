<?php
/**
 * Industrial 01 quote/contact CTA.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page = homepage_page_reference( (int) homepage_effective_source_value( 'industrial-01', 'contact' ) );
$model = function_exists( __NAMESPACE__ . '\\contact_surface_model' ) ? contact_surface_model() : null;

$phone = '';
if ( is_array( $model ) ) {
    foreach ( (array) ( $model['contact']['points'] ?? [] ) as $point ) {
        if ( is_array( $point ) && 'phone' === ( $point['kind'] ?? '' ) ) {
            $candidate = trim( (string) ( $point['value'] ?? '' ) );
            if ( '' !== $candidate ) {
                $phone = $candidate;
                break;
            }
        }
    }
}

$summary = $page instanceof \WP_Post ? trim( (string) get_the_excerpt( $page ) ) : '';
if ( '' === $summary ) {
    $summary = __( 'Gửi nhu cầu, mã sản phẩm hoặc thông số đang quan tâm để trao đổi với đầu mối phụ trách của website.', 'aznet-theme' );
}
?>
<section id="aznet-industrial01-quote" data-aznet-homepage-surface="cta" class="aznet-theme-industrial01-section aznet-theme-industrial01-cta" aria-labelledby="aznet-industrial01-cta-title">
    <div class="aznet-theme-industrial01-shell aznet-theme-industrial01-cta__inner">
        <div>
            <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Tư vấn kỹ thuật', 'aznet-theme' ); ?></p>
            <h2 id="aznet-industrial01-cta-title"><?php echo esc_html( preset_term( 'primary_cta', __( 'Yêu cầu báo giá', 'aznet-theme' ), 'industrial-01' ) ); ?></h2>
            <p class="aznet-theme-industrial01-lede"><?php echo esc_html( $summary ); ?></p>
        </div>
        <div class="aznet-theme-industrial01-cta__actions">
            <?php if ( $page instanceof \WP_Post ) : ?>
                <a class="aznet-theme-industrial01-button aznet-theme-industrial01-button--light" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Gửi yêu cầu tư vấn', 'aznet-theme' ); ?></a>
            <?php endif; ?>
            <?php if ( '' !== $phone ) : ?>
                <a class="aznet-theme-industrial01-cta__phone" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>

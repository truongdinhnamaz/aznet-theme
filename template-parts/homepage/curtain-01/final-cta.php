<?php
/**
 * Curtain 01 final contact handoff.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page = homepage_page_reference( (int) setting( 'homepage_contact_page', 0 ) );
$model = function_exists( __NAMESPACE__ . '\\contact_surface_model' ) ? contact_surface_model() : null;

$phone = '';
if ( is_array( $model ) ) {
    foreach ( (array) ( $model['contact']['points'] ?? [] ) as $point ) {
        if ( ! is_array( $point ) || 'phone' !== ( $point['kind'] ?? '' ) ) {
            continue;
        }
        $candidate = trim( (string) ( $point['value'] ?? '' ) );
        if ( '' !== $candidate ) {
            $phone = $candidate;
            break;
        }
    }
}

if ( ! $page instanceof \WP_Post && '' === $phone ) {
    return;
}

$summary = $page instanceof \WP_Post ? trim( (string) get_the_excerpt( $page ) ) : '';
?>
<section class="aznet-theme-curtain01-section aznet-theme-curtain01-final-cta" aria-labelledby="aznet-curtain01-cta-title">
    <div class="aznet-theme-curtain01-shell aznet-theme-curtain01-final-cta__inner">
        <div>
            <p class="aznet-theme-curtain01-kicker aznet-theme-curtain01-kicker--inverse"><?php esc_html_e( 'Bắt đầu từ không gian của bạn', 'aznet-theme' ); ?></p>
            <h2 id="aznet-curtain01-cta-title"><?php esc_html_e( 'Trao đổi để tìm giải pháp rèm phù hợp', 'aznet-theme' ); ?></h2>
            <?php if ( '' !== $summary ) : ?><p><?php echo esc_html( $summary ); ?></p><?php endif; ?>
        </div>
        <div class="aznet-theme-curtain01-actions">
            <?php if ( $page instanceof \WP_Post ) : ?>
                <a class="aznet-theme-curtain01-button aznet-theme-curtain01-button--light" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Gửi yêu cầu tư vấn', 'aznet-theme' ); ?></a>
            <?php endif; ?>
            <?php if ( '' !== $phone ) : ?>
                <a class="aznet-theme-curtain01-phone" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>

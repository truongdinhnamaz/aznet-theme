<?php
/** Law 01 final contact handoff. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$page = homepage_page_reference( (int) setting( 'homepage_contact_page', 0 ) );
$model = function_exists( __NAMESPACE__ . '\\contact_surface_model' ) ? contact_surface_model() : null;
$phone = '';
if ( is_array( $model ) ) {
    foreach ( (array) ( $model['contact']['points'] ?? [] ) as $point ) {
        if ( is_array( $point ) && 'phone' === ( $point['kind'] ?? '' ) && '' !== trim( (string) ( $point['value'] ?? '' ) ) ) {
            $phone = trim( (string) $point['value'] );
            break;
        }
    }
}
if ( ! $page instanceof \WP_Post && '' === $phone ) { return; }
?>
<section class="aznet-theme-law01-section aznet-theme-law01-final-cta" aria-labelledby="aznet-law01-cta-title">
<div class="aznet-theme-law01-container">
<div class="aznet-theme-law01-final-cta__inner">
<div class="aznet-theme-law01-final-cta__copy">
<p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Tư vấn pháp lý', 'aznet-theme' ); ?></p>
<h2 id="aznet-law01-cta-title"><?php esc_html_e( 'Bạn đang cần hỗ trợ về một vấn đề pháp lý?', 'aznet-theme' ); ?></h2>
<p><?php esc_html_e( 'Trao đổi với đội ngũ để xác định hướng xử lý phù hợp cho trường hợp của bạn.', 'aznet-theme' ); ?></p>
</div>
<div class="aznet-theme-law01-actions aznet-theme-law01-final-cta__actions">
<?php if ( $page instanceof \WP_Post ) : ?><a class="aznet-theme-law01-button" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a><?php endif; ?>
<?php if ( '' !== $phone ) : ?><a class="aznet-theme-law01-button aznet-theme-law01-button--inverse" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><?php endif; ?>
</div>
</div>
</div>
</section>

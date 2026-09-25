<?php
/** Law 01 final contact handoff. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$page = homepage_page_reference( (int) homepage_source_value( 'law-01', 'contact' ) );
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
$heading = $page instanceof \WP_Post ? trim( (string) get_the_title( $page ) ) : __( 'Liên hệ', 'aznet-theme' );
$summary = $page instanceof \WP_Post ? trim( (string) get_the_excerpt( $page ) ) : '';
?>
<section id="aznet-homepage-contact" data-aznet-homepage-surface="final-cta" class="aznet-theme-law01-section aznet-theme-law01-final-cta" aria-labelledby="aznet-law01-cta-title"><div class="aznet-theme-law01-container"><h2 id="aznet-law01-cta-title"><?php echo esc_html( $heading ); ?></h2><?php if ( '' !== $summary ) : ?><p><?php echo esc_html( $summary ); ?></p><?php endif; ?><div class="aznet-theme-law01-actions"><?php if ( $page instanceof \WP_Post ) : ?><a class="aznet-theme-law01-button" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?></a><?php endif; ?><?php if ( '' !== $phone ) : ?><a class="aznet-theme-law01-button aznet-theme-law01-button--inverse" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><?php endif; ?></div></div></section>

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

// Technical B2B must not render an unactionable quote CTA when contact is unmapped.
// WooCommerce is the public catalogue owner; this is presentation-only fallback.
$is_technical_b2b = 'technical-b2b' === setting( 'homepage_industrial01_variant', 'standard' );
$shop_url = $is_technical_b2b && function_exists( 'AZnet\Theme\Integrations\WooCommerce\shop_url' )
    ? (string) \AZnet\Theme\Integrations\WooCommerce\shop_url()
    : '';
$fallback_to_shop = $is_technical_b2b && ! ( $page instanceof \WP_Post ) && '' === $phone && '' !== $shop_url;
if ( $is_technical_b2b && ! ( $page instanceof \WP_Post ) && '' === $phone && ! $fallback_to_shop ) {
    // Provider absent: hide the non-actionable CTA; never invent a phone or form.
    return;
}

// Avoid auto-generated excerpts that pull Contact Form labels into the Homepage CTA.
$summary = $page instanceof \WP_Post ? trim( (string) $page->post_excerpt ) : '';
if ( '' === $summary ) {
    $summary = __( 'Gửi nhu cầu, mã sản phẩm hoặc thông số đang quan tâm để trao đổi với đầu mối phụ trách của website.', 'aznet-theme' );
}
if ( $fallback_to_shop ) {
    $summary = __( 'Xem các sản phẩm và thông tin đã công bố để xác định lựa chọn phù hợp.', 'aznet-theme' );
}
$cta_heading = $fallback_to_shop
    ? __( 'Khám phá sản phẩm', 'aznet-theme' )
    : preset_term( 'primary_cta', __( 'Yêu cầu báo giá', 'aznet-theme' ), 'industrial-01' );
?>
<section id="aznet-industrial01-quote" data-aznet-homepage-surface="cta" class="aznet-theme-industrial01-section aznet-theme-industrial01-cta" aria-labelledby="aznet-industrial01-cta-title">
    <div class="aznet-theme-industrial01-shell aznet-theme-industrial01-cta__inner">
        <div>
            <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Tư vấn kỹ thuật', 'aznet-theme' ); ?></p>
            <h2 id="aznet-industrial01-cta-title"><?php echo esc_html( $cta_heading ); ?></h2>
            <p class="aznet-theme-industrial01-lede"><?php echo esc_html( $summary ); ?></p>
        </div>
        <div class="aznet-theme-industrial01-cta__actions">
            <?php if ( $page instanceof \WP_Post ) : ?>
                <a class="aznet-theme-industrial01-button aznet-theme-industrial01-button--light" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Gửi yêu cầu tư vấn', 'aznet-theme' ); ?></a>
            <?php endif; ?>
            <?php if ( $fallback_to_shop ) : ?>
                <a class="aznet-theme-industrial01-button aznet-theme-industrial01-button--light" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Xem sản phẩm', 'aznet-theme' ); ?></a>
            <?php endif; ?>
            <?php if ( '' !== $phone ) : ?>
                <a class="aznet-theme-industrial01-cta__phone" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>

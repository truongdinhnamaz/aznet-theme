<?php
/**
 * Industrial 01 quote CTA.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<section id="aznet-industrial01-quote" class="aznet-theme-industrial01-section aznet-theme-industrial01-cta">
    <div class="aznet-theme-industrial01-shell aznet-theme-industrial01-cta__inner">
        <div>
            <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Tư vấn kỹ thuật', 'aznet-theme' ); ?></p>
            <h2><?php echo esc_html( \AZnet\Theme\preset_term( 'primary_cta', __( 'Yêu cầu báo giá', 'aznet-theme' ), 'industrial-01' ) ); ?></h2>
            <p class="aznet-theme-industrial01-lede"><?php esc_html_e( 'Gửi nhu cầu, mã sản phẩm hoặc thông số cần tư vấn để đội ngũ phụ trách phản hồi theo thông tin liên hệ của website.', 'aznet-theme' ); ?></p>
        </div>
    </div>
</section>

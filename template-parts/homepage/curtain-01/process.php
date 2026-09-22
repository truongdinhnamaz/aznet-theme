<?php
/**
 * Curtain 01 process rail.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page = homepage_page_reference( (int) setting( 'homepage_process_page', 0 ) );
if ( ! $page instanceof \WP_Post ) {
    return;
}

$raw = trim( (string) $page->post_content );
if ( '' === $raw ) {
    return;
}

$content = apply_filters( 'the_content', $raw );
if ( '' === trim( (string) $content ) ) {
    return;
}
?>
<section class="aznet-theme-curtain01-section aznet-theme-curtain01-process" aria-labelledby="aznet-curtain01-process-title">
    <div class="aznet-theme-curtain01-shell">
        <div class="aznet-theme-curtain01-section-heading aznet-theme-curtain01-process__heading">
            <div>
                <p class="aznet-theme-curtain01-kicker"><?php esc_html_e( 'Quy trình tư vấn & triển khai', 'aznet-theme' ); ?></p>
                <h2 id="aznet-curtain01-process-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            </div>
        </div>

        <div class="aznet-theme-curtain01-process__rail">
            <?php echo wp_kses_post( $content ); ?>
        </div>
    </div>
</section>

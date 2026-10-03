<?php
/**
 * Industrial 01 mapped process/support page.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page = homepage_page_reference( (int) homepage_effective_source_value( 'industrial-01', 'process' ) );
if ( ! $page instanceof \WP_Post ) {
    return;
}

$raw = trim( (string) $page->post_content );
if ( '' === $raw ) {
    return;
}

$content = trim( (string) apply_filters( 'the_content', $raw ) );
if ( '' === $content ) {
    return;
}
?>
<section id="aznet-homepage-industrial-process" data-aznet-homepage-surface="process" class="aznet-theme-industrial01-section aznet-theme-industrial01-process" aria-labelledby="aznet-industrial01-process-title">
    <div class="aznet-theme-industrial01-shell">
        <div class="aznet-theme-industrial01-heading aznet-theme-industrial01-heading--split">
            <div>
                <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Dịch vụ kỹ thuật', 'aznet-theme' ); ?></p>
                <h2 id="aznet-industrial01-process-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            </div>
            <a class="aznet-theme-industrial01-text-link" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Xem đầy đủ dịch vụ', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
        </div>
        <div class="aznet-theme-industrial01-process__content">
            <?php echo wp_kses_post( $content ); ?>
        </div>
    </div>
</section>

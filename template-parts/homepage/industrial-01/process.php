<?php
/**
 * Industrial 01 mapped technical-service summary.
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
$summary_source = '' !== trim( (string) $page->post_excerpt ) ? (string) $page->post_excerpt : strip_shortcodes( $raw );
$summary = trim( wp_strip_all_tags( $summary_source ) );
$summary = '' !== $summary ? wp_trim_words( $summary, 44, '…' ) : '';

$service_labels = [];
if ( '' !== $content && preg_match_all( '/<h3[^>]*>(.*?)<\/h3>/is', $content, $matches ) ) {
    foreach ( array_slice( (array) ( $matches[1] ?? [] ), 0, 4 ) as $heading ) {
        $label = trim( wp_strip_all_tags( (string) $heading ) );
        if ( '' !== $label ) {
            $service_labels[] = $label;
        }
    }
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
        <?php if ( '' !== $summary ) : ?><p class="aznet-theme-industrial01-process__summary"><?php echo esc_html( $summary ); ?></p><?php endif; ?>
        <?php if ( [] !== $service_labels ) : ?>
            <div class="aznet-theme-industrial01-process__grid">
                <?php foreach ( $service_labels as $index => $label ) : ?>
                    <div class="aznet-theme-industrial01-process__card">
                        <span aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
                        <strong><?php echo esc_html( $label ); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
/**
 * Industrial 01 technical services: mapped WordPress Page presentation only.
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

// The page's complete introductory paragraph is preferable to a generated
// excerpt that can end mid-sentence. Never take a service's description after
// a heading and present it as a claim about all technical services.
$opening = (string) ( preg_split( '/<h[2-6]\b/i', $content, 2 )[0] ?? '' );
$summary_source = '';
if ( preg_match( '/<p\b[^>]*>(.*?)<\/p>/is', $opening, $intro_match ) ) {
    $summary_source = (string) $intro_match[1];
}
if ( '' === trim( wp_strip_all_tags( $summary_source ) ) ) {
    $summary_source = (string) $page->post_excerpt;
}
$summary = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $summary_source ) ) );

$service_labels = [];
if ( '' !== $content && preg_match_all( '/<h3[^>]*>(.*?)<\/h3>/is', $content, $matches ) ) {
    foreach ( array_slice( (array) ( $matches[1] ?? [] ), 0, 4 ) as $heading ) {
        $label = trim( wp_strip_all_tags( (string) $heading ) );
        if ( '' !== $label ) {
            $service_labels[] = $label;
        }
    }
}

// Icons are decorative and position-based, not service routing or promises.
$icon_paths = [
    'M4 7h16M7 4v6M4 17h16M16 14v6',
    'M14.5 6.5a4 4 0 0 0-5 5L4 17a2 2 0 0 0 3 3l5.5-5.5a4 4 0 0 0 5-5l-3 3-3-3 3-3Z',
    'M12 2 4 6v6c0 5 3.5 8 8 10 4.5-2 8-5 8-10V6l-8-4ZM9 12l2 2 4-4',
    'M4 17l6-6m4-4 6-6M8 4l12 12M4 20l4-4',
];
?>
<section id="aznet-homepage-industrial-process" data-aznet-homepage-surface="process" class="aznet-theme-industrial01-section aznet-theme-industrial01-process" aria-labelledby="aznet-industrial01-process-title">
    <div class="aznet-theme-industrial01-shell">
        <div class="aznet-theme-industrial01-process__heading">
            <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Đồng hành kỹ thuật', 'aznet-theme' ); ?></p>
            <h2 id="aznet-industrial01-process-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            <?php if ( '' !== $summary ) : ?>
                <p class="aznet-theme-industrial01-process__summary"><?php echo esc_html( $summary ); ?></p>
            <?php endif; ?>
        </div>
        <?php if ( [] !== $service_labels ) : ?>
            <div class="aznet-theme-industrial01-process__grid">
                <?php foreach ( $service_labels as $index => $label ) : ?>
                    <div class="aznet-theme-industrial01-process__card">
                        <div class="aznet-theme-industrial01-process__card-top">
                            <span class="aznet-theme-industrial01-process__card-number" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
                            <span class="aznet-theme-industrial01-process__card-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true"><path d="<?php echo esc_attr( $icon_paths[ $index % count( $icon_paths ) ] ); ?>"/></svg>
                            </span>
                        </div>
                        <h3 class="aznet-theme-industrial01-process__card-title"><?php echo esc_html( $label ); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="aznet-theme-industrial01-process__footer">
            <a class="aznet-theme-industrial01-process__all" href="<?php echo esc_url( get_permalink( $page ) ); ?>">
                <?php esc_html_e( 'Xem đầy đủ dịch vụ', 'aznet-theme' ); ?>
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 17 17 7M8 7h9v9"/></svg>
            </a>
        </div>
    </div>
</section>

<?php
/**
 * Industrial 01 source-derived solutions cards.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page = homepage_page_reference( (int) homepage_effective_source_value( 'industrial-01', 'solutions' ) );
if ( ! $page instanceof \WP_Post ) {
    return;
}

$raw = trim( (string) $page->post_content );
if ( '' === $raw ) {
    return;
}

$rendered = trim( (string) apply_filters( 'the_content', $raw ) );
$solution_labels = [];
if ( '' !== $rendered && preg_match_all( '/<h3[^>]*>(.*?)<\/h3>/is', $rendered, $matches ) ) {
    foreach ( array_slice( (array) ( $matches[1] ?? [] ), 0, 4 ) as $heading ) {
        $label = trim( wp_strip_all_tags( (string) $heading ) );
        if ( '' !== $label ) {
            $solution_labels[] = $label;
        }
    }
}

if ( [] === $solution_labels ) {
    return;
}
?>
<section id="aznet-homepage-industrial-solutions" data-aznet-homepage-surface="solutions" class="aznet-theme-industrial01-section aznet-theme-industrial01-solutions" aria-labelledby="aznet-industrial01-solutions-title">
    <div class="aznet-theme-industrial01-shell">
        <div class="aznet-theme-industrial01-heading aznet-theme-industrial01-heading--split">
            <div>
                <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Theo nhu cầu sản xuất', 'aznet-theme' ); ?></p>
                <h2 id="aznet-industrial01-solutions-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            </div>
            <a class="aznet-theme-industrial01-text-link" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Khám phá giải pháp', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
        </div>
        <div class="aznet-theme-industrial01-solutions__grid">
            <?php foreach ( $solution_labels as $index => $label ) : ?>
                <a class="aznet-theme-industrial01-solutions__card" href="<?php echo esc_url( get_permalink( $page ) ); ?>">
                    <span aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
                    <strong><?php echo esc_html( $label ); ?></strong>
                    <span class="aznet-theme-industrial01-solutions__arrow" aria-hidden="true">→</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

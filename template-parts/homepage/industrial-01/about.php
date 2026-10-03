<?php
/**
 * Industrial 01 About.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page = homepage_page_reference( (int) homepage_effective_source_value( 'industrial-01', 'about' ) );
if ( ! $page instanceof \WP_Post ) {
    return;
}

$summary = trim( (string) $page->post_excerpt );
if ( '' === $summary ) {
    $summary = homepage_surface_text_summary( (string) apply_filters( 'the_content', $page->post_content ), 55 );
}
$image = has_post_thumbnail( $page )
    ? get_the_post_thumbnail( $page, 'large', [ 'class' => 'aznet-theme-industrial01-about__image', 'loading' => 'lazy' ] )
    : '';
?>
<section id="aznet-homepage-industrial-about" data-aznet-homepage-surface="about" class="aznet-theme-industrial01-section aznet-theme-industrial01-about" aria-labelledby="aznet-industrial01-about-title">
    <div class="aznet-theme-industrial01-shell aznet-theme-industrial01-about__grid<?php echo '' === $image ? ' aznet-theme-industrial01-about__grid--text' : ''; ?>">
        <?php if ( '' !== $image ) : ?>
            <div class="aznet-theme-industrial01-about__media"><?php echo wp_kses_post( $image ); ?></div>
        <?php endif; ?>
        <div class="aznet-theme-industrial01-about__content">
            <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Năng lực & đồng hành', 'aznet-theme' ); ?></p>
            <h2 id="aznet-industrial01-about-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            <?php if ( '' !== $summary ) : ?><p class="aznet-theme-industrial01-lede"><?php echo esc_html( $summary ); ?></p><?php endif; ?>
            <a class="aznet-theme-industrial01-text-link aznet-theme-industrial01-about__link" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Tìm hiểu thêm', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>

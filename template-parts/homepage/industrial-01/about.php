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

$summary_source = trim( (string) $page->post_excerpt );
if ( '' === $summary_source ) {
    $summary_source = trim( (string) $page->post_content );
}
$summary_source = strip_shortcodes( $summary_source );
$summary_source = (string) preg_replace( '/\[(?:\/)?[A-Za-z0-9_-]+(?:\s[^\]]*)?\]/', ' ', $summary_source );
$summary = trim( wp_strip_all_tags( $summary_source ) );
$summary = (string) preg_replace( '/\s+/u', ' ', $summary );
$site_name = trim( (string) get_bloginfo( 'name' ) );
$leading_phrases = [
    trim( (string) get_the_title( $page ) ),
    '' !== $site_name ? sprintf( __( 'Giới thiệu về %s', 'aznet-theme' ), $site_name ) : '',
];
foreach ( $leading_phrases as $leading_phrase ) {
    if ( '' !== $leading_phrase ) {
        $summary = (string) preg_replace( '/^' . preg_quote( $leading_phrase, '/' ) . '\s*/iu', '', $summary, 1 );
    }
}
$summary = '' !== $summary ? wp_trim_words( $summary, 55, '…' ) : '';
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

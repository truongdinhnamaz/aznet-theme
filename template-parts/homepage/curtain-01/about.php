<?php
/**
 * Curtain 01 About.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page = homepage_page_reference( (int) setting( 'homepage_about_page', 0 ) );
if ( ! $page instanceof \WP_Post ) {
    return;
}

$summary = trim( (string) get_the_excerpt( $page ) );
if ( '' === $summary ) {
    $plain = trim( wp_strip_all_tags( strip_shortcodes( (string) $page->post_content ) ) );
    $summary = '' !== $plain ? wp_trim_words( $plain, 42, '…' ) : '';
}

$image = has_post_thumbnail( $page )
    ? get_the_post_thumbnail( $page, 'large', [ 'class' => 'aznet-theme-curtain01-about__image' ] )
    : '';
?>
<section class="aznet-theme-curtain01-section aznet-theme-curtain01-about" aria-labelledby="aznet-curtain01-about-title">
    <div class="aznet-theme-curtain01-shell aznet-theme-curtain01-about__grid<?php echo '' === $image ? ' aznet-theme-curtain01-about__grid--text' : ''; ?>">
        <div class="aznet-theme-curtain01-about__content">
            <p class="aznet-theme-curtain01-kicker"><?php esc_html_e( 'Câu chuyện thương hiệu', 'aznet-theme' ); ?></p>
            <h2 id="aznet-curtain01-about-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            <?php if ( '' !== $summary ) : ?>
                <p class="aznet-theme-curtain01-lede"><?php echo esc_html( $summary ); ?></p>
            <?php endif; ?>
            <a class="aznet-theme-curtain01-text-link" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Tìm hiểu thêm', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
        </div>
        <?php if ( '' !== $image ) : ?>
            <div class="aznet-theme-curtain01-about__media"><?php echo wp_kses_post( $image ); ?></div>
        <?php endif; ?>
    </div>
</section>

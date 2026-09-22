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

$summary = trim( (string) $page->post_excerpt );
$kicker = trim( (string) setting( 'homepage_about_kicker', '' ) );
$heading = trim( (string) setting( 'homepage_about_heading', '' ) );
$quote = trim( (string) setting( 'homepage_about_quote', '' ) );
$image_id = (int) setting( 'homepage_about_image', 0 );

if ( '' === $kicker ) {
    $kicker = __( 'Hành trình thương hiệu', 'aznet-theme' );
}

if ( '' === $heading ) {
    $heading = get_the_title( $page );
}

if ( $image_id > 0 && wp_attachment_is_image( $image_id ) ) {
    $image = wp_get_attachment_image(
        $image_id,
        'large',
        false,
        [
            'class' => 'aznet-theme-curtain01-about__image',
            'alt'   => get_the_title( $page ),
        ]
    );
} else {
    $image = has_post_thumbnail( $page )
        ? get_the_post_thumbnail( $page, 'large', [ 'class' => 'aznet-theme-curtain01-about__image' ] )
        : '';
}
?>
<section class="aznet-theme-curtain01-section aznet-theme-curtain01-about" aria-labelledby="aznet-curtain01-about-title">
    <div class="aznet-theme-curtain01-shell aznet-theme-curtain01-about__history<?php echo '' === $image ? ' aznet-theme-curtain01-about__history--text' : ''; ?>">
        <?php if ( '' !== $image ) : ?>
            <div class="aznet-theme-curtain01-about__media">
                <div class="aznet-theme-curtain01-about__media-frame"><?php echo wp_kses_post( $image ); ?></div>
            </div>
        <?php endif; ?>
        <div class="aznet-theme-curtain01-about__content">
            <p class="aznet-theme-curtain01-kicker"><?php echo esc_html( $kicker ); ?></p>
            <h2 id="aznet-curtain01-about-title"><?php echo esc_html( $heading ); ?></h2>
            <?php if ( '' !== $summary ) : ?>
                <p class="aznet-theme-curtain01-lede"><?php echo esc_html( $summary ); ?></p>
            <?php endif; ?>
            <?php if ( '' !== $quote ) : ?>
                <blockquote class="aznet-theme-curtain01-about__quote"><p><?php echo esc_html( $quote ); ?></p></blockquote>
            <?php endif; ?>
            <a class="aznet-theme-curtain01-text-link aznet-theme-curtain01-about__story-link" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Đọc câu chuyện thương hiệu', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>

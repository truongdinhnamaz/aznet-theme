<?php
/** Law 01 Hero. */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

$front_id = (int) get_the_ID();
$title = trim( (string) get_the_title( $front_id ) );
$excerpt = trim( (string) get_the_excerpt( $front_id ) );
$contact = homepage_page_reference( (int) setting( 'homepage_contact_page', 0 ) );
$image = has_post_thumbnail( $front_id ) ? get_the_post_thumbnail( $front_id, 'large', [ 'class' => 'aznet-theme-law01-hero__image' ] ) : '';
if ( '' === $title ) { return; }
?>
<section class="aznet-theme-law01-section aznet-theme-law01-hero" aria-labelledby="aznet-law01-title">
    <div class="aznet-theme-law01-container aznet-theme-law01-hero__grid<?php echo '' === $image ? ' aznet-theme-law01-hero__grid--text' : ''; ?>">
        <div class="aznet-theme-law01-hero__content">
            <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Tư vấn pháp lý chuyên nghiệp', 'aznet-theme' ); ?></p>
            <h1 id="aznet-law01-title"><?php echo esc_html( $title ); ?></h1>
            <?php if ( '' !== $excerpt ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
            <?php if ( $contact instanceof \WP_Post ) : ?>
                <p class="aznet-theme-law01-actions"><a class="aznet-theme-law01-button" href="<?php echo esc_url( get_permalink( $contact ) ); ?>"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?></a></p>
            <?php endif; ?>
        </div>
        <?php if ( '' !== $image ) : ?><div class="aznet-theme-law01-hero__media"><?php echo wp_kses_post( $image ); ?></div><?php endif; ?>
    </div>
</section>

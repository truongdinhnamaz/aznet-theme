<?php
/** Law 01 Hero. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$front_id = (int) get_the_ID();
$title = trim( (string) get_the_title( $front_id ) );
$excerpt = trim( (string) get_the_excerpt( $front_id ) );
$slogan = trim( (string) get_bloginfo( 'description' ) );
$contact = homepage_page_reference( (int) setting( 'homepage_contact_page', 0 ) );
$services = homepage_page_reference( (int) setting( 'homepage_services_page', 0 ) );
$image = has_post_thumbnail( $front_id ) ? get_the_post_thumbnail( $front_id, 'large', [ 'class' => 'aznet-theme-law01-hero__image' ] ) : '';
if ( '' === $title ) { return; }
$trust_items = [
    __( 'Tư vấn rõ ràng', 'aznet-theme' ),
    __( 'Giải pháp thực tiễn', 'aznet-theme' ),
    __( 'Bảo mật thông tin', 'aznet-theme' ),
    __( 'Đồng hành tận tâm', 'aznet-theme' ),
];
?>
<section class="aznet-theme-law01-section aznet-theme-law01-hero" aria-labelledby="aznet-law01-title">
    <div class="aznet-theme-law01-container aznet-theme-law01-hero__grid<?php echo '' === $image ? ' aznet-theme-law01-hero__grid--text' : ''; ?>">
        <div class="aznet-theme-law01-hero__content">
            <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Văn phòng luật sư', 'aznet-theme' ); ?></p>
            <h1 id="aznet-law01-title"><?php echo esc_html( $title ); ?></h1>
            <?php if ( '' !== $excerpt ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
            <?php if ( '' !== $slogan ) : ?><p class="aznet-theme-law01-hero__quote aznet-theme-law01-hero__slogan"><?php echo esc_html( $slogan ); ?></p><?php endif; ?>
            <?php if ( $contact instanceof \WP_Post || $services instanceof \WP_Post ) : ?>
                <p class="aznet-theme-law01-actions">
                    <?php if ( $contact instanceof \WP_Post ) : ?><a class="aznet-theme-law01-button" href="<?php echo esc_url( get_permalink( $contact ) ); ?>"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a><?php endif; ?>
                    <?php if ( $services instanceof \WP_Post ) : ?><a class="aznet-theme-law01-hero__secondary-action" href="<?php echo esc_url( get_permalink( $services ) ); ?>"><?php esc_html_e( 'Xem dịch vụ', 'aznet-theme' ); ?></a><?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="aznet-theme-law01-hero__visual">
            <?php if ( '' !== $image ) : ?><div class="aznet-theme-law01-hero__media"><?php echo wp_kses_post( $image ); ?></div><?php endif; ?>
        </div>
    </div>
    <div class="aznet-theme-law01-hero__trust" aria-label="<?php esc_attr_e( 'Cam kết dịch vụ', 'aznet-theme' ); ?>">
        <div class="aznet-theme-law01-container aznet-theme-law01-hero__trust-grid">
            <?php foreach ( $trust_items as $index => $item ) : ?>
                <div class="aznet-theme-law01-hero__trust-item"><span class="aznet-theme-law01-hero__trust-mark" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span><span><?php echo esc_html( $item ); ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

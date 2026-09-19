<?php
/** Law 01 Hero. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$hero_block = homepage_block_reference( (int) setting( 'homepage_hero_block', 0 ) );
$hero_variant = (string) setting( 'homepage_hero_variant', 'split' );
if ( ! in_array( $hero_variant, [ 'split', 'centered', 'inverse', 'media-left' ], true ) ) {
    $hero_variant = 'split';
}

$hero_block_html = '';
if ( $hero_block instanceof \WP_Post ) {
    $hero_block_content = trim( (string) $hero_block->post_content );
    $hero_block_html = '' !== $hero_block_content ? do_blocks( $hero_block_content ) : '';
}

$hero = null;
$front_id = (int) get_the_ID();
$lede = '';
$slogan = '';
$title = '';
$value_proposition = '';
$body_html = '';
$image = '';

if ( '' === $hero_block_html ) {
    $hero = homepage_page_reference( (int) setting( 'homepage_hero_page', 0 ) );

    if ( $hero instanceof \WP_Post ) {
        $title = trim( (string) get_the_title( $hero ) );
        $value_proposition = trim( (string) get_the_excerpt( $hero ) );
        $body = trim( (string) $hero->post_content );
        $body_html = '' !== $body ? wpautop( do_blocks( $body ) ) : '';
        $image = has_post_thumbnail( $hero ) ? get_the_post_thumbnail( $hero, 'large', [ 'class' => 'aznet-theme-law01-hero__image' ] ) : '';
    } else {
        // Backward-compatible presentation path for sites upgraded from <= 1.3.3.
        // WordPress still owns every source value; the Theme only composes them.
        $site_title = trim( (string) get_bloginfo( 'name' ) );
        $page_title = trim( (string) get_the_title( $front_id ) );
        $title = '' !== $site_title ? $site_title : $page_title;
        $value_proposition = '' !== $page_title && 0 !== strcasecmp( $page_title, $title ) ? $page_title : '';
        $lede = trim( (string) get_the_excerpt( $front_id ) );
        $slogan = trim( (string) get_bloginfo( 'description' ) );
        $body_html = '';
        $image = has_post_thumbnail( $front_id ) ? get_the_post_thumbnail( $front_id, 'large', [ 'class' => 'aznet-theme-law01-hero__image' ] ) : '';
    }
}

$contact = homepage_page_reference( (int) setting( 'homepage_contact_page', 0 ) );
$services = homepage_page_reference( (int) setting( 'homepage_services_page', 0 ) );
if ( '' === $hero_block_html && '' === $title ) { return; }

$trust_items = [
    __( 'Tư vấn rõ ràng', 'aznet-theme' ),
    __( 'Giải pháp thực tiễn', 'aznet-theme' ),
    __( 'Bảo mật thông tin', 'aznet-theme' ),
    __( 'Đồng hành tận tâm', 'aznet-theme' ),
];

$section_classes = 'aznet-theme-law01-section aznet-theme-law01-hero';
if ( '' !== $hero_block_html ) {
    $section_classes .= ' aznet-theme-law01-hero--library aznet-theme-law01-hero--' . $hero_variant;
}
?>
<section class="<?php echo esc_attr( $section_classes ); ?>" <?php echo '' !== $hero_block_html ? 'aria-label="' . esc_attr__( 'Hero trang chủ', 'aznet-theme' ) . '"' : 'aria-labelledby="aznet-law01-title"'; ?>>
    <?php if ( '' !== $hero_block_html ) : ?>
        <div class="aznet-theme-law01-container aznet-theme-law01-hero__library-content">
            <?php echo wp_kses_post( $hero_block_html ); ?>
        </div>
    <?php else : ?>
        <div class="aznet-theme-law01-container aznet-theme-law01-hero__grid<?php echo '' === $image ? ' aznet-theme-law01-hero__grid--text' : ''; ?>">
            <div class="aznet-theme-law01-hero__content">
                <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Văn phòng luật sư', 'aznet-theme' ); ?></p>
                <h1 id="aznet-law01-title"><?php echo esc_html( $title ); ?></h1>
                <?php if ( '' !== $value_proposition ) : ?><p class="aznet-theme-law01-hero__value"><?php echo esc_html( $value_proposition ); ?></p><?php endif; ?>
                <?php if ( '' !== $lede ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
                <?php if ( '' !== $slogan ) : ?><p class="aznet-theme-law01-hero__quote aznet-theme-law01-hero__slogan"><?php echo esc_html( $slogan ); ?></p><?php endif; ?>
                <?php if ( '' !== $body_html ) : ?><div class="aznet-theme-law01-hero__body"><?php echo wp_kses_post( $body_html ); ?></div><?php endif; ?>
                <?php if ( $contact instanceof \WP_Post || $services instanceof \WP_Post ) : ?>
                    <p class="aznet-theme-law01-actions">
                        <?php if ( $contact instanceof \WP_Post ) : ?><a class="aznet-theme-law01-button" href="<?php echo esc_url( get_permalink( $contact ) ); ?>"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a><?php endif; ?>
                        <?php if ( $services instanceof \WP_Post ) : ?><a class="aznet-theme-law01-hero__secondary-action" href="<?php echo esc_url( get_permalink( $services ) ); ?>"><?php esc_html_e( 'Xem dịch vụ', 'aznet-theme' ); ?></a><?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if ( true === setting( 'header_utilities', true ) && has_nav_menu( 'header-utility' ) ) : ?>
                    <nav class="aznet-theme-law01-hero__contact-nav" aria-label="<?php echo esc_attr__( 'Liên hệ nhanh', 'aznet-theme' ); ?>">
                        <?php
                        wp_nav_menu(
                            [
                                'theme_location' => 'header-utility',
                                'container'      => false,
                                'menu_class'     => 'aznet-theme-law01-hero__contact-list',
                                'menu_id'        => 'aznet-theme-law01-hero-contact-menu',
                                'fallback_cb'    => false,
                                'depth'          => 1,
                            ]
                        );
                        ?>
                    </nav>
                <?php endif; ?>
            </div>
            <div class="aznet-theme-law01-hero__visual">
                <?php if ( '' !== $image ) : ?><div class="aznet-theme-law01-hero__media"><?php echo wp_kses_post( $image ); ?></div><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="aznet-theme-law01-hero__trust" aria-label="<?php esc_attr_e( 'Cam kết dịch vụ', 'aznet-theme' ); ?>">
        <div class="aznet-theme-law01-container aznet-theme-law01-hero__trust-grid">
            <?php foreach ( $trust_items as $index => $item ) : ?>
                <div class="aznet-theme-law01-hero__trust-item">
                    <span class="aznet-theme-law01-hero__trust-icon" aria-hidden="true">
                        <?php if ( 0 === $index ) : ?>
                            <svg viewBox="0 0 24 24" focusable="false"><path d="M4 4h16v11H8l-4 4V4Zm4 5h8M8 12h5"/></svg>
                        <?php elseif ( 1 === $index ) : ?>
                            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 3 4 7v5c0 4.5 3.4 7.7 8 9 4.6-1.3 8-4.5 8-9V7l-8-4Zm-3 9 2 2 4-4"/></svg>
                        <?php elseif ( 2 === $index ) : ?>
                            <svg viewBox="0 0 24 24" focusable="false"><path d="M7 10V8a5 5 0 0 1 10 0v2m-11 0h12v10H6V10Zm6 4v3"/></svg>
                        <?php else : ?>
                            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm-4-9 2.5 2.5L16 9"/></svg>
                        <?php endif; ?>
                    </span>
                    <span><?php echo esc_html( $item ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

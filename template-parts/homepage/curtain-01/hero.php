<?php
/**
 * Curtain 01 Hero.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$hero_block = homepage_block_reference( (int) setting( 'homepage_hero_block', 0 ) );
$hero_html = '';

if ( $hero_block instanceof \WP_Post ) {
    $raw = trim( (string) $hero_block->post_content );
    $hero_html = '' !== $raw ? do_blocks( $raw ) : '';
}

if ( '' !== $hero_html ) :
    ?>
    <section class="aznet-theme-curtain01-section aznet-theme-curtain01-hero aznet-theme-curtain01-hero--library" aria-label="<?php echo esc_attr__( 'Hero trang chủ', 'aznet-theme' ); ?>">
        <div class="aznet-theme-curtain01-shell aznet-theme-curtain01-hero__library">
            <?php echo wp_kses_post( $hero_html ); ?>
        </div>
    </section>
    <?php
    return;
endif;

$front_id = (int) get_the_ID();
$title = trim( (string) get_bloginfo( 'name' ) );
if ( '' === $title && $front_id > 0 ) {
    $title = trim( (string) get_the_title( $front_id ) );
}

$lede = $front_id > 0 ? trim( (string) get_the_excerpt( $front_id ) ) : '';
if ( '' === $lede ) {
    $lede = trim( (string) get_bloginfo( 'description' ) );
}

$image = $front_id > 0 && has_post_thumbnail( $front_id )
    ? get_the_post_thumbnail(
        $front_id,
        'full',
        [
            'class'   => 'aznet-theme-curtain01-hero__image',
            'loading' => 'eager',
        ]
    )
    : '';

$contact = homepage_page_reference( (int) setting( 'homepage_contact_page', 0 ) );
$shop_url = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\shop_url' )
    ? \AZnet\Theme\Integrations\WooCommerce\shop_url()
    : '';

if ( '' === $title && '' === $lede && '' === $image ) {
    return;
}
?>
<section class="aznet-theme-curtain01-section aznet-theme-curtain01-hero" aria-labelledby="aznet-curtain01-hero-title">
    <div class="aznet-theme-curtain01-shell aznet-theme-curtain01-hero__grid<?php echo '' === $image ? ' aznet-theme-curtain01-hero__grid--text' : ''; ?>">
        <div class="aznet-theme-curtain01-hero__content">
            <?php if ( '' !== $title ) : ?>
                <h1 id="aznet-curtain01-hero-title"><?php echo esc_html( $title ); ?></h1>
            <?php endif; ?>
            <?php if ( '' !== $lede ) : ?>
                <p class="aznet-theme-curtain01-lede"><?php echo esc_html( $lede ); ?></p>
            <?php endif; ?>
            <?php if ( $contact instanceof \WP_Post || '' !== $shop_url ) : ?>
                <div class="aznet-theme-curtain01-actions">
                    <?php if ( $contact instanceof \WP_Post ) : ?>
                        <a class="aznet-theme-curtain01-button" href="<?php echo esc_url( get_permalink( $contact ) ); ?>"><?php esc_html_e( 'Nhận tư vấn', 'aznet-theme' ); ?></a>
                    <?php endif; ?>
                    <?php if ( '' !== $shop_url ) : ?>
                        <a class="aznet-theme-curtain01-text-link" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Xem sản phẩm', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ( '' !== $image ) : ?>
            <div class="aznet-theme-curtain01-hero__media"><?php echo wp_kses_post( $image ); ?></div>
        <?php endif; ?>
    </div>
</section>

<?php
/**
 * Industrial 01 Hero.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$site_name = get_bloginfo( 'name' );
$tagline   = get_bloginfo( 'description' );
$shop_url  = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\shop_url' )
    ? \AZnet\Theme\Integrations\WooCommerce\shop_url()
    : '';
?>
<section class="aznet-theme-industrial01-section aznet-theme-industrial01-hero">
    <div class="aznet-theme-industrial01-shell aznet-theme-industrial01-hero__grid">
        <div class="aznet-theme-industrial01-hero__content">
            <p class="aznet-theme-industrial01-kicker"><?php echo esc_html( \AZnet\Theme\preset_term( 'catalog', __( 'Thiết bị & phụ kiện', 'aznet-theme' ), 'industrial-01' ) ); ?></p>
            <h1><?php echo esc_html( $site_name ); ?></h1>
            <?php if ( '' !== trim( (string) $tagline ) ) : ?>
                <p class="aznet-theme-industrial01-lede"><?php echo esc_html( $tagline ); ?></p>
            <?php endif; ?>
            <div class="aznet-theme-industrial01-actions">
                <a class="aznet-theme-industrial01-button" href="<?php echo esc_url( '' !== $shop_url ? $shop_url : home_url( '/' ) ); ?>">
                    <?php echo esc_html( \AZnet\Theme\preset_term( 'primary_group', __( 'Danh mục thiết bị', 'aznet-theme' ), 'industrial-01' ) ); ?>
                </a>
                <a class="aznet-theme-industrial01-text-link" href="#aznet-industrial01-quote">
                    <?php echo esc_html( \AZnet\Theme\preset_term( 'primary_cta', __( 'Yêu cầu báo giá', 'aznet-theme' ), 'industrial-01' ) ); ?>
                </a>
            </div>
        </div>
        <div class="aznet-theme-industrial01-hero__visual" aria-hidden="true">
            <div class="aznet-theme-industrial01-hero__panel"></div>
        </div>
    </div>
</section>

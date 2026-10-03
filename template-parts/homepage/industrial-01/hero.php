<?php
/**
 * Industrial 01 Hero.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$site_name = trim( (string) get_bloginfo( 'name' ) );
$tagline   = trim( (string) get_bloginfo( 'description' ) );
$shop_url  = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\shop_url' )
    ? \AZnet\Theme\Integrations\WooCommerce\shop_url()
    : '';

$kicker = trim( (string) \AZnet\Theme\setting( 'homepage_industrial01_hero_kicker', '' ) );
$title  = trim( (string) \AZnet\Theme\setting( 'homepage_industrial01_hero_title', '' ) );
$lede   = trim( (string) \AZnet\Theme\setting( 'homepage_industrial01_hero_lede', '' ) );

$primary_label = trim( (string) \AZnet\Theme\setting( 'homepage_industrial01_hero_primary_label', '' ) );
$primary_url   = trim( (string) \AZnet\Theme\setting( 'homepage_industrial01_hero_primary_url', '' ) );
$secondary_label = trim( (string) \AZnet\Theme\setting( 'homepage_industrial01_hero_secondary_label', '' ) );
$secondary_url   = trim( (string) \AZnet\Theme\setting( 'homepage_industrial01_hero_secondary_url', '' ) );

if ( '' === $kicker ) {
    $kicker = \AZnet\Theme\preset_term( 'catalog', __( 'Thiết bị & phụ kiện', 'aznet-theme' ), 'industrial-01' );
}
if ( '' === $title ) {
    $title = $site_name;
}
if ( '' === $lede ) {
    $lede = $tagline;
}
if ( '' === $lede ) {
    $about_page = \AZnet\Theme\homepage_page_reference( (int) \AZnet\Theme\homepage_effective_source_value( 'industrial-01', 'about' ) );
    if ( $about_page instanceof \WP_Post ) {
        $about_copy = strip_shortcodes( (string) $about_page->post_content );
        $about_copy = (string) preg_replace( '/\[(?:\/)?[A-Za-z0-9_-]+(?:\s[^\]]*)?\]/', ' ', $about_copy );
        $about_copy = trim( wp_strip_all_tags( $about_copy ) );
        $about_copy = (string) preg_replace( '/\s+/u', ' ', $about_copy );
        $leading_phrases = [
            trim( (string) get_the_title( $about_page ) ),
            '' !== $site_name ? sprintf( __( 'Giới thiệu về %s', 'aznet-theme' ), $site_name ) : '',
        ];
        foreach ( $leading_phrases as $leading_phrase ) {
            if ( '' !== $leading_phrase ) {
                $about_copy = (string) preg_replace( '/^' . preg_quote( $leading_phrase, '/' ) . '\s*/iu', '', $about_copy, 1 );
            }
        }
        if ( '' !== $about_copy ) {
            $lede = wp_trim_words( $about_copy, 28, '…' );
        }
    }
}
if ( '' === $primary_label ) {
    $primary_label = \AZnet\Theme\preset_term( 'primary_group', __( 'Danh mục thiết bị', 'aznet-theme' ), 'industrial-01' );
}
if ( '' === $primary_url ) {
    $primary_url = '' !== $shop_url ? $shop_url : home_url( '/' );
}
if ( '' === $secondary_label ) {
    $secondary_label = \AZnet\Theme\preset_term( 'primary_cta', __( 'Yêu cầu báo giá', 'aznet-theme' ), 'industrial-01' );
}
if ( '' === $secondary_url ) {
    $secondary_url = '#aznet-industrial01-quote';
}

$hero_image_id = (int) \AZnet\Theme\setting( 'homepage_industrial01_hero_image', 0 );
$background_id = (int) \AZnet\Theme\setting( 'homepage_industrial01_hero_background', 0 );

$hero_image = '';
if ( $hero_image_id > 0 && function_exists( 'wp_get_attachment_image' ) ) {
    $hero_image = (string) wp_get_attachment_image(
        $hero_image_id,
        'full',
        false,
        [
            'class'         => 'aznet-theme-industrial01-hero__image',
            'alt'           => '',
            'loading'       => 'eager',
            'fetchpriority' => 'high',
            'decoding'      => 'async',
        ]
    );
}

$background_url = '';
if ( $background_id > 0 && function_exists( 'wp_get_attachment_image_url' ) ) {
    $background_url = (string) wp_get_attachment_image_url( $background_id, 'full' );
}

$hero_style = '';
if ( '' !== $background_url ) {
    $hero_style = '--industrial01-hero-background-image:url("' . esc_url_raw( $background_url ) . '");';
}
?>
<section class="aznet-theme-industrial01-section aznet-theme-industrial01-hero"<?php echo '' !== $hero_style ? ' style="' . esc_attr( $hero_style ) . '"' : ''; ?>>
    <div class="aznet-theme-industrial01-shell aznet-theme-industrial01-hero__grid">
        <div class="aznet-theme-industrial01-hero__content">
            <?php if ( '' !== $kicker ) : ?>
                <p class="aznet-theme-industrial01-kicker"><?php echo esc_html( $kicker ); ?></p>
            <?php endif; ?>
            <?php if ( '' !== $title ) : ?>
                <h1><?php echo esc_html( $title ); ?></h1>
            <?php endif; ?>
            <?php if ( '' !== $lede ) : ?>
                <p class="aznet-theme-industrial01-lede"><?php echo esc_html( $lede ); ?></p>
            <?php endif; ?>
            <div class="aznet-theme-industrial01-actions">
                <?php if ( '' !== $primary_label && '' !== $primary_url ) : ?>
                    <a class="aznet-theme-industrial01-button" href="<?php echo esc_url( $primary_url ); ?>">
                        <?php echo esc_html( $primary_label ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( '' !== $secondary_label && '' !== $secondary_url ) : ?>
                    <a class="aznet-theme-industrial01-text-link" href="<?php echo esc_url( $secondary_url ); ?>">
                        <?php echo esc_html( $secondary_label ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="aznet-theme-industrial01-hero__visual" aria-hidden="true">
            <?php if ( '' !== trim( $hero_image ) ) : ?>
                <div class="aznet-theme-industrial01-hero__panel">
                    <?php echo $hero_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment image HTML. ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

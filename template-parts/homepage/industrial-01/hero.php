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
        $about_source = '' !== trim( (string) $about_page->post_excerpt )
            ? (string) $about_page->post_excerpt
            : (string) $about_page->post_content;
        $about_copy = strip_shortcodes( $about_source );
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
            $sentences = preg_split( '/(?<=[.!?])\s+/u', $about_copy, 2 );
            $lead_sentence = is_array( $sentences ) && isset( $sentences[0] ) ? trim( (string) $sentences[0] ) : $about_copy;
            $lede = wp_trim_words( $lead_sentence, 32, '…' );
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

// Reuse the WordPress-owned Hero attachment as the background only when
// a distinct background has not been selected by the site's editor.
$background_from_hero = $background_id <= 0 && $hero_image_id > 0;
$effective_background_id = $background_from_hero ? $hero_image_id : $background_id;
$background_url = '';
if ( $effective_background_id > 0 && function_exists( 'wp_get_attachment_image_url' ) ) {
    $background_url = (string) wp_get_attachment_image_url( $effective_background_id, 'full' );
}
$background_from_hero = $background_from_hero && '' !== $background_url;

$hero_image = '';
if ( $hero_image_id > 0 && ! $background_from_hero && function_exists( 'wp_get_attachment_image' ) ) {
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

$hero_style = '';
$hero_classes = 'aznet-theme-industrial01-section aznet-theme-industrial01-hero';
if ( '' !== $background_url ) {
    $hero_style = '--industrial01-hero-background-image:url("' . esc_url_raw( $background_url ) . '");';
    $hero_classes .= ' aznet-theme-industrial01-hero--with-background';
}
if ( $background_from_hero ) {
    $hero_classes .= ' aznet-theme-industrial01-hero--background-only';
}

$catalog_menu = '';
if ( function_exists( 'AZnet\Theme\header_catalog_home_rail' ) && \AZnet\Theme\header_catalog_home_rail() ) {
    $catalog_menu = \AZnet\Theme\header_catalog_menu_html( 'aznet-theme-hero-catalog-menu' );
}
if ( '' !== $catalog_menu ) {
    $hero_classes .= ' aznet-theme-industrial01-hero--with-catalog';
}
?>
<section class="<?php echo esc_attr( $hero_classes ); ?>"<?php echo '' !== $hero_style ? ' style="' . esc_attr( $hero_style ) . '"' : ''; ?>>
    <div class="aznet-theme-industrial01-shell aznet-theme-industrial01-hero__grid">
        <?php if ( '' !== $catalog_menu ) : ?>
            <aside class="aznet-theme-industrial01-hero__catalog" aria-label="<?php echo esc_attr__( 'Danh mục sản phẩm', 'aznet-theme' ); ?>">
                <p class="aznet-theme-industrial01-hero__catalog-heading"><?php esc_html_e( 'DANH MỤC SẢN PHẨM', 'aznet-theme' ); ?></p>
                <nav aria-label="<?php echo esc_attr__( 'Điều hướng danh mục thiết bị', 'aznet-theme' ); ?>">
                    <?php echo $catalog_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu() output. ?>
                </nav>
            </aside>
        <?php endif; ?>
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
        <?php if ( '' !== trim( $hero_image ) ) : ?>
            <div class="aznet-theme-industrial01-hero__visual" aria-hidden="true">
                <div class="aznet-theme-industrial01-hero__panel">
                    <?php echo $hero_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment image HTML. ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

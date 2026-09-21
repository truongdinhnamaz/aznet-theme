<?php
/**
 * Header brand primitive.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$site_title   = isset( $args['site_title'] ) ? (string) $args['site_title'] : '';
$home_url     = isset( $args['home_url'] ) ? (string) $args['home_url'] : home_url( '/' );
$logo_html    = isset( $args['logo_html'] ) ? (string) $args['logo_html'] : '';
$law01_header = ! empty( $args['law01_header'] );
$brand_label  = $law01_header
    ? trim( $site_title . ' ' . __( 'Văn phòng luật sư', 'aznet-theme' ) )
    : $site_title;
?>
<a class="aznet-theme-site-header__brand" href="<?php echo esc_url( $home_url ); ?>" rel="home" aria-label="<?php echo esc_attr( $brand_label ); ?>">
    <?php if ( '' !== $logo_html ) : ?>
        <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment HTML from wp_get_attachment_image(). ?>
        <?php if ( '' !== $site_title ) : ?>
            <span class="aznet-theme-site-header__brand-copy">
                <span class="aznet-theme-site-header__brand-title"><?php echo esc_html( $site_title ); ?></span>
                <?php if ( $law01_header ) : ?><span class="aznet-theme-site-header__brand-kicker"><?php esc_html_e( 'Văn phòng luật sư', 'aznet-theme' ); ?></span><?php endif; ?>
            </span>
        <?php endif; ?>
    <?php else : ?>
        <span><?php echo esc_html( $site_title ); ?></span>
    <?php endif; ?>
</a>

<?php
/**
 * Header brand primitive.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$site_title = isset( $args['site_title'] ) ? (string) $args['site_title'] : '';
$home_url   = isset( $args['home_url'] ) ? (string) $args['home_url'] : home_url( '/' );
$logo_html  = isset( $args['logo_html'] ) ? (string) $args['logo_html'] : '';
?>
<a class="aznet-theme-site-header__brand" href="<?php echo esc_url( $home_url ); ?>" rel="home" aria-label="<?php echo esc_attr( $site_title ); ?>">
    <?php if ( '' !== $logo_html ) : ?>
        <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment HTML from wp_get_attachment_image(). ?>
    <?php else : ?>
        <span><?php echo esc_html( $site_title ); ?></span>
    <?php endif; ?>
</a>

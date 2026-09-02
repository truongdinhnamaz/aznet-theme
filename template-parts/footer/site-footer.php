<?php
/**
 * Theme-owned site footer presentation.
 *
 * Presentation provenance:
 * - ConvertFlow Core P5.223 SiteFooterRenderer.php
 * - ConvertFlow Core P5.223 assets/css/site-footer.css
 *
 * Authoritative identity/contact/social providers remain outside the theme.
 * WordPress-native site identity and menu locations are the bounded fallback.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$site_title = trim( (string) get_bloginfo( 'name' ) );
$tagline    = trim( (string) get_bloginfo( 'description' ) );
$home_url   = home_url( '/' );
$logo_id    = (int) get_theme_mod( 'custom_logo', 0 );
$logo_html  = '';

if ( $logo_id > 0 && function_exists( 'wp_get_attachment_image' ) ) {
    $logo_html = (string) wp_get_attachment_image(
        $logo_id,
        'full',
        false,
        [
            'class' => 'aznet-theme-site-footer__logo',
            'alt'   => '',
        ]
    );
}

$menu_html = static function ( string $location, string $class_name ): string {
    $html = wp_nav_menu(
        [
            'theme_location' => $location,
            'container'      => false,
            'fallback_cb'    => false,
            'echo'           => false,
            'depth'          => 2,
            'menu_class'     => $class_name,
        ]
    );

    return is_string( $html ) ? trim( $html ) : '';
};

$primary_menu = $menu_html( 'footer', 'aznet-theme-site-footer__menu' );
$contact_menu = $menu_html( 'footer-contact', 'aznet-theme-site-footer__contact-menu' );
$social_menu  = $menu_html( 'footer-social', 'aznet-theme-site-footer__social-menu' );
$policy_menu  = $menu_html( 'footer-policy', 'aznet-theme-site-footer__policy-menu' );
$year         = function_exists( 'wp_date' ) ? wp_date( 'Y' ) : gmdate( 'Y' );
?>
<footer class="aznet-theme-site-footer aznet-theme-site-footer--standard" data-aznet-theme-site-footer role="contentinfo">
    <div class="aznet-theme-site-footer__inner">
        <div class="aznet-theme-site-footer__main">
            <div class="aznet-theme-site-footer__identity" data-aznet-theme-footer-identity-source="wordpress">
                <a class="aznet-theme-site-footer__brand" href="<?php echo esc_url( $home_url ); ?>" rel="home" aria-label="<?php echo esc_attr( $site_title ); ?>">
                    <?php if ( '' !== $logo_html ) : ?>
                        <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment HTML. ?>
                    <?php else : ?>
                        <strong><?php echo esc_html( $site_title ); ?></strong>
                    <?php endif; ?>
                </a>
                <?php if ( '' !== $tagline ) : ?>
                    <p><?php echo esc_html( $tagline ); ?></p>
                <?php endif; ?>
            </div>

            <?php if ( '' !== $primary_menu ) : ?>
                <nav class="aznet-theme-site-footer__navigation" aria-label="<?php echo esc_attr__( 'Điều hướng Footer', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Khám phá', 'aznet-theme' ); ?></h2>
                    <?php echo $primary_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
                </nav>
            <?php endif; ?>

            <?php if ( '' !== $contact_menu ) : ?>
                <nav class="aznet-theme-site-footer__contact" aria-label="<?php echo esc_attr__( 'Liên hệ', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Liên hệ', 'aznet-theme' ); ?></h2>
                    <?php echo $contact_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
                </nav>
            <?php endif; ?>
        </div>

        <div class="aznet-theme-site-footer__bottom">
            <p class="aznet-theme-site-footer__copyright">
                <?php echo esc_html( sprintf( '© %s %s', $year, $site_title ) ); ?>
            </p>
            <div class="aznet-theme-site-footer__bottom-nav">
                <?php if ( '' !== $social_menu ) : ?>
                    <nav class="aznet-theme-site-footer__social" aria-label="<?php echo esc_attr__( 'Mạng xã hội', 'aznet-theme' ); ?>">
                        <?php echo $social_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
                    </nav>
                <?php endif; ?>
                <?php if ( '' !== $policy_menu ) : ?>
                    <nav class="aznet-theme-site-footer__policies" aria-label="<?php echo esc_attr__( 'Chính sách', 'aznet-theme' ); ?>">
                        <?php echo $policy_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<?php
/**
 * Theme-owned site Footer presentation.
 *
 * WordPress owns the site identity and menu data exposed through footer_context().
 * The Theme owns only the presentation composition below.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$context      = \AZnet\Theme\footer_context();
$preset       = (string) ( $context['preset'] ?? 'standard' );
$site_title   = (string) ( $context['site_title'] ?? '' );
$tagline      = (string) ( $context['tagline'] ?? '' );
$home_url     = (string) ( $context['home_url'] ?? '' );
$logo_html    = (string) ( $context['logo_html'] ?? '' );
$menus        = is_array( $context['menus'] ?? null ) ? $context['menus'] : [];
$primary_menu = (string) ( $menus['footer'] ?? '' );
$contact_menu = (string) ( $menus['footer-contact'] ?? '' );
$social_menu  = (string) ( $menus['footer-social'] ?? '' );
$policy_menu  = (string) ( $menus['footer-policy'] ?? '' );
$year         = (string) ( $context['year'] ?? '' );
?>
<footer class="aznet-theme-site-footer aznet-theme-site-footer--<?php echo esc_attr( $preset ); ?>" data-aznet-theme-site-footer role="contentinfo">
    <div class="aznet-theme-site-footer__inner">
        <div class="aznet-theme-site-footer__main">
            <div class="aznet-theme-site-footer__identity" data-aznet-theme-footer-identity-source="wordpress">
                <a class="aznet-theme-site-footer__brand" href="<?php echo esc_url( $home_url ); ?>" rel="home" aria-label="<?php echo esc_attr( $site_title ); ?>">
                    <?php if ( '' !== $logo_html ) : ?>
                        <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment HTML from Theme context. ?>
                        <?php if ( '' !== $site_title ) : ?><strong class="aznet-theme-site-footer__brand-title"><?php echo esc_html( $site_title ); ?></strong><?php endif; ?>
                    <?php else : ?>
                        <strong><?php echo esc_html( $site_title ); ?></strong>
                    <?php endif; ?>
                </a>
                <?php if ( '' !== $tagline ) : ?>
                    <p><?php echo esc_html( $tagline ); ?></p>
                <?php endif; ?>
            </div>

            <?php if ( 'professional' === $preset && '' !== $contact_menu ) : ?>
                <nav class="aznet-theme-site-footer__contact" aria-label="<?php echo esc_attr__( 'Liên hệ', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Liên hệ', 'aznet-theme' ); ?></h2>
                    <?php echo $contact_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                </nav>
            <?php endif; ?>

            <?php if ( '' !== $primary_menu ) : ?>
                <nav class="aznet-theme-site-footer__navigation" aria-label="<?php echo esc_attr__( 'Điều hướng Footer', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Khám phá', 'aznet-theme' ); ?></h2>
                    <?php echo $primary_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                </nav>
            <?php endif; ?>

            <?php if ( 'professional' !== $preset && '' !== $contact_menu ) : ?>
                <nav class="aznet-theme-site-footer__contact" aria-label="<?php echo esc_attr__( 'Liên hệ', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Liên hệ', 'aznet-theme' ); ?></h2>
                    <?php echo $contact_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                </nav>
            <?php endif; ?>

            <?php if ( 'professional' === $preset && '' !== $social_menu ) : ?>
                <nav class="aznet-theme-site-footer__social-column aznet-theme-site-footer__social" aria-label="<?php echo esc_attr__( 'Kết nối với chúng tôi', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Kết nối với chúng tôi', 'aznet-theme' ); ?></h2>
                    <?php echo $social_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                </nav>
            <?php endif; ?>
        </div>

        <div class="aznet-theme-site-footer__bottom">
            <p class="aznet-theme-site-footer__copyright">
                <?php echo esc_html( sprintf( '© %s %s', $year, $site_title ) ); ?>
            </p>
            <?php if ( '' !== $social_menu || '' !== $policy_menu ) : ?>
                <div class="aznet-theme-site-footer__bottom-nav">
                    <?php if ( '' !== $social_menu && 'professional' !== $preset ) : ?>
                        <nav class="aznet-theme-site-footer__social" aria-label="<?php echo esc_attr__( 'Mạng xã hội', 'aznet-theme' ); ?>">
                            <?php echo $social_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                        </nav>
                    <?php endif; ?>
                    <?php if ( '' !== $policy_menu ) : ?>
                        <nav class="aznet-theme-site-footer__policies" aria-label="<?php echo esc_attr__( 'Chính sách', 'aznet-theme' ); ?>">
                            <?php echo $policy_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                        </nav>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>

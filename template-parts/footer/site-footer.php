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
$is_law01     = 'law-01' === $preset;

$footer_classes = [
    'aznet-theme-site-footer',
    'aznet-theme-site-footer--' . $preset,
];

$contact_heading    = __( 'Thông tin liên hệ', 'aznet-theme' );
$navigation_heading = __( 'Liên kết nhanh', 'aznet-theme' );
?>
<footer class="<?php echo esc_attr( implode( ' ', $footer_classes ) ); ?>" data-aznet-theme-site-footer role="contentinfo">
    <?php if ( $is_law01 ) : ?>
        <svg class="aznet-theme-site-footer__watermark" viewBox="0 0 240 190" aria-hidden="true" focusable="false">
            <path d="M24 166h192M44 166v-73h24v73m35 0V75h34v91m35 0V93h24v73M30 93l90-58 90 58M58 82h124M80 58h80M106 35h28M38 176h164" fill="none" stroke="currentColor" stroke-width="2"/>
            <path d="M49 104h14m45-10h24m45 10h14M49 123h14m45-8h24m45 8h14M49 142h14m45-6h24m45 6h14" fill="none" stroke="currentColor" stroke-width="1.5"/>
        </svg>
    <?php endif; ?>

    <div class="aznet-theme-site-footer__inner">
        <div class="aznet-theme-site-footer__main">
            <div class="aznet-theme-site-footer__identity" data-aznet-theme-footer-identity-source="wordpress">
                <a class="aznet-theme-site-footer__brand" href="<?php echo esc_url( $home_url ); ?>" rel="home" aria-label="<?php echo esc_attr( $site_title ); ?>">
                    <?php if ( '' !== $logo_html ) : ?>
                        <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment HTML from Theme context. ?>
                        <?php if ( '' !== $site_title ) : ?>
                            <span class="aznet-theme-site-footer__brand-copy">
                                <strong class="aznet-theme-site-footer__brand-title"><?php echo esc_html( $site_title ); ?></strong>
                            </span>
                        <?php endif; ?>
                    <?php else : ?>
                        <strong><?php echo esc_html( $site_title ); ?></strong>
                    <?php endif; ?>
                </a>
                <?php if ( '' !== $tagline ) : ?>
                    <p><?php echo esc_html( $tagline ); ?></p>
                <?php endif; ?>
            </div>

            <?php if ( ( 'professional' === $preset || $is_law01 ) && '' !== $contact_menu ) : ?>
                <nav class="aznet-theme-site-footer__contact" aria-label="<?php echo esc_attr__( 'Liên hệ', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php echo esc_html( $contact_heading ); ?></h2>
                    <?php echo $contact_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                </nav>
            <?php endif; ?>

            <?php if ( '' !== $primary_menu ) : ?>
                <nav class="aznet-theme-site-footer__navigation" aria-label="<?php echo esc_attr__( 'Điều hướng Footer', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php echo esc_html( $navigation_heading ); ?></h2>
                    <?php echo $primary_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                </nav>
            <?php endif; ?>

            <?php if ( 'professional' !== $preset && ! $is_law01 && '' !== $contact_menu ) : ?>
                <nav class="aznet-theme-site-footer__contact" aria-label="<?php echo esc_attr__( 'Liên hệ', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php echo esc_html( $contact_heading ); ?></h2>
                    <?php echo $contact_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                </nav>
            <?php endif; ?>

            <?php if ( $is_law01 ) : ?>
                <section class="aznet-theme-site-footer__connect" aria-labelledby="aznet-theme-footer-connect-heading">
                    <h2 id="aznet-theme-footer-connect-heading" class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Kết nối với chúng tôi', 'aznet-theme' ); ?></h2>
                    <?php if ( '' !== $social_menu ) : ?>
                        <nav class="aznet-theme-site-footer__social" aria-label="<?php echo esc_attr__( 'Mạng xã hội', 'aznet-theme' ); ?>">
                            <?php echo $social_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                        </nav>
                    <?php endif; ?>
                    <p class="aznet-theme-site-footer__law01-copyright">
                        <?php echo esc_html( sprintf( '© %s %s', $year, $site_title ) ); ?>
                        <span><?php esc_html_e( 'All rights reserved.', 'aznet-theme' ); ?></span>
                    </p>
                </section>
            <?php elseif ( 'professional' === $preset && '' !== $social_menu ) : ?>
                <nav class="aznet-theme-site-footer__social-column aznet-theme-site-footer__social" aria-label="<?php echo esc_attr__( 'Kết nối với chúng tôi', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Kết nối với chúng tôi', 'aznet-theme' ); ?></h2>
                    <?php echo $social_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                </nav>
            <?php endif; ?>
        </div>

        <div class="aznet-theme-site-footer__bottom">
            <?php if ( $is_law01 ) : ?>
                <?php if ( '' !== $tagline ) : ?>
                    <p class="aznet-theme-site-footer__bottom-note"><?php echo esc_html( $tagline ); ?></p>
                <?php endif; ?>
            <?php else : ?>
                <p class="aznet-theme-site-footer__copyright">
                    <?php echo esc_html( sprintf( '© %s %s', $year, $site_title ) ); ?>
                </p>
            <?php endif; ?>

            <?php if ( ( '' !== $social_menu && ! $is_law01 ) || '' !== $policy_menu ) : ?>
                <div class="aznet-theme-site-footer__bottom-nav">
                    <?php if ( '' !== $social_menu && 'professional' !== $preset && ! $is_law01 ) : ?>
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

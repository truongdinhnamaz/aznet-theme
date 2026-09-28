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
$about_intro  = (string) ( $context['about_intro'] ?? '' );
$services        = is_array( $context['services'] ?? null ) ? $context['services'] : [];
$social_channels = is_array( $context['social_channels'] ?? null ) ? $context['social_channels'] : [];
$contact_links   = is_array( $context['contact_links'] ?? null ) ? $context['contact_links'] : [];
$menus           = is_array( $context['menus'] ?? null ) ? $context['menus'] : [];
$primary_menu = (string) ( $menus['footer'] ?? '' );
$contact_menu = (string) ( $menus['footer-contact'] ?? '' );
$social_menu  = (string) ( $menus['footer-social'] ?? '' );
$policy_menu  = (string) ( $menus['footer-policy'] ?? '' );
$year         = (string) ( $context['year'] ?? '' );

$footer_classes = [
    'aznet-theme-site-footer',
    'aznet-theme-site-footer--' . $preset,
];

$contact_heading    = __( 'Thông tin liên hệ', 'aznet-theme' );
$navigation_heading = __( 'Liên kết nhanh', 'aznet-theme' );
?>
<footer class="<?php echo esc_attr( implode( ' ', $footer_classes ) ); ?>" data-aznet-theme-site-footer role="contentinfo">
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
                    <p class="aznet-theme-site-footer__tagline"><?php echo esc_html( $tagline ); ?></p>
                <?php endif; ?>
                <?php if ( 'law-01' === $preset && '' !== $about_intro ) : ?>
                    <p class="aznet-theme-site-footer__about"><?php echo esc_html( $about_intro ); ?></p>
                <?php endif; ?>
                <?php if ( 'law-01' === $preset && ! empty( $social_channels ) ) : ?>
                    <nav class="aznet-theme-site-footer__channels" aria-label="<?php echo esc_attr__( 'Kênh mạng xã hội', 'aznet-theme' ); ?>">
                        <?php foreach ( $social_channels as $channel ) : ?>
                            <?php
                            $channel_key   = sanitize_html_class( (string) ( $channel['key'] ?? '' ) );
                            $channel_title = (string) ( $channel['title'] ?? '' );
                            $channel_url   = (string) ( $channel['url'] ?? '' );
                            if ( '' === $channel_key || '' === $channel_title || '' === $channel_url ) {
                                continue;
                            }
                            ?>
                            <a class="aznet-theme-site-footer__channel-link aznet-theme-site-footer__channel-link--<?php echo esc_attr( $channel_key ); ?>"
                               href="<?php echo esc_url( $channel_url ); ?>"
                               aria-label="<?php echo esc_attr( $channel_title ); ?>"
                               title="<?php echo esc_attr( $channel_title ); ?>"
                               target="_blank"
                               rel="noopener noreferrer">
                                <span class="aznet-theme-site-footer__channel-icon" aria-hidden="true"></span>
                                <span class="screen-reader-text"><?php echo esc_html( $channel_title ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>
            </div>

            <?php if ( 'law-01' === $preset && ! empty( $services ) ) : ?>
                <nav class="aznet-theme-site-footer__services" aria-label="<?php echo esc_attr__( 'Dịch vụ chính', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Dịch vụ chính', 'aznet-theme' ); ?></h2>
                    <ul class="aznet-theme-site-footer__services-menu">
                        <?php foreach ( array_slice( $services, 0, 4 ) as $service ) : ?>
                            <li><a href="<?php echo esc_url( (string) ( $service['url'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $service['title'] ?? '' ) ); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <?php if ( 'professional' === $preset && '' !== $contact_menu ) : ?>
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

            <?php if ( 'law-01' === $preset && ! empty( $contact_links ) ) : ?>
                <div class="aznet-theme-site-footer__contact aznet-theme-site-footer__contact-social">
                    <h2 class="aznet-theme-site-footer__heading"><?php esc_html_e( 'Thông tin liên hệ', 'aznet-theme' ); ?></h2>
                    <ul class="aznet-theme-site-footer__contact-links">
                        <?php foreach ( $contact_links as $contact ) : ?>
                            <?php
                            $contact_key   = sanitize_html_class( (string) ( $contact['key'] ?? '' ) );
                            $contact_title = (string) ( $contact['title'] ?? '' );
                            $contact_url   = (string) ( $contact['url'] ?? '' );
                            if ( '' === $contact_key || '' === $contact_title || '' === $contact_url ) {
                                continue;
                            }
                            ?>
                            <li>
                                <a class="aznet-theme-site-footer__contact-link aznet-theme-site-footer__contact-link--<?php echo esc_attr( $contact_key ); ?>"
                                   href="<?php echo esc_url( $contact_url, [ 'http', 'https', 'tel', 'mailto' ] ); ?>">
                                    <span class="aznet-theme-site-footer__contact-icon" aria-hidden="true"></span>
                                    <span class="screen-reader-text"><?php echo esc_html( $contact_title ); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif ( 'professional' !== $preset && '' !== $contact_menu ) : ?>
                <nav class="aznet-theme-site-footer__contact" aria-label="<?php echo esc_attr__( 'Liên hệ', 'aznet-theme' ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php echo esc_html( $contact_heading ); ?></h2>
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
                    <?php if ( '' !== $social_menu && ! in_array( $preset, [ 'professional', 'law-01' ], true ) ) : ?>
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

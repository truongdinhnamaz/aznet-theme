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
$skin         = (string) ( $context['skin'] ?? 'default' );
$site_title   = (string) ( $context['site_title'] ?? '' );
$tagline      = (string) ( $context['tagline'] ?? '' );
$home_url     = (string) ( $context['home_url'] ?? '' );
$logo_html    = (string) ( $context['logo_html'] ?? '' );
$about_intro            = (string) ( $context['about_intro'] ?? '' );
$footer_columns_active  = ! empty( $context['footer_columns_active'] );
$footer_columns         = is_array( $context['footer_columns'] ?? null ) ? $context['footer_columns'] : [];
$services               = is_array( $context['services'] ?? null ) ? $context['services'] : [];
$social_channels = is_array( $context['social_channels'] ?? null ) ? $context['social_channels'] : [];
$contact_links   = is_array( $context['contact_links'] ?? null ) ? $context['contact_links'] : [];
$menus           = is_array( $context['menus'] ?? null ) ? $context['menus'] : [];
$labels          = is_array( $context['labels'] ?? null ) ? $context['labels'] : [];
$primary_menu = (string) ( $menus['footer'] ?? '' );
$contact_menu = (string) ( $menus['footer-contact'] ?? '' );
$social_menu  = (string) ( $menus['footer-social'] ?? '' );
$policy_menu  = (string) ( $menus['footer-policy'] ?? '' );
$year         = (string) ( $context['year'] ?? '' );

$footer_classes = [
    'aznet-theme-site-footer',
    'aznet-theme-site-footer--' . $preset,
];

if ( 'default' !== $skin && '' !== $skin ) {
    $footer_classes[] = 'aznet-theme-site-footer--skin-' . sanitize_html_class( $skin );
}

$contact_heading    = (string) ( $labels['contact_heading'] ?? __( 'Thông tin liên hệ', 'aznet-theme' ) );
$navigation_heading = (string) ( $labels['primary_heading'] ?? __( 'Liên kết nhanh', 'aznet-theme' ) );
$social_heading     = (string) ( $labels['social_heading'] ?? __( 'Kết nối với chúng tôi', 'aznet-theme' ) );
$policy_heading     = (string) ( $labels['policy_heading'] ?? __( 'Chính sách', 'aznet-theme' ) );
?>
<footer class="<?php echo esc_attr( implode( ' ', $footer_classes ) ); ?>" data-aznet-theme-site-footer role="contentinfo">
    <div class="aznet-theme-site-footer__inner">
        <div class="aznet-theme-site-footer__main">
            <?php if ( $footer_columns_active ) : ?>
                <?php foreach ( $footer_columns as $column_index => $column_content ) : ?>
                    <section class="aznet-theme-site-footer__column-content aznet-theme-site-footer__column-content--<?php echo esc_attr( (string) ( $column_index + 1 ) ); ?>">
                        <?php if ( 0 === $column_index ) : ?>
                            <div class="aznet-theme-site-footer__identity" data-aznet-theme-footer-identity-source="wordpress">
                                <a class="aznet-theme-site-footer__brand" href="<?php echo esc_url( $home_url ); ?>" rel="home" aria-label="<?php echo esc_attr( $site_title ); ?>">
                                    <?php if ( '' !== $logo_html ) : ?>
                                        <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment HTML from Theme context. ?>
                                        <?php if ( '' !== $site_title ) : ?><strong class="aznet-theme-site-footer__brand-title"><?php echo esc_html( $site_title ); ?></strong><?php endif; ?>
                                    <?php else : ?>
                                        <strong><?php echo esc_html( $site_title ); ?></strong>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if ( '' !== trim( (string) $column_content ) ) : ?>
                            <div class="aznet-theme-site-footer__rich-content">
                                <?php echo $column_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized WordPress-owned rich content from Footer context. ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            <?php else : ?>
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
                    <h2 class="aznet-theme-site-footer__heading"><?php echo esc_html( $contact_heading ); ?></h2>
                    <ul class="aznet-theme-site-footer__contact-links">
                        <?php
                        $contact_labels = [
                            'location' => __( 'Địa chỉ', 'aznet-theme' ),
                            'phone'    => __( 'SĐT', 'aznet-theme' ),
                            'email'    => __( 'Email', 'aznet-theme' ),
                            'website'  => __( 'Website', 'aznet-theme' ),
                        ];
                        ?>
                        <?php foreach ( $contact_links as $contact ) : ?>
                            <?php
                            $contact_key   = sanitize_html_class( (string) ( $contact['key'] ?? '' ) );
                            $contact_title = (string) ( $contact['title'] ?? '' );
                            $contact_url   = (string) ( $contact['url'] ?? '' );
                            $contact_label = (string) ( $contact_labels[ $contact_key ] ?? '' );
                            if ( '' === $contact_key || '' === $contact_title || '' === $contact_label ) {
                                continue;
                            }
                            $contact_is_link = '' !== $contact_url && '#' !== $contact_url;
                            ?>
                            <li>
                                <?php if ( $contact_is_link ) : ?>
                                    <a class="aznet-theme-site-footer__contact-link aznet-theme-site-footer__contact-link--<?php echo esc_attr( $contact_key ); ?>"
                                       href="<?php echo esc_url( $contact_url, [ 'http', 'https', 'tel', 'mailto' ] ); ?>">
                                        <span class="aznet-theme-site-footer__contact-label"><?php echo esc_html( $contact_label ); ?>:</span>
                                        <span class="aznet-theme-site-footer__contact-value aznet-theme-site-footer__contact-text"><?php echo esc_html( $contact_title ); ?></span>
                                    </a>
                                <?php else : ?>
                                    <span class="aznet-theme-site-footer__contact-link aznet-theme-site-footer__contact-link--<?php echo esc_attr( $contact_key ); ?> aznet-theme-site-footer__contact-link--static">
                                        <span class="aznet-theme-site-footer__contact-label"><?php echo esc_html( $contact_label ); ?>:</span>
                                        <span class="aznet-theme-site-footer__contact-value aznet-theme-site-footer__contact-text"><?php echo esc_html( $contact_title ); ?></span>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>

                        <?php foreach ( $social_channels as $channel ) : ?>
                            <?php
                            $channel_key = sanitize_html_class( (string) ( $channel['key'] ?? '' ) );
                            $channel_url = (string) ( $channel['url'] ?? '' );
                            if ( 'facebook' !== $channel_key || '' === $channel_url ) {
                                continue;
                            }
                            $channel_title = (string) ( $channel['title'] ?? '' );
                            ?>
                            <li>
                                <a class="aznet-theme-site-footer__contact-link aznet-theme-site-footer__contact-link--fanpage"
                                   href="<?php echo esc_url( $channel_url ); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer">
                                    <span class="aznet-theme-site-footer__contact-label"><?php esc_html_e( 'Fanpage', 'aznet-theme' ); ?>:</span>
                                    <span class="aznet-theme-site-footer__contact-value aznet-theme-site-footer__contact-text"><?php echo esc_html( '' !== $channel_title ? $channel_title : $channel_url ); ?></span>
                                </a>
                            </li>
                            <?php break; ?>
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
                <nav class="aznet-theme-site-footer__social-column aznet-theme-site-footer__social" aria-label="<?php echo esc_attr( $social_heading ); ?>">
                    <h2 class="aznet-theme-site-footer__heading"><?php echo esc_html( $social_heading ); ?></h2>
                    <?php echo $social_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                </nav>
            <?php endif; ?>
        </div>


            <?php endif; ?>
        </div>

        <div class="aznet-theme-site-footer__bottom">
            <p class="aznet-theme-site-footer__copyright">
                <?php echo esc_html( sprintf( '© %s %s', $year, $site_title ) ); ?>
            </p>
            <?php if ( '' !== $social_menu || '' !== $policy_menu ) : ?>
                <div class="aznet-theme-site-footer__bottom-nav">
                    <?php if ( ! $footer_columns_active && '' !== $social_menu && ! in_array( $preset, [ 'professional', 'law-01' ], true ) ) : ?>
                        <nav class="aznet-theme-site-footer__social" aria-label="<?php echo esc_attr__( 'Mạng xã hội', 'aznet-theme' ); ?>">
                            <?php echo $social_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                        </nav>
                    <?php endif; ?>
                    <?php if ( '' !== $policy_menu ) : ?>
                        <nav class="aznet-theme-site-footer__policies" aria-label="<?php echo esc_attr( $policy_heading ); ?>">
                            <?php echo $policy_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output from Theme context. ?>
                        </nav>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>

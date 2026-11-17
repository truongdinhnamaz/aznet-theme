<?php
/**
 * Optional, Theme-owned floating contact presentation sourced from a WordPress menu.
 * Phone number, Zalo ID and conversion state remain WordPress-owned.
 *
 * @package AZnetTheme
 */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Explicit, native WordPress site opt-in; disabled for every existing installation. */
function floating_contact_enabled(): bool {
    if ( ! function_exists( 'get_theme_mod' ) ) { return false; }
    return in_array( get_theme_mod( 'aznet_theme_floating_contact_enabled', false ), [ true, 1, '1' ], true );
}

/**
 * Read only valid public phone and Zalo links from the existing footer-contact menu.
 * An incomplete rail is hidden; Theme never guesses contact information.
 *
 * @return array<string,array{url:string,label:string}>
 */
function floating_contact_links(): array {
    if ( ! floating_contact_enabled() || ! function_exists( 'get_nav_menu_locations' )
        || ! function_exists( 'wp_get_nav_menu_items' ) ) { return []; }
    $locations = get_nav_menu_locations();
    if ( ! is_array( $locations ) ) { return []; }
    $menu_id = (int) ( $locations['footer-contact'] ?? 0 );
    if ( $menu_id < 1 ) { return []; }
    $items = wp_get_nav_menu_items( $menu_id );
    if ( ! is_array( $items ) ) { return []; }
    $links = [];
    foreach ( $items as $item ) {
        if ( ! is_object( $item ) || 'publish' !== (string) ( $item->post_status ?? '' ) ) { continue; }
        $url = trim( (string) ( $item->url ?? '' ) );
        $kind = '';
        if ( 1 === preg_match( '/^tel:\+?[0-9]{7,15}$/i', $url ) ) {
            $kind = 'phone';
        } elseif ( 1 === preg_match( '~^https://zalo\.me/[0-9]{9,15}/?$~i', $url ) ) {
            $kind = 'zalo';
        }
        if ( '' === $kind || isset( $links[ $kind ] ) ) { continue; }
        $label = trim( (string) ( $item->title ?? '' ) );
        $links[ $kind ] = [
            'url'   => $url,
            'label' => '' !== $label ? $label : ( 'phone' === $kind ? 'Gọi điện' : 'Nhắn Zalo' ),
        ];
    }
    return isset( $links['phone'], $links['zalo'] ) ? $links : [];
}

/** Keep the bottom of mobile pages clear when the fixed controls are present. */
function floating_contact_body_classes( array $classes ): array {
    if ( [] !== floating_contact_links() ) {
        $classes[] = 'aznet-theme-has-floating-contact';
    }
    return $classes;
}

/** Load only where opted in and fully configured. No JavaScript needed. */
function enqueue_floating_contact_assets(): void {
    if ( [] === floating_contact_links() ) { return; }
    $path = '/assets/css/components/floating-contact.css';
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;
    wp_enqueue_style(
        'aznet-theme-floating-contact',
        get_theme_file_uri( $path ),
        [ 'aznet-theme-tokens' ],
        function_exists( __NAMESPACE__ . '\\asset_content_version' )
            ? asset_content_version( $path, $version )
            : $version
    );
}

/** Print one globally shared, keyboard-accessible contact rail. */
function render_floating_contact(): void {
    $links = floating_contact_links();
    if ( [] === $links ) { return; }
    $icons = [
        'phone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.09 4.18 2 2 0 0 1 4.09 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.91.7 2.81a2 2 0 0 1-.45 2.11L8.07 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'zalo'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
    ];
    echo '<nav class="aznet-theme-floating-contact" data-aznet-theme-floating-contact aria-label="',
        esc_attr( __( 'Liên hệ nhanh', 'aznet-theme' ) ), '"><ul class="aznet-theme-floating-contact__list">';
    foreach ( [ 'phone', 'zalo' ] as $kind ) {
        $link = $links[ $kind ];
        echo '<li class="aznet-theme-floating-contact__item aznet-theme-floating-contact__item--',
            esc_attr( $kind ), '"><a class="aznet-theme-floating-contact__link" href="',
            esc_url( $link['url'] ), '" aria-label="', esc_attr( $link['label'] ), '"';
        if ( 'zalo' === $kind ) { echo ' target="_blank" rel="noopener noreferrer"'; }
        echo '><span class="aznet-theme-floating-contact__label">', esc_html( $link['label'] ),
            '</span><span class="aznet-theme-floating-contact__icon" aria-hidden="true">',
            $icons[ $kind ], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed Theme SVG paths.
            '</span></a></li>';
    }
    echo '</ul></nav>';
}

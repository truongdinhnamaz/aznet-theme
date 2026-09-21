<?php
/**
 * Y3 Footer System 2.0 real WordPress runtime fixture and fail-soft assertions.
 *
 * Run only inside an installed WordPress runtime through WP-CLI after the local
 * HTTP server is available.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Fail the runtime gate with a precise reason. */
function y3_runtime_fail( string $message ): never {
    WP_CLI::error( 'Y3 Footer: ' . $message );
}

/** Request the live local WordPress front page and return its HTML. */
function y3_runtime_home_html(): string {
    $response = wp_remote_get(
        home_url( '/' ),
        [
            'timeout'     => 15,
            'redirection' => 2,
        ]
    );

    if ( is_wp_error( $response ) ) {
        y3_runtime_fail( 'front-page request failed: ' . $response->get_error_message() );
    }
    if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
        y3_runtime_fail( 'front-page request did not return HTTP 200' );
    }

    return (string) wp_remote_retrieve_body( $response );
}

/** Extract the Theme-owned Footer fragment for focused assertions. */
function y3_runtime_footer_html( string $html ): string {
    if ( 1 !== preg_match( '/<footer\b[^>]*data-aznet-theme-site-footer[^>]*>.*?<\/footer>/si', $html, $matches ) ) {
        y3_runtime_fail( 'unable to isolate Theme Footer markup' );
    }

    return (string) $matches[0];
}

/** Persist one normalized Footer preset without altering unrelated settings. */
function y3_runtime_set_preset( string $preset ): void {
    $settings = get_theme_mod( 'aznet_theme_settings', [] );
    $settings = is_array( $settings ) ? $settings : [];
    $settings['footer_preset'] = $preset;
    set_theme_mod( 'aznet_theme_settings', $settings );
}

update_option( 'blogname', 'AZnet Y3 Runtime' );
update_option( 'blogdescription', 'WordPress-owned Footer identity sentinel.' );

$front_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'Y3 Footer Runtime Home',
        'post_name'    => 'y3-footer-runtime-home',
        'post_content' => '<p>Y3 Footer runtime content owned by WordPress.</p>',
    ],
    true
);
if ( is_wp_error( $front_id ) || 0 >= (int) $front_id ) {
    y3_runtime_fail( 'unable to create front-page fixture' );
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', (int) $front_id );

$menu_definitions = [
    'footer'         => [ 'Y3 Footer Primary', 'Y3 Primary Sentinel', '/y3-primary/' ],
    'footer-contact' => [ 'Y3 Footer Contact', 'Y3 Contact Sentinel', '/y3-contact/' ],
    'footer-social'  => [ 'Y3 Footer Social', 'Y3 Social Sentinel', '/y3-social/' ],
    'footer-policy'  => [ 'Y3 Footer Policy', 'Y3 Policy Sentinel', '/y3-policy/' ],
];
$menu_ids = [];
$locations = [];

foreach ( $menu_definitions as $location => $definition ) {
    [ $menu_name, $label, $path ] = $definition;
    $menu_id = wp_create_nav_menu( $menu_name );
    if ( is_wp_error( $menu_id ) || 0 >= (int) $menu_id ) {
        y3_runtime_fail( 'unable to create menu for ' . $location );
    }

    $item_id = wp_update_nav_menu_item(
        (int) $menu_id,
        0,
        [
            'menu-item-title'  => $label,
            'menu-item-url'    => home_url( $path ),
            'menu-item-status' => 'publish',
        ]
    );
    if ( is_wp_error( $item_id ) || 0 >= (int) $item_id ) {
        y3_runtime_fail( 'unable to create sentinel menu item for ' . $location );
    }

    $menu_ids[ $location ] = (int) $menu_id;
    $locations[ $location ] = (int) $menu_id;
}
set_theme_mod( 'nav_menu_locations', $locations );
y3_runtime_set_preset( 'standard' );

if ( ! function_exists( 'AZnet\\Theme\\footer_context' ) || ! function_exists( 'AZnet\\Theme\\footer_preset' ) ) {
    y3_runtime_fail( 'Footer presentation helpers are unavailable in the active Theme' );
}

$context = \AZnet\Theme\footer_context();
foreach ( array_keys( $menu_definitions ) as $location ) {
    if ( '' === trim( (string) ( $context['menus'][ $location ] ?? '' ) ) ) {
        y3_runtime_fail( 'assigned WordPress menu did not reach Footer context: ' . $location );
    }
}

foreach ( [ 'standard', 'professional', 'compact', 'law-01' ] as $preset ) {
    y3_runtime_set_preset( $preset );
    $html = y3_runtime_home_html();
    $footer = y3_runtime_footer_html( $html );

    if ( 1 !== substr_count( $html, 'role="contentinfo"' ) && 1 !== substr_count( $html, 'role="contentinfo"' ) ) {
        y3_runtime_fail( 'expected exactly one contentinfo landmark for preset ' . $preset );
    }
    if ( ! str_contains( $footer, 'aznet-theme-site-footer--' . $preset ) ) {
        y3_runtime_fail( 'normalized Footer preset class missing: ' . $preset );
    }
    foreach ( $menu_definitions as $definition ) {
        if ( ! str_contains( $footer, (string) $definition[1] ) ) {
            y3_runtime_fail( 'assigned sentinel link missing for preset ' . $preset );
        }
    }
}

$region_assertions = [
    'footer'         => [ 'aznet-theme-site-footer__navigation', 'Khám phá' ],
    'footer-contact' => [ 'aznet-theme-site-footer__contact', 'Liên hệ' ],
    'footer-social'  => [ 'aznet-theme-site-footer__social', '' ],
    'footer-policy'  => [ 'aznet-theme-site-footer__policies', '' ],
];

foreach ( $menu_definitions as $location => $definition ) {
    $reduced = $locations;
    unset( $reduced[ $location ] );
    set_theme_mod( 'nav_menu_locations', $reduced );

    $footer = y3_runtime_footer_html( y3_runtime_home_html() );
    [ $region_class, $heading ] = $region_assertions[ $location ];

    if ( str_contains( $footer, $region_class ) ) {
        y3_runtime_fail( 'empty menu left an orphan Footer region: ' . $location );
    }
    if ( str_contains( $footer, (string) $definition[1] ) ) {
        y3_runtime_fail( 'empty menu left its sentinel content rendered: ' . $location );
    }
    if ( '' !== $heading && str_contains( $footer, '>' . $heading . '<' ) ) {
        y3_runtime_fail( 'empty menu left an orphan heading: ' . $location );
    }
}

set_theme_mod( 'nav_menu_locations', $locations );
y3_runtime_set_preset( 'standard' );

$front_url = get_permalink( (int) $front_id );
if ( ! is_string( $front_url ) || '' === $front_url ) {
    y3_runtime_fail( 'unable to resolve front-page fixture URL' );
}

echo wp_json_encode(
    [
        'front_id'  => (int) $front_id,
        'front_url' => $front_url,
        'menu_ids'  => $menu_ids,
        'locations' => array_keys( $menu_definitions ),
    ],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

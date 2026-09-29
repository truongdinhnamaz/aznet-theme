<?php
/**
 * Theme-owned Footer presentation resolver and context.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the normalized Footer presentation preset.
 */
function footer_preset(): string {
    $preset = setting( 'footer_preset', 'standard' );

    return in_array( $preset, [ 'standard', 'professional', 'compact', 'law-01' ], true )
        ? (string) $preset
        : 'standard';
}

/**
 * Build Footer presentation context from WordPress-native state.
 *
 * @return array{preset:string,site_title:string,tagline:string,home_url:string,logo_html:string,menus:array<string,string>,year:string}
 */
function footer_context(): array {
    $site_title = trim( (string) get_bloginfo( 'name' ) );
    $tagline    = trim( (string) get_bloginfo( 'description' ) );
    $home_url   = (string) home_url( '/' );
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

    $about_intro = '';
    $services    = [];

    if ( 'law-01' === footer_preset() ) {
        if ( function_exists( __NAMESPACE__ . '\\homepage_source_value' ) ) {
            $about_id = (int) homepage_source_value( 'law-01', 'about' );
            if ( $about_id > 0 && function_exists( 'get_post_field' ) ) {
                $about_intro = trim( (string) get_post_field( 'post_excerpt', $about_id ) );
            }

            $services_id = (int) homepage_source_value( 'law-01', 'services' );
            if ( $services_id > 0 && function_exists( __NAMESPACE__ . '\\homepage_renderable_child_pages' ) ) {
                foreach ( homepage_renderable_child_pages( $services_id, 4 ) as $service_page ) {
                    if ( ! $service_page instanceof \WP_Post ) {
                        continue;
                    }
                    $title = trim( (string) get_the_title( $service_page ) );
                    $url   = (string) get_permalink( $service_page );
                    if ( '' !== $title && '' !== $url ) {
                        $services[] = [
                            'title' => $title,
                            'url'   => $url,
                        ];
                    }
                }
            }
        }
    }

    $social_channels = [];
    if ( 'law-01' === footer_preset() && function_exists( 'get_nav_menu_locations' ) && function_exists( 'wp_get_nav_menu_items' ) ) {
        $locations = get_nav_menu_locations();
        $social_menu_id = (int) ( $locations['footer-social'] ?? 0 );
        if ( $social_menu_id > 0 ) {
            $social_items = wp_get_nav_menu_items( $social_menu_id );
            if ( is_array( $social_items ) ) {
                foreach ( $social_items as $item ) {
                    if ( 'publish' !== (string) ( $item->post_status ?? '' ) ) {
                        continue;
                    }
                    $classes = is_array( $item->classes ?? null ) ? $item->classes : [];
                    $key = '';
                    foreach ( [ 'facebook', 'youtube', 'linkedin', 'tiktok', 'instagram' ] as $candidate ) {
                        if ( in_array( 'aznet-theme-footer-field-' . $candidate, $classes, true ) ) {
                            $key = $candidate;
                            break;
                        }
                    }
                    $url = trim( (string) ( $item->url ?? '' ) );
                    if ( '' === $key || '' === $url ) {
                        continue;
                    }
                    $title = trim( (string) ( $item->title ?? '' ) );
                    $social_channels[] = [
                        'key'   => $key,
                        'title' => '' !== $title ? $title : ucfirst( $key ),
                        'url'   => $url,
                    ];
                }
            }
        }
    }

    $contact_links = [];
    if ( 'law-01' === footer_preset() && function_exists( 'get_nav_menu_locations' ) && function_exists( 'wp_get_nav_menu_items' ) ) {
        $locations       = get_nav_menu_locations();
        $contact_menu_id = (int) ( $locations['footer-contact'] ?? 0 );
        if ( $contact_menu_id > 0 ) {
            $contact_items = wp_get_nav_menu_items( $contact_menu_id );
            if ( is_array( $contact_items ) ) {
                $has_managed_location = false;
                $has_managed_phone    = false;
                $has_managed_email    = false;

                foreach ( $contact_items as $item ) {
                    $classes = is_array( $item->classes ?? null ) ? $item->classes : [];
                    $has_managed_location = $has_managed_location || in_array( 'aznet-theme-footer-field-location', $classes, true );
                    $has_managed_phone    = $has_managed_phone || in_array( 'aznet-theme-footer-field-phone', $classes, true );
                    $has_managed_email    = $has_managed_email || in_array( 'aznet-theme-footer-field-email', $classes, true );
                }

                foreach ( $contact_items as $item ) {
                    if ( 'publish' !== (string) ( $item->post_status ?? '' ) ) {
                        continue;
                    }

                    $url     = trim( (string) ( $item->url ?? '' ) );
                    $title   = trim( (string) ( $item->title ?? '' ) );
                    $classes = is_array( $item->classes ?? null ) ? $item->classes : [];
                    $key     = '';

                    if ( in_array( 'aznet-theme-footer-field-location', $classes, true ) ) {
                        $key = 'location';
                        $url = '';
                    } elseif ( in_array( 'aznet-theme-footer-field-phone', $classes, true ) ) {
                        $key   = 'phone';
                        $title = preg_replace( '/^tel:/i', '', $url ) ?? '';
                    } elseif ( in_array( 'aznet-theme-footer-field-email', $classes, true ) ) {
                        $key   = 'email';
                        $title = preg_replace( '/^mailto:/i', '', $url ) ?? '';
                    } elseif ( str_starts_with( strtolower( $url ), 'tel:' ) ) {
                        if ( $has_managed_phone ) {
                            continue;
                        }
                        $key = 'phone';
                    } elseif ( str_starts_with( strtolower( $url ), 'mailto:' ) ) {
                        if ( $has_managed_email ) {
                            continue;
                        }
                        $key = 'email';
                    } else {
                        if ( $has_managed_location ) {
                            continue;
                        }
                        $key = 'location';
                    }

                    if ( '' === $title ) {
                        continue;
                    }

                    $contact_links[] = [
                        'key'   => $key,
                        'title' => $title,
                        'url'   => $url,
                    ];
                }
            }
        }

        if ( ! empty( $contact_links ) ) {
            $website_title = function_exists( 'wp_parse_url' ) ? (string) wp_parse_url( $home_url, PHP_URL_HOST ) : '';
            if ( '' === $website_title ) {
                $website_title = preg_replace( '#^https?://#i', '', rtrim( $home_url, '/' ) ) ?? '';
            }
            if ( '' !== $website_title && '' !== $home_url ) {
                $contact_links[] = [
                    'key'   => 'website',
                    'title' => $website_title,
                    'url'   => $home_url,
                ];
            }
        }

        $contact_order = [
            'location' => 10,
            'phone'    => 20,
            'website'  => 30,
            'email'    => 40,
        ];
        usort(
            $contact_links,
            static fn ( array $left, array $right ): int =>
                ( $contact_order[ (string) ( $left['key'] ?? '' ) ] ?? 99 )
                <=>
                ( $contact_order[ (string) ( $right['key'] ?? '' ) ] ?? 99 )
        );
    }

    $menu_html = static function ( string $location, string $class_name ): string {
        if ( ! function_exists( 'wp_nav_menu' ) ) {
            return '';
        }

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

    return [
        'preset'      => footer_preset(),
        'site_title'  => $site_title,
        'tagline'     => $tagline,
        'home_url'    => $home_url,
        'logo_html'   => $logo_html,
        'about_intro' => $about_intro,
        'services'        => $services,
        'social_channels' => $social_channels,
        'contact_links'   => $contact_links,
        'menus'           => [
            'footer'         => $menu_html( 'footer', 'aznet-theme-site-footer__menu' ),
            'footer-contact' => $menu_html( 'footer-contact', 'aznet-theme-site-footer__contact-menu' ),
            'footer-social'  => $menu_html( 'footer-social', 'aznet-theme-site-footer__social-menu' ),
            'footer-policy'  => $menu_html( 'footer-policy', 'aznet-theme-site-footer__policy-menu' ),
        ],
        'year'        => function_exists( 'wp_date' ) ? (string) wp_date( 'Y' ) : '',
    ];
}

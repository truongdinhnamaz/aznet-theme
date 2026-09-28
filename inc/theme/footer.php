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
        'services'    => $services,
        'menus'       => [
            'footer'         => $menu_html( 'footer', 'aznet-theme-site-footer__menu' ),
            'footer-contact' => $menu_html( 'footer-contact', 'aznet-theme-site-footer__contact-menu' ),
            'footer-social'  => $menu_html( 'footer-social', 'aznet-theme-site-footer__social-menu' ),
            'footer-policy'  => $menu_html( 'footer-policy', 'aznet-theme-site-footer__policy-menu' ),
        ],
        'year'        => function_exists( 'wp_date' ) ? (string) wp_date( 'Y' ) : '',
    ];
}

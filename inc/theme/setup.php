<?php
/**
 * Generic WordPress theme setup.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function setup(): void {
    load_theme_textdomain( 'aznet-theme', get_template_directory() . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support(
        'html5',
        [
            'comment-list',
            'comment-form',
            'search-form',
            'gallery',
            'caption',
            'style',
            'script',
        ]
    );

    register_nav_menus(
        [
            'primary' => __( 'Primary Menu', 'aznet-theme' ),
            'footer'         => __( 'Footer Menu', 'aznet-theme' ),
            'footer-contact' => __( 'Footer Contact Menu', 'aznet-theme' ),
            'footer-social'  => __( 'Footer Social Menu', 'aznet-theme' ),
            'footer-policy'  => __( 'Footer Policy Menu', 'aznet-theme' ),
        ]
    );
}

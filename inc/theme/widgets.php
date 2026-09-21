<?php
/**
 * Theme-owned WordPress widget areas.
 *
 * WordPress owns widget placement and widget instance state. Integrations such
 * as ConvertFlow may provide widgets, but the Theme only provides presentation
 * regions through the public WordPress Widgets API.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Register native Theme widget areas. */
function register_widget_areas(): void {
    register_sidebar(
        [
            'name'          => __( 'Điều hướng bài viết', 'aznet-theme' ),
            'id'            => 'article-navigation',
            'description'   => __( 'Vùng tiện ích bên cạnh nội dung Bài viết, phù hợp cho mục lục hoặc điều hướng nội dung.', 'aznet-theme' ),
            'before_widget' => '<section id="%1$s" class="widget aznet-theme-article-navigation-widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="aznet-theme-article-navigation-widget__title">',
            'after_title'   => '</h2>',
        ]
    );
}

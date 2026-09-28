<?php
/**
 * WordPress-native Footer contact/social authoring adapter.
 *
 * The Theme owns the admin presentation only. Values are persisted as WordPress
 * nav-menu items in the registered Footer menu locations so switching themes
 * does not delete the underlying contact/social content.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Footer profile field definitions.
 *
 * @return array<string,array{label:string,location:string,type:string,title:string}>
 */
function footer_profile_definitions(): array {
    return [
        'phone' => [
            'label'    => __( 'Số điện thoại', 'aznet-theme' ),
            'location' => 'footer-contact',
            'type'     => 'tel',
            'title'    => __( 'Điện thoại', 'aznet-theme' ),
        ],
        'email' => [
            'label'    => __( 'Email', 'aznet-theme' ),
            'location' => 'footer-contact',
            'type'     => 'email',
            'title'    => __( 'Email', 'aznet-theme' ),
        ],
        'facebook' => [
            'label'    => __( 'Fanpage Facebook', 'aznet-theme' ),
            'location' => 'footer-social',
            'type'     => 'url',
            'title'    => __( 'Facebook', 'aznet-theme' ),
        ],
        'youtube' => [
            'label'    => __( 'YouTube', 'aznet-theme' ),
            'location' => 'footer-social',
            'type'     => 'url',
            'title'    => __( 'YouTube', 'aznet-theme' ),
        ],
        'linkedin' => [
            'label'    => __( 'LinkedIn', 'aznet-theme' ),
            'location' => 'footer-social',
            'type'     => 'url',
            'title'    => __( 'LinkedIn', 'aznet-theme' ),
        ],
        'tiktok' => [
            'label'    => __( 'TikTok', 'aznet-theme' ),
            'location' => 'footer-social',
            'type'     => 'url',
            'title'    => __( 'TikTok', 'aznet-theme' ),
        ],
        'instagram' => [
            'label'    => __( 'Instagram', 'aznet-theme' ),
            'location' => 'footer-social',
            'type'     => 'url',
            'title'    => __( 'Instagram', 'aznet-theme' ),
        ],
    ];
}

function footer_profile_marker( string $key ): string {
    return 'aznet-theme-footer-field-' . sanitize_html_class( $key );
}

/**
 * Find Theme-authored Footer profile items without inspecting private storage.
 *
 * @return array<string,string>
 */
function footer_profile_from_menus(): array {
    $values = [];
    foreach ( footer_profile_definitions() as $key => $definition ) {
        $values[ $key ] = '';
    }

    $locations = get_nav_menu_locations();
    foreach ( footer_profile_definitions() as $key => $definition ) {
        $menu_id = (int) ( $locations[ $definition['location'] ] ?? 0 );
        if ( $menu_id <= 0 ) {
            continue;
        }

        $items = wp_get_nav_menu_items( $menu_id, [ 'post_status' => 'any' ] );
        if ( ! is_array( $items ) ) {
            continue;
        }

        $marker = footer_profile_marker( $key );
        foreach ( $items as $item ) {
            $classes = is_array( $item->classes ?? null ) ? $item->classes : [];
            if ( ! in_array( $marker, $classes, true ) || 'publish' !== (string) ( $item->post_status ?? '' ) ) {
                continue;
            }

            $url = (string) ( $item->url ?? '' );
            if ( 'tel' === $definition['type'] ) {
                $url = preg_replace( '/^tel:/i', '', $url ) ?? '';
            } elseif ( 'email' === $definition['type'] ) {
                $url = preg_replace( '/^mailto:/i', '', $url ) ?? '';
            }
            $values[ $key ] = $url;
            break;
        }
    }

    return $values;
}

/**
 * Ensure a persistent WordPress menu is assigned to a Footer location.
 */
function footer_profile_menu_id( string $location ): int {
    $locations = get_nav_menu_locations();
    $menu_id   = (int) ( $locations[ $location ] ?? 0 );
    if ( $menu_id > 0 ) {
        return $menu_id;
    }

    $label = 'footer-contact' === $location ? 'AZnet Footer Contact' : 'AZnet Footer Social';
    $menu  = wp_get_nav_menu_object( $label );
    if ( $menu instanceof \WP_Term ) {
        $menu_id = (int) $menu->term_id;
    } else {
        $created = wp_create_nav_menu( $label );
        $menu_id = is_wp_error( $created ) ? 0 : (int) $created;
    }

    if ( $menu_id > 0 ) {
        $locations[ $location ] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }

    return $menu_id;
}

/**
 * Return a marked menu item ID when one already exists.
 */
function footer_profile_item_id( int $menu_id, string $key ): int {
    if ( $menu_id <= 0 ) {
        return 0;
    }
    $items = wp_get_nav_menu_items( $menu_id, [ 'post_status' => 'any' ] );
    if ( ! is_array( $items ) ) {
        return 0;
    }

    $marker = footer_profile_marker( $key );
    foreach ( $items as $item ) {
        $classes = is_array( $item->classes ?? null ) ? $item->classes : [];
        if ( in_array( $marker, $classes, true ) ) {
            return (int) $item->ID;
        }
    }
    return 0;
}

function footer_profile_sanitize_value( string $type, mixed $raw ): string {
    $value = is_string( $raw ) ? trim( wp_unslash( $raw ) ) : '';
    if ( 'email' === $type ) {
        return sanitize_email( $value );
    }
    if ( 'tel' === $type ) {
        return preg_replace( '/[^0-9+]/', '', $value ) ?? '';
    }
    return esc_url_raw( $value, [ 'http', 'https' ] );
}

function handle_footer_profile_save(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) );
    }
    check_admin_referer( 'aznet_theme_save_footer_profile' );

    if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
        require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
    }

    $raw = isset( $_POST['aznet_theme_footer_profile'] ) && is_array( $_POST['aznet_theme_footer_profile'] )
        ? $_POST['aznet_theme_footer_profile']
        : [];

    foreach ( footer_profile_definitions() as $key => $definition ) {
        $value   = footer_profile_sanitize_value( $definition['type'], $raw[ $key ] ?? '' );
        $menu_id = footer_profile_menu_id( $definition['location'] );
        if ( $menu_id <= 0 ) {
            continue;
        }

        $item_id = footer_profile_item_id( $menu_id, $key );
        $url     = $value;
        if ( '' !== $value && 'tel' === $definition['type'] ) {
            $url = 'tel:' . $value;
        } elseif ( '' !== $value && 'email' === $definition['type'] ) {
            $url = 'mailto:' . $value;
        }

        if ( '' === $value && $item_id <= 0 ) {
            continue;
        }

        $display_title = ( 'tel' === $definition['type'] || 'email' === $definition['type'] )
            ? $value
            : (string) $definition['title'];

        wp_update_nav_menu_item(
            $menu_id,
            $item_id,
            [
                'menu-item-title'   => $display_title,
                'menu-item-url'     => '' !== $url ? $url : '#',
                'menu-item-status'  => '' !== $value ? 'publish' : 'draft',
                'menu-item-type'    => 'custom',
                'menu-item-classes' => footer_profile_marker( $key ),
            ]
        );
    }

    $redirect = add_query_arg(
        [
            'page'         => 'aznet-theme',
            'section'      => 'overview',
            'footer_saved' => '1',
        ],
        admin_url( 'admin.php' )
    );
    wp_safe_redirect( $redirect );
    exit;
}

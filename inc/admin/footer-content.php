<?php
/**
 * WordPress-native rich Footer content authoring.
 *
 * Theme stores only a reference to a core wp_block post. Rich content remains
 * WordPress-owned and survives theme switching.
 *
 * @package AZnetTheme
 */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\normalize_settings;
use function AZnet\Theme\setting;
use function AZnet\Theme\settings;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function footer_content_block_id(): int {
    return (int) setting( 'footer_content_block', 0 );
}

function footer_content_seed_from_menus(): string {
    $values = function_exists( __NAMESPACE__ . '\\footer_profile_from_menus' ) ? footer_profile_from_menus() : [];
    $rows = [];
    $defs = [
        'location' => [ __( 'Địa chỉ', 'aznet-theme' ), '' ],
        'phone' => [ __( 'SĐT', 'aznet-theme' ), 'tel:' ],
        'email' => [ __( 'Email', 'aznet-theme' ), 'mailto:' ],
        'website' => [ __( 'Website', 'aznet-theme' ), '' ],
        'facebook' => [ __( 'Fanpage', 'aznet-theme' ), '' ],
        'youtube' => [ __( 'YouTube', 'aznet-theme' ), '' ],
        'linkedin' => [ __( 'LinkedIn', 'aznet-theme' ), '' ],
        'tiktok' => [ __( 'TikTok', 'aznet-theme' ), '' ],
        'instagram' => [ __( 'Instagram', 'aznet-theme' ), '' ],
    ];
    foreach ( $defs as $key => $def ) {
        $value = trim( (string) ( $values[ $key ] ?? '' ) );
        if ( '' === $value ) { continue; }
        $label = esc_html( (string) $def[0] );
        if ( 'location' === $key ) {
            $rows[] = '<strong>' . $label . ':</strong> ' . esc_html( $value );
            continue;
        }
        $href = in_array( $key, [ 'phone', 'email' ], true ) ? (string) $def[1] . $value : $value;
        $rows[] = '<strong>' . $label . ':</strong> <a href="' . esc_url( $href, [ 'http', 'https', 'tel', 'mailto' ] ) . '">' . esc_html( $value ) . '</a>';
    }
    return empty( $rows ) ? '' : '<p>' . implode( '<br>', $rows ) . '</p>';
}

function footer_content_editor_value(): string {
    $block_id = footer_content_block_id();
    if ( $block_id > 0 ) {
        $post = get_post( $block_id );
        if ( $post instanceof \WP_Post && 'wp_block' === $post->post_type ) {
            return (string) $post->post_content;
        }
    }
    return footer_content_seed_from_menus();
}

function handle_footer_content_save(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) );
    }
    check_admin_referer( 'aznet_theme_save_footer_content' );

    $content = isset( $_POST['aznet_theme_footer_content'] )
        ? wp_kses_post( wp_unslash( (string) $_POST['aznet_theme_footer_content'] ) )
        : '';

    $block_id = footer_content_block_id();
    $post = $block_id > 0 ? get_post( $block_id ) : null;
    if ( $post instanceof \WP_Post && 'wp_block' === $post->post_type ) {
        $result = wp_update_post(
            [
                'ID'           => $block_id,
                'post_content' => $content,
            ],
            true
        );
    } else {
        $result = wp_insert_post(
            [
                'post_type'    => 'wp_block',
                'post_status'  => 'publish',
                'post_title'   => __( 'AZnet Footer Content', 'aznet-theme' ),
                'post_content' => $content,
            ],
            true
        );
    }

    if ( is_wp_error( $result ) ) {
        wp_die( esc_html( $result->get_error_message() ) );
    }

    $raw = settings();
    $raw['footer_content_block'] = (int) $result;
    set_theme_mod( 'aznet_theme_settings', normalize_settings( $raw ) );

    wp_safe_redirect(
        add_query_arg(
            [ 'page' => 'aznet-theme', 'section' => 'footer', 'footer_saved' => '1' ],
            admin_url( 'admin.php' )
        )
    );
    exit;
}


if ( is_admin() ) {
    add_action( 'admin_post_aznet_theme_save_footer_content', __NAMESPACE__ . '\\handle_footer_content_save' );
}

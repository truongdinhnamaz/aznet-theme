<?php
namespace AZnet\Theme\Admin;

use function AZnet\Theme\normalize_settings;
use function AZnet\Theme\settings_defaults;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function require_settings_capability(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thay đổi thiết lập Theme.', 'aznet-theme' ) );
    }
}

function handle_save_settings(): void {
    require_settings_capability();
    check_admin_referer( 'aznet_theme_save_settings' );
    $raw = isset( $_POST['aznet_theme_settings'] ) && is_array( $_POST['aznet_theme_settings'] )
        ? wp_unslash( $_POST['aznet_theme_settings'] )
        : [];
    set_theme_mod( 'aznet_theme_settings', normalize_settings( $raw ) );
    $return_section = isset( $_POST['aznet_theme_return_section'] ) ? sanitize_key( wp_unslash( $_POST['aznet_theme_return_section'] ) ) : '';
    $allowed_sections = [ 'header', 'footer', 'homepage', 'provisioning', 'commerce', 'system-health' ];
    $redirect_args = [ 'page' => 'aznet-theme', 'updated' => '1' ];
    if ( in_array( $return_section, $allowed_sections, true ) ) {
        $redirect_args['section'] = $return_section;
    }
    wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
    exit;
}

function handle_reset_settings(): void {
    require_settings_capability();
    check_admin_referer( 'aznet_theme_reset_settings' );
    if ( '1' !== (string) ( $_POST['confirm_reset'] ?? '' ) ) {
        wp_die( esc_html__( 'Yêu cầu đặt lại chưa được xác nhận.', 'aznet-theme' ) );
    }
    set_theme_mod( 'aznet_theme_settings', settings_defaults() );
    wp_safe_redirect( add_query_arg( [ 'page' => 'aznet-theme', 'reset' => '1' ], admin_url( 'admin.php' ) ) );
    exit;
}

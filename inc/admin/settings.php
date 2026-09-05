<?php
/**
 * Secure Control Center write actions.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function redirect_url( string $notice ): string {
    return add_query_arg(
        'aznet_theme_notice',
        sanitize_key( $notice ),
        admin_url( 'admin.php?page=aznet-theme' )
    );
}

function assert_allowed(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thay đổi AZnet Theme.', 'aznet-theme' ) );
    }
}

function handle_save(): void {
    assert_allowed();
    check_admin_referer( 'aznet_theme_save_u0_settings' );

    $current = \AZnet\Theme\Settings\get();
    $current['preset'] = '';
    \AZnet\Theme\Settings\save( $current );

    wp_safe_redirect( redirect_url( 'saved' ) );
    exit;
}

function handle_reset(): void {
    assert_allowed();
    check_admin_referer( 'aznet_theme_reset_settings' );

    if ( ! isset( $_POST['confirm_reset'] ) || '1' !== sanitize_key( wp_unslash( $_POST['confirm_reset'] ) ) ) {
        wp_safe_redirect( redirect_url( 'confirm-reset' ) );
        exit;
    }

    \AZnet\Theme\Settings\reset();

    wp_safe_redirect( redirect_url( 'reset' ) );
    exit;
}

<?php
/** Admin-only Theme presentation console bootstrap. */
namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/control-center.php';
require_once __DIR__ . '/homepage.php';
require_once __DIR__ . '/provisioning.php';
require_once __DIR__ . '/settings-actions.php';
require_once __DIR__ . '/settings-portability.php';
require_once __DIR__ . '/system-health.php';
require_once __DIR__ . '/editor-policy.php';

function register_control_center(): void {
    $hook = add_menu_page(
        __( 'AZnet Theme', 'aznet-theme' ),
        __( 'AZnet Theme', 'aznet-theme' ),
        'edit_theme_options',
        'aznet-theme',
        __NAMESPACE__ . '\\render_control_center',
        'dashicons-admin-appearance',
        61
    );

    add_action( 'admin_enqueue_scripts', static function ( string $suffix ) use ( $hook ): void {
        if ( $suffix !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'aznet-theme-control-center',
            get_theme_file_uri( 'assets/css/admin/control-center.css' ),
            [],
            AZNET_THEME_VERSION
        );
    } );
}

if ( is_admin() ) {
    add_action( 'admin_menu', __NAMESPACE__ . '\\register_control_center' );
    add_action( 'admin_post_aznet_theme_save_settings', __NAMESPACE__ . '\\handle_save_settings' );
    add_action( 'admin_post_aznet_theme_reset_settings', __NAMESPACE__ . '\\handle_reset_settings' );
    add_action( 'admin_post_aznet_theme_export_settings', __NAMESPACE__ . '\\handle_export_settings' );
    add_action( 'admin_post_aznet_theme_import_settings', __NAMESPACE__ . '\\handle_import_settings' );
    add_action( 'admin_post_aznet_theme_provisioning_plan', __NAMESPACE__ . '\\handle_provisioning_plan' );
    add_action( 'admin_post_aznet_theme_provisioning_apply', __NAMESPACE__ . '\\handle_provisioning_apply' );
    add_action( 'admin_post_aznet_theme_provisioning_restore_indexing', __NAMESPACE__ . '\\handle_provisioning_restore_indexing' );
    add_filter( 'use_block_editor_for_post', __NAMESPACE__ . '\\use_classic_editor_for_regular_content', 10, 2 );
}

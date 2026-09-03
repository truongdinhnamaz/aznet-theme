<?php
/**
 * AZnet Theme admin bootstrap.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/control-center.php';
require_once __DIR__ . '/settings.php';

function bootstrap(): void {
    add_action( 'admin_menu', '\\AZnet\\Theme\\Admin\\ControlCenter\\register_menu' );
    add_action( 'admin_post_aznet_theme_save_u0_settings', '\\AZnet\\Theme\\Admin\\Settings\\handle_save' );
    add_action( 'admin_post_aznet_theme_reset_settings', '\\AZnet\\Theme\\Admin\\Settings\\handle_reset' );
}

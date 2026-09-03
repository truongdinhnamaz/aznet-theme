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

function bootstrap(): void {
    add_action( 'admin_menu', '\\AZnet\\Theme\\Admin\\ControlCenter\\register_menu' );
}

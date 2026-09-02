<?php
/**
 * AZnet Theme bootstrap entry point.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'AZNET_THEME_VERSION' ) ) {
    define( 'AZNET_THEME_VERSION', '0.1.0-alpha.7' );
}

require_once __DIR__ . '/inc/theme/bootstrap.php';

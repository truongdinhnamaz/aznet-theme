<?php
/**
 * Theme-owned bootstrap wiring only.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/homepage-content-map.php';
require_once __DIR__ . '/homepage-composer.php';
require_once __DIR__ . '/design-system.php';
require_once __DIR__ . '/setup.php';
require_once __DIR__ . '/patterns.php';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../integrations/woocommerce.php';
require_once __DIR__ . '/woocommerce-presentation.php';
require_once __DIR__ . '/woocommerce-product.php';
require_once __DIR__ . '/woocommerce-archive.php';
require_once __DIR__ . '/woocommerce-cart.php';
require_once __DIR__ . '/woocommerce-checkout.php';
require_once __DIR__ . '/woocommerce-account.php';
require_once __DIR__ . '/woocommerce-blocks.php';
require_once __DIR__ . '/content-shell.php';
require_once __DIR__ . '/assets.php';
require_once __DIR__ . '/../integrations/rootprofile.php';
require_once __DIR__ . '/contact-surface.php';
require_once __DIR__ . '/profile-surface.php';
require_once __DIR__ . '/rootprofile-current-surface.php';
require_once __DIR__ . '/../admin/bootstrap.php';

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup' );
add_action( 'init', __NAMESPACE__ . '\\register_pattern_categories' );
add_action( 'init', __NAMESPACE__ . '\\register_woocommerce_patterns', 20 );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_homepage_blueprint_editor_asset' );
add_filter( 'body_class', __NAMESPACE__ . '\\visual_preset_body_classes' );
add_filter( 'body_class', __NAMESPACE__ . '\\woocommerce_presentation_body_classes' );

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

require_once __DIR__ . '/setup.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/../integrations/woocommerce.php';
require_once __DIR__ . '/woocommerce-product.php';
require_once __DIR__ . '/woocommerce-archive.php';
require_once __DIR__ . '/woocommerce-cart.php';
require_once __DIR__ . '/woocommerce-checkout.php';
require_once __DIR__ . '/woocommerce-account.php';
require_once __DIR__ . '/content-shell.php';
require_once __DIR__ . '/assets.php';
require_once __DIR__ . '/../integrations/rootprofile.php';
require_once __DIR__ . '/contact-surface.php';
require_once __DIR__ . '/profile-surface.php';
require_once __DIR__ . '/rootprofile-current-surface.php';

if ( is_admin() ) {
    require_once __DIR__ . '/../admin/bootstrap.php';
    \AZnet\Theme\Admin\bootstrap();
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );

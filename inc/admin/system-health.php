<?php
namespace AZnet\Theme\Admin;

use function AZnet\Theme\settings;
use function AZnet\Theme\Integrations\WooCommerce\available as woo_available;
use function AZnet\Theme\Integrations\RootProfile\provider_available;
use function AZnet\Theme\Integrations\RootProfile\profile_provider_available;
use function AZnet\Theme\Integrations\RootProfile\current_surface_available;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function system_health_report(): array {
    $theme = wp_get_theme();
    $s = settings();
    return [
        'environment' => [
            'wordpress' => get_bloginfo( 'version' ),
            'php' => PHP_VERSION,
        ],
        'theme' => [
            'name' => $theme->get( 'Name' ),
            'version' => $theme->get( 'Version' ),
        ],
        'configuration' => [
            'visual_preset' => $s['visual_preset'] ?? 'default',
            'header_preset' => $s['header_preset'] ?? 'standard',
            'woo_catalog_preset' => $s['woo_catalog_preset'] ?? 'grid',
            'logo' => has_custom_logo(),
            'primary_menu' => has_nav_menu( 'primary' ),
        ],
        'capabilities' => [
            'woocommerce' => woo_available(),
            'rootprofile_v1' => provider_available(),
            'rootprofile_v2' => profile_provider_available(),
            'rootprofile_current_surface' => current_surface_available(),
            'convertflow' => 'unknown',
        ],
    ];
}

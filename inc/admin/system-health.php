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
        'standalone_core' => [
            'status' => 'ready',
            'visual_preset' => $s['visual_preset'] ?? 'default',
            'header_preset' => $s['header_preset'] ?? 'standard',
            'logo' => has_custom_logo(),
            'primary_menu' => has_nav_menu( 'primary' ),
        ],
        'optional_integrations' => [
            'woocommerce' => woo_available() ? 'available' : 'not_present',
            'rootprofile_v1' => provider_available() ? 'available' : 'not_present',
            'rootprofile_v2' => profile_provider_available() ? 'available' : 'not_present',
            'rootprofile_current_surface' => current_surface_available() ? 'available' : 'not_present',
            'convertflow' => 'unknown',
        ],
    ];
}

function support_snapshot(): array {
    return [
        'product' => 'aznet-theme',
        'report' => system_health_report(),
        'settings' => settings(),
    ];
}

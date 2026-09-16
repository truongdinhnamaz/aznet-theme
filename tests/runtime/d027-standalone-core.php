<?php
/** D-027 clean WordPress runtime verifier. Execute with `wp eval-file` after Law01 v1.1 provisioning. */

function aznet_d027_must( bool $ok, string $message ): void {
    if ( ! $ok ) {
        throw new RuntimeException( $message );
    }
}

$theme = wp_get_theme();
aznet_d027_must( 'aznet-theme' === $theme->get_stylesheet(), 'AZnet Theme is not active' );
aznet_d027_must( version_compare( get_bloginfo( 'version' ), '6.9', '>=' ), 'WordPress support floor not met' );
aznet_d027_must( version_compare( PHP_VERSION, '8.1', '>=' ), 'PHP support floor not met' );

$active_plugins = array_values( (array) get_option( 'active_plugins', [] ) );
aznet_d027_must( [] === $active_plugins, 'Standalone Core runtime has active third-party plugins: ' . implode( ',', $active_plugins ) );
if ( is_multisite() ) {
    aznet_d027_must( [] === (array) get_site_option( 'active_sitewide_plugins', [] ), 'Standalone Core runtime has network-active plugins' );
}

foreach ( [
    'AZnet\\Theme\\settings',
    'AZnet\\Theme\\provisioning_readiness',
    'AZnet\\Theme\\homepage_composer_active',
    'AZnet\\Theme\\Admin\\system_health_report',
    'AZnet\\Theme\\Admin\\support_snapshot',
] as $function ) {
    aznet_d027_must( function_exists( $function ), 'Missing Core function: ' . $function );
}

$settings = AZnet\Theme\settings();
aznet_d027_must( 'law-01' === (string) ( $settings['homepage_preset'] ?? 'off' ), 'Law01 presentation preset is not active' );
aznet_d027_must( 'page' === (string) get_option( 'show_on_front', 'posts' ), 'Static Front Page is not configured' );
$front_id = (int) get_option( 'page_on_front', 0 );
aznet_d027_must( $front_id > 0 && get_post( $front_id ) instanceof WP_Post, 'Provisioned Front Page is missing' );
aznet_d027_must( AZnet\Theme\homepage_composer_active(), 'Homepage Composer is not active after provisioning' );

$readiness = AZnet\Theme\provisioning_readiness();
aznet_d027_must( true === ( $readiness['setup_ready'] ?? false ), 'Provisioning setup is not ready: ' . implode( ',', (array) ( $readiness['missing_required'] ?? [] ) ) );

$locations = get_nav_menu_locations();
aznet_d027_must( (int) ( $locations['primary'] ?? 0 ) > 0, 'Primary menu is missing after provisioning' );

$owned = [];
foreach ( [ 'page', 'post', 'attachment' ] as $post_type ) {
    $owned[ $post_type ] = get_posts( [
        'post_type' => $post_type,
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [ [ 'key' => '_aznet_theme_provisioned_by', 'value' => 'law01-v1-1' ] ],
    ] );
    aznet_d027_must( [] !== $owned[ $post_type ], 'No WordPress-owned provisioned ' . $post_type . ' objects found' );
}

$thumb_id = (int) get_post_thumbnail_id( $front_id );
aznet_d027_must( $thumb_id > 0, 'Provisioned Front Page Hero attachment is missing' );
$thumb_url = (string) wp_get_attachment_url( $thumb_id );
aznet_d027_must( false !== strpos( $thumb_url, '/uploads/' ), 'Hero is not served from WordPress uploads' );
aznet_d027_must( false === strpos( $thumb_url, '/themes/aznet-theme/' ), 'Hero still depends on Theme package path at runtime' );

$report = AZnet\Theme\Admin\system_health_report();
aznet_d027_must( isset( $report['standalone_core'], $report['optional_integrations'] ), 'System Health does not separate Core from Optional Integrations' );
aznet_d027_must( 'ready' === (string) ( $report['standalone_core']['status'] ?? '' ), 'Standalone Core health is not READY' );
foreach ( [ 'woocommerce', 'rootprofile_v1', 'rootprofile_v2', 'rootprofile_current_surface' ] as $provider ) {
    aznet_d027_must( 'not_present' === (string) ( $report['optional_integrations'][ $provider ] ?? '' ), 'Optional provider unexpectedly present: ' . $provider );
}
aznet_d027_must( 'unknown' === (string) ( $report['optional_integrations']['convertflow'] ?? '' ), 'ConvertFlow absence must remain informational/unknown without a public detector' );

$snapshot = AZnet\Theme\Admin\support_snapshot();
aznet_d027_must( 'aznet-theme' === (string) ( $snapshot['product'] ?? '' ), 'Support Snapshot product identity mismatch' );
aznet_d027_must( isset( $snapshot['report']['standalone_core'], $snapshot['report']['optional_integrations'] ), 'Support Snapshot lost D-027 health separation' );

echo "PASS: D-027 clean WordPress standalone runtime\n";

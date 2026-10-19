<?php
/** D-050A: privileged Theme admin entry is visible only to the named operator. */
declare(strict_types=1);
define( 'ABSPATH', '/mock/' );
$GLOBALS['caps'] = [];
function current_user_can( string $cap ): bool { return $GLOBALS['caps'][$cap] ?? false; }
function is_multisite(): bool { return false; }
function admin_url( string $relative ): string { return 'https://example.test/wp-admin/' . $relative; }
function add_query_arg( array $query, string $base ): string { return $base . '?' . http_build_query( $query ); }
function esc_url( string $url ): string { return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' ); }
function esc_html__( string $message, string $domain ): string { return htmlspecialchars( $message, ENT_QUOTES, 'UTF-8' ); }
require dirname( __DIR__, 2 ) . '/inc/admin/danger-zone.php';
function check_entry( bool $okay, string $message ): void {
    if ( ! $okay ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); }
}
check_entry( function_exists( 'AZnet\\Theme\\Admin\\render_danger_zone_entry_link' ), 'Theme must provide a discreet privileged entry link in its own management screen' );
$entry = static function (): string {
    ob_start();
    \AZnet\Theme\Admin\render_danger_zone_entry_link();
    return (string) ob_get_clean();
};
check_entry( '' === $entry(), 'ordinary user sees no link' );
$GLOBALS['caps']['manage_options'] = true;
check_entry( '' === $entry(), 'ordinary Administrator alone does not see privileged entry' );
$GLOBALS['caps']['manage_options'] = false;
$GLOBALS['caps']['aznet_theme_manage_danger_zone'] = true;
check_entry( '' === $entry(), 'special capability without manage_options never discloses entry' );
$GLOBALS['caps']['manage_options'] = true;
$html = $entry();
check_entry( str_contains( $html, 'Khu vực nguy hiểm' ), 'named authorized operator sees a Theme-owned card' );
check_entry( str_contains( $html, 'https://example.test/wp-admin/admin.php?page=aznet-theme&amp;section=danger-zone' ), 'entry targets only Theme admin route' );
check_entry( ! str_contains( $html, 'page=aznet-site-guard' ) && ! str_contains( $html, 'admin-post.php' ), 'not a competing Site Guard admin interface or mutation control' );
$control = (string) file_get_contents( dirname( __DIR__, 2 ) . '/inc/admin/control-center.php' );
check_entry( str_contains( $control, 'render_danger_zone_entry_link();' ), 'AZnet Theme Support composes the discreet privileged entry' );
check_entry( ! str_contains( $control, "'danger-zone' =>" ), 'Danger Zone remains outside ordinary navigation tabs' );
echo "PASS: D050 privileged Theme-only discovery, independent Site Guard owner, default hidden\n";

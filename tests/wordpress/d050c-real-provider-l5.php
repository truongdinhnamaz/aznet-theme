<?php
/**
 * D-050C L5: execute from WP-CLI eval-file against disposable, fully installed
 * WordPress + AZnet Theme PR candidate + exact Site Guard provider checkout.
 * This is integration evidence; no provider source may live in Theme repo.
 */
// WP-CLI eval-file wraps this source in eval; declare(strict_types=1) is invalid.
function d050c_l5_check( $ok, string $why ): void {
    if ( ! $ok ) {
        fwrite( STDERR, "FAIL L5: {$why}\n" );
        exit( 1 );
    }
}
$expected_wp = getenv( 'D050C_EXPECT_WP' );
d050c_l5_check( is_string( $expected_wp ) && $expected_wp === get_bloginfo( 'version' ), 'WP version must match matrix' );
$expected_theme = getenv( 'D050C_THEME_HEAD' );
$expected_provider = getenv( 'D050C_PROVIDER_SHA' );
d050c_l5_check( is_string( $expected_theme ) && strlen( $expected_theme ) === 40, 'Theme checkout pinned in CI' );
d050c_l5_check( is_string( $expected_provider ) && strlen( $expected_provider ) === 40, 'provider checkout pinned in CI' );
d050c_l5_check( get_stylesheet() === 'aznet-theme', 'actual candidate Theme active' );
d050c_l5_check( function_exists( '\\AZnet\\Theme\\Admin\\site_guard_admin_projection_v1' ), 'AZnet Theme v1 consumer actually loaded' );
d050c_l5_check( function_exists( '\\AZnet\\Theme\\Admin\\danger_zone_is_authorized' ), 'Theme-owned Danger Zone retained' );
d050c_l5_check( has_filter( 'aznet_site_guard/v1/admin_projection' ) !== false, 'Site Guard v1 provider hook missing' );
d050c_l5_check( is_readable( WPMU_PLUGIN_DIR . '/aznet-site-guard.php' ), 'independent site-owned MU loader absent' );
d050c_l5_check( is_readable( WPMU_PLUGIN_DIR . '/aznet-site-guard/class-site-guard.php' ), 'independent site-owned MU dependency absent' );
d050c_l5_check( get_option( 'show_on_front' ) === 'page', 'fixture is a static homepage' );
$front = (int) get_option( 'page_on_front' );
d050c_l5_check( $front > 0 && get_post_status( $front ) === 'publish', 'fixture homepage published' );
$second = (int) get_option( 'd050c_replacement_page' );
d050c_l5_check( $second > 0 && $second !== $front, 'isolated alternate page seeded' );
if ( ! function_exists( 'set_current_screen' ) ) {
    require_once ABSPATH . 'wp-admin/includes/screen.php';
}
set_current_screen( 'dashboard' );
$ordinary = get_user_by( 'login', 'ordinary' );
$operator = get_user_by( 'login', 'owner' );
$theme_only = get_user_by( 'login', 'themeonly' );
$guard_only = get_user_by( 'login', 'guardonly' );
foreach ( [ $ordinary, $operator, $theme_only, $guard_only ] as $user ) {
    d050c_l5_check( $user instanceof WP_User && in_array( 'administrator', $user->roles, true ), 'four distinct test administrators' );
}
wp_set_current_user( (int) $ordinary->ID );
$p = apply_filters( 'aznet_site_guard/v1/admin_projection', null );
d050c_l5_check( is_array( $p ) && $p['contract'] === 'aznet_site_guard_admin' && $p['major'] === 1, 'actual v1 projection' );
d050c_l5_check( $p['operator_authorized'] === false && $p['actor_mode'] === 'locked', 'ordinary admin has no Site Guard operator rights' );
d050c_l5_check( \AZnet\Theme\Admin\danger_zone_is_authorized() === false, 'ordinary admin has no Theme Danger Zone rights' );
d050c_l5_check( current_user_can( 'switch_themes' ) === false, 'locked ordinary admin denied WordPress theme switch capability' );
$result = update_option( 'page_on_front', $second );
d050c_l5_check( (int) get_option( 'page_on_front' ) === $front, 'ordinary WordPress homepage option mutation is rejected' );
$trashed = wp_trash_post( $front );
d050c_l5_check( false === $trashed && get_post_status( $front ) === 'publish', 'ordinary admin cannot trash active homepage through WP API' );
wp_set_current_user( (int) $theme_only->ID );
d050c_l5_check( \AZnet\Theme\Admin\danger_zone_is_authorized() === true, 'Theme-only operator can render Theme Danger Zone' );
$p = apply_filters( 'aznet_site_guard/v1/admin_projection', null );
d050c_l5_check( is_array( $p ) && $p['operator_authorized'] === false, 'Theme capability does not grant site-owned unlock' );
$consumer = \AZnet\Theme\Admin\site_guard_admin_projection_v1();
d050c_l5_check( $consumer['state'] === 'available' && $consumer['data']['operator_authorized'] === false, 'Theme consumes actual limited-permission provider projection' );
wp_set_current_user( (int) $guard_only->ID );
d050c_l5_check( \AZnet\Theme\Admin\danger_zone_is_authorized() === false, 'Site Guard operator does not gain Theme Danger Zone permission' );
$p = apply_filters( 'aznet_site_guard/v1/admin_projection', null );
d050c_l5_check( is_array( $p ) && $p['operator_authorized'] === true, 'Site Guard operator authority independent of Theme' );
wp_set_current_user( (int) $operator->ID );
d050c_l5_check( \AZnet\Theme\Admin\danger_zone_is_authorized() === true, 'named owner has independent Theme authority' );
$p = \AZnet\Theme\Admin\site_guard_admin_projection_v1();
d050c_l5_check( $p['state'] === 'available' && $p['data']['operator_authorized'] === true, 'Theme gets active real provider projection for owner' );
d050c_l5_check( $p['data']['actor_mode'] === 'locked' && $p['data']['active_scope'] === null, 'provider default lock before authenticated session grants' );
d050c_l5_check( count( $p['data']['scopes'] ) === 4, 'public contract contains only 4 approved scopes' );
d050c_l5_check( (int) get_option( 'page_on_front' ) === $front, 'homepage remained unchanged throughout integration assertions' );
echo "PASS L5: exact real WordPress with 2 separately-owned products; v1 projection, authorization split, homepage guard\n";

<?php
/**
 * D-050C L5: real independent Site Guard MU-plugin + AZnet Theme consumer.
 * Execute with wp eval-file in a disposable WordPress installation only.
 * Intentionally RED when only AZnet Theme is installed.
 */

function d050c_l5_assert( bool $condition, string $description ): void {
    if ( ! $condition ) {
        fwrite( STDERR, "FAIL L5: {$description}\n" );
        exit( 1 );
    }
}
if ( ! function_exists( 'set_current_screen' ) ) {
    require_once ABSPATH . 'wp-admin/includes/screen.php';
}
set_current_screen( 'dashboard' );
d050c_l5_assert( is_admin(), 'real WordPress admin context required' );
d050c_l5_assert( 'aznet-theme' === get_option( 'stylesheet' ), 'AZnet Theme active before L5' );
d050c_l5_assert( false !== has_filter( 'aznet_site_guard/v1/admin_projection' ), 'provider contract missing' );
require_once get_template_directory() . '/inc/admin/site-guard-consumer.php';

function d050c_l5_login_as( string $login ): int {
    $user = get_user_by( 'login', $login );
    d050c_l5_assert( $user instanceof WP_User, 'fixture account exists: ' . $login );
    $id = (int) $user->ID;
    wp_set_current_user( $id );
    $expiry = time() + 3600;
    $token = WP_Session_Tokens::get_instance( $id )->create( $expiry );
    $_COOKIE[LOGGED_IN_COOKIE] = wp_generate_auth_cookie( $id, $expiry, 'logged_in', $token );
    d050c_l5_assert( '' !== wp_get_session_token(), 'real login token for: ' . $login );
    return $id;
}

$version = getenv( 'D050C_EXPECTED_WP_VERSION' ) ?: '6.9';
d050c_l5_assert( $version === get_bloginfo( 'version' ), 'expected WordPress ' . $version );
$front = (int) get_option( 'page_on_front' );
d050c_l5_assert( $front > 0 && 'page' === get_option( 'show_on_front' ), 'fixture uses published static homepage' );

d050c_l5_login_as( 'restricted' );
$restricted = apply_filters( 'aznet_site_guard/v1/admin_projection', null );
d050c_l5_assert( is_array( $restricted ) && 1 === $restricted['major'], 'real Site Guard public v1 projection' );
d050c_l5_assert( false === $restricted['operator_authorized'] && 'locked' === $restricted['actor_mode'], 'ordinary Administrator has no provider operator rights' );
d050c_l5_assert( ! current_user_can( 'switch_themes' ), 'real WP default Theme switch denied' );
d050c_l5_assert( ! current_user_can( 'install_themes' ), 'real WP default Theme install denied' );
d050c_l5_assert( current_user_can( 'update_themes' ), 'Theme security updates remain available' );
d050c_l5_assert( ! current_user_can( 'delete_post', $front ), 'published Front Page deletion denied' );
d050c_l5_assert( false === wp_trash_post( $front ), 'real WordPress trash blocked on active Front Page' );
update_option( 'page_on_front', 0 );
d050c_l5_assert( (int) get_option( 'page_on_front' ) === $front, 'critical homepage option remains unchanged' );

d050c_l5_login_as( 'themeonly' );
d050c_l5_assert( current_user_can( 'aznet_theme_manage_danger_zone' ), 'Theme-only actor has Theme capability' );
$projection = \AZnet\Theme\Admin\site_guard_admin_projection_v1();
d050c_l5_assert( 'available' === $projection['state'] && ! $projection['data']['operator_authorized'], 'Theme-only actor cannot control Site Guard' );
ob_start();
\AZnet\Theme\Admin\render_site_guard_admin_controls();
$readonly = (string) ob_get_clean();
d050c_l5_assert( str_contains( $readonly, 'chưa được cấp quyền vận hành' ) && ! str_contains( $readonly, 'name="guard_scope"' ), 'Theme view cannot grant provider authority' );

d050c_l5_login_as( 'guardonly' );
d050c_l5_assert( ! current_user_can( 'aznet_theme_manage_danger_zone' ), 'Site Guard-only actor lacks Theme danger permission' );
$projection = \AZnet\Theme\Admin\site_guard_admin_projection_v1();
d050c_l5_assert( 'available' === $projection['state'] && $projection['data']['operator_authorized'], 'Site Guard independent of Theme permission' );

d050c_l5_login_as( 'owner' );
d050c_l5_assert( current_user_can( 'aznet_theme_manage_danger_zone' ), 'owner has explicit Theme danger capability' );
$projection = \AZnet\Theme\Admin\site_guard_admin_projection_v1();
d050c_l5_assert(
    'available' === $projection['state']
    && 'active' === $projection['data']['enforcement']
    && $projection['data']['operator_authorized']
    && 'locked' === $projection['data']['actor_mode'],
    'full owner sees real locked provider through Theme validated contract'
);
ob_start();
\AZnet\Theme\Admin\render_site_guard_admin_controls();
$html = (string) ob_get_clean();
d050c_l5_assert(
    str_contains( $html, 'aznet_site_guard_unlock' )
    && str_contains( $html, 'name="guard_scope"' )
    && str_contains( $html, 'name="guard_confirm"' )
    && str_contains( $html, 'name="guard_return_to"' ),
    'Theme renders only Site Guard-owned POST contract'
);
d050c_l5_assert( ! str_contains( $html, 'CHƯA XÁC MINH' ), 'real provider must not appear absent' );
d050c_l5_assert( (int) get_option( 'page_on_front' ) === $front && 'publish' === get_post_status( $front ), 'homepage still safe' );
fwrite( STDOUT, "PASS L5: real Site Guard public contract consumed by AZnet Theme with 4 separated roles, WordPress protection and homepage continuity\n" );

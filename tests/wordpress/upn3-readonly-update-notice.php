<?php
/**
 * UPN3 disposable WordPress proof: use WP-CLI eval-file with WordPress loaded.
 *
 * No live GitHub release is required. WordPress.org and GitHub HTTP calls are
 * intercepted; only test transients are touched and restored.
 *
 * Required environment:
 *   AZT_UPN3_DISPOSABLE=YES_DISPOSABLE
 *   AZT_UPN3_EXPECT_BLOB=<40-character Git blob SHA of core-update-notice.php>
 *
 * Example on an independently provisioned disposable WordPress installation:
 *   AZT_UPN3_DISPOSABLE=YES_DISPOSABLE AZT_UPN3_EXPECT_BLOB=a3f118fb987aad11e7072475dfd80e1ac16c4d37 \
 *     wp eval-file tests/wordpress/upn3-readonly-update-notice.php --skip-plugins
 *
 * Do not execute on production. This does not install or update a theme.
 */

function aznet_upn3_assert( $condition, string $message ): void {
    if ( ! $condition ) {
        echo 'FAIL ' . $message . PHP_EOL;
        throw new RuntimeException( 'UPN3 FAIL: ' . $message );
    }
    echo 'PASS ' . $message . PHP_EOL;
}

aznet_upn3_assert( 'YES_DISPOSABLE' === getenv( 'AZT_UPN3_DISPOSABLE' ), 'Explicit disposable-site gate' );
aznet_upn3_assert( defined( 'ABSPATH' ) && function_exists( 'wp_get_theme' ), 'Real WordPress bootstrapped' );
aznet_upn3_assert( version_compare( get_bloginfo( 'version' ), '6.9', '>=' ), 'WordPress >= 6.9' );
aznet_upn3_assert( version_compare( PHP_VERSION, '8.1', '>=' ), 'PHP >= 8.1' );
aznet_upn3_assert( ! is_multisite(), 'Single-site test scope (multisite is separate)' );
aznet_upn3_assert( 'aznet-theme' === get_stylesheet(), 'AZnet Theme candidate active' );
aznet_upn3_assert( [] === (array) get_option( 'active_plugins', [] ), 'No third-party active plugins' );

$theme = wp_get_theme( 'aznet-theme' );
$canonical_uri = 'https://github.com/truongdinhnamaz/aznet-theme';
$github_endpoint = 'https://api.github.com/repos/truongdinhnamaz/aznet-theme/releases/latest';
aznet_upn3_assert( $theme->exists() && $canonical_uri === $theme->get( 'UpdateURI' ), 'Candidate Update URI present' );

$version = $theme->get( 'Version' );
aznet_upn3_assert( is_string( $version ) && 1 === preg_match( '/^\d+\.\d+\.\d+$/D', $version ), 'Installed Theme semver valid' );
$parts = explode( '.', $version );
++$parts[2];
$new_version = implode( '.', $parts );
$new_tag = 'v' . $new_version;
$new_url = $canonical_uri . '/releases/tag/' . $new_tag;

$module = get_template_directory() . '/inc/theme/core-update-notice.php';
aznet_upn3_assert( is_file( $module ) && is_readable( $module ), 'Updater module installed on Theme path' );
$bytes = file_get_contents( $module );
$expected_blob = getenv( 'AZT_UPN3_EXPECT_BLOB' );
aznet_upn3_assert(
    is_string( $bytes ) && is_string( $expected_blob ) && 1 === preg_match( '/^[0-9a-f]{40}$/D', $expected_blob ) &&
    hash_equals( $expected_blob, sha1( 'blob ' . strlen( $bytes ) . "\0" . $bytes ) ),
    'Updater module bytes match pinned Git blob'
);

$notice_fn = 'AZnet\\Theme\\core_theme_update_admin_notice';
$filter_fn = 'AZnet\\Theme\\core_theme_update_notice';
aznet_upn3_assert( function_exists( $notice_fn ) && function_exists( $filter_fn ), 'UPN2 runtime functions loaded' );
aznet_upn3_assert( false !== has_filter( 'update_themes_github.com', $filter_fn ), 'Native WordPress update host filter registered' );
aznet_upn3_assert( false !== has_action( 'admin_notices', $notice_fn ), 'Native WordPress admin notice action registered' );

$admin = 0;
foreach ( get_users( [ 'role' => 'administrator', 'number' => 20, 'fields' => 'ids' ] ) as $user_id ) {
    if ( user_can( (int) $user_id, 'update_themes' ) ) {
        $admin = (int) $user_id;
        break;
    }
}
aznet_upn3_assert( 0 < $admin, 'Disposable admin has update_themes capability' );

$prior_user = get_current_user_id();
$cache_key = 'aznet_theme_core_release_notice_v1';
$old_cache = get_transient( $cache_key );
$old_updates = get_site_transient( 'update_themes' );
$mode = 'same';
$github_calls = 0;
$wporg_calls = 0;

$mock_http = static function ( $preempt, $arguments, $url ) use (
    &$mode, &$github_calls, &$wporg_calls, $github_endpoint, $canonical_uri, $version, $new_tag
) {
    if ( $github_endpoint === $url ) {
        ++$github_calls;
        // Never print inside the HTTP mock: the caller captures admin notice
        // markup with ob_start() and needs an empty string for same-version.
        if ( ( $arguments['limit_response_size'] ?? null ) !== 262144
            || ( $arguments['timeout'] ?? 10 ) > 3
            || ( $arguments['redirection'] ?? 1 ) !== 0 ) {
            throw new RuntimeException( 'UPN3 FAIL: GitHub metadata request is unbounded' );
        }
        if ( 'outage' === $mode ) {
            return new WP_Error( 'upn3_mock_timeout', 'Controlled test outage' );
        }
        $tag = 'same' === $mode ? 'v' . $version : $new_tag;
        return [
            'headers' => [],
            'response' => [ 'code' => 200, 'message' => 'OK' ],
            'body' => wp_json_encode( [
                'draft' => false,
                'prerelease' => false,
                'tag_name' => $tag,
                'html_url' => $canonical_uri . '/releases/tag/' . $tag,
            ] ),
            'cookies' => [],
        ];
    }
    if ( 1 === preg_match( '~^https?://api\.wordpress\.org/themes/update-check/1\.1/?$~', $url ) ) {
        ++$wporg_calls;
        return [
            'headers' => [],
            'response' => [ 'code' => 200, 'message' => 'OK' ],
            'body' => wp_json_encode( [ 'themes' => [], 'no_update' => [], 'translations' => [] ] ),
            'cookies' => [],
        ];
    }
    // No outbound network access during this controlled runtime proof.
    return new WP_Error( 'upn3_external_blocked', 'Unapproved external request blocked in test' );
};

add_filter( 'pre_http_request', $mock_http, PHP_INT_MAX, 3 );

try {
    wp_set_current_user( $admin );

    $mode = 'same';
    delete_transient( $cache_key );
    ob_start();
    $notice_fn();
    $same_markup = ob_get_clean();
    aznet_upn3_assert( '' === $same_markup && 1 === $github_calls, 'Installed version: no admin notice' );

    $mode = 'newer';
    delete_transient( $cache_key );
    $github_calls = 0;
    ob_start();
    $notice_fn();
    $new_markup = ob_get_clean();
    aznet_upn3_assert(
        false !== strpos( $new_markup, 'AZnet Theme ' . $new_version . ' is available' ) &&
        false !== strpos( $new_markup, esc_url( $new_url ) ) &&
        false === strpos( $new_markup, 'update.php' ) &&
        false === strpos( $new_markup, '<form' ),
        'Newer official release: safe read-only admin notice'
    );
    aznet_upn3_assert( 1 === $github_calls, 'First new-release notice fetched once' );
    ob_start();
    $notice_fn();
    ob_end_clean();
    aznet_upn3_assert( 1 === $github_calls, 'Repeated admin notice uses cached release' );

    $theme_data = [ 'UpdateURI' => $canonical_uri, 'Version' => $version ];
    aznet_upn3_assert(
        false === apply_filters( 'update_themes_github.com', false, $theme_data, 'aznet-theme', [] ),
        'No installable native update response'
    );
    aznet_upn3_assert(
        'unchanged' === apply_filters( 'update_themes_github.com', 'unchanged', [ 'UpdateURI' => 'https://github.com/foreign/theme' ], 'aznet-theme', [] ),
        'Other GitHub Update URI remains untouched'
    );

    // Actual core routine, with WordPress.org response mocked: do not enqueue
    // a non-installable AZnet Theme update.
    delete_site_transient( 'update_themes' );
    $wporg_calls = 0;
    wp_update_themes( [ 'upn3_disposable' => '1' ] );
    $updates = get_site_transient( 'update_themes' );
    aznet_upn3_assert(
        0 < $wporg_calls && is_object( $updates ) &&
        empty( $updates->response['aznet-theme'] ),
        'Real WordPress update routine does not enqueue AZnet Theme'
    );

    $mode = 'outage';
    delete_transient( $cache_key );
    ob_start();
    $notice_fn();
    $outage_markup = ob_get_clean();
    aznet_upn3_assert( '' === $outage_markup, 'GitHub outage fails softly in WordPress' );

    wp_set_current_user( 0 );
    $mode = 'newer';
    $github_calls = 0;
    delete_transient( $cache_key );
    ob_start();
    $notice_fn();
    $denied_markup = ob_get_clean();
    aznet_upn3_assert( '' === $denied_markup && 0 === $github_calls, 'Unauthorized actor sees no notice and triggers no HTTP request' );
} finally {
    remove_filter( 'pre_http_request', $mock_http, PHP_INT_MAX );
    wp_set_current_user( $prior_user );
    if ( false === $old_cache ) {
        delete_transient( $cache_key );
    } else {
        set_transient( $cache_key, $old_cache, 6 * HOUR_IN_SECONDS );
    }
    if ( false === $old_updates ) {
        delete_site_transient( 'update_themes' );
    } else {
        set_site_transient( 'update_themes', $old_updates );
    }
}

echo 'UPN3 WP runtime proof PASS; actual browser rendering and production release remain separate gates.' . PHP_EOL;

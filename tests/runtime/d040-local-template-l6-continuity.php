<?php
/**
 * D-040 LOCAL-ZIP-v1 L6 continuity on a disposable WordPress install.
 *
 * The entry points intentionally run in separate wp-cli processes, so a
 * Theme switch or in-place Core replacement cannot be masked by a PHP
 * request-local cache. This fixture never runs on a public WordPress site.
 */
// WP-CLI eval-file is embedded in eval(): no top-level strict_types directive.

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$phase = (string) getenv( 'D040_L6_PHASE' );
$state_file = (string) getenv( 'D040_L6_STATE' );
$fail = static function ( string $message ): void {
    fwrite( STDERR, 'FAIL D040-L6 ' . (string) getenv( 'D040_L6_PHASE' ) . ': ' . $message . "\n" );
    exit( 1 );
};
$check = static function ( bool $condition, string $message ) use ( $fail ): void {
    if ( ! $condition ) { $fail( $message ); }
};
$check( in_array( $phase, [ 'seed', 'away', 'restored', 'upgrade' ], true ), 'unknown phase' );
$check( '' !== $state_file, 'missing isolated state path' );
$css_path = 'assets/css/components/homepage-curtain-01.css';
$package_option = 'aznet_theme_local_template_packages';

if ( 'seed' === $phase ) {
    $check( 'aznet-theme' === get_stylesheet(), 'AZnet Theme not active at seed' );
    $check( class_exists( '\ZipArchive' ), 'ZIP PHP extension missing' );
    $check( '1.3.110' === AZNET_THEME_VERSION, 'unexpected canonical Core version' );
    $check( [] === (array) get_option( $package_option, [] ), 'package registry is not empty at seed' );

    $post_id = wp_insert_post( [
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_title'   => 'D040 L6 native content sentinel',
        'post_content' => '<p>WordPress retains source-of-truth content.</p>',
    ], true );
    $check( is_int( $post_id ) && $post_id > 0, 'cannot create disposable native Post' );
    update_post_meta( $post_id, '_d040_l6_continuity', 'native-owner-preserved' );
    set_theme_mod( 'd040_l6_theme_presentation', 'theme-mod-retained' );

    $raw_css = file_get_contents( get_theme_file_path( '/' . $css_path ) );
    $check( is_string( $raw_css ) && '' !== $raw_css, 'bundled Curtain 01 CSS missing' );
    $fixture_css = $raw_css . "\n/* D040-L6 disposable-package continuity fixture */\n";
    $css_sha = hash( 'sha256', $fixture_css );
    $manifest = require get_theme_file_path( '/inc/theme/templates/curtain-01/manifest.php' );
    $manifest['version'] = '1.0.1';
    $manifest['assets']['homepage'] = [ $manifest['assets']['homepage'][0] ];
    $package = [
        'package_contract_version' => 1,
        'requires' => [ 'theme' => '1.3.110', 'wordpress' => '6.9', 'php' => '8.1' ],
        'files' => [ $css_path => $css_sha ],
    ];
    $zip_path = dirname( $state_file ) . '/local-presentation-fixture.zip';
    $zip = new ZipArchive();
    $check( true === $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ), 'cannot open fixture ZIP' );
    $check( $zip->addFromString( 'manifest.json', wp_json_encode( $manifest ) ), 'manifest ZIP write' );
    $check( $zip->addFromString( 'package.json', wp_json_encode( $package ) ), 'package ZIP write' );
    $check( $zip->addFromString( $css_path, $fixture_css ), 'CSS ZIP write' );
    $zip->close();

    $zip_sha = (string) hash_file( 'sha256', $zip_path );
    $result = \AZnet\Theme\local_template_package_install( $zip_path, $zip_sha );
    $check( true === ( $result['ok'] ?? false ), 'install local CSS-only ZIP: ' . wp_json_encode( $result ) );
    $active = \AZnet\Theme\local_template_package_read_active( 'curtain-01' );
    $asset = \AZnet\Theme\local_template_package_asset( 'curtain-01', '/' . $css_path );
    $check( is_array( $active ) && is_array( $asset ), 'CSS package did not activate' );
    $check( '1.0.1' === ( $active['record']['version'] ?? null ), 'active version wrong' );
    $check( hash_file( 'sha256', $active['dir'] . '/' . $css_path ) === $css_sha, 'active CSS changed during install' );
    $check( str_contains( $asset['url'], '/aznet-theme-templates/releases/curtain-01/1.0.1-' ), 'CSS URL not from uploads' );

    $state = [
        'post_id' => $post_id,
        'post_content' => '<p>WordPress retains source-of-truth content.</p>',
        'css_sha' => $css_sha,
        'zip_sha' => $zip_sha,
        'active_record' => $active['record'],
        'dir' => $active['dir'],
        'url' => $asset['url'],
        'asset_version' => $asset['version'],
    ];
    $check( false !== file_put_contents( $state_file, wp_json_encode( $state, JSON_PRETTY_PRINT ) ), 'cannot write external QA evidence' );
    echo "PASS D040-L6 seed: CSS-only ZIP active, recorded immutable release and native sentinel\n";
    exit( 0 );
}

$check( is_file( $state_file ), 'QA state missing' );
$state = json_decode( (string) file_get_contents( $state_file ), true );
$check( is_array( $state ), 'invalid QA state' );
$check( get_post( $state['post_id'] ) instanceof WP_Post, 'WordPress-native Post disappeared' );
$check( $state['post_content'] === get_post( $state['post_id'] )->post_content, 'WordPress-native content mutated' );
$check( 'native-owner-preserved' === get_post_meta( $state['post_id'], '_d040_l6_continuity', true ), 'WordPress-native metadata mutated' );
$records = (array) get_option( $package_option, [] );
$check( ( $records['curtain-01']['active'] ?? null ) === $state['active_record'], 'active package pointer changed' );
$check( is_file( $state['dir'] . '/' . $css_path ), 'uploads CSS lost' );
$check( hash_file( 'sha256', $state['dir'] . '/' . $css_path ) === $state['css_sha'], 'uploads CSS SHA drifted' );
$check( is_file( dirname( $state_file ) . '/local-presentation-fixture.zip' ), 'original ZIP fixture lost' );
$check( hash_file( 'sha256', dirname( $state_file ) . '/local-presentation-fixture.zip' ) === $state['zip_sha'], 'fixture ZIP SHA drifted' );

if ( 'away' === $phase ) {
    $check( 'aznet-theme' !== get_stylesheet(), 'switch-away did not occur' );
    $check( ! function_exists( '\AZnet\Theme\local_template_package_asset' ), 'AZnet Theme PHP unexpectedly loaded in non-AZnet Theme' );
    echo "PASS D040-L6 away: other Theme active, native data/options/uploads CSS intact\n";
    exit( 0 );
}

$check( 'aznet-theme' === get_stylesheet(), 'AZnet Theme not reactivated' );
$check( 'theme-mod-retained' === get_theme_mod( 'd040_l6_theme_presentation', '' ), 'AZnet Theme setting lost' );
$active = \AZnet\Theme\local_template_package_read_active( 'curtain-01' );
$asset = \AZnet\Theme\local_template_package_asset( 'curtain-01', '/' . $css_path );
$check( is_array( $active ) && is_array( $asset ), 'installed CSS not resolved' );
$check( $active['record'] === $state['active_record'], 'active release identity changed' );
$check( $asset['url'] === $state['url'] && $asset['version'] === $state['asset_version'], 'CSS URL or content version changed' );
$check( '1.0.1' === ( $active['record']['version'] ?? null ), 'local ZIP version changed' );

if ( 'restored' === $phase ) {
    $check( '1.3.110' === AZNET_THEME_VERSION, 'Core version drifted before fixture update' );
    echo "PASS D040-L6 restored: ZIP CSS still active with exact URL/hash after Theme switch roundtrip\n";
    exit( 0 );
}

$check( '1.3.111' === AZNET_THEME_VERSION, 'simulated higher Core version not loaded' );
$check( '1.3.111' === wp_get_theme()->get( 'Version' ), 'simulated package metadata version not loaded' );
$check( \AZnet\Theme\local_template_package_rollback( 'curtain-01' ), 'rollback after simulated Core update failed' );
$check( null === \AZnet\Theme\local_template_package_asset( 'curtain-01', '/' . $css_path ), 'rollback did not restore bundled CSS' );
$check( get_post( $state['post_id'] )->post_content === $state['post_content'], 'rollback mutated WordPress content' );
echo "PASS D040-L6 upgrade: version-metadata fixture reinstalled, same ZIP hash/pointer/CSS; rollback preserved native Post\n";

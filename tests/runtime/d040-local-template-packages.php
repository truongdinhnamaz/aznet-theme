<?php
/** D-040 local-only package install/update/rollback on disposable WordPress. */
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'ZipArchive' ) ) { exit(1); }

$assert = static function ( bool $ok, string $msg ): void {
    if ( ! $ok ) { fwrite( STDERR, "FAIL D040 L3: {$msg}\n" ); exit(1); }
};

$operator = get_user_by( 'login', 'admin' );
$assert( $operator instanceof WP_User, 'operator missing' );
wp_set_current_user( $operator->ID );
$assert( ! \AZnet\Theme\Admin\danger_zone_is_authorized(), 'Administrator must not be auto-authorized' );
$operator->add_cap( 'aznet_theme_manage_danger_zone', true );
$assert( \AZnet\Theme\Admin\danger_zone_is_authorized(), 'individually authorized operator needed' );

$fixture_post = wp_insert_post( [
    'post_title' => 'Template installer preservation fixture',
    'post_content' => '<p>WordPress is source of truth.</p>',
    'post_status' => 'publish',
], true );
$assert( ! is_wp_error( $fixture_post ), 'WordPress fixture post' );
$original = get_post( $fixture_post );
$original_theme_version = AZNET_THEME_VERSION;
$source = get_theme_file_path( '/assets/css/components/homepage-curtain-01.css' );
$css = file_get_contents( $source );
$assert( is_string( $css ) && '' !== $css, 'original CSS source missing' );
$manifest = require get_theme_file_path( '/inc/theme/templates/curtain-01/manifest.php' );
$manifest['version'] = '1.0.1';
$manifest['assets']['homepage'] = [ $manifest['assets']['homepage'][0] ];
$path = 'assets/css/components/homepage-curtain-01.css';
$package = [
    'package_contract_version' => 1,
    'requires' => [ 'theme' => '1.3.110', 'wordpress' => '6.9', 'php' => '8.1' ],
    'files' => [ $path => hash( 'sha256', $css ) ],
];

$make_zip = static function ( array $extra = [] ) use ( $manifest, $package, $css, $path ): string {
    $name = tempnam( sys_get_temp_dir(), 'tpl3-' );
    $zip = new ZipArchive();
    if ( true !== $zip->open( $name, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
        throw new RuntimeException( 'Failed to create ZIP fixture' );
    }
    $zip->addFromString( 'manifest.json', wp_json_encode( $manifest ) );
    $zip->addFromString( 'package.json', wp_json_encode( $package ) );
    $zip->addFromString( $path, $css );
    foreach ( $extra as $file => $body ) { $zip->addFromString( $file, $body ); }
    $zip->close();
    return $name;
};

$zip_file = $make_zip();
$result = \AZnet\Theme\local_template_package_install( $zip_file, hash_file( 'sha256', $zip_file ) );
$assert( true === ( $result['ok'] ?? false ), 'valid CSS package install: ' . wp_json_encode( $result ) );
$active = \AZnet\Theme\local_template_package_read_active( 'curtain-01' );
$assert( is_array( $active ) && '1.0.1' === ( $active['record']['version'] ?? null ), 'active version immutable metadata' );
$asset = \AZnet\Theme\local_template_package_asset( 'curtain-01', '/' . $path );
$assert( is_array( $asset ) && str_contains( $asset['url'], '/aznet-theme-templates/releases/curtain-01/1.0.1-' ), 'WordPress upload URL served for CSS-only override' );
$assert( '1.3.110' === AZNET_THEME_VERSION && $original_theme_version === AZNET_THEME_VERSION, 'Theme Core version unchanged' );
$assert( get_post( $fixture_post )->post_content === $original->post_content, 'WordPress content remains unchanged' );

$rejected = $make_zip( [ 'inc/theme/arbitrary.php' => '<?php exit;' ] );
$fail = \AZnet\Theme\local_template_package_install( $rejected, hash_file( 'sha256', $rejected ) );
$assert( false === ( $fail['ok'] ?? false ), 'PHP injection must be rejected' );
$assert( is_array( \AZnet\Theme\local_template_package_asset( 'curtain-01', '/' . $path ) ), 'rejected package cannot deactivate active CSS' );

$assert( \AZnet\Theme\local_template_package_rollback( 'curtain-01' ), 'rollback succeeds' );
$assert( null === \AZnet\Theme\local_template_package_asset( 'curtain-01', '/' . $path ), 'bundled stylesheet is restored' );
$assert( get_post( $fixture_post )->post_content === $original->post_content, 'rollback preserves WordPress content' );

foreach ( [ $zip_file, $rejected ] as $file ) { unlink( $file ); }
echo "PASS D040 L3: WordPress 6.9 CSS package install, integrity, rollback, operator, domain preservation\n";

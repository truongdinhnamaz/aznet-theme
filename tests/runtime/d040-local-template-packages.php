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
// WP_User's request-local current-user object is separate from the object
// returned by get_user_by(). Reload it after updating the fixture capability.
wp_set_current_user( 0 );
wp_set_current_user( $operator->ID );
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
$pinned_css = getenv( 'D040_CURTAIN01_CSS' );
$with_pr593 = is_string( $pinned_css ) && '' !== $pinned_css;
$assert( ! $with_pr593 || is_file( $pinned_css ), 'pinned PR593 stylesheet missing' );
$css = file_get_contents( $with_pr593 ? $pinned_css : $source );
$assert( is_string( $css ) && '' !== $css, 'Curtain 01 CSS source missing' );
if ( $with_pr593 ) {
    $assert( hash( 'sha256', $css ) !== hash_file( 'sha256', $source ), 'PR593 must differ from bundled Core CSS' );
    $assert( str_contains( $css, 'height: clamp(17rem, 28vw, 24rem);' ), 'PR593 bounded Project Showcase media missing' );
}
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
    // Fixed DOS epoch makes the three-file candidate ZIP byte-reproducible.
    foreach ( [ 'manifest.json', 'package.json', $path ] as $entry ) {
        if ( ! $zip->setMtimeName( $entry, 315532800 ) ) {
            throw new RuntimeException( 'Failed to fix ZIP entry timestamp' );
        }
    }
    foreach ( $extra as $file => $body ) { $zip->addFromString( $file, $body ); }
    $zip->close();
    return $name;
};

$zip_file = $make_zip();
$zip_replica = $make_zip();
$assert( hash_file( 'sha256', $zip_file ) === hash_file( 'sha256', $zip_replica ), 'candidate ZIP exact-byte rebuild' );
$result = \AZnet\Theme\local_template_package_install( $zip_file, hash_file( 'sha256', $zip_file ) );
$assert( true === ( $result['ok'] ?? false ), 'valid CSS package install: ' . wp_json_encode( $result ) );
$active = \AZnet\Theme\local_template_package_read_active( 'curtain-01' );
$assert( is_array( $active ) && '1.0.1' === ( $active['record']['version'] ?? null ), 'active version immutable metadata' );
$asset = \AZnet\Theme\local_template_package_asset( 'curtain-01', '/' . $path );
$assert( is_array( $asset ) && str_contains( $asset['url'], '/aznet-theme-templates/releases/curtain-01/1.0.1-' ), 'WordPress upload URL served for CSS-only override' );
$assert( hash_file( 'sha256', $active['dir'] . '/' . $path ) === hash( 'sha256', $css ), 'installed asset must retain exact PR593 CSS bytes' );
if ( $with_pr593 ) {
    $evidence_dir = getenv( 'D040_CURTAIN01_ARTIFACT_DIR' );
    $assert( is_string( $evidence_dir ) && is_dir( $evidence_dir ), 'candidate evidence directory missing' );
    $candidate = $evidence_dir . '/curtain-01-v1.0.1-PR593-CANDIDATE.zip';
    $assert( copy( $zip_file, $candidate ), 'candidate ZIP artifact copy' );
    $zip_sha = (string) hash_file( 'sha256', $candidate );
    $css_sha = hash( 'sha256', $css );
    $checksums = $zip_sha . '  ' . basename( $candidate ) . "\n"
        . $css_sha . '  homepage-curtain-01.css (PR593 pinned)' . "\n";
    $assert( false !== file_put_contents( $evidence_dir . '/SHA256SUMS.txt', $checksums ), 'candidate checksums' );
    echo "PASS D040 Rèm 01 v1.0.1 PR593 CSS candidate: SHA256 ZIP={$zip_sha} CSS={$css_sha}; Core unchanged\n";
}
$assert( '1.3.110' === AZNET_THEME_VERSION && $original_theme_version === AZNET_THEME_VERSION, 'Theme Core version unchanged' );
$assert( get_post( $fixture_post )->post_content === $original->post_content, 'WordPress content remains unchanged' );

$rejected = $make_zip( [ 'inc/theme/arbitrary.php' => '<?php exit;' ] );
$fail = \AZnet\Theme\local_template_package_install( $rejected, hash_file( 'sha256', $rejected ) );
$assert( false === ( $fail['ok'] ?? false ), 'PHP injection must be rejected' );
$assert( is_array( \AZnet\Theme\local_template_package_asset( 'curtain-01', '/' . $path ) ), 'rejected package cannot deactivate active CSS' );

$assert( \AZnet\Theme\local_template_package_rollback( 'curtain-01' ), 'rollback succeeds' );
$assert( null === \AZnet\Theme\local_template_package_asset( 'curtain-01', '/' . $path ), 'bundled stylesheet is restored' );
$assert( get_post( $fixture_post )->post_content === $original->post_content, 'rollback preserves WordPress content' );

// An independently uploaded v2-native CSS-only template must enter the
// existing Template Library without adding any PHP renderer or Core branches.
$native_id = 'sample-native-01';
$native_path = 'assets/css/sample-native-01.css';
$native_css = '.aznet-theme-homepage--sample-native-01{max-width:72rem}';
$native_manifest = [
    'contract_version' => 2, 'id' => $native_id, 'name' => 'Sample Native 01',
    'version' => '1.0.0', 'category' => 'Sample', 'description' => 'Presentation only',
    'capabilities' => [ 'homepage' ],
    'presentation' => [ 'homepage_preset' => $native_id ],
    'assets' => [ 'homepage' => [ [ 'type' => 'style',
        'handle' => 'aznet-theme-homepage-sample-native-01', 'path' => '/' . $native_path,
        'dependencies' => [ 'aznet-theme-tokens' ] ] ] ],
    'homepage' => [ 'composition' => [ 'mode' => 'native' ], 'authoring' => [ 'sections' => [ 'content' ] ] ],
    'provisioning' => [],
];
$native_package = [
    'package_contract_version' => 1,
    'requires' => [ 'theme' => '1.3.110', 'wordpress' => '6.9', 'php' => '8.1' ],
    'files' => [ $native_path => hash( 'sha256', $native_css ) ],
];
$native_zip = tempnam( sys_get_temp_dir(), 'tpl3-native-' );
$writer = new ZipArchive();
$assert( true === $writer->open( $native_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE ), 'new native ZIP creation' );
$writer->addFromString( 'manifest.json', wp_json_encode( $native_manifest ) );
$writer->addFromString( 'package.json', wp_json_encode( $native_package ) );
$writer->addFromString( $native_path, $native_css );
$writer->close();
$new_result = \AZnet\Theme\local_template_package_install( $native_zip, hash_file( 'sha256', $native_zip ) );
$assert( true === ( $new_result['ok'] ?? false ), 'new native-only ZIP install: ' . wp_json_encode( $new_result ) );
$registered = \AZnet\Theme\load_installed_template_manifests();
$assert( in_array( $native_id, $registered, true ), 'new template should register from installed JSON manifest' );
$assert( in_array( $native_id, \AZnet\Theme\homepage_preset_ids(), true ), 'new template should be selectable as Homepage preset' );
$library = \AZnet\Theme\Admin\homepage_template_library_items();
$assert( in_array( $native_id, array_column( $library, 'template_id' ), true ), 'new native template missing from Template Library' );
$native_asset = \AZnet\Theme\local_template_package_asset( $native_id, '/' . $native_path );
$assert( is_array( $native_asset ) && str_contains( $native_asset['url'], '/aznet-theme-templates/' ), 'new native CSS asset missing' );
$assert( \AZnet\Theme\local_template_package_rollback( $native_id ), 'new template local rollback' );
$assert( get_post( $fixture_post )->post_content === $original->post_content, 'new native template must never mutate WordPress content' );

foreach ( [ $zip_file, $zip_replica, $rejected, $native_zip ] as $file ) { unlink( $file ); }
echo "PASS D040 L3: WordPress 6.9 CSS-only install, registry/library, integrity, rollback, operator and domain preservation\n";

<?php
/**
 * D-040 local-only declarative Template ZIP consumer (TPL3 candidate).
 *
 * Accepts data/CSS only. No PHP execution, remote entitlement, network
 * requests, content mutation, or Theme file replacement happens here.
 *
 * @package AZnetTheme
 */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

const LOCAL_TEMPLATE_PACKAGES_OPTION = 'aznet_theme_local_template_packages';
const LOCAL_TEMPLATE_PACKAGES_DIR = 'aznet-theme-templates';

/** @return array<string,mixed> */
function local_template_package_records(): array {
    if ( ! function_exists( 'get_option' ) ) { return []; }
    $records = get_option( LOCAL_TEMPLATE_PACKAGES_OPTION, [] );
    return is_array( $records ) ? $records : [];
}

/** @return array{root:string,url:string}|null */
function local_template_package_location(): ?array {
    if ( ! function_exists( 'wp_upload_dir' ) ) { return null; }
    $upload = wp_upload_dir( null, false );
    if ( ! is_array( $upload ) || ! empty( $upload['error'] )
        || empty( $upload['basedir'] ) || empty( $upload['baseurl'] ) ) { return null; }
    return [
        'root' => rtrim( (string) $upload['basedir'], '/\\' ) . '/' . LOCAL_TEMPLATE_PACKAGES_DIR,
        'url'  => rtrim( (string) $upload['baseurl'], '/' ) . '/' . LOCAL_TEMPLATE_PACKAGES_DIR,
    ];
}

/** Limit the first local package format to versioned, CSS-only presentation assets. */
function local_template_css_path( string $path ): bool {
    return 1 === preg_match( '~^assets/css/(?:[a-z0-9-]+/)*[a-z0-9-]+\.css$~D', $path );
}

/**
 * Validate a D-043 manifest against one conservative, locally uploaded
 * D-040 envelope. Existing bundled templates can replace CSS assets only;
 * new templates must use the supported v2 WordPress-native composition.
 *
 * @return true|string True or stable failure code.
 */
function local_template_package_validate( array $manifest, array $package, bool $check_version = true ) {
    if ( array_keys( $package ) !== [ 'package_contract_version', 'requires', 'files' ]
        || 1 !== $package['package_contract_version'] ) { return 'package_schema'; }

    $requires = $package['requires'];
    if ( ! is_array( $requires ) || array_keys( $requires ) !== [ 'theme', 'wordpress', 'php' ] ) { return 'compatibility_schema'; }
    foreach ( $requires as $version ) {
        if ( ! is_string( $version ) || 1 !== preg_match( '/^[0-9]+\.[0-9]+(?:\.[0-9]+)?$/D', $version ) ) { return 'compatibility_version'; }
    }
    if ( ! defined( 'AZNET_THEME_VERSION' )
        || version_compare( AZNET_THEME_VERSION, $requires['theme'], '<' )
        || version_compare( PHP_VERSION, $requires['php'], '<' )
        || ( function_exists( 'get_bloginfo' ) && version_compare( (string) get_bloginfo( 'version' ), $requires['wordpress'], '<' ) ) ) {
        return 'incompatible_runtime';
    }

    $normal = normalize_template_manifest( $manifest );
    if ( ! is_array( $normal ) ) { return 'manifest_schema'; }
    $id = $normal['id'];
    $version = $normal['version'];
    if ( ! is_string( $version ) || 1 !== preg_match( '/^[0-9]+\.[0-9]+\.[0-9]+$/D', $version ) ) { return 'template_version'; }

    $files = $package['files'];
    if ( ! is_array( $files ) || count( $files ) < 1 || count( $files ) > 8 ) { return 'files_schema'; }
    foreach ( $files as $path => $sha ) {
        if ( ! is_string( $path ) || ! local_template_css_path( $path )
            || ! is_string( $sha ) || 1 !== preg_match( '/^[a-f0-9]{64}$/D', $sha ) ) { return 'unsafe_file'; }
    }

    if ( array_keys( $normal['assets'] ) !== [ 'homepage' ] ) { return 'asset_scope'; }
    $assets = $normal['assets']['homepage'] ?? null;
    if ( ! is_array( $assets ) || count( $assets ) !== count( $files ) ) { return 'asset_count'; }
    $declared_paths = [];
    foreach ( $assets as $asset ) {
        if ( ! is_array( $asset ) || 'style' !== ( $asset['type'] ?? null )
            || ! isset( $asset['handle'], $asset['path'] ) || ! is_string( $asset['handle'] )
            || 1 !== preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $asset['handle'] )
            || ! is_string( $asset['path'] ) || ! str_starts_with( $asset['path'], '/' ) ) { return 'asset_schema'; }
        foreach ( array_keys( $asset ) as $key ) {
            if ( ! in_array( $key, [ 'type', 'handle', 'path', 'dependencies' ], true ) ) { return 'asset_extra_key'; }
        }
        $deps = $asset['dependencies'] ?? [];
        if ( ! is_array( $deps ) || array_diff( $deps, [ 'aznet-theme-tokens', 'aznet-theme-homepage' ] ) ) { return 'asset_dependency'; }
        $path = ltrim( $asset['path'], '/' );
        if ( ! array_key_exists( $path, $files ) || in_array( $path, $declared_paths, true ) ) { return 'asset_mismatch'; }
        $declared_paths[] = $path;
    }

    $bundled = template_manifest( $id );
    if ( is_array( $bundled ) ) {
        foreach ( [ 'contract_version', 'id', 'capabilities', 'presentation', 'homepage', 'provisioning' ] as $key ) {
            if ( ( $normal[ $key ] ?? null ) != ( $bundled[ $key ] ?? null ) ) { return 'bundled_contract_change'; }
        }
        $base_assets = $bundled['assets']['homepage'] ?? [];
        foreach ( $assets as $asset ) {
            if ( ! in_array( $asset, $base_assets, false ) ) { return 'bundled_asset_change'; }
        }
    } else {
        // No executable renderer can arrive in a remote/local ZIP.
        if ( array_keys( $normal['homepage'] ) !== [ 'composition', 'authoring' ]
            || array_keys( $normal['homepage']['authoring'] ?? [] ) !== [ 'sections' ]
            || 2 !== $normal['contract_version']
            || 'native' !== ( $normal['homepage']['composition']['mode'] ?? null )
            || $id !== ( $normal['presentation']['homepage_preset'] ?? null )
            || isset( $normal['presentation']['visual_preset'] )
            || [ 'homepage' ] !== $normal['capabilities']
            || [ 'content' ] !== ( $normal['homepage']['authoring']['sections'] ?? null )
            || [] !== ( $normal['provisioning'] ?? [] ) ) { return 'unsupported_new_template'; }
    }

    if ( $check_version ) {
        $records = local_template_package_records();
        $current = $records[ $id ]['active']['version'] ?? $bundled['version'] ?? null;
        if ( is_string( $current ) && version_compare( $version, $current, '<=' ) ) { return 'not_newer'; }
    }
    return true;
}

/** @return array{manifest:array<string,mixed>,package:array<string,mixed>,record:array<string,mixed>,dir:string,url:string}|null */
function local_template_package_read_active( string $id ): ?array {
    if ( 1 !== preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id ) ) { return null; }
    $records = local_template_package_records();
    $record = $records[ $id ]['active'] ?? null;
    $where = local_template_package_location();
    if ( ! is_array( $record ) || null === $where ) { return null; }
    $sha = $record['zip_sha256'] ?? '';
    $version = $record['version'] ?? '';
    if ( ! is_string( $sha ) || 1 !== preg_match( '/^[a-f0-9]{64}$/D', $sha )
        || ! is_string( $version ) || 1 !== preg_match( '/^[0-9]+\.[0-9]+\.[0-9]+$/D', $version ) ) { return null; }
    $relative = 'releases/' . $id . '/' . $version . '-' . substr( $sha, 0, 12 );
    $dir = $where['root'] . '/' . $relative;
    $manifest_file = $dir . '/manifest.json';
    $package_file = $dir . '/package.json';
    if ( ! is_file( $manifest_file ) || ! is_file( $package_file )
        || ! isset( $record['manifest_sha256'] ) || ! is_string( $record['manifest_sha256'] )
        || ! hash_equals( $record['manifest_sha256'], (string) hash_file( 'sha256', $manifest_file ) ) ) { return null; }
    $manifest = json_decode( (string) file_get_contents( $manifest_file ), true );
    $package = json_decode( (string) file_get_contents( $package_file ), true );
    if ( ! is_array( $manifest ) || ! is_array( $package ) || $manifest['id'] !== $id
        || $manifest['version'] !== $version || true !== local_template_package_validate( $manifest, $package, false ) ) { return null; }
    if ( ( $record['files'] ?? null ) !== $package['files'] ) { return null; }
    return [ 'manifest' => $manifest, 'package' => $package, 'record' => $record,
        'dir' => $dir, 'url' => $where['url'] . '/' . $relative ];
}

/** Load only additive native v2 manifests. Bundled PHP capabilities stay bundled. */
function load_installed_template_manifests(): array {
    $added = [];
    foreach ( local_template_package_records() as $id => $entry ) {
        if ( ! is_string( $id ) || null !== template_manifest( $id ) ) { continue; }
        $installed = local_template_package_read_active( $id );
        if ( is_array( $installed ) && register_template_manifest( $installed['manifest'] ) ) { $added[] = $id; }
    }
    return $added;
}

/** @return array{url:string,version:string}|null */
function local_template_package_asset( string $id, string $path ): ?array {
    if ( ! str_starts_with( $path, '/' ) ) { return null; }
    $file = ltrim( $path, '/' );
    if ( ! local_template_css_path( $file ) ) { return null; }
    $installed = local_template_package_read_active( $id );
    if ( null === $installed || ! isset( $installed['package']['files'][ $file ] ) ) { return null; }
    $actual = $installed['dir'] . '/' . $file;
    $root = realpath( $installed['dir'] );
    $resolved = realpath( $actual );
    if ( false === $root || false === $resolved || ! str_starts_with( $resolved, $root . DIRECTORY_SEPARATOR )
        || ! is_file( $resolved ) ) { return null; }
    $expected = $installed['package']['files'][ $file ];
    if ( ! hash_equals( $expected, (string) hash_file( 'sha256', $resolved ) ) ) { return null; }
    return [ 'url' => $installed['url'] . '/' . $file, 'version' => substr( $expected, 0, 16 ) ];
}

/** @return array{ok:bool,code:string,id?:string,version?:string,sha256?:string} */
function local_template_package_install( string $zip_path, string $expected_zip_sha256 ): array {
    $fail = static function ( string $code ): array { return [ 'ok' => false, 'code' => $code ]; };
    if ( ! class_exists( '\\ZipArchive' ) ) { return $fail( 'zip_extension_unavailable' ); }
    if ( ! is_file( $zip_path ) || filesize( $zip_path ) > 3 * 1024 * 1024
        || 1 !== preg_match( '/^[a-f0-9]{64}$/D', $expected_zip_sha256 )
        || ! hash_equals( $expected_zip_sha256, (string) hash_file( 'sha256', $zip_path ) ) ) { return $fail( 'zip_integrity' ); }

    $zip = new \ZipArchive();
    if ( true !== $zip->open( $zip_path ) ) { return $fail( 'invalid_zip' ); }
    $contents = [];
    $total = 0;
    $code = '';
    if ( $zip->numFiles < 3 || $zip->numFiles > 24 ) { $code = 'zip_entry_count'; }
    for ( $i = 0; '' === $code && $i < $zip->numFiles; $i++ ) {
        $stat = $zip->statIndex( $i );
        $name = is_array( $stat ) ? (string) ( $stat['name'] ?? '' ) : '';
        $size = (int) ( $stat['size'] ?? -1 );
        $compressed = (int) ( $stat['comp_size'] ?? -1 );
        if ( '' === $name || str_contains( $name, '..' ) || str_contains( $name, '\\' )
            || str_contains( $name, ':' ) || str_starts_with( $name, '/' ) || isset( $contents[ $name ] ) ) { $code = 'unsafe_archive_path'; break; }
        $os = 0; $attrs = 0;
        if ( $zip->getExternalAttributesIndex( $i, $os, $attrs )
            && 0120000 === ( ( $attrs >> 16 ) & 0170000 ) ) { $code = 'archive_symlink'; break; }
        if ( str_ends_with( $name, '/' ) ) {
            if ( 1 !== preg_match( '~^assets/css/(?:[a-z0-9-]+/)*$~D', $name )
                && ! in_array( $name, [ 'assets/', 'assets/css/' ], true ) ) { $code = 'unsafe_archive_directory'; }
            continue;
        }
        if ( ! in_array( $name, [ 'manifest.json', 'package.json' ], true ) && ! local_template_css_path( $name ) ) { $code = 'unsafe_archive_file'; break; }
        $total += $size;
        if ( $size < 1 || $size > 1024 * 1024 || $total > 3 * 1024 * 1024
            || $compressed < 0 || ( $size > 4096 && $compressed * 200 < $size ) ) { $code = 'zip_limits'; break; }
        $body = $zip->getFromIndex( $i );
        if ( ! is_string( $body ) || strlen( $body ) !== $size ) { $code = 'zip_read'; break; }
        $contents[ $name ] = $body;
    }
    $zip->close();
    if ( '' !== $code ) { return $fail( $code ); }
    if ( ! isset( $contents['manifest.json'], $contents['package.json'] ) ) { return $fail( 'package_files_missing' ); }
    $manifest = json_decode( $contents['manifest.json'], true );
    $package = json_decode( $contents['package.json'], true );
    if ( ! is_array( $manifest ) || ! is_array( $package ) ) { return $fail( 'package_json' ); }
    $valid = local_template_package_validate( $manifest, $package );
    if ( true !== $valid ) { return $fail( (string) $valid ); }
    $files = $package['files'];
    if ( count( $contents ) !== count( $files ) + 2 ) { return $fail( 'undeclared_content' ); }
    foreach ( $files as $path => $sha ) {
        if ( ! isset( $contents[ $path ] ) || ! hash_equals( $sha, hash( 'sha256', $contents[ $path ] ) ) ) { return $fail( 'asset_integrity' ); }
        // No third-party CSS imports, active resources, or executable server code.
        if ( preg_match( '/@import\b|url\s*\(|expression\s*\(/i', $contents[ $path ] ) ) { return $fail( 'external_css_resource' ); }
    }

    $where = local_template_package_location();
    if ( null === $where || ( ! is_dir( $where['root'] ) && ! wp_mkdir_p( $where['root'] ) ) ) { return $fail( 'storage_unavailable' ); }
    $id = $manifest['id'];
    $version = $manifest['version'];
    $relative = 'releases/' . $id . '/' . $version . '-' . substr( $expected_zip_sha256, 0, 12 );
    $target = $where['root'] . '/' . $relative;
    $stage = $where['root'] . '/stage-' . bin2hex( random_bytes( 10 ) );
    if ( is_dir( $target ) || ! mkdir( $stage, 0755 ) ) { return $fail( 'target_exists_or_stage_failed' ); }
    $staged = true;
    foreach ( $contents as $name => $body ) {
        $dst = $stage . '/' . $name;
        if ( ! is_dir( dirname( $dst ) ) && ! wp_mkdir_p( dirname( $dst ) ) ) { $staged = false; break; }
        if ( strlen( $body ) !== file_put_contents( $dst, $body, LOCK_EX ) ) { $staged = false; break; }
    }
    if ( ! $staged || ( ! is_dir( dirname( $target ) ) && ! wp_mkdir_p( dirname( $target ) ) ) ) {
        local_template_package_cleanup_stage( $stage );
        return $fail( 'stage_write_failed' );
    }
    if ( ! rename( $stage, $target ) ) {
        local_template_package_cleanup_stage( $stage );
        return $fail( 'atomic_rename_failed' );
    }
    $records = local_template_package_records();
    $prior = $records[ $id ]['active'] ?? null;
    $records[ $id ] = [
        'active' => [
            'version' => $version, 'zip_sha256' => $expected_zip_sha256,
            'manifest_sha256' => hash( 'sha256', $contents['manifest.json'] ), 'files' => $files,
        ],
        'previous' => is_array( $prior ) ? $prior : null,
    ];
    if ( ! update_option( LOCAL_TEMPLATE_PACKAGES_OPTION, $records, false ) ) {
        // Do not change the former active pointer if persistence failed.
        return $fail( 'activation_option_failed' );
    }
    return [ 'ok' => true, 'code' => 'installed', 'id' => $id, 'version' => $version, 'sha256' => $expected_zip_sha256 ];
}

/** Remove only an unactivated staging tree created during this request. */
function local_template_package_cleanup_stage( string $stage ): void {
    if ( ! is_dir( $stage ) || ! str_contains( basename( $stage ), 'stage-' ) ) { return; }
    $iter = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator( $stage, \FilesystemIterator::SKIP_DOTS ),
        \RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ( $iter as $file ) {
        if ( $file->isDir() ) { rmdir( $file->getPathname() ); }
        else { unlink( $file->getPathname() ); }
    }
    rmdir( $stage );
}

/** Switch back to the previous version, or the bundled Core template, atomically. */
function local_template_package_rollback( string $id ): bool {
    $records = local_template_package_records();
    if ( ! isset( $records[ $id ]['active'] ) || null === local_template_package_read_active( $id ) ) { return false; }
    $former = $records[ $id ]['active'];
    $previous = $records[ $id ]['previous'] ?? null;
    if ( ! is_array( $previous ) ) {
        unset( $records[ $id ] );
        return update_option( LOCAL_TEMPLATE_PACKAGES_OPTION, $records, false );
    }
    $records[ $id ]['active'] = $previous;
    $records[ $id ]['previous'] = $former;
    if ( null === local_template_package_read_record( $id, $previous ) ) { return false; }
    return update_option( LOCAL_TEMPLATE_PACKAGES_OPTION, $records, false );
}

/** Validate a retained release without mutating the active option. */
function local_template_package_read_record( string $id, array $record ): ?array {
    // The helper validates a retained release without changing WordPress options.
    $where = local_template_package_location();
    $sha = $record['zip_sha256'] ?? '';
    $version = $record['version'] ?? '';
    if ( null === $where || ! is_string( $sha ) || ! preg_match( '/^[a-f0-9]{64}$/D', $sha )
        || ! is_string( $version ) || ! preg_match( '/^[0-9]+\.[0-9]+\.[0-9]+$/D', $version ) ) { return null; }
    $dir = $where['root'] . '/releases/' . $id . '/' . $version . '-' . substr( $sha, 0, 12 );
    if ( ! is_file( $dir . '/manifest.json' ) || ! is_file( $dir . '/package.json' ) ) { return null; }
    $m = json_decode( (string) file_get_contents( $dir . '/manifest.json' ), true );
    $p = json_decode( (string) file_get_contents( $dir . '/package.json' ), true );
    if ( ! is_array( $m ) || ! is_array( $p ) || $m['id'] !== $id || $m['version'] !== $version
        || ! isset( $record['manifest_sha256'] )
        || ! hash_equals( (string) $record['manifest_sha256'], (string) hash_file( 'sha256', $dir . '/manifest.json' ) )
        || ( $record['files'] ?? null ) !== $p['files']
        || true !== local_template_package_validate( $m, $p, false ) ) { return null; }
    foreach ( $p['files'] as $file => $hash ) {
        $full = realpath( $dir . '/' . $file );
        $canonical = realpath( $dir );
        if ( false === $full || false === $canonical || ! str_starts_with( $full, $canonical . DIRECTORY_SEPARATOR )
            || ! hash_equals( $hash, (string) hash_file( 'sha256', $full ) ) ) { return null; }
    }
    return [ 'manifest' => $m, 'package' => $p ];
}

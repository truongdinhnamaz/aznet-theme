<?php
/**
 * Deterministic loader for local AZnet Theme template manifests.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

/**
 * Load local Theme template manifests from each local template directory.
 *
 * @return array{loaded:array<int,string>,rejected:array<int,string>}
 */
function load_local_template_manifests(): array {
    $loaded = [];
    $rejected = [];
    $paths = glob( __DIR__ . '/templates/*/manifest.php' );

    if ( false === $paths ) {
        return [ 'loaded' => [], 'rejected' => [] ];
    }

    sort( $paths, SORT_STRING );

    foreach ( $paths as $path ) {
        $manifest = require $path;
        $fallback_id = basename( dirname( $path ) );

        if ( ! is_array( $manifest ) ) {
            $rejected[] = $fallback_id;
            continue;
        }

        $id = trim( (string) ( $manifest['id'] ?? $fallback_id ) );
        if ( register_template_manifest( $manifest ) ) {
            $loaded[] = $id;
        } else {
            $rejected[] = '' !== $id ? $id : $fallback_id;
        }
    }

    return [
        'loaded' => array_values( $loaded ),
        'rejected' => array_values( $rejected ),
    ];
}

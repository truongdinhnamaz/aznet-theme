<?php
/**
 * Deterministic loader for local Theme template manifests.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

/**
 * Load bundled Theme template manifests in deterministic path order.
 *
 * @return array{loaded:array<int,string>,rejected:array<int,string>}
 */
function load_local_template_manifests(): array {
    $paths = glob( __DIR__ . '/templates/*/manifest.php' );
    if ( false === $paths ) {
        $paths = [];
    }

    sort( $paths, SORT_STRING );

    $loaded = [];
    $rejected = [];

    foreach ( $paths as $path ) {
        $manifest = require $path;
        $id = is_array( $manifest ) ? trim( (string) ( $manifest['id'] ?? '' ) ) : '';

        if ( ! is_array( $manifest ) || ! register_template_manifest( $manifest ) ) {
            $rejected[] = '' !== $id ? $id : $path;
            continue;
        }

        $loaded[] = $id;
    }

    return [
        'loaded'   => $loaded,
        'rejected' => $rejected,
    ];
}

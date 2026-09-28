<?php
/**
 * Versioned Theme template manifest registry.
 *
 * The registry is Theme-owned presentation state only. It does not own or copy
 * WordPress/plugin/provider domain data.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'AZNET_THEME_TEMPLATE_MANIFEST_VERSION' ) ) {
    define( 'AZNET_THEME_TEMPLATE_MANIFEST_VERSION', 1 );
}

/**
 * Normalize one template manifest against the current bounded contract.
 *
 * @param array<string,mixed> $manifest Raw manifest.
 * @return array<string,mixed>|null
 */
function normalize_template_manifest( array $manifest ): ?array {
    $allowed_keys = [
        'contract_version',
        'id',
        'name',
        'version',
        'category',
        'description',
        'capabilities',
        'presentation',
        'assets',
        'homepage',
        'provisioning',
    ];

    foreach ( array_keys( $manifest ) as $key ) {
        if ( ! is_string( $key ) || ! in_array( $key, $allowed_keys, true ) ) {
            return null;
        }
    }

    if ( AZNET_THEME_TEMPLATE_MANIFEST_VERSION !== (int) ( $manifest['contract_version'] ?? 0 ) ) {
        return null;
    }

    $id = trim( (string) ( $manifest['id'] ?? '' ) );
    if ( '' === $id || 1 !== preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $id ) ) {
        return null;
    }

    $name = trim( (string) ( $manifest['name'] ?? '' ) );
    $version = trim( (string) ( $manifest['version'] ?? '' ) );
    if ( '' === $name || '' === $version ) {
        return null;
    }

    $category = trim( (string) ( $manifest['category'] ?? '' ) );
    $description = trim( (string) ( $manifest['description'] ?? '' ) );

    $capabilities = [];
    foreach ( (array) ( $manifest['capabilities'] ?? [] ) as $capability ) {
        $capability = trim( (string) $capability );
        if ( '' === $capability || 1 !== preg_match( '/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', $capability ) ) {
            return null;
        }
        $capabilities[] = $capability;
    }
    $capabilities = array_values( array_unique( $capabilities ) );

    foreach ( [ 'presentation', 'assets', 'homepage', 'provisioning' ] as $section ) {
        if ( isset( $manifest[ $section ] ) && ! is_array( $manifest[ $section ] ) ) {
            return null;
        }
    }

    return [
        'contract_version' => AZNET_THEME_TEMPLATE_MANIFEST_VERSION,
        'id'               => $id,
        'name'             => $name,
        'version'          => $version,
        'category'         => $category,
        'description'      => $description,
        'capabilities'     => $capabilities,
        'presentation'     => (array) ( $manifest['presentation'] ?? [] ),
        'assets'           => (array) ( $manifest['assets'] ?? [] ),
        'homepage'         => (array) ( $manifest['homepage'] ?? [] ),
        'provisioning'     => (array) ( $manifest['provisioning'] ?? [] ),
    ];
}

/**
 * Internal request-local manifest store.
 *
 * @return array<string,array<string,mixed>>
 */
function &template_manifest_store(): array {
    static $manifests = [];
    return $manifests;
}

/**
 * Register one validated template manifest without overwriting an existing id.
 *
 * @param array<string,mixed> $manifest Raw manifest.
 */
function register_template_manifest( array $manifest ): bool {
    $normalized = normalize_template_manifest( $manifest );
    if ( null === $normalized ) {
        return false;
    }

    $id = (string) $normalized['id'];
    $store =& template_manifest_store();
    if ( array_key_exists( $id, $store ) ) {
        return false;
    }

    $store[ $id ] = $normalized;
    return true;
}

/** @return array<string,array<string,mixed>> */
function template_manifests(): array {
    return template_manifest_store();
}

/** @return array<int,string> */
function template_manifest_ids(): array {
    return array_keys( template_manifest_store() );
}

/** @return array<string,mixed>|null */
function template_manifest( string $id ): ?array {
    $id = trim( $id );
    if ( '' === $id ) {
        return null;
    }

    $store = template_manifest_store();
    return $store[ $id ] ?? null;
}


/** @return array<int,string> */
function template_presentation_preset_ids( string $key ): array {
    if ( ! in_array( $key, [ 'visual_preset', 'homepage_preset' ], true ) ) {
        return [];
    }

    $values = [];
    foreach ( template_manifests() as $manifest ) {
        $value = trim( (string) ( $manifest['presentation'][ $key ] ?? '' ) );
        if ( '' !== $value && 1 === preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value ) ) {
            $values[] = $value;
        }
    }

    return array_values( array_unique( $values ) );
}

/**
 * Return Core-native visual presets plus visual presets contributed by registered templates.
 *
 * @return array<int,string>
 */
function template_supported_visual_presets(): array {
    return array_values(
        array_unique(
            array_merge(
                [ 'default', 'editorial', 'commerce' ],
                template_presentation_preset_ids( 'visual_preset' )
            )
        )
    );
}

/**
 * Return the native/off Homepage path plus Homepage presets contributed by registered templates.
 *
 * @return array<int,string>
 */
function template_supported_homepage_presets(): array {
    return array_values(
        array_unique(
            array_merge(
                [ 'off' ],
                template_presentation_preset_ids( 'homepage_preset' )
            )
        )
    );
}

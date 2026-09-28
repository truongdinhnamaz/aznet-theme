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


/**
 * Return visual preset ids available to Theme settings.
 *
 * Core-native visual presets remain available independently of templates.
 *
 * @return array<int,string>
 */
function template_visual_preset_ids(): array {
    $ids = [ 'default', 'editorial', 'commerce' ];
    foreach ( template_manifests() as $manifest ) {
        $preset = trim( (string) ( $manifest['presentation']['visual_preset'] ?? '' ) );
        if ( '' !== $preset ) {
            $ids[] = $preset;
        }
    }
    return array_values( array_unique( $ids ) );
}

/** @return array<int,string> */
function template_homepage_preset_ids(): array {
    $ids = [ 'off' ];
    foreach ( template_manifests() as $manifest ) {
        $preset = trim( (string) ( $manifest['presentation']['homepage_preset'] ?? '' ) );
        if ( '' !== $preset ) {
            $ids[] = $preset;
        }
    }
    return array_values( array_unique( $ids ) );
}

/**
 * Register Theme-shipped template manifests.
 *
 * This is presentation metadata only. It does not provision or mutate content.
 */
function register_builtin_template_manifests(): void {
    $builtins = [
        [
            'contract_version' => AZNET_THEME_TEMPLATE_MANIFEST_VERSION,
            'id' => 'law-01',
            'name' => 'Law 01',
            'version' => '1.0.0',
            'category' => 'professional',
            'description' => 'Mẫu trình bày cho website dịch vụ chuyên nghiệp/pháp lý.',
            'capabilities' => [ 'homepage' ],
            'presentation' => [
                'visual_preset' => 'default',
                'homepage_preset' => 'law-01',
            ],
            'assets' => [],
            'homepage' => [],
            'provisioning' => [],
        ],
        [
            'contract_version' => AZNET_THEME_TEMPLATE_MANIFEST_VERSION,
            'id' => 'curtain-01',
            'name' => 'Rèm 01',
            'version' => '1.0.0',
            'category' => 'commerce',
            'description' => 'Mẫu trình bày cho website rèm/nội thất có WooCommerce.',
            'capabilities' => [ 'homepage', 'woocommerce' ],
            'presentation' => [
                'visual_preset' => 'curtain-01',
                'homepage_preset' => 'curtain-01',
            ],
            'assets' => [],
            'homepage' => [],
            'provisioning' => [],
        ],
    ];

    foreach ( $builtins as $manifest ) {
        if ( null === template_manifest( (string) $manifest['id'] ) ) {
            register_template_manifest( $manifest );
        }
    }
}

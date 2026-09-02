<?php
/**
 * RootProfile public presentation-provider consumer.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Integrations\RootProfile;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const PROVIDER_HOOK = 'rootprofile/presentation/provider/v1';
const PROVIDER_CONTRACT = 'rootprofile.presentation';
const PROVIDER_VERSION = 1;

/**
 * Whether the RootProfile v1 public presentation provider is registered.
 */
function provider_available(): bool {
    if ( ! function_exists( 'has_filter' ) ) {
        return false;
    }

    return false !== has_filter( PROVIDER_HOOK );
}

/**
 * Read a RootProfile presentation payload without touching RootProfile storage.
 *
 * @return array<string,mixed>|null
 */
function payload( string $resource, int $entity_id = 0 ): ?array {
    if ( ! function_exists( 'apply_filters' ) ) {
        return null;
    }

    try {
        $candidate = apply_filters( PROVIDER_HOOK, null, $resource, $entity_id );
    } catch ( \Throwable ) {
        return null;
    }
    if ( ! is_array( $candidate ) ) {
        return null;
    }

    if ( ( $candidate['contract'] ?? null ) !== PROVIDER_CONTRACT ) {
        return null;
    }

    if ( (int) ( $candidate['version'] ?? 0 ) !== PROVIDER_VERSION ) {
        return null;
    }

    if ( ( $candidate['resource'] ?? null ) !== $resource ) {
        return null;
    }

    return $candidate;
}

/** @return array<string,mixed>|null */
function organization(): ?array {
    return payload( 'organization' );
}

/** @return array<string,mixed>|null */
function person( int $entity_id ): ?array {
    if ( $entity_id < 1 ) {
        return null;
    }

    return payload( 'person', $entity_id );
}

/** @return array<string,mixed>|null */
function contact(): ?array {
    return payload( 'contact' );
}

const PROFILE_PROVIDER_HOOK = 'rootprofile/presentation/provider/v2';
const PROFILE_PROVIDER_VERSION = 2;

/**
 * Whether the RootProfile v2 Profile Surface provider is registered.
 */
function profile_provider_available(): bool {
    if ( ! function_exists( 'has_filter' ) ) {
        return false;
    }

    return false !== has_filter( PROFILE_PROVIDER_HOOK );
}

/**
 * Read a RootProfile Profile Surface v2 payload without touching RootProfile storage.
 *
 * @return array<string,mixed>|null
 */
function profile_payload( string $resource, int $entity_id = 0 ): ?array {
    if ( ! function_exists( 'apply_filters' ) ) {
        return null;
    }

    try {
        $candidate = apply_filters( PROFILE_PROVIDER_HOOK, null, $resource, $entity_id );
    } catch ( \Throwable ) {
        return null;
    }

    if ( ! is_array( $candidate ) ) {
        return null;
    }

    if ( ( $candidate['contract'] ?? null ) !== PROVIDER_CONTRACT ) {
        return null;
    }

    if ( (int) ( $candidate['version'] ?? 0 ) !== PROFILE_PROVIDER_VERSION ) {
        return null;
    }

    if ( ( $candidate['resource'] ?? null ) !== $resource ) {
        return null;
    }

    return $candidate;
}

/** @return array<string,mixed>|null */
function person_profile( int $entity_id ): ?array {
    if ( $entity_id < 1 ) {
        return null;
    }

    return profile_payload( 'person_profile', $entity_id );
}

/** @return array<string,mixed>|null */
function organization_profile(): ?array {
    return profile_payload( 'organization_profile' );
}

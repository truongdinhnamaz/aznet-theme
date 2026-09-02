<?php
/**
 * Theme-owned Profile Surface presentation model.
 *
 * RootProfile remains authoritative for identity, Profile URL, resolved section
 * semantics/order/navigation, Claims, Evidence, Relationships, Responsibility,
 * Readiness and every public-safe datum exposed by Provider v2. This module only
 * composes that read model for AZnet Theme presentation.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Build a presentation-only Profile Surface model from RootProfile Provider v2.
 *
 * E3 deliberately does not reconstruct the Person section set or the Business
 * Profile 17 Core Sections. The provider-resolved list is preserved in order and
 * is the only source for section presence, labels, anchors and navigation state.
 *
 * @return array<string,mixed>|null
 */
function profile_surface_model( string $resource, int $entity_id = 0 ): ?array {
    if ( ! in_array( $resource, [ 'person_profile', 'organization_profile' ], true ) ) {
        return null;
    }

    if ( ! \AZnet\Theme\Integrations\RootProfile\profile_provider_available() ) {
        return null;
    }

    if ( 'person_profile' === $resource ) {
        if ( $entity_id < 1 ) {
            return null;
        }
        $payload = \AZnet\Theme\Integrations\RootProfile\person_profile( $entity_id );
    } else {
        $payload = \AZnet\Theme\Integrations\RootProfile\organization_profile();
    }

    if ( ! is_array( $payload ) ) {
        return null;
    }

    $entity = is_array( $payload['entity'] ?? null ) ? $payload['entity'] : [];
    $uuid = trim( (string) ( $entity['uuid'] ?? '' ) );
    $display_name = trim( (string) ( $entity['display_name'] ?? '' ) );
    $profile_url = trim( (string) ( $entity['profile_url'] ?? '' ) );

    if ( '' === $uuid || '' === $display_name || '' === $profile_url ) {
        return null;
    }

    $sections = [];
    $navigation = [];

    foreach ( (array) ( $payload['sections'] ?? [] ) as $section ) {
        if ( ! is_array( $section ) ) {
            continue;
        }

        $key = trim( (string) ( $section['key'] ?? '' ) );
        $label = trim( (string) ( $section['label'] ?? '' ) );
        $anchor = trim( (string) ( $section['anchor'] ?? '' ) );
        if ( '' === $key || '' === $label || '' === $anchor ) {
            continue;
        }

        $resolved = [
            'key' => $key,
            'label' => $label,
            'anchor' => $anchor,
            'show_in_navigation' => (bool) ( $section['show_in_navigation'] ?? false ),
            'data' => is_array( $section['data'] ?? null ) ? $section['data'] : [],
        ];

        foreach ( [ 'section_type', 'origin' ] as $field ) {
            $value = trim( (string) ( $section[ $field ] ?? '' ) );
            if ( '' !== $value ) {
                $resolved[ $field ] = $value;
            }
        }

        $sections[] = $resolved;

        if ( $resolved['show_in_navigation'] ) {
            $navigation[] = [
                'label' => $label,
                'anchor' => $anchor,
            ];
        }
    }

    $model = [
        'resource' => $resource,
        'entity' => $entity,
        'sections' => $sections,
        'navigation' => $navigation,
        'signals' => is_array( $payload['signals'] ?? null ) ? $payload['signals'] : [],
        'updated_at' => trim( (string) ( $payload['updated_at'] ?? '' ) ),
    ];

    if ( 'person_profile' === $resource ) {
        $model['organization_context'] = [
            'organization' => is_array( $payload['organization'] ?? null ) ? $payload['organization'] : [],
            'role_context' => is_array( $payload['role_context'] ?? null ) ? $payload['role_context'] : [],
        ];
    }

    return $model;
}

/**
 * Whether a presentation model is structurally safe to hand to the template.
 *
 * This does not create or repair domain truth. It only rejects malformed input
 * before presentation so fail-soft paths cannot turn into warnings or partial
 * profile output.
 *
 * @param array<string,mixed> $model Presentation model candidate.
 */
function profile_surface_model_is_renderable( array $model ): bool {
    $resource = (string) ( $model['resource'] ?? '' );
    if ( ! in_array( $resource, [ 'person_profile', 'organization_profile' ], true ) ) {
        return false;
    }

    $entity = is_array( $model['entity'] ?? null ) ? $model['entity'] : [];
    foreach ( [ 'uuid', 'display_name', 'profile_url' ] as $field ) {
        if ( '' === trim( (string) ( $entity[ $field ] ?? '' ) ) ) {
            return false;
        }
    }

    if ( ! isset( $model['sections'] ) || ! is_array( $model['sections'] ) ) {
        return false;
    }

    if ( isset( $model['navigation'] ) && ! is_array( $model['navigation'] ) ) {
        return false;
    }

    return true;
}

/** @return array<string,mixed>|null */
function person_profile_surface_model( int $entity_id ): ?array {
    return profile_surface_model( 'person_profile', $entity_id );
}

/** @return array<string,mixed>|null */
function organization_profile_surface_model(): ?array {
    return profile_surface_model( 'organization_profile' );
}

/**
 * Render a Profile Surface from an explicit model or provider-derived resource.
 *
 * This helper is intentionally not wired to WordPress routing in E3.
 *
 * @param array<string,mixed>|null $model Optional explicit presentation model.
 */
function render_profile_surface( ?array $model = null, string $resource = 'organization_profile', int $entity_id = 0 ): void {
    if ( null === $model ) {
        $model = profile_surface_model( $resource, $entity_id );
    }

    if ( ! is_array( $model ) || [] === $model || ! profile_surface_model_is_renderable( $model ) ) {
        return;
    }

    get_template_part(
        'template-parts/profile/surface',
        null,
        [ 'model' => $model ]
    );
}

/**
 * Render a Person Profile Surface without claiming its route.
 *
 * @param array<string,mixed>|null $model Optional explicit presentation model.
 */
function render_person_profile_surface( int $entity_id, ?array $model = null ): void {
    if ( null === $model ) {
        $model = person_profile_surface_model( $entity_id );
    }
    render_profile_surface( $model, 'person_profile', $entity_id );
}

/**
 * Render an Organization Profile Surface without claiming its route.
 *
 * @param array<string,mixed>|null $model Optional explicit presentation model.
 */
function render_organization_profile_surface( ?array $model = null ): void {
    if ( null === $model ) {
        $model = organization_profile_surface_model();
    }
    render_profile_surface( $model, 'organization_profile' );
}

/**
 * Enqueue Profile Surface CSS only when a destination explicitly opts in.
 */
function enqueue_profile_surface_assets(): void {
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;

    wp_enqueue_style(
        'aznet-theme-profile-surface',
        get_theme_file_uri( '/assets/css/components/profile-surface.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}

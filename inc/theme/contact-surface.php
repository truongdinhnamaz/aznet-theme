<?php
/**
 * Theme-owned Contact Surface presentation model.
 *
 * RootProfile remains authoritative for Organization identity and contact facts.
 * This module only composes the normalized public Provider v1 payload for Theme rendering.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Build a presentation-only Contact Surface model from RootProfile Provider v1.
 *
 * The contact payload is required. The organization payload is optional presentation
 * enrichment and may only be combined when both resources resolve the same Entity UUID.
 *
 * @return array<string,mixed>|null
 */
function contact_surface_model(): ?array {
    if ( ! \AZnet\Theme\Integrations\RootProfile\provider_available() ) {
        return null;
    }

    $contact_payload = \AZnet\Theme\Integrations\RootProfile\contact();
    if ( ! is_array( $contact_payload ) ) {
        return null;
    }

    $contact_entity = is_array( $contact_payload['entity'] ?? null )
        ? $contact_payload['entity']
        : [];
    $uuid = trim( (string) ( $contact_entity['uuid'] ?? '' ) );
    $display_name = trim( (string) ( $contact_entity['display_name'] ?? '' ) );

    if ( '' === $uuid || '' === $display_name ) {
        return null;
    }

    $organization_payload = \AZnet\Theme\Integrations\RootProfile\organization();
    $organization_entity = is_array( $organization_payload['entity'] ?? null )
        ? $organization_payload['entity']
        : [];
    $organization_uuid = trim( (string) ( $organization_entity['uuid'] ?? '' ) );

    if ( '' !== $organization_uuid && $organization_uuid !== $uuid ) {
        return null;
    }

    $contact = is_array( $contact_payload['contact'] ?? null )
        ? $contact_payload['contact']
        : [];
    $address = is_array( $contact['address'] ?? null ) ? $contact['address'] : [];
    $points = is_array( $contact['points'] ?? null ) ? array_values( $contact['points'] ) : [];
    $opening_hours = is_array( $contact['opening_hours'] ?? null )
        ? array_values( $contact['opening_hours'] )
        : [];
    $social_links = is_array( $contact_payload['social_links'] ?? null )
        ? array_values( $contact_payload['social_links'] )
        : [];
    $policies = is_array( $contact_payload['policies'] ?? null )
        ? array_values( $contact_payload['policies'] )
        : [];
    $responsible_people = is_array( $contact_payload['responsible_people'] ?? null )
        ? array_values( $contact_payload['responsible_people'] )
        : [];

    $has_details = '' !== trim( (string) ( $contact['website'] ?? '' ) )
        || [] !== $address
        || '' !== trim( (string) ( $contact['service_area'] ?? '' ) )
        || [] !== $points
        || [] !== $opening_hours
        || [] !== $social_links
        || [] !== $policies
        || [] !== $responsible_people;

    if ( ! $has_details ) {
        return null;
    }

    return [
        'entity' => [
            'uuid' => $uuid,
            'display_name' => $display_name,
            'legal_name' => trim( (string) ( $contact_entity['legal_name'] ?? '' ) ),
            'summary' => trim( (string) ( $organization_entity['summary'] ?? '' ) ),
            'logo_url' => trim( (string) ( $contact_entity['logo_url'] ?? $organization_entity['logo_url'] ?? '' ) ),
            'profile_url' => trim( (string) ( $contact_entity['profile_url'] ?? $organization_entity['profile_url'] ?? '' ) ),
        ],
        'surface' => [
            'url' => trim( (string) ( $contact_payload['surface']['url'] ?? '' ) ),
        ],
        'contact' => [
            'website' => trim( (string) ( $contact['website'] ?? '' ) ),
            'address' => $address,
            'service_area' => trim( (string) ( $contact['service_area'] ?? '' ) ),
            'points' => $points,
            'opening_hours' => $opening_hours,
        ],
        'social_links' => $social_links,
        'policies' => $policies,
        'responsible_people' => $responsible_people,
        'signals' => is_array( $contact_payload['signals'] ?? null ) ? $contact_payload['signals'] : [],
    ];
}

/**
 * Render the Contact Surface template for an explicit or provider-derived model.
 *
 * This helper is intentionally not wired to page.php or any route in E2.
 *
 * @param array<string,mixed>|null $model Optional explicit presentation model.
 */
function render_contact_surface( ?array $model = null ): void {
    if ( null === $model ) {
        $model = contact_surface_model();
    }

    if ( ! is_array( $model ) || [] === $model ) {
        return;
    }

    get_template_part(
        'template-parts/contact/surface',
        null,
        [ 'model' => $model ]
    );
}

/**
 * Enqueue Contact Surface presentation CSS when a destination template explicitly opts in.
 *
 * E2 does not call this from the global enqueue path, so generic Page requests remain unchanged.
 */
function enqueue_contact_surface_assets(): void {
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;

    wp_enqueue_style(
        'aznet-theme-contact-surface',
        get_theme_file_uri( '/assets/css/components/contact-surface.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}

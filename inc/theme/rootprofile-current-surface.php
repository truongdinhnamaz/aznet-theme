<?php
/**
 * Dormant RootProfile current-surface presentation dispatcher.
 *
 * This module does not claim WordPress routes. A future separately gated E5-D
 * slice may invoke it from an approved takeover boundary after LIVE UAT.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dispatch a validated RootProfile current-surface context to existing Theme renderers.
 *
 * This function is deliberately dormant: it is not registered on a WordPress
 * request lifecycle hook in E5-B.
 *
 * @param array<string,mixed> $context Validated current-surface context.
 */
function render_current_rootprofile_surface( array $context ): bool {
    $surface = (string) ( $context['surface'] ?? '' );
    $presentation = $context['presentation'] ?? null;
    if ( ! is_array( $presentation ) ) {
        return false;
    }

    if ( in_array( $surface, [ 'person_profile', 'organization_profile' ], true ) ) {
        $model = profile_surface_model_from_payload( $surface, $presentation );
        if ( ! is_array( $model ) || ! profile_surface_model_is_renderable( $model ) ) {
            return false;
        }

        enqueue_profile_surface_assets();
        render_profile_surface( $model, $surface );
        return true;
    }

    if ( 'contact' === $surface ) {
        $organization_payload = \AZnet\Theme\Integrations\RootProfile\organization();
        $model = contact_surface_model_from_payload(
            $presentation,
            is_array( $organization_payload ) ? $organization_payload : null
        );
        if ( ! is_array( $model ) ) {
            return false;
        }

        enqueue_contact_surface_assets();
        render_contact_surface( $model );
        return true;
    }

    return false;
}

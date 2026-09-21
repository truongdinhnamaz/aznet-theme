<?php
/**
 * Law 01 native category archive presentation context.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Whether the approved Law 01 legal-site category archive presentation is active.
 *
 * WordPress keeps category routing, the authoritative main query and pagination.
 * The Theme only switches presentation for native category archives.
 */
function law01_category_archive_active(): bool {
    if ( ! function_exists( 'is_category' ) || ! is_category() ) {
        return false;
    }

    return function_exists( __NAMESPACE__ . '\\header_law01_active' )
        && header_law01_active();
}

/**
 * Return the explicitly mapped public Contact Page URL for the archive consultation CTA.
 */
function archive_contact_page_url(): string {
    $contact_id = (int) setting( 'homepage_contact_page', 0 );
    if ( $contact_id <= 0 ) {
        return '';
    }

    $contact = get_post( $contact_id );
    if ( ! $contact instanceof \WP_Post || 'page' !== $contact->post_type || 'publish' !== $contact->post_status ) {
        return '';
    }

    $url = get_permalink( $contact_id );

    return is_string( $url ) ? $url : '';
}

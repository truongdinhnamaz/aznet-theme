<?php
/**
 * Theme-owned Professional Page Kit registry consumers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the fixed role-to-pattern mapping for reusable professional Page Kits.
 *
 * @return array<string,string>
 */
function professional_page_kit_roles(): array {
    return [
        'about'           => 'aznet-theme/page-about',
        'services'        => 'aznet-theme/page-services',
        'team'            => 'aznet-theme/page-team-expertise',
        'contact'         => 'aznet-theme/page-contact',
        'content_landing' => 'aznet-theme/page-content-landing',
    ];
}

/**
 * Return registered WordPress pattern content for one Page Kit role.
 */
function professional_page_kit_content( string $role ): string {
    $map = professional_page_kit_roles();

    if ( ! isset( $map[ $role ] ) || ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
        return '';
    }

    $pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( $map[ $role ] );

    return is_array( $pattern ) && isset( $pattern['content'] ) ? (string) $pattern['content'] : '';
}

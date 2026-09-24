<?php
/**
 * Native WordPress Page presentation context.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Determine whether Theme breadcrumbs are enabled for native Pages. */
function page_breadcrumbs_enabled(): bool {
    return true === setting( 'page_breadcrumbs', true );
}

/** Return the bounded Page presentation variant from native Page Template state. */
function page_variant( ?int $post_id = null ): string {
    $post_id = $post_id ?: (int) get_queried_object_id();
    $template = $post_id > 0 ? (string) get_page_template_slug( $post_id ) : '';

    return match ( $template ) {
        'page-templates/wide.php'    => 'wide',
        'page-templates/landing.php' => 'landing',
        default                      => 'standard',
    };
}

/**
 * Return the native Page excerpt without pre-consuming plugin-owned dynamic content.
 *
 * WooCommerce surfaces can render their authoritative content through the WordPress
 * content filter. Calling get_the_excerpt() first would execute that filtered content
 * once while building an automatic excerpt and leave the later content render empty.
 * The public current-surface adapter is therefore used only as a presentation guard;
 * generic WordPress Pages retain their existing excerpt behavior unchanged.
 */
function page_excerpt( ?int $post_id = null ): string {
    $surface_function = __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface';
    if (
        function_exists( $surface_function )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface()
    ) {
        return '';
    }

    $excerpt = null === $post_id ? get_the_excerpt() : get_the_excerpt( $post_id );

    return trim( (string) $excerpt );
}

/**
 * Return breadcrumb ancestors derived only from native WordPress Page hierarchy.
 *
 * @return array<int, array{title:string,url:string}>
 */
function page_breadcrumb_items( ?int $post_id = null ): array {
    if ( ! page_breadcrumbs_enabled() ) {
        return [];
    }

    $post_id = $post_id ?: (int) get_queried_object_id();
    if ( $post_id <= 0 ) {
        return [];
    }

    $ancestors = array_reverse( array_map( 'intval', get_post_ancestors( $post_id ) ) );
    $items = [];

    foreach ( $ancestors as $ancestor_id ) {
        $ancestor = get_post( $ancestor_id );
        if ( ! $ancestor instanceof \WP_Post || 'page' !== $ancestor->post_type ) {
            continue;
        }

        $url = get_permalink( $ancestor_id );
        $title = trim( (string) get_the_title( $ancestor_id ) );
        if ( ! is_string( $url ) || '' === $url || '' === $title ) {
            continue;
        }

        $items[] = [
            'title' => $title,
            'url'   => $url,
        ];
    }

    return $items;
}



/**
 * Whether the current native Page is the explicitly mapped Contact Page.
 *
 * The Contact Page ID is sourced from the Theme Content Map. This deliberately
 * avoids title, slug and URL heuristics.
 */
function contact_page_is_mapped( ?int $post_id = null ): bool {
    $post_id = $post_id ?: (int) get_queried_object_id();
    $contact_id = (int) homepage_source_value( 'law-01', 'contact' );

    if ( $post_id <= 0 || $contact_id <= 0 || $post_id !== $contact_id ) {
        return false;
    }

    $post = get_post( $post_id );

    return $post instanceof \WP_Post
        && 'page' === $post->post_type
        && 'publish' === $post->post_status;
}

/**
 * Whether the premium Law 01 Contact Page presentation is active.
 */
function contact_page_presentation_active( ?int $post_id = null ): bool {
    if ( ! contact_page_is_mapped( $post_id ) ) {
        return false;
    }

    return function_exists( __NAMESPACE__ . '\\header_law01_active' )
        && header_law01_active();
}

/**
 * Whether the current native Page is the explicitly mapped Services Page.
 *
 * The Services Page ID comes from the Theme Content Map. This avoids slug, title
 * and URL heuristics while keeping WordPress authoritative for Page content.
 */
function services_page_is_mapped( ?int $post_id = null ): bool {
    $post_id = $post_id ?: (int) get_queried_object_id();
    $services_id = (int) homepage_source_value( 'law-01', 'services' );

    if ( $post_id <= 0 || $services_id <= 0 || $post_id !== $services_id ) {
        return false;
    }

    $post = get_post( $post_id );

    return $post instanceof \WP_Post
        && 'page' === $post->post_type
        && 'publish' === $post->post_status;
}

/** Whether the premium Law 01 Services Page presentation is active. */
function services_page_presentation_active( ?int $post_id = null ): bool {
    if ( ! services_page_is_mapped( $post_id ) ) {
        return false;
    }

    return function_exists( __NAMESPACE__ . '\\header_law01_active' )
        && header_law01_active();
}

/**
 * Return published direct service children of the explicitly mapped Services Page.
 *
 * @return array<int, \WP_Post>
 */
function services_page_children( int $limit = 12 ): array {
    $services_id = (int) homepage_source_value( 'law-01', 'services' );
    if ( $services_id <= 0 ) {
        return [];
    }

    $limit = max( 1, min( 24, $limit ) );
    $posts = get_posts(
        [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_parent'    => $services_id,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'posts_per_page' => $limit,
            'no_found_rows'  => true,
        ]
    );

    return is_array( $posts ) ? $posts : [];
}

/**
 * Whether one native Page is an explicitly mapped direct child of the Services Page.
 *
 * The Services Page ID comes from the Theme Content Map. This deliberately avoids
 * title, slug, URL or Page-ID heuristics beyond the explicit mapped parent reference.
 */
function service_page_is_detail( ?int $post_id = null ): bool {
    $post_id = $post_id ?: (int) get_queried_object_id();
    if ( $post_id <= 0 ) {
        return false;
    }

    $services_id = (int) homepage_source_value( 'law-01', 'services' );
    if ( $services_id <= 0 || $post_id === $services_id ) {
        return false;
    }

    $post = get_post( $post_id );
    if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type || 'publish' !== $post->post_status ) {
        return false;
    }

    return $services_id === (int) $post->post_parent;
}

/**
 * Return the explicitly mapped Contact Page URL for service CTA presentation.
 */
function service_page_contact_url(): string {
    $contact_id = (int) homepage_source_value( 'law-01', 'contact' );
    if ( $contact_id <= 0 ) {
        return '';
    }

    $post = get_post( $contact_id );
    if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type || 'publish' !== $post->post_status ) {
        return '';
    }

    $url = get_permalink( $contact_id );

    return is_string( $url ) ? $url : '';
}

/**
 * Return other published service Pages under the same explicitly mapped Services parent.
 *
 * @return array<int, \WP_Post>
 */
function service_page_siblings( ?int $post_id = null, int $limit = 6 ): array {
    $post_id = $post_id ?: (int) get_queried_object_id();
    if ( ! service_page_is_detail( $post_id ) ) {
        return [];
    }

    $services_id = (int) homepage_source_value( 'law-01', 'services' );
    $limit = max( 1, min( 12, $limit ) );
    $posts = get_posts(
        [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_parent'    => $services_id,
            'post__not_in'   => [ $post_id ],
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'posts_per_page' => $limit,
            'no_found_rows'  => true,
        ]
    );

    return is_array( $posts ) ? $posts : [];
}

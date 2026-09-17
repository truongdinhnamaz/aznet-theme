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

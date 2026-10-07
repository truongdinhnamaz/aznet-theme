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
 * Whether a native Page explicitly opts into hero-first composition.
 *
 * The Landing Page template is the bounded WordPress-native presentation signal:
 * it changes composition only and does not infer intent from title, slug, URL or ID.
 */
function page_hero_first_active( ?int $post_id = null ): bool {
    $post_id = $post_id ?: (int) get_queried_object_id();
    if ( $post_id <= 0 || 'landing' !== page_variant( $post_id ) ) {
        return false;
    }

    $post = get_post( $post_id );
    if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
        return false;
    }

    $blocks = parse_blocks( (string) $post->post_content );
    $first = $blocks[0] ?? null;
    if ( ! is_array( $first ) ) {
        return false;
    }

    $class_name = trim( (string) ( $first['attrs']['className'] ?? '' ) );
    if ( 1 !== preg_match( '/(?:^|\\s)aznet-theme-page-hero(?:\\s|$)/', $class_name ) ) {
        return false;
    }

    $queue = [ $first ];
    while ( [] !== $queue ) {
        $block = array_shift( $queue );
        if ( ! is_array( $block ) ) {
            continue;
        }

        if (
            'core/heading' === (string) ( $block['blockName'] ?? '' )
            && 1 === (int) ( $block['attrs']['level'] ?? 2 )
        ) {
            return true;
        }

        foreach ( (array) ( $block['innerBlocks'] ?? [] ) as $inner_block ) {
            if ( is_array( $inner_block ) ) {
                $queue[] = $inner_block;
            }
        }
    }

    return false;
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

    return homepage_renderable_child_pages( $services_id, $limit );
}

/**
 * Whether the current native Page is the explicitly mapped Team directory Page.
 */
function team_page_is_mapped( ?int $post_id = null ): bool {
    $post_id = $post_id ?: (int) get_queried_object_id();
    $team_id = (int) homepage_source_value( 'law-01', 'team' );

    if ( $post_id <= 0 || $team_id <= 0 || $post_id !== $team_id ) {
        return false;
    }

    $post = get_post( $post_id );

    return $post instanceof \WP_Post
        && 'page' === $post->post_type
        && 'publish' === $post->post_status;
}

/** Whether the premium Law 01 Team directory Page presentation is active. */
function team_page_presentation_active( ?int $post_id = null ): bool {
    return team_page_is_mapped( $post_id )
        && function_exists( __NAMESPACE__ . '\\header_law01_active' )
        && header_law01_active();
}

/**
 * Whether one native Page is a published direct child of the explicitly mapped Team Page.
 *
 * The mapped Team parent is the authoritative relation. No slug, title or URL heuristics
 * are used to decide whether a Page is a Team member detail surface.
 */
function team_member_page_is_detail( ?int $post_id = null ): bool {
    $post_id = $post_id ?: (int) get_queried_object_id();
    if ( $post_id <= 0 ) {
        return false;
    }

    $team_id = (int) homepage_source_value( 'law-01', 'team' );
    if ( $team_id <= 0 || $post_id === $team_id ) {
        return false;
    }

    $post = get_post( $post_id );
    if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type || 'publish' !== $post->post_status ) {
        return false;
    }

    return $team_id === (int) $post->post_parent
        && function_exists( __NAMESPACE__ . '\\header_law01_active' )
        && header_law01_active();
}

/**
 * Return a Team member portrait attachment ID suitable for presentation.
 *
 * WordPress remains authoritative for the featured image. When the featured image
 * resolves to the public Site Icon URL, treat it as a site-level fallback rather
 * than a person's portrait and let the Team template render its neutral placeholder.
 */
function team_member_portrait_id( int $post_id ): int {
    if ( $post_id <= 0 || ! function_exists( 'get_post_thumbnail_id' ) ) {
        return 0;
    }

    $thumbnail_id = (int) get_post_thumbnail_id( $post_id );
    if ( $thumbnail_id <= 0 ) {
        return 0;
    }

    if ( function_exists( 'get_site_icon_url' ) && function_exists( 'wp_get_attachment_image_url' ) ) {
        $site_icon_url = (string) get_site_icon_url( 512 );
        $thumbnail_url = wp_get_attachment_image_url( $thumbnail_id, [ 512, 512 ] );

        if ( '' !== $site_icon_url && is_string( $thumbnail_url ) && $site_icon_url === $thumbnail_url ) {
            return 0;
        }
    }

    return $thumbnail_id;
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
    $siblings = array_values(
        array_filter(
            homepage_renderable_child_pages( $services_id, 24 ),
            static fn ( $page ): bool => $page instanceof \WP_Post && (int) $page->ID !== $post_id
        )
    );

    return array_slice( $siblings, 0, $limit );
}

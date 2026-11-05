<?php
/**
 * Theme-owned asset registration.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue the active non-default visual preset stylesheet.
 *
 * @param string|null $version Asset version.
 */
function enqueue_visual_preset_asset( ?string $version = null ): void {
    $preset = function_exists( __NAMESPACE__ . '\\visual_preset' ) ? visual_preset() : 'default';

    if ( 'default' === $preset ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-preset-' . $preset,
        get_theme_file_uri( '/assets/css/presets/' . $preset . '.css' ),
        [ 'aznet-theme-tokens' ],
        asset_content_version( '/assets/css/presets/' . $preset . '.css', $version )
    );
}

/**
 * Enqueue mobile Header navigation enhancement only when its panel renders.
 *
 * @param string|null $version Asset version.
 */
function enqueue_header_navigation_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\header_mobile_panel_enabled' ) || ! header_mobile_panel_enabled() ) {
        return;
    }

    wp_enqueue_script(
        'aznet-theme-header-navigation',
        get_theme_file_uri( '/assets/js/header-navigation.js' ),
        [],
        $version,
        true
    );
}

/**
 * Load the reusable catalog presentation only on opted-in assigned-menu sites.
 *
 * @param string|null $version Asset version.
 */
function enqueue_header_catalog_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\header_catalog_enabled' ) || ! header_catalog_enabled() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-catalog-navigation',
        get_theme_file_uri( '/assets/css/components/catalog-navigation.css' ),
        [ 'aznet-theme-site-header' ],
        asset_content_version( '/assets/css/components/catalog-navigation.css', $version )
    );

    wp_enqueue_script(
        'aznet-theme-catalog-submenu',
        get_theme_file_uri( '/assets/js/catalog-navigation.js' ),
        [],
        asset_content_version( '/assets/js/catalog-navigation.js', $version ),
        true
    );
    wp_localize_script(
        'aznet-theme-catalog-submenu',
        'aznetThemeCatalogNav',
        [
            'expand'   => __( 'Mở danh mục con', 'aznet-theme' ),
            'collapse' => __( 'Thu gọn danh mục con', 'aznet-theme' ),
        ]
    );
}

/**
 * Enqueue Header utility presentation only when the WordPress menu exists.
 *
 * @param string|null $version Asset version.
 */
function enqueue_header_utility_asset( ?string $version = null ): void {
    if ( function_exists( __NAMESPACE__ . '\\setting' ) && true !== setting( 'header_utilities', true ) ) {
        return;
    }
    if ( ! function_exists( 'has_nav_menu' ) || ! has_nav_menu( 'header-utility' ) ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-header-utility',
        get_theme_file_uri( '/assets/css/components/header-utility.css' ),
        [ 'aznet-theme-site-header' ],
        asset_content_version( '/assets/css/components/header-utility.css', $version )
    );
}

/**
 * Enqueue sticky-compact enhancement only for that Header mode.
 *
 * @param string|null $version Asset version.
 */
function enqueue_sticky_header_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\header_sticky_mode' ) || 'sticky-compact' !== header_sticky_mode() ) {
        return;
    }

    wp_enqueue_script(
        'aznet-theme-sticky-header',
        get_theme_file_uri( '/assets/js/sticky-header.js' ),
        [],
        $version,
        true
    );
}

/**
 * Enqueue the site-wide Law 01 Header presentation independently of Front Page assets.
 *
 * @param string|null $version Asset version.
 */
function enqueue_header_law01_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\header_law01_active' ) || ! header_law01_active() ) {
        return;
    }

    if ( 'burgundy-gold' !== homepage_law01_variant() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-header-law-01',
        get_theme_file_uri( '/assets/css/components/header-law-01.css' ),
        [ 'aznet-theme-site-header' ],
        asset_content_version( '/assets/css/components/header-law-01.css', $version )
    );
}

/**
 * Enqueue the dedicated Homepage blueprint presentation only on the Front Page.
 *
 * @param string|null $version Asset version.
 */
function enqueue_homepage_blueprint_asset( ?string $version = null ): void {
    if ( ! function_exists( 'is_front_page' ) || ! is_front_page() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-homepage',
        get_theme_file_uri( '/assets/css/components/homepage.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}

/**
 * Enqueue scoped Homepage blueprint styles in the Block Editor.
 */
function enqueue_homepage_blueprint_editor_asset(): void {
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;

    wp_enqueue_style(
        'aznet-theme-tokens',
        get_theme_file_uri( '/assets/css/tokens.css' ),
        [],
        asset_content_version( '/assets/css/tokens.css', $version )
    );

    wp_enqueue_style(
        'aznet-theme-homepage-editor',
        get_theme_file_uri( '/assets/css/components/homepage.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}

/**
 * Build a cache key that changes when a scoped asset's bytes change.
 *
 * @param string      $relative_path Theme-relative asset path.
 * @param string|null $fallback      Theme/version fallback.
 */
function asset_content_version( string $relative_path, ?string $fallback = null ): ?string {
    if ( ! function_exists( 'get_theme_file_path' ) ) {
        return $fallback;
    }

    $path = get_theme_file_path( $relative_path );
    if ( ! is_file( $path ) ) {
        return $fallback;
    }

    $hash = hash_file( 'sha256', $path );
    if ( ! is_string( $hash ) || '' === $hash ) {
        return $fallback;
    }

    $fingerprint = substr( $hash, 0, 12 );
    return null !== $fallback && '' !== $fallback ? $fallback . '-' . $fingerprint : $fingerprint;
}

/**
 * Return normalized Homepage asset descriptors declared by one template manifest.
 *
 * Invalid descriptors fail soft and are skipped. The result is request-local
 * presentation metadata only; no provider/domain state is read or stored.
 *
 * @return array<int,array{type:string,handle:string,path:string,dependencies:array<int,string>,in_footer:bool}>
 */
function template_homepage_asset_descriptors( string $preset ): array {
    $manifest = template_manifest_for_homepage_preset( $preset );
    $raw = is_array( $manifest ) ? ( $manifest['assets']['homepage'] ?? [] ) : [];
    if ( ! is_array( $raw ) ) {
        return [];
    }

    $assets = [];
    foreach ( $raw as $descriptor ) {
        if ( ! is_array( $descriptor ) ) {
            continue;
        }

        $type = trim( (string) ( $descriptor['type'] ?? '' ) );
        $handle = trim( (string) ( $descriptor['handle'] ?? '' ) );
        $path = trim( (string) ( $descriptor['path'] ?? '' ) );
        if ( ! in_array( $type, [ 'style', 'script' ], true )
            || 1 !== preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $handle )
            || ! str_starts_with( $path, '/assets/' )
            || str_contains( $path, '..' ) ) {
            continue;
        }

        $dependencies = [];
        foreach ( (array) ( $descriptor['dependencies'] ?? [] ) as $dependency ) {
            $dependency = trim( (string) $dependency );
            if ( 1 !== preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $dependency ) ) {
                continue;
            }
            if ( ! in_array( $dependency, $dependencies, true ) ) {
                $dependencies[] = $dependency;
            }
        }

        $assets[] = [
            'type'         => $type,
            'handle'       => $handle,
            'path'         => $path,
            'dependencies' => $dependencies,
            'in_footer'    => true === ( $descriptor['in_footer'] ?? false ),
        ];
    }

    return $assets;
}

/** Enqueue one registered template's Homepage assets through the generic Core path. */
function enqueue_template_homepage_assets( string $preset, ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\homepage_composer_active' ) || ! homepage_composer_active() ) {
        return;
    }
    if ( ! function_exists( __NAMESPACE__ . '\\homepage_preset' ) || $preset !== homepage_preset() ) {
        return;
    }

    foreach ( template_homepage_asset_descriptors( $preset ) as $asset ) {
        $path = (string) $asset['path'];
        if ( function_exists( 'get_theme_file_path' ) && ! is_file( get_theme_file_path( $path ) ) ) {
            continue;
        }

        if ( 'style' === $asset['type'] ) {
            wp_enqueue_style(
                (string) $asset['handle'],
                get_theme_file_uri( $path ),
                (array) $asset['dependencies'],
                asset_content_version( $path, $version )
            );
            continue;
        }

        wp_enqueue_script(
            (string) $asset['handle'],
            get_theme_file_uri( $path ),
            (array) $asset['dependencies'],
            asset_content_version( $path, $version ),
            (bool) $asset['in_footer']
        );
    }
}

/** Enqueue Homepage assets declared by the active registered template manifest. */
function enqueue_active_template_homepage_assets( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\homepage_preset' ) ) {
        return;
    }
    $preset = homepage_preset();
    if ( null === template_manifest_for_homepage_preset( $preset ) ) {
        return;
    }
    enqueue_template_homepage_assets( $preset, $version );
}

/**
 * Explicit WordPress-native Digital 01 Brand Light 2026 Page opt-in.
 * The Theme owns presentation only; WordPress owns the Page content.
 */
function digital01_brand2026_page_active(): bool {
    if ( ! function_exists( 'is_singular' ) || ! is_singular( 'page' )
        || ! function_exists( __NAMESPACE__ . '\\homepage_preset' )
        || 'digital-01' !== homepage_preset()
        || ! function_exists( 'get_queried_object' ) ) {
        return false;
    }

    $page = get_queried_object();
    return $page instanceof \WP_Post
        && str_contains( (string) $page->post_content, 'aznet-digital01--brand-2026' );
}

/** Load optional Brand Light CSS and its existing Digital 01 base only on opted-in Pages. */
function enqueue_digital01_brand2026_asset( ?string $version = null ): void {
    if ( ! digital01_brand2026_page_active() ) {
        return;
    }

    $path = '/assets/css/components/homepage-digital-01-brand-2026.css';
    if ( ! function_exists( 'get_theme_file_path' ) || ! is_file( get_theme_file_path( $path ) ) ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-homepage',
        get_theme_file_uri( '/assets/css/components/homepage.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );

    $base_path = '/assets/css/components/homepage-digital-01.css';
    wp_enqueue_style(
        'aznet-theme-homepage-digital-01',
        get_theme_file_uri( $base_path ),
        [ 'aznet-theme-tokens', 'aznet-theme-homepage' ],
        asset_content_version( $base_path, $version )
    );

    wp_enqueue_style(
        'aznet-theme-homepage-digital-01-brand-2026',
        get_theme_file_uri( $path ),
        [ 'aznet-theme-homepage-digital-01', 'aznet-theme-site-header' ],
        asset_content_version( $path, $version )
    );

    // Keep the inverse Process CTA accessible if a CDN still serves a stale
    // minified CSS asset after a Theme update. WordPress prints this scoped
    // foreground with the current Page, without reading any plugin cache.
    wp_add_inline_style(
        'aznet-theme-homepage-digital-01-brand-2026',
        'body.aznet-theme-preset--digital-01 .aznet-digital01--brand-2026 .aznet-digital01__process .aznet-digital01__text-link > a{color:#ff9d85!important;text-decoration:underline;text-underline-offset:.18em;}'
    );
}

/** Enqueue Law 01 only for its active Front Page presentation surface. */
/** Enqueue the shared Team card presentation for Team surfaces only. */
function enqueue_team_card_asset( ?string $version = null ): void {
    wp_enqueue_script(
        'aznet-theme-team-name-fit',
        get_theme_file_uri( '/assets/js/team-name-fit.js' ),
        [],
        asset_content_version( '/assets/js/team-name-fit.js', $version ),
        true
    );
    wp_enqueue_style(
        'aznet-theme-team-card',
        get_theme_file_uri( '/assets/css/components/team-card.css' ),
        [ 'aznet-theme-tokens' ],
        asset_content_version( '/assets/css/components/team-card.css', $version )
    );
}

function enqueue_homepage_law01_asset( ?string $version = null ): void {
    enqueue_template_homepage_assets( 'law-01', $version );
}

/** Enqueue Industrial 01 only for its active Front Page presentation surface. */
function enqueue_homepage_industrial01_asset( ?string $version = null ): void {
    enqueue_template_homepage_assets( 'industrial-01', $version );
}

/** Enqueue Curtain 01 only for its active Front Page presentation surface. */
function enqueue_homepage_curtain01_asset( ?string $version = null ): void {
    enqueue_template_homepage_assets( 'curtain-01', $version );
}

/** Determine whether the native Post comments surface will render. */
function should_enqueue_comments_assets(): bool {
    if ( ! function_exists( 'is_singular' ) || ! is_singular( 'post' ) ) {
        return false;
    }

    if ( function_exists( 'post_password_required' ) && post_password_required() ) {
        return false;
    }

    if ( ! function_exists( 'comments_open' ) || ! function_exists( 'get_comments_number' ) ) {
        return false;
    }

    return comments_open() || 0 < get_comments_number();
}

/** Enqueue native comments presentation and Core threaded-reply enhancement. */
function enqueue_comments_assets( ?string $version = null ): void {
    if ( ! should_enqueue_comments_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-comments',
        get_theme_file_uri( '/assets/css/components/comments.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content', 'aznet-theme-forms' ],
        $version
    );

    if ( comments_open() && function_exists( 'get_option' ) && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}

/** Determine whether the native Search/404/archive recovery presentation can render. */
function should_enqueue_recovery_assets(): bool {
    if ( ! function_exists( 'is_search' ) || ! function_exists( 'is_404' ) || ! function_exists( 'is_archive' ) ) {
        return false;
    }

    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    return is_search() || is_404() || is_archive();
}

/** Enqueue Search/404/archive recovery presentation only on its native surfaces. */
function enqueue_recovery_assets( ?string $version = null ): void {
    if ( ! should_enqueue_recovery_assets() ) {
        return;
    }

    $dependencies = [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ];
    if ( should_enqueue_form_assets() ) {
        $dependencies[] = 'aznet-theme-forms';
    }

    wp_enqueue_style(
        'aznet-theme-recovery',
        get_theme_file_uri( '/assets/css/components/recovery.css' ),
        $dependencies,
        asset_content_version( '/assets/css/components/recovery.css', $version )
    );
}

/** Determine whether native authored Post/Page media presentation can render. */
function should_enqueue_media_assets(): bool {
    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    $is_post = function_exists( 'is_singular' ) && is_singular( 'post' );
    $is_page = function_exists( 'is_page' ) && is_page();

    return $is_post || $is_page;
}

/** Enqueue authored-media presentation only on native Post/Page surfaces. */
function enqueue_media_assets( ?string $version = null ): void {
    if ( ! should_enqueue_media_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-media',
        get_theme_file_uri( '/assets/css/components/media.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/media.css', $version )
    );
}

/** Determine whether the approved Law 01 category archive presentation can render. */
function should_enqueue_law01_archive_asset(): bool {
    if ( ! function_exists( __NAMESPACE__ . '\\law01_category_archive_active' ) ) {
        return false;
    }

    return law01_category_archive_active();
}

/** Enqueue the Law 01 category archive stylesheet only on its native category surface. */
function enqueue_law01_archive_asset( ?string $version = null ): void {
    if ( ! should_enqueue_law01_archive_asset() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-archive-law-01',
        get_theme_file_uri( '/assets/css/components/archive-law-01.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/archive-law-01.css', $version )
    );
}

/** Determine whether the native Page presentation module should render. */
function should_enqueue_page_assets(): bool {
    if ( ! function_exists( 'is_page' ) || ! is_page() ) {
        return false;
    }

    if ( function_exists( 'is_front_page' ) && is_front_page() ) {
        return false;
    }

    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    return true;
}

/**
 * Explicit Gutenberg authoring opt-in for a native Digital 01 Landing Page.
 * Inspect only public WordPress Page block presentation attrs; never infer
 * page identity from a slug, title, URL or Page ID.
 */
function should_enqueue_about_showcase_asset(): bool {
    if ( ! should_enqueue_page_assets()
        || ! function_exists( __NAMESPACE__ . '\\visual_preset' )
        || 'digital-01' !== visual_preset()
        || ! function_exists( 'get_queried_object' )
        || ! function_exists( 'parse_blocks' ) ) {
        return false;
    }

    $page = get_queried_object();
    if ( ! is_object( $page ) || ! isset( $page->post_content ) || ! is_string( $page->post_content ) ) {
        return false;
    }

    $blocks = parse_blocks( $page->post_content );
    if ( ! is_array( $blocks ) ) {
        return false;
    }

    // A V2 design marker on a later card cannot change the entire Page.
    // Only an authored first-level native Hero Group can opt in.
    $hero = $blocks[0] ?? null;
    if ( ! is_array( $hero ) || 'core/group' !== ( $hero['blockName'] ?? '' ) ) {
        return false;
    }

    $class_name = is_array( $hero['attrs'] ?? null ) ? (string) ( $hero['attrs']['className'] ?? '' ) : '';
    $classes = preg_split( '/\\s+/', trim( $class_name ), -1, PREG_SPLIT_NO_EMPTY );
    $tokens = is_array( $classes ) ? $classes : [];

    return in_array( 'aznet-theme-page-hero', $tokens, true )
        && in_array( 'aznet-theme-about-showcase', $tokens, true );
}

/** Enqueue V2 About storytelling only for an opted-in native Page. */
function enqueue_about_showcase_asset( ?string $version = null ): void {
    if ( ! should_enqueue_about_showcase_asset()
        || ! function_exists( 'get_theme_file_path' )
        || ! is_file( get_theme_file_path( '/assets/css/components/about-showcase-v2.css' ) ) ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-about-showcase',
        get_theme_file_uri( '/assets/css/components/about-showcase-v2.css' ),
        [ 'aznet-theme-page' ],
        asset_content_version( '/assets/css/components/about-showcase-v2.css', $version )
    );
}

/** Enqueue Y1 native Page presentation only on non-front, non-Woo Page surfaces. */
function enqueue_page_assets( ?string $version = null ): void {
    if ( ! should_enqueue_page_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-page',
        get_theme_file_uri( '/assets/css/components/page.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/page.css', $version )
    );

    // Existing explicit presentation choice; no Page identity/content inference.
    if ( function_exists( __NAMESPACE__ . '\\homepage_preset' )
        && 'wellness-01' === homepage_preset()
        && is_file( get_theme_file_path( '/assets/css/components/page-wellness-01.css' ) ) ) {
        wp_enqueue_style(
            'aznet-theme-page-wellness-01',
            get_theme_file_uri( '/assets/css/components/page-wellness-01.css' ),
            [ 'aznet-theme-page', 'aznet-theme-media' ],
            asset_content_version( '/assets/css/components/page-wellness-01.css', $version )
        );
    }
}



/** Determine whether the mapped premium Law 01 Contact Page presentation can render. */
function should_enqueue_contact_page_assets(): bool {
    if ( ! function_exists( __NAMESPACE__ . '\\contact_page_presentation_active' ) ) {
        return false;
    }

    return contact_page_presentation_active();
}

/** Enqueue scoped premium Contact Page presentation only for the explicit mapped Page. */
function enqueue_contact_page_assets( ?string $version = null ): void {
    if ( ! should_enqueue_contact_page_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-contact-surface',
        get_theme_file_uri( '/assets/css/components/contact-surface.css' ),
        [ 'aznet-theme-tokens' ],
        asset_content_version( '/assets/css/components/contact-surface.css', $version )
    );

    wp_enqueue_style(
        'aznet-theme-contact-page',
        get_theme_file_uri( '/assets/css/components/contact-page.css' ),
        [ 'aznet-theme-page', 'aznet-theme-forms', 'aznet-theme-contact-surface' ],
        asset_content_version( '/assets/css/components/contact-page.css', $version )
    );
}

/** Determine whether the mapped premium Law 01 Services Page presentation can render. */
function should_enqueue_services_page_assets(): bool {
    if ( ! function_exists( __NAMESPACE__ . '\\services_page_presentation_active' ) ) {
        return false;
    }

    return services_page_presentation_active();
}

/** Enqueue the shared service-card component only when a Services surface consumes it. */
function enqueue_service_card_asset( ?string $version = null ): void {
    wp_enqueue_style(
        'aznet-theme-service-card',
        get_theme_file_uri( '/assets/css/components/service-card.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-page' ],
        asset_content_version( '/assets/css/components/service-card.css', $version )
    );
}

/** Enqueue scoped premium Services Page presentation only for the explicit mapped Page. */
function enqueue_services_page_assets( ?string $version = null ): void {
    if ( ! should_enqueue_services_page_assets() ) {
        return;
    }

    enqueue_service_card_asset( $version );

    wp_enqueue_style(
        'aznet-theme-services-page',
        get_theme_file_uri( '/assets/css/components/services-page.css' ),
        [ 'aznet-theme-service-card' ],
        asset_content_version( '/assets/css/components/services-page.css', $version )
    );
}

/** Determine whether the mapped Team directory Page presentation can render. */
function should_enqueue_team_page_assets(): bool {
    return function_exists( __NAMESPACE__ . '\\team_page_presentation_active' )
        && team_page_presentation_active();
}

/** Enqueue scoped Team directory Page presentation only for the exact mapped Team Page. */
function enqueue_team_page_assets( ?string $version = null ): void {
    if ( ! should_enqueue_team_page_assets() ) {
        return;
    }

    enqueue_team_card_asset( $version );

    wp_enqueue_style(
        'aznet-theme-team-directory',
        get_theme_file_uri( '/assets/css/components/team-directory.css' ),
        [ 'aznet-theme-page', 'aznet-theme-team-card' ],
        asset_content_version( '/assets/css/components/team-directory.css', $version )
    );
}

/** Determine whether a mapped Team member child Page is rendering. */
function should_enqueue_team_member_assets(): bool {
    return function_exists( __NAMESPACE__ . '\\team_member_page_is_detail' )
        && team_member_page_is_detail();
}

/** Enqueue the scoped Team member detail presentation only for mapped Team child Pages. */
function enqueue_team_member_assets( ?string $version = null ): void {
    if ( ! should_enqueue_team_member_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-team-member',
        get_theme_file_uri( '/assets/css/components/team-member.css' ),
        [ 'aznet-theme-page' ],
        asset_content_version( '/assets/css/components/team-member.css', $version )
    );
}

/** Determine whether the mapped Services child landing presentation can render. */
function should_enqueue_service_page_assets(): bool {
    if ( ! function_exists( __NAMESPACE__ . '\\service_page_is_detail' ) ) {
        return false;
    }

    return service_page_is_detail();
}

/** Enqueue scoped service landing presentation only for mapped direct service children. */
function enqueue_service_page_assets( ?string $version = null ): void {
    if ( ! should_enqueue_service_page_assets() ) {
        return;
    }

    enqueue_service_card_asset( $version );

    wp_enqueue_style(
        'aznet-theme-service-page',
        get_theme_file_uri( '/assets/css/components/service-page.css' ),
        [ 'aznet-theme-service-card' ],
        asset_content_version( '/assets/css/components/service-page.css', $version )
    );
}

/** Determine whether shared native form presentation can render. */
function should_enqueue_form_assets(): bool {
    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    $is_search = function_exists( 'is_search' ) && is_search();
    $is_404    = function_exists( 'is_404' ) && is_404();
    $is_post   = function_exists( 'is_singular' ) && is_singular( 'post' );
    $is_page   = function_exists( 'is_page' ) && is_page();

    return $is_search || $is_404 || $is_post || $is_page;
}

/** Enqueue shared WordPress-native form presentation only on X5 surfaces. */
function enqueue_form_assets( ?string $version = null ): void {
    if ( ! should_enqueue_form_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-forms',
        get_theme_file_uri( '/assets/css/components/forms.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/forms.css', $version )
    );
}

/** Determine whether native pagination/navigation presentation can render. */
function should_enqueue_navigation_assets(): bool {
    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    $is_archive = function_exists( 'is_archive' ) && is_archive();
    $is_search  = function_exists( 'is_search' ) && is_search();
    $is_post    = function_exists( 'is_singular' ) && is_singular( 'post' );

    return $is_archive || $is_search || $is_post;
}

/** Enqueue shared native pagination/navigation presentation only on X4 surfaces. */
function enqueue_navigation_assets( ?string $version = null ): void {
    if ( ! should_enqueue_navigation_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-navigation',
        get_theme_file_uri( '/assets/css/components/navigation.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/navigation.css', $version )
    );
}

function enqueue_assets(): void {
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;

    wp_enqueue_style(
        'aznet-theme-tokens',
        get_theme_file_uri( '/assets/css/tokens.css' ),
        [],
        asset_content_version( '/assets/css/tokens.css', $version )
    );

    enqueue_visual_preset_asset( $version );

    wp_enqueue_style(
        'aznet-theme-convertflow-contract',
        get_theme_file_uri( '/assets/css/integrations/convertflow.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );

    wp_enqueue_style(
        'aznet-theme-style',
        get_stylesheet_uri(),
        [ 'aznet-theme-tokens' ],
        $version
    );

    wp_enqueue_style(
        'aznet-theme-site-header',
        get_theme_file_uri( '/assets/css/components/site-header.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );

    enqueue_header_law01_asset( $version );
    enqueue_header_catalog_asset( $version );
    enqueue_header_utility_asset( $version );
    enqueue_header_navigation_asset( $version );
    enqueue_sticky_header_asset( $version );

    wp_enqueue_style(
        'aznet-theme-site-footer',
        get_theme_file_uri( '/assets/css/components/site-footer.css' ),
        [ 'aznet-theme-tokens' ],
        asset_content_version( '/assets/css/components/site-footer.css', $version )
    );

    // D-064: preset-only Footer CSS has its own asset path so WordPress
    // serves the current presentation without relying on stale shared min CSS.
    // This has no effect on other sites/presets or WordPress-authored menus.
    if ( function_exists( __NAMESPACE__ . '\\visual_preset' ) && 'digital-01' === visual_preset() ) {
        wp_enqueue_style(
            'aznet-theme-site-footer-digital-01',
            get_theme_file_uri( '/assets/css/components/site-footer-digital-01.css' ),
            [ 'aznet-theme-site-footer', 'aznet-theme-preset-digital-01' ],
            asset_content_version( '/assets/css/components/site-footer-digital-01.css', $version )
        );
    }

    enqueue_homepage_blueprint_asset( $version );
    enqueue_active_template_homepage_assets( $version );
    enqueue_digital01_brand2026_asset( $version );

    if ( should_enqueue_generic_content_assets() ) {
        wp_enqueue_style(
            'aznet-theme-generic-content',
            get_theme_file_uri( '/assets/css/components/generic-content.css' ),
            [ 'aznet-theme-tokens' ],
            asset_content_version( '/assets/css/components/generic-content.css', $version )
        );
    }

    enqueue_law01_archive_asset( $version );
    enqueue_page_assets( $version );
    enqueue_about_showcase_asset( $version );
    enqueue_contact_page_assets( $version );
    enqueue_services_page_assets( $version );
    enqueue_team_page_assets( $version );
    enqueue_team_member_assets( $version );
    enqueue_service_page_assets( $version );
    enqueue_form_assets( $version );
    enqueue_navigation_assets( $version );
    enqueue_media_assets( $version );
    enqueue_recovery_assets( $version );

    if ( function_exists( 'is_singular' ) && is_singular( 'post' ) ) {
        wp_enqueue_style(
            'aznet-theme-article',
            get_theme_file_uri( '/assets/css/components/article.css' ),
            [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
            asset_content_version( '/assets/css/components/article.css', $version )
        );
    }

    enqueue_comments_assets( $version );

    if ( should_enqueue_woocommerce_product_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-product',
            get_theme_file_uri( '/assets/css/components/woocommerce-product.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );

        wp_enqueue_script(
            'aznet-theme-convertflow-sticky-submenu',
            get_theme_file_uri( '/assets/js/convertflow-sticky-submenu.js' ),
            [],
            $version,
            true
        );
    }

    if ( should_enqueue_woocommerce_archive_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-archive',
            get_theme_file_uri( '/assets/css/components/woocommerce-archive.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( should_enqueue_woocommerce_cart_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-cart',
            get_theme_file_uri( '/assets/css/components/woocommerce-cart.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( should_enqueue_woocommerce_checkout_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-checkout',
            get_theme_file_uri( '/assets/css/components/woocommerce-checkout.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( should_enqueue_woocommerce_account_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-account',
            get_theme_file_uri( '/assets/css/components/woocommerce-account.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( function_exists( __NAMESPACE__ . '\\should_enqueue_woocommerce_blocks_assets' ) && should_enqueue_woocommerce_blocks_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-blocks',
            get_theme_file_uri( '/assets/css/components/woocommerce-blocks.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }
}


/**
 * Prioritize only the WordPress-owned CSS background used by the active
 * Industrial 01 Hero. This is a browser resource hint, not a media store.
 *
 * @param array<int, array<string, string>> $resources Existing WP preloads.
 * @return array<int, array<string, string>>
 */
function industrial01_hero_preload_resources( array $resources ): array {
    if ( ! homepage_composer_active() || 'industrial-01' !== homepage_preset() ) {
        return $resources;
    }

    // Do not fetch media when the owner turned the Hero surface off.
    $hero_rendered = false;
    foreach ( homepage_effective_surface_map( 'industrial-01' ) as $surface ) {
        if ( 'hero' === ( $surface['template'] ?? '' ) ) {
            $hero_rendered = true;
            break;
        }
    }
    if ( ! $hero_rendered ) {
        return $resources;
    }

    $background_id = (int) setting( 'homepage_industrial01_hero_background', 0 );
    $hero_id       = (int) setting( 'homepage_industrial01_hero_image', 0 );
    $effective_id  = $background_id > 0 ? $background_id : $hero_id;

    if ( $effective_id <= 0 || ! function_exists( 'wp_get_attachment_image_url' ) ) {
        return $resources;
    }

    // Use the exact WordPress Media Library URL rendered by hero.php.
    $background_url = (string) wp_get_attachment_image_url( $effective_id, 'full' );
    if ( '' === $background_url ) {
        return $resources;
    }

    foreach ( $resources as $resource ) {
        if ( is_array( $resource ) && $background_url === ( $resource['href'] ?? '' ) ) {
            return $resources;
        }
    }

    $resources[] = [
        'href'          => $background_url,
        'as'            => 'image',
        'fetchpriority' => 'high',
    ];
    return $resources;
}

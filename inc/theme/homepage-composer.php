<?php
/**
 * Theme-owned Homepage composition orchestration.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the normalized Homepage presentation preset.
 */
function homepage_preset(): string {
    return (string) setting( 'homepage_preset', 'off' );
}

/**
 * Whether Theme-owned Homepage composition is active for this request.
 */
function homepage_composer_active(): bool {
    return function_exists( 'is_front_page' ) && is_front_page() && 'law-01' === homepage_preset();
}

/**
 * Reset request-local displayed Post IDs.
 */
function homepage_ledger_reset(): void {
    $GLOBALS['aznet_theme_homepage_ledger'] = [];
}

/**
 * Add Post IDs to the request-local display ledger.
 *
 * @param array<int, mixed> $post_ids Post IDs.
 */
function homepage_ledger_add( array $post_ids ): void {
    $ledger = homepage_ledger_ids();
    foreach ( $post_ids as $candidate ) {
        $id = is_numeric( $candidate ) ? (int) $candidate : 0;
        if ( $id > 0 && ! in_array( $id, $ledger, true ) ) {
            $ledger[] = $id;
        }
    }
    $GLOBALS['aznet_theme_homepage_ledger'] = $ledger;
}

/**
 * Return current request-local displayed Post IDs.
 *
 * @return array<int, int>
 */
function homepage_ledger_ids(): array {
    $ledger = $GLOBALS['aznet_theme_homepage_ledger'] ?? [];
    return is_array( $ledger ) ? array_values( array_filter( array_map( 'intval', $ledger ) ) ) : [];
}

/**
 * Render a Law 01 template part when it exists.
 *
 * @param string               $slug Section slug.
 * @param array<string, mixed> $args Section arguments.
 */
function render_law01_part( string $slug, array $args = [] ): void {
    $path = get_theme_file_path( 'template-parts/homepage/law-01/' . $slug . '.php' );
    if ( ! is_file( $path ) ) {
        return;
    }

    get_template_part( 'template-parts/homepage/law-01/' . $slug, null, $args );
}

/**
 * Render Law 01 sections that precede the native Front Page body boundary.
 */
function render_homepage_before_content(): void {
    if ( ! homepage_composer_active() ) {
        return;
    }

    echo '<div class="aznet-theme-homepage aznet-theme-homepage--law-01">';
    foreach ( [ 'hero', 'services', 'about', 'team' ] as $section ) {
        render_law01_part( $section );
    }
}

/**
 * Render Law 01 sections after the native Front Page body boundary.
 */
function render_homepage_after_content(): void {
    if ( ! homepage_composer_active() ) {
        return;
    }

    foreach ( [ 'topics', 'latest', 'analysis', 'news', 'process', 'faq', 'final-cta' ] as $section ) {
        render_law01_part( $section );
    }
    echo '</div>';
}

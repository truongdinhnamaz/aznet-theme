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

/** Return the normalized Homepage presentation preset. */
function homepage_preset(): string {
    return (string) setting( 'homepage_preset', 'off' );
}

/** Return the normalized Law 01 visual variant. */
function homepage_law01_variant(): string {
    $variant = (string) setting( 'homepage_law01_variant', 'navy-gold' );
    return in_array( $variant, [ 'navy-gold', 'burgundy-gold' ], true ) ? $variant : 'navy-gold';
}

/** Whether Theme-owned Homepage composition is active for this request. */
function homepage_composer_active(): bool {
    if ( ! function_exists( 'is_front_page' ) || ! is_front_page() || ! in_array( homepage_preset(), [ 'law-01', 'curtain-01' ], true ) ) {
        return false;
    }

    if ( 'page' !== get_option( 'show_on_front' ) ) {
        return false;
    }

    $front_id = (int) get_option( 'page_on_front' );
    return $front_id > 0 && null !== homepage_page_reference( $front_id );
}

/** Reset request-local displayed Post IDs. */
function homepage_ledger_reset(): void { $GLOBALS['aznet_theme_homepage_ledger'] = []; }

/** @param array<int, mixed> $post_ids Post IDs. */
function homepage_ledger_add( array $post_ids ): void {
    $ledger = homepage_ledger_ids();
    foreach ( $post_ids as $candidate ) {
        $id = is_numeric( $candidate ) ? (int) $candidate : 0;
        if ( $id > 0 && ! in_array( $id, $ledger, true ) ) { $ledger[] = $id; }
    }
    $GLOBALS['aznet_theme_homepage_ledger'] = $ledger;
}

/** @return array<int, int> */
function homepage_ledger_ids(): array {
    $ledger = $GLOBALS['aznet_theme_homepage_ledger'] ?? [];
    return is_array( $ledger ) ? array_values( array_filter( array_map( 'intval', $ledger ) ) ) : [];
}

/** @param string $slug Section slug. @param array<string, mixed> $args Section arguments. */
function render_law01_part( string $slug, array $args = [] ): void {
    $path = get_theme_file_path( 'template-parts/homepage/law-01/' . $slug . '.php' );
    if ( ! is_file( $path ) ) { return; }
    get_template_part( 'template-parts/homepage/law-01/' . $slug, null, $args );
}

/** @param string $slug Curtain 01 section slug. @param array<string, mixed> $args Section arguments. */
function render_curtain01_part( string $slug, array $args = [] ): void {
    $path = get_theme_file_path( 'template-parts/homepage/curtain-01/' . $slug . '.php' );
    if ( ! is_file( $path ) ) { return; }
    get_template_part( 'template-parts/homepage/curtain-01/' . $slug, null, $args );
}

/** Render Law 01 sections that precede the native Front Page body boundary. */
function render_homepage_before_content(): void {
    if ( ! homepage_composer_active() ) { return; }

    if ( 'curtain-01' === homepage_preset() ) {
        echo '<div class="aznet-theme-homepage aznet-theme-homepage--curtain-01">';
        render_curtain01_part( 'hero' );
        render_curtain01_part( 'proof-strip' );
        return;
    }

    $variant = homepage_law01_variant();
    echo '<div class="aznet-theme-homepage aznet-theme-homepage--law-01 aznet-theme-homepage--law-01-' . esc_attr( $variant ) . '">';
    foreach ( [ 'hero', 'services', 'profile' ] as $section ) { render_law01_part( $section ); }
}

/** Render Law 01 sections after the native Front Page body boundary. */
function render_homepage_after_content(): void {
    if ( ! homepage_composer_active() ) { return; }

    if ( 'curtain-01' === homepage_preset() ) {
        foreach ( [ 'about', 'category-showcase', 'catalogue', 'process', 'projects', 'knowledge', 'final-cta' ] as $section ) {
            render_curtain01_part( $section );
        }
        echo '</div>';
        return;
    }

    $variant = homepage_law01_variant();
    $sections = 'burgundy-gold' === $variant ? [ 'latest', 'topics', 'analysis', 'news', 'process', 'faq', 'final-cta' ] : [ 'topics', 'latest', 'analysis', 'news', 'process', 'faq', 'final-cta' ];
    foreach ( $sections as $section ) { render_law01_part( $section ); }
    echo '</div>';
}

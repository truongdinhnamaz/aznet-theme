<?php
/**
 * Core Homepage Design backend shell.
 *
 * Core owns the shared admin flow. Template manifests own surface-specific
 * presentation adapters. WordPress/provider owners keep their authoritative data.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Admin;

use function AZnet\Theme\homepage_source_value;
use function AZnet\Theme\settings;
use function AZnet\Theme\template_manifest;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return one template-declared Homepage design surface config.
 *
 * @return array<string,mixed>
 */
function homepage_design_surface_config( string $preset, string $surface ): array {
    $manifest = template_manifest( $preset );
    if ( ! is_array( $manifest ) ) {
        return [];
    }

    $homepage = isset( $manifest['homepage'] ) && is_array( $manifest['homepage'] )
        ? $manifest['homepage']
        : [];
    $config = $homepage[ $surface ] ?? [];

    return is_array( $config ) ? $config : [];
}

function homepage_hero_design_available( string $preset ): bool {
    $config = homepage_design_surface_config( $preset, 'hero' );
    return true === (bool) ( $config['enabled'] ?? false )
        && '' !== trim( (string) ( $config['admin_renderer'] ?? '' ) );
}

function homepage_hero_design_renderer( string $preset ): string {
    if ( ! homepage_hero_design_available( $preset ) ) {
        return '';
    }

    $config = homepage_design_surface_config( $preset, 'hero' );
    return trim( (string) ( $config['admin_renderer'] ?? '' ) );
}

/**
 * Core fallback for template Heroes that are WordPress-owned blocks but do not
 * need a custom form. This keeps content editing in WordPress and keeps Core
 * free of template-specific field semantics.
 */
function render_homepage_native_hero_editor( array $theme_settings, string $preset ): void {
    $hero_id = (int) homepage_source_value( $preset, 'hero', $theme_settings );
    $hero = $hero_id > 0 ? get_post( $hero_id ) : null;

    echo '<div class="aznet-theme-panel aznet-theme-homepage-hero-editor">';
    echo '<h2>' . esc_html__( 'Hero', 'aznet-theme' ) . '</h2>';

    if ( $hero instanceof \WP_Post && 'wp_block' === $hero->post_type ) {
        $edit_url = get_edit_post_link( $hero->ID, 'raw' );
        echo '<p class="description">' . esc_html__( 'Nội dung Hero thuộc WordPress. Theme chỉ cung cấp bố cục và presentation của mẫu đang dùng.', 'aznet-theme' ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Nguồn:', 'aznet-theme' ) . '</strong> ' . esc_html( get_the_title( $hero ) ) . '</p>';
        if ( is_string( $edit_url ) && '' !== $edit_url ) {
            echo '<p><a class="button button-primary" href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Sửa nội dung Hero bằng WordPress', 'aznet-theme' ) . '</a></p>';
        }
    } else {
        echo '<p class="notice notice-info inline">' . esc_html__( 'Mẫu này chưa có nguồn Hero WordPress hợp lệ. Hãy cấu hình nguồn ở phần Nguồn & cài đặt nâng cao.', 'aznet-theme' ) . '</p>';
    }

    echo '</div>';
}

/**
 * Render the shared Homepage Design > Hero screen and delegate only the
 * template-specific body to the renderer declared by the active manifest.
 */
function render_homepage_design_hero_screen(): void {
    $theme_settings = settings();
    $preset = (string) ( $theme_settings['homepage_preset'] ?? 'off' );
    $manifest = template_manifest( $preset );
    $back_url = add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'homepage' ], admin_url( 'admin.php' ) );

    echo '<div class="aznet-theme-panel aznet-theme-homepage-design-screen">';
    echo '<p><a class="button" href="' . esc_url( $back_url ) . '">← ' . esc_html__( 'Quay lại Trang chủ', 'aznet-theme' ) . '</a></p>';
    echo '<p class="aznet-theme-homepage-design-screen__eyebrow">' . esc_html__( 'Thiết kế Trang chủ', 'aznet-theme' ) . '</p>';
    echo '<h2>' . esc_html__( 'Hero', 'aznet-theme' ) . '</h2>';

    if ( is_array( $manifest ) ) {
        echo '<p class="description">' . esc_html(
            sprintf(
                /* translators: %s: template name */
                __( 'Core AZnet Theme đang chỉnh Hero cho mẫu %s. Phần dùng chung do Core quản lý; chi tiết presentation riêng do mẫu khai báo.', 'aznet-theme' ),
                (string) ( $manifest['name'] ?? $preset )
            )
        ) . '</p>';
    }
    echo '</div>';

    $renderer = homepage_hero_design_renderer( $preset );
    if ( '' === $renderer || ! is_callable( __NAMESPACE__ . '\\' . $renderer ) ) {
        echo '<div class="aznet-theme-panel"><p class="notice notice-info inline">' . esc_html__( 'Mẫu đang dùng chưa khai báo bộ thiết kế Hero cho Core.', 'aznet-theme' ) . '</p></div>';
        return;
    }

    $callback = __NAMESPACE__ . '\\' . $renderer;
    if ( 'render_homepage_native_hero_editor' === $renderer ) {
        $callback( $theme_settings, $preset );
        return;
    }

    $callback( $theme_settings );
}

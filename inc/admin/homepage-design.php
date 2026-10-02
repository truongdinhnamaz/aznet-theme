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
 * Return a normalized manifest-declared mapping from generic Hero preview roles
 * to template-owned presentation setting keys.
 *
 * Core understands only presentation roles; it never derives template semantics
 * from a preset id or from setting-name heuristics.
 *
 * @return array<string,string>
 */
function homepage_hero_design_preview_config( string $preset ): array {
    $config = homepage_design_surface_config( $preset, 'hero' );
    $preview = $config['preview'] ?? [];
    if ( ! is_array( $preview ) ) {
        return [];
    }

    $allowed_roles = [ 'eyebrow', 'title', 'value', 'lead', 'primary_label', 'secondary_label', 'image' ];
    $normalized = [];

    foreach ( $allowed_roles as $role ) {
        $key = trim( (string) ( $preview[ $role ] ?? '' ) );
        if ( '' !== $key ) {
            $normalized[ $role ] = $key;
        }
    }

    return $normalized;
}


function render_homepage_design_entrypoint( string $preset ): void {
    if ( ! homepage_hero_design_available( $preset ) ) {
        return;
    }

    $url = add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'hero-library' ], admin_url( 'admin.php' ) );
    echo '<div class="aznet-theme-panel aznet-theme-homepage-design-entry">';
    echo '<p class="aznet-theme-homepage-design-screen__eyebrow">' . esc_html__( 'Thiết kế Trang chủ', 'aznet-theme' ) . '</p>';
    echo '<div class="aznet-theme-homepage-design-entry__row"><div><h2>' . esc_html__( 'Hero', 'aznet-theme' ) . '</h2><p class="description">' . esc_html__( 'Bắt đầu từ Hero. Core cung cấp backend chung; mẫu đang dùng chỉ khai báo phần presentation riêng.', 'aznet-theme' ) . '</p></div>';
    echo '<a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Thiết kế Hero', 'aznet-theme' ) . '</a></div>';
    echo '</div>';
}


/** @return array<int,array<string,mixed>> */
function homepage_hero_design_settings_fields( string $preset ): array {
    $config = homepage_design_surface_config( $preset, 'hero' );
    $fields = $config['settings_fields'] ?? [];
    if ( ! is_array( $fields ) ) {
        return [];
    }

    $normalized = [];
    foreach ( $fields as $field ) {
        if ( ! is_array( $field ) ) { continue; }
        $key = trim( (string) ( $field['key'] ?? '' ) );
        $type = trim( (string) ( $field['type'] ?? 'text' ) );
        $label = trim( (string) ( $field['label'] ?? '' ) );
        if ( '' === $key || '' === $label || ! in_array( $type, [ 'text', 'textarea', 'url', 'image' ], true ) ) {
            continue;
        }
        $normalized[] = [
            'key'         => $key,
            'type'        => $type,
            'label'       => $label,
            'placeholder' => (string) ( $field['placeholder'] ?? '' ),
            'help'        => (string) ( $field['help'] ?? '' ),
        ];
    }

    return $normalized;
}

/**
 * Generic Core renderer for a template that declares Theme presentation
 * settings as a compatibility adapter. Template-specific field definitions
 * live in its manifest; Core owns only the form lifecycle/UI primitives.
 */
function render_homepage_manifest_settings_hero_editor( array $theme_settings, string $preset ): void {
    $fields = homepage_hero_design_settings_fields( $preset );
    if ( [] === $fields ) {
        echo '<div class="aznet-theme-panel"><p class="notice notice-info inline">' . esc_html__( 'Mẫu này chưa khai báo trường thiết kế Hero.', 'aznet-theme' ) . '</p></div>';
        return;
    }

    $keys = array_values( array_map( static fn( array $field ): string => (string) $field['key'], $fields ) );
    $preview = homepage_hero_design_preview_config( $preset );
    echo '<section class="aznet-theme-panel aznet-theme-homepage-manifest-hero-editor">';
    echo '<p class="description">' . esc_html__( 'Các trường riêng của mẫu được khai báo trong manifest; Core AZnet Theme chỉ cung cấp form, media picker và vòng đời lưu chung.', 'aznet-theme' ) . '</p>';
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( $keys );
    echo '<div class="aznet-theme-homepage-hero-simple-form__grid">';

    foreach ( $fields as $field ) {
        $key = (string) $field['key'];
        $type = (string) $field['type'];
        $label = (string) $field['label'];
        $placeholder = (string) $field['placeholder'];
        $help = (string) $field['help'];
        $value = $theme_settings[ $key ] ?? ( 'image' === $type ? 0 : '' );

        if ( 'image' === $type ) {
            $id = (int) $value;
            $input_id = 'aznet-homepage-design-' . sanitize_html_class( str_replace( '_', '-', $key ) );
            $preview_id = $input_id . '-preview';
            echo '<div class="aznet-theme-field aznet-theme-homepage-hero-simple-form__media"><span>' . esc_html( $label ) . '</span>';
            echo '<input id="' . esc_attr( $input_id ) . '" type="hidden" name="aznet_theme_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $id ) . '" data-preview-key="' . esc_attr( $key ) . '">';
            echo '<div id="' . esc_attr( $preview_id ) . '" class="aznet-theme-homepage-media-preview">';
            if ( $id > 0 ) { echo wp_kses_post( wp_get_attachment_image( $id, 'medium' ) ); }
            echo '</div><p>';
            echo '<button type="button" class="button aznet-theme-homepage-media-select" data-media-input="' . esc_attr( $input_id ) . '" data-media-preview="' . esc_attr( $preview_id ) . '" data-preview-key="' . esc_attr( $key ) . '">' . esc_html__( 'Chọn / thay ảnh', 'aznet-theme' ) . '</button> ';
            echo '<button type="button" class="button-link-delete aznet-theme-homepage-media-clear" data-media-input="' . esc_attr( $input_id ) . '" data-media-preview="' . esc_attr( $preview_id ) . '" data-preview-key="' . esc_attr( $key ) . '">' . esc_html__( 'Bỏ ảnh', 'aznet-theme' ) . '</button>';
            echo '</p>';
            if ( '' !== $help ) { echo '<small class="description">' . esc_html( $help ) . '</small>'; }
            echo '</div>';
            continue;
        }

        $wide = 'textarea' === $type ? ' aznet-theme-homepage-hero-simple-form__wide' : '';
        echo '<label class="aznet-theme-field' . esc_attr( $wide ) . '"><span>' . esc_html( $label ) . '</span>';
        if ( 'textarea' === $type ) {
            echo '<textarea rows="3" name="aznet_theme_settings[' . esc_attr( $key ) . ']" data-preview-key="' . esc_attr( $key ) . '" placeholder="' . esc_attr( $placeholder ) . '">' . esc_textarea( (string) $value ) . '</textarea>';
        } else {
            $html_type = 'url' === $type ? 'url' : 'text';
            echo '<input type="' . esc_attr( $html_type ) . '" name="aznet_theme_settings[' . esc_attr( $key ) . ']" data-preview-key="' . esc_attr( $key ) . '" value="' . esc_attr( (string) $value ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
        }
        if ( '' !== $help ) { echo '<small class="description">' . esc_html( $help ) . '</small>'; }
        echo '</label>';
    }

    echo '</div>';

    if ( [] !== $preview ) {
        $preview_value = static function ( string $role ) use ( $preview, $theme_settings ): string {
            $key = (string) ( $preview[ $role ] ?? '' );
            return '' !== $key ? (string) ( $theme_settings[ $key ] ?? '' ) : '';
        };
        $image_key = (string) ( $preview['image'] ?? '' );
        $image_id = '' !== $image_key ? (int) ( $theme_settings[ $image_key ] ?? 0 ) : 0;

        echo '<div class="aznet-theme-homepage-hero-live-preview aznet-theme-homepage-hero-live-preview--split" data-hero-live-preview>';
        echo '<div class="aznet-theme-homepage-hero-live-preview__toolbar"><strong>' . esc_html__( 'Xem trước Hero', 'aznet-theme' ) . '</strong><span>' . esc_html__( 'Cập nhật tức thời khi chỉnh trường', 'aznet-theme' ) . '</span></div>';
        echo '<div class="aznet-theme-homepage-hero-live-preview__canvas">';
        echo '<div class="aznet-theme-homepage-hero-live-preview__copy">';
        if ( isset( $preview['eyebrow'] ) ) { echo '<span class="aznet-theme-homepage-hero-live-preview__eyebrow" data-preview-key-target="' . esc_attr( $preview['eyebrow'] ) . '">' . esc_html( $preview_value( 'eyebrow' ) ) . '</span>'; }
        if ( isset( $preview['title'] ) ) { echo '<strong class="aznet-theme-homepage-hero-live-preview__title" data-preview-key-target="' . esc_attr( $preview['title'] ) . '">' . esc_html( $preview_value( 'title' ) ) . '</strong>'; }
        if ( isset( $preview['value'] ) ) { echo '<span class="aznet-theme-homepage-hero-live-preview__value" data-preview-key-target="' . esc_attr( $preview['value'] ) . '">' . esc_html( $preview_value( 'value' ) ) . '</span>'; }
        if ( isset( $preview['lead'] ) ) { echo '<span class="aznet-theme-homepage-hero-live-preview__lead" data-preview-key-target="' . esc_attr( $preview['lead'] ) . '">' . esc_html( $preview_value( 'lead' ) ) . '</span>'; }
        if ( isset( $preview['primary_label'] ) || isset( $preview['secondary_label'] ) ) {
            echo '<span class="aznet-theme-homepage-hero-live-preview__actions">';
            if ( isset( $preview['primary_label'] ) ) { echo '<span data-preview-key-target="' . esc_attr( $preview['primary_label'] ) . '">' . esc_html( $preview_value( 'primary_label' ) ) . '</span>'; }
            if ( isset( $preview['secondary_label'] ) ) { echo '<span data-preview-key-target="' . esc_attr( $preview['secondary_label'] ) . '">' . esc_html( $preview_value( 'secondary_label' ) ) . '</span>'; }
            echo '</span>';
        }
        echo '</div>';
        echo '<div class="aznet-theme-homepage-hero-live-preview__media"' . ( '' !== $image_key ? ' data-preview-media-key="' . esc_attr( $image_key ) . '"' : '' ) . '>';
        if ( $image_id > 0 ) { echo wp_kses_post( wp_get_attachment_image( $image_id, 'medium_large' ) ); }
        echo '</div></div></div>';
    }

    submit_button( __( 'Lưu Hero', 'aznet-theme' ) );
    echo '</form></section>';
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
    if ( in_array( $renderer, [ 'render_homepage_native_hero_editor', 'render_homepage_manifest_settings_hero_editor' ], true ) ) {
        $callback( $theme_settings, $preset );
        return;
    }

    $callback( $theme_settings );
}

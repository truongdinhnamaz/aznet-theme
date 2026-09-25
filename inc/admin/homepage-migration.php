<?php
/** Explicit, reference-only Homepage preset migration. */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\homepage_preset_registry;
use function AZnet\Theme\homepage_source_initialization_key;
use function AZnet\Theme\homepage_source_neutral_value;
use function AZnet\Theme\homepage_source_value_is_nonempty;
use function AZnet\Theme\normalize_settings;
use function AZnet\Theme\settings;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** @return array<string, array<string, mixed>> */
function homepage_preset_migration_map( string $preset ): array {
    $registry = homepage_preset_registry();
    return isset( $registry[ $preset ] ) && is_array( $registry[ $preset ] ) ? $registry[ $preset ] : [];
}

/**
 * Copy legacy Theme references into empty preset-scoped keys only.
 *
 * No WordPress content is created, edited, published or deleted here.
 *
 * @param array<string, mixed> $settings Normalized Theme settings.
 * @return array<string, mixed>
 */
function migrate_homepage_preset_references( string $preset, array $settings ): array {
    $map = homepage_preset_migration_map( $preset );
    $marker = homepage_source_initialization_key( $preset );
    if ( [] === $map || null === $marker ) {
        return $settings;
    }

    foreach ( $map as $descriptor ) {
        if ( ! is_array( $descriptor ) ) { continue; }
        $key = (string) ( $descriptor['key'] ?? '' );
        $legacy = (string) ( $descriptor['legacy'] ?? '' );
        $type = (string) ( $descriptor['type'] ?? '' );
        if ( '' === $key || '' === $legacy || $key === $legacy ) { continue; }

        $current = $settings[ $key ] ?? homepage_source_neutral_value( $type );
        if ( homepage_source_value_is_nonempty( $current, $type ) ) { continue; }

        $legacy_value = $settings[ $legacy ] ?? homepage_source_neutral_value( $type );
        if ( homepage_source_value_is_nonempty( $legacy_value, $type ) ) {
            $settings[ $key ] = $legacy_value;
        }
    }

    $settings[ $marker ] = true;
    return normalize_settings( $settings );
}

/** Execute one authorized explicit mapping migration. */
function handle_homepage_preset_migration(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thay đổi thiết lập Theme.', 'aznet-theme' ) );
    }
    check_admin_referer( 'aznet_theme_migrate_homepage_preset' );

    $preset = isset( $_POST['homepage_preset_scope'] )
        ? sanitize_key( wp_unslash( $_POST['homepage_preset_scope'] ) )
        : '';
    if ( ! in_array( $preset, [ 'law-01', 'curtain-01' ], true ) ) {
        wp_die( esc_html__( 'Mẫu trang chủ không hợp lệ.', 'aznet-theme' ) );
    }

    $migrated = migrate_homepage_preset_references( $preset, settings() );
    set_theme_mod( 'aznet_theme_settings', normalize_settings( $migrated ) );
    wp_safe_redirect(
        add_query_arg(
            [
                'page'              => 'aznet-theme',
                'section'           => 'homepage',
                'homepage_migrated' => $preset,
            ],
            admin_url( 'admin.php' )
        )
    );
    exit;
}

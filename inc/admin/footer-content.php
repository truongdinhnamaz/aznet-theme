<?php
/**
 * WordPress-native Footer column content authoring.
 *
 * Each selected Footer template exposes a fixed number of presentation columns.
 * Rich content lives in WordPress core wp_block posts; the Theme stores only
 * bounded references for the selected preset/column.
 *
 * @package AZnetTheme
 */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\normalize_settings;
use function AZnet\Theme\setting;
use function AZnet\Theme\settings;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Return the generic Footer presets and their presentation column counts.
 *
 * @return array<string,int>
 */
function footer_column_counts(): array {
    return [
        'minimal'      => 2,
        'classic'      => 3,
        'professional' => 4,
        'split'        => 3,
        'centered'     => 1,
        'compact'      => 3,
    ];
}

function footer_column_setting_key( string $preset, int $column ): string {
    return 'footer_' . $preset . '_column_' . $column . '_block';
}

function footer_column_block_id( string $preset, int $column ): int {
    $counts = footer_column_counts();
    if ( ! isset( $counts[ $preset ] ) || $column < 1 || $column > $counts[ $preset ] ) {
        return 0;
    }
    return (int) setting( footer_column_setting_key( $preset, $column ), 0 );
}

/**
 * Seed the first unsaved column from the previous shared Footer profile so the
 * migration does not silently discard contact/social content.
 */
function footer_column_seed_from_menus( int $column ): string {
    if ( 1 !== $column ) {
        return '';
    }

    $values = function_exists( __NAMESPACE__ . '\\footer_profile_from_menus' ) ? footer_profile_from_menus() : [];
    $rows = [];
    $defs = [
        'location'  => [ __( 'Địa chỉ', 'aznet-theme' ), '' ],
        'phone'     => [ __( 'SĐT', 'aznet-theme' ), 'tel:' ],
        'email'     => [ __( 'Email', 'aznet-theme' ), 'mailto:' ],
        'website'   => [ __( 'Website', 'aznet-theme' ), '' ],
        'facebook'  => [ __( 'Fanpage', 'aznet-theme' ), '' ],
        'youtube'   => [ __( 'YouTube', 'aznet-theme' ), '' ],
        'linkedin'  => [ __( 'LinkedIn', 'aznet-theme' ), '' ],
        'tiktok'    => [ __( 'TikTok', 'aznet-theme' ), '' ],
        'instagram' => [ __( 'Instagram', 'aznet-theme' ), '' ],
    ];

    foreach ( $defs as $key => $def ) {
        $value = trim( (string) ( $values[ $key ] ?? '' ) );
        if ( '' === $value ) {
            continue;
        }

        $label = esc_html( (string) $def[0] );
        if ( 'location' === $key ) {
            $rows[] = '<strong>' . $label . ':</strong> ' . esc_html( $value );
            continue;
        }

        $href = in_array( $key, [ 'phone', 'email' ], true ) ? (string) $def[1] . $value : $value;
        $rows[] = '<strong>' . $label . ':</strong> <a href="' . esc_url( $href, [ 'http', 'https', 'tel', 'mailto' ] ) . '">' . esc_html( $value ) . '</a>';
    }

    return empty( $rows ) ? '' : '<p>' . implode( '<br>', $rows ) . '</p>';
}

function footer_column_editor_value( string $preset, int $column ): string {
    $block_id = footer_column_block_id( $preset, $column );
    if ( $block_id > 0 ) {
        $post = get_post( $block_id );
        if ( $post instanceof \WP_Post && 'wp_block' === $post->post_type ) {
            return (string) $post->post_content;
        }
    }

    return footer_column_seed_from_menus( $column );
}

/**
 * Save exactly the column set belonging to the submitted Footer template.
 */
function handle_footer_columns_save(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) );
    }
    check_admin_referer( 'aznet_theme_save_footer_columns' );

    $preset = isset( $_POST['aznet_theme_footer_editor_preset'] )
        ? sanitize_key( wp_unslash( (string) $_POST['aznet_theme_footer_editor_preset'] ) )
        : '';

    $counts = footer_column_counts();
    if ( ! isset( $counts[ $preset ] ) ) {
        wp_die( esc_html__( 'Mẫu Footer không hợp lệ.', 'aznet-theme' ) );
    }

    $submitted = isset( $_POST['aznet_theme_footer_columns'] ) && is_array( $_POST['aznet_theme_footer_columns'] )
        ? $_POST['aznet_theme_footer_columns']
        : [];

    $raw = settings();

    for ( $column = 1; $column <= $counts[ $preset ]; $column++ ) {
        $content = isset( $submitted[ $column ] )
            ? wp_kses_post( wp_unslash( (string) $submitted[ $column ] ) )
            : '';

        $setting_key = footer_column_setting_key( $preset, $column );
        $block_id    = footer_column_block_id( $preset, $column );
        $post        = $block_id > 0 ? get_post( $block_id ) : null;

        if ( $post instanceof \WP_Post && 'wp_block' === $post->post_type ) {
            $result = wp_update_post(
                [
                    'ID'           => $block_id,
                    'post_content' => $content,
                ],
                true
            );
        } else {
            $result = wp_insert_post(
                [
                    'post_type'    => 'wp_block',
                    'post_status'  => 'publish',
                    'post_title'   => sprintf(
                        /* translators: 1: Footer preset, 2: column number. */
                        __( 'AZnet Footer %1$s - Cột %2$d', 'aznet-theme' ),
                        $preset,
                        $column
                    ),
                    'post_content' => $content,
                ],
                true
            );
        }

        if ( is_wp_error( $result ) ) {
            wp_die( esc_html( $result->get_error_message() ) );
        }

        $raw[ $setting_key ] = (int) $result;
    }

    set_theme_mod( 'aznet_theme_settings', normalize_settings( $raw ) );

    wp_safe_redirect(
        add_query_arg(
            [
                'page'         => 'aznet-theme',
                'section'      => 'footer',
                'footer_saved' => '1',
            ],
            admin_url( 'admin.php' )
        )
    );
    exit;
}

if ( is_admin() ) {
    add_action( 'admin_post_aznet_theme_save_footer_columns', __NAMESPACE__ . '\\handle_footer_columns_save' );
}

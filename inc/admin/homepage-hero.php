<?php
/**
 * Explicit WordPress-native Homepage Hero initialization.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Admin;

use function AZnet\Theme\homepage_block_reference;
use function AZnet\Theme\normalize_settings;
use function AZnet\Theme\settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Return the D-030 Hero Library presentation variants. */
function homepage_hero_library_variants(): array {
    return [
        'split'      => __( 'Chia đôi', 'aznet-theme' ),
        'centered'   => __( 'Căn giữa', 'aznet-theme' ),
        'inverse'    => __( 'Tương phản', 'aznet-theme' ),
        'media-left' => __( 'Ảnh bên trái', 'aznet-theme' ),
    ];
}

/**
 * Resolve the configured WordPress Hero block for admin authoring, including a draft.
 */
function homepage_hero_candidate_reference( int $id ): ?\WP_Post {
    if ( $id <= 0 ) {
        return null;
    }

    $post = get_post( $id );
    if ( ! $post instanceof \WP_Post || 'wp_block' !== $post->post_type || ! in_array( $post->post_status, [ 'draft', 'publish' ], true ) ) {
        return null;
    }

    return $post;
}

/** Resolve the portable Core-block scaffold registered by WordPress. */
function homepage_hero_scaffold_content(): string {
    if ( ! class_exists( '\\WP_Block_Patterns_Registry' ) ) {
        return '';
    }

    $pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'aznet-theme/homepage-hero-content' );
    return is_array( $pattern ) && isset( $pattern['content'] ) ? trim( (string) $pattern['content'] ) : '';
}

/** Explicitly initialize/select a WordPress-owned synced Hero and apply its Theme presentation variant. */
function handle_homepage_hero_apply(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thay đổi Hero trang chủ.', 'aznet-theme' ) );
    }

    check_admin_referer( 'aznet_theme_apply_homepage_hero' );

    $variants = homepage_hero_library_variants();
    $requested = isset( $_POST['homepage_hero_variant'] )
        ? sanitize_key( (string) wp_unslash( $_POST['homepage_hero_variant'] ) )
        : 'split';
    $variant = isset( $variants[ $requested ] ) ? $requested : 'split';

    $theme_settings = settings();
    $hero_id = (int) ( $theme_settings['homepage_hero_block'] ?? 0 );
    $hero = homepage_hero_candidate_reference( $hero_id );
    $created = false;

    if ( ! $hero instanceof \WP_Post ) {
        $content = homepage_hero_scaffold_content();
        if ( '' === $content ) {
            wp_die( esc_html__( 'Không thể khởi tạo nội dung Hero WordPress.', 'aznet-theme' ) );
        }

        $hero_id = wp_insert_post(
            [
                'post_type'    => 'wp_block',
                'post_status'  => 'draft',
                'post_title'   => __( 'Hero trang chủ', 'aznet-theme' ),
                'post_content' => $content,
            ],
            true
        );

        if ( is_wp_error( $hero_id ) || (int) $hero_id <= 0 ) {
            wp_die( esc_html__( 'Không thể tạo Hero WordPress.', 'aznet-theme' ) );
        }

        $hero_id = (int) $hero_id;
        $theme_settings['homepage_hero_block'] = $hero_id;
        $hero = get_post( $hero_id );
        $created = true;
    }

    $theme_settings['homepage_hero_variant'] = $variant;
    set_theme_mod( 'aznet_theme_settings', normalize_settings( $theme_settings ) );

    if ( $hero instanceof \WP_Post && 'draft' === $hero->post_status ) {
        $edit_link = get_edit_post_link( $hero->ID, 'raw' );
        if ( is_string( $edit_link ) && '' !== $edit_link ) {
            wp_safe_redirect( $edit_link );
            exit;
        }
    }

    wp_safe_redirect(
        add_query_arg(
            [
                'page'    => 'aznet-theme',
                'section' => 'homepage',
                'hero'    => $created ? 'draft' : 'ready',
            ],
            admin_url( 'admin.php' )
        )
    );
    exit;
}

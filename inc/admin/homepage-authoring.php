<?php
/** Bounded WordPress-native Homepage authoring actions. */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\homepage_category_reference;
use function AZnet\Theme\homepage_source_descriptor;
use function AZnet\Theme\homepage_source_value;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Whether one exact source ID is authorized by the preset/slot mapping. */
function homepage_quick_edit_target_allowed( string $preset, string $slot, int $source_id, array $descriptor ): bool {
    $mapped = homepage_source_value( $preset, $slot );
    $type = (string) ( $descriptor['type'] ?? '' );

    if ( 'categories' === $type ) {
        return in_array( $source_id, array_map( 'intval', (array) $mapped ), true );
    }

    if ( (int) $mapped === $source_id ) { return true; }

    if ( 'page' === $type && ! empty( $descriptor['children'] ) && (int) $mapped > 0 ) {
        $target = get_post( $source_id );
        return $target instanceof \WP_Post
            && 'page' === $target->post_type
            && (int) $target->post_parent === (int) $mapped;
    }

    return false;
}

/** Execute bounded Quick Edit against the already-mapped WordPress source. */
function handle_homepage_quick_edit_source(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thay đổi thiết lập Theme.', 'aznet-theme' ) );
    }
    check_admin_referer( 'aznet_theme_quick_edit_homepage_source' );

    $preset = isset( $_POST['homepage_preset_scope'] ) ? sanitize_key( wp_unslash( $_POST['homepage_preset_scope'] ) ) : '';
    $slot = isset( $_POST['homepage_source_slot'] ) ? sanitize_key( wp_unslash( $_POST['homepage_source_slot'] ) ) : '';
    $source_id = isset( $_POST['homepage_source_id'] ) ? absint( $_POST['homepage_source_id'] ) : 0;
    $descriptor = homepage_source_descriptor( $preset, $slot );

    if ( ! in_array( $preset, [ 'law-01', 'curtain-01' ], true ) || null === $descriptor || $source_id <= 0 || ! homepage_quick_edit_target_allowed( $preset, $slot, $source_id, $descriptor ) ) {
        wp_die( esc_html__( 'Nguồn chỉnh sửa không còn khớp với cấu hình trang chủ.', 'aznet-theme' ) );
    }

    $type = (string) ( $descriptor['type'] ?? '' );
    if ( 'page' === $type ) {
        $post = get_post( $source_id );
        if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type || ! current_user_can( 'edit_post', $source_id ) ) {
            wp_die( esc_html__( 'Page không hợp lệ hoặc bạn không có quyền chỉnh sửa.', 'aznet-theme' ) );
        }
        $title = isset( $_POST['homepage_source_title'] ) ? sanitize_text_field( wp_unslash( $_POST['homepage_source_title'] ) ) : '';
        $excerpt = isset( $_POST['homepage_source_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['homepage_source_excerpt'] ) ) : '';
        $result = wp_update_post( [ 'ID' => $source_id, 'post_title' => $title, 'post_excerpt' => $excerpt ], true );
        if ( is_wp_error( $result ) ) { wp_die( esc_html( $result->get_error_message() ) ); }

        $image_id = isset( $_POST['homepage_featured_image_id'] ) ? absint( $_POST['homepage_featured_image_id'] ) : 0;
        if ( $image_id > 0 ) {
            if ( ! wp_attachment_is_image( $image_id ) ) {
                wp_die( esc_html__( 'Ảnh đại diện không hợp lệ.', 'aznet-theme' ) );
            }
            set_post_thumbnail( $source_id, $image_id );
        } else {
            delete_post_thumbnail( $source_id );
        }
    } elseif ( in_array( $type, [ 'category', 'categories' ], true ) ) {
        $term = homepage_category_reference( $source_id );
        if ( ! $term instanceof \WP_Term || ! current_user_can( 'manage_categories' ) ) {
            wp_die( esc_html__( 'Chuyên mục không hợp lệ hoặc bạn không có quyền chỉnh sửa.', 'aznet-theme' ) );
        }
        $name = isset( $_POST['homepage_source_title'] ) ? sanitize_text_field( wp_unslash( $_POST['homepage_source_title'] ) ) : '';
        $description = isset( $_POST['homepage_source_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['homepage_source_excerpt'] ) ) : '';
        $result = wp_update_term( $source_id, 'category', [ 'name' => $name, 'description' => $description ] );
        if ( is_wp_error( $result ) ) { wp_die( esc_html( $result->get_error_message() ) ); }
    } else {
        wp_die( esc_html__( 'Loại nguồn này chỉ được chỉnh trong trình soạn thảo WordPress đầy đủ.', 'aznet-theme' ) );
    }

    wp_safe_redirect( add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'homepage', 'homepage_quick_edited' => '1' ], admin_url( 'admin.php' ) ) );
    exit;
}

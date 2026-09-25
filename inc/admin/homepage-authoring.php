<?php
/** Bounded WordPress-native Homepage authoring actions. */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\homepage_category_reference;
use function AZnet\Theme\homepage_source_descriptor;
use function AZnet\Theme\homepage_source_value;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Whether Quick Edit may author Featured Image for this exact Homepage source. */
function homepage_quick_edit_featured_image_allowed( string $preset, string $slot, int $source_id, array $descriptor ): bool {
    if ( 'page' !== (string) ( $descriptor['type'] ?? '' ) || $source_id <= 0 ) {
        return false;
    }

    if ( 'law-01' === $preset && 'team' === $slot ) {
        return (int) homepage_source_value( $preset, $slot ) !== $source_id;
    }

    return true;
}

/** Whether one exact source ID is authorized by the preset/slot mapping. */
function homepage_quick_edit_target_allowed( string $preset, string $slot, int $source_id, array $descriptor ): bool {
    $mapped = homepage_source_value( $preset, $slot );
    $type = (string) ( $descriptor['type'] ?? '' );

    if ( 'categories' === $type ) {
        return in_array( $source_id, array_map( 'intval', (array) $mapped ), true );
    }

    if ( (int) $mapped === $source_id ) { return true; }

    if ( 'law-01' === $preset && 'team' === $slot && 'page' === $type ) {
        return \AZnet\Theme\team_directory_member_is_child( $source_id );
    }

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
        $image_id = isset( $_POST['homepage_featured_image_id'] ) ? absint( $_POST['homepage_featured_image_id'] ) : 0;
        if ( homepage_quick_edit_featured_image_allowed( $preset, $slot, $source_id, $descriptor ) ) {
            if ( $image_id > 0 && ! wp_attachment_is_image( $image_id ) ) {
                wp_die( esc_html__( 'Ảnh đại diện không hợp lệ.', 'aznet-theme' ) );
            }
        }

        $result = wp_update_post( [ 'ID' => $source_id, 'post_title' => $title, 'post_excerpt' => $excerpt ], true );
        if ( is_wp_error( $result ) ) { wp_die( esc_html( $result->get_error_message() ) ); }

        if ( homepage_quick_edit_featured_image_allowed( $preset, $slot, $source_id, $descriptor ) ) {
            if ( $image_id > 0 ) {
                set_post_thumbnail( $source_id, $image_id );
            } else {
                delete_post_thumbnail( $source_id );
            }
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

    $redirect = add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'homepage', 'homepage_quick_edited' => '1' ], admin_url( 'admin.php' ) );
    if ( 'law-01' === $preset && 'team' === $slot ) {
        $redirect .= '#homepage-team';
    }
    wp_safe_redirect( $redirect );
    exit;
}

/** Build one ordinary published child Page payload for a Team member. */
function homepage_team_member_insert_data( \WP_Post $parent, string $name, string $role ): array {
    return [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_parent'  => (int) $parent->ID,
        'post_title'   => $name,
        'post_excerpt' => $role,
        'menu_order'   => \AZnet\Theme\team_directory_next_menu_order(),
    ];
}

/** Create one published WordPress Page child under the exact mapped Team parent. */
function handle_homepage_team_member_create(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thêm nhân sự.', 'aznet-theme' ) );
    }
    check_admin_referer( 'aznet_theme_create_team_member' );

    $parent = \AZnet\Theme\team_directory_parent();
    if ( ! $parent instanceof \WP_Post || ! current_user_can( 'edit_post', $parent->ID ) ) {
        wp_die( esc_html__( 'Page Đội ngũ chưa hợp lệ.', 'aznet-theme' ) );
    }

    $name = isset( $_POST['team_member_name'] )
        ? sanitize_text_field( wp_unslash( $_POST['team_member_name'] ) )
        : '';
    $role = isset( $_POST['team_member_role'] )
        ? sanitize_textarea_field( wp_unslash( $_POST['team_member_role'] ) )
        : '';
    $image_id = isset( $_POST['homepage_featured_image_id'] )
        ? absint( $_POST['homepage_featured_image_id'] )
        : 0;

    if ( '' === $name ) {
        wp_die( esc_html__( 'Tên nhân sự không được để trống.', 'aznet-theme' ) );
    }
    if ( $image_id > 0 && ! wp_attachment_is_image( $image_id ) ) {
        wp_die( esc_html__( 'Ảnh nhân sự không hợp lệ.', 'aznet-theme' ) );
    }

    $id = wp_insert_post(
        homepage_team_member_insert_data( $parent, $name, $role ),
        true
    );
    if ( is_wp_error( $id ) ) {
        wp_die( esc_html( $id->get_error_message() ) );
    }

    if ( $image_id > 0 ) {
        set_post_thumbnail( (int) $id, $image_id );
    }

    $url = add_query_arg(
        [ 'page' => 'aznet-theme', 'section' => 'homepage', 'team_member_created' => '1' ],
        admin_url( 'admin.php' )
    );
    wp_safe_redirect( $url . '#homepage-team' );
    exit;
}


/** Create one WordPress-native draft copy while reusing Media attachment references. */
function homepage_duplicate_native_post( \WP_Post $source, string $title_suffix, int $parent_override = -1 ): int|\WP_Error {
    $new_id = wp_insert_post(
        [
            'post_type'    => $source->post_type,
            'post_status'  => 'draft',
            'post_title'   => $source->post_title . $title_suffix,
            'post_excerpt' => $source->post_excerpt,
            'post_content' => $source->post_content,
            'post_parent'  => $parent_override >= 0 ? $parent_override : (int) $source->post_parent,
            'menu_order'   => (int) $source->menu_order,
        ],
        true
    );
    if ( is_wp_error( $new_id ) ) { return $new_id; }
    $thumbnail_id = (int) get_post_thumbnail_id( $source->ID );
    if ( $thumbnail_id > 0 && wp_attachment_is_image( $thumbnail_id ) ) {
        set_post_thumbnail( (int) $new_id, $thumbnail_id );
    }
    return (int) $new_id;
}

/** Explicitly separate one shared Page/wp_block source into a draft for one preset. */
function handle_homepage_duplicate_source(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thay đổi thiết lập Theme.', 'aznet-theme' ) );
    }
    check_admin_referer( 'aznet_theme_duplicate_homepage_source' );

    $preset = isset( $_POST['homepage_preset_scope'] ) ? sanitize_key( wp_unslash( $_POST['homepage_preset_scope'] ) ) : '';
    $slot = isset( $_POST['homepage_source_slot'] ) ? sanitize_key( wp_unslash( $_POST['homepage_source_slot'] ) ) : '';
    $source_id = isset( $_POST['homepage_source_id'] ) ? absint( $_POST['homepage_source_id'] ) : 0;
    $descriptor = homepage_source_descriptor( $preset, $slot );
    $type = is_array( $descriptor ) ? (string) ( $descriptor['type'] ?? '' ) : '';
    $mapped_id = (int) homepage_source_value( $preset, $slot );

    if ( ! in_array( $preset, [ 'law-01', 'curtain-01' ], true )
        || ! is_array( $descriptor )
        || ! in_array( $type, [ 'page', 'wp_block' ], true )
        || $source_id <= 0
        || $source_id !== $mapped_id
        || [] === \AZnet\Theme\homepage_shared_source_uses( $preset, $slot ) ) {
        wp_die( esc_html__( 'Nguồn dùng chung không còn hợp lệ để tách.', 'aznet-theme' ) );
    }

    $source = get_post( $source_id );
    if ( ! $source instanceof \WP_Post || $source->post_type !== $type || ! current_user_can( 'edit_post', $source_id ) ) {
        wp_die( esc_html__( 'Nguồn WordPress không hợp lệ hoặc bạn không có quyền chỉnh sửa.', 'aznet-theme' ) );
    }

    $preset_label = 'law-01' === $preset ? 'Luật 01' : 'Rèm 01';
    $new_id = homepage_duplicate_native_post( $source, ' — ' . $preset_label );
    if ( is_wp_error( $new_id ) ) { wp_die( esc_html( $new_id->get_error_message() ) ); }

    if ( 'page' === $type && ! empty( $descriptor['children'] ) ) {
        foreach ( \AZnet\Theme\homepage_direct_published_children( $source_id, 24 ) as $child ) {
            if ( ! $child instanceof \WP_Post ) { continue; }
            $child_copy = homepage_duplicate_native_post( $child, ' — ' . $preset_label, (int) $new_id );
            if ( is_wp_error( $child_copy ) ) { wp_die( esc_html( $child_copy->get_error_message() ) ); }
        }
    }

    $key = (string) ( $descriptor['key'] ?? '' );
    if ( '' === $key ) { wp_die( esc_html__( 'Không xác định được mapping của mẫu.', 'aznet-theme' ) ); }
    $theme_settings = \AZnet\Theme\settings();
    $theme_settings[ $key ] = (int) $new_id;
    set_theme_mod( 'aznet_theme_settings', \AZnet\Theme\normalize_settings( $theme_settings ) );

    wp_safe_redirect( add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'homepage', 'homepage_source_separated' => '1' ], admin_url( 'admin.php' ) ) );
    exit;
}

<?php
/** Scoped WordPress Page editor fields for mapped Team member contact facts. */
namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Only mapped Team child Pages expose these existing contact fields. */
function register_team_member_page_metabox( \WP_Post $post ): void {
    if ( ! \AZnet\Theme\team_directory_member_is_child( (int) $post->ID ) ) {
        return;
    }
    add_meta_box(
        'aznet-theme-team-contact',
        __( 'Liên hệ nhân sự', 'aznet-theme' ),
        __NAMESPACE__ . '\\render_team_member_page_metabox',
        'page',
        'normal',
        'default',
        [ '__back_compat_meta_box' => false ]
    );
}

function render_team_member_page_metabox( \WP_Post $post ): void {
    $phone = \AZnet\Theme\team_member_phone( (int) $post->ID );
    $zalo = \AZnet\Theme\team_member_zalo( (int) $post->ID );
    wp_nonce_field( 'aznet_theme_team_member_contact_' . $post->ID, 'aznet_theme_team_member_contact_nonce' );
    echo '<p><label for="aznet-theme-team-member-phone"><strong>' . esc_html__( 'Số điện thoại', 'aznet-theme' ) . '</strong></label><br>';
    echo '<input class="widefat" id="aznet-theme-team-member-phone" type="tel" inputmode="tel" autocomplete="tel" name="aznet_theme_team_member_phone" value="' . esc_attr( $phone ) . '" placeholder="0912.047.054"></p>';
    echo '<p><label for="aznet-theme-team-member-zalo"><strong>' . esc_html__( 'Zalo (số điện thoại)', 'aznet-theme' ) . '</strong></label><br>';
    echo '<input class="widefat" id="aznet-theme-team-member-zalo" type="tel" inputmode="tel" name="aznet_theme_team_member_zalo" value="' . esc_attr( $zalo ) . '" placeholder="0987.472.480"></p>';
    echo '<p class="description">' . esc_html__( 'Đồng bộ với Sửa nhanh nhân sự trong AZnet Theme. Để trống để ẩn liên hệ tương ứng trên website.', 'aznet-theme' ) . '</p>';
}

/** Respect native Page revisions, privileges and nonce rather than creating a parallel store. */
function save_team_member_page_metabox( int $post_id, \WP_Post $post ): void {
    if ( ! \AZnet\Theme\team_directory_member_is_child( $post_id )
        || 'page' !== $post->post_type
        || wp_is_post_revision( $post_id )
        || wp_is_post_autosave( $post_id )
        || ! current_user_can( 'edit_post', $post_id )
        || ! isset( $_POST['aznet_theme_team_member_contact_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aznet_theme_team_member_contact_nonce'] ) ), 'aznet_theme_team_member_contact_' . $post_id )
        || ! isset( $_POST['aznet_theme_team_member_phone'], $_POST['aznet_theme_team_member_zalo'] )
    ) {
        return;
    }

    $phone = sanitize_text_field( wp_unslash( $_POST['aznet_theme_team_member_phone'] ) );
    $zalo = sanitize_text_field( wp_unslash( $_POST['aznet_theme_team_member_zalo'] ) );
    \AZnet\Theme\save_team_member_contact( $post_id, $phone, $zalo );
}

add_action( 'add_meta_boxes_page', __NAMESPACE__ . '\\register_team_member_page_metabox' );
add_action( 'save_post_page', __NAMESPACE__ . '\\save_team_member_page_metabox', 10, 2 );

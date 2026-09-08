<?php
/**
 * Admin editor policy for regular WordPress content.
 *
 * Posts and ordinary Pages use the Classic Editor. The configured static
 * Front Page preserves WordPress' existing Block Editor decision so the
 * native Homepage blueprint remains editable. Other post types are untouched.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Keep the Block Editor only for the configured static Front Page.
 *
 * The incoming decision is preserved for the Front Page and for post types
 * outside post/page so the Theme never force-enables the editor against
 * WordPress or another owner.
 *
 * @param bool     $use_block_editor Current WordPress/provider decision.
 * @param \WP_Post $post             Post being edited.
 * @return bool
 */
function use_classic_editor_for_regular_content( bool $use_block_editor, \WP_Post $post ): bool {
    if ( 'post' === $post->post_type ) {
        return false;
    }

    if ( 'page' !== $post->post_type ) {
        return $use_block_editor;
    }

    $show_on_front = (string) get_option( 'show_on_front', 'posts' );
    $front_page_id = (int) get_option( 'page_on_front', 0 );

    if ( 'page' === $show_on_front && $front_page_id > 0 && $post->ID === $front_page_id ) {
        return $use_block_editor;
    }

    return false;
}

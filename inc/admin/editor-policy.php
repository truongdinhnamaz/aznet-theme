<?php
/**
 * Admin editor policy for native WordPress content.
 *
 * Native Posts and Pages, including the configured static Front Page, use the
 * Classic Editor. Other post types preserve the incoming WordPress/provider
 * editor decision and remain outside Theme ownership.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Use Classic Editor for native Posts and Pages only.
 *
 * @param bool     $use_block_editor Current WordPress/provider decision.
 * @param \WP_Post $post             Post being edited.
 * @return bool
 */
function use_classic_editor_for_regular_content( bool $use_block_editor, \WP_Post $post ): bool {
    if ( in_array( $post->post_type, [ 'post', 'page' ], true ) ) {
        return false;
    }

    return $use_block_editor;
}

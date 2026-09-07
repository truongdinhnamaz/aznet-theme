<?php
/**
 * Title: Content — Authority Quote
 * Slug: aznet-theme/content-authority-quote
 * Categories: aznet-theme-content
 * Description: Replaceable authority quote presentation without claiming identity or evidence ownership.
 */
?>
<!-- wp:group {"align":"wide","className":"aznet-theme-pattern aznet-theme-pattern--content-authority-quote","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section","left":"var:preset|spacing|4","right":"var:preset|spacing|4"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide aznet-theme-pattern aznet-theme-pattern--content-authority-quote" style="padding-top:var(--wp--preset--spacing--section);padding-right:var(--wp--preset--spacing--4);padding-bottom:var(--wp--preset--spacing--section);padding-left:var(--wp--preset--spacing--4)">
<!-- wp:paragraph {"textColor":"primary","fontSize":"sm","className":"aznet-theme-pattern__eyebrow"} -->
<p class="aznet-theme-pattern__eyebrow has-primary-color has-text-color has-sm-font-size"><?php esc_html_e( 'Trích dẫn mẫu', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:quote {"className":"is-style-plain"} -->
<blockquote class="wp-block-quote is-style-plain"><p><?php esc_html_e( '“Thay trích dẫn mẫu này bằng phát biểu thực, có nguồn và đúng người chịu trách nhiệm nội dung.”', 'aznet-theme' ); ?></p><cite><?php esc_html_e( 'Tên và vai trò mẫu', 'aznet-theme' ); ?></cite></blockquote>
<!-- /wp:quote -->
<!-- wp:paragraph {"textColor":"muted","fontSize":"sm"} -->
<p class="has-muted-color has-text-color has-sm-font-size"><?php esc_html_e( 'Gợi ý: kiểm tra nguồn trước khi xuất bản tên, chức danh hoặc tuyên bố chuyên môn.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

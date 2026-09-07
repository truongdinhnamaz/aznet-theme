<?php
/**
 * Title: Content — Alternating Media
 * Slug: aznet-theme/content-alternating
 * Categories: aznet-theme-content
 * Description: Two alternating text and media rows for explaining a topic in clear sections.
 */
?>
<!-- wp:group {"align":"wide","className":"aznet-theme-pattern aznet-theme-pattern--content-alternating","style":{"spacing":{"blockGap":"var:preset|spacing|section","padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide aznet-theme-pattern aznet-theme-pattern--content-alternating" style="padding-top:var(--wp--preset--spacing--section);padding-bottom:var(--wp--preset--spacing--section)">
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} --><div class="wp-block-column is-vertically-aligned-center"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e( 'Phần nội dung đầu tiên', 'aznet-theme' ); ?></h2><!-- /wp:heading --><!-- wp:paragraph --><p><?php esc_html_e( 'Nội dung mẫu — giải thích một ý quan trọng bằng ngôn ngữ ngắn gọn và dễ kiểm chứng.', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center"} --><div class="wp-block-column is-vertically-aligned-center"><!-- wp:group {"backgroundColor":"surface","className":"aznet-theme-pattern__media-placeholder","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group aznet-theme-pattern__media-placeholder has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--section);padding-bottom:var(--wp--preset--spacing--section)"><!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"></figure><!-- /wp:image --></div><!-- /wp:group --></div><!-- /wp:column --></div>
<!-- /wp:columns -->
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} --><div class="wp-block-column is-vertically-aligned-center"><!-- wp:group {"backgroundColor":"surface","className":"aznet-theme-pattern__media-placeholder","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group aznet-theme-pattern__media-placeholder has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--section);padding-bottom:var(--wp--preset--spacing--section)"><!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"></figure><!-- /wp:image --></div><!-- /wp:group --></div><!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center"} --><div class="wp-block-column is-vertically-aligned-center"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e( 'Phần nội dung tiếp theo', 'aznet-theme' ); ?></h2><!-- /wp:heading --><!-- wp:paragraph --><p><?php esc_html_e( 'Nội dung mẫu — bổ sung ý thứ hai mà không biến bố cục thành một trình dựng trang riêng.', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

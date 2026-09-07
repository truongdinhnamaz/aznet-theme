<?php
/**
 * Title: Content — Article Grid
 * Slug: aznet-theme/content-article-grid
 * Categories: aznet-theme-content
 * Description: Native Query block grid that leaves WordPress in control of content query semantics.
 */
?>
<!-- wp:group {"align":"wide","className":"aznet-theme-pattern aznet-theme-pattern--content-article-grid","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide aznet-theme-pattern aznet-theme-pattern--content-article-grid" style="padding-top:var(--wp--preset--spacing--section);padding-bottom:var(--wp--preset--spacing--section)">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Bài viết mới', 'aznet-theme' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:query {"queryId":0,"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"flex","columns":3}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:group {"className":"aznet-theme-pattern__article-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group aznet-theme-pattern__article-card"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"lg"} /-->
<!-- wp:post-date {"textColor":"muted","fontSize":"sm"} /-->
<!-- wp:post-excerpt {"moreText":""} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->
<!-- wp:query-no-results -->
<!-- wp:paragraph --><p><?php esc_html_e( 'Chưa có bài viết phù hợp để hiển thị.', 'aznet-theme' ); ?></p><!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->

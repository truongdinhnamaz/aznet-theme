<?php
/**
 * Title: Hero — Split
 * Slug: aznet-theme/hero-split
 * Categories: aznet-theme-hero
 * Description: Two-column hero with text-first content and a replaceable media placeholder.
 */
?>
<!-- wp:group {"align":"full","className":"aznet-theme-pattern aznet-theme-pattern--hero-split","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section","left":"var:preset|spacing|4","right":"var:preset|spacing|4"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull aznet-theme-pattern aznet-theme-pattern--hero-split" style="padding-top:var(--wp--preset--spacing--section);padding-right:var(--wp--preset--spacing--4);padding-bottom:var(--wp--preset--spacing--section);padding-left:var(--wp--preset--spacing--4)">
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"textColor":"primary","fontSize":"sm","className":"aznet-theme-pattern__eyebrow"} -->
<p class="aznet-theme-pattern__eyebrow has-primary-color has-text-color has-sm-font-size"><?php esc_html_e( 'Một thông điệp rõ ràng', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Đặt giá trị cốt lõi cạnh hình ảnh nổi bật', 'aznet-theme' ); ?></h1>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Nội dung mẫu ngắn gọn giúp người đọc hiểu điều quan trọng trước khi đi sâu vào trang.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"primary"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button"><?php esc_html_e( 'Khám phá thêm', 'aznet-theme' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:group {"backgroundColor":"surface","className":"aznet-theme-pattern__media-placeholder","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section","left":"var:preset|spacing|4","right":"var:preset|spacing|4"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group aznet-theme-pattern__media-placeholder has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--section);padding-right:var(--wp--preset--spacing--4);padding-bottom:var(--wp--preset--spacing--section);padding-left:var(--wp--preset--spacing--4)"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"></figure>
<!-- /wp:image --><!-- wp:paragraph {"align":"center","textColor":"muted","fontSize":"sm"} -->
<p class="has-text-align-center has-muted-color has-text-color has-sm-font-size"><?php esc_html_e( 'Chọn hình ảnh của bạn trong trình biên tập.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

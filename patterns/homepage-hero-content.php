<?php
/**
 * Title: Homepage Hero — Editable Content
 * Slug: aznet-theme/homepage-hero-content
 * Categories: aznet-theme-hero
 * Description: Portable Core-block content scaffold for the Homepage Hero Library.
 */
?>
<!-- wp:group {"className":"aznet-theme-homepage-hero-content","lock":{"move":true,"remove":true},"layout":{"type":"constrained"}} -->
<div class="wp-block-group aznet-theme-homepage-hero-content">
<!-- wp:columns {"verticalAlignment":"center","className":"aznet-theme-homepage-hero-content__layout","lock":{"move":true,"remove":true}} -->
<div class="wp-block-columns are-vertically-aligned-center aznet-theme-homepage-hero-content__layout">
<!-- wp:column {"verticalAlignment":"center","className":"aznet-theme-homepage-hero-content__copy","lock":{"move":true,"remove":true}} -->
<div class="wp-block-column is-vertically-aligned-center aznet-theme-homepage-hero-content__copy">
<!-- wp:paragraph {"className":"aznet-theme-homepage-hero-content__eyebrow","lock":{"move":true,"remove":true}} -->
<p class="aznet-theme-homepage-hero-content__eyebrow"><?php esc_html_e( 'Thông điệp mở đầu', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1,"className":"aznet-theme-homepage-hero-content__title","lock":{"move":true,"remove":true}} -->
<h1 class="wp-block-heading aznet-theme-homepage-hero-content__title"><?php esc_html_e( 'Viết tiêu đề Hero của bạn tại đây', 'aznet-theme' ); ?></h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"aznet-theme-homepage-hero-content__lead","lock":{"move":true,"remove":true}} -->
<p class="aznet-theme-homepage-hero-content__lead"><?php esc_html_e( 'Mô tả ngắn giá trị chính. Nội dung này thuộc WordPress và có thể sửa bằng trình biên tập block.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"className":"aznet-theme-homepage-hero-content__actions","lock":{"move":true,"remove":true}} -->
<div class="wp-block-buttons aznet-theme-homepage-hero-content__actions">
<!-- wp:button {"backgroundColor":"primary","lock":{"move":true,"remove":true}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button"><?php esc_html_e( 'Hành động chính', 'aznet-theme' ); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center","className":"aznet-theme-homepage-hero-content__media","lock":{"move":true,"remove":true}} -->
<div class="wp-block-column is-vertically-aligned-center aznet-theme-homepage-hero-content__media">
<!-- wp:paragraph {"align":"center","className":"aznet-theme-homepage-hero-content__media-help","fontSize":"sm"} -->
<p class="has-text-align-center aznet-theme-homepage-hero-content__media-help has-sm-font-size"><?php esc_html_e( 'Chèn block Ảnh vào cột này để chọn ảnh Hero.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

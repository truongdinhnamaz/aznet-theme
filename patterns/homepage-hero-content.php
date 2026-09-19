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
<!-- wp:paragraph {"className":"aznet-theme-homepage-hero-content__value","lock":{"move":true,"remove":true}} -->
<p class="aznet-theme-homepage-hero-content__value"><?php esc_html_e( 'Viết dòng giá trị chính của Hero.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"aznet-theme-homepage-hero-content__lead","lock":{"move":true,"remove":true}} -->
<p class="aznet-theme-homepage-hero-content__lead"><?php esc_html_e( 'Viết thông điệp hoặc khẩu hiệu hỗ trợ. Toàn bộ nội dung Hero này thuộc WordPress và sửa trực tiếp bằng trình biên tập block.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"className":"aznet-theme-homepage-hero-content__actions","lock":{"move":true,"remove":true}} -->
<div class="wp-block-buttons aznet-theme-homepage-hero-content__actions">
<!-- wp:button {"backgroundColor":"primary","lock":{"move":true,"remove":true}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?></a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline aznet-theme-homepage-hero-content__secondary","lock":{"move":true,"remove":true}} -->
<div class="wp-block-button is-style-outline aznet-theme-homepage-hero-content__secondary"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Xem dịch vụ', 'aznet-theme' ); ?></a></div>
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
<!-- wp:group {"className":"aznet-theme-homepage-hero-content__trust","lock":{"move":true,"remove":true},"layout":{"type":"constrained"}} -->
<div class="wp-block-group aznet-theme-homepage-hero-content__trust">
<!-- wp:columns {"className":"aznet-theme-homepage-hero-content__trust-grid","lock":{"move":true,"remove":true}} -->
<div class="wp-block-columns aznet-theme-homepage-hero-content__trust-grid">
<!-- wp:column {"lock":{"move":true,"remove":true}} -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"aznet-theme-homepage-hero-content__trust-item"} --><p class="aznet-theme-homepage-hero-content__trust-item"><?php esc_html_e( 'Tư vấn rõ ràng', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column {"lock":{"move":true,"remove":true}} -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"aznet-theme-homepage-hero-content__trust-item"} --><p class="aznet-theme-homepage-hero-content__trust-item"><?php esc_html_e( 'Giải pháp thực tiễn', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column {"lock":{"move":true,"remove":true}} -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"aznet-theme-homepage-hero-content__trust-item"} --><p class="aznet-theme-homepage-hero-content__trust-item"><?php esc_html_e( 'Bảo mật thông tin', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column {"lock":{"move":true,"remove":true}} -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"aznet-theme-homepage-hero-content__trust-item"} --><p class="aznet-theme-homepage-hero-content__trust-item"><?php esc_html_e( 'Đồng hành tận tâm', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<?php
/**
 * Title: Hero — Centered
 * Slug: aznet-theme/hero-centered
 * Categories: aznet-theme-hero
 * Description: Centered hero with eyebrow, heading, supporting copy and primary action.
 */
?>
<!-- wp:group {"align":"full","className":"aznet-theme-pattern aznet-theme-pattern--hero-centered","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section","left":"var:preset|spacing|4","right":"var:preset|spacing|4"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull aznet-theme-pattern aznet-theme-pattern--hero-centered" style="padding-top:var(--wp--preset--spacing--section);padding-right:var(--wp--preset--spacing--4);padding-bottom:var(--wp--preset--spacing--section);padding-left:var(--wp--preset--spacing--4)">
<!-- wp:paragraph {"align":"center","textColor":"primary","fontSize":"sm","className":"aznet-theme-pattern__eyebrow"} -->
<p class="has-text-align-center aznet-theme-pattern__eyebrow has-primary-color has-text-color has-sm-font-size"><?php esc_html_e( 'Giải pháp WordPress từ nền tảng sạch', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Xây website nhanh mà không khóa nội dung vào builder riêng', 'aznet-theme' ); ?></h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php esc_html_e( 'Thay nội dung mẫu này bằng thông điệp, giá trị và lời hứa thương hiệu của bạn.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"primary"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button"><?php esc_html_e( 'Hành động chính', 'aznet-theme' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

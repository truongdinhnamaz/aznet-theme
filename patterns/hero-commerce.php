<?php
/**
 * Title: Hero — Commerce
 * Slug: aznet-theme/hero-commerce
 * Categories: aznet-theme-hero
 * Description: Core-block commerce hero that remains useful when WooCommerce is absent.
 */
?>
<!-- wp:group {"align":"full","className":"aznet-theme-pattern aznet-theme-pattern--hero-commerce","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section","left":"var:preset|spacing|4","right":"var:preset|spacing|4"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull aznet-theme-pattern aznet-theme-pattern--hero-commerce" style="padding-top:var(--wp--preset--spacing--section);padding-right:var(--wp--preset--spacing--4);padding-bottom:var(--wp--preset--spacing--section);padding-left:var(--wp--preset--spacing--4)">
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"60%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:60%"><!-- wp:paragraph {"textColor":"primary","fontSize":"sm","className":"aznet-theme-pattern__eyebrow"} -->
<p class="aznet-theme-pattern__eyebrow has-primary-color has-text-color has-sm-font-size"><?php esc_html_e( 'Bộ sưu tập nổi bật', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Giới thiệu nhóm sản phẩm bằng thông điệp dễ hiểu', 'aznet-theme' ); ?></h1>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Hero này chỉ dùng block lõi nên vẫn hiển thị bình thường khi WooCommerce chưa được cài đặt.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"primary"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button"><?php esc_html_e( 'Xem bộ sưu tập', 'aznet-theme' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%"><!-- wp:group {"backgroundColor":"surface","className":"aznet-theme-pattern__commerce-note","style":{"spacing":{"padding":{"top":"var:preset|spacing|6","bottom":"var:preset|spacing|6","left":"var:preset|spacing|4","right":"var:preset|spacing|4"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group aznet-theme-pattern__commerce-note has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--6);padding-right:var(--wp--preset--spacing--4);padding-bottom:var(--wp--preset--spacing--6);padding-left:var(--wp--preset--spacing--4)"><!-- wp:heading {"level":2,"fontSize":"lg"} -->
<h2 class="wp-block-heading has-lg-font-size"><?php esc_html_e( 'Điểm nổi bật', 'aznet-theme' ); ?></h2>
<!-- /wp:heading --><!-- wp:list -->
<ul class="wp-block-list"><li><?php esc_html_e( 'Thay bằng lợi ích thực tế', 'aznet-theme' ); ?></li><li><?php esc_html_e( 'Thay bằng điểm khác biệt', 'aznet-theme' ); ?></li><li><?php esc_html_e( 'Thay bằng cam kết phù hợp', 'aznet-theme' ); ?></li></ul>
<!-- /wp:list --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<?php
/**
 * Title: Utility — Contact
 * Slug: aznet-theme/utility-contact
 * Categories: aznet-theme-utility
 * Description: Replaceable contact-information section without storing contact data in Theme settings.
 */
?>
<!-- wp:group {"align":"wide","className":"aznet-theme-pattern aznet-theme-pattern--utility-contact","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide aznet-theme-pattern aznet-theme-pattern--utility-contact" style="padding-top:var(--wp--preset--spacing--section);padding-bottom:var(--wp--preset--spacing--section)">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e( 'Liên hệ', 'aznet-theme' ); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e( 'Thông tin liên hệ mẫu — thay trực tiếp bằng dữ liệu do website của bạn sở hữu.', 'aznet-theme' ); ?></p><!-- /wp:paragraph -->
<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3,"fontSize":"lg"} --><h3 class="wp-block-heading has-lg-font-size"><?php esc_html_e( 'Địa chỉ mẫu', 'aznet-theme' ); ?></h3><!-- /wp:heading --><!-- wp:paragraph --><p><?php esc_html_e( 'Số nhà, đường, khu vực', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3,"fontSize":"lg"} --><h3 class="wp-block-heading has-lg-font-size"><?php esc_html_e( 'Điện thoại mẫu', 'aznet-theme' ); ?></h3><!-- /wp:heading --><!-- wp:paragraph --><p><?php esc_html_e( '0000 000 000', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3,"fontSize":"lg"} --><h3 class="wp-block-heading has-lg-font-size"><?php esc_html_e( 'Email mẫu', 'aznet-theme' ); ?></h3><!-- /wp:heading --><!-- wp:paragraph --><p><?php esc_html_e( 'ten@vi-du.test', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:column --></div><!-- /wp:columns --></div>
<!-- /wp:group -->

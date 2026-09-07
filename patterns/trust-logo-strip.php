<?php
/**
 * Title: Trust — Logo Strip
 * Slug: aznet-theme/trust-logo-strip
 * Categories: aznet-theme-trust
 * Description: Replaceable logo-name strip for displaying verified partners or clients supplied by the site owner.
 */
?>
<!-- wp:group {"align":"full","className":"aznet-theme-pattern aznet-theme-pattern--trust-logo-strip","style":{"spacing":{"padding":{"top":"var:preset|spacing|6","bottom":"var:preset|spacing|6","left":"var:preset|spacing|4","right":"var:preset|spacing|4"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull aznet-theme-pattern aznet-theme-pattern--trust-logo-strip" style="padding-top:var(--wp--preset--spacing--6);padding-right:var(--wp--preset--spacing--4);padding-bottom:var(--wp--preset--spacing--6);padding-left:var(--wp--preset--spacing--4)">
<!-- wp:paragraph {"align":"center","textColor":"muted","fontSize":"sm"} -->
<p class="has-text-align-center has-muted-color has-text-color has-sm-font-size"><?php esc_html_e( 'Tên/logo mẫu — chỉ thay bằng đối tác hoặc khách hàng đã được bạn xác thực.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><?php esc_html_e( 'LOGO MẪU 01', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><?php esc_html_e( 'LOGO MẪU 02', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><?php esc_html_e( 'LOGO MẪU 03', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><?php esc_html_e( 'LOGO MẪU 04', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

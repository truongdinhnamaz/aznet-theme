<?php
/**
 * Title: Utility — Footer CTA
 * Slug: aznet-theme/utility-footer-cta
 * Categories: aznet-theme-utility
 * Description: Pre-footer call to action using replaceable copy and native blocks only.
 */
?>
<!-- wp:group {"align":"full","backgroundColor":"text","textColor":"surface","className":"aznet-theme-pattern aznet-theme-pattern--utility-footer-cta","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section","left":"var:preset|spacing|4","right":"var:preset|spacing|4"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull aznet-theme-pattern aznet-theme-pattern--utility-footer-cta has-surface-color has-text-color has-text-background-color has-background" style="padding-top:var(--wp--preset--spacing--section);padding-right:var(--wp--preset--spacing--4);padding-bottom:var(--wp--preset--spacing--section);padding-left:var(--wp--preset--spacing--4)">
<!-- wp:heading {"textAlign":"center","level":2,"textColor":"surface"} --><h2 class="wp-block-heading has-text-align-center has-surface-color has-text-color"><?php esc_html_e( 'CTA cuối trang mẫu', 'aznet-theme' ); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><?php esc_html_e( 'Thay bằng bước tiếp theo phù hợp trước khi người đọc đến Footer.', 'aznet-theme' ); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"surface","textColor":"text"} --><div class="wp-block-button"><a class="wp-block-button__link has-text-color has-surface-background-color has-background wp-element-button"><?php esc_html_e( 'Hành động mẫu', 'aznet-theme' ); ?></a></div><!-- /wp:button --></div><!-- /wp:buttons --></div>
<!-- /wp:group -->

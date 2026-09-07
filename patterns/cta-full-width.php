<?php
/**
 * Title: CTA — Full Width
 * Slug: aznet-theme/cta-full-width
 * Categories: aznet-theme-trust
 * Description: Full-width call to action with replaceable example message and primary action.
 */
?>
<!-- wp:group {"align":"full","backgroundColor":"primary","textColor":"surface","className":"aznet-theme-pattern aznet-theme-pattern--cta-full-width","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section","left":"var:preset|spacing|4","right":"var:preset|spacing|4"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull aznet-theme-pattern aznet-theme-pattern--cta-full-width has-surface-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--section);padding-right:var(--wp--preset--spacing--4);padding-bottom:var(--wp--preset--spacing--section);padding-left:var(--wp--preset--spacing--4)">
<!-- wp:paragraph {"align":"center","fontSize":"sm"} -->
<p class="has-text-align-center has-sm-font-size"><?php esc_html_e( 'CTA mẫu — thay bằng lời mời phù hợp với mục tiêu trang.', 'aznet-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"textAlign":"center","level":2,"textColor":"surface"} -->
<h2 class="wp-block-heading has-text-align-center has-surface-color has-text-color"><?php esc_html_e( 'Giúp người đọc biết bước tiếp theo là gì', 'aznet-theme' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"surface","textColor":"text"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-surface-background-color has-background wp-element-button"><?php esc_html_e( 'Hành động mẫu', 'aznet-theme' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

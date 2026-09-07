<?php
/**
 * Woo-dependent category-grid pattern content.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

return <<<'HTML'
<!-- wp:group {"align":"wide","className":"aznet-theme-pattern aznet-theme-pattern--commerce-category-grid","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide aznet-theme-pattern aznet-theme-pattern--commerce-category-grid" style="padding-top:var(--wp--preset--spacing--section);padding-bottom:var(--wp--preset--spacing--section)">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Danh mục sản phẩm</h2><!-- /wp:heading -->
<!-- wp:woocommerce/product-categories {"hasCount":true,"hasImage":true,"isDropdown":false} /-->
</div>
<!-- /wp:group -->
HTML;

<?php
/**
 * Woo-dependent featured-products pattern content.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

return <<<'HTML'
<!-- wp:group {"align":"wide","className":"aznet-theme-pattern aznet-theme-pattern--commerce-featured-products","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide aznet-theme-pattern aznet-theme-pattern--commerce-featured-products" style="padding-top:var(--wp--preset--spacing--section);padding-bottom:var(--wp--preset--spacing--section)">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Sản phẩm nổi bật</h2><!-- /wp:heading -->
<!-- wp:woocommerce/product-collection {"queryId":1,"query":{"perPage":4,"pages":0,"offset":0,"postType":"product","order":"desc","orderBy":"date","search":"","exclude":[],"inherit":false},"displayLayout":{"type":"flex","columns":4},"collection":"woocommerce/product-collection/featured"} -->
<div class="wp-block-woocommerce-product-collection"><!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"isDescendentOfQueryLoop":true} /-->
<!-- wp:woocommerce/product-title {"level":3,"isLink":true} /-->
<!-- wp:woocommerce/product-price /-->
<!-- wp:woocommerce/product-button /-->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection -->
</div>
<!-- /wp:group -->
HTML;

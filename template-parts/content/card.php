<?php
/**
 * Generic WordPress listing card.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-card' ); ?>>
    <h2 class="aznet-theme-card__title">
        <a class="aznet-theme-card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>

    <div class="aznet-theme-card__excerpt">
        <?php the_excerpt(); ?>
    </div>
</article>

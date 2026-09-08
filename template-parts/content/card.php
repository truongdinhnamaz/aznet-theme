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
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="aznet-theme-card__media">
            <?php the_post_thumbnail( 'large', array( 'class' => 'aznet-theme-card__thumbnail' ) ); ?>
        </div>
    <?php endif; ?>

    <div class="aznet-theme-card__meta">
        <time class="aznet-theme-card__date" datetime="<?php echo esc_attr( get_the_time( DATE_W3C ) ); ?>">
            <?php echo esc_html( get_the_date() ); ?>
        </time>
    </div>

    <h2 class="aznet-theme-card__title">
        <a class="aznet-theme-card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>

    <div class="aznet-theme-card__excerpt">
        <?php the_excerpt(); ?>
    </div>
</article>

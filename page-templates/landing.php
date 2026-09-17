<?php
/**
 * Template Name: Landing
 * Template Post Type: page
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="aznet-theme-main">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : ?>
            <?php the_post(); ?>
            <?php get_template_part( 'template-parts/content/page' ); ?>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php esc_html_e( 'No content found.', 'aznet-theme' ); ?></p>
    <?php endif; ?>
</main>
<?php
get_footer();

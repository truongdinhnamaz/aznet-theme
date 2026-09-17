<?php
/**
 * Template Name: Wide
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
    <?php while ( have_posts() ) : the_post(); ?>
        <?php get_template_part( 'template-parts/content/page' ); ?>
    <?php endwhile; ?>
</main>
<?php
get_footer();

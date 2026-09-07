<?php
/**
 * Native WordPress front-page site shell.
 *
 * The WordPress content boundary is intentionally preserved so a public
 * content filter may replace only the Page body while the Theme continues
 * to own the template, Header, Footer, and global presentation shell.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="aznet-theme-main aznet-theme-main--front-page">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : ?>
            <?php the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-front-page' ); ?>>
                <div class="aznet-theme-front-page__content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php esc_html_e( 'No content found.', 'aznet-theme' ); ?></p>
    <?php endif; ?>
</main>
<?php
get_footer();

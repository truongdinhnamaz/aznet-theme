<?php
/**
 * Native WordPress front page template.
 *
 * The Theme owns the site shell and renders the public WordPress Page body
 * through the normal content boundary. Domain providers may filter that body
 * through WordPress; this template does not own or infer provider semantics.
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
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-entry aznet-theme-entry--page aznet-theme-entry--front-page' ); ?>>
                <div class="aznet-theme-entry__content">
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

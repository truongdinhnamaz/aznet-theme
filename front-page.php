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
            <?php if ( function_exists( 'AZnet\\Theme\\homepage_composer_active' ) && \AZnet\Theme\homepage_composer_active() ) : ?>
                <?php \AZnet\Theme\homepage_ledger_reset(); ?>
                <?php \AZnet\Theme\render_homepage_before_content(); ?>
            <?php endif; ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-entry aznet-theme-entry--page aznet-theme-entry--front-page' ); ?>>
                <div class="aznet-theme-entry__content">
                    <?php the_content(); ?>
                </div>
            </article>
            <?php if ( function_exists( 'AZnet\\Theme\\homepage_composer_active' ) && \AZnet\Theme\homepage_composer_active() ) : ?>
                <?php \AZnet\Theme\render_homepage_after_content(); ?>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php esc_html_e( 'No content found.', 'aznet-theme' ); ?></p>
    <?php endif; ?>
</main>
<?php
get_footer();

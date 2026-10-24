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

$aznet_native_posts_index = function_exists( 'is_home' ) && is_home();

get_header();
?>
<main id="main" class="aznet-theme-main <?php echo $aznet_native_posts_index ? 'aznet-theme-main--listing aznet-theme-main--posts-page' : 'aznet-theme-main--front-page'; ?>">
    <?php if ( $aznet_native_posts_index ) : ?>
        <div class="aznet-theme-content-shell">
            <section class="aznet-theme-listing" aria-labelledby="aznet-theme-posts-page-title">
                <header class="aznet-theme-listing__header">
                    <h1 id="aznet-theme-posts-page-title" class="aznet-theme-listing__title"><?php esc_html_e( 'Bài viết', 'aznet-theme' ); ?></h1>
                </header>
                <?php if ( have_posts() ) : ?>
                    <div class="aznet-theme-listing__grid">
                        <?php while ( have_posts() ) : ?>
                            <?php the_post(); ?>
                            <?php get_template_part( 'template-parts/content/card' ); ?>
                        <?php endwhile; ?>
                    </div>
                    <div class="aznet-theme-listing__pagination">
                        <?php the_posts_pagination(); ?>
                    </div>
                <?php else : ?>
                    <p class="aznet-theme-listing__empty"><?php esc_html_e( 'No content found.', 'aznet-theme' ); ?></p>
                <?php endif; ?>
            </section>
        </div>
    <?php else : ?>
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : ?>
            <?php the_post(); ?>
            <?php $aznet_homepage_composer_active = function_exists( 'AZnet\\Theme\\homepage_composer_active' ) && \AZnet\Theme\homepage_composer_active(); ?>
            <?php if ( $aznet_homepage_composer_active ) : ?>
                <?php \AZnet\Theme\homepage_ledger_reset(); ?>
                <?php \AZnet\Theme\render_homepage_before_content(); ?>
            <?php endif; ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-entry aznet-theme-entry--page aznet-theme-entry--front-page' ); ?><?php echo $aznet_homepage_composer_active ? ' data-aznet-homepage-surface="front-page-content"' : ''; ?>>
                <div class="aznet-theme-entry__content">
                    <?php the_content(); ?>
                </div>
            </article>
            <?php if ( $aznet_homepage_composer_active ) : ?>
                <?php \AZnet\Theme\render_homepage_after_content(); ?>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php esc_html_e( 'No content found.', 'aznet-theme' ); ?></p>
    <?php endif; ?>
    <?php endif; ?>
</main>
<?php
get_footer();

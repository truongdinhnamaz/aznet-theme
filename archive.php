<?php
/**
 * Native WordPress Archive template.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$shell_classes = \AZnet\Theme\content_shell_classes( false );
?>
<main id="main" class="aznet-theme-main aznet-theme-main--listing">
    <div class="<?php echo esc_attr( implode( ' ', $shell_classes ) ); ?>">
        <section class="aznet-theme-listing" aria-labelledby="aznet-theme-archive-title">
            <header class="aznet-theme-listing__header">
                <h1 id="aznet-theme-archive-title" class="aznet-theme-listing__title"><?php the_archive_title(); ?></h1>
                <?php the_archive_description( '<div class="aznet-theme-listing__description">', '</div>' ); ?>
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
</main>
<?php
get_footer();

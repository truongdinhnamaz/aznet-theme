<?php
/**
 * Native WordPress Search template.
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
        <section class="aznet-theme-listing" aria-labelledby="aznet-theme-search-title">
            <header class="aznet-theme-listing__header">
                <h1 id="aznet-theme-search-title" class="aznet-theme-listing__title">
                    <?php printf( esc_html__( 'Search results for: %s', 'aznet-theme' ), esc_html( get_search_query() ) ); ?>
                </h1>
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
                <div class="aznet-theme-listing__empty">
                    <p><?php esc_html_e( 'No results found.', 'aznet-theme' ); ?></p>
                    <?php get_search_form(); ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php
get_footer();

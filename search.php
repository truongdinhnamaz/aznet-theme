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
            <header class="aznet-theme-listing__header aznet-theme-search-header">
                <p class="aznet-theme-listing__eyebrow"><?php esc_html_e( 'Search', 'aznet-theme' ); ?></p>
                <h1 id="aznet-theme-search-title" class="aznet-theme-listing__title">
                    <?php
                    printf(
                        esc_html__( 'Search results for “%s”', 'aznet-theme' ),
                        esc_html( get_search_query() )
                    );
                    ?>
                </h1>
                <div class="aznet-theme-search-header__form">
                    <?php get_search_form(); ?>
                </div>
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
                <div class="aznet-theme-recovery aznet-theme-recovery--search" role="status">
                    <h2 class="aznet-theme-recovery__title"><?php esc_html_e( 'No matching results found', 'aznet-theme' ); ?></h2>
                    <p class="aznet-theme-recovery__description">
                        <?php esc_html_e( 'Try a shorter phrase or different keywords.', 'aznet-theme' ); ?>
                    </p>
                    <div class="aznet-theme-recovery__search">
                        <?php get_search_form(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php
get_footer();

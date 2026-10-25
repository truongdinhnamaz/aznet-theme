<?php
/**
 * Native WordPress posts page template.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

if ( function_exists( 'AZnet\\Theme\\law01_posts_page_active' ) && \AZnet\Theme\law01_posts_page_active() ) {
    ?>
    <main id="main" class="aznet-theme-main aznet-theme-main--listing aznet-theme-main--law01-posts">
        <div class="aznet-theme-content-shell">
            <?php get_template_part( 'template-parts/archive/law01-posts-page' ); ?>
        </div>
    </main>
    <?php
    get_footer();
    return;
}

$shell_classes = \AZnet\Theme\content_shell_classes( false );
$posts_page_title = single_post_title( '', false );
if ( '' === trim( (string) $posts_page_title ) ) {
    $posts_page_title = __( 'Bài viết', 'aznet-theme' );
}
?>
<main id="main" class="aznet-theme-main aznet-theme-main--listing aznet-theme-main--posts-page">
    <div class="<?php echo esc_attr( implode( ' ', $shell_classes ) ); ?>">
        <section class="aznet-theme-listing" aria-labelledby="aznet-theme-posts-page-title">
            <header class="aznet-theme-listing__header">
                <h1 id="aznet-theme-posts-page-title" class="aznet-theme-listing__title"><?php echo esc_html( $posts_page_title ); ?></h1>
            </header>

            <?php if ( have_posts() ) : ?>
                <div class="aznet-theme-listing__grid">
                    <?php while ( have_posts() ) : ?>
                        <?php the_post(); ?>
                        <?php get_template_part( 'template-parts/content/card' ); ?>
                    <?php endwhile; ?>
                </div>

                <div class="aznet-theme-listing__pagination">
                    <?php
                    the_posts_pagination(
                        [
                            'mid_size'   => 1,
                            'prev_text'  => esc_html__( 'Previous', 'aznet-theme' ),
                            'next_text'  => esc_html__( 'Next', 'aznet-theme' ),
                            'aria_label' => esc_attr__( 'Posts pagination', 'aznet-theme' ),
                        ]
                    );
                    ?>
                </div>
            <?php else : ?>
                <div class="aznet-theme-recovery aznet-theme-recovery--empty" role="status">
                    <h2 class="aznet-theme-recovery__title"><?php esc_html_e( 'No content is available here yet', 'aznet-theme' ); ?></h2>
                    <p class="aznet-theme-recovery__description"><?php esc_html_e( 'You can return to the homepage and continue browsing from there.', 'aznet-theme' ); ?></p>
                    <div class="aznet-theme-recovery__actions">
                        <a class="aznet-theme-recovery__action" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <?php esc_html_e( 'Back to home', 'aznet-theme' ); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php
get_footer();

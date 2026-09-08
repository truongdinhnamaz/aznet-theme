<?php
/**
 * Single Post editorial presentation.
 *
 * WordPress remains the owner of Post, author, taxonomy and media state.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-entry aznet-theme-entry--post aznet-theme-article' ); ?>>
    <header class="aznet-theme-entry__header aznet-theme-article__header">
        <?php if ( has_category() ) : ?>
            <div class="aznet-theme-article__categories" aria-label="<?php esc_attr_e( 'Categories', 'aznet-theme' ); ?>">
                <?php the_category( ' ' ); ?>
            </div>
        <?php endif; ?>

        <h1 class="aznet-theme-entry__title aznet-theme-article__title"><?php the_title(); ?></h1>
        <?php get_template_part( 'template-parts/content/meta' ); ?>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure class="aznet-theme-article__featured-media">
            <?php the_post_thumbnail( 'large', [ 'class' => 'aznet-theme-article__featured-image' ] ); ?>
        </figure>
    <?php endif; ?>

    <div class="aznet-theme-entry__content aznet-theme-article__content">
        <?php the_content(); ?>
        <?php
        wp_link_pages(
            [
                'before' => '<nav class="aznet-theme-article__page-links" aria-label="' . esc_attr__( 'Article pages', 'aznet-theme' ) . '">',
                'after'  => '</nav>',
            ]
        );
        ?>
    </div>

    <?php if ( has_tag() ) : ?>
        <footer class="aznet-theme-article__taxonomy">
            <?php the_tags( '<div class="aznet-theme-article__tags"><span class="aznet-theme-article__taxonomy-label">' . esc_html__( 'Tags:', 'aznet-theme' ) . '</span> ', ' ', '</div>' ); ?>
        </footer>
    <?php endif; ?>

    <?php
    the_post_navigation(
        [
            'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous', 'aznet-theme' ) . '</span><span class="nav-title">%title</span>',
            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next', 'aznet-theme' ) . '</span><span class="nav-title">%title</span>',
        ]
    );
    ?>

    <?php get_template_part( 'template-parts/content/author' ); ?>
</article>

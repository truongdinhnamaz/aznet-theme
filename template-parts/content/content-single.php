<?php
/**
 * Single Post editorial presentation.
 *
 * WordPress remains the owner of Post, author, taxonomy and media state.
 * ConvertFlow owns any table-of-contents behavior; the Theme does not parse
 * headings or reconstruct TOC state.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$law01_article = function_exists( 'AZnet\\Theme\\header_law01_active' )
    && \AZnet\Theme\header_law01_active();
$article_classes = 'aznet-theme-entry aznet-theme-entry--post aznet-theme-article';
if ( $law01_article ) {
    $article_classes .= ' aznet-theme-article--law01';
}

$dek = has_excerpt() ? trim( (string) get_the_excerpt() ) : '';
$has_article_navigation = is_active_sidebar( 'article-navigation' );
$reading_layout_class = 'aznet-theme-article__reading-layout';
if ( $has_article_navigation ) {
    $reading_layout_class .= ' aznet-theme-article__reading-layout--with-navigation';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $article_classes ); ?>>
    <header class="aznet-theme-entry__header aznet-theme-article__header">
        <?php if ( has_category() ) : ?>
            <nav class="aznet-theme-article__categories" aria-label="<?php esc_attr_e( 'Categories', 'aznet-theme' ); ?>">
                <?php the_category( ' ' ); ?>
            </nav>
        <?php endif; ?>

        <h1 class="aznet-theme-entry__title aznet-theme-article__title"><?php the_title(); ?></h1>

        <?php if ( '' !== $dek ) : ?>
            <p class="aznet-theme-article__dek"><?php echo esc_html( $dek ); ?></p>
        <?php endif; ?>

        <?php get_template_part( 'template-parts/content/meta' ); ?>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure class="aznet-theme-article__featured-media">
            <?php the_post_thumbnail( 'large', [ 'class' => 'aznet-theme-article__featured-image' ] ); ?>
        </figure>
    <?php endif; ?>

    <div class="<?php echo esc_attr( $reading_layout_class ); ?>">
        <div class="aznet-theme-article__reading-main">
            <div class="aznet-theme-entry__content aznet-theme-article__content">
                <?php the_content(); ?>
                <?php
                wp_link_pages(
                    [
                        'before' => '<nav class="aznet-theme-article__page-links" aria-label="' . esc_attr__( 'Article pages', 'aznet-theme' ) . '"><span class="aznet-theme-navigation__summary">' . esc_html__( 'Pages:', 'aznet-theme' ) . '</span>',
                        'after'  => '</nav>',
                    ]
                );
                ?>
            </div>
        </div>

        <?php if ( $has_article_navigation ) : ?>
            <aside class="aznet-theme-article__navigation-sidebar" aria-label="<?php esc_attr_e( 'Điều hướng nội dung bài viết', 'aznet-theme' ); ?>">
                <?php dynamic_sidebar( 'article-navigation' ); ?>
            </aside>
        <?php endif; ?>
    </div>

    <?php if ( has_tag() ) : ?>
        <footer class="aznet-theme-article__taxonomy">
            <?php the_tags( '<div class="aznet-theme-article__tags"><span class="aznet-theme-article__taxonomy-label">' . esc_html__( 'Tags:', 'aznet-theme' ) . '</span> ', ' ', '</div>' ); ?>
        </footer>
    <?php endif; ?>

    <?php
    the_post_navigation(
        [
            'prev_text'  => '<span class="nav-subtitle">' . esc_html__( 'Bài trước', 'aznet-theme' ) . '</span><span class="nav-title">%title</span>',
            'next_text'  => '<span class="nav-subtitle">' . esc_html__( 'Bài sau', 'aznet-theme' ) . '</span><span class="nav-title">%title</span>',
            'aria_label' => esc_attr__( 'Post navigation', 'aznet-theme' ),
        ]
    );
    ?>

    <?php get_template_part( 'template-parts/content/author' ); ?>
</article>

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
$has_article_sidebar = is_active_sidebar( 'article-navigation' );
$reading_layout_class = 'aznet-theme-article__reading-layout';
if ( $has_article_sidebar ) {
    $reading_layout_class .= ' aznet-theme-article__reading-layout--with-sidebar';
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

    <div class="<?php echo esc_attr( $reading_layout_class ); ?>">
        <div class="aznet-theme-article__reading-main">
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
                        'before' => '<nav class="aznet-theme-article__page-links" aria-label="' . esc_attr__( 'Article pages', 'aznet-theme' ) . '"><span class="aznet-theme-navigation__summary">' . esc_html__( 'Pages:', 'aznet-theme' ) . '</span>',
                        'after'  => '</nav>',
                    ]
                );
                ?>
            </div>
        </div>

        <?php if ( $has_article_sidebar ) : ?>
            <aside class="aznet-theme-article__sidebar" aria-label="<?php esc_attr_e( 'Điều hướng nội dung bài viết', 'aznet-theme' ); ?>">
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
    $related_posts = array_values(
        array_filter(
            [
                get_previous_post( true ),
                get_next_post( true ),
            ],
            static fn ( $post ) => $post instanceof WP_Post
        )
    );
    ?>

    <?php if ( ! empty( $related_posts ) ) : ?>
        <section class="aznet-theme-article__related" aria-labelledby="aznet-theme-related-title">
            <header class="aznet-theme-article__related-header">
                <p class="aznet-theme-article__related-kicker"><?php esc_html_e( 'Khám phá thêm', 'aznet-theme' ); ?></p>
                <h2 id="aznet-theme-related-title" class="aznet-theme-article__related-heading"><?php esc_html_e( 'Bài liên quan', 'aznet-theme' ); ?></h2>
            </header>

            <div class="aznet-theme-article__related-grid">
                <?php foreach ( $related_posts as $related_post ) : ?>
                    <?php
                    $related_excerpt    = trim( wp_strip_all_tags( get_the_excerpt( $related_post ) ) );
                    $related_categories = get_the_category( $related_post->ID );
                    ?>
                    <article class="aznet-theme-related-card">
                        <a class="aznet-theme-related-card__media" href="<?php echo esc_url( get_permalink( $related_post ) ); ?>" tabindex="-1" aria-hidden="true">
                            <?php if ( has_post_thumbnail( $related_post ) ) : ?>
                                <?php echo get_the_post_thumbnail( $related_post, 'medium_large', [ 'class' => 'aznet-theme-related-card__image', 'loading' => 'lazy' ] ); ?>
                            <?php else : ?>
                                <span class="aznet-theme-related-card__placeholder" aria-hidden="true"></span>
                            <?php endif; ?>
                        </a>

                        <div class="aznet-theme-related-card__body">
                            <?php if ( ! empty( $related_categories ) ) : ?>
                                <span class="aznet-theme-related-card__category"><?php echo esc_html( $related_categories[0]->name ); ?></span>
                            <?php endif; ?>

                            <h3 class="aznet-theme-related-card__title">
                                <a href="<?php echo esc_url( get_permalink( $related_post ) ); ?>">
                                    <?php echo esc_html( get_the_title( $related_post ) ); ?>
                                </a>
                            </h3>

                            <?php if ( '' !== $related_excerpt ) : ?>
                                <p class="aznet-theme-related-card__excerpt"><?php echo esc_html( wp_trim_words( $related_excerpt, 24, '…' ) ); ?></p>
                            <?php endif; ?>

                            <a class="aznet-theme-related-card__cta" href="<?php echo esc_url( get_permalink( $related_post ) ); ?>">
                                <?php esc_html_e( 'Đọc tiếp', 'aznet-theme' ); ?>
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part( 'template-parts/content/author' ); ?>
</article>

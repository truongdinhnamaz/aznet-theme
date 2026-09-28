<?php
/** Law 01 Knowledge topics. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$terms = homepage_category_references( (array) homepage_source_value( 'law-01', 'knowledge' ) );
if ( [] === $terms ) { return; }
?>
<section id="aznet-homepage-topics" data-aznet-homepage-surface="topics" class="aznet-theme-law01-section aznet-theme-law01-topics" aria-labelledby="aznet-law01-topics-title">
    <div class="aznet-theme-law01-container">
        <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Kiến thức', 'aznet-theme' ); ?></p>
        <h2 id="aznet-law01-topics-title"><?php esc_html_e( 'Chủ đề', 'aznet-theme' ); ?></h2>

        <div class="aznet-theme-law01-grid aznet-theme-law01-grid--topics">
            <?php foreach ( $terms as $index => $term ) : ?>
                <?php
                $link = get_term_link( $term );
                if ( is_wp_error( $link ) ) { continue; }

                $posts = get_posts(
                    [
                        'category'        => (int) $term->term_id,
                        'posts_per_page' => 2,
                        'post_status'    => 'publish',
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                        'no_found_rows'  => true,
                        'ignore_sticky_posts' => true,
                    ]
                );

                $featured_post = $posts[0] ?? null;
                $is_featured = $index < 2
                    && $featured_post instanceof \WP_Post
                    && has_post_thumbnail( $featured_post );
                $card_classes = 'aznet-theme-law01-topic-card';
                if ( $is_featured ) {
                    $card_classes .= ' aznet-theme-law01-topic-card--featured';
                }
                ?>
                <article class="<?php echo esc_attr( $card_classes ); ?>">
                    <?php if ( $is_featured && $featured_post instanceof \WP_Post ) : ?>
                        <a class="aznet-theme-law01-topic-card__featured-media" href="<?php echo esc_url( get_permalink( $featured_post ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $featured_post ) ); ?>">
                            <?php
                            echo get_the_post_thumbnail(
                                $featured_post,
                                'medium_large',
                                [
                                    'class'   => 'aznet-theme-law01-topic-card__featured-image',
                                    'loading' => 'lazy',
                                ]
                            );
                            ?>
                        </a>
                    <?php endif; ?>

                    <div class="aznet-theme-law01-topic-card__content">
                        <div class="aznet-theme-law01-topic-card__heading">
                            <h3><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $term->name ); ?></a></h3>
                            <a class="aznet-theme-law01-topic-card__category-link" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Xem chuyên mục', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
                        </div>

                        <?php if ( [] !== $posts ) : ?>
                            <ul class="aznet-theme-law01-topic-card__posts">
                                <?php foreach ( $posts as $post ) : ?>
                                    <?php if ( ! $post instanceof \WP_Post ) { continue; } ?>
                                    <li<?php if ( $is_featured && $featured_post instanceof \WP_Post && $post->ID === $featured_post->ID ) : ?> class="aznet-theme-law01-topic-card__lead-post"<?php endif; ?>>
                                        <a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

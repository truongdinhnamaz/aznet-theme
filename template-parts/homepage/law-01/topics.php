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
                            <div class="aznet-theme-law01-topic-card__title-wrap">
                                <span class="aznet-theme-law01-topic-card__icon" aria-hidden="true">
                                    <?php if ( 0 === $index ) : ?>
                                        <svg viewBox="0 0 24 24" focusable="false"><path d="M7 4h10v16H7zM9 8h6M9 12h6M9 16h4"/></svg>
                                    <?php elseif ( 1 === $index ) : ?>
                                        <svg viewBox="0 0 24 24" focusable="false"><path d="M4 10.5 12 4l8 6.5M6 9.5V20h12V9.5M9 20v-6h6v6"/></svg>
                                    <?php elseif ( 2 === $index ) : ?>
                                        <svg viewBox="0 0 24 24" focusable="false"><path d="M4 20V8h16v12M8 8V5h8v3M8 12h2M14 12h2M8 16h2M14 16h2"/></svg>
                                    <?php elseif ( 3 === $index ) : ?>
                                        <svg viewBox="0 0 24 24" focusable="false"><path d="M12 3v18M6 7h12M8 7l-3 5h6L8 7Zm8 0-3 5h6l-3-5ZM9 21h6"/></svg>
                                    <?php elseif ( 4 === $index ) : ?>
                                        <svg viewBox="0 0 24 24" focusable="false"><path d="M12 20s-7-4.2-7-10a4 4 0 0 1 7-2.7A4 4 0 0 1 19 10c0 5.8-7 10-7 10Z"/></svg>
                                    <?php else : ?>
                                        <svg viewBox="0 0 24 24" focusable="false"><path d="M8 4h8v4H8zM5 8h14v12H5zM9 12h6M9 16h4"/></svg>
                                    <?php endif; ?>
                                </span>
                                <h3><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $term->name ); ?></a></h3>
                            </div>
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

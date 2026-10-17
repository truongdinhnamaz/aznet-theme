<?php
/**
 * Related Post cards and related category links.
 *
 * WordPress owns Post, taxonomy and media state. The Theme performs a bounded
 * read of native Posts in the current Post's categories and only renders the
 * public presentation projection.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_post_id = get_the_ID();
$category_ids    = wp_get_post_categories( $current_post_id );

if ( empty( $category_ids ) ) {
    return;
}

$candidates = get_posts(
    [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 8,
        'post__not_in'        => [ $current_post_id ],
        'category__in'        => $category_ids,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
    ]
);

$related_posts = [];

foreach ( $candidates as $candidate ) {
    $candidate_excerpt = trim( wp_strip_all_tags( get_the_excerpt( $candidate ) ) );
    $candidate_content = trim( wp_strip_all_tags( $candidate->post_content ) );

    if ( ! has_post_thumbnail( $candidate ) || ( '' === $candidate_excerpt && '' === $candidate_content ) ) {
        continue;
    }

    $related_posts[] = $candidate;

    if ( 3 === count( $related_posts ) ) {
        break;
    }
}

$related_categories = [];

foreach ( $category_ids as $category_id ) {
    $category = get_category( $category_id );

    if ( $category instanceof WP_Term && ! is_wp_error( $category ) ) {
        $category_key = sanitize_title( $category->name );
        if ( ! isset( $related_categories[ $category_key ] ) ) {
            $related_categories[ $category_key ] = $category;
        }
    }
}

foreach ( $related_posts as $related_post ) {
    foreach ( get_the_category( $related_post->ID ) as $category ) {
        $category_key = sanitize_title( $category->name );
        if ( ! isset( $related_categories[ $category_key ] ) ) {
            $related_categories[ $category_key ] = $category;
        }
    }
}
?>

<?php if ( ! empty( $related_posts ) ) : ?>
    <section class="aznet-theme-article__related" aria-labelledby="aznet-theme-related-title">
        <header class="aznet-theme-article__related-header">
            <p class="aznet-theme-article__related-kicker"><?php esc_html_e( 'Khám phá thêm', 'aznet-theme' ); ?></p>
            <h2 id="aznet-theme-related-title" class="aznet-theme-article__related-heading"><?php esc_html_e( 'Bài viết liên quan', 'aznet-theme' ); ?></h2>
        </header>

        <div class="aznet-theme-article__related-grid">
            <?php foreach ( $related_posts as $related_post ) : ?>
                <?php
                $related_excerpt = trim( wp_strip_all_tags( get_the_excerpt( $related_post ) ) );
                if ( '' === $related_excerpt ) {
                    $related_excerpt = wp_trim_words( wp_strip_all_tags( $related_post->post_content ), 24, '…' );
                }
                $related_categories_for_post = get_the_category( $related_post->ID );
                ?>
                <article class="aznet-theme-related-card">
                    <a class="aznet-theme-related-card__media" href="<?php echo esc_url( get_permalink( $related_post ) ); ?>" tabindex="-1" aria-hidden="true">
                        <?php echo get_the_post_thumbnail( $related_post, 'medium_large', [ 'class' => 'aznet-theme-related-card__image', 'loading' => 'lazy' ] ); ?>
                    </a>

                    <div class="aznet-theme-related-card__body">
                        <?php if ( ! empty( $related_categories_for_post ) ) : ?>
                            <span class="aznet-theme-related-card__category"><?php echo esc_html( $related_categories_for_post[0]->name ); ?></span>
                        <?php endif; ?>

                        <h3 class="aznet-theme-related-card__title">
                            <a href="<?php echo esc_url( get_permalink( $related_post ) ); ?>">
                                <?php echo esc_html( get_the_title( $related_post ) ); ?>
                            </a>
                        </h3>

                        <p class="aznet-theme-related-card__excerpt"><?php echo esc_html( $related_excerpt ); ?></p>

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

<?php if ( ! empty( $related_categories ) ) : ?>
    <nav class="aznet-theme-article__related-categories" aria-label="<?php esc_attr_e( 'Chuyên mục liên quan', 'aznet-theme' ); ?>">
        <span class="aznet-theme-article__related-categories-label"><?php esc_html_e( 'Chuyên mục liên quan', 'aznet-theme' ); ?></span>
        <div class="aznet-theme-article__related-categories-list">
            <?php foreach ( $related_categories as $category ) : ?>
                <a href="<?php echo esc_url( get_category_link( $category ) ); ?>">
                    <?php echo esc_html( $category->name ); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
<?php endif; ?>

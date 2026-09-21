<?php
/**
 * Law 01 category archive editorial item.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$variant = isset( $args['variant'] ) ? (string) $args['variant'] : 'standard';
if ( ! in_array( $variant, [ 'featured', 'latest', 'standard' ], true ) ) {
    $variant = 'standard';
}

$classes = 'aznet-theme-law01-archive-item aznet-theme-law01-archive-item--' . $variant;
if ( ! has_post_thumbnail() ) {
    $classes .= ' aznet-theme-law01-archive-item--no-media';
}

$categories = get_the_category_list( ' <span aria-hidden="true">·</span> ' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <a class="aznet-theme-law01-archive-item__media" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Đọc %s', 'aznet-theme' ), get_the_title() ) ); ?>">
            <?php the_post_thumbnail( 'large', [ 'class' => 'aznet-theme-law01-archive-item__thumbnail' ] ); ?>
        </a>
    <?php endif; ?>

    <div class="aznet-theme-law01-archive-item__body">
        <div class="aznet-theme-law01-archive-item__meta">
            <?php if ( is_string( $categories ) && '' !== trim( $categories ) ) : ?>
                <span class="aznet-theme-law01-archive-item__categories"><?php echo wp_kses_post( $categories ); ?></span>
                <span class="aznet-theme-law01-archive-item__separator" aria-hidden="true">·</span>
            <?php endif; ?>
            <time class="aznet-theme-law01-archive-item__date" datetime="<?php echo esc_attr( get_the_time( DATE_W3C ) ); ?>">
                <?php echo esc_html( get_the_date() ); ?>
            </time>
        </div>

        <h2 class="aznet-theme-law01-archive-item__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>

        <div class="aznet-theme-law01-archive-item__excerpt">
            <?php the_excerpt(); ?>
        </div>

        <?php if ( 'featured' === $variant ) : ?>
            <a class="aznet-theme-law01-archive-item__read-more" href="<?php the_permalink(); ?>">
                <?php esc_html_e( 'Đọc bài viết', 'aznet-theme' ); ?>
                <span aria-hidden="true">→</span>
            </a>
        <?php endif; ?>
    </div>
</article>

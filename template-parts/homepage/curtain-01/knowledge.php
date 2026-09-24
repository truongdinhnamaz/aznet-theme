<?php
/**
 * Curtain 01 mapped knowledge feed.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$category_ids = (array) homepage_source_value( 'curtain-01', 'knowledge' );
$posts = homepage_latest_posts( $category_ids, 3, homepage_ledger_ids() );

if ( [] === $posts ) {
    return;
}

homepage_ledger_add(
    array_map(
        static fn ( $post ): int => (int) $post->ID,
        $posts
    )
);

$terms = homepage_category_references( $category_ids );
$archive_url = '';
if ( [] !== $terms ) {
    $candidate = get_term_link( $terms[0] );
    if ( ! is_wp_error( $candidate ) ) {
        $archive_url = (string) $candidate;
    }
}
?>
<section class="aznet-theme-curtain01-section aznet-theme-curtain01-knowledge" aria-labelledby="aznet-curtain01-knowledge-title">
    <div class="aznet-theme-curtain01-shell">
        <div class="aznet-theme-curtain01-section-heading">
            <div>
                <p class="aznet-theme-curtain01-kicker"><?php esc_html_e( 'Kiến thức', 'aznet-theme' ); ?></p>
                <h2 id="aznet-curtain01-knowledge-title"><?php esc_html_e( 'Gợi ý để chọn rèm phù hợp', 'aznet-theme' ); ?></h2>
            </div>
            <?php if ( '' !== $archive_url ) : ?>
                <a class="aznet-theme-curtain01-text-link" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Xem thêm bài viết', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
            <?php endif; ?>
        </div>
        <div class="aznet-theme-curtain01-knowledge__grid">
            <?php foreach ( $posts as $post ) : ?>
                <?php
                $image = has_post_thumbnail( $post )
                    ? get_the_post_thumbnail( $post, 'large', [ 'class' => 'aznet-theme-curtain01-knowledge-card__image' ] )
                    : '';
                $excerpt = trim( (string) get_the_excerpt( $post ) );
                ?>
                <article class="aznet-theme-curtain01-knowledge-card">
                    <?php if ( '' !== $image ) : ?>
                        <a class="aznet-theme-curtain01-knowledge-card__media" href="<?php echo esc_url( get_permalink( $post ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post ) ); ?>">
                            <?php echo wp_kses_post( $image ); ?>
                        </a>
                    <?php endif; ?>
                    <div class="aznet-theme-curtain01-knowledge-card__body">
                        <time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( '', $post ) ); ?></time>
                        <h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
                        <?php if ( '' !== $excerpt ) : ?><p><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
                        <a class="aznet-theme-curtain01-text-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Đọc bài viết', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
/**
 * Curtain 01 mapped project showcase.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$category_id = (int) setting( 'homepage_curtain01_projects_term', 0 );
$category = homepage_category_reference( $category_id );
if ( ! $category instanceof \WP_Term ) {
    return;
}

$posts = homepage_latest_posts( [ $category_id ], 3, homepage_ledger_ids() );
if ( [] === $posts ) {
    return;
}

homepage_ledger_add(
    array_map(
        static fn ( $post ): int => (int) $post->ID,
        $posts
    )
);

$archive_url = get_term_link( $category );
if ( is_wp_error( $archive_url ) ) {
    $archive_url = '';
}
?>
<section class="aznet-theme-curtain01-section aznet-theme-curtain01-projects" aria-labelledby="aznet-curtain01-projects-title">
    <div class="aznet-theme-curtain01-shell">
        <div class="aznet-theme-curtain01-section-heading aznet-theme-curtain01-projects__heading">
            <div>
                <p class="aznet-theme-curtain01-kicker"><?php esc_html_e( 'Công trình', 'aznet-theme' ); ?></p>
                <h2 id="aznet-curtain01-projects-title"><?php esc_html_e( 'Câu chuyện từ công trình', 'aznet-theme' ); ?></h2>
            </div>
            <?php if ( '' !== $archive_url ) : ?>
                <a class="aznet-theme-curtain01-text-link" href="<?php echo esc_url( (string) $archive_url ); ?>"><?php esc_html_e( 'Xem thêm công trình', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
            <?php endif; ?>
        </div>

        <div class="aznet-theme-curtain01-projects__grid">
            <?php foreach ( $posts as $index => $post ) : ?>
                <?php
                $image = has_post_thumbnail( $post )
                    ? get_the_post_thumbnail( $post, 'large', [ 'class' => 'aznet-theme-curtain01-project-card__image' ] )
                    : '';
                $excerpt = trim( (string) get_the_excerpt( $post ) );
                $card_class = 0 === $index
                    ? 'aznet-theme-curtain01-project-card aznet-theme-curtain01-project-card--lead'
                    : 'aznet-theme-curtain01-project-card';
                ?>
                <article class="<?php echo esc_attr( $card_class ); ?>">
                    <?php if ( '' !== $image ) : ?>
                        <a class="aznet-theme-curtain01-project-card__media" href="<?php echo esc_url( get_permalink( $post ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post ) ); ?>">
                            <?php echo wp_kses_post( $image ); ?>
                        </a>
                    <?php endif; ?>
                    <div class="aznet-theme-curtain01-project-card__body">
                        <h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
                        <?php if ( '' !== $excerpt ) : ?><p><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
                        <a class="aznet-theme-curtain01-text-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Xem công trình', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

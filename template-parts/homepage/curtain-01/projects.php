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

$category_id = (int) homepage_effective_source_value( 'curtain-01', 'projects' );
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
<section id="aznet-homepage-curtain-projects" data-aznet-homepage-surface="projects" class="aznet-theme-curtain01-section aznet-theme-curtain01-projects" aria-labelledby="aznet-curtain01-projects-title">
    <div class="aznet-theme-curtain01-shell">
        <div class="aznet-theme-curtain01-section-heading aznet-theme-curtain01-projects__heading">
            <div>
                <p class="aznet-theme-curtain01-kicker"><?php esc_html_e( 'Công trình', 'aznet-theme' ); ?></p>
                <h2 id="aznet-curtain01-projects-title"><?php esc_html_e( 'Công trình tiêu biểu', 'aznet-theme' ); ?></h2>
            </div>
            <?php if ( '' !== $archive_url ) : ?>
                <a class="aznet-theme-curtain01-text-link" href="<?php echo esc_url( (string) $archive_url ); ?>"><?php esc_html_e( 'Xem tất cả công trình', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
            <?php endif; ?>
        </div>

        <div class="aznet-theme-curtain01-projects__grid" data-project-count="<?php echo esc_attr( (string) count( $posts ) ); ?>">
            <?php foreach ( $posts as $index => $post ) : ?>
                <?php
                $image = has_post_thumbnail( $post )
                    ? get_the_post_thumbnail(
                        $post,
                        'large',
                        [
                            'class'   => 'aznet-theme-curtain01-project-card__image',
                            'loading' => 0 === $index ? 'eager' : 'lazy',
                        ]
                    )
                    : '';
                $excerpt = trim( (string) get_the_excerpt( $post ) );
                $card_class = 0 === $index
                    ? 'aznet-theme-curtain01-project-card aznet-theme-curtain01-project-card--lead'
                    : 'aznet-theme-curtain01-project-card';
                ?>
                <article class="<?php echo esc_attr( $card_class ); ?>">
                    <a class="aznet-theme-curtain01-project-card__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
                        <span class="aznet-theme-curtain01-project-card__media">
                            <?php if ( '' !== $image ) : ?>
                                <?php echo wp_kses_post( $image ); ?>
                            <?php endif; ?>
                        </span>
                        <span class="aznet-theme-curtain01-project-card__body">
                            <span class="aznet-theme-curtain01-project-card__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
                            <span class="aznet-theme-curtain01-project-card__copy">
                                <h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
                                <?php if ( '' !== $excerpt ) : ?><span class="aznet-theme-curtain01-project-card__excerpt"><?php echo esc_html( $excerpt ); ?></span><?php endif; ?>
                            </span>
                            <span class="aznet-theme-curtain01-project-card__arrow" aria-hidden="true">↗</span>
                        </span>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

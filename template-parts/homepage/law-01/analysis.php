<?php
/** Law 01 Case analysis. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$term_id = (int) setting( 'homepage_case_analysis_term', 0 );
$term = homepage_category_reference( $term_id );
if ( ! $term instanceof \WP_Term ) { return; }

$posts = homepage_latest_posts( [ $term_id ], 3, homepage_ledger_ids() );
if ( [] === $posts ) { return; }
homepage_ledger_add( array_map( static fn( $post ): int => (int) $post->ID, $posts ) );
?>
<section class="aznet-theme-law01-section aznet-theme-law01-analysis" aria-labelledby="aznet-law01-analysis-title">
    <div class="aznet-theme-law01-container">
        <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Chuyên sâu', 'aznet-theme' ); ?></p>
        <h2 id="aznet-law01-analysis-title"><?php echo esc_html( $term->name ); ?></h2>
        <div class="aznet-theme-law01-grid aznet-theme-law01-grid--analysis">
            <?php foreach ( $posts as $post ) : ?>
                <?php $excerpt = trim( (string) get_the_excerpt( $post ) ); ?>
                <article class="aznet-theme-law01-article-card aznet-theme-law01-analysis-card">
                    <?php if ( has_post_thumbnail( $post ) ) : ?>
                        <div class="aznet-theme-law01-analysis-card__media">
                            <?php
                            echo get_the_post_thumbnail(
                                $post,
                                'large',
                                [
                                    'class' => 'aznet-theme-law01-analysis-card__image',
                                    'loading' => 'lazy',
                                    'decoding' => 'async',
                                    'style' => 'display:block;width:100%;height:auto;',
                                ]
                            );
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="aznet-theme-law01-analysis-card__body">
                        <p class="aznet-theme-law01-meta"><?php echo esc_html( get_the_date( '', $post ) ); ?></p>
                        <h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
                        <?php if ( '' !== $excerpt ) : ?><p class="aznet-theme-law01-analysis-card__excerpt"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
                        <a class="aznet-theme-law01-text-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Đọc phân tích', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

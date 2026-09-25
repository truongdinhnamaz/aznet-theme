<?php
/** Law 01 Legal news. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$term_id = (int) setting( 'homepage_legal_news_term', 0 );
$term = homepage_category_reference( $term_id );
if ( ! $term instanceof \WP_Term ) { return; }

$posts = homepage_latest_posts( [ $term_id ], 5, homepage_ledger_ids() );
if ( [] === $posts ) { return; }
homepage_ledger_add( array_map( static fn( $post ): int => (int) $post->ID, $posts ) );

$featured = array_shift( $posts );
$featured_excerpt = $featured instanceof \WP_Post ? trim( (string) get_the_excerpt( $featured ) ) : '';
?>
<section class="aznet-theme-law01-section aznet-theme-law01-news" aria-labelledby="aznet-law01-news-title">
    <div class="aznet-theme-law01-container">
        <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Cập nhật', 'aznet-theme' ); ?></p>
        <h2 id="aznet-law01-news-title"><?php echo esc_html( $term->name ); ?></h2>
        <?php if ( $featured instanceof \WP_Post ) : ?>
            <article class="aznet-theme-law01-news-featured">
                <?php if ( has_post_thumbnail( $featured ) ) : ?>
                    <div class="aznet-theme-law01-news-featured__media">
                        <?php
                        echo get_the_post_thumbnail(
                            $featured,
                            'large',
                            [
                                'class' => 'aznet-theme-law01-news-featured__image',
                                'loading' => 'lazy',
                                'decoding' => 'async',
                                'style' => 'display:block;width:100%;height:auto;',
                            ]
                        );
                        ?>
                    </div>
                <?php endif; ?>
                <div class="aznet-theme-law01-news-featured__body">
                    <p class="aznet-theme-law01-meta"><?php echo esc_html( get_the_date( '', $featured ) ); ?></p>
                    <h3><a href="<?php echo esc_url( get_permalink( $featured ) ); ?>"><?php echo esc_html( get_the_title( $featured ) ); ?></a></h3>
                    <?php if ( '' !== $featured_excerpt ) : ?><p><?php echo esc_html( $featured_excerpt ); ?></p><?php endif; ?>
                    <a class="aznet-theme-law01-text-link" href="<?php echo esc_url( get_permalink( $featured ) ); ?>"><?php esc_html_e( 'Đọc tin', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a>
                </div>
            </article>
        <?php endif; ?>
        <?php if ( [] !== $posts ) : ?>
            <div class="aznet-theme-law01-news-list aznet-theme-law01-news-list--secondary">
                <?php foreach ( $posts as $post ) : ?>
                    <article>
                        <p class="aznet-theme-law01-meta"><?php echo esc_html( get_the_date( '', $post ) ); ?></p>
                        <h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

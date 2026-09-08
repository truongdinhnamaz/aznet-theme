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
?>
<section class="aznet-theme-law01-section aznet-theme-law01-news" aria-labelledby="aznet-law01-news-title"><div class="aznet-theme-law01-container"><h2 id="aznet-law01-news-title"><?php echo esc_html( $term->name ); ?></h2><div class="aznet-theme-law01-news-list"><?php foreach ( $posts as $post ) : ?><article><p class="aznet-theme-law01-meta"><?php echo esc_html( get_the_date( '', $post ) ); ?></p><h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3></article><?php endforeach; ?></div></div></section>

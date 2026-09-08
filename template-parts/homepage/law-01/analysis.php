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
<section class="aznet-theme-law01-section aznet-theme-law01-analysis" aria-labelledby="aznet-law01-analysis-title"><div class="aznet-theme-law01-container"><p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Chuyên sâu', 'aznet-theme' ); ?></p><h2 id="aznet-law01-analysis-title"><?php echo esc_html( $term->name ); ?></h2><div class="aznet-theme-law01-grid aznet-theme-law01-grid--analysis"><?php foreach ( $posts as $post ) : ?><article class="aznet-theme-law01-article-card"><p class="aznet-theme-law01-meta"><?php echo esc_html( get_the_date( '', $post ) ); ?></p><h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3></article><?php endforeach; ?></div></div></section>

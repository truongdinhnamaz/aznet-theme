<?php
/** Law 01 Latest knowledge. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$posts = homepage_latest_posts( (array) setting( 'homepage_knowledge_terms', [] ), 5, homepage_ledger_ids() );
if ( [] === $posts ) { return; }
homepage_ledger_add( array_map( static fn( $post ): int => (int) $post->ID, $posts ) );
?>
<section class="aznet-theme-law01-section aznet-theme-law01-articles" aria-labelledby="aznet-law01-latest-title"><div class="aznet-theme-law01-container">
<p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Kiến thức', 'aznet-theme' ); ?></p><h2 id="aznet-law01-latest-title"><?php esc_html_e( 'Mới cập nhật', 'aznet-theme' ); ?></h2>
<div class="aznet-theme-law01-grid aznet-theme-law01-grid--articles"><?php foreach ( $posts as $post ) : ?><article class="aznet-theme-law01-article-card"><p class="aznet-theme-law01-meta"><?php echo esc_html( get_the_date( '', $post ) ); ?></p><h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3><?php $excerpt = trim( (string) get_the_excerpt( $post ) ); if ( '' !== $excerpt ) : ?><p><?php echo esc_html( $excerpt ); ?></p><?php endif; ?></article><?php endforeach; ?></div>
</div></section>

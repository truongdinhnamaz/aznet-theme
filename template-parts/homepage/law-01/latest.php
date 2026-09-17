<?php
/** Law 01 Latest knowledge. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$posts = homepage_latest_posts( (array) setting( 'homepage_knowledge_terms', [] ), 3, homepage_ledger_ids() );
if ( [] === $posts ) { return; }
homepage_ledger_add( array_map( static fn( $post ): int => (int) $post->ID, $posts ) );
?>
<section class="aznet-theme-law01-section aznet-theme-law01-articles" aria-labelledby="aznet-law01-latest-title"><div class="aznet-theme-law01-container">
<div class="aznet-theme-law01-section-heading"><div><p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Kiến thức pháp luật', 'aznet-theme' ); ?></p><h2 id="aznet-law01-latest-title"><?php esc_html_e( 'Bài viết mới nhất', 'aznet-theme' ); ?></h2></div></div>
<div class="aznet-theme-law01-grid aznet-theme-law01-grid--articles"><?php foreach ( $posts as $post ) : $categories = get_the_category( (int) $post->ID ); $image = has_post_thumbnail( $post ) ? get_the_post_thumbnail( $post, 'large', [ 'class' => 'aznet-theme-law01-article-card__image' ] ) : ''; ?><article class="aznet-theme-law01-article-card">
<?php if ( '' !== $image ) : ?><a class="aznet-theme-law01-article-card__media" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo wp_kses_post( $image ); ?></a><?php endif; ?>
<div class="aznet-theme-law01-article-card__body"><div class="aznet-theme-law01-article-card__meta"><?php if ( [] !== $categories ) : ?><span><?php echo esc_html( $categories[0]->name ); ?></span><?php endif; ?><time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( '', $post ) ); ?></time></div><h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3><?php $excerpt = trim( (string) get_the_excerpt( $post ) ); if ( '' !== $excerpt ) : ?><p><?php echo esc_html( $excerpt ); ?></p><?php endif; ?><a class="aznet-theme-law01-text-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Đọc thêm', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></div>
</article><?php endforeach; ?></div>
</div></section>

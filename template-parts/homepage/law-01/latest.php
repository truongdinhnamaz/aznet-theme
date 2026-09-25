<?php
/** Law 01 Latest knowledge. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$posts = homepage_latest_posts( (array) homepage_source_value( 'law-01', 'knowledge' ), 3, homepage_ledger_ids() );
if ( [] === $posts ) { return; }
homepage_ledger_add( array_map( static fn( $post ): int => (int) $post->ID, $posts ) );

$posts_page_id = (int) get_option( 'page_for_posts', 0 );
$posts_page_url = '';
$posts_page_title = $posts_page_id > 0 && 'publish' === get_post_status( $posts_page_id ) ? trim( (string) get_the_title( $posts_page_id ) ) : '';
if ( '' === $posts_page_title ) { $posts_page_title = __( 'Bài viết', 'aznet-theme' ); }
if ( $posts_page_id > 0 && 'publish' === get_post_status( $posts_page_id ) ) {
    $posts_page_permalink = get_permalink( $posts_page_id );
    $posts_page_url = is_string( $posts_page_permalink ) ? $posts_page_permalink : '';
}
?>
<section id="aznet-homepage-articles" data-aznet-homepage-surface="latest" class="aznet-theme-law01-section aznet-theme-law01-articles" aria-labelledby="aznet-law01-latest-title"><div class="aznet-theme-law01-container">
<div class="aznet-theme-law01-section-heading"><div><p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Mới cập nhật', 'aznet-theme' ); ?></p><h2 id="aznet-law01-latest-title"><?php echo esc_html( $posts_page_title ); ?></h2></div><?php if ( '' !== $posts_page_url ) : ?><a class="aznet-theme-law01-text-link" href="<?php echo esc_url( $posts_page_url ); ?>"><?php esc_html_e( 'Xem tất cả bài viết', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a><?php endif; ?></div>
<div class="aznet-theme-law01-grid aznet-theme-law01-grid--articles"><?php foreach ( $posts as $post ) : $categories = get_the_category( (int) $post->ID ); $image = has_post_thumbnail( $post ) ? get_the_post_thumbnail( $post, 'large', [ 'class' => 'aznet-theme-law01-article-card__image', 'style' => 'display:block;width:100%;height:auto;' ] ) : ''; ?><article class="aznet-theme-law01-article-card">
<?php if ( '' !== $image ) : ?><div class="aznet-theme-law01-article-card__media"><a class="aznet-theme-law01-article-card__media-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post ) ); ?>"><?php echo wp_kses_post( $image ); ?></a></div><?php endif; ?>
<div class="aznet-theme-law01-article-card__body"><div class="aznet-theme-law01-article-card__meta"><?php if ( [] !== $categories ) : ?><span><?php echo esc_html( $categories[0]->name ); ?></span><?php endif; ?><time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( '', $post ) ); ?></time></div><h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3><?php $excerpt = trim( (string) get_the_excerpt( $post ) ); if ( '' !== $excerpt ) : ?><p><?php echo esc_html( $excerpt ); ?></p><?php endif; ?><a class="aznet-theme-law01-text-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Đọc thêm: %s', 'aznet-theme' ), get_the_title( $post ) ) ); ?>"><?php esc_html_e( 'Đọc thêm', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></div>
</article><?php endforeach; ?></div>
</div></section>

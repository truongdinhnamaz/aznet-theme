<?php
/** Law 01 Knowledge topics. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$terms = homepage_category_references( (array) homepage_source_value( 'law-01', 'knowledge' ) );
if ( [] === $terms ) { return; }
?>
<section id="aznet-homepage-topics" data-aznet-homepage-surface="topics" class="aznet-theme-law01-section aznet-theme-law01-topics" aria-labelledby="aznet-law01-topics-title"><div class="aznet-theme-law01-container">
<p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Kiến thức', 'aznet-theme' ); ?></p><h2 id="aznet-law01-topics-title"><?php esc_html_e( 'Chủ đề', 'aznet-theme' ); ?></h2>
<div class="aznet-theme-law01-grid aznet-theme-law01-grid--topics"><?php foreach ( $terms as $term ) : $link = get_term_link( $term ); if ( is_wp_error( $link ) ) { continue; } ?><a class="aznet-theme-law01-topic" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $term->name ); ?></a><?php endforeach; ?></div>
</div></section>

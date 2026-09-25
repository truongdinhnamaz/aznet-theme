<?php
/** Law 01 Services. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$parent = homepage_page_reference( (int) homepage_source_value( 'law-01', 'services' ) );
if ( ! $parent instanceof \WP_Post ) { return; }
$items = homepage_direct_published_children( (int) $parent->ID, 6 );
if ( [] === $items ) { return; }
$intro = trim( (string) get_the_excerpt( $parent ) );
?>
<section id="aznet-homepage-services" data-aznet-homepage-surface="services" class="aznet-theme-law01-section aznet-theme-law01-services" aria-labelledby="aznet-law01-services-title">
<div class="aznet-theme-law01-container">
<div class="aznet-theme-law01-section-heading"><div><p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Dịch vụ', 'aznet-theme' ); ?></p><h2 id="aznet-law01-services-title" class="aznet-theme-law01-services__heading"><?php echo esc_html( get_the_title( $parent ) ); ?></h2></div><a class="aznet-theme-law01-text-link" href="<?php echo esc_url( get_permalink( $parent ) ); ?>"><?php esc_html_e( 'Xem tất cả dịch vụ', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></div>
<?php if ( '' !== $intro ) : ?><p class="aznet-theme-law01-lede aznet-theme-law01-services__intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
<div class="aznet-theme-law01-grid aznet-theme-law01-grid--services" data-count="<?php echo esc_attr( (string) count( $items ) ); ?>">
<?php foreach ( $items as $index => $item ) : if ( ! $item instanceof \WP_Post ) { continue; } ?>
<a class="aznet-theme-law01-card" href="<?php echo esc_url( get_permalink( $item ) ); ?>">
<span class="aznet-theme-law01-card__badge aznet-theme-law01-card__icon" aria-hidden="true">
<?php if ( 0 === $index ) : ?>
<svg viewBox="0 0 24 24" focusable="false"><path d="M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 9h.01M15 9h.01M9 13h.01M15 13h.01"/></svg>
<?php elseif ( 1 === $index ) : ?>
<svg viewBox="0 0 24 24" focusable="false"><path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3 20v-2a4 4 0 0 1 4-4h2a4 4 0 0 1 4 4v2m0-5a4 4 0 0 1 4-1h1a3 3 0 0 1 3 3v3"/></svg>
<?php elseif ( 2 === $index ) : ?>
<svg viewBox="0 0 24 24" focusable="false"><path d="m14 5 5 5M6 13l8-8 5 5-8 8M4 20l6-2-4-4-2 6Zm8-8 5 5M3 21h18"/></svg>
<?php elseif ( 3 === $index ) : ?>
<svg viewBox="0 0 24 24" focusable="false"><path d="m3 11 9-7 9 7M5 10v10h14V10M9 20v-6h6v6"/></svg>
<?php elseif ( 4 === $index ) : ?>
<svg viewBox="0 0 24 24" focusable="false"><path d="M12 20s-7-4.35-7-10a4 4 0 0 1 7-2.65A4 4 0 0 1 19 10c0 5.65-7 10-7 10Z"/></svg>
<?php else : ?>
<svg viewBox="0 0 24 24" focusable="false"><path d="M4 8h16v11H4V8Zm5 0V5h6v3M4 12h16M10 12v2h4v-2"/></svg>
<?php endif; ?>
</span>
<h3><?php echo esc_html( get_the_title( $item ) ); ?></h3>
<?php $summary = trim( (string) get_the_excerpt( $item ) ); if ( '' !== $summary ) : ?><p><?php echo esc_html( $summary ); ?></p><?php endif; ?>
<span class="aznet-theme-law01-card__link"><?php esc_html_e( 'Xem chi tiết', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></span>
</a>
<?php endforeach; ?>
</div>
</div>
</section>

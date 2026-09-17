<?php
/** Law 01 Services. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$parent = homepage_page_reference( (int) setting( 'homepage_services_page', 0 ) );
if ( ! $parent instanceof \WP_Post ) { return; }
$items = homepage_direct_published_children( (int) $parent->ID, 6 );
if ( [] === $items ) { return; }
$intro = trim( (string) get_the_excerpt( $parent ) );
?>
<section class="aznet-theme-law01-section aznet-theme-law01-services" aria-labelledby="aznet-law01-services-title">
<div class="aznet-theme-law01-container">
<div class="aznet-theme-law01-section-heading"><div><p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Dịch vụ nổi bật', 'aznet-theme' ); ?></p><h2 id="aznet-law01-services-title"><?php echo esc_html( get_the_title( $parent ) ); ?></h2></div><a class="aznet-theme-law01-text-link" href="<?php echo esc_url( get_permalink( $parent ) ); ?>"><?php esc_html_e( 'Xem tất cả dịch vụ', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></div>
<?php if ( '' !== $intro ) : ?><p class="aznet-theme-law01-lede aznet-theme-law01-services__intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
<div class="aznet-theme-law01-grid aznet-theme-law01-grid--services" data-count="<?php echo esc_attr( (string) count( $items ) ); ?>">
<?php foreach ( $items as $index => $item ) : if ( ! $item instanceof \WP_Post ) { continue; } ?>
<a class="aznet-theme-law01-card" href="<?php echo esc_url( get_permalink( $item ) ); ?>">
<span class="aznet-theme-law01-card__badge" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
<h3><?php echo esc_html( get_the_title( $item ) ); ?></h3>
<?php $summary = trim( (string) get_the_excerpt( $item ) ); if ( '' !== $summary ) : ?><p><?php echo esc_html( $summary ); ?></p><?php endif; ?>
<span class="aznet-theme-law01-card__link"><?php esc_html_e( 'Xem chi tiết', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></span>
</a>
<?php endforeach; ?>
</div>
</div>
</section>

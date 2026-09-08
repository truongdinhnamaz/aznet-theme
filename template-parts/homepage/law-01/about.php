<?php
/** Law 01 About. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$page = homepage_page_reference( (int) setting( 'homepage_about_page', 0 ) );
if ( ! $page instanceof \WP_Post ) { return; }
$summary = trim( (string) get_the_excerpt( $page ) );
$image = has_post_thumbnail( $page ) ? get_the_post_thumbnail( $page, 'large', [ 'class' => 'aznet-theme-law01-editorial__image' ] ) : '';
?>
<section class="aznet-theme-law01-section aznet-theme-law01-editorial" aria-labelledby="aznet-law01-about-title"><div class="aznet-theme-law01-container aznet-theme-law01-editorial__grid">
<div><p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Về chúng tôi', 'aznet-theme' ); ?></p><h2 id="aznet-law01-about-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2><?php if ( '' !== $summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $summary ); ?></p><?php endif; ?><p><a class="aznet-theme-law01-text-link" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Tìm hiểu thêm', 'aznet-theme' ); ?></a></p></div>
<?php if ( '' !== $image ) : ?><div><?php echo wp_kses_post( $image ); ?></div><?php endif; ?>
</div></section>

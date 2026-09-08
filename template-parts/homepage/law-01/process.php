<?php
/** Law 01 Process Page preview; does not infer steps from prose. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$page = homepage_page_reference( (int) setting( 'homepage_process_page', 0 ) );
if ( ! $page instanceof \WP_Post ) { return; }
$summary = trim( (string) get_the_excerpt( $page ) );
?>
<section class="aznet-theme-law01-section aznet-theme-law01-process" aria-labelledby="aznet-law01-process-title"><div class="aznet-theme-law01-container aznet-theme-law01-panel"><p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Quy trình', 'aznet-theme' ); ?></p><h2 id="aznet-law01-process-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2><?php if ( '' !== $summary ) : ?><p><?php echo esc_html( $summary ); ?></p><?php endif; ?><p><a class="aznet-theme-law01-text-link" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Xem quy trình tư vấn', 'aznet-theme' ); ?></a></p></div></section>

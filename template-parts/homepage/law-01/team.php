<?php
/** Law 01 Team teaser; WordPress Page presentation only. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$page = homepage_page_reference( (int) setting( 'homepage_team_page', 0 ) );
if ( ! $page instanceof \WP_Post ) { return; }
$summary = trim( (string) get_the_excerpt( $page ) );
?>
<section class="aznet-theme-law01-section aznet-theme-law01-team" aria-labelledby="aznet-law01-team-title"><div class="aznet-theme-law01-container aznet-theme-law01-panel">
<p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Đội ngũ', 'aznet-theme' ); ?></p><h2 id="aznet-law01-team-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
<?php if ( '' !== $summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $summary ); ?></p><?php endif; ?>
<p><a class="aznet-theme-law01-button aznet-theme-law01-button--secondary" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Gặp đội ngũ của chúng tôi', 'aznet-theme' ); ?></a></p>
</div></section>

<?php
/** Law 01 Team presentation from WordPress-owned Pages. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
$page = homepage_page_reference( (int) setting( 'homepage_team_page', 0 ) );
if ( ! $page instanceof \WP_Post ) { return; }
$summary = trim( (string) get_the_excerpt( $page ) );
$members = homepage_direct_published_children( (int) $page->ID, 8 );
?>
<section class="aznet-theme-law01-section aznet-theme-law01-team" aria-labelledby="aznet-law01-team-title"><div class="aznet-theme-law01-container aznet-theme-law01-panel">
<p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Đội ngũ', 'aznet-theme' ); ?></p><h2 id="aznet-law01-team-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
<?php if ( '' !== $summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $summary ); ?></p><?php endif; ?>
<?php if ( [] !== $members ) : ?>
<div class="aznet-theme-law01-grid aznet-theme-law01-grid--team">
<?php foreach ( $members as $member ) : if ( ! $member instanceof \WP_Post ) { continue; }
    $member_title = get_the_title( $member );
    $member_name = is_string( $member_title ) ? $member_title : '';
    $member_honorific = '';
    if ( 1 === preg_match( '/^(Luật sư|Luật gia)\\s+(.+)$/u', $member_name, $parts ) ) {
        $member_honorific = $parts[1];
        $member_name = $parts[2];
    }
    $member_summary = trim( (string) get_the_excerpt( $member ) );
    $member_image = has_post_thumbnail( $member ) ? get_the_post_thumbnail( $member, 'medium_large', [ 'class' => 'aznet-theme-law01-team-card__image' ] ) : '';
?>
<article class="aznet-theme-law01-team-card">
<?php if ( '' !== $member_image ) : ?><a class="aznet-theme-law01-team-card__media" href="<?php echo esc_url( get_permalink( $member ) ); ?>"><?php echo wp_kses_post( $member_image ); ?></a><?php endif; ?>
<div class="aznet-theme-law01-team-card__body"><h3><a href="<?php echo esc_url( get_permalink( $member ) ); ?>"><?php if ( '' !== $member_honorific ) : ?><span class="aznet-theme-team-card__honorific"><?php echo esc_html( $member_honorific ); ?></span> <?php endif; ?><span class="aznet-theme-team-card__person-name"><?php echo esc_html( $member_name ); ?></span></a></h3>
<?php if ( '' !== $member_summary ) : ?><p><?php echo esc_html( $member_summary ); ?></p><?php endif; ?></div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<p><a class="aznet-theme-law01-button aznet-theme-law01-button--secondary" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Gặp đội ngũ của chúng tôi', 'aznet-theme' ); ?></a></p>
</div></section>

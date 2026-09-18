<?php
/** Law 01 combined About + Team presentation band. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$about = homepage_page_reference( (int) setting( 'homepage_about_page', 0 ) );
$team = homepage_page_reference( (int) setting( 'homepage_team_page', 0 ) );
if ( ! $about instanceof \WP_Post && ! $team instanceof \WP_Post ) { return; }

$about_summary = $about instanceof \WP_Post ? trim( (string) get_the_excerpt( $about ) ) : '';
$about_image = $about instanceof \WP_Post && has_post_thumbnail( $about ) ? get_the_post_thumbnail( $about, 'large', [ 'class' => 'aznet-theme-law01-profile__about-image' ] ) : '';
$team_summary = $team instanceof \WP_Post ? trim( (string) get_the_excerpt( $team ) ) : '';
$members = $team instanceof \WP_Post ? homepage_direct_published_children( (int) $team->ID, 4 ) : [];
?>
<section class="aznet-theme-law01-section aznet-theme-law01-profile" aria-label="<?php esc_attr_e( 'Giới thiệu và đội ngũ', 'aznet-theme' ); ?>">
<div class="aznet-theme-law01-container">
    <?php if ( $about instanceof \WP_Post ) : ?>
    <div class="aznet-theme-law01-profile__about-grid">
        <div class="aznet-theme-law01-profile__about-copy aznet-theme-law01-editorial">
            <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Giới thiệu về văn phòng', 'aznet-theme' ); ?></p>
            <h2><?php echo esc_html( get_the_title( $about ) ); ?></h2>
            <?php if ( '' !== $about_summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $about_summary ); ?></p><?php endif; ?>
            <p><a class="aznet-theme-law01-button" href="<?php echo esc_url( get_permalink( $about ) ); ?>"><?php esc_html_e( 'Tìm hiểu thêm', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></p>
        </div>
        <?php if ( '' !== $about_image ) : ?><div class="aznet-theme-law01-profile__about-media"><?php echo wp_kses_post( $about_image ); ?></div><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ( $team instanceof \WP_Post ) : ?>
    <div class="aznet-theme-law01-profile__team-band aznet-theme-law01-team">
        <div class="aznet-theme-law01-section-heading">
            <div>
                <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Đội ngũ luật sư', 'aznet-theme' ); ?></p>
                <h2><?php echo esc_html( get_the_title( $team ) ); ?></h2>
                <?php if ( '' !== $team_summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $team_summary ); ?></p><?php endif; ?>
            </div>
        </div>
        <?php if ( [] !== $members ) : ?><div class="aznet-theme-law01-profile__members">
            <?php foreach ( $members as $member ) : if ( ! $member instanceof \WP_Post ) { continue; } $member_image = has_post_thumbnail( $member ) ? get_the_post_thumbnail( $member, 'medium_large', [ 'class' => 'aznet-theme-law01-profile__member-image aznet-theme-law01-team-card__image' ] ) : ''; $member_summary = trim( (string) get_the_excerpt( $member ) ); ?>
            <article class="aznet-theme-law01-profile__member aznet-theme-law01-team-card">
                <?php if ( '' !== $member_image ) : ?><a class="aznet-theme-law01-team-card__media" href="<?php echo esc_url( get_permalink( $member ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $member ) ); ?>"><?php echo wp_kses_post( $member_image ); ?></a><?php endif; ?>
                <div class="aznet-theme-law01-team-card__body"><h3><a href="<?php echo esc_url( get_permalink( $member ) ); ?>"><?php echo esc_html( get_the_title( $member ) ); ?></a></h3>
                <?php if ( '' !== $member_summary ) : ?><p><?php echo esc_html( $member_summary ); ?></p><?php endif; ?></div>
            </article>
            <?php endforeach; ?>
        </div><?php endif; ?>
        <p class="aznet-theme-law01-team-more"><a class="aznet-theme-law01-button aznet-theme-law01-button--secondary" href="<?php echo esc_url( get_permalink( $team ) ); ?>"><?php esc_html_e( 'Xem thêm về đội ngũ', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></p>
    </div>
    <?php endif; ?>
</div>
</section>

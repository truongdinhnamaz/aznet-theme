<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$team_member = $args['team_member'] ?? null;
if ( ! $team_member instanceof \WP_Post ) { return; }

$heading_tag = isset( $args['heading_tag'] ) ? (string) $args['heading_tag'] : 'h3';
if ( ! in_array( $heading_tag, [ 'h2', 'h3', 'h4', 'h5', 'h6' ], true ) ) {
    $heading_tag = 'h3';
}

$portrait_id = function_exists( 'AZnet\\Theme\\team_member_portrait_id' )
    ? \AZnet\Theme\team_member_portrait_id( (int) $team_member->ID )
    : 0;
$member_image = $portrait_id > 0
    ? wp_get_attachment_image( $portrait_id, 'medium_large', false, [ 'class' => 'aznet-theme-team-card__image aznet-theme-law01-profile__member-image aznet-theme-law01-team-card__image' ] )
    : '';
$member_role = trim( (string) $team_member->post_excerpt );
$member_phone = function_exists( 'AZnet\\Theme\\team_member_phone' ) ? \AZnet\Theme\team_member_phone( (int) $team_member->ID ) : '';
$member_zalo = function_exists( 'AZnet\\Theme\\team_member_zalo' ) ? \AZnet\Theme\team_member_zalo( (int) $team_member->ID ) : '';
$member_phone_href = function_exists( 'AZnet\\Theme\\team_member_phone_href' ) ? \AZnet\Theme\team_member_phone_href( $member_phone ) : '';
$member_phone_display = function_exists( 'AZnet\\Theme\\team_member_phone_display' ) ? \AZnet\Theme\team_member_phone_display( $member_phone ) : $member_phone;
$member_zalo_url = function_exists( 'AZnet\\Theme\\team_member_zalo_url' ) ? \AZnet\Theme\team_member_zalo_url( $member_zalo ) : '';
$url = get_permalink( $team_member );
$url = is_string( $url ) ? $url : '';
?>
<article class="aznet-theme-team-card aznet-theme-law01-profile__member aznet-theme-law01-team-card">
    <?php if ( '' !== $member_image && '' !== $url ) : ?>
        <a class="aznet-theme-team-card__media aznet-theme-law01-team-card__media" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( get_the_title( $team_member ) ); ?>">
            <?php echo wp_kses_post( $member_image ); ?>
        </a>
    <?php endif; ?>
    <div class="aznet-theme-team-card__body aznet-theme-law01-team-card__body">
        <<?php echo esc_html( $heading_tag ); ?> class="aznet-theme-team-card__name">
            <?php if ( '' !== $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php endif; ?>
            <?php echo esc_html( get_the_title( $team_member ) ); ?>
            <?php if ( '' !== $url ) : ?></a><?php endif; ?>
        </<?php echo esc_html( $heading_tag ); ?>>
        <?php if ( '' !== $member_role ) : ?>
            <p class="aznet-theme-team-card__role"><?php echo esc_html( $member_role ); ?></p>
        <?php endif; ?>
        <?php if ( '' !== $member_phone_href || '' !== $member_zalo_url ) : ?>
            <div class="aznet-theme-team-card__contacts" aria-label="<?php echo esc_attr__( 'Liên hệ', 'aznet-theme' ); ?>">
                <?php if ( '' !== $member_phone_href ) : ?>
                    <a class="aznet-theme-team-card__contact aznet-theme-team-card__contact--phone" href="<?php echo esc_url( $member_phone_href ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Gọi %s', 'aznet-theme' ), $member_phone_display ) ); ?>">
                        <span aria-hidden="true">☎</span><span><?php echo esc_html( $member_phone_display ); ?></span>
                    </a>
                <?php endif; ?>
                <?php if ( '' !== $member_zalo_url ) : ?>
                    <a class="aznet-theme-team-card__contact aznet-theme-team-card__contact--zalo" href="<?php echo esc_url( $member_zalo_url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr__( 'Mở Zalo', 'aznet-theme' ); ?>">
                        <span class="aznet-theme-team-card__contact-badge" aria-hidden="true">Z</span><span><?php esc_html_e( 'Zalo', 'aznet-theme' ); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>

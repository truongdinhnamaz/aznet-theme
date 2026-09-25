<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$team_member = $args['team_member'] ?? null;
if ( ! $team_member instanceof \WP_Post ) { return; }

$member_image = has_post_thumbnail( $team_member )
    ? get_the_post_thumbnail( $team_member, 'medium_large', [ 'class' => 'aznet-theme-team-card__image aznet-theme-law01-profile__member-image aznet-theme-law01-team-card__image' ] )
    : '';
$member_role = trim( (string) $team_member->post_excerpt );
$url = get_permalink( $team_member );
$url = is_string( $url ) ? $url : '';
?>
<article class="aznet-theme-team-card aznet-theme-law01-profile__member aznet-theme-law01-team-card">
    <?php if ( '' !== $member_image && '' !== $url ) : ?>
        <a class="aznet-theme-team-card__media aznet-theme-law01-team-card__media" href="<?php echo esc_url( $url ); ?>">
            <?php echo wp_kses_post( $member_image ); ?>
        </a>
    <?php endif; ?>
    <div class="aznet-theme-team-card__body aznet-theme-law01-team-card__body">
        <h3 class="aznet-theme-team-card__name">
            <?php if ( '' !== $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php endif; ?>
            <?php echo esc_html( get_the_title( $team_member ) ); ?>
            <?php if ( '' !== $url ) : ?></a><?php endif; ?>
        </h3>
        <?php if ( '' !== $member_role ) : ?>
            <p class="aznet-theme-team-card__role"><?php echo esc_html( $member_role ); ?></p>
        <?php endif; ?>
    </div>
</article>

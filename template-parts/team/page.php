<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$excerpt = isset( $args['excerpt'] ) ? trim( (string) $args['excerpt'] ) : '';
$members = \AZnet\Theme\team_directory_members();
?>
<section class="aznet-theme-team-directory">
    <div class="aznet-theme-page__section-inner">
        <header class="aznet-theme-team-directory__header">
            <p class="aznet-theme-team-directory__eyebrow"><?php esc_html_e( 'Đội ngũ', 'aznet-theme' ); ?></p>
            <h1><?php the_title(); ?></h1>
            <?php if ( '' !== $excerpt ) : ?><p class="aznet-theme-team-directory__lead"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
        </header>

        <?php if ( trim( (string) get_the_content() ) !== '' ) : ?>
            <div class="aznet-theme-team-directory__intro aznet-theme-entry__content"><?php the_content(); ?></div>
        <?php endif; ?>

        <?php if ( [] !== $members ) : ?>
            <div class="aznet-theme-team-directory__grid">
                <?php foreach ( $members as $member ) : ?>
                    <?php get_template_part( 'template-parts/team/card', null, [ 'team_member' => $member ] ); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

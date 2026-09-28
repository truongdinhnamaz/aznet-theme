<?php
/**
 * Presentation for a published direct child of the mapped Team Page.
 *
 * WordPress owns the member title, excerpt, featured image and authored body.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$excerpt = isset( $args['excerpt'] ) ? trim( (string) $args['excerpt'] ) : '';
$crumbs  = isset( $args['crumbs'] ) && is_array( $args['crumbs'] ) ? $args['crumbs'] : [];
$team_url = '';
if ( [] !== $crumbs ) {
    $team_crumb = end( $crumbs );
    if ( is_array( $team_crumb ) && ! empty( $team_crumb['url'] ) ) {
        $team_url = (string) $team_crumb['url'];
    }
}
$contact_url = function_exists( 'AZnet\\Theme\\service_page_contact_url' )
    ? \AZnet\Theme\service_page_contact_url()
    : '';
$portrait_id = function_exists( 'AZnet\\Theme\\team_member_portrait_id' )
    ? \AZnet\Theme\team_member_portrait_id( (int) get_the_ID() )
    : 0;
$has_body = '' !== trim( (string) get_post_field( 'post_content', (int) get_the_ID() ) );
?>
<section class="aznet-theme-team-member" aria-labelledby="aznet-theme-team-member-title">
    <div class="aznet-theme-team-member__shell">
        <?php if ( [] !== $crumbs ) : ?>
            <nav class="aznet-theme-team-member__breadcrumbs" aria-label="<?php echo esc_attr__( 'Breadcrumb', 'aznet-theme' ); ?>">
                <ol>
                    <?php foreach ( $crumbs as $item ) : ?>
                        <li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
                    <?php endforeach; ?>
                    <li aria-current="page"><?php the_title(); ?></li>
                </ol>
            </nav>
        <?php endif; ?>

        <div class="aznet-theme-team-member__hero">
            <div class="aznet-theme-team-member__portrait">
                <?php if ( $portrait_id > 0 ) : ?>
                    <?php echo wp_get_attachment_image( $portrait_id, 'large', false, [ 'fetchpriority' => 'high' ] ); ?>
                <?php else : ?>
                    <div class="aznet-theme-team-member__portrait-placeholder" aria-hidden="true">
                        <span class="aznet-theme-team-member__portrait-placeholder-icon"></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="aznet-theme-team-member__intro">
                <p class="aznet-theme-team-member__eyebrow"><?php esc_html_e( 'Đội ngũ chuyên môn', 'aznet-theme' ); ?></p>
                <h1 id="aznet-theme-team-member-title" class="aznet-theme-team-member__title"><?php the_title(); ?></h1>

                <?php if ( '' !== $excerpt ) : ?>
                    <p class="aznet-theme-team-member__lead"><?php echo esc_html( $excerpt ); ?></p>
                <?php endif; ?>

                <div class="aznet-theme-team-member__rule" aria-hidden="true"></div>

                <div class="aznet-theme-team-member__actions">
                    <?php if ( '' !== $contact_url ) : ?>
                        <a class="aznet-theme-team-member__primary" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?></a>
                    <?php endif; ?>
                    <?php if ( '' !== $team_url ) : ?>
                        <a class="aznet-theme-team-member__secondary" href="<?php echo esc_url( $team_url ); ?>"><?php esc_html_e( 'Xem toàn bộ đội ngũ', 'aznet-theme' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ( $has_body ) : ?>
        <div class="aznet-theme-team-member__body-section">
            <div class="aznet-theme-team-member__body">
                <p class="aznet-theme-team-member__section-label"><?php esc_html_e( 'Hồ sơ chuyên môn', 'aznet-theme' ); ?></p>
                <div class="aznet-theme-entry__content">
                    <?php the_content(); ?>
                    <?php wp_link_pages(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

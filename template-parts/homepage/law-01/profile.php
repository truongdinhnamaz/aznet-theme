<?php
/** Law 01 combined About + Team presentation band. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$about = homepage_page_reference( (int) setting( 'homepage_about_page', 0 ) );
$team = homepage_page_reference( (int) setting( 'homepage_team_page', 0 ) );
$has_about = $about instanceof \WP_Post;
$has_team = $team instanceof \WP_Post;
if ( ! $has_about && ! $has_team ) { return; }

$about_summary = $about instanceof \WP_Post ? trim( (string) get_the_excerpt( $about ) ) : '';
$about_image = $about instanceof \WP_Post && has_post_thumbnail( $about ) ? get_the_post_thumbnail( $about, 'large', [ 'class' => 'aznet-theme-law01-profile__about-image' ] ) : '';
$team_summary = $team instanceof \WP_Post ? trim( (string) get_the_excerpt( $team ) ) : '';
$members = $has_team ? team_directory_members( 4 ) : [];

$services_page = homepage_page_reference( (int) setting( 'homepage_services_page', 0 ) );
$process_page = homepage_page_reference( (int) setting( 'homepage_process_page', 0 ) );
$faq_page = homepage_page_reference( (int) setting( 'homepage_faq_page', 0 ) );
$service_count = $services_page instanceof \WP_Post
    ? count( homepage_direct_published_children( (int) $services_page->ID, 24 ) )
    : 0;
$profile_facts = [];
if ( $service_count > 0 && $services_page instanceof \WP_Post ) {
    $profile_facts[] = [
        'value' => (string) $service_count,
        'label' => __( 'Lĩnh vực dịch vụ', 'aznet-theme' ),
        'url'   => get_permalink( $services_page ),
    ];
}
if ( $process_page instanceof \WP_Post ) {
    $profile_facts[] = [
        'value' => __( 'Quy trình', 'aznet-theme' ),
        'label' => get_the_title( $process_page ),
        'url'   => get_permalink( $process_page ),
    ];
}
if ( $faq_page instanceof \WP_Post ) {
    $profile_facts[] = [
        'value' => __( 'Hỏi đáp', 'aznet-theme' ),
        'label' => get_the_title( $faq_page ),
        'url'   => get_permalink( $faq_page ),
    ];
}

$section_label = $has_team
    ? ( $has_about ? __( 'Giới thiệu và đội ngũ', 'aznet-theme' ) : __( 'Đội ngũ luật sư', 'aznet-theme' ) )
    : __( 'Giới thiệu', 'aznet-theme' );

$container_classes = 'aznet-theme-law01-container aznet-theme-law01-profile__container';
if ( ! $has_team ) {
    $container_classes .= ' aznet-theme-law01-profile__container--about-only';
} elseif ( ! $has_about ) {
    $container_classes .= ' aznet-theme-law01-profile__container--team-only';
}
?>
<section class="aznet-theme-law01-section aznet-theme-law01-profile" aria-label="<?php echo esc_attr( $section_label ); ?>">
<div class="<?php echo esc_attr( $container_classes ); ?>">
    <?php if ( $about instanceof \WP_Post ) : ?>
    <div class="aznet-theme-law01-profile__about-grid">
        <div class="aznet-theme-law01-profile__about-copy aznet-theme-law01-editorial">
            <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Giới thiệu về văn phòng', 'aznet-theme' ); ?></p>
            <h2><?php echo esc_html( get_the_title( $about ) ); ?></h2>
            <?php if ( '' !== $about_summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $about_summary ); ?></p><?php endif; ?>
            <p><a class="aznet-theme-law01-button" href="<?php echo esc_url( get_permalink( $about ) ); ?>"><?php esc_html_e( 'Tìm hiểu thêm', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></p>
            <?php if ( [] !== $profile_facts ) : ?>
                <div class="aznet-theme-law01-profile__facts" aria-label="<?php esc_attr_e( 'Thông tin nổi bật', 'aznet-theme' ); ?>">
                    <?php foreach ( $profile_facts as $fact ) : ?>
                        <a class="aznet-theme-law01-profile__fact" href="<?php echo esc_url( (string) $fact['url'] ); ?>">
                            <strong class="aznet-theme-law01-profile__fact-value"><?php echo esc_html( (string) $fact['value'] ); ?></strong>
                            <span class="aznet-theme-law01-profile__fact-label"><?php echo esc_html( (string) $fact['label'] ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
        <?php if ( [] !== $members ) : ?>
        <div class="aznet-theme-law01-profile__members">
            <?php foreach ( $members as $member ) : ?>
                <?php
                if ( ! $member instanceof \WP_Post ) { continue; }
                get_template_part(
                    'template-parts/team/card',
                    null,
                    [ 'team_member' => $member ]
                );
                ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <p class="aznet-theme-law01-team-more"><a class="aznet-theme-law01-button aznet-theme-law01-button--secondary" href="<?php echo esc_url( get_permalink( $team ) ); ?>"><?php esc_html_e( 'Xem tất cả', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></p>
    </div>
    <?php endif; ?>
</div>
</section>

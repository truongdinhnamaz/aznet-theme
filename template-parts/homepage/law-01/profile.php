<?php
/** Law 01 combined About + Team presentation band. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$settings = settings();

$about = homepage_page_reference( (int) homepage_effective_source_value( 'law-01', 'about', $settings ) );
$about_fallback = false;
if ( ! $about instanceof \WP_Post ) {
    $about = homepage_page_reference( (int) setting( 'homepage_about_page', 0 ) );
    $about_fallback = $about instanceof \WP_Post;
}

$team = homepage_page_reference( (int) homepage_effective_source_value( 'law-01', 'team', $settings ) );
$has_about = $about instanceof \WP_Post;
$has_team = $team instanceof \WP_Post;
if ( ! $has_about && ! $has_team ) { return; }

$about_summary = $has_about ? trim( (string) get_the_excerpt( $about ) ) : '';
$team_summary = $has_team ? trim( (string) get_the_excerpt( $team ) ) : '';
$members = $has_team ? team_directory_public_members( 4 ) : [];

$services_page = homepage_page_reference( (int) homepage_effective_source_value( 'law-01', 'services', $settings ) );
$contact_page = homepage_page_reference( (int) homepage_effective_source_value( 'law-01', 'contact', $settings ) );
$service_count = $services_page instanceof \WP_Post
    ? count( homepage_renderable_child_pages( (int) $services_page->ID, 24 ) )
    : 0;
$member_count = count( $members );

$profile_facts = [];
if ( $service_count > 0 && $services_page instanceof \WP_Post ) {
    $profile_facts[] = [
        'value' => (string) $service_count,
        'label' => __( 'Dịch vụ công khai', 'aznet-theme' ),
        'url'   => get_permalink( $services_page ),
    ];
}
if ( $member_count > 0 && $team instanceof \WP_Post ) {
    $profile_facts[] = [
        'value' => (string) $member_count,
        'label' => __( 'Nhân sự công khai', 'aznet-theme' ),
        'url'   => get_permalink( $team ),
    ];
}
if ( $contact_page instanceof \WP_Post ) {
    $profile_facts[] = [
        'value' => __( 'Liên hệ', 'aznet-theme' ),
        'label' => __( 'Thông tin văn phòng', 'aznet-theme' ),
        'url'   => get_permalink( $contact_page ),
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
if ( $about_fallback ) {
    $container_classes .= ' aznet-theme-law01-profile__about-fallback';
}
?>
<section id="aznet-homepage-profile" data-aznet-homepage-surface="profile" class="aznet-theme-law01-section aznet-theme-law01-profile" aria-label="<?php echo esc_attr( $section_label ); ?>">
<div class="<?php echo esc_attr( $container_classes ); ?>">
    <?php if ( $about instanceof \WP_Post ) : ?>
    <div class="aznet-theme-law01-profile__about-grid">
        <div class="aznet-theme-law01-profile__about-copy aznet-theme-law01-editorial">
            <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Giới thiệu về văn phòng', 'aznet-theme' ); ?></p>
            <h2><?php echo esc_html( get_the_title( $about ) ); ?></h2>
            <?php if ( '' !== $about_summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $about_summary ); ?></p><?php endif; ?>
            <p class="aznet-theme-law01-profile__about-action"><a class="aznet-theme-law01-button" href="<?php echo esc_url( get_permalink( $about ) ); ?>"><?php esc_html_e( 'Tìm hiểu thêm', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></p>
            <?php if ( [] !== $profile_facts ) : ?>
                <div class="aznet-theme-law01-profile__stats" aria-label="<?php esc_attr_e( 'Thông tin công khai', 'aznet-theme' ); ?>">
                    <?php foreach ( array_slice( $profile_facts, 0, 3 ) as $fact ) : ?>
                        <a class="aznet-theme-law01-profile__fact" href="<?php echo esc_url( (string) $fact['url'] ); ?>">
                            <strong class="aznet-theme-law01-profile__fact-value"><?php echo esc_html( (string) $fact['value'] ); ?></strong>
                            <span class="aznet-theme-law01-profile__fact-label"><?php echo esc_html( (string) $fact['label'] ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( $team instanceof \WP_Post ) : ?>
    <div class="aznet-theme-law01-profile__team-band aznet-theme-law01-team">
        <div class="aznet-theme-law01-profile__team-heading">
            <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Đội ngũ luật sư', 'aznet-theme' ); ?></p>
            <h2><?php echo esc_html( get_the_title( $team ) ); ?></h2>
            <?php if ( '' !== $team_summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $team_summary ); ?></p><?php endif; ?>
        </div>
        <?php if ( [] !== $members ) : ?>
        <div class="aznet-theme-law01-profile__members" data-count="<?php echo esc_attr( (string) count( $members ) ); ?>">
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
        <p class="aznet-theme-law01-team-more"><a class="aznet-theme-law01-button aznet-theme-law01-button--secondary" href="<?php echo esc_url( get_permalink( $team ) ); ?>"><?php esc_html_e( 'Xem thêm về đội ngũ', 'aznet-theme' ); ?> <span aria-hidden="true">→</span></a></p>
    </div>
    <?php endif; ?>
</div>
</section>

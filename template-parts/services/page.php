<?php
/**
 * Premium Law 01 mapped Services Page presentation.
 *
 * WordPress owns the Page body and child service Pages. The Theme only composes
 * their public presentation from the explicit Theme Content Map relationship.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$excerpt = trim( (string) ( $args['excerpt'] ?? '' ) );
$services = \AZnet\Theme\services_page_children();
$contact_url = \AZnet\Theme\service_page_contact_url();
$lead = '' !== $excerpt
    ? $excerpt
    : __( 'Khám phá các nhóm dịch vụ pháp lý và chọn nội dung phù hợp với nhu cầu cần trao đổi.', 'aznet-theme' );
$authored_content = trim( (string) get_the_content() );
?>
<div class="aznet-theme-services-page">
    <header class="aznet-theme-services-page__hero">
        <div class="aznet-theme-services-page__hero-copy">
            <p class="aznet-theme-services-page__eyebrow"><?php esc_html_e( 'Dịch vụ pháp lý', 'aznet-theme' ); ?></p>
            <h1 class="aznet-theme-services-page__title"><?php the_title(); ?></h1>
            <p class="aznet-theme-services-page__lead"><?php echo esc_html( $lead ); ?></p>

            <?php if ( [] !== $services || '' !== $contact_url ) : ?>
                <div class="aznet-theme-services-page__hero-actions">
                    <?php if ( [] !== $services ) : ?>
                        <a class="aznet-theme-services-page__primary" href="#aznet-theme-services-list">
                            <?php esc_html_e( 'Xem các lĩnh vực', 'aznet-theme' ); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( '' !== $contact_url ) : ?>
                        <a class="aznet-theme-services-page__secondary" href="<?php echo esc_url( $contact_url ); ?>">
                            <?php esc_html_e( 'Trao đổi nhu cầu', 'aznet-theme' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="aznet-theme-services-page__hero-emblem" aria-hidden="true">
            <span></span>
        </div>
    </header>

    <?php if ( '' !== $authored_content ) : ?>
        <section class="aznet-theme-services-page__intro" aria-label="<?php esc_attr_e( 'Giới thiệu dịch vụ', 'aznet-theme' ); ?>">
            <div class="aznet-theme-services-page__intro-mark" aria-hidden="true">§</div>
            <div class="aznet-theme-services-page__content aznet-theme-entry__content">
                <?php the_content(); ?>
                <?php wp_link_pages(); ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( [] !== $services ) : ?>
        <section id="aznet-theme-services-list" class="aznet-theme-services-page__services" aria-labelledby="aznet-theme-services-heading">
            <header class="aznet-theme-services-page__section-heading">
                <p class="aznet-theme-services-page__section-kicker"><?php esc_html_e( 'Lĩnh vực hỗ trợ', 'aznet-theme' ); ?></p>
                <h2 id="aznet-theme-services-heading"><?php esc_html_e( 'Chọn nhóm dịch vụ phù hợp', 'aznet-theme' ); ?></h2>
                <p><?php esc_html_e( 'Mỗi trang dịch vụ trình bày phạm vi hỗ trợ và những thông tin nên chuẩn bị trước khi trao đổi.', 'aznet-theme' ); ?></p>
            </header>

            <div class="aznet-theme-services-page__grid">
                <?php foreach ( $services as $index => $service_page ) : ?>
                    <?php
                    $service_url = get_permalink( $service_page );
                    if ( ! is_string( $service_url ) || '' === $service_url ) {
                        continue;
                    }
                    $service_excerpt = trim( (string) $service_page->post_excerpt );
                    ?>
                    <article class="aznet-theme-services-page__card">
                        <div class="aznet-theme-services-page__card-top">
                            <span class="aznet-theme-services-page__card-index" aria-hidden="true">
                                <?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?>
                            </span>
                            <span class="aznet-theme-services-page__card-icon" aria-hidden="true"></span>
                        </div>
                        <h3 class="aznet-theme-services-page__card-title">
                            <a href="<?php echo esc_url( $service_url ); ?>"><?php echo esc_html( get_the_title( $service_page ) ); ?></a>
                        </h3>
                        <?php if ( '' !== $service_excerpt ) : ?>
                            <p class="aznet-theme-services-page__card-excerpt"><?php echo esc_html( $service_excerpt ); ?></p>
                        <?php endif; ?>
                        <a class="aznet-theme-services-page__card-link" href="<?php echo esc_url( $service_url ); ?>">
                            <?php esc_html_e( 'Xem dịch vụ', 'aznet-theme' ); ?> <span aria-hidden="true">→</span>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( '' !== $contact_url ) : ?>
        <section class="aznet-theme-services-page__consultation" aria-labelledby="aznet-theme-services-consultation-title">
            <div>
                <p class="aznet-theme-services-page__section-kicker"><?php esc_html_e( 'Cần trao đổi trực tiếp?', 'aznet-theme' ); ?></p>
                <h2 id="aznet-theme-services-consultation-title"><?php esc_html_e( 'Chưa rõ nên bắt đầu từ dịch vụ nào?', 'aznet-theme' ); ?></h2>
                <p><?php esc_html_e( 'Gửi thông tin khái quát về vấn đề để văn phòng có cơ sở tiếp nhận và hướng dẫn bước trao đổi tiếp theo.', 'aznet-theme' ); ?></p>
            </div>
            <a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Yêu cầu tư vấn', 'aznet-theme' ); ?></a>
        </section>
    <?php endif; ?>
</div>

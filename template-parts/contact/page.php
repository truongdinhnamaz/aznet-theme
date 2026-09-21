<?php
/**
 * Premium Law 01 Contact Page presentation.
 *
 * WordPress owns the authored Page content. RootProfile, when available,
 * remains authoritative for organization/contact facts exposed through its
 * public Provider v1 contract.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$excerpt = trim( (string) ( $args['excerpt'] ?? '' ) );
$site_title = trim( (string) get_bloginfo( 'name' ) );
$authored_content = trim( (string) get_the_content() );
$provider_model = function_exists( 'AZnet\\Theme\\contact_surface_model' )
    ? \AZnet\Theme\contact_surface_model()
    : null;
$has_provider = is_array( $provider_model ) && [] !== $provider_model;

$lead = '' !== $excerpt
    ? $excerpt
    : __( 'Gửi thông tin cần trao đổi để văn phòng có cơ sở liên hệ và tiếp nhận yêu cầu của bạn.', 'aznet-theme' );
?>
<div class="aznet-theme-contact-page">
    <header class="aznet-theme-contact-page__hero">
        <div class="aznet-theme-contact-page__hero-copy">
            <p class="aznet-theme-contact-page__eyebrow">
                <?php
                echo esc_html(
                    '' !== $site_title
                        ? sprintf( __( 'Liên hệ %s', 'aznet-theme' ), $site_title )
                        : __( 'Liên hệ văn phòng luật sư', 'aznet-theme' )
                );
                ?>
            </p>
            <h1 class="aznet-theme-contact-page__title"><?php the_title(); ?></h1>
            <p class="aznet-theme-contact-page__lead"><?php echo esc_html( $lead ); ?></p>
            <div class="aznet-theme-contact-page__actions">
                <a class="aznet-theme-contact-page__primary" href="#aznet-theme-contact-request">
                    <?php esc_html_e( 'Gửi yêu cầu tư vấn', 'aznet-theme' ); ?>
                </a>
                <?php if ( $has_provider ) : ?>
                    <a class="aznet-theme-contact-page__secondary" href="#aznet-theme-contact-details">
                        <?php esc_html_e( 'Xem thông tin liên hệ', 'aznet-theme' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="aznet-theme-contact-page__hero-mark" aria-hidden="true">
            <span></span>
        </div>
    </header>

    <div class="aznet-theme-contact-page__body">
        <section id="aznet-theme-contact-request" class="aznet-theme-contact-page__content-card" aria-labelledby="aznet-theme-contact-request-title">
            <p class="aznet-theme-contact-page__section-kicker"><?php esc_html_e( 'Trao đổi nhu cầu', 'aznet-theme' ); ?></p>
            <h2 id="aznet-theme-contact-request-title"><?php esc_html_e( 'Gửi thông tin để được tiếp nhận thuận tiện', 'aznet-theme' ); ?></h2>
            <div class="aznet-theme-contact-page__content aznet-theme-entry__content">
                <?php if ( '' !== $authored_content ) : ?>
                    <?php the_content(); ?>
                    <?php wp_link_pages(); ?>
                <?php else : ?>
                    <p><?php esc_html_e( 'Bạn có thể để lại thông tin liên hệ và nội dung cần trao đổi. Nội dung chi tiết của trang này vẫn được quản lý trực tiếp trong WordPress.', 'aznet-theme' ); ?></p>
                <?php endif; ?>
            </div>
        </section>

        <aside class="aznet-theme-contact-page__prep" aria-labelledby="aznet-theme-contact-prep-title">
            <p class="aznet-theme-contact-page__section-kicker"><?php esc_html_e( 'Chuẩn bị trước khi liên hệ', 'aznet-theme' ); ?></p>
            <h2 id="aznet-theme-contact-prep-title"><?php esc_html_e( 'Thông tin giúp việc trao đổi rõ ràng hơn', 'aznet-theme' ); ?></h2>
            <ul class="aznet-theme-contact-page__checklist">
                <li><?php esc_html_e( 'Họ tên và cách thức liên hệ thuận tiện.', 'aznet-theme' ); ?></li>
                <li><?php esc_html_e( 'Tóm tắt vấn đề hoặc nhu cầu cần tư vấn.', 'aznet-theme' ); ?></li>
                <li><?php esc_html_e( 'Mốc thời gian, sự kiện hoặc tài liệu liên quan nếu có.', 'aznet-theme' ); ?></li>
                <li><?php esc_html_e( 'Câu hỏi cụ thể bạn muốn được làm rõ.', 'aznet-theme' ); ?></li>
            </ul>
            <p class="aznet-theme-contact-page__note">
                <?php esc_html_e( 'Không cần đưa thông tin nhạy cảm vào nội dung công khai trên website.', 'aznet-theme' ); ?>
            </p>
        </aside>
    </div>

    <?php if ( $has_provider ) : ?>
        <section id="aznet-theme-contact-details" class="aznet-theme-contact-page__provider" aria-labelledby="aznet-theme-contact-details-title">
            <div class="aznet-theme-contact-page__provider-heading">
                <p class="aznet-theme-contact-page__section-kicker"><?php esc_html_e( 'Thông tin chính thức', 'aznet-theme' ); ?></p>
                <h2 id="aznet-theme-contact-details-title"><?php esc_html_e( 'Kênh liên hệ và địa chỉ', 'aznet-theme' ); ?></h2>
            </div>
            <?php \AZnet\Theme\render_contact_surface( $provider_model ); ?>
        </section>
    <?php endif; ?>
</div>

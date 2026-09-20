<?php
/**
 * Shared native WordPress Page presentation.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = (int) get_the_ID();
$variant = \AZnet\Theme\page_variant( $post_id );
$crumbs  = \AZnet\Theme\page_breadcrumb_items( $post_id );
$excerpt = \AZnet\Theme\page_excerpt( $post_id );
$is_service_detail = \AZnet\Theme\service_page_is_detail( $post_id );
$is_service_hub = \AZnet\Theme\service_hub_is_current_page( $post_id );
$service_contact_url = ( $is_service_detail || $is_service_hub ) ? \AZnet\Theme\service_page_contact_url() : '';
$service_siblings = $is_service_detail ? \AZnet\Theme\service_page_siblings( $post_id ) : [];
$service_hub_items = $is_service_hub ? \AZnet\Theme\service_hub_items( 6 ) : [];
$service_hub_posts = $is_service_hub ? \AZnet\Theme\service_hub_latest_posts( 3 ) : [];
$service_hub_process = $is_service_hub ? \AZnet\Theme\service_hub_support_page( 'process' ) : null;
$service_hub_faq = $is_service_hub ? \AZnet\Theme\service_hub_support_page( 'faq' ) : null;
$service_hub_post = $is_service_hub ? get_post( $post_id ) : null;
$service_hub_has_editorial = $service_hub_post instanceof \WP_Post && '' !== trim( (string) $service_hub_post->post_content );

$article_classes = 'aznet-theme-page aznet-theme-page--' . $variant;
if ( $is_service_detail ) {
    $article_classes .= ' aznet-theme-page--service-detail';
}
if ( $is_service_hub ) {
    $article_classes .= ' aznet-theme-page--service-hub';
}

$card_summary = static function ( \WP_Post $post, int $words = 24 ): string {
    $summary = trim( (string) $post->post_excerpt );
    if ( '' !== $summary ) {
        return $summary;
    }

    $content = trim( wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) ) );
    return '' !== $content ? wp_trim_words( $content, $words, '…' ) : '';
};
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $article_classes ); ?>>
    <header class="aznet-theme-page__header<?php echo $is_service_hub ? ' aznet-theme-service-hub__hero' : ''; ?>">
        <?php if ( [] !== $crumbs ) : ?>
            <nav class="aznet-theme-page__breadcrumbs" aria-label="<?php echo esc_attr__( 'Breadcrumb', 'aznet-theme' ); ?>">
                <ol>
                    <?php foreach ( $crumbs as $item ) : ?>
                        <li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
                    <?php endforeach; ?>
                    <li aria-current="page"><?php the_title(); ?></li>
                </ol>
            </nav>
        <?php endif; ?>

        <?php if ( $is_service_hub ) : ?>
            <p class="aznet-theme-service-hub__eyebrow"><?php esc_html_e( 'Dịch vụ pháp lý', 'aznet-theme' ); ?></p>
        <?php endif; ?>

        <h1 class="aznet-theme-page__title"><?php the_title(); ?></h1>

        <?php if ( '' !== $excerpt ) : ?>
            <p class="aznet-theme-page__lead"><?php echo esc_html( $excerpt ); ?></p>
        <?php elseif ( $is_service_hub ) : ?>
            <p class="aznet-theme-page__lead aznet-theme-service-hub__hero-intro"><?php esc_html_e( 'Chọn lĩnh vực gần với nhu cầu của bạn hoặc gửi thông tin ban đầu để bắt đầu trao đổi.', 'aznet-theme' ); ?></p>
        <?php endif; ?>

        <?php if ( $is_service_hub && [] !== $service_hub_items ) : ?>
            <p class="aznet-theme-service-hub__hero-meta"><strong><?php echo esc_html( (string) count( $service_hub_items ) ); ?></strong> <?php esc_html_e( 'lĩnh vực hỗ trợ', 'aznet-theme' ); ?></p>
        <?php endif; ?>

        <?php if ( $is_service_hub ) : ?>
            <div class="aznet-theme-service-hub__hero-actions">
                <?php if ( [] !== $service_hub_items ) : ?>
                    <a class="aznet-theme-service-hub__primary" href="#aznet-theme-service-selection"><?php esc_html_e( 'Xem lĩnh vực hỗ trợ', 'aznet-theme' ); ?></a>
                <?php endif; ?>
                <?php if ( '' !== $service_contact_url ) : ?>
                    <a class="aznet-theme-service-hub__secondary" href="<?php echo esc_url( $service_contact_url ); ?>"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?></a>
                <?php endif; ?>
            </div>
        <?php elseif ( $is_service_detail ) : ?>
            <div class="aznet-theme-page__service-actions">
                <?php if ( '' !== $service_contact_url ) : ?>
                    <a class="aznet-theme-page__service-primary" href="<?php echo esc_url( $service_contact_url ); ?>"><?php esc_html_e( 'Trao đổi nhu cầu', 'aznet-theme' ); ?></a>
                <?php endif; ?>
                <?php if ( [] !== $crumbs ) : ?>
                    <?php $services_crumb = end( $crumbs ); ?>
                    <?php if ( is_array( $services_crumb ) && ! empty( $services_crumb['url'] ) ) : ?>
                        <a class="aznet-theme-page__service-secondary" href="<?php echo esc_url( (string) $services_crumb['url'] ); ?>"><?php esc_html_e( 'Xem tất cả dịch vụ', 'aznet-theme' ); ?></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ( has_post_thumbnail() ) : ?>
            <figure class="aznet-theme-page__featured">
                <?php the_post_thumbnail( 'large' ); ?>
            </figure>
        <?php endif; ?>
    </header>

    <?php if ( $is_service_hub ) : ?>
        <div class="aznet-theme-service-hub">
            <?php if ( [] !== $service_hub_items ) : ?>
                <section id="aznet-theme-service-selection" class="aznet-theme-service-hub__section aznet-theme-service-hub__selection" aria-labelledby="aznet-theme-service-selection-title">
                    <div class="aznet-theme-service-hub__section-heading">
                        <p class="aznet-theme-service-hub__eyebrow"><?php esc_html_e( 'Lĩnh vực hỗ trợ', 'aznet-theme' ); ?></p>
                        <h2 id="aznet-theme-service-selection-title"><?php esc_html_e( 'Chọn vấn đề gần với nhu cầu của bạn', 'aznet-theme' ); ?></h2>
                    </div>
                    <div class="aznet-theme-service-hub__grid">
                        <?php foreach ( $service_hub_items as $service_index => $service_page ) : ?>
                            <?php
                            $service_url = get_permalink( $service_page );
                            if ( ! is_string( $service_url ) || '' === $service_url ) {
                                continue;
                            }
                            $service_summary = $card_summary( $service_page );
                            ?>
                            <article class="aznet-theme-service-hub__card">
                                <div class="aznet-theme-service-hub__card-number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', (int) $service_index + 1 ) ); ?></div>
                                <h3><a href="<?php echo esc_url( $service_url ); ?>"><?php echo esc_html( get_the_title( $service_page ) ); ?></a></h3>
                                <?php if ( '' !== $service_summary ) : ?>
                                    <p><?php echo esc_html( $service_summary ); ?></p>
                                <?php endif; ?>
                                <a class="aznet-theme-service-hub__card-link" href="<?php echo esc_url( $service_url ); ?>"><?php esc_html_e( 'Xem chi tiết', 'aznet-theme' ); ?><span aria-hidden="true"> →</span></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ( '' !== $service_contact_url ) : ?>
                <section class="aznet-theme-service-hub__section aznet-theme-service-hub__orientation" aria-labelledby="aznet-theme-service-orientation-title">
                    <div>
                        <p class="aznet-theme-service-hub__eyebrow"><?php esc_html_e( 'Chưa biết bắt đầu từ đâu?', 'aznet-theme' ); ?></p>
                        <h2 id="aznet-theme-service-orientation-title"><?php esc_html_e( 'Bạn không cần tự phân loại vấn đề trước khi liên hệ', 'aznet-theme' ); ?></h2>
                        <p><?php esc_html_e( 'Hãy gửi thông tin ban đầu. Đơn vị tư vấn sẽ tiếp nhận và trao đổi bước phù hợp dựa trên nội dung bạn cung cấp.', 'aznet-theme' ); ?></p>
                    </div>
                    <a class="aznet-theme-service-hub__primary" href="<?php echo esc_url( $service_contact_url ); ?>"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?></a>
                </section>
            <?php endif; ?>

            <?php if ( $service_hub_has_editorial ) : ?>
                <section class="aznet-theme-service-hub__section aznet-theme-service-hub__editorial" aria-label="<?php echo esc_attr__( 'Thông tin dịch vụ', 'aznet-theme' ); ?>">
                    <div class="aznet-theme-page__content aznet-theme-entry__content">
                        <?php the_content(); ?>
                        <?php wp_link_pages(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ( $service_hub_process instanceof \WP_Post || $service_hub_faq instanceof \WP_Post ) : ?>
                <section class="aznet-theme-service-hub__section aznet-theme-service-hub__support" aria-labelledby="aznet-theme-service-support-title">
                    <div class="aznet-theme-service-hub__section-heading">
                        <p class="aznet-theme-service-hub__eyebrow"><?php esc_html_e( 'Trước khi trao đổi', 'aznet-theme' ); ?></p>
                        <h2 id="aznet-theme-service-support-title"><?php esc_html_e( 'Thông tin giúp bạn chủ động hơn', 'aznet-theme' ); ?></h2>
                    </div>
                    <div class="aznet-theme-service-hub__support-grid">
                        <?php foreach ( [ $service_hub_process, $service_hub_faq ] as $support_page ) : ?>
                            <?php if ( ! $support_page instanceof \WP_Post ) { continue; } ?>
                            <?php $support_url = get_permalink( $support_page ); ?>
                            <?php if ( ! is_string( $support_url ) || '' === $support_url ) { continue; } ?>
                            <article class="aznet-theme-service-hub__support-card">
                                <h3><a href="<?php echo esc_url( $support_url ); ?>"><?php echo esc_html( get_the_title( $support_page ) ); ?></a></h3>
                                <?php $support_summary = $card_summary( $support_page, 30 ); ?>
                                <?php if ( '' !== $support_summary ) : ?><p><?php echo esc_html( $support_summary ); ?></p><?php endif; ?>
                                <a href="<?php echo esc_url( $support_url ); ?>"><?php esc_html_e( 'Xem thêm', 'aznet-theme' ); ?><span aria-hidden="true"> →</span></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ( [] !== $service_hub_posts ) : ?>
                <section class="aznet-theme-service-hub__section aznet-theme-service-hub__knowledge" aria-labelledby="aznet-theme-service-knowledge-title">
                    <div class="aznet-theme-service-hub__section-heading">
                        <p class="aznet-theme-service-hub__eyebrow"><?php esc_html_e( 'Kiến thức pháp lý', 'aznet-theme' ); ?></p>
                        <h2 id="aznet-theme-service-knowledge-title"><?php esc_html_e( 'Bài viết mới', 'aznet-theme' ); ?></h2>
                    </div>
                    <div class="aznet-theme-service-hub__knowledge-grid">
                        <?php foreach ( $service_hub_posts as $knowledge_post ) : ?>
                            <?php $knowledge_url = get_permalink( $knowledge_post ); ?>
                            <?php if ( ! is_string( $knowledge_url ) || '' === $knowledge_url ) { continue; } ?>
                            <article class="aznet-theme-service-hub__knowledge-card">
                                <p class="aznet-theme-service-hub__meta"><?php echo esc_html( get_the_date( '', $knowledge_post ) ); ?></p>
                                <h3><a href="<?php echo esc_url( $knowledge_url ); ?>"><?php echo esc_html( get_the_title( $knowledge_post ) ); ?></a></h3>
                                <?php $knowledge_summary = $card_summary( $knowledge_post, 28 ); ?>
                                <?php if ( '' !== $knowledge_summary ) : ?><p><?php echo esc_html( $knowledge_summary ); ?></p><?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ( '' !== $service_contact_url ) : ?>
                <section class="aznet-theme-service-hub__section aznet-theme-service-hub__final-cta" aria-labelledby="aznet-theme-service-final-title">
                    <div>
                        <p class="aznet-theme-service-hub__eyebrow"><?php esc_html_e( 'Trao đổi trực tiếp', 'aznet-theme' ); ?></p>
                        <h2 id="aznet-theme-service-final-title"><?php esc_html_e( 'Bắt đầu từ thông tin bạn đang có', 'aznet-theme' ); ?></h2>
                        <p><?php esc_html_e( 'Liên hệ để trao đổi nhu cầu và xác định bước tiếp theo phù hợp.', 'aznet-theme' ); ?></p>
                    </div>
                    <a class="aznet-theme-service-hub__primary" href="<?php echo esc_url( $service_contact_url ); ?>"><?php esc_html_e( 'Liên hệ tư vấn', 'aznet-theme' ); ?></a>
                </section>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <div class="aznet-theme-page__content aznet-theme-entry__content">
            <?php the_content(); ?>
            <?php wp_link_pages(); ?>
        </div>
    <?php endif; ?>

    <?php if ( $is_service_detail && [] !== $service_siblings ) : ?>
        <aside class="aznet-theme-page__service-siblings" aria-labelledby="aznet-theme-service-siblings-title">
            <div class="aznet-theme-page__service-siblings-header">
                <p class="aznet-theme-page__service-eyebrow"><?php esc_html_e( 'Dịch vụ pháp lý', 'aznet-theme' ); ?></p>
                <h2 id="aznet-theme-service-siblings-title"><?php esc_html_e( 'Các dịch vụ khác', 'aznet-theme' ); ?></h2>
            </div>
            <div class="aznet-theme-page__service-siblings-grid">
                <?php foreach ( $service_siblings as $service_page ) : ?>
                    <?php $service_url = get_permalink( $service_page ); ?>
                    <?php if ( ! is_string( $service_url ) || '' === $service_url ) { continue; } ?>
                    <article class="aznet-theme-page__service-card">
                        <h3><a href="<?php echo esc_url( $service_url ); ?>"><?php echo esc_html( get_the_title( $service_page ) ); ?></a></h3>
                        <?php $service_excerpt = trim( (string) $service_page->post_excerpt ); ?>
                        <?php if ( '' !== $service_excerpt ) : ?>
                            <p><?php echo esc_html( $service_excerpt ); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </aside>
    <?php endif; ?>
</article>

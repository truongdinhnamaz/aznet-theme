<?php
/**
 * Choice 1 — Law 01 legal-site category archive.
 *
 * Consumes the native WordPress main loop without replacing or mutating it.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$category_title = single_cat_title( '', false );
$category_description = category_description();
$contact_url = \AZnet\Theme\archive_contact_page_url();

$featured_html = '';
$latest_html = '';
$list_html = '';
$post_index = 0;

while ( have_posts() ) {
    the_post();

    $variant = 0 === $post_index
        ? 'featured'
        : ( $post_index <= 3 ? 'latest' : 'standard' );

    ob_start();
    get_template_part(
        'template-parts/archive/law01-item',
        null,
        [
            'variant' => $variant,
        ]
    );
    $rendered = (string) ob_get_clean();

    if ( 'featured' === $variant ) {
        $featured_html .= $rendered;
    } elseif ( 'latest' === $variant ) {
        $latest_html .= $rendered;
    } else {
        $list_html .= $rendered;
    }

    ++$post_index;
}
?>
<section class="aznet-theme-law01-archive" aria-labelledby="aznet-theme-law01-archive-title">
    <header class="aznet-theme-law01-archive__header">
        <p class="aznet-theme-law01-archive__eyebrow"><?php esc_html_e( 'Chuyên trang pháp lý', 'aznet-theme' ); ?></p>
        <h1 id="aznet-theme-law01-archive-title" class="aznet-theme-law01-archive__title"><?php echo esc_html( $category_title ); ?></h1>
        <?php if ( '' !== trim( wp_strip_all_tags( $category_description ) ) ) : ?>
            <div class="aznet-theme-law01-archive__description"><?php echo wp_kses_post( $category_description ); ?></div>
        <?php endif; ?>
        <span class="aznet-theme-law01-archive__rule" aria-hidden="true"></span>
    </header>

    <?php if ( '' !== $featured_html ) : ?>
        <section class="aznet-theme-law01-archive__featured" aria-labelledby="aznet-theme-law01-featured-title">
            <div class="aznet-theme-law01-archive__section-heading">
                <p><?php esc_html_e( 'Bài viết nổi bật', 'aznet-theme' ); ?></p>
                <h2 id="aznet-theme-law01-featured-title"><?php esc_html_e( 'Nội dung đáng chú ý', 'aznet-theme' ); ?></h2>
            </div>
            <?php echo $featured_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered Theme template part. ?>
        </section>
    <?php endif; ?>

    <?php if ( '' !== $latest_html ) : ?>
        <section class="aznet-theme-law01-archive__latest" aria-labelledby="aznet-theme-law01-latest-title">
            <div class="aznet-theme-law01-archive__section-heading">
                <p><?php esc_html_e( 'Mới cập nhật', 'aznet-theme' ); ?></p>
                <h2 id="aznet-theme-law01-latest-title"><?php esc_html_e( 'Các bài viết mới', 'aznet-theme' ); ?></h2>
            </div>
            <div class="aznet-theme-law01-archive__latest-grid">
                <?php echo $latest_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered Theme template parts. ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="aznet-theme-law01-archive__all" aria-labelledby="aznet-theme-law01-all-title">
        <div class="aznet-theme-law01-archive__section-heading">
            <p><?php esc_html_e( 'Thư viện bài viết', 'aznet-theme' ); ?></p>
            <h2 id="aznet-theme-law01-all-title"><?php esc_html_e( 'Tất cả bài viết', 'aznet-theme' ); ?></h2>
        </div>

        <div class="aznet-theme-law01-archive__editorial-layout">
            <div class="aznet-theme-law01-archive__editorial-list">
                <?php if ( '' !== $list_html ) : ?>
                    <?php echo $list_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered Theme template parts. ?>
                <?php else : ?>
                    <p class="aznet-theme-law01-archive__continuation">
                        <?php esc_html_e( 'Các bài viết tiếp theo sẽ được hiển thị tại đây khi chuyên mục có thêm nội dung.', 'aznet-theme' ); ?>
                    </p>
                <?php endif; ?>
            </div>

            <aside class="aznet-theme-law01-archive__sidebar" aria-label="<?php esc_attr_e( 'Điều hướng chuyên mục và tư vấn', 'aznet-theme' ); ?>">
                <section class="aznet-theme-law01-archive__sidebar-card">
                    <p class="aznet-theme-law01-archive__sidebar-kicker"><?php esc_html_e( 'Khám phá nội dung', 'aznet-theme' ); ?></p>
                    <h2><?php esc_html_e( 'Chuyên mục pháp lý', 'aznet-theme' ); ?></h2>
                    <nav aria-label="<?php esc_attr_e( 'Danh sách chuyên mục', 'aznet-theme' ); ?>">
                        <ul class="aznet-theme-law01-archive__category-list">
                            <?php
                            wp_list_categories(
                                [
                                    'title_li'   => '',
                                    'hide_empty' => true,
                                    'depth'      => 1,
                                ]
                            );
                            ?>
                        </ul>
                    </nav>
                </section>

                <?php if ( '' !== $contact_url ) : ?>
                    <section class="aznet-theme-law01-archive__consultation">
                        <p class="aznet-theme-law01-archive__sidebar-kicker"><?php esc_html_e( 'Trao đổi trực tiếp', 'aznet-theme' ); ?></p>
                        <h2><?php esc_html_e( 'Bạn cần tư vấn về vấn đề pháp lý?', 'aznet-theme' ); ?></h2>
                        <p><?php esc_html_e( 'Gửi thông tin để Văn phòng luật sư có cơ sở tiếp nhận và trao đổi phù hợp với nhu cầu của bạn.', 'aznet-theme' ); ?></p>
                        <a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Yêu cầu tư vấn', 'aznet-theme' ); ?></a>
                    </section>
                <?php endif; ?>
            </aside>
        </div>
    </section>

    <div class="aznet-theme-listing__pagination aznet-theme-law01-archive__pagination">
        <?php
        the_posts_pagination(
            [
                'mid_size'   => 1,
                'prev_text'  => esc_html__( '← Trước', 'aznet-theme' ),
                'next_text'  => esc_html__( 'Sau →', 'aznet-theme' ),
                'aria_label' => esc_attr__( 'Phân trang chuyên mục', 'aznet-theme' ),
            ]
        );
        ?>
    </div>
</section>

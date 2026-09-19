<?php
/** Homepage presentation/reference configuration. */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\homepage_category_reference;
use function AZnet\Theme\homepage_category_references;
use function AZnet\Theme\homepage_direct_published_children;
use function AZnet\Theme\homepage_latest_posts;
use function AZnet\Theme\homepage_page_reference;
use function AZnet\Theme\settings;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Return read-only diagnostics for configured Homepage sources.
 *
 * @return array<string, string>
 */
function homepage_slot_statuses(): array {
    $s = settings();
    $statuses = [];
    $page_slots = [
        'hero'     => 'homepage_hero_page',
        'services' => 'homepage_services_page',
        'about'    => 'homepage_about_page',
        'team'     => 'homepage_team_page',
        'process'  => 'homepage_process_page',
        'faq'      => 'homepage_faq_page',
        'contact'  => 'homepage_contact_page',
    ];

    foreach ( $page_slots as $slot => $key ) {
        $id = (int) ( $s[ $key ] ?? 0 );
        if ( $id <= 0 ) {
            $statuses[ $slot ] = 'UNMAPPED';
            continue;
        }
        if ( null === homepage_page_reference( $id ) ) {
            $statuses[ $slot ] = 'INVALID';
            continue;
        }
        if ( 'services' === $slot && [] === homepage_direct_published_children( $id, 1 ) ) {
            $statuses[ $slot ] = 'EMPTY';
            continue;
        }
        $statuses[ $slot ] = 'READY';
    }

    $knowledge_ids = (array) ( $s['homepage_knowledge_terms'] ?? [] );
    if ( [] === $knowledge_ids ) {
        $statuses['knowledge'] = 'UNMAPPED';
    } else {
        $valid = homepage_category_references( $knowledge_ids );
        $statuses['knowledge'] = [] === $valid ? 'INVALID' : ( [] === homepage_latest_posts( $knowledge_ids, 1 ) ? 'EMPTY' : 'READY' );
    }

    foreach ( [ 'case_analysis' => 'homepage_case_analysis_term', 'legal_news' => 'homepage_legal_news_term' ] as $slot => $key ) {
        $id = (int) ( $s[ $key ] ?? 0 );
        if ( $id <= 0 ) {
            $statuses[ $slot ] = 'UNMAPPED';
        } elseif ( null === homepage_category_reference( $id ) ) {
            $statuses[ $slot ] = 'INVALID';
        } else {
            $statuses[ $slot ] = [] === homepage_latest_posts( [ $id ], 1 ) ? 'EMPTY' : 'READY';
        }
    }

    $statuses['contact_provider'] = function_exists( 'AZnet\\Theme\\Integrations\\RootProfile\\provider_available' )
        && \AZnet\Theme\Integrations\RootProfile\provider_available()
            ? 'READY'
            : 'PROVIDER_UNAVAILABLE';

    return $statuses;
}

/** Render one Page selector. */
function homepage_page_select( string $key, string $label, int $current, array $pages ): void {
    echo '<label class="aznet-theme-field"><span>' . esc_html( $label ) . '</span><select name="aznet_theme_settings[' . esc_attr( $key ) . ']">';
    echo '<option value="0">' . esc_html__( '— Chưa chọn —', 'aznet-theme' ) . '</option>';
    foreach ( $pages as $page ) {
        if ( ! $page instanceof \WP_Post ) { continue; }
        echo '<option value="' . esc_attr( (string) $page->ID ) . '" ' . selected( $current, (int) $page->ID, false ) . '>' . esc_html( get_the_title( $page ) ) . '</option>';
    }
    echo '</select></label>';
}

/**
 * Render the Homepage Hero authoring bridge without duplicating WordPress content.
 *
 * @param array<string, mixed> $settings Normalized Theme presentation settings.
 * @param array<int, \WP_Post> $pages Published WordPress Pages.
 */
function render_homepage_hero_editor( array $settings, array $pages ): void {
    $hero_id = (int) ( $settings['homepage_hero_page'] ?? 0 );
    $hero = homepage_page_reference( $hero_id );

    echo '<div class="aznet-theme-homepage-hero-editor">';
    echo '<div class="aznet-theme-homepage-hero-editor__heading"><div><h3>' . esc_html__( 'Hero trang chủ', 'aznet-theme' ) . '</h3>';
    echo '<p class="description">' . esc_html__( 'Nội dung Hero thuộc WordPress. Theme chỉ chọn nguồn và trình bày, vì vậy anh/chị có thể sửa copy riêng cho Hero mà không phải đổi Tên website hoặc tiêu đề Trang chủ.', 'aznet-theme' ) . '</p></div></div>';

    homepage_page_select( 'homepage_hero_page', __( 'Nguồn Hero', 'aznet-theme' ), $hero_id, $pages );

    if ( $hero instanceof \WP_Post ) {
        $title = trim( (string) get_the_title( $hero ) );
        $excerpt = trim( (string) get_the_excerpt( $hero ) );
        $body = trim( (string) wp_strip_all_tags( strip_shortcodes( (string) $hero->post_content ) ) );
        $summary = '' !== $excerpt ? $excerpt : wp_trim_words( $body, 34, '…' );
        $edit_link = get_edit_post_link( $hero->ID, 'raw' );
        $view_link = get_permalink( $hero );
        $image = has_post_thumbnail( $hero )
            ? get_the_post_thumbnail( $hero, 'medium', [ 'class' => 'aznet-theme-homepage-hero-editor__image' ] )
            : '';

        echo '<div class="aznet-theme-homepage-hero-editor__status aznet-theme-homepage-hero-editor__status--ready"><strong>' . esc_html__( 'Nguồn Hero hiện tại:', 'aznet-theme' ) . '</strong> ' . esc_html__( 'Hero riêng', 'aznet-theme' ) . '</div>';
        echo '<div class="aznet-theme-homepage-hero-editor__preview">';
        if ( '' !== $image ) {
            echo '<div class="aznet-theme-homepage-hero-editor__media">' . wp_kses_post( $image ) . '</div>';
        }
        echo '<div class="aznet-theme-homepage-hero-editor__copy">';
        echo '<span class="aznet-theme-homepage-hero-editor__label">' . esc_html__( 'Tiêu đề Hero', 'aznet-theme' ) . '</span>';
        echo '<strong class="aznet-theme-homepage-hero-editor__title">' . esc_html( '' !== $title ? $title : __( '(Chưa có tiêu đề)', 'aznet-theme' ) ) . '</strong>';
        if ( '' !== $summary ) {
            echo '<p>' . esc_html( $summary ) . '</p>';
        } else {
            echo '<p class="description">' . esc_html__( 'Page Hero chưa có Mô tả ngắn hoặc Nội dung.', 'aznet-theme' ) . '</p>';
        }
        echo '</div></div>';

        echo '<div class="aznet-theme-homepage-hero-editor__actions">';
        if ( is_string( $edit_link ) && '' !== $edit_link ) {
            echo '<a class="button button-primary" href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Sửa nội dung Hero', 'aznet-theme' ) . '</a>';
        }
        if ( is_string( $view_link ) && '' !== $view_link ) {
            echo '<a class="button" href="' . esc_url( $view_link ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Xem Page Hero', 'aznet-theme' ) . '</a>';
        }
        echo '</div>';
    } else {
        $front_id = (int) get_option( 'page_on_front', 0 );
        $site_title = trim( (string) get_bloginfo( 'name' ) );
        $front_title = $front_id > 0 ? trim( (string) get_the_title( $front_id ) ) : '';
        $fallback_title = '' !== $site_title ? $site_title : $front_title;

        echo '<div class="aznet-theme-homepage-hero-editor__status aznet-theme-homepage-hero-editor__status--warning"><strong>' . esc_html__( 'Nguồn Hero hiện tại:', 'aznet-theme' ) . '</strong> ' . esc_html__( 'Dữ liệu dự phòng', 'aznet-theme' ) . '</div>';
        echo '<p class="notice notice-warning inline aznet-theme-homepage-hero-editor__notice">' . esc_html__( 'Hero hiện đang dùng dữ liệu dự phòng từ Tên website và Trang chủ. Hãy chọn hoặc tạo một Page Hero riêng để viết tiêu đề, mô tả và nội dung đúng cho phần mở đầu.', 'aznet-theme' ) . '</p>';
        if ( '' !== $fallback_title ) {
            echo '<div class="aznet-theme-homepage-hero-editor__preview aznet-theme-homepage-hero-editor__preview--fallback"><div class="aznet-theme-homepage-hero-editor__copy">';
            echo '<span class="aznet-theme-homepage-hero-editor__label">' . esc_html__( 'Tiêu đề đang hiển thị theo fallback', 'aznet-theme' ) . '</span>';
            echo '<strong class="aznet-theme-homepage-hero-editor__title">' . esc_html( $fallback_title ) . '</strong>';
            echo '</div></div>';
        }
        echo '<div class="aznet-theme-homepage-hero-editor__actions">';
        echo '<a class="button button-primary" href="' . esc_url( admin_url( 'post-new.php?post_type=page' ) ) . '">' . esc_html__( 'Tạo Page Hero mới', 'aznet-theme' ) . '</a>';
        echo '</div>';
    }

    echo '<p class="description aznet-theme-homepage-hero-editor__ownership">' . esc_html__( 'Theme không lưu bản sao tiêu đề, mô tả hoặc nội dung Hero. Sau khi sửa Page, nội dung mới sẽ được dùng tại Hero khi Page đó được chọn làm nguồn.', 'aznet-theme' ) . '</p>';
    echo '</div>';
}

/** Render one Category selector. */
function homepage_category_select( string $key, string $label, int $current, array $categories ): void {
    echo '<label class="aznet-theme-field"><span>' . esc_html( $label ) . '</span><select name="aznet_theme_settings[' . esc_attr( $key ) . ']">';
    echo '<option value="0">' . esc_html__( '— Chưa chọn —', 'aznet-theme' ) . '</option>';
    foreach ( $categories as $category ) {
        if ( ! $category instanceof \WP_Term ) { continue; }
        echo '<option value="' . esc_attr( (string) $category->term_id ) . '" ' . selected( $current, (int) $category->term_id, false ) . '>' . esc_html( $category->name ) . '</option>';
    }
    echo '</select></label>';
}

/** Render Homepage presentation preset and typed source mapping. */
function render_homepage_settings(): void {
    $s = settings();
    $pages = get_pages( [ 'post_status' => 'publish', 'sort_column' => 'post_title' ] );
    $categories = get_categories( [ 'hide_empty' => false ] );
    $visible = [
        'homepage_preset', 'homepage_law01_variant', 'homepage_hero_page', 'homepage_services_page', 'homepage_about_page', 'homepage_team_page',
        'homepage_knowledge_terms', 'homepage_case_analysis_term', 'homepage_legal_news_term',
        'homepage_process_page', 'homepage_faq_page', 'homepage_contact_page',
    ];

    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( $visible );
    echo '<h2>' . esc_html__( 'Mẫu trang chủ', 'aznet-theme' ) . '</h2>';
    field_select( 'homepage_preset', __( 'Mẫu', 'aznet-theme' ), [ 'off' => 'Tắt Composer', 'law-01' => 'Luật 01' ], (string) $s['homepage_preset'] );
    field_select( 'homepage_law01_variant', __( 'Biến thể Luật 01', 'aznet-theme' ), [ 'navy-gold' => 'Navy + Gold', 'burgundy-gold' => 'Burgundy + Gold' ], (string) $s['homepage_law01_variant'] );
    echo '<p class="description">' . esc_html__( 'Luật 01: website dịch vụ pháp lý kết hợp nội dung chuyên môn. Áp dụng mẫu chỉ đổi presentation, không sửa nội dung WordPress.', 'aznet-theme' ) . '</p>';

    echo '<h2>' . esc_html__( 'Nguồn nội dung', 'aznet-theme' ) . '</h2>';
    render_homepage_hero_editor( $s, $pages );
    homepage_page_select( 'homepage_services_page', 'Dịch vụ pháp lý', (int) $s['homepage_services_page'], $pages );
    homepage_page_select( 'homepage_about_page', 'Giới thiệu', (int) $s['homepage_about_page'], $pages );
    homepage_page_select( 'homepage_team_page', 'Đội ngũ', (int) $s['homepage_team_page'], $pages );

    echo '<label class="aznet-theme-field"><span>' . esc_html__( 'Chủ đề kiến thức', 'aznet-theme' ) . '</span><select multiple size="8" name="aznet_theme_settings[homepage_knowledge_terms][]">';
    foreach ( $categories as $category ) {
        if ( ! $category instanceof \WP_Term ) { continue; }
        $selected = in_array( (int) $category->term_id, (array) $s['homepage_knowledge_terms'], true ) ? ' selected' : '';
        echo '<option value="' . esc_attr( (string) $category->term_id ) . '"' . $selected . '>' . esc_html( $category->name ) . '</option>';
    }
    echo '</select></label>';

    homepage_category_select( 'homepage_case_analysis_term', 'Phân tích vụ việc', (int) $s['homepage_case_analysis_term'], $categories );
    homepage_category_select( 'homepage_legal_news_term', 'Tin pháp luật', (int) $s['homepage_legal_news_term'], $categories );
    homepage_page_select( 'homepage_process_page', 'Quy trình tư vấn', (int) $s['homepage_process_page'], $pages );
    homepage_page_select( 'homepage_faq_page', 'Câu hỏi thường gặp', (int) $s['homepage_faq_page'], $pages );
    homepage_page_select( 'homepage_contact_page', 'Liên hệ', (int) $s['homepage_contact_page'], $pages );
    submit_button( __( 'Lưu Trang chủ', 'aznet-theme' ) );
    echo '</form>';

    echo '<div class="aznet-theme-panel"><h2>' . esc_html__( 'Tình trạng trang chủ', 'aznet-theme' ) . '</h2><table class="widefat striped"><tbody>';
    foreach ( homepage_slot_statuses() as $slot => $status ) {
        echo '<tr><th>' . esc_html( $slot ) . '</th><td><code>' . esc_html( $status ) . '</code></td></tr>';
    }
    echo '</tbody></table></div>';
}

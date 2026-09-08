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
        'homepage_preset', 'homepage_services_page', 'homepage_about_page', 'homepage_team_page',
        'homepage_knowledge_terms', 'homepage_case_analysis_term', 'homepage_legal_news_term',
        'homepage_process_page', 'homepage_faq_page', 'homepage_contact_page',
    ];

    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( $visible );
    echo '<h2>' . esc_html__( 'Mẫu trang chủ', 'aznet-theme' ) . '</h2>';
    field_select( 'homepage_preset', __( 'Mẫu', 'aznet-theme' ), [ 'off' => 'Tắt Composer', 'law-01' => 'Luật 01' ], (string) $s['homepage_preset'] );
    echo '<p class="description">' . esc_html__( 'Luật 01: website dịch vụ pháp lý kết hợp nội dung chuyên môn. Áp dụng mẫu chỉ đổi presentation, không sửa nội dung WordPress.', 'aznet-theme' ) . '</p>';

    echo '<h2>' . esc_html__( 'Nguồn nội dung', 'aznet-theme' ) . '</h2>';
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
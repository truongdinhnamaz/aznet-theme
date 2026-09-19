<?php
/** Homepage presentation/reference configuration. */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\homepage_category_reference;
use function AZnet\Theme\homepage_category_references;
use function AZnet\Theme\homepage_direct_published_children;
use function AZnet\Theme\homepage_latest_posts;
use function AZnet\Theme\homepage_block_reference;
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

    $hero_block_id = (int) ( $s['homepage_hero_block'] ?? 0 );
    $legacy_hero_page_id = (int) ( $s['homepage_hero_page'] ?? 0 );
    if ( null !== homepage_block_reference( $hero_block_id ) ) {
        $statuses['hero'] = 'READY';
    } elseif ( null !== homepage_hero_candidate_reference( $hero_block_id ) ) {
        $statuses['hero'] = 'DRAFT';
    } elseif ( null !== homepage_page_reference( $legacy_hero_page_id ) ) {
        $statuses['hero'] = 'LEGACY_PAGE';
    } else {
        $statuses['hero'] = 'FALLBACK';
    }

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

/**
 * Render the D-030 Hero Library without storing Hero copy in Theme settings.
 *
 * @param array<string, mixed> $settings Normalized Theme presentation/reference settings.
 */
function render_homepage_hero_library( array $settings ): void {
    $hero_id = (int) ( $settings['homepage_hero_block'] ?? 0 );
    $hero_block = homepage_block_reference( $hero_id );
    $hero_candidate = homepage_hero_candidate_reference( $hero_id );
    $legacy_page = homepage_page_reference( (int) ( $settings['homepage_hero_page'] ?? 0 ) );
    $current_variant = (string) ( $settings['homepage_hero_variant'] ?? 'split' );
    $variants = homepage_hero_library_variants();

    echo '<div class="aznet-theme-panel aznet-theme-homepage-hero-editor">';
    echo '<div class="aznet-theme-homepage-hero-editor__heading"><div><h2>' . esc_html__( 'Thư viện Hero', 'aznet-theme' ) . '</h2>';
    echo '<p class="description">' . esc_html__( 'Chọn cách trình bày Hero. Nội dung được lưu trong WordPress dưới dạng Hero đồng bộ bằng block lõi; Theme không lưu bản sao tiêu đề, mô tả, ảnh hay CTA.', 'aznet-theme' ) . '</p></div></div>';

    if ( $hero_block instanceof \WP_Post ) {
        echo '<div class="aznet-theme-homepage-hero-editor__status aznet-theme-homepage-hero-editor__status--ready"><strong>' . esc_html__( 'Nguồn Hero hiện tại:', 'aznet-theme' ) . '</strong> ' . esc_html__( 'Hero WordPress', 'aznet-theme' ) . '</div>';
    } elseif ( $hero_candidate instanceof \WP_Post && 'draft' === $hero_candidate->post_status ) {
        echo '<div class="aznet-theme-homepage-hero-editor__status aznet-theme-homepage-hero-editor__status--warning"><strong>' . esc_html__( 'Nguồn Hero hiện tại:', 'aznet-theme' ) . '</strong> ' . esc_html__( 'Hero WordPress đang soạn', 'aznet-theme' ) . '</div>';
        echo '<p class="notice notice-info inline aznet-theme-homepage-hero-editor__notice">' . esc_html__( 'Hero mới vẫn là bản nháp nên website tiếp tục hiển thị Hero cũ/dự phòng. Hoàn thiện nội dung và xuất bản Hero để chuyển nguồn an toàn.', 'aznet-theme' ) . '</p>';
    } elseif ( $legacy_page instanceof \WP_Post ) {
        echo '<div class="aznet-theme-homepage-hero-editor__status aznet-theme-homepage-hero-editor__status--warning"><strong>' . esc_html__( 'Nguồn Hero hiện tại:', 'aznet-theme' ) . '</strong> ' . esc_html__( 'Hero Page cũ', 'aznet-theme' ) . '</div>';
        echo '<p class="notice notice-warning inline aznet-theme-homepage-hero-editor__notice">' . esc_html__( 'Website vẫn đang dùng Page Hero cũ để tương thích. Chọn một mẫu sẽ tạo Hero WordPress ở trạng thái bản nháp; Page cũ tiếp tục hiển thị cho đến khi Hero mới được xuất bản và không bị xóa.', 'aznet-theme' ) . '</p>';
    } else {
        echo '<div class="aznet-theme-homepage-hero-editor__status aznet-theme-homepage-hero-editor__status--warning"><strong>' . esc_html__( 'Nguồn Hero hiện tại:', 'aznet-theme' ) . '</strong> ' . esc_html__( 'Dữ liệu dự phòng', 'aznet-theme' ) . '</div>';
        echo '<p class="notice notice-warning inline aznet-theme-homepage-hero-editor__notice">' . esc_html__( 'Chưa có Hero WordPress riêng. Chọn một mẫu để tạo bản nháp Hero mà không cần tạo Page; website hiện tại chưa đổi cho đến khi Hero mới được xuất bản.', 'aznet-theme' ) . '</p>';
    }

    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-homepage-hero-library">';
    echo '<input type="hidden" name="action" value="aznet_theme_apply_homepage_hero">';
    wp_nonce_field( 'aznet_theme_apply_homepage_hero' );
    echo '<div class="aznet-theme-homepage-hero-library__grid">';
    foreach ( $variants as $slug => $label ) {
        $checked = checked( $current_variant, $slug, false );
        echo '<label class="aznet-theme-homepage-hero-library__card aznet-theme-homepage-hero-library__card--' . esc_attr( $slug ) . '">';
        echo '<input type="radio" name="homepage_hero_variant" value="' . esc_attr( $slug ) . '"' . $checked . '>';
        echo '<span class="aznet-theme-homepage-hero-library__preview" aria-hidden="true"><span></span><span></span></span>';
        echo '<strong>' . esc_html( $label ) . '</strong>';
        echo '</label>';
    }
    echo '</div>';
    echo '<div class="aznet-theme-homepage-hero-editor__actions">';
    submit_button( __( 'Dùng mẫu này', 'aznet-theme' ), 'primary', 'submit', false );
    if ( $hero_candidate instanceof \WP_Post ) {
        $edit_link = get_edit_post_link( $hero_candidate->ID, 'raw' );
        if ( is_string( $edit_link ) && '' !== $edit_link ) {
            $edit_label = 'draft' === $hero_candidate->post_status
                ? __( 'Tiếp tục sửa Hero', 'aznet-theme' )
                : __( 'Sửa nội dung Hero', 'aznet-theme' );
            echo '<a class="button" href="' . esc_url( $edit_link ) . '">' . esc_html( $edit_label ) . '</a>';
        }
    } elseif ( $legacy_page instanceof \WP_Post ) {
        $legacy_edit = get_edit_post_link( $legacy_page->ID, 'raw' );
        if ( is_string( $legacy_edit ) && '' !== $legacy_edit ) {
            echo '<a class="button" href="' . esc_url( $legacy_edit ) . '">' . esc_html__( 'Sửa Hero Page cũ', 'aznet-theme' ) . '</a>';
        }
    }
    echo '</div></form>';
    echo '<p class="description aznet-theme-homepage-hero-editor__ownership">' . esc_html__( 'Đổi mẫu chỉ đổi presentation. Tạo Hero mới theo cơ chế bản nháp trước; nội dung WordPress không bị viết lại khi đổi mẫu và vẫn còn khi đổi Theme.', 'aznet-theme' ) . '</p>';
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

    $preset_visible = [ 'homepage_preset', 'homepage_law01_variant' ];
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( $preset_visible );
    echo '<h2>' . esc_html__( 'Mẫu trang chủ', 'aznet-theme' ) . '</h2>';
    field_select( 'homepage_preset', __( 'Mẫu', 'aznet-theme' ), [ 'off' => 'Tắt Composer', 'law-01' => 'Luật 01' ], (string) $s['homepage_preset'] );
    field_select( 'homepage_law01_variant', __( 'Biến thể Luật 01', 'aznet-theme' ), [ 'navy-gold' => 'Navy + Gold', 'burgundy-gold' => 'Burgundy + Gold' ], (string) $s['homepage_law01_variant'] );
    echo '<p class="description">' . esc_html__( 'Luật 01: website dịch vụ pháp lý kết hợp nội dung chuyên môn. Áp dụng mẫu chỉ đổi presentation, không sửa nội dung WordPress.', 'aznet-theme' ) . '</p>';
    submit_button( __( 'Lưu mẫu trang chủ', 'aznet-theme' ) );
    echo '</form>';

    render_homepage_hero_library( $s );

    $source_visible = [
        'homepage_services_page', 'homepage_about_page', 'homepage_team_page',
        'homepage_knowledge_terms', 'homepage_case_analysis_term', 'homepage_legal_news_term',
        'homepage_process_page', 'homepage_faq_page', 'homepage_contact_page',
    ];
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( $source_visible );
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
    submit_button( __( 'Lưu nguồn nội dung', 'aznet-theme' ) );
    echo '</form>';

    echo '<div class="aznet-theme-panel"><h2>' . esc_html__( 'Tình trạng trang chủ', 'aznet-theme' ) . '</h2><table class="widefat striped"><tbody>';
    foreach ( homepage_slot_statuses() as $slot => $status ) {
        echo '<tr><th>' . esc_html( $slot ) . '</th><td><code>' . esc_html( $status ) . '</code></td></tr>';
    }
    echo '</tbody></table></div>';
}

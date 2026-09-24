<?php
/** Homepage presentation/reference configuration. */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\homepage_category_reference;
use function AZnet\Theme\homepage_category_references;
use function AZnet\Theme\homepage_direct_published_children;
use function AZnet\Theme\homepage_latest_posts;
use function AZnet\Theme\homepage_block_reference;
use function AZnet\Theme\homepage_page_reference;
use function AZnet\Theme\homepage_source_descriptor;
use function AZnet\Theme\homepage_source_key;
use function AZnet\Theme\homepage_source_value;
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
    $hero_block = homepage_block_reference( $hero_block_id );
    if ( $hero_block instanceof \WP_Post ) {
        $statuses['hero'] = '' !== trim( (string) $hero_block->post_content ) ? 'READY' : 'EMPTY';
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
        'process'  => 'curtain-01' === (string) ( $s['homepage_preset'] ?? 'off' )
            ? 'homepage_curtain01_process_page'
            : 'homepage_process_page',
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

    if ( 'curtain-01' === (string) ( $s['homepage_preset'] ?? 'off' ) ) {
        $project_term_id = (int) ( $s['homepage_curtain01_projects_term'] ?? 0 );
        if ( $project_term_id <= 0 ) {
            $statuses['projects'] = 'UNMAPPED';
        } elseif ( null === homepage_category_reference( $project_term_id ) ) {
            $statuses['projects'] = 'INVALID';
        } else {
            $statuses['projects'] = [] === homepage_latest_posts( [ $project_term_id ], 1 ) ? 'EMPTY' : 'READY';
        }
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

/** Render one published WordPress synced-block selector. */
function homepage_block_select( string $key, string $label, int $current, array $blocks ): void {
    echo '<label class="aznet-theme-field"><span>' . esc_html( $label ) . '</span><select name="aznet_theme_settings[' . esc_attr( $key ) . ']">';
    echo '<option value="0">' . esc_html__( '— Chưa chọn —', 'aznet-theme' ) . '</option>';
    foreach ( $blocks as $block ) {
        if ( ! $block instanceof \WP_Post || 'wp_block' !== $block->post_type || 'publish' !== $block->post_status ) { continue; }
        echo '<option value="' . esc_attr( (string) $block->ID ) . '" ' . selected( $current, (int) $block->ID, false ) . '>' . esc_html( get_the_title( $block ) ) . '</option>';
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
    $hero_block_has_content = $hero_block instanceof \WP_Post && '' !== trim( (string) $hero_block->post_content );
    $legacy_page = homepage_page_reference( (int) ( $settings['homepage_hero_page'] ?? 0 ) );
    $current_variant = (string) ( $settings['homepage_hero_variant'] ?? 'split' );
    $variants = homepage_hero_library_variants();

    echo '<div class="aznet-theme-panel aznet-theme-homepage-hero-editor">';
    echo '<div class="aznet-theme-homepage-hero-editor__heading"><div><h2>' . esc_html__( 'Thư viện Hero', 'aznet-theme' ) . '</h2>';
    echo '<p class="description">' . esc_html__( 'Chọn cách trình bày Hero. Nội dung được lưu trong WordPress dưới dạng Hero đồng bộ bằng block lõi; Theme không lưu bản sao tiêu đề, mô tả, ảnh hay CTA.', 'aznet-theme' ) . '</p>';
    echo '<p class="description"><strong>' . esc_html__( 'Một nơi để sửa toàn bộ Hero:', 'aznet-theme' ) . '</strong> ' . esc_html__( 'chữ, nút, ảnh và cam kết đều được chỉnh trong chính Hero WordPress; Site Title, Tagline và tiêu đề Trang chủ chỉ còn là nguồn dự phòng tương thích.', 'aznet-theme' ) . '</p></div></div>';

    if ( $hero_block instanceof \WP_Post && $hero_block_has_content ) {
        echo '<div class="aznet-theme-homepage-hero-editor__status aznet-theme-homepage-hero-editor__status--ready"><strong>' . esc_html__( 'Nguồn Hero hiện tại:', 'aznet-theme' ) . '</strong> ' . esc_html__( 'Hero WordPress', 'aznet-theme' ) . '</div>';
        echo '<p class="description">' . esc_html__( 'Muốn tạm quay lại Hero cũ/dự phòng mà không xóa nội dung, hãy chuyển Hero WordPress này về trạng thái Bản nháp.', 'aznet-theme' ) . '</p>';
    } elseif ( $hero_block instanceof \WP_Post ) {
        echo '<div class="aznet-theme-homepage-hero-editor__status aznet-theme-homepage-hero-editor__status--warning"><strong>' . esc_html__( 'Nguồn Hero hiện tại:', 'aznet-theme' ) . '</strong> ' . esc_html__( 'Hero WordPress chưa có nội dung', 'aznet-theme' ) . '</div>';
        echo '<p class="notice notice-warning inline aznet-theme-homepage-hero-editor__notice">' . esc_html__( 'Hero WordPress đã xuất bản nhưng đang trống, nên website tiếp tục dùng Hero cũ/dự phòng. Hãy thêm nội dung vào Hero để chuyển nguồn.', 'aznet-theme' ) . '</p>';
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
    echo '<fieldset class="aznet-theme-homepage-hero-library__fieldset">';
    echo '<legend class="screen-reader-text">' . esc_html__( 'Chọn mẫu Hero', 'aznet-theme' ) . '</legend>';
    echo '<div class="aznet-theme-homepage-hero-library__grid">';
    foreach ( $variants as $slug => $label ) {
        $checked = checked( $current_variant, $slug, false );
        echo '<label class="aznet-theme-homepage-hero-library__card aznet-theme-homepage-hero-library__card--' . esc_attr( $slug ) . '">';
        echo '<input type="radio" name="homepage_hero_variant" value="' . esc_attr( $slug ) . '"' . $checked . '>';
        echo '<span class="aznet-theme-homepage-hero-library__preview" aria-hidden="true"><span></span><span></span></span>';
        echo '<strong>' . esc_html( $label ) . '</strong>';
        echo '</label>';
    }
    echo '</div></fieldset>';
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

/** Return authoring sections in frontend reading order for one preset. */
function homepage_authoring_sections( string $preset ): array {
    if ( 'law-01' === $preset ) {
        return [ 'hero', 'services', 'about', 'team', 'knowledge', 'case_analysis', 'legal_news', 'process', 'faq', 'contact' ];
    }
    if ( 'curtain-01' === $preset ) {
        return [ 'hero', 'proof', 'about', 'process', 'projects', 'knowledge', 'contact' ];
    }
    return [];
}

/** Human label for one Homepage slot. */
function homepage_authoring_label( string $slot ): string {
    return [
        'hero' => __( 'Hero', 'aznet-theme' ),
        'services' => __( 'Dịch vụ', 'aznet-theme' ),
        'about' => __( 'Giới thiệu', 'aznet-theme' ),
        'team' => __( 'Đội ngũ', 'aznet-theme' ),
        'knowledge' => __( 'Bài viết / kiến thức', 'aznet-theme' ),
        'case_analysis' => __( 'Phân tích vụ việc', 'aznet-theme' ),
        'legal_news' => __( 'Tin pháp luật', 'aznet-theme' ),
        'process' => __( 'Quy trình', 'aznet-theme' ),
        'faq' => __( 'Câu hỏi thường gặp', 'aznet-theme' ),
        'contact' => __( 'Liên hệ / CTA cuối', 'aznet-theme' ),
        'proof' => __( 'Bằng chứng nhanh', 'aznet-theme' ),
        'projects' => __( 'Công trình', 'aznet-theme' ),
    ][ $slot ] ?? $slot;
}

/** Resolve a human-readable source title and native edit URL. */
function homepage_authoring_source_summary( string $preset, string $slot ): array {
    $descriptor = homepage_source_descriptor( $preset, $slot );
    if ( null === $descriptor ) { return [ 'title' => __( 'Chưa hỗ trợ', 'aznet-theme' ), 'edit' => '', 'status' => 'UNMAPPED' ]; }
    $type = (string) ( $descriptor['type'] ?? '' );
    $value = homepage_source_value( $preset, $slot );
    if ( in_array( $type, [ 'page', 'wp_block' ], true ) ) {
        $id = (int) $value;
        $post = $id > 0 ? get_post( $id ) : null;
        if ( ! $post instanceof \WP_Post ) { return [ 'title' => __( 'Chưa chọn nguồn', 'aznet-theme' ), 'edit' => '', 'status' => $id > 0 ? 'INVALID' : 'UNMAPPED' ]; }
        $edit = get_edit_post_link( $post->ID, 'raw' );
        return [ 'title' => get_the_title( $post ), 'edit' => is_string( $edit ) ? $edit : '', 'status' => 'publish' === $post->post_status ? 'READY' : strtoupper( (string) $post->post_status ) ];
    }
    if ( 'category' === $type ) {
        $id = (int) $value;
        $term = $id > 0 ? homepage_category_reference( $id ) : null;
        if ( ! $term instanceof \WP_Term ) { return [ 'title' => __( 'Chưa chọn nguồn', 'aznet-theme' ), 'edit' => '', 'status' => $id > 0 ? 'INVALID' : 'UNMAPPED' ]; }
        $edit = get_edit_term_link( $term->term_id, 'category' );
        return [ 'title' => $term->name, 'edit' => is_string( $edit ) ? $edit : '', 'status' => 'READY' ];
    }
    if ( 'categories' === $type ) {
        $terms = homepage_category_references( (array) $value );
        return [ 'title' => [] === $terms ? __( 'Chưa chọn nguồn', 'aznet-theme' ) : implode( ', ', array_map( static fn( \WP_Term $term ): string => $term->name, $terms ) ), 'edit' => '', 'status' => [] === $terms ? 'UNMAPPED' : 'READY' ];
    }
    return [ 'title' => __( 'Nguồn trình bày', 'aznet-theme' ), 'edit' => '', 'status' => 'READY' ];
}

/** Render preset-aware Homepage section cards. */
function render_homepage_authoring_console( string $preset ): void {
    if ( ! in_array( $preset, [ 'law-01', 'curtain-01' ], true ) ) { return; }
    $preset_label = 'law-01' === $preset ? __( 'Luật 01', 'aznet-theme' ) : __( 'Rèm 01', 'aznet-theme' );
    echo '<div class="aznet-theme-panel aznet-theme-homepage-authoring">';
    echo '<h2>' . esc_html( sprintf( __( 'Mẫu đang chỉnh: %s', 'aznet-theme' ), $preset_label ) ) . '</h2>';
    echo '<p class="description">' . esc_html__( 'Mỗi mục dùng nguồn WordPress của chính mẫu. Sửa nhanh chỉ thay đổi nguồn đã ánh xạ; Chỉnh đầy đủ mở trình soạn thảo WordPress.', 'aznet-theme' ) . '</p>';
    echo '<div class="aznet-theme-homepage-section-grid">';
    foreach ( homepage_authoring_sections( $preset ) as $slot ) {
        $summary = homepage_authoring_source_summary( $preset, $slot );
        echo '<article class="aznet-theme-homepage-section-card">';
        echo '<div class="aznet-theme-homepage-section-card__heading"><h3>' . esc_html( homepage_authoring_label( $slot ) ) . '</h3><code class="aznet-theme-homepage-section-card__status">' . esc_html( (string) $summary['status'] ) . '</code></div>';
        echo '<p><strong>' . esc_html__( 'Nguồn:', 'aznet-theme' ) . '</strong> ' . esc_html( (string) $summary['title'] ) . '</p>';
        echo '<div class="aznet-theme-homepage-section-card__actions">';
        echo '<button type="button" class="button aznet-theme-homepage-quick-edit" data-preset="' . esc_attr( $preset ) . '" data-slot="' . esc_attr( $slot ) . '">' . esc_html__( 'Sửa nhanh', 'aznet-theme' ) . '</button>';
        if ( '' !== (string) $summary['edit'] ) { echo '<a class="button" href="' . esc_url( (string) $summary['edit'] ) . '">' . esc_html__( 'Chỉnh đầy đủ', 'aznet-theme' ) . '</a>'; }
        echo '<a class="button" href="#aznet-theme-homepage-sources">' . esc_html__( 'Đổi nguồn', 'aznet-theme' ) . '</a>';
        echo '</div></article>';
    }
    echo '</div></div>';
}

/** Render Homepage presentation preset and typed source mapping. */
function render_homepage_settings(): void {
    $s = settings();
    $pages = get_pages( [ 'post_status' => 'publish', 'sort_column' => 'post_title' ] );
    $categories = get_categories( [ 'hide_empty' => false ] );
    $blocks = get_posts( [
        'post_type'      => 'wp_block',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ] );

    $preset_visible = [ 'homepage_preset', 'homepage_law01_variant' ];
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( $preset_visible );
    echo '<h2>' . esc_html__( 'Mẫu trang chủ', 'aznet-theme' ) . '</h2>';
    field_select( 'homepage_preset', __( 'Mẫu', 'aznet-theme' ), [ 'off' => 'Tắt Composer', 'law-01' => 'Luật 01', 'curtain-01' => 'Rèm 01' ], (string) $s['homepage_preset'] );
    if ( 'law-01' === (string) $s['homepage_preset'] ) {
        field_select( 'homepage_law01_variant', __( 'Biến thể Luật 01', 'aznet-theme' ), [ 'navy-gold' => 'Navy + Gold', 'burgundy-gold' => 'Burgundy + Gold' ], (string) $s['homepage_law01_variant'] );
        echo '<p class="description">' . esc_html__( 'Luật 01: website dịch vụ pháp lý kết hợp nội dung chuyên môn. Áp dụng mẫu chỉ đổi presentation, không sửa nội dung WordPress.', 'aznet-theme' ) . '</p>';
    } elseif ( 'curtain-01' === (string) $s['homepage_preset'] ) {
        echo '<p class="description">' . esc_html__( 'Rèm 01: website rèm và giải pháp kiểm soát ánh sáng. Đây là mẫu độc lập; áp dụng mẫu không sửa nội dung WordPress và không thay đổi cấu hình Luật 01.', 'aznet-theme' ) . '</p>';
    }
    submit_button( __( 'Lưu mẫu trang chủ', 'aznet-theme' ) );
    echo '</form>';

    if ( in_array( (string) $s['homepage_preset'], [ 'law-01', 'curtain-01' ], true ) ) {
        $scope = (string) $s['homepage_preset'];
        $marker_key = \AZnet\Theme\homepage_source_initialization_key( $scope );
        $initialized = null !== $marker_key && ! empty( $s[ $marker_key ] );
        echo '<div class="aznet-theme-panel aznet-theme-homepage-migration">';
        echo '<h2>' . esc_html__( 'Tách cấu hình nội dung theo mẫu', 'aznet-theme' ) . '</h2>';
        if ( $initialized ) {
            echo '<p class="description">' . esc_html__( 'Đã tách mapping cho mẫu đang dùng.', 'aznet-theme' ) . '</p>';
        } else {
            echo '<p>' . esc_html__( 'Thao tác này chỉ sao chép các mapping nguồn hiện có sang phạm vi riêng của mẫu. Nội dung WordPress không bị sao chép, sửa hoặc xuất bản.', 'aznet-theme' ) . '</p>';
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
            echo '<input type="hidden" name="action" value="aznet_theme_migrate_homepage_preset">';
            echo '<input type="hidden" name="homepage_preset_scope" value="' . esc_attr( $scope ) . '">';
            wp_nonce_field( 'aznet_theme_migrate_homepage_preset' );
            submit_button( __( 'Khởi tạo mapping riêng cho mẫu này', 'aznet-theme' ), 'secondary', 'submit', false );
            echo '</form>';
        }
        echo '</div>';
    }

    render_homepage_authoring_console( $active_preset );

    render_homepage_hero_library( $s );

    $source_slots = 'law-01' === $active_preset
        ? [ 'services', 'about', 'team', 'knowledge', 'case_analysis', 'legal_news', 'process', 'faq', 'contact' ]
        : ( 'curtain-01' === $active_preset ? [ 'proof', 'about', 'knowledge', 'process', 'projects', 'contact' ] : [] );
    $source_visible = [];
    foreach ( $source_slots as $slot ) {
        $source_key = homepage_source_key( $active_preset, $slot );
        if ( is_string( $source_key ) && '' !== $source_key ) { $source_visible[] = $source_key; }
    }
    echo '<details class="aznet-theme-homepage-diagnostics" id="aznet-theme-homepage-sources"><summary>' . esc_html__( 'Nguồn & chẩn đoán', 'aznet-theme' ) . '</summary>';
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( $source_visible );
    echo '<h2>' . esc_html__( 'Nguồn nội dung', 'aznet-theme' ) . '</h2>';

    foreach ( $source_slots as $slot ) {
        $descriptor = homepage_source_descriptor( $active_preset, $slot );
        $key = homepage_source_key( $active_preset, $slot );
        if ( null === $descriptor || null === $key || '' === $key ) { continue; }
        $type = (string) ( $descriptor['type'] ?? '' );
        $label = homepage_authoring_label( $slot );
        $value = homepage_source_value( $active_preset, $slot, $s );
        if ( 'page' === $type ) {
            homepage_page_select( $key, $label, (int) $value, $pages );
        } elseif ( 'wp_block' === $type ) {
            homepage_block_select( $key, $label, (int) $value, $blocks );
        } elseif ( 'category' === $type ) {
            homepage_category_select( $key, $label, (int) $value, $categories );
        } elseif ( 'categories' === $type ) {
            echo '<label class="aznet-theme-field"><span>' . esc_html( $label ) . '</span><select multiple size="8" name="aznet_theme_settings[' . esc_attr( $key ) . '][]">';
            foreach ( $categories as $category ) {
                if ( ! $category instanceof \WP_Term ) { continue; }
                $selected = in_array( (int) $category->term_id, (array) $value, true ) ? ' selected' : '';
                echo '<option value="' . esc_attr( (string) $category->term_id ) . '"' . $selected . '>' . esc_html( $category->name ) . '</option>';
            }
            echo '</select></label>';
        }
    }
    submit_button( __( 'Lưu nguồn nội dung', 'aznet-theme' ) );
    echo '</form>';

    echo '<div class="aznet-theme-panel"><h2>' . esc_html__( 'Tình trạng trang chủ', 'aznet-theme' ) . '</h2><table class="widefat striped"><tbody>';
    foreach ( homepage_slot_statuses() as $slot => $status ) {
        echo '<tr><th>' . esc_html( $slot ) . '</th><td><code>' . esc_html( $status ) . '</code></td></tr>';
    }
    echo '</tbody></table></div>';
    echo '</details>';
}

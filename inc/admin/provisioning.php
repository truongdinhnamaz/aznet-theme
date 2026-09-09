<?php
/** Law Site Provisioning admin wizard. */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\provisioning_blueprint;
use function AZnet\Theme\provisioning_build_plan;
use function AZnet\Theme\provisioning_discovery;
use function AZnet\Theme\provisioning_plan_fingerprint;
use function AZnet\Theme\provisioning_validate_plan;
use function AZnet\Theme\provisioning_apply_plan;
use function AZnet\Theme\provisioning_readiness;
use function AZnet\Theme\provisioning_recommendations;
use function AZnet\Theme\provisioning_editorial_coverage_recommendations;
use function AZnet\Theme\provisioning_site_mode;
use function AZnet\Theme\provisioning_index_restore_available;
use function AZnet\Theme\provisioning_restore_search_visibility;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function provisioning_url( int $step = 1 ): string { return add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'provisioning', 'step' => max( 1, min( 4, $step ) ) ], admin_url( 'admin.php' ) ); }
function provisioning_plan_transient_key(): string { return 'aznet_theme_provisioning_plan_' . (string) get_current_user_id(); }
function provisioning_result_transient_key(): string { return 'aznet_theme_provisioning_result_' . (string) get_current_user_id(); }

function render_provisioning_invitation(): void {
    echo '<div class="aznet-theme-panel aznet-theme-provisioning-invitation"><h2>' . esc_html__( 'Thiết lập website nhanh', 'aznet-theme' ) . '</h2>';
    echo '<p>' . esc_html__( 'Tạo hoặc map cấu trúc WordPress-native cho mẫu Luật 01. Theme không tự chạy thiết lập khi kích hoạt.', 'aznet-theme' ) . '</p>';
    echo '<p><a class="button button-primary" href="' . esc_url( provisioning_url( 1 ) ) . '">' . esc_html__( 'Thiết lập website nhanh', 'aznet-theme' ) . '</a></p></div>';
}

/** @return string */
function provisioning_candidate_label( int $id, array $candidates ): string {
    foreach ( $candidates as $candidate ) {
        if ( ! is_array( $candidate ) || $id !== (int) ( $candidate['id'] ?? 0 ) ) { continue; }
        return (string) ( $candidate['title'] ?? $candidate['name'] ?? (string) $id );
    }
    return '';
}

/** Keep full typed controls available for advanced correction while defaulting to a proposal-only recommendation. */
function provisioning_action_select( string $kind, string $role, array $candidates, bool $new_site, bool $required, ?array $recommendation = null ): void {
    $default = $new_site || $required ? 'create' : 'skip';
    $default_id = 0;
    if ( is_array( $recommendation ) ) {
        $candidate_action = (string) ( $recommendation['action'] ?? '' );
        if ( in_array( $candidate_action, [ 'create', 'reuse', 'skip' ], true ) ) { $default = $candidate_action; }
        $default_id = (int) ( $recommendation['object_id'] ?? 0 );
        if ( 'AMBIGUOUS' === (string) ( $recommendation['state'] ?? '' ) ) { $default = $required ? 'reuse' : 'skip'; $default_id = 0; }
    }
    $base_id = 'aznet-theme-provision-' . sanitize_html_class( $kind . '-' . $role );
    $action_id = $base_id . '-action';
    $object_id = $base_id . '-object';
    echo '<fieldset class="aznet-theme-provision-row"><legend><strong>' . esc_html( $role ) . '</strong></legend>';
    echo '<label class="screen-reader-text" for="' . esc_attr( $action_id ) . '">' . esc_html( sprintf( __( 'Hành động cho %s', 'aznet-theme' ), $role ) ) . '</label>';
    echo '<select id="' . esc_attr( $action_id ) . '" name="' . esc_attr( $kind ) . '[' . esc_attr( $role ) . '][action]">';
    foreach ( [ 'create' => 'Create new', 'reuse' => 'Reuse existing', 'skip' => 'Skip' ] as $value => $label ) {
        if ( $required && 'skip' === $value ) { continue; }
        echo '<option value="' . esc_attr( $value ) . '" ' . selected( $default, $value, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
    echo '<label class="screen-reader-text" for="' . esc_attr( $object_id ) . '">' . esc_html( sprintf( __( 'Nguồn WordPress hiện có cho %s', 'aznet-theme' ), $role ) ) . '</label>';
    echo '<select id="' . esc_attr( $object_id ) . '" name="' . esc_attr( $kind ) . '[' . esc_attr( $role ) . '][object_id]"><option value="0">—</option>';
    foreach ( $candidates as $candidate ) {
        $id = (int) ( $candidate['id'] ?? 0 );
        $label = $candidate['title'] ?? $candidate['name'] ?? (string) $id;
        echo '<option value="' . esc_attr( (string) $id ) . '" ' . selected( $default_id, $id, false ) . '>' . esc_html( (string) $label ) . ' (#' . esc_html( (string) $id ) . ')</option>';
    }
    echo '</select></fieldset>';
}

function provisioning_render_recommendation_card( string $kind, string $role, array $definition, array $recommendation, array $candidates ): void {
    $state = (string) ( $recommendation['state'] ?? 'SUGGESTED_CREATE' );
    $object_id = (int) ( $recommendation['object_id'] ?? 0 );
    $title = (string) ( $definition['title'] ?? $definition['name'] ?? $role );
    $label = provisioning_candidate_label( $object_id, $candidates );
    $state_label = match ( $state ) {
        'CURRENT' => __( 'Đang sử dụng', 'aznet-theme' ),
        'PROVISIONED_REUSE' => __( 'Đã tạo trong lần thiết lập trước', 'aznet-theme' ),
        'AMBIGUOUS' => __( 'Cần xác nhận', 'aznet-theme' ),
        default => __( 'Gợi ý — chưa áp dụng', 'aznet-theme' ),
    };
    echo '<article class="aznet-theme-provision-recommendation aznet-theme-provision-recommendation--' . esc_attr( strtolower( $state ) ) . '">';
    echo '<div><h4>' . esc_html( $title ) . '</h4><p class="aznet-theme-provision-state"><strong>' . esc_html( $state_label ) . '</strong></p>';
    if ( 'AMBIGUOUS' === $state ) {
        echo '<p>' . esc_html__( 'Có nhiều ứng viên hoặc chưa đủ tín hiệu để chọn an toàn. Hãy xác nhận trong phần tùy chỉnh nâng cao.', 'aznet-theme' ) . '</p>';
    } elseif ( 'reuse' === (string) ( $recommendation['action'] ?? '' ) && $object_id > 0 ) {
        echo '<p>' . esc_html( sprintf( __( 'Khuyến nghị sử dụng nguồn hiện có: %1$s (#%2$d)', 'aznet-theme' ), '' !== $label ? $label : $role, $object_id ) ) . '</p>';
    } else {
        echo '<p>' . esc_html( sprintf( __( 'Chưa có nguồn được xác nhận. Khuyến nghị tạo mới “%s”.', 'aznet-theme' ), $title ) ) . '</p>';
    }
    echo '</div></article>';
}

/** @return array<string,array{action:string,object_id:int}> */
function provisioning_sanitize_choices( array $raw ): array {
    $out = [];
    foreach ( $raw as $role => $choice ) {
        $role = sanitize_key( (string) $role );
        if ( ! is_array( $choice ) ) { continue; }
        $action = sanitize_key( (string) ( $choice['action'] ?? 'skip' ) );
        if ( ! in_array( $action, [ 'create','reuse','skip' ], true ) ) { $action = 'skip'; }
        $out[ $role ] = [ 'action' => $action, 'object_id' => absint( $choice['object_id'] ?? 0 ) ];
    }
    return $out;
}

function handle_provisioning_plan(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) ); }
    check_admin_referer( 'aznet_theme_provisioning' );
    $blueprint = isset( $_POST['blueprint'] ) ? sanitize_key( wp_unslash( $_POST['blueprint'] ) ) : '';
    if ( ! in_array( $blueprint, [ 'law01-v1', 'law01-v1-1' ], true ) ) { wp_die( esc_html__( 'Blueprint không hợp lệ.', 'aznet-theme' ) ); }
    $raw_pages = isset( $_POST['pages'] ) && is_array( $_POST['pages'] ) ? wp_unslash( $_POST['pages'] ) : [];
    $raw_categories = isset( $_POST['categories'] ) && is_array( $_POST['categories'] ) ? wp_unslash( $_POST['categories'] ) : [];
    $menu_action = isset( $_POST['menu_action'] ) ? sanitize_key( wp_unslash( $_POST['menu_action'] ) ) : 'skip';
    if ( ! in_array( $menu_action, [ 'create','reuse','skip' ], true ) ) { $menu_action = 'skip'; }
    $selections = [
        'pages' => provisioning_sanitize_choices( $raw_pages ),
        'categories' => provisioning_sanitize_choices( $raw_categories ),
        'menu' => [ 'action' => $menu_action, 'menu_id' => absint( $_POST['menu_id'] ?? 0 ) ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
    ];

    if ( 'law01-v1-1' === $blueprint ) {
        $starter_posts = [];
        $raw_starter_posts = isset( $_POST['starter_posts'] ) && is_array( $_POST['starter_posts'] ) ? wp_unslash( $_POST['starter_posts'] ) : [];
        foreach ( $raw_starter_posts as $role => $value ) {
            $role = sanitize_key( (string) $role );
            $starter_posts[ $role ] = [ 'action' => ! empty( $value ) ? 'create' : 'skip' ];
        }
        $coverage = provisioning_editorial_coverage_recommendations( [ 'categories' => $selections['categories'] ], provisioning_discovery() );
        foreach ( $coverage as $role => $recommendation ) {
            if ( 'SKIP_HAS_PUBLIC_CONTENT' === (string) ( $recommendation['state'] ?? '' ) ) { $starter_posts[ $role ] = [ 'action' => 'skip' ]; }
        }
        $starter_media = [];
        $raw_media = isset( $_POST['starter_media'] ) && is_array( $_POST['starter_media'] ) ? wp_unslash( $_POST['starter_media'] ) : [];
        foreach ( [ 'hero', 'editorial-1', 'editorial-2', 'editorial-3', 'editorial-4' ] as $media_role ) {
            $starter_media[ $media_role ] = ! empty( $raw_media[ $media_role ] );
        }
        $selections['starter_posts'] = $starter_posts;
        $selections['starter_media'] = $starter_media;
        $selections['starter_publication'] = [
            'publish' => ! empty( $_POST['publish_starter_posts'] ),
            'discourage_indexing' => ! empty( $_POST['discourage_indexing'] ),
        ];
    }

    $plan = provisioning_build_plan( $blueprint, $selections, provisioning_discovery() );
    $validation = provisioning_validate_plan( $plan );
    if ( ! $validation['ok'] ) { wp_die( esc_html( implode( ' ', $validation['errors'] ) ) ); }
    set_transient( provisioning_plan_transient_key(), $plan, 15 * MINUTE_IN_SECONDS );
    wp_safe_redirect( provisioning_url( 3 ) ); exit;
}

function handle_provisioning_apply(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) ); }
    check_admin_referer( 'aznet_theme_provisioning' );
    if ( empty( $_POST['confirm_plan'] ) ) { wp_die( esc_html__( 'Bạn cần xác nhận đã xem kế hoạch thay đổi trước khi áp dụng.', 'aznet-theme' ) ); }
    $plan = get_transient( provisioning_plan_transient_key() );
    if ( ! is_array( $plan ) ) { wp_die( esc_html__( 'Kế hoạch đã hết hạn. Hãy chạy lại bước kiểm tra.', 'aznet-theme' ) ); }
    $submitted = isset( $_POST['plan_fingerprint'] ) ? sanitize_text_field( wp_unslash( $_POST['plan_fingerprint'] ) ) : '';
    if ( '' === $submitted || ! hash_equals( provisioning_plan_fingerprint( $plan ), $submitted ) ) { wp_die( esc_html__( 'Kế hoạch xác nhận không khớp.', 'aznet-theme' ) ); }
    $result = provisioning_apply_plan( $plan );
    set_transient( provisioning_result_transient_key(), $result, 30 * MINUTE_IN_SECONDS );
    delete_transient( provisioning_plan_transient_key() );
    wp_safe_redirect( provisioning_url( 4 ) ); exit;
}

function handle_provisioning_restore_indexing(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) ); }
    check_admin_referer( 'aznet_theme_restore_indexing' );
    $result = provisioning_restore_search_visibility();
    set_transient( provisioning_result_transient_key(), [
        'ok' => ! empty( $result['ok'] ),
        'created' => [],
        'reused' => [],
        'errors' => (array) ( $result['errors'] ?? [] ),
        'indexing_restored' => ! empty( $result['changed'] ),
    ], 30 * MINUTE_IN_SECONDS );
    wp_safe_redirect( provisioning_url( 4 ) );
    exit;
}

/** @return string */
function provisioning_summary_group( string $type ): string {
    if ( in_array( $type, [ 'reuse_page','reuse_term','reuse_menu' ], true ) ) { return 'existing'; }
    if ( in_array( $type, [ 'create_page','create_term','create_menu','create_post' ], true ) ) { return 'create'; }
    if ( in_array( $type, [ 'import_media','assign_featured_media' ], true ) ) { return 'media'; }
    return 'config';
}

function provisioning_render_plan_summary( array $plan ): void {
    $groups = [
        'existing' => [ 'label' => __( 'Sử dụng nội dung hiện có', 'aznet-theme' ), 'items' => [] ],
        'create' => [ 'label' => __( 'Sẽ tạo mới', 'aznet-theme' ), 'items' => [] ],
        'media' => [ 'label' => __( 'Sẽ import media', 'aznet-theme' ), 'items' => [] ],
        'config' => [ 'label' => __( 'Sẽ thay đổi cấu hình', 'aznet-theme' ), 'items' => [] ],
    ];
    foreach ( (array) ( $plan['operations'] ?? [] ) as $op ) {
        $group = provisioning_summary_group( (string) ( $op['type'] ?? '' ) );
        $groups[ $group ]['items'][] = (string) ( $op['effect'] ?? '' );
    }
    foreach ( $groups as $group ) {
        if ( [] === $group['items'] ) { continue; }
        echo '<section class="aznet-theme-provision-summary"><h4>' . esc_html( (string) $group['label'] ) . '</h4><ul>';
        foreach ( $group['items'] as $effect ) { echo '<li>' . esc_html( $effect ) . '</li>'; }
        echo '</ul></section>';
    }
}

function render_provisioning_wizard(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) ); }
    $step = isset( $_GET['step'] ) ? max( 1, min( 4, (int) $_GET['step'] ) ) : 1;
    $state = provisioning_discovery();
    $blueprint = provisioning_blueprint( 'law01-v1-1' );
    echo '<div class="aznet-theme-panel aznet-theme-provisioning"><h2>' . esc_html__( 'Thiết lập website nhanh', 'aznet-theme' ) . '</h2><p class="description">' . esc_html( sprintf( 'Bước %d/4', $step ) ) . '</p>';
    if ( 1 === $step ) {
        echo '<h3>' . esc_html__( 'Kiểm tra website', 'aznet-theme' ) . '</h3><dl><dt>Pages</dt><dd>' . esc_html( (string) $state['page_count'] ) . '</dd><dt>Categories</dt><dd>' . esc_html( (string) $state['category_count'] ) . '</dd><dt>Posts</dt><dd>' . esc_html( (string) $state['post_count'] ) . '</dd></dl>';
        echo '<p><strong>' . esc_html__( 'Chế độ đề xuất:', 'aznet-theme' ) . '</strong> <code>' . esc_html( provisioning_site_mode( $state ) ) . '</code></p>';
        echo '<p class="description">' . esc_html__( 'Bước kiểm tra này chỉ đọc dữ liệu và không thay đổi website.', 'aznet-theme' ) . '</p><p><a class="button button-primary" href="' . esc_url( provisioning_url( 2 ) ) . '">' . esc_html__( 'Tiếp tục', 'aznet-theme' ) . '</a></p>';
    } elseif ( 2 === $step && is_array( $blueprint ) ) {
        $mode = provisioning_site_mode( $state );
        $new_site = 'NEW_OR_MOSTLY_EMPTY' === $mode;
        $recommendations = provisioning_recommendations( 'law01-v1-1', $state );
        $editorial = provisioning_editorial_coverage_recommendations( $recommendations, $state );
        echo '<h3>' . esc_html__( 'Thiết lập được khuyến nghị', 'aznet-theme' ) . '</h3><p>' . esc_html__( 'Theme chỉ chuẩn bị gợi ý. Không có mapping hoặc dữ liệu nào được thay đổi trước khi bạn xem và xác nhận kế hoạch ở bước tiếp theo.', 'aznet-theme' ) . '</p>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aznet_theme_provisioning_plan"><input type="hidden" name="blueprint" value="law01-v1-1">';
        wp_nonce_field( 'aznet_theme_provisioning' );
        echo '<div class="aznet-theme-provision-recommendations">';
        foreach ( $blueprint['pages'] as $role => $definition ) { provisioning_render_recommendation_card( 'pages', $role, $definition, (array) ( $recommendations['pages'][ $role ] ?? [] ), $state['pages'] ); }
        foreach ( $blueprint['categories'] as $role => $definition ) { provisioning_render_recommendation_card( 'categories', $role, $definition, (array) ( $recommendations['categories'][ $role ] ?? [] ), $state['categories'] ); }
        echo '</div>';
        echo '<details class="aznet-theme-provision-advanced"><summary>' . esc_html__( 'Tùy chỉnh nâng cao', 'aznet-theme' ) . '</summary>';
        foreach ( $blueprint['pages'] as $role => $definition ) { provisioning_action_select( 'pages', $role, $state['pages'], $new_site, in_array( $role, [ 'home','about','services','contact' ], true ), (array) ( $recommendations['pages'][ $role ] ?? [] ) ); }
        foreach ( $blueprint['categories'] as $role => $definition ) { provisioning_action_select( 'categories', $role, $state['categories'], $new_site, false, (array) ( $recommendations['categories'][ $role ] ?? [] ) ); }
        $menu_default = (int) $state['primary_menu_id'] > 0 ? 'reuse' : 'create';
        echo '<fieldset class="aznet-theme-provision-row"><legend><strong>Primary Menu</strong></legend><label class="screen-reader-text" for="aznet-theme-provision-menu-action">' . esc_html__( 'Hành động cho Primary Menu', 'aznet-theme' ) . '</label><select id="aznet-theme-provision-menu-action" name="menu_action"><option value="create" ' . selected( $menu_default, 'create', false ) . '>Create new</option><option value="reuse" ' . selected( $menu_default, 'reuse', false ) . '>Reuse assigned</option><option value="skip">Skip</option></select><label class="screen-reader-text" for="aznet-theme-provision-menu-id">' . esc_html__( 'ID Primary Menu hiện có', 'aznet-theme' ) . '</label><input id="aznet-theme-provision-menu-id" type="number" min="0" name="menu_id" value="' . esc_attr( (string) $state['primary_menu_id'] ) . '"></fieldset></details>';
        echo '<section class="aznet-theme-provision-starter"><h4>' . esc_html__( 'Nội dung mẫu để Trang chủ có dữ liệu', 'aznet-theme' ) . '</h4>';
        foreach ( $editorial as $role => $choice ) {
            if ( 'SUGGESTED_CREATE' !== (string) ( $choice['state'] ?? '' ) ) { continue; }
            $definition = $blueprint['editorial_examples']['items'][ $role ] ?? null;
            if ( ! is_array( $definition ) ) { continue; }
            echo '<label><input type="checkbox" name="starter_posts[' . esc_attr( $role ) . ']" value="1" checked> ' . esc_html( (string) $definition['title'] ) . '</label><br>';
        }
        echo '<h4>' . esc_html__( 'Media mẫu', 'aznet-theme' ) . '</h4>';
        foreach ( [ 'hero' => 'Ảnh Hero mẫu', 'editorial-1' => 'Ảnh editorial 1', 'editorial-2' => 'Ảnh editorial 2', 'editorial-3' => 'Ảnh editorial 3', 'editorial-4' => 'Ảnh editorial 4' ] as $media_role => $label ) {
            echo '<label><input type="checkbox" name="starter_media[' . esc_attr( $media_role ) . ']" value="1" checked> ' . esc_html( $label ) . '</label><br>';
        }
        echo '</section>';
        if ( $new_site ) {
            echo '<section class="aznet-theme-provision-index-safety"><h4>' . esc_html__( 'Hiển thị bài mẫu trong giai đoạn hoàn thiện', 'aznet-theme' ) . '</h4>';
            echo '<label><input type="checkbox" name="publish_starter_posts" value="1" checked> ' . esc_html__( 'Publish các bài starter để Trang chủ hiển thị đầy đủ ngay', 'aznet-theme' ) . '</label><br>';
            echo '<label><input type="checkbox" name="discourage_indexing" value="1" checked> ' . esc_html__( 'Tạm thời ngăn công cụ tìm kiếm lập chỉ mục website', 'aznet-theme' ) . '</label>';
            echo '<p class="description">' . esc_html__( 'Nếu bỏ lựa chọn ngăn lập chỉ mục, các bài starter sẽ tự chuyển sang Draft thay vì Publish.', 'aznet-theme' ) . '</p></section>';
        } else {
            echo '<input type="hidden" name="publish_starter_posts" value="0"><input type="hidden" name="discourage_indexing" value="0">';
            echo '<p class="description">' . esc_html__( 'Website đang hoạt động: starter Posts mới sẽ giữ Draft. Theme không noindex toàn site và không ghi private SEO meta.', 'aznet-theme' ) . '</p>';
        }
        submit_button( __( 'Dùng các thiết lập được khuyến nghị', 'aznet-theme' ) ); echo '</form>';
    } elseif ( 3 === $step ) {
        $plan = get_transient( provisioning_plan_transient_key() );
        if ( ! is_array( $plan ) ) { echo '<p>' . esc_html__( 'Kế hoạch đã hết hạn. Hãy chạy lại bước kiểm tra.', 'aznet-theme' ) . '</p>'; }
        else {
            echo '<h3>' . esc_html__( 'Xem trước thay đổi', 'aznet-theme' ) . '</h3>';
            provisioning_render_plan_summary( $plan );
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aznet_theme_provisioning_apply"><input type="hidden" name="plan_fingerprint" value="' . esc_attr( provisioning_plan_fingerprint( $plan ) ) . '">';
            wp_nonce_field( 'aznet_theme_provisioning' );
            echo '<p><label><input type="checkbox" name="confirm_plan" value="1" required> ' . esc_html__( 'Tôi đã xem các thay đổi trên và đồng ý áp dụng.', 'aznet-theme' ) . '</label></p>';
            submit_button( __( 'Áp dụng thiết lập', 'aznet-theme' ) ); echo '</form>';
        }
    } else {
        $result = get_transient( provisioning_result_transient_key() );
        if ( ! is_array( $result ) ) { echo '<p>' . esc_html__( 'Không có kết quả thiết lập gần đây.', 'aznet-theme' ) . '</p>'; }
        elseif ( empty( $result['ok'] ) ) {
            echo '<h3>' . esc_html__( 'Thiết lập chưa hoàn tất', 'aznet-theme' ) . '</h3><div class="notice notice-error inline"><p>' . esc_html( implode( ' ', (array) ( $result['errors'] ?? [] ) ) ) . '</p></div>';
            echo '<p><a class="button" href="' . esc_url( provisioning_url( 1 ) ) . '">' . esc_html__( 'Chạy lại kiểm tra', 'aznet-theme' ) . '</a></p>';
        } else {
            $readiness = provisioning_readiness();
            echo '<h3>' . esc_html__( 'Thiết lập hoàn tất — Website đã sẵn sàng để chỉnh nội dung.', 'aznet-theme' ) . '</h3>';
            echo '<p><strong>' . esc_html__( 'Setup status:', 'aznet-theme' ) . '</strong> <code>' . esc_html( (string) $readiness['status'] ) . '</code></p>';
            echo '<p>' . esc_html( sprintf( 'Created: %d · Reused: %d', count( (array) ( $result['created'] ?? [] ) ), count( (array) ( $result['reused'] ?? [] ) ) ) ) . '</p>';
            echo '<div class="aznet-theme-provision-actions">';
            echo '<a class="button button-primary" href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Xem trang chủ', 'aznet-theme' ) . '</a> ';
            echo '<a class="button" href="#launch-checklist">' . esc_html__( 'Chỉnh nội dung cần thiết', 'aznet-theme' ) . '</a> ';
            echo '<a class="button" href="' . esc_url( add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'homepage' ], admin_url( 'admin.php' ) ) ) . '">' . esc_html__( 'Quản lý Trang chủ', 'aznet-theme' ) . '</a></div>';
            echo '<div id="launch-checklist" class="aznet-theme-launch-checklist"><h4>' . esc_html__( 'Trước khi phát hành chính thức', 'aznet-theme' ) . '</h4><ul>';
            foreach ( (array) $readiness['launch_warnings'] as $warning ) { echo '<li>' . esc_html( (string) $warning ) . '</li>'; }
            foreach ( (array) $readiness['optional_warnings'] as $warning ) { echo '<li>' . esc_html( sprintf( 'Phần tùy chọn chưa cấu hình: %s', (string) $warning ) ) . '</li>'; }
            echo '</ul><p><a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '">' . esc_html__( 'Mở danh sách Page để chỉnh nội dung', 'aznet-theme' ) . '</a></p>';
            if ( provisioning_index_restore_available() ) {
                echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Tôi đã hoàn thiện nội dung', 'aznet-theme' ) . '</strong></p><p>' . esc_html__( 'Website đang tạm ngăn công cụ tìm kiếm lập chỉ mục do Thiết lập nhanh.', 'aznet-theme' ) . '</p></div>';
                echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aznet_theme_provisioning_restore_indexing">';
                wp_nonce_field( 'aznet_theme_restore_indexing' );
                submit_button( __( 'Cho phép công cụ tìm kiếm lập chỉ mục trở lại', 'aznet-theme' ), 'secondary', 'submit', false );
                echo '</form>';
            }
            echo '</div>';
        }
    }
    echo '</div>';
}

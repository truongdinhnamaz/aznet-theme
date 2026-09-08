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
if ( ! defined( 'ABSPATH' ) ) { exit; }

function provisioning_url( int $step = 1 ): string { return add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'provisioning', 'step' => max( 1, min( 4, $step ) ) ], admin_url( 'admin.php' ) ); }
function provisioning_plan_transient_key(): string { return 'aznet_theme_provisioning_plan_' . (string) get_current_user_id(); }
function provisioning_result_transient_key(): string { return 'aznet_theme_provisioning_result_' . (string) get_current_user_id(); }

function render_provisioning_invitation(): void {
    echo '<div class="aznet-theme-panel aznet-theme-provisioning-invitation"><h2>' . esc_html__( 'Thiết lập website nhanh', 'aznet-theme' ) . '</h2>';
    echo '<p>' . esc_html__( 'Tạo hoặc map cấu trúc WordPress-native cho mẫu Luật 01. Theme không tự chạy thiết lập khi kích hoạt.', 'aznet-theme' ) . '</p>';
    echo '<p><a class="button button-primary" href="' . esc_url( provisioning_url( 1 ) ) . '">' . esc_html__( 'Thiết lập website nhanh', 'aznet-theme' ) . '</a></p></div>';
}

function provisioning_action_select( string $kind, string $role, array $candidates, bool $new_site, bool $required ): void {
    $default = $new_site || $required ? 'create' : 'skip';
    echo '<div class="aznet-theme-provision-row"><strong>' . esc_html( $role ) . '</strong>';
    echo '<select name="' . esc_attr( $kind ) . '[' . esc_attr( $role ) . '][action]">';
    foreach ( [ 'create' => 'Create new', 'reuse' => 'Reuse existing', 'skip' => 'Skip' ] as $value => $label ) {
        if ( $required && 'skip' === $value ) { continue; }
        echo '<option value="' . esc_attr( $value ) . '" ' . selected( $default, $value, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select><select name="' . esc_attr( $kind ) . '[' . esc_attr( $role ) . '][object_id]"><option value="0">—</option>';
    foreach ( $candidates as $candidate ) {
        $id = (int) ( $candidate['id'] ?? 0 );
        $label = $candidate['title'] ?? $candidate['name'] ?? (string) $id;
        echo '<option value="' . esc_attr( (string) $id ) . '">' . esc_html( (string) $label ) . ' (#' . esc_html( (string) $id ) . ')</option>';
    }
    echo '</select></div>';
}

function handle_provisioning_plan(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) ); }
    check_admin_referer( 'aznet_theme_provisioning' );
    $blueprint = isset( $_POST['blueprint'] ) ? sanitize_key( wp_unslash( $_POST['blueprint'] ) ) : '';
    if ( 'law01-v1' !== $blueprint ) { wp_die( esc_html__( 'Blueprint không hợp lệ.', 'aznet-theme' ) ); }
    $raw_pages = isset( $_POST['pages'] ) && is_array( $_POST['pages'] ) ? wp_unslash( $_POST['pages'] ) : [];
    $raw_categories = isset( $_POST['categories'] ) && is_array( $_POST['categories'] ) ? wp_unslash( $_POST['categories'] ) : [];
    $sanitize_choices = static function ( array $raw ): array {
        $out = [];
        foreach ( $raw as $role => $choice ) {
            $role = sanitize_key( (string) $role );
            if ( ! is_array( $choice ) ) { continue; }
            $action = sanitize_key( (string) ( $choice['action'] ?? 'skip' ) );
            if ( ! in_array( $action, [ 'create','reuse','skip' ], true ) ) { $action = 'skip'; }
            $out[ $role ] = [ 'action' => $action, 'object_id' => absint( $choice['object_id'] ?? 0 ) ];
        }
        return $out;
    };
    $menu_action = isset( $_POST['menu_action'] ) ? sanitize_key( wp_unslash( $_POST['menu_action'] ) ) : 'skip';
    if ( ! in_array( $menu_action, [ 'create','reuse','skip' ], true ) ) { $menu_action = 'skip'; }
    $selections = [
        'pages' => $sanitize_choices( $raw_pages ),
        'categories' => $sanitize_choices( $raw_categories ),
        'menu' => [ 'action' => $menu_action, 'menu_id' => absint( $_POST['menu_id'] ?? 0 ) ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
    ];
    $plan = provisioning_build_plan( $blueprint, $selections, provisioning_discovery() );
    $validation = provisioning_validate_plan( $plan );
    if ( ! $validation['ok'] ) { wp_die( esc_html( implode( ' ', $validation['errors'] ) ) ); }
    set_transient( provisioning_plan_transient_key(), $plan, 15 * MINUTE_IN_SECONDS );
    wp_safe_redirect( provisioning_url( 3 ) ); exit;
}

function handle_provisioning_apply(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) ); }
    check_admin_referer( 'aznet_theme_provisioning' );
    $plan = get_transient( provisioning_plan_transient_key() );
    if ( ! is_array( $plan ) ) { wp_die( esc_html__( 'Kế hoạch đã hết hạn. Hãy chạy lại bước kiểm tra.', 'aznet-theme' ) ); }
    $submitted = isset( $_POST['plan_fingerprint'] ) ? sanitize_text_field( wp_unslash( $_POST['plan_fingerprint'] ) ) : '';
    if ( '' === $submitted || ! hash_equals( provisioning_plan_fingerprint( $plan ), $submitted ) ) { wp_die( esc_html__( 'Kế hoạch xác nhận không khớp.', 'aznet-theme' ) ); }
    $result = provisioning_apply_plan( $plan );
    set_transient( provisioning_result_transient_key(), $result, 30 * MINUTE_IN_SECONDS );
    delete_transient( provisioning_plan_transient_key() );
    wp_safe_redirect( provisioning_url( 4 ) ); exit;
}

function render_provisioning_wizard(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'aznet-theme' ) ); }
    $step = isset( $_GET['step'] ) ? max( 1, min( 4, (int) $_GET['step'] ) ) : 1;
    $state = provisioning_discovery();
    $blueprint = provisioning_blueprint( 'law01-v1' );
    echo '<div class="aznet-theme-panel aznet-theme-provisioning"><h2>' . esc_html__( 'Thiết lập website nhanh', 'aznet-theme' ) . '</h2><p class="description">' . esc_html( sprintf( 'Bước %d/4', $step ) ) . '</p>';
    if ( 1 === $step ) {
        echo '<h3>' . esc_html__( 'Kiểm tra website', 'aznet-theme' ) . '</h3><dl><dt>Pages</dt><dd>' . esc_html( (string) $state['page_count'] ) . '</dd><dt>Categories</dt><dd>' . esc_html( (string) $state['category_count'] ) . '</dd><dt>Posts</dt><dd>' . esc_html( (string) $state['post_count'] ) . '</dd></dl>';
        echo '<p class="description">' . esc_html__( 'Bước kiểm tra này chỉ đọc dữ liệu và không thay đổi website.', 'aznet-theme' ) . '</p><p><a class="button button-primary" href="' . esc_url( provisioning_url( 2 ) ) . '">' . esc_html__( 'Tiếp tục', 'aznet-theme' ) . '</a></p>';
    } elseif ( 2 === $step && is_array( $blueprint ) ) {
        $new_site = (int) $state['page_count'] <= 2;
        echo '<h3>' . esc_html__( 'Chọn cấu trúc Luật 01', 'aznet-theme' ) . '</h3><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aznet_theme_provisioning_plan"><input type="hidden" name="blueprint" value="law01-v1">';
        wp_nonce_field( 'aznet_theme_provisioning' );
        foreach ( $blueprint['pages'] as $role => $definition ) { provisioning_action_select( 'pages', $role, $state['pages'], $new_site, in_array( $role, [ 'home','about','services','contact' ], true ) ); }
        foreach ( $blueprint['categories'] as $role => $definition ) { provisioning_action_select( 'categories', $role, $state['categories'], $new_site, false ); }
        $menu_default = (int) $state['primary_menu_id'] > 0 ? 'reuse' : 'create';
        echo '<div class="aznet-theme-provision-row"><strong>Primary Menu</strong><select name="menu_action"><option value="create" ' . selected( $menu_default, 'create', false ) . '>Create new</option><option value="reuse" ' . selected( $menu_default, 'reuse', false ) . '>Reuse assigned</option><option value="skip">Skip</option></select><input type="number" min="0" name="menu_id" value="' . esc_attr( (string) $state['primary_menu_id'] ) . '"></div>';
        submit_button( __( 'Xem trước thay đổi', 'aznet-theme' ) ); echo '</form>';
    } elseif ( 3 === $step ) {
        $plan = get_transient( provisioning_plan_transient_key() );
        if ( ! is_array( $plan ) ) { echo '<p>' . esc_html__( 'Kế hoạch đã hết hạn. Hãy chạy lại bước kiểm tra.', 'aznet-theme' ) . '</p>'; }
        else {
            echo '<h3>' . esc_html__( 'Xem trước thay đổi', 'aznet-theme' ) . '</h3><table class="widefat striped"><tbody>';
            foreach ( $plan['operations'] as $op ) { echo '<tr><th><code>' . esc_html( (string) $op['type'] ) . '</code></th><td>' . esc_html( (string) $op['effect'] ) . '</td></tr>'; }
            echo '</tbody></table><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aznet_theme_provisioning_apply"><input type="hidden" name="plan_fingerprint" value="' . esc_attr( provisioning_plan_fingerprint( $plan ) ) . '">';
            wp_nonce_field( 'aznet_theme_provisioning' ); submit_button( __( 'Áp dụng thiết lập', 'aznet-theme' ) ); echo '</form>';
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
            echo '</ul><p><a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '">' . esc_html__( 'Mở danh sách Page để chỉnh nội dung', 'aznet-theme' ) . '</a></p></div>';
        }
    }
    echo '</div>';
}

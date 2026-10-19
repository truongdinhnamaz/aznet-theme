<?php
/**
 * D-050C: Optional Site Guard public v1 projection consumer for Theme admin.
 * Theme never owns or mutates Site Guard policy, capabilities or stored state.
 */
namespace AZnet\Theme\Admin;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Read versioned public current-actor status. Missing/invalid provider fails soft.
 * @return array{state:string,data?:array}
 */
function site_guard_admin_projection_v1(): array {
    try {
        $raw = apply_filters( 'aznet_site_guard/v1/admin_projection', null );
    } catch ( \Throwable $exception ) {
        return [ 'state' => 'invalid' ];
    }
    if ( null === $raw ) { return [ 'state' => 'absent' ]; }
    if ( ! is_array( $raw ) ) { return [ 'state' => 'invalid' ]; }
    $expected = [
        'contract','major','provider_version','enforcement','operator_authorized',
        'actor_mode','active_scope','remaining_seconds','scopes'
    ];
    $keys = array_keys( $raw );
    sort( $expected );
    sort( $keys );
    if (
        $expected !== $keys
        || 'aznet_site_guard_admin' !== $raw['contract']
        || 1 !== $raw['major']
        || ! is_string( $raw['provider_version'] )
        || ! preg_match( '/^\d+\.\d+\.\d+(?:-[a-zA-Z0-9.-]+)?$/D', $raw['provider_version'] )
        || ! in_array( $raw['enforcement'], [ 'active', 'emergency' ], true )
        || ! is_bool( $raw['operator_authorized'] )
        || ! in_array( $raw['actor_mode'], [ 'locked', 'temporarily_unlocked' ], true )
        || ! is_int( $raw['remaining_seconds'] )
        || $raw['remaining_seconds'] < 0
        || $raw['remaining_seconds'] > 600
        || ! is_array( $raw['scopes'] )
    ) {
        return [ 'state' => 'invalid' ];
    }
    $scope_ids = [ 'theme_install','theme_switch','theme_delete','homepage_critical' ];
    $actual_scopes = array_keys( $raw['scopes'] );
    sort( $actual_scopes );
    sort( $scope_ids );
    if ( $actual_scopes !== $scope_ids ) { return [ 'state' => 'invalid' ]; }
    foreach ( $raw['scopes'] as $label ) {
        if ( ! is_string( $label ) || '' === trim( $label ) || strlen( $label ) > 220 ) {
            return [ 'state' => 'invalid' ];
        }
    }
    if ( 'locked' === $raw['actor_mode'] ) {
        if ( null !== $raw['active_scope'] || 0 !== $raw['remaining_seconds'] ) {
            return [ 'state' => 'invalid' ];
        }
    } elseif (
        ! $raw['operator_authorized']
        || 'active' !== $raw['enforcement']
        || ! is_string( $raw['active_scope'] )
        || ! in_array( $raw['active_scope'], $scope_ids, true )
        || 0 === $raw['remaining_seconds']
    ) {
        return [ 'state' => 'invalid' ];
    }
    if ( 'emergency' === $raw['enforcement'] && 'locked' !== $raw['actor_mode'] ) {
        return [ 'state' => 'invalid' ];
    }
    return [ 'state' => 'available', 'data' => $raw ];
}

/** Render only a public-safe projection and Site Guard-owned POST forms. */
function render_site_guard_admin_controls(): void {
    $projected = site_guard_admin_projection_v1();
    echo '<section class="aznet-theme-panel aznet-theme-site-guard-panel" aria-labelledby="aznet-site-guard-title">';
    echo '<h3 id="aznet-site-guard-title">Bảo vệ giao diện và trang chủ — AZnet Site Guard</h3>';
    if ( 'available' !== $projected['state'] ) {
        echo '<div class="notice notice-warning inline" role="status"><p><strong>CHƯA XÁC MINH khóa cấp website.</strong> ';
        echo 'absent' === $projected['state']
            ? 'Chưa nhận được trạng thái từ Site Guard. Các thao tác WordPress cấp website chưa được xác nhận là đang được bảo vệ.'
            : 'Hợp đồng Site Guard không tương thích hoặc trả lỗi. Không hiển thị thao tác mở khóa khi trạng thái chưa xác minh.';
        echo '</p></div></section>';
        return;
    }
    $site_guard = $projected['data'];
    echo '<p>Trạng thái do Site Guard v1 cung cấp. Theme chỉ hiển thị; Site Guard kiểm soát mọi quyền và thay đổi trên máy chủ.</p>';
    if ( 'emergency' === $site_guard['enforcement'] ) {
        echo '<div class="notice notice-error inline" role="alert"><p><strong>KHẨN CẤP: BẢO VỆ ĐÃ BỊ BỎ QUA TẠI HOSTING.</strong> ';
        echo 'Kiểm tra và tắt chế độ khôi phục tại máy chủ trước khi tiếp tục. Không thể khẳng định website đang được khóa.</p></div></section>';
        return;
    }
    if ( ! $site_guard['operator_authorized'] ) {
        echo '<p role="status"><strong>Site Guard đang báo chính sách hoạt động.</strong> Tài khoản hiện tại chưa được cấp quyền vận hành Site Guard.</p></section>';
        return;
    }
    $return_url = admin_url( 'admin.php?page=aznet-theme&section=danger-zone' );
    if ( 'temporarily_unlocked' === $site_guard['actor_mode'] ) {
        echo '<div class="notice notice-warning inline" role="status"><p><strong>ĐÃ MỞ KHÓA TẠM THỜI:</strong> ';
        echo esc_html( $site_guard['scopes'][ $site_guard['active_scope'] ] ) . '. Còn tối đa ';
        echo esc_html( (string) $site_guard['remaining_seconds'] ) . ' giây. Hoàn tất thao tác sẽ tự khóa lại.</p></div>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="aznet_site_guard_lock">';
        echo '<input type="hidden" name="guard_return_to" value="' . esc_attr( $return_url ) . '">';
        wp_nonce_field( 'aznet_site_guard_lock' );
        submit_button( 'Khóa Site Guard ngay', 'secondary', 'submit', false );
        echo '</form></section>';
        return;
    }
    echo '<div class="notice notice-warning inline" role="status"><p><strong>ĐANG KHÓA.</strong> ';
    echo 'Chỉ tài khoản được cấp quyền riêng mới mở được một phạm vi thao tác trong tối đa 10 phút. ';
    echo 'Hãy xác nhận bản sao lưu và quyền khôi phục tại hosting trước khi mở.</p></div>';
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    echo '<input type="hidden" name="action" value="aznet_site_guard_unlock">';
    echo '<input type="hidden" name="guard_return_to" value="' . esc_attr( $return_url ) . '">';
    wp_nonce_field( 'aznet_site_guard_unlock' );
    echo '<p><label for="aznet-site-guard-scope"><strong>Phạm vi cần mở khóa</strong></label></p>';
    echo '<select id="aznet-site-guard-scope" name="guard_scope" required>';
    foreach ( $site_guard['scopes'] as $scope => $label ) {
        echo '<option value="' . esc_attr( (string) $scope ) . '">' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
    echo '<p><label for="aznet-site-guard-reason"><strong>Lý do (12–240 byte)</strong></label></p>';
    echo '<textarea id="aznet-site-guard-reason" name="guard_reason" rows="3" minlength="12" maxlength="200" required aria-describedby="aznet-site-guard-reason-help"></textarea>';
    echo '<p id="aznet-site-guard-reason-help" class="description">Không nhập mật khẩu, token hoặc dữ liệu riêng tư.</p>';
    echo '<p><label for="aznet-site-guard-confirm"><strong>Nhập chính xác UNLOCK để xác nhận rủi ro</strong></label></p>';
    echo '<input id="aznet-site-guard-confirm" type="text" name="guard_confirm" autocomplete="off" required>';
    echo '<p>';
    submit_button( 'Tôi hiểu rủi ro — Mở khóa Site Guard tạm thời', 'secondary', 'submit', false );
    echo '</p></form></section>';
}

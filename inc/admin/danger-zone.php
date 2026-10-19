<?php
/**
 * D-050: AZnet Theme-owned high-risk settings operations.
 *
 * This screen is NOT a WordPress site policy / Theme installation guard.
 * Only this Theme's reset/import operations are enforced here.
 *
 * @package AZnetTheme
 */
namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

const DANGER_ZONE_UNLOCK_SECONDS = 600;
const DANGER_ZONE_CAPABILITY = 'aznet_theme_manage_danger_zone';

/**
 * Multi-site has an actual Super Admin boundary. Single-site requires an
 * explicitly granted user capability, never a blanket Administrator grant.
 */
function danger_zone_is_authorized(): bool {
    if ( ! current_user_can( 'manage_options' ) ) {
        return false;
    }
    if ( is_multisite() ) {
        return is_super_admin();
    }
    return current_user_can( DANGER_ZONE_CAPABILITY );
}

/**
 * A private, session-scoped transient key. Do not store plaintext auth tokens.
 * Missing WordPress login session => fail closed.
 */
function danger_zone_transient_key(): ?string {
    $user_id = (int) get_current_user_id();
    $session = (string) wp_get_session_token();
    if ( $user_id <= 0 || '' === $session ) {
        return null;
    }
    return 'aznet_theme_dz_' . substr( hash( 'sha256', $user_id . ':' . $session ), 0, 32 );
}

/** Return an actor/session-bound unlock, with explicit expiry in addition to WP TTL. */
function danger_zone_session_state(): ?array {
    if ( ! danger_zone_is_authorized() ) {
        return null;
    }
    $key = danger_zone_transient_key();
    if ( null === $key ) {
        return null;
    }
    $value = get_transient( $key );
    if (
        ! is_array( $value )
        || (int) ( $value['user_id'] ?? 0 ) !== (int) get_current_user_id()
        || (int) ( $value['expires_at'] ?? 0 ) <= time()
    ) {
        return null;
    }
    return $value;
}

function danger_zone_is_unlocked(): bool {
    return null !== danger_zone_session_state();
}

/**
 * Best-effort Theme-local history only, not an immutable security audit.
 * Site-level security evidence belongs to the separately owned site guard.
 */
function danger_zone_audit_event( string $event, string $detail = '' ): void {
    $event = substr( (string) preg_replace( '/[^a-z0-9_]/', '', strtolower( $event ) ), 0, 56 );
    $detail = substr( trim( strip_tags( $detail ) ), 0, 240 );
    $history = get_option( 'aznet_theme_danger_audit', [] );
    if ( ! is_array( $history ) ) {
        $history = [];
    }
    array_unshift( $history, [
        'at'      => time(),
        'user_id' => (int) get_current_user_id(),
        'event'   => $event,
        'detail'  => $detail,
    ] );
    update_option( 'aznet_theme_danger_audit', array_slice( $history, 0, 25 ), false );
}

/** Start one short-lived unlock only for a separately authorized admin. */
function danger_zone_start_unlock( string $reason ): bool {
    if ( ! danger_zone_is_authorized() ) {
        return false;
    }
    $key = danger_zone_transient_key();
    if ( null === $key ) {
        return false;
    }
    $saved = set_transient( $key, [
        'user_id'    => (int) get_current_user_id(),
        'expires_at' => time() + DANGER_ZONE_UNLOCK_SECONDS,
    ], DANGER_ZONE_UNLOCK_SECONDS );
    if ( ! $saved ) {
        return false;
    }
    danger_zone_audit_event( 'unlocked', $reason );
    return true;
}

/** Successful mutation consumes the unlock; it is not a permanent toggle. */
function danger_zone_complete_action( string $action ): void {
    $key = danger_zone_transient_key();
    if ( null !== $key ) {
        delete_transient( $key );
    }
    danger_zone_audit_event( 'executed_' . $action );
}

function danger_zone_require_authorized(): void {
    if ( ! danger_zone_is_authorized() ) {
        wp_die( esc_html__( 'Bạn không có quyền quản trị đặc biệt đối với khu vực nguy hiểm.', 'aznet-theme' ) );
    }
}

/** Guard every server-side mutation endpoint, not only the HTML button. */
function danger_zone_require_unlocked( string $action ): void {
    danger_zone_require_authorized();
    if ( ! danger_zone_is_unlocked() ) {
        danger_zone_audit_event( 'denied_' . $action, 'locked_or_expired' );
        wp_die( esc_html__( 'Thao tác đã khóa. Hãy mở khóa trong Khu vực nguy hiểm bằng tài khoản được cấp quyền.', 'aznet-theme' ) );
    }
}

function danger_zone_admin_url(): string {
    return add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'danger-zone' ], admin_url( 'admin.php' ) );
}

function handle_danger_zone_unlock(): void {
    danger_zone_require_authorized();
    check_admin_referer( 'aznet_theme_danger_unlock' );
    $confirm = isset( $_POST['danger_confirm'] ) && is_string( $_POST['danger_confirm'] )
        ? trim( sanitize_text_field( wp_unslash( $_POST['danger_confirm'] ) ) )
        : '';
    $reason = isset( $_POST['danger_reason'] ) && is_string( $_POST['danger_reason'] )
        ? trim( sanitize_text_field( wp_unslash( $_POST['danger_reason'] ) ) )
        : '';
    if ( 'MỞ KHÓA' !== $confirm || strlen( $reason ) < 8 || strlen( $reason ) > 240 ) {
        danger_zone_audit_event( 'unlock_rejected', 'invalid_confirmation_or_reason' );
        wp_die( esc_html__( 'Cần nhập chính xác MỞ KHÓA và lý do dài từ 8 đến 240 byte.', 'aznet-theme' ) );
    }
    if ( ! danger_zone_start_unlock( $reason ) ) {
        wp_die( esc_html__( 'Không tạo được phiên mở khóa. Hãy kiểm tra lại phiên đăng nhập.', 'aznet-theme' ) );
    }
    wp_safe_redirect( danger_zone_admin_url() );
    exit;
}

function handle_danger_zone_lock(): void {
    danger_zone_require_authorized();
    check_admin_referer( 'aznet_theme_danger_lock' );
    $key = danger_zone_transient_key();
    if ( null !== $key ) {
        delete_transient( $key );
    }
    danger_zone_audit_event( 'locked_manually' );
    wp_safe_redirect( danger_zone_admin_url() );
    exit;
}

/**
 * Show the one Theme-owned management entry point only to explicitly
 * authorized operators. The Site Guard fallback panel stays invisible here.
 */
function render_danger_zone_entry_link(): void {
    if ( ! danger_zone_is_authorized() ) {
        return;
    }
    echo '<div class="aznet-theme-panel aznet-theme-danger-zone-entry">';
    echo '<h2>' . esc_html__( 'Khu vực nguy hiểm', 'aznet-theme' ) . '</h2>';
    echo '<p>' . esc_html__( 'Quản lý mở khóa tạm thời các thao tác nhạy cảm của Theme và trạng thái Site Guard tại một nơi. Chỉ người được cấp quyền riêng mới thấy mục này.', 'aznet-theme' ) . '</p>';
    echo '<a class="button button-secondary" href="' . esc_url( danger_zone_admin_url() ) . '">';
    echo esc_html__( 'Mở Khu vực nguy hiểm', 'aznet-theme' ) . '</a>';
    echo '</div>';
}

/**
 * Render the Theme-owned controls. The WordPress/site guard deliberately has
 * no false "locked" switch unless a real external owner contract is proven.
 */
function render_danger_zone_screen(): void {
    danger_zone_require_authorized();
    $state = danger_zone_session_state();
    echo '<div class="aznet-theme-panel aznet-theme-danger-zone">';
    echo '<h2>' . esc_html__( 'Khu vực nguy hiểm', 'aznet-theme' ) . '</h2>';
    echo '<p><strong>' . esc_html__( 'Chỉ dành cho quản trị viên được cấp quyền đặc biệt.', 'aznet-theme' ) . '</strong> ';
    echo esc_html__( 'Mọi thay đổi tại đây có thể làm sai giao diện; cần bản sao lưu và kế hoạch khôi phục trước khi thực hiện.', 'aznet-theme' ) . '</p>';

    render_site_guard_admin_controls();


    echo '<h3>' . esc_html__( 'Các thao tác thuộc AZnet Theme', 'aznet-theme' ) . '</h3>';
    if ( null === $state ) {
        echo '<p><strong>' . esc_html__( 'ĐANG KHÓA', 'aznet-theme' ) . '</strong> — ';
        echo esc_html__( 'Đặt lại và nhập cấu hình Theme bị khóa mặc định.', 'aznet-theme' ) . '</p>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
        echo '<input type="hidden" name="action" value="aznet_theme_danger_unlock">';
        wp_nonce_field( 'aznet_theme_danger_unlock' );
        echo '<p><label for="aznet-danger-confirm"><strong>' . esc_html__( 'Nhập MỞ KHÓA để xác nhận:', 'aznet-theme' ) . '</strong></label></p>';
        echo '<input id="aznet-danger-confirm" class="regular-text" type="text" name="danger_confirm" autocomplete="off" required>';
        echo '<p><label for="aznet-danger-reason"><strong>' . esc_html__( 'Lý do thực hiện:', 'aznet-theme' ) . '</strong></label></p>';
        echo '<textarea id="aznet-danger-reason" class="large-text" rows="3" name="danger_reason" minlength="8" maxlength="200" required aria-describedby="aznet-danger-help"></textarea>';
        echo '<p id="aznet-danger-help" class="description">' . esc_html__( 'Mở khóa cho riêng phiên đăng nhập trong tối đa 10 phút; thao tác thành công sẽ khóa lại. Không nhập mật khẩu, token hoặc dữ liệu bí mật trong lý do.', 'aznet-theme' ) . '</p>';
        submit_button( __( 'Tôi hiểu rủi ro — Mở khóa tạm thời', 'aznet-theme' ), 'secondary', 'submit', false );
        echo '</form>';
    } else {
        $remaining = max( 0, (int) $state['expires_at'] - time() );
        echo '<div class="notice notice-warning inline" role="status"><p><strong>';
        echo esc_html__( 'ĐÃ MỞ KHÓA TẠM THỜI.', 'aznet-theme' );
        echo '</strong> ' . esc_html( sprintf( __( 'Còn tối đa %d giây; thao tác thành công sẽ tự khóa lại.', 'aznet-theme' ), $remaining ) ) . '</p></div>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="aznet_theme_danger_lock">';
        wp_nonce_field( 'aznet_theme_danger_lock' );
        submit_button( __( 'Khóa ngay', 'aznet-theme' ), 'secondary', 'submit', false );
        echo '</form>';

        echo '<div class="aznet-theme-panel aznet-theme-danger"><h3>' . esc_html__( 'Đặt lại cấu hình trình bày AZnet Theme', 'aznet-theme' ) . '</h3>';
        echo '<p>' . esc_html__( 'Chỉ đặt lại Theme Mods của AZnet Theme. Có thể làm mất cấu hình bố cục đang hiển thị. Không xóa Page/Post/Media.', 'aznet-theme' ) . '</p>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="aznet_theme_reset_settings">';
        wp_nonce_field( 'aznet_theme_reset_settings' );
        echo '<label><input type="checkbox" name="confirm_reset" value="1" required> ';
        echo esc_html__( 'Tôi đã có bản sao lưu và xác nhận đặt lại các thiết lập trình bày của AZnet Theme.', 'aznet-theme' ) . '</label> ';
        submit_button( __( 'Đặt lại cấu hình Theme', 'aznet-theme' ), 'delete', 'submit', false );
        echo '</form></div>';

        echo '<div class="aznet-theme-panel"><h3>' . esc_html__( 'Nhập thiết lập trình bày JSON', 'aznet-theme' ) . '</h3>';
        echo '<p>' . esc_html__( 'Thay thế toàn bộ thiết lập trình bày hiện tại bằng bản nhập; nên xuất JSON dự phòng trước.', 'aznet-theme' ) . '</p>';
        echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="aznet_theme_import_settings">';
        wp_nonce_field( 'aznet_theme_import_settings' );
        echo '<label for="aznet-danger-import">' . esc_html__( 'Chọn tệp JSON:', 'aznet-theme' ) . '</label> ';
        echo '<input id="aznet-danger-import" type="file" name="aznet_theme_settings_file" accept="application/json,.json" required> ';
        submit_button( __( 'Nhập cấu hình Theme', 'aznet-theme' ), 'secondary', 'submit', false );
        echo '</form></div>';
    }

    $history = get_option( 'aznet_theme_danger_audit', [] );
    if ( is_array( $history ) && [] !== $history ) {
        echo '<h3>' . esc_html__( 'Nhật ký Theme-local (10 sự kiện gần nhất)', 'aznet-theme' ) . '</h3>';
        echo '<p class="description">' . esc_html__( 'Nhật ký giới hạn phục vụ kiểm tra thao tác Theme; không phải nhật ký an ninh bất biến của website.', 'aznet-theme' ) . '</p>';
        echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Thời điểm', 'aznet-theme' ) . '</th><th>' . esc_html__( 'User ID', 'aznet-theme' ) . '</th><th>' . esc_html__( 'Hành động', 'aznet-theme' ) . '</th></tr></thead><tbody>';
        foreach ( array_slice( $history, 0, 10 ) as $event ) {
            if ( ! is_array( $event ) ) { continue; }
            echo '<tr><td>' . esc_html( gmdate( 'Y-m-d H:i:s', (int) ( $event['at'] ?? 0 ) ) . ' UTC' ) . '</td>';
            echo '<td>' . esc_html( (string) (int) ( $event['user_id'] ?? 0 ) ) . '</td>';
            echo '<td>' . esc_html( (string) ( $event['event'] ?? '' ) ) . '</td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
}

<?php
/** Operator-only local Template ZIP upload and rollback UI; no external service. */
namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use function AZnet\Theme\local_template_package_install;
use function AZnet\Theme\local_template_package_records;
use function AZnet\Theme\local_template_package_rollback;

/** Both direct routes are guarded by the same dedicated Theme operator cap. */
function handle_local_template_package_upload(): void {
    require_presentation_operator();
    check_admin_referer( 'aznet_theme_local_template_upload' );
    $confirm = isset( $_POST['template_install_confirm'] ) && '1' === (string) wp_unslash( $_POST['template_install_confirm'] );
    $expected = isset( $_POST['template_package_sha256'] ) && is_string( $_POST['template_package_sha256'] )
        ? strtolower( trim( sanitize_text_field( wp_unslash( $_POST['template_package_sha256'] ) ) ) ) : '';
    $upload = $_FILES['template_package_zip'] ?? null;
    if ( ! $confirm || ! is_array( $upload ) || UPLOAD_ERR_OK !== ( $upload['error'] ?? null )
        || ! is_string( $upload['tmp_name'] ?? null ) || ! is_uploaded_file( $upload['tmp_name'] ) ) {
        wp_die( esc_html__( 'Cần xác nhận, chọn ZIP mẫu và tải lên qua WordPress.', 'aznet-theme' ), '', [ 'response' => 400 ] );
    }
    $result = local_template_package_install( $upload['tmp_name'], $expected );
    $code = (string) ( $result['code'] ?? 'unknown' );
    $url = add_query_arg( [
        'page' => 'aznet-theme', 'section' => 'homepage',
        'template_package_result' => ( $result['ok'] ?? false ) ? 'installed' : $code,
    ], admin_url( 'admin.php' ) );
    wp_safe_redirect( $url );
    exit;
}

function handle_local_template_package_rollback(): void {
    require_presentation_operator();
    check_admin_referer( 'aznet_theme_local_template_rollback' );
    $id = isset( $_POST['template_package_id'] ) && is_string( $_POST['template_package_id'] )
        ? sanitize_key( wp_unslash( $_POST['template_package_id'] ) ) : '';
    $confirm = isset( $_POST['template_rollback_confirm'] ) && '1' === (string) wp_unslash( $_POST['template_rollback_confirm'] );
    if ( ! $confirm || 1 !== preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id ) ) {
        wp_die( esc_html__( 'Xác nhận rollback mẫu không hợp lệ.', 'aznet-theme' ), '', [ 'response' => 400 ] );
    }
    $ok = local_template_package_rollback( $id );
    wp_safe_redirect( add_query_arg( [
        'page' => 'aznet-theme', 'section' => 'homepage',
        'template_package_result' => $ok ? 'rolled_back' : 'rollback_failed',
    ], admin_url( 'admin.php' ) ) );
    exit;
}

/** Render outside the current settings <form>; never nest HTML forms. */
function render_local_template_package_manager(): void {
    if ( ! danger_zone_is_authorized() ) { return; }
    $records = local_template_package_records();
    $result = isset( $_GET['template_package_result'] ) && is_string( $_GET['template_package_result'] )
        ? sanitize_key( wp_unslash( $_GET['template_package_result'] ) ) : '';
    echo '<section class="aznet-theme-panel aznet-theme-local-template-packages" aria-labelledby="aznet-local-template-heading">';
    echo '<h2 id="aznet-local-template-heading">' . esc_html__( 'Cài / cập nhật gói mẫu độc lập (ZIP)', 'aznet-theme' ) . '</h2>';
    echo '<p>' . esc_html__( 'Chỉ chấp nhận gói có manifest phiên bản hợp lệ, CSS khai báo và SHA-256 từ nguồn đáng tin cậy. Không chạy PHP trong ZIP; cập nhật mẫu không thay tệp Core hoặc nội dung WordPress.', 'aznet-theme' ) . '</p>';
    if ( '' !== $result ) {
        $success = in_array( $result, [ 'installed', 'rolled_back' ], true );
        echo '<div class="notice ' . ( $success ? 'notice-success' : 'notice-error' ) . ' inline"><p>' . esc_html( $success ? __( 'Thao tác mẫu đã hoàn tất.', 'aznet-theme' ) : __( 'Gói mẫu bị từ chối / không thể áp dụng: ', 'aznet-theme' ) . $result ) . '</p></div>';
    }
    echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    echo '<input type="hidden" name="action" value="aznet_theme_local_template_upload">';
    wp_nonce_field( 'aznet_theme_local_template_upload' );
    echo '<p><label for="aznet-local-template-zip">' . esc_html__( 'Tệp ZIP mẫu', 'aznet-theme' ) . '</label> <input type="file" id="aznet-local-template-zip" name="template_package_zip" accept=".zip,application/zip" required></p>';
    echo '<p><label for="aznet-local-template-sha">' . esc_html__( 'SHA-256 của ZIP (lấy từ kênh phát hành tin cậy)', 'aznet-theme' ) . '</label> <input type="text" id="aznet-local-template-sha" name="template_package_sha256" pattern="[a-fA-F0-9]{64}" maxlength="64" minlength="64" required style="width:min(100%,37rem)"></p>';
    echo '<p><label><input type="checkbox" name="template_install_confirm" value="1" required> ' . esc_html__( 'Tôi đã xác minh nguồn ZIP/hash, đồng ý cập nhật trình bày mẫu đang sử dụng và giữ bản trước để rollback.', 'aznet-theme' ) . '</label></p>';
    submit_button( __( 'Kiểm tra và cài / cập nhật gói mẫu', 'aznet-theme' ), 'secondary', 'submit', false );
    echo '</form>';
    foreach ( $records as $id => $entry ) {
        if ( ! is_string( $id ) || ! is_array( $entry ) || ! is_array( $entry['active'] ?? null ) ) { continue; }
        $version = (string) ( $entry['active']['version'] ?? '' );
        echo '<div class="aznet-theme-local-template-packages__installed"><p><strong>' . esc_html( $id ) . '</strong> — ' . esc_html( $version ) . '</p>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="aznet_theme_local_template_rollback">';
        echo '<input type="hidden" name="template_package_id" value="' . esc_attr( $id ) . '">';
        wp_nonce_field( 'aznet_theme_local_template_rollback' );
        echo '<label><input type="checkbox" name="template_rollback_confirm" value="1" required> ' . esc_html__( 'Xác nhận khôi phục mẫu trước', 'aznet-theme' ) . '</label> ';
        submit_button( __( 'Rollback mẫu', 'aznet-theme' ), 'secondary', 'submit', false );
        echo '</form></div>';
    }
    echo '</section>';
}

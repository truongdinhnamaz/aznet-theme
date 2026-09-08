<?php
namespace AZnet\Theme\Admin;

use function AZnet\Theme\normalize_settings;
use function AZnet\Theme\settings;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function export_payload(): array {
    return [
        'product' => 'aznet-theme',
        'schema_version' => 1,
        'theme_version' => defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : 'unknown',
        'settings' => settings(),
    ];
}

function handle_export_settings(): void {
    require_settings_capability();
    check_admin_referer( 'aznet_theme_export_settings' );
    $json = wp_json_encode( export_payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    nocache_headers();
    header( 'Content-Type: application/json; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="aznet-theme-settings.json"' );
    echo $json;
    exit;
}

function handle_import_settings(): void {
    require_settings_capability();
    check_admin_referer( 'aznet_theme_import_settings' );
    $file = $_FILES['aznet_theme_settings_file'] ?? null;
    if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) ( $file['error'] ?? -1 ) || (int) ( $file['size'] ?? 0 ) > 65536 ) {
        wp_die( esc_html__( 'Tệp thiết lập không hợp lệ hoặc vượt quá 64 KiB.', 'aznet-theme' ) );
    }
    $tmp = (string) ( $file['tmp_name'] ?? '' );
    if ( '' === $tmp || ! is_uploaded_file( $tmp ) ) {
        wp_die( esc_html__( 'Không xác nhận được tệp tải lên.', 'aznet-theme' ) );
    }
    $raw = file_get_contents( $tmp );
    $payload = is_string( $raw ) ? json_decode( $raw, true ) : null;
    if ( ! is_array( $payload ) || 'aznet-theme' !== ( $payload['product'] ?? null ) || 1 !== (int) ( $payload['schema_version'] ?? 0 ) || ! is_array( $payload['settings'] ?? null ) ) {
        wp_die( esc_html__( 'Tệp thiết lập không đúng định dạng AZnet Theme.', 'aznet-theme' ) );
    }
    set_theme_mod( 'aznet_theme_settings', normalize_settings( $payload['settings'] ) );
    wp_safe_redirect( add_query_arg( [ 'page' => 'aznet-theme', 'imported' => '1' ], admin_url( 'admin.php' ) ) );
    exit;
}

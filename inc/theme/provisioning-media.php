<?php
/** Starter media definitions/import for Law Site Provisioning. */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** @return array<string,array{relative_path:string,path:string,alt:string}> */
function provisioning_media_definitions(): array {
    return [
        'hero' => [
            'relative_path' => 'assets/starter/law01/hero.webp',
            'path' => 'assets/starter/law01/hero.webp',
            'alt' => 'Không gian pháp lý với cân công lý và ánh sáng ấm',
        ],
        'editorial-1' => [
            'relative_path' => 'assets/starter/law01/editorial-1.webp',
            'path' => 'assets/starter/law01/editorial-1.webp',
            'alt' => 'Chi tiết kiến trúc và không gian đô thị chuyên nghiệp',
        ],
        'editorial-2' => [
            'relative_path' => 'assets/starter/law01/editorial-2.webp',
            'path' => 'assets/starter/law01/editorial-2.webp',
            'alt' => 'Chi tiết cân công lý trong không gian làm việc',
        ],
        'editorial-3' => [
            'relative_path' => 'assets/starter/law01/editorial-3.webp',
            'path' => 'assets/starter/law01/editorial-3.webp',
            'alt' => 'Bàn làm việc với sổ ghi chép và chi tiết kim loại',
        ],
        'editorial-4' => [
            'relative_path' => 'assets/starter/law01/editorial-4.webp',
            'path' => 'assets/starter/law01/editorial-4.webp',
            'alt' => 'Không gian văn phòng với cây xanh và ánh sáng tự nhiên',
        ],
    ];
}

function provisioning_media_role_provenance( string $media_role ): string {
    return 'starter_media:' . $media_role;
}

function provisioning_theme_root_path(): string {
    if ( function_exists( 'get_template_directory' ) ) { return rtrim( (string) get_template_directory(), '/\\' ); }
    return dirname( __DIR__, 2 );
}

/** Import one packaged starter asset into WordPress uploads and return attachment ID. */
function provisioning_import_media( string $media_role, string $blueprint, string $run_id ): int {
    $defs = provisioning_media_definitions();
    if ( ! isset( $defs[ $media_role ] ) ) { throw new \RuntimeException( 'Unknown starter media role: ' . $media_role ); }
    if ( ! in_array( $blueprint, provisioning_blueprint_keys(), true ) ) { throw new \RuntimeException( 'Unknown provisioning blueprint for media import.' ); }
    if ( '' === $run_id ) { throw new \RuntimeException( 'Provisioning run ID is required for media import.' ); }

    $provenance_role = provisioning_media_role_provenance( $media_role );
    $owned = provisioning_find_owned_role( $blueprint, 'attachment', $provenance_role );
    if ( $owned > 0 ) {
        $post = get_post( $owned );
        if ( $post instanceof \WP_Post && 'attachment' === $post->post_type ) { return $owned; }
    }

    $relative = (string) $defs[ $media_role ]['relative_path'];
    $source = provisioning_theme_root_path() . '/' . ltrim( $relative, '/\\' );
    if ( ! is_file( $source ) || ! is_readable( $source ) ) { throw new \RuntimeException( 'Starter media source is unavailable: ' . $media_role ); }

    if ( ! function_exists( 'media_handle_sideload' ) ) {
        $file = ABSPATH . 'wp-admin/includes/file.php';
        $media = ABSPATH . 'wp-admin/includes/media.php';
        $image = ABSPATH . 'wp-admin/includes/image.php';
        if ( is_file( $file ) ) { require_once $file; }
        if ( is_file( $media ) ) { require_once $media; }
        if ( is_file( $image ) ) { require_once $image; }
    }
    if ( ! function_exists( 'media_handle_sideload' ) ) { throw new \RuntimeException( 'WordPress media sideload API is unavailable.' ); }

    $tmp = function_exists( 'wp_tempnam' ) ? wp_tempnam( basename( $source ) ) : tempnam( sys_get_temp_dir(), 'aznet-law01-' );
    if ( ! is_string( $tmp ) || '' === $tmp ) { throw new \RuntimeException( 'Unable to allocate temporary starter media file.' ); }
    if ( ! copy( $source, $tmp ) ) { @unlink( $tmp ); throw new \RuntimeException( 'Unable to copy starter media into temporary storage.' ); }

    $sideload = [
        'name' => 'aznet-law01-' . $media_role . '.webp',
        'type' => 'image/webp',
        'tmp_name' => $tmp,
        'error' => 0,
        'size' => (int) filesize( $tmp ),
    ];
    $attachment_id = media_handle_sideload( $sideload, 0, '' );
    if ( is_wp_error( $attachment_id ) || (int) $attachment_id <= 0 ) {
        @unlink( $tmp );
        throw new \RuntimeException( is_wp_error( $attachment_id ) ? $attachment_id->get_error_message() : 'Starter media import failed.' );
    }
    $attachment_id = (int) $attachment_id;
    update_post_meta( $attachment_id, '_wp_attachment_image_alt', (string) $defs[ $media_role ]['alt'] );
    if ( ! provisioning_mark_post( $attachment_id, $blueprint, $provenance_role, $run_id ) ) {
        wp_delete_attachment( $attachment_id, true );
        throw new \RuntimeException( 'Starter media provenance failed for attachment #' . $attachment_id );
    }
    return $attachment_id;
}

<?php
/** WordPress-native index-safety receipt for Law Site Provisioning. */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

const PROVISIONING_INDEX_RECEIPT_OPTION = 'aznet_theme_provisioning_index_receipt';

/** @return array<string,mixed> */
function provisioning_index_receipt(): array {
    $receipt = get_option( PROVISIONING_INDEX_RECEIPT_OPTION, [] );
    return is_array( $receipt ) ? $receipt : [];
}

/** Store only bounded provenance needed to prove ownership of the temporary search-visibility change. */
function provisioning_store_index_receipt( array $receipt ): bool {
    $clean = [
        'schema_version' => 1,
        'blueprint' => in_array( (string) ( $receipt['blueprint'] ?? '' ), provisioning_blueprint_keys(), true ) ? (string) $receipt['blueprint'] : '',
        'run_id' => substr( (string) ( $receipt['run_id'] ?? '' ), 0, 128 ),
        'changed' => ! empty( $receipt['changed'] ),
        'pre_blog_public' => (int) ( $receipt['pre_blog_public'] ?? 1 ),
        'target_blog_public' => (int) ( $receipt['target_blog_public'] ?? 0 ),
        'restored' => ! empty( $receipt['restored'] ),
        'restored_at' => (int) ( $receipt['restored_at'] ?? 0 ),
    ];
    if ( '' === $clean['blueprint'] || '' === $clean['run_id'] ) { return false; }
    return (bool) update_option( PROVISIONING_INDEX_RECEIPT_OPTION, $clean, false ) || provisioning_index_receipt() === $clean;
}

function provisioning_index_restore_available(): bool {
    $receipt = provisioning_index_receipt();
    return 1 === (int) ( $receipt['schema_version'] ?? 0 )
        && ! empty( $receipt['changed'] )
        && empty( $receipt['restored'] )
        && 1 === (int) ( $receipt['pre_blog_public'] ?? 0 )
        && 0 === (int) ( $receipt['target_blog_public'] ?? 1 )
        && 0 === (int) get_option( 'blog_public', 1 );
}

/** @return array{ok:bool,changed:bool,errors:array<int,string>} */
function provisioning_restore_search_visibility(): array {
    if ( ! provisioning_index_restore_available() ) {
        return [ 'ok' => false, 'changed' => false, 'errors' => [ 'Không có bằng chứng rằng Thiết lập nhanh đang sở hữu thay đổi noindex hiện tại.' ] ];
    }
    $receipt = provisioning_index_receipt();
    $updated = update_option( 'blog_public', 1 );
    if ( ! $updated && 1 !== (int) get_option( 'blog_public', 0 ) ) {
        return [ 'ok' => false, 'changed' => false, 'errors' => [ 'Không thể cho phép lập chỉ mục trở lại.' ] ];
    }
    $receipt['restored'] = true;
    $receipt['restored_at'] = function_exists( 'current_time' ) ? (int) current_time( 'timestamp', true ) : time();
    provisioning_store_index_receipt( $receipt );
    return [ 'ok' => true, 'changed' => true, 'errors' => [] ];
}

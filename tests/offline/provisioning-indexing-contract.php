<?php
declare(strict_types=1);

$root = dirname( __DIR__, 2 );
$indexing = $root . '/inc/theme/provisioning-indexing.php';
assert( is_file( $indexing ), 'provisioning-indexing.php must exist' );
$src = (string) file_get_contents( $indexing );
foreach ( [
    'function provisioning_index_receipt(',
    'function provisioning_store_index_receipt(',
    'function provisioning_index_restore_available(',
    'function provisioning_restore_search_visibility(',
    "'aznet_theme_provisioning_index_receipt'",
    "get_option( 'blog_public'",
    "update_option( 'blog_public'",
] as $needle ) {
    assert( str_contains( $src, $needle ), 'Missing index-safety contract marker: ' . $needle );
}
foreach ( [ 'rank_math', 'rankmath', 'yoast', '_robots', 'wpseo_' ] as $forbidden ) {
    assert( ! str_contains( strtolower( $src ), $forbidden ), 'Indexing module must not write/read private SEO-provider storage: ' . $forbidden );
}

$runner = (string) file_get_contents( $root . '/inc/theme/provisioning-runner.php' );
foreach ( [
    "'blog_public' => (int) get_option( 'blog_public', 1 )",
    "'set_search_visibility' === \$type",
    "update_option( 'blog_public', \$target )",
    "update_option( 'blog_public', (int) ( \$receipt['pre']['blog_public'] ?? 1 ) )",
    'provisioning_store_index_receipt(',
    'provisioning_store_starter_fingerprint(',
] as $needle ) {
    assert( str_contains( $runner, $needle ), 'Runner missing index/fingerprint behavior: ' . $needle );
}

$readiness = (string) file_get_contents( $root . '/inc/theme/provisioning-readiness.php' );
foreach ( [ 'provisioning_index_restore_available(', 'provisioning_untouched_starter_post_ids(' ] as $needle ) {
    assert( str_contains( $readiness, $needle ), 'Readiness missing starter/index diagnostic: ' . $needle );
}

$admin = (string) file_get_contents( $root . '/inc/admin/provisioning.php' );
foreach ( [
    'function handle_provisioning_restore_indexing(',
    "check_admin_referer( 'aznet_theme_restore_indexing' )",
    'Cho phép công cụ tìm kiếm lập chỉ mục trở lại',
    'Tôi đã hoàn thiện nội dung',
] as $needle ) {
    assert( str_contains( $admin, $needle ), 'Admin missing explicit index restore flow: ' . $needle );
}
$bootstrap = (string) file_get_contents( $root . '/inc/admin/bootstrap.php' );
assert( str_contains( $bootstrap, 'admin_post_aznet_theme_provisioning_restore_indexing' ) );

$editorial = (string) file_get_contents( $root . '/inc/theme/provisioning-editorial.php' );
foreach ( [
    'PROVISIONING_META_STARTER_FINGERPRINT',
    'function provisioning_starter_post_fingerprint(',
    'function provisioning_store_starter_fingerprint(',
    'function provisioning_untouched_starter_post_ids(',
] as $needle ) {
    assert( str_contains( $editorial, $needle ), 'Starter fingerprint diagnostic missing: ' . $needle );
}

echo "PASS: Law v1.1 WordPress-native index safety, persistent receipt and starter diagnostics\n";

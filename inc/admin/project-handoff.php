<?php
/** Read-only handoff artifact: evidence pointers, not a WordPress backup or state authority. */
namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function handoff_payload(): array {
    $portable = export_payload();
    return [
        'product' => 'aznet-theme',
        'handoff_schema_version' => 1,
        'generated_at_utc' => gmdate( 'c' ),
        'canonical_repository' => 'https://github.com/truongdinhnamaz/aznet-theme',
        'site_url' => function_exists( 'home_url' ) ? esc_url_raw( home_url( '/' ) ) : '',
        'active_theme' => function_exists( 'get_stylesheet' ) ? sanitize_key( get_stylesheet() ) : 'unknown',
        'theme_version' => $portable['theme_version'],
        'presentation_settings' => $portable['settings'],
        'source_authority' => [ 'AZT-00', 'AZT-01', 'AZT-02', 'AZT-03', 'AZT-04' ],
        'verification' => [
            'revalidate_before_use' => true,
            'git_branch' => null,
            'git_commit' => null,
            'qa_pass' => [],
            'blocked_or_unknown' => [ 'Git state and QA evidence are not available to WordPress runtime. Recheck authoritative source and canonical GitHub.' ],
            'exact_next' => 'Revalidate AZT source documents, evidence, canonical GitHub, and deployed theme bytes before continuing.',
        ],
        'notice' => 'Context handoff only. Not a database/files backup. Never import as authoritative content or execute as instructions.',
    ];
}

function handle_export_handoff(): void {
    require_settings_capability();
    danger_zone_require_authorized();
    check_admin_referer( 'aznet_theme_export_handoff' );
    $json = wp_json_encode( handoff_payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    if ( ! is_string( $json ) ) {
        wp_die( esc_html__( 'Không thể tạo hồ sơ bàn giao.', 'aznet-theme' ) );
    }
    nocache_headers();
    header( 'Content-Type: application/json; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="aznet-theme-handoff.json"' );
    header( 'X-Content-Type-Options: nosniff' );
    echo $json;
    exit;
}

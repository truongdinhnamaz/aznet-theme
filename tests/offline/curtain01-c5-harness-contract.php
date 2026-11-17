<?php
/**
 * C5 harness-only regression: operator capability and reproducible HTTP diagnostics.
 * Checks the GitHub Actions test fixture; does not alter production Theme behavior.
 */
declare(strict_types=1);

$workflow = file_get_contents( __DIR__ . '/../../.github/workflows/curtain01-c5-pilot-preview.yml' );
if ( ! is_string( $workflow ) || '' === $workflow ) {
    fwrite( STDERR, "RED C5_HARNESS: workflow unavailable\n" );
    exit( 1 );
}

$required = [
    'isolated_test_operator' => 'wp user add-cap admin aznet_theme_manage_danger_zone --path=/tmp/wp --allow-root',
    'explicit_capability_assertion' => 'user_can( get_user_by( "login", "admin" ), "aznet_theme_manage_danger_zone" )',
    'http_probe_status_diagnostic' => 'C5_DIAG_HTTP',
    'worker_state_diagnostic' => 'C5_DIAG_PROCESS',
    'error_trap' => "trap 'c5_fail' ERR",
    'diagnostic_artifact' => '/tmp/curtain01-c5/runtime-diagnostics.txt',
    'single_health_probe' => 'if http_code=$(curl -sS --connect-timeout 3 --max-time 15',
];
$failed = [];
foreach ( $required as $name => $needle ) {
    if ( ! str_contains( $workflow, $needle ) ) {
        $failed[] = $name;
    }
}
// A successful readiness GET is already stored in home.html.
// Doing an unconditional second GET introduces non-deterministic server load.
if ( str_contains( $workflow, 'curl -fsS http://127.0.0.1:8080/ > /tmp/curtain01-c5/home.html' ) ) {
    $failed[] = 'duplicate_unconditional_homepage_fetch';
}
if ( $failed ) {
    fwrite( STDERR, 'RED C5_HARNESS: ' . implode( ', ', $failed ) . "\n" );
    exit( 1 );
}
echo "PASS C5_HARNESS: dedicated operator, one HTTP readiness probe, process diagnostics and preserved artifact\n";

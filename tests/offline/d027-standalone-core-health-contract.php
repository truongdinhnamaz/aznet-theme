<?php
$health = file_get_contents( __DIR__ . '/../../inc/admin/system-health.php' );
$control = file_get_contents( __DIR__ . '/../../inc/admin/control-center.php' );

$fail = static function ( string $message ): void {
    fwrite( STDERR, "FAIL: {$message}\n" );
    exit( 1 );
};

if ( false === strpos( $health, "'standalone_core'" ) ) {
    $fail( 'system_health_report must expose standalone_core' );
}
if ( false === strpos( $health, "'optional_integrations'" ) ) {
    $fail( 'system_health_report must expose optional_integrations' );
}
if ( false === strpos( $control, 'Standalone Core' ) ) {
    $fail( 'System Health UI must label Standalone Core separately' );
}
if ( false === strpos( $control, 'Optional Integrations' ) ) {
    $fail( 'System Health UI must label Optional Integrations separately' );
}
if ( preg_match( '/required plugin|missing dependency|install required plugins/i', $control ) ) {
    $fail( 'Core admin must not advertise optional providers as required dependencies' );
}

echo "PASS: D-027 standalone Core health contract\n";

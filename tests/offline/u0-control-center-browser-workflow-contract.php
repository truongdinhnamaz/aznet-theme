<?php
$root = dirname( __DIR__, 2 );
$workflow = $root . '/.github/workflows/u0-control-center-browser.yml';
$browser = $root . '/tests/browser/u0-control-center-browser.mjs';
foreach ( [ $workflow, $browser ] as $path ) {
    if ( ! is_file( $path ) ) {
        fwrite( STDERR, "missing U0 L4 path: {$path}\n" );
        exit( 1 );
    }
}
$workflow_source = file_get_contents( $workflow );
$browser_source = file_get_contents( $browser );
$workflow_required = [
    'WordPress', 'mysql:8.0', "php-version: '8.1'", "node-version: '22'",
    'playwright@1.55.0', '@axe-core/playwright@4.10.2',
    'u0-control-center-browser.mjs', 'u0-control-center-browser-evidence',
];
foreach ( $workflow_required as $needle ) {
    if ( false === strpos( $workflow_source, $needle ) ) {
        fwrite( STDERR, "missing L4 workflow contract: {$needle}\n" );
        exit( 1 );
    }
}
$browser_required = [
    '1440', '1000', '1024', '768',
    'AZnet Theme', 'Logo', 'Primary Menu', 'WooCommerce',
    'Lưu thiết lập nền', 'Đặt lại thiết lập AZnet Theme',
    'horizontal overflow', 'keyboard Tab',
    "['critical', 'serious']", 'AxeBuilder', 'screenshot',
];
foreach ( $browser_required as $needle ) {
    if ( false === strpos( $browser_source, $needle ) ) {
        fwrite( STDERR, "missing L4 browser contract: {$needle}\n" );
        exit( 1 );
    }
}
echo "PASS: U0 Control Center L4 workflow contract\n";

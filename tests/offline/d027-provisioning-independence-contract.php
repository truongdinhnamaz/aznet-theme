<?php
$root = dirname( __DIR__, 2 );
$paths = [
    'inc/admin/provisioning.php',
    'inc/admin/homepage.php',
    'inc/theme/provisioning-plan.php',
    'inc/theme/provisioning-runner.php',
    'inc/theme/provisioning-media.php',
];

$sources = [];
foreach ( $paths as $relative ) {
    $path = $root . '/' . $relative;
    if ( ! is_file( $path ) ) {
        fwrite( STDERR, "FAIL: missing Core provisioning path {$relative}\n" );
        exit( 1 );
    }
    $sources[ $relative ] = (string) file_get_contents( $path );
}
$combined = implode( "\n", $sources );

$forbidden = [
    'wp_remote_get(',
    'wp_remote_post(',
    'plugins_api(',
    'Plugin_Upgrader',
    'activate_plugin(',
    'install_plugin_install_status(',
    'rootprofile_',
    '_rootprofile_',
    'choiceguide_',
    '_choiceguide_',
];
foreach ( $forbidden as $needle ) {
    if ( false !== stripos( $combined, $needle ) ) {
        fwrite( STDERR, "FAIL: Core provisioning path contains mandatory external/provider dependency {$needle}\n" );
        exit( 1 );
    }
}

$runner = $sources['inc/theme/provisioning-runner.php'];
foreach ( [ 'wp_insert_post(', 'wp_create_nav_menu(' ] as $needle ) {
    if ( false === strpos( $runner, $needle ) ) {
        fwrite( STDERR, "FAIL: provisioning runner missing WordPress public API {$needle}\n" );
        exit( 1 );
    }
}

$media = $sources['inc/theme/provisioning-media.php'];
foreach ( [ 'assets/starter/law01/', 'media_handle_sideload(' ] as $needle ) {
    if ( false === strpos( $media, $needle ) ) {
        fwrite( STDERR, "FAIL: starter media path missing local WordPress-owned import contract {$needle}\n" );
        exit( 1 );
    }
}

if ( preg_match( '~https?://[^\s\'\"]+~i', $media ) ) {
    fwrite( STDERR, "FAIL: starter media definitions must not require a remote URL\n" );
    exit( 1 );
}

echo "PASS: D-027 standalone provisioning independence contract\n";

<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = (string) file_get_contents( $root . '/template-parts/homepage/law-01/profile.php' );
$authoring = (string) file_get_contents( $root . '/inc/theme/homepage-authoring.php' );
$surface = (string) file_get_contents( $root . '/inc/theme/homepage-surface-map.php' );

foreach ( [
    "homepage_effective_source_value( 'law-01', 'about', \$settings )",
    "homepage_effective_source_value( 'law-01', 'team', \$settings )",
    "homepage_effective_source_value( 'law-01', 'services', \$settings )",
    "homepage_source_value( 'law-01', 'process' )",
    "setting( 'homepage_process_page', 0 )",
    "homepage_source_value( 'law-01', 'faq' )",
    "setting( 'homepage_faq_page', 0 )",
] as $needle ) {
    if ( ! str_contains( $profile, $needle ) ) {
        fwrite( STDERR, "FAIL: Law 01 Profile compatibility path missing {$needle}\n" );
        exit( 1 );
    }
}

foreach ( [
    "'about'         => [ 'type' => 'page', 'key' => 'homepage_law01_about_page', 'legacy' => 'homepage_about_page' ]",
    "'team'          => [ 'type' => 'page', 'key' => 'homepage_law01_team_page', 'legacy' => 'homepage_team_page'",
    "'services'      => [ 'type' => 'page', 'key' => 'homepage_law01_services_page', 'legacy' => 'homepage_services_page'",
    "if ( '' !== \$legacy && array_key_exists( \$legacy, \$settings ) )",
    "return \$settings[ \$legacy ];",
] as $needle ) {
    if ( ! str_contains( $authoring, $needle ) ) {
        fwrite( STDERR, "FAIL: Law 01 effective resolver lost proven legacy compatibility: {$needle}\n" );
        exit( 1 );
    }
}

if ( ! str_contains( $surface, 'return homepage_source_value( $preset, $slot, $settings );' ) ) {
    fwrite( STDERR, "FAIL: Effective source wrapper no longer delegates to the scoped-or-legacy resolver.\n" );
    exit( 1 );
}

echo "PASS: Law 01 Profile preserves proven sources through effective scoped resolution with legacy compatibility.\n";

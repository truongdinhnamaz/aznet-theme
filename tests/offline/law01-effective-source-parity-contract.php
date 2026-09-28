<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = (string) file_get_contents( $root . '/template-parts/homepage/law-01/profile.php' );

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

echo "PASS: Law 01 Profile preserves proven sources and uses bounded scoped fallback for Process/FAQ.\n";

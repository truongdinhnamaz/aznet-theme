<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$map = [
    'about.php'    => [ 'about' ],
    'team.php'     => [ 'team' ],
    'services.php' => [ 'services' ],
    'profile.php'  => [ 'about', 'team', 'services', 'process', 'faq' ],
    'process.php'  => [ 'process' ],
    'faq.php'      => [ 'faq' ],
    'latest.php'   => [ 'knowledge' ],
    'topics.php'   => [ 'knowledge' ],
    'analysis.php' => [ 'case_analysis' ],
    'news.php'     => [ 'legal_news' ],
    'final-cta.php'=> [ 'contact' ],
];

$failed = [];
foreach ( $map as $file => $slots ) {
    $source = (string) file_get_contents( $root . '/template-parts/homepage/law-01/' . $file );
    foreach ( $slots as $slot ) {
        $needle = "homepage_source_value( 'law-01', '" . $slot . "'";
        if ( ! str_contains( $source, $needle ) ) {
            $failed[] = $file . ' missing ' . $slot . ' resolver';
        }
    }
    if ( preg_match( "/setting\(\s*'homepage_(?:services|about|team|knowledge|case_analysis|legal_news|process|faq|contact)_[^']*'/", $source ) ) {
        $failed[] = $file . ' still reads legacy Homepage source directly';
    }
}

if ( [] !== $failed ) {
    fwrite( STDERR, 'FAIL: ' . implode( '; ', $failed ) . "\n" );
    exit( 1 );
}

echo "PASS: all Law 01 content surfaces consume effective scoped sources.\n";

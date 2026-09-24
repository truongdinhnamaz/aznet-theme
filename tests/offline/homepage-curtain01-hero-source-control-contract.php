<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$source = (string) file_get_contents($root . '/inc/admin/homepage.php');

$expected = "( 'curtain-01' === \$active_preset ? [ 'hero', 'proof', 'about', 'knowledge', 'process', 'projects', 'contact' ] : [] )";
if ( ! str_contains( $source, $expected ) ) {
    fwrite( STDERR, "FAIL: Curtain 01 Hero is not available in the preset-scoped source selector\n" );
    exit( 1 );
}

echo "PASS: Curtain 01 Hero has a preset-scoped source selector\n";

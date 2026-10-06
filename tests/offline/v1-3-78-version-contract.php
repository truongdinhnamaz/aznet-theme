<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');

if (strpos($style, 'Version: 1.3.78') === false) {
    fwrite(STDERR, "expected Theme header Version: 1.3.78\n");
    exit(1);
}

if (strpos($functions, "define( 'AZNET_THEME_VERSION', '1.3.78' );") === false) {
    fwrite(STDERR, "expected AZNET_THEME_VERSION 1.3.78\n");
    exit(2);
}

echo "PASS: AZnet Theme 1.3.78 metadata contract\n";

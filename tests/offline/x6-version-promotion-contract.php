<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');

if ($style === false || $functions === false) {
    throw new RuntimeException('Cannot read Theme version declarations.');
}

preg_match('/^Version:\s*([^\r\n]+)/m', $style, $styleMatch);
preg_match("/define\( 'AZNET_THEME_VERSION', '([^']+)' \);/", $functions, $phpMatch);

if (trim($styleMatch[1] ?? '') !== '1.2.0') {
    throw new RuntimeException('style.css Theme Version must be exactly 1.2.0 in X6 promotion.');
}

if (($phpMatch[1] ?? '') !== '1.2.0') {
    throw new RuntimeException('AZNET_THEME_VERSION must be exactly 1.2.0 in X6 promotion.');
}

echo "PASS: X6 Theme metadata promoted atomically to 1.2.0\n";

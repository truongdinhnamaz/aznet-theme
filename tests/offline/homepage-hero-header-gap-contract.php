<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-variants.css');
$selector = '.aznet-theme-homepage--law-01 .aznet-theme-law01-hero--library .aznet-theme-law01-hero__library-content {';
$pos = strpos($css, $selector);

if (false === $pos) {
    fwrite(STDERR, "FAIL: Hero Library content rule missing.\n");
    exit(1);
}

$block = substr($css, $pos, 260);
if (! str_contains($block, 'padding-block: 0;')) {
    fwrite(STDERR, "FAIL: Hero Library still adds vertical shell gap below header.\n");
    exit(1);
}

echo "PASS: Hero Library starts flush below the header.\n";

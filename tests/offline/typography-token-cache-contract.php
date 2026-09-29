<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$assets_path = $root . '/inc/theme/assets.php';
assert(is_file($assets_path), 'Theme asset registration file must exist.');

$assets = (string) file_get_contents($assets_path);
$needle = 'asset_content_version( \'/assets/css/tokens.css\', $version )';
$count = substr_count($assets, $needle);

assert(
    2 === $count,
    sprintf(
        'Theme token stylesheet must use content-aware cache busting in both frontend and editor enqueues; found %d matching calls.',
        $count
    )
);


$footer_needle = "asset_content_version( '/assets/css/components/site-footer.css', $version )";
assert(
    str_contains($assets, $footer_needle),
    'Footer stylesheet must use content-aware cache busting after Footer publishes.'
);

echo "PASS: typography and Footer cache-busting contract\n";

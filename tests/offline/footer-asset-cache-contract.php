<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$assets_path = $root . '/inc/theme/assets.php';
assert(is_file($assets_path), 'Theme asset registration file must exist.');

$assets = (string) file_get_contents($assets_path);
$needle = "asset_content_version( '/assets/css/components/site-footer.css', $version )";

assert(
    str_contains($assets, $needle),
    'Footer stylesheet must use content-aware cache busting so production HTML/CSS cannot drift after a Footer publish.'
);

echo "PASS: Footer asset cache-busting contract\n";

<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$source = (string) file_get_contents($root . '/inc/admin/homepage.php');
$handler = (string) file_get_contents($root . '/inc/admin/homepage-hero.php');
assert(str_contains($source, 'type="text" name="homepage_hero_primary_url"'), 'Primary Hero link must accept site-relative paths such as /lien-he/.');
assert(str_contains($source, 'type="text" name="homepage_hero_secondary_url"'), 'Secondary Hero link must accept site-relative paths such as /dich-vu/.');
assert(str_contains($handler, 'esc_url_raw'), 'Hero link values must still be sanitized server-side.');
echo "PASS: Hero simple form accepts relative links safely\n";

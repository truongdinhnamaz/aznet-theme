<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$hero = (string) file_get_contents($root . '/template-parts/homepage/law-01/hero.php');

assert(str_contains($hero, "setting( 'homepage_hero_block', 0 )"), 'D-030 synced Hero block must be the preferred source.');
assert(str_contains($hero, 'homepage_block_reference'), 'Synced Hero content must resolve through the bounded wp_block helper.');
assert(str_contains($hero, "setting( 'homepage_hero_page', 0 )"), 'Dedicated Hero Page must remain as a legacy compatibility source.');
assert(str_contains($hero, 'homepage_page_reference'), 'Legacy Hero Page must still resolve through the bounded Page helper.');

// Regression: upgrading a site from <=1.3.3 must not make the whole Hero disappear
// merely because the new dedicated mapping has not been configured yet.
assert(str_contains($hero, "get_bloginfo( 'name' )"), 'Legacy Site Title fallback must keep existing Law 01 Hero visible when no dedicated Hero Page is mapped.');
assert(str_contains($hero, "get_bloginfo( 'description' )"), 'Legacy Site Tagline fallback must retain the previous Hero slogan when no dedicated Hero Page is mapped.');
assert(str_contains($hero, 'get_the_ID()'), 'Legacy fallback must retain the existing Front Page presentation source.');
assert(str_contains($hero, 'has_post_thumbnail( $front_id )'), 'Legacy fallback must retain the existing Front Page Hero image.');
assert(strpos($hero, "setting( 'homepage_hero_block', 0 )") < strpos($hero, "setting( 'homepage_hero_page', 0 )"), 'Synced Hero block must resolve before the legacy Page path.');
assert(str_contains($hero, 'if ( $hero instanceof \\WP_Post )'), 'Legacy Page source must remain before the oldest Site/Front Page fallback.');

echo "PASS: Law 01 Hero backward compatibility contract\n";

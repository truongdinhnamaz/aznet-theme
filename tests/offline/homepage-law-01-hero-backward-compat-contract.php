<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$hero = (string) file_get_contents($root . '/template-parts/homepage/law-01/hero.php');

$block_resolver = "homepage_source_value( 'law-01', 'hero' )";
$page_resolver  = "homepage_source_value( 'law-01', 'hero_page' )";

assert(str_contains($hero, $block_resolver), 'D-038 Law 01 Hero block must resolve through the preset-scoped compatibility resolver.');
assert(str_contains($hero, 'homepage_block_reference'), 'Synced Hero content must resolve through the bounded wp_block helper.');
assert(str_contains($hero, $page_resolver), 'Legacy Hero Page must remain available through the preset-scoped compatibility resolver.');
assert(str_contains($hero, 'homepage_page_reference'), 'Legacy Hero Page must still resolve through the bounded Page helper.');
assert(! str_contains($hero, "setting( 'homepage_hero_block', 0 )"), 'Law 01 renderer must not direct-read the generic Hero block key.');
assert(! str_contains($hero, "setting( 'homepage_hero_page', 0 )"), 'Law 01 renderer must not direct-read the generic Hero Page key.');

// Regression: upgrading a site from <=1.3.3 must not make the whole Hero disappear
// merely because scoped mappings are still resolving through legacy compatibility.
assert(str_contains($hero, "get_bloginfo( 'name' )"), 'Legacy Site Title fallback must keep existing Law 01 Hero visible when no dedicated Hero Page is mapped.');
assert(str_contains($hero, "get_bloginfo( 'description' )"), 'Legacy Site Tagline fallback must retain the previous Hero slogan when no dedicated Hero Page is mapped.');
assert(str_contains($hero, 'get_the_ID()'), 'Legacy fallback must retain the existing Front Page presentation source.');
assert(str_contains($hero, 'has_post_thumbnail( $front_id )'), 'Legacy fallback must retain the existing Front Page Hero image.');
assert(strpos($hero, $block_resolver) < strpos($hero, $page_resolver), 'Published synced Hero resolution must be attempted before the legacy Page path.');
assert(str_contains($hero, 'if ( $hero instanceof \\WP_Post )'), 'Legacy Page source must remain before the oldest Site/Front Page fallback.');

echo "PASS: Law 01 Hero backward compatibility contract\n";

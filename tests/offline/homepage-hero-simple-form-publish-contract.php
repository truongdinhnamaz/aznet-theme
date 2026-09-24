<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$heroAdmin = (string) file_get_contents($root . '/inc/admin/homepage-hero.php');
$contentMap = (string) file_get_contents($root . '/inc/theme/homepage-content-map.php');

assert(str_contains($contentMap, "'publish' !== \$post->post_status"), 'Public Hero resolver must remain publish-only.');

$start = strpos($heroAdmin, 'function handle_homepage_hero_form_save');
$end = false === $start ? false : strpos($heroAdmin, 'function handle_homepage_hero_form_upgrade', $start);
$save = false !== $start && false !== $end ? substr($heroAdmin, $start, $end - $start) : '';
assert('' !== $save, 'Hero simple-form save handler must remain discoverable.');
assert(str_contains($save, "'draft' === \$hero->post_status"), 'Saving a draft Hero must handle its draft state explicitly.');
assert(str_contains($save, "current_user_can('publish_posts')"), 'Publishing a draft Hero from the simple form must require publish_posts capability.');
assert(str_contains($save, "'post_status'=>'publish'"), 'Saving a draft Hero must publish it so the public Homepage can render the new content.');

echo "PASS: Hero simple form publishes draft before public handoff\n";

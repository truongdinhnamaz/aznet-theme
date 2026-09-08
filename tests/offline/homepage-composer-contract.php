<?php
/** Homepage Composer boundary contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$front = file_get_contents($root . '/front-page.php');
$composer_path = $root . '/inc/theme/homepage-composer.php';

if (! is_file($composer_path)) {
    fwrite(STDERR, "FAIL: homepage composer module missing\n");
    exit(1);
}

$composer = file_get_contents($composer_path);
assert(substr_count($front, 'the_content();') === 1, 'Front Page must preserve exactly one the_content execution');
assert(substr_count($front, 'id="main"') === 1, 'Front Page must keep one Theme main landmark');
assert(str_contains($front, 'while ( have_posts() )'));
assert(str_contains($front, 'the_post();'));
assert(str_contains($front, 'homepage_composer_active()'));
assert(str_contains($front, 'render_homepage_before_content()'));
assert(str_contains($front, 'render_homepage_after_content()'));

foreach (['set_transient(', 'update_option(', 'update_post_meta(', 'wp_insert_post(', 'wp_update_post(', 'wp_delete_post('] as $forbidden) {
    assert(! str_contains($composer, $forbidden), "Composer must not persist/mutate content: {$forbidden}");
}

assert(str_contains($composer, "'law-01'"));
assert(str_contains($composer, 'homepage_ledger_reset'));
assert(str_contains($composer, 'homepage_ledger_add'));
assert(str_contains($composer, 'homepage_ledger_ids'));

echo "PASS: Homepage Composer boundary contract\n";

<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$admin = $root . '/inc/admin/homepage.php';
if (! is_file($admin)) { fwrite(STDERR, "FAIL: Homepage admin module missing\n"); exit(1); }
$source = file_get_contents($admin);
$control = file_get_contents($root . '/inc/admin/control-center.php');
$bootstrap = file_get_contents($root . '/inc/admin/bootstrap.php');
foreach (['render_homepage_settings', 'homepage_slot_statuses', 'READY', 'EMPTY', 'UNMAPPED', 'INVALID', 'PROVIDER_UNAVAILABLE', 'law-01', 'homepage_knowledge_terms][', 'get_pages(', 'get_categories('] as $required) {
    assert(str_contains($source, $required), "Missing Homepage admin contract: {$required}");
}
assert(str_contains($control, "'homepage'"));
assert(str_contains($control, "'Trang chủ'"));
assert(str_contains($control, 'render_homepage_settings()'));
assert(str_contains($bootstrap, "require_once __DIR__ . '/homepage.php';"));
foreach (['wp_insert_post(', 'wp_update_post(', 'wp_delete_post(', 'wp_insert_term(', 'wp_delete_term('] as $forbidden) {
    assert(! str_contains($source, $forbidden), "Admin must not mutate native content: {$forbidden}");
}
echo "PASS: Homepage Control Center contract\n";

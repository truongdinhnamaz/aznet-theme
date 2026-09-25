<?php
/** Process/FAQ authoring must reach the WordPress content that actually renders. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$admin = file_get_contents($root . '/inc/admin/homepage.php');

foreach ([
    [$admin, 'Sửa tiêu đề & mô tả', 'Process/FAQ quick edit label remains misleading'],
    [$admin, 'Sửa các bước', 'Process full-content editor action missing'],
    [$admin, 'Sửa câu hỏi', 'FAQ full-content editor action missing'],
    [$admin, 'get_edit_post_link(', 'native WordPress editor bridge missing'],
] as [$haystack, $needle, $message]) {
    if (! is_string($haystack) || ! str_contains($haystack, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

echo "PASS: Process/FAQ backend reaches rendered WordPress content\n";

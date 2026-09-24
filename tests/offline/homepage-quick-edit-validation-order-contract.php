<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$source = (string) file_get_contents($root . '/inc/admin/homepage-authoring.php');

$start = strpos($source, 'function handle_homepage_quick_edit_source(): void');
$end = strpos($source, 'function homepage_duplicate_native_post', $start === false ? 0 : $start);

if ($start === false || $end === false || $end <= $start) {
    fwrite(STDERR, "FAIL: Quick Edit function boundary not found\n");
    exit(1);
}

$quick_edit = substr($source, $start, $end - $start);
$image_validation = strpos($quick_edit, 'wp_attachment_is_image(');
$post_mutation = strpos($quick_edit, 'wp_update_post(');

if ($image_validation === false || $post_mutation === false) {
    fwrite(STDERR, "FAIL: Expected image validation and Page mutation calls are missing\n");
    exit(1);
}

if ($image_validation > $post_mutation) {
    fwrite(STDERR, "FAIL: Quick Edit validates featured image after mutating the Page\n");
    exit(1);
}

echo "PASS: Homepage Quick Edit validates featured image before Page mutation\n";

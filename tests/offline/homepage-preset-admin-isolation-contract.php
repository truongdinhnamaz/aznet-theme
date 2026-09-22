<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$admin_path = $root . '/inc/admin/homepage.php';
$admin = is_file($admin_path) ? file_get_contents($admin_path) : false;

if (! is_string($admin)) {
    fwrite(STDERR, "FAIL: Homepage admin source missing.\n");
    exit(1);
}

if (! str_contains($admin, "'curtain-01' => 'Rèm 01'")) {
    fwrite(STDERR, "FAIL: Rèm 01 must be exposed as an independent Homepage preset.\n");
    exit(1);
}

if (! str_contains($admin, "if ( 'law-01' === (string) \\$s['homepage_preset'] )")) {
    fwrite(STDERR, "FAIL: Law 01 variant control must be isolated to the Law 01 preset.\n");
    exit(1);
}

if (! str_contains($admin, 'Rèm 01: website rèm và giải pháp kiểm soát ánh sáng.')) {
    fwrite(STDERR, "FAIL: Rèm 01 preset must have its own admin description.\n");
    exit(1);
}

echo "PASS: Homepage preset admin isolation contract\n";

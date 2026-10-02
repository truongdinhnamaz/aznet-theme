<?php
/** D-043 C2 Template Library manifest projection contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/template-loader.php';

$store =& \AZnet\Theme\template_manifest_store();
$store = [];
$summary = \AZnet\Theme\load_local_template_manifests();

if (! is_array($summary) || [] !== ($summary['rejected'] ?? [])) {
    fwrite(STDERR, "FAIL: bundled template manifests did not load cleanly.\n");
    exit(1);
}

$expected = [
    'law-01' => ['Luật 01', 'Luật', 'Website dịch vụ pháp lý kết hợp nội dung chuyên môn.'],
    'curtain-01' => ['Rèm 01', 'Rèm / Nội thất', 'Website rèm và giải pháp kiểm soát ánh sáng.'],
    'industrial-01' => ['Industrial 01', 'Thiết bị công nghiệp', 'Website B2B bán thiết bị công nghiệp và phụ kiện.'],
];

foreach ($expected as $id => [$name, $category, $description]) {
    $manifest = \AZnet\Theme\template_manifest($id);
    if (! is_array($manifest)) {
        fwrite(STDERR, "FAIL: missing bundled manifest {$id}.\n");
        exit(1);
    }
    if ($name !== ($manifest['name'] ?? null) || $category !== ($manifest['category'] ?? null) || $description !== ($manifest['description'] ?? null)) {
        fwrite(STDERR, "FAIL: Template Library display metadata is not owned by manifest {$id}.\n");
        exit(1);
    }
    if ($id !== ($manifest['presentation']['homepage_preset'] ?? null)) {
        fwrite(STDERR, "FAIL: Homepage preset projection mismatch for {$id}.\n");
        exit(1);
    }
}

$admin = (string) file_get_contents($root . '/inc/admin/homepage.php');

if (! str_contains($admin, 'template_manifests()')) {
    fwrite(STDERR, "FAIL: Template Library does not consume Template Manifest Registry.\n");
    exit(1);
}
if (! str_contains($admin, "['presentation']['homepage_preset']")) {
    fwrite(STDERR, "FAIL: Template Library does not project Homepage preset from manifest presentation metadata.\n");
    exit(1);
}
foreach ([
    "'template_id'   => 'law-01'",
    "'template_id'   => 'curtain-01'",
    "'template_id'   => 'industrial-01'",
] as $literal) {
    if (str_contains($admin, $literal)) {
        fwrite(STDERR, "FAIL: generic Template Library still hard-codes bundled template entries: {$literal}.\n");
        exit(1);
    }
}

echo "PASS: D-043 C2 Template Library manifest projection\n";

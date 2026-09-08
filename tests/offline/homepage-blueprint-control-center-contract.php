<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$path = $root . '/inc/admin/control-center.php';
$source = (string) file_get_contents($path);

function homepage_control_fail(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

$required = [
    'function render_homepage_setup_card(): void',
    'Homepage Setup',
    'Classic Editor',
    'get_edit_post_link',
    "get_option( 'show_on_front'",
    "get_option( 'page_on_front'",
];

foreach ($required as $needle) {
    if (! str_contains($source, $needle)) {
        homepage_control_fail('Homepage Setup guidance missing: ' . $needle);
    }
}

$start = strpos($source, 'function render_homepage_setup_card(): void');
$end = strpos($source, 'function render_control_center(): void');
if (false === $start || false === $end || $end <= $start) {
    homepage_control_fail('unable to isolate Homepage Setup helper');
}

$helper = substr($source, $start, $end - $start);
foreach ([
    'wp_insert_post(',
    'wp_update_post(',
    'update_option(',
    'set_theme_mod(',
    'post_content',
    'delete_post_meta(',
    'update_post_meta(',
] as $needle) {
    if (str_contains($helper, $needle)) {
        homepage_control_fail('Homepage Setup must remain read-only: ' . $needle);
    }
}

foreach ([
    'Block Inserter',
    'Homepage — Professional Services',
    'AZnet — Pages',
] as $needle) {
    if (str_contains($helper, $needle)) {
        homepage_control_fail('Homepage Setup must not instruct a Block Editor-only workflow after Classic mode is selected: ' . $needle);
    }
}

if (! str_contains($source, 'render_homepage_setup_card();')) {
    homepage_control_fail('Homepage Setup card is not rendered on Control Center Overview');
}

if (str_contains($helper, 'choiceguide_') || str_contains($helper, 'rootprofile_') || str_contains($helper, 'convertflow')) {
    homepage_control_fail('Homepage Setup guidance must not inspect provider state');
}

echo "PASS: Homepage Classic Editor Control Center guidance contract\n";

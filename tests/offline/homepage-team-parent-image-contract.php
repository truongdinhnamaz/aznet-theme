<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$homepage = (string) file_get_contents($root . '/inc/admin/homepage.php');
$authoring = (string) file_get_contents($root . '/inc/admin/homepage-authoring.php');

foreach ([
    'function homepage_quick_edit_featured_image_allowed',
    "'law-01' === \$preset && 'team' === \$slot",
    'homepage_source_value( $preset, $slot ) !== $source_id',
] as $needle) {
    if (! str_contains($authoring, $needle)) {
        fwrite(STDERR, "FAIL: missing Team parent image policy: {$needle}\n");
        exit(1);
    }
}

if (! str_contains($homepage, 'homepage_quick_edit_featured_image_allowed( $preset, $slot, $source_id, $descriptor )')) {
    fwrite(STDERR, "FAIL: Homepage quick edit does not consume Team parent image policy.\n");
    exit(1);
}

if (! str_contains($authoring, 'if ( homepage_quick_edit_featured_image_allowed( $preset, $slot, $source_id, $descriptor ) )')) {
    fwrite(STDERR, "FAIL: Quick-edit save still mutates Team parent Featured Image.\n");
    exit(1);
}

echo "PASS: Team parent hides/preserves Featured Image while member Pages retain image authoring.\n";

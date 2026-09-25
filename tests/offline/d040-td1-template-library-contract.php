<?php
/** D-040 TD1 Template Library admin presentation RED contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$adminPath = $root . '/inc/admin/homepage.php';
$admin = file_get_contents($adminPath);

if (! is_string($admin)) {
    fwrite(STDERR, "FAIL: cannot read Homepage admin implementation.\n");
    exit(1);
}

$required = [
    'function homepage_template_library_items()' => 'normalized Template Library catalog helper is missing',
    'aznet-theme-template-library' => 'Template Library root marker is missing',
    'aznet-theme-template-library__grid' => 'Template Library card grid is missing',
    'homepage-template-search' => 'Template Library search control is missing',
    'homepage-template-category' => 'Template Library category filter is missing',
];

foreach ($required as $needle => $message) {
    if (! str_contains($admin, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

if (str_contains($admin, "field_select( 'homepage_preset', __( 'Mẫu', 'aznet-theme' )")) {
    fwrite(STDERR, "FAIL: Homepage preset still uses the flat select instead of the Template Library presentation.\n");
    exit(1);
}

foreach (["'off'", "'law-01'", "'curtain-01'"] as $preset) {
    if (! str_contains($admin, $preset)) {
        fwrite(STDERR, "FAIL: retained preset compatibility missing for {$preset}.\n");
        exit(1);
    }
}

$forbidden = [
    'get_post_meta(',
    '$wpdb',
    'choiceguide_',
    'rootprofile_',
    'payment',
    'checkout',
];

foreach ($forbidden as $needle) {
    if (str_contains($admin, $needle)) {
        fwrite(STDERR, "FAIL: TD1 admin presentation crossed a forbidden/domain boundary: {$needle}.\n");
        exit(1);
    }
}

echo "PASS: D-040 TD1 Template Library admin presentation contract\n";

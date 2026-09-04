<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$paths = [
    $root . '/front-page.php',
];

function fail_test(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

$forbidden = [
    'choiceguide_',
    '_choiceguide_',
    'get_option(',
    'get_post_meta(',
    'update_option(',
    'update_post_meta(',
    '$wpdb',
    'HomepageNavigationTargetResolver',
    'DecisionEvent',
    'journey_section_viewed',
    'journey_cta_clicked',
    'product_categories',
    'featured_products',
    'organization_trust',
    'final_conversion',
    'template_include',
    'template_redirect',
];

foreach ($paths as $path) {
    if (!is_file($path)) {
        fail_test('missing production path: ' . $path);
    }
    $source = file_get_contents($path);
    if (false === $source) {
        fail_test('unable to read production path: ' . $path);
    }
    foreach ($forbidden as $needle) {
        if (str_contains($source, $needle)) {
            fail_test(basename($path) . " contains forbidden Homepage/domain coupling: {$needle}");
        }
    }
}

echo "PASS: F3 Homepage ownership static contract\n";

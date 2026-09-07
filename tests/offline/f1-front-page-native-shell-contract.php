<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$template = $root . '/front-page.php';

function fail_test(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

if (!is_file($template)) {
    fail_test('front-page.php missing');
}

$source = file_get_contents($template);
if (false === $source) {
    fail_test('unable to read front-page.php');
}

$required = [
    'get_header()',
    'get_footer()',
    'have_posts()',
    'the_post()',
    'the_content()',
    'id="main"',
    'aznet-theme-main',
];

foreach ($required as $needle) {
    if (!str_contains($source, $needle)) {
        fail_test("front-page.php must preserve {$needle}");
    }
}

if (1 !== substr_count($source, '<main')) {
    fail_test('front-page.php must contain exactly one main element');
}

$forbidden = [
    'choiceguide_',
    'ConvertFlow',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'template_include',
    'template_redirect',
    'is_page(',
];

foreach ($forbidden as $needle) {
    if (str_contains($source, $needle)) {
        fail_test("front-page.php contains forbidden dependency/heuristic: {$needle}");
    }
}

echo "PASS: F1 native front-page shell contract\n";

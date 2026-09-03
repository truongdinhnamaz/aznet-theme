<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function fail_gate(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function require_file_text(string $root, string $path): string
{
    $full = $root . '/' . $path;
    $text = @file_get_contents($full);
    if (!is_string($text)) {
        fail_gate("cannot read {$path}");
    }
    return $text;
}

$production = [
    'inc/integrations/rootprofile.php',
    'inc/theme/contact-surface.php',
    'inc/theme/profile-surface.php',
    'inc/theme/rootprofile-current-surface.php',
    'inc/theme/bootstrap.php',
];

$forbidden = [
    'TruongDinhNam\\RootProfile\\',
    'rootprofile_person',
    'rootprofile_organization',
    'get_query_var(',
    'is_page(',
    'get_queried_object_id(',
    'template_include',
    'template_redirect',
    'the_content',
];

foreach ($production as $path) {
    $text = require_file_text($root, $path);
    foreach ($forbidden as $needle) {
        if (str_contains($text, $needle)) {
            fail_gate("forbidden ownership/takeover token {$needle} found in {$path}");
        }
    }

    if (preg_match('/[\"\']_rootprofile_[A-Za-z0-9_-]+[\"\']/', $text) === 1) {
        fail_gate("forbidden RootProfile storage key literal found in {$path}");
    }

    if ('inc/integrations/rootprofile.php' !== $path) {
        foreach (['apply_filters(', 'has_filter('] as $filter_api) {
            if (str_contains($text, $filter_api)) {
                fail_gate("public provider filter API {$filter_api} is only allowed in inc/integrations/rootprofile.php; found in {$path}");
            }
        }
    }
}

$dispatcher = require_file_text($root, 'inc/theme/rootprofile-current-surface.php');
foreach (['add_action(', 'add_filter('] as $registration) {
    if (str_contains($dispatcher, $registration)) {
        fail_gate("dispatcher must stay dormant; found {$registration}");
    }
}

$bootstrap = require_file_text($root, 'inc/theme/bootstrap.php');
if (substr_count($bootstrap, "add_action( 'after_setup_theme', __NAMESPACE__ . '\\\\setup' );") !== 1) {
    fail_gate('bootstrap must preserve exactly one after_setup_theme setup hook');
}
if (substr_count($bootstrap, "add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\\\enqueue_assets' );") !== 1) {
    fail_gate('bootstrap must preserve exactly one wp_enqueue_scripts global asset hook');
}
if (substr_count($bootstrap, 'add_action(') !== 2) {
    fail_gate('bootstrap must contain only the two pre-existing add_action registrations');
}
if (str_contains($bootstrap, 'add_filter(')) {
    fail_gate('bootstrap must not register filters in E5-B');
}
if (str_contains($bootstrap, 'render_current_rootprofile_surface')) {
    fail_gate('bootstrap must not register or invoke render_current_rootprofile_surface()');
}
if (substr_count($bootstrap, "require_once __DIR__ . '/rootprofile-current-surface.php';") !== 1) {
    fail_gate('bootstrap must load the dormant dispatcher exactly once');
}

echo "PASS: E5-B ownership / no-takeover static contract\n";

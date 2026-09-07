<?php
/**
 * R2 native pattern-library static contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root          = dirname(__DIR__, 2);
$patterns_file = $root . '/inc/theme/patterns.php';
$bootstrap     = $root . '/inc/theme/bootstrap.php';

if (! is_file($patterns_file)) {
    fwrite(STDERR, "FAIL: R2 pattern registry missing inc/theme/patterns.php\n");
    exit(1);
}

$registry = file_get_contents($patterns_file);
$boot     = file_get_contents($bootstrap);

if (false === $registry || false === $boot) {
    fwrite(STDERR, "FAIL: unable to read R2 pattern registry/bootstrap\n");
    exit(1);
}

$required_categories = [
    'aznet-theme-hero',
    'aznet-theme-trust',
    'aznet-theme-content',
    'aznet-theme-commerce',
    'aznet-theme-utility',
];

foreach ($required_categories as $category) {
    if (! str_contains($registry, "'{$category}'")) {
        fwrite(STDERR, "FAIL: missing Theme-owned pattern category {$category}\n");
        exit(1);
    }
}

if (! str_contains($registry, 'function register_pattern_categories(): void')) {
    fwrite(STDERR, "FAIL: register_pattern_categories() missing or signature changed\n");
    exit(1);
}

if (! str_contains($boot, "require_once __DIR__ . '/patterns.php';")) {
    fwrite(STDERR, "FAIL: bootstrap does not load inc/theme/patterns.php\n");
    exit(1);
}

if (! str_contains($boot, "add_action( 'init', __NAMESPACE__ . '\\\\register_pattern_categories' );")) {
    fwrite(STDERR, "FAIL: pattern categories are not wired to init\n");
    exit(1);
}

$forbidden = [
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'choiceguide_',
    '_choiceguide_',
    'rootprofile_',
    'wp_insert_post(',
    'register_post_type(',
];

$scan_files = [$patterns_file];
$native_patterns = glob($root . '/patterns/*.php') ?: [];
$scan_files = array_merge($scan_files, $native_patterns);

foreach ($native_patterns as $file) {
    $content = file_get_contents($file);
    if (false === $content) {
        fwrite(STDERR, "FAIL: unable to read {$file}\n");
        exit(1);
    }

    foreach (['Title:', 'Slug:', 'Categories:', 'Description:'] as $header) {
        if (! str_contains($content, $header)) {
            fwrite(STDERR, "FAIL: " . basename($file) . " missing {$header} header\n");
            exit(1);
        }
    }
}

foreach ($scan_files as $file) {
    $content = file_get_contents($file);
    if (false === $content) {
        fwrite(STDERR, "FAIL: unable to scan {$file}\n");
        exit(1);
    }

    foreach ($forbidden as $needle) {
        if (str_contains($content, $needle)) {
            fwrite(STDERR, "FAIL: forbidden domain/storage token {$needle} in " . basename($file) . "\n");
            exit(1);
        }
    }
}

echo "PASS: R2 native pattern static contract\n";

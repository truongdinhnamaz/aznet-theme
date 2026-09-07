<?php
/**
 * R2 Hero pattern contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$expected = [
    'hero-centered.php' => 'aznet-theme/hero-centered',
    'hero-split.php'    => 'aznet-theme/hero-split',
    'hero-inverse.php'  => 'aznet-theme/hero-inverse',
    'hero-commerce.php' => 'aznet-theme/hero-commerce',
];

foreach ($expected as $filename => $slug) {
    $path = $root . '/patterns/' . $filename;
    if (! is_file($path)) {
        fwrite(STDERR, "FAIL: missing R2 Hero pattern {$filename}\n");
        exit(1);
    }

    $content = file_get_contents($path);
    if (false === $content) {
        fwrite(STDERR, "FAIL: unable to read {$filename}\n");
        exit(1);
    }

    foreach ([
        'Title:',
        'Slug: ' . $slug,
        'Categories: aznet-theme-hero',
        'Description:',
    ] as $needle) {
        if (! str_contains($content, $needle)) {
            fwrite(STDERR, "FAIL: {$filename} missing {$needle}\n");
            exit(1);
        }
    }

    if (preg_match('/<!--\s*wp:[a-z0-9-]+\/[a-z0-9-]+/i', $content)) {
        fwrite(STDERR, "FAIL: {$filename} uses a non-core block namespace\n");
        exit(1);
    }

    if (preg_match('/(?:href|src)=["\']https?:\/\//i', $content)) {
        fwrite(STDERR, "FAIL: {$filename} contains a hard-coded external URL\n");
        exit(1);
    }

    if (! str_contains($content, 'aznet-theme-pattern')) {
        fwrite(STDERR, "FAIL: {$filename} does not consume the Theme pattern class vocabulary\n");
        exit(1);
    }
}

echo "PASS: R2 Hero patterns contract\n";

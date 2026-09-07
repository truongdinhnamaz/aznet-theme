<?php
/**
 * R2 Trust and CTA pattern contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$expected = [
    'trust-logo-strip.php'    => 'aznet-theme/trust-logo-strip',
    'trust-feature-grid.php'  => 'aznet-theme/trust-feature-grid',
    'trust-testimonials.php'  => 'aznet-theme/trust-testimonials',
    'cta-full-width.php'      => 'aznet-theme/cta-full-width',
];

foreach ($expected as $filename => $slug) {
    $path = $root . '/patterns/' . $filename;
    if (! is_file($path)) {
        fwrite(STDERR, "FAIL: missing R2 Trust/CTA pattern {$filename}\n");
        exit(1);
    }

    $content = file_get_contents($path);
    if (false === $content) {
        fwrite(STDERR, "FAIL: unable to read {$filename}\n");
        exit(1);
    }

    foreach ([
        'Slug: ' . $slug,
        'Categories: aznet-theme-trust',
        'aznet-theme-pattern',
    ] as $needle) {
        if (! str_contains($content, $needle)) {
            fwrite(STDERR, "FAIL: {$filename} missing {$needle}\n");
            exit(1);
        }
    }

    if (! str_contains(mb_strtolower($content), 'mẫu')) {
        fwrite(STDERR, "FAIL: {$filename} does not clearly identify replaceable example content\n");
        exit(1);
    }

    if (preg_match('/(?:href|src)=["\']https?:\/\//i', $content)) {
        fwrite(STDERR, "FAIL: {$filename} contains a hard-coded external URL\n");
        exit(1);
    }

    foreach (['đã phục vụ hơn', 'được chứng nhận bởi', 'khách hàng tin dùng trên toàn quốc'] as $claim) {
        if (str_contains(mb_strtolower($content), $claim)) {
            fwrite(STDERR, "FAIL: {$filename} contains an authoritative trust claim\n");
            exit(1);
        }
    }
}

echo "PASS: R2 Trust and CTA patterns contract\n";

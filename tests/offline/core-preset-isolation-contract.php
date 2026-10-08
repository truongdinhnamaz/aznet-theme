<?php
/**
 * Core/preset isolation contract.
 *
 * Generic Theme production paths must not recognize a pilot by WordPress Page ID,
 * site hostname, or Rèm Quốc Anh-specific authored-class prefix. Preset-scoped
 * assets/manifests and historical evidence are intentionally outside this scan.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$pageCssPath = $root . '/assets/css/components/page.css';
$pageCss = file_get_contents($pageCssPath);

if (false === $pageCss) {
    fwrite(STDERR, "FAIL: unable to read generic page.css\n");
    exit(1);
}

if (1 === preg_match('/\.page-id-[0-9]+\b/', $pageCss, $match)) {
    fwrite(STDERR, "FAIL: generic page.css contains pilot Page-ID coupling: {$match[0]}\n");
    exit(2);
}

if (str_contains($pageCss, 'rqa-')) {
    fwrite(STDERR, "FAIL: generic page.css contains RQA-specific presentation identifiers\n");
    exit(3);
}

$genericRoots = [
    $root . '/assets/css/components',
    $root . '/assets/js',
    $root . '/inc/theme',
];

$forbiddenHosts = [
    'remquocanh.vn',
    'lstamduchn.vn',
    'minhnguyen.vn',
];

$violations = [];

foreach ($genericRoots as $genericRoot) {
    if (! is_dir($genericRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($genericRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $path = $file->getPathname();
        $relative = str_replace($root . '/', '', $path);

        // D-043 templates are extension scope, not generic Core.
        if (str_starts_with($relative, 'inc/theme/templates/')) {
            continue;
        }

        $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        if (! in_array($extension, ['php', 'css', 'js'], true)) {
            continue;
        }

        $source = file_get_contents($path);
        if (false === $source) {
            fwrite(STDERR, "FAIL: unable to read {$relative}\n");
            exit(4);
        }

        foreach ($forbiddenHosts as $host) {
            if (str_contains($source, $host)) {
                $violations[] = "{$relative}:{$host}";
            }
        }
    }
}

if ([] !== $violations) {
    fwrite(STDERR, "FAIL: generic Core contains pilot hostname coupling: " . implode(', ', $violations) . "\n");
    exit(5);
}

echo "PASS: generic Core is isolated from pilot Page IDs, RQA presentation identifiers, and pilot hostnames.\n";

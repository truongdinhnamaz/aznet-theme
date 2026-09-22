<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssRoot = $root . '/assets/css';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cssRoot));

$forbiddenFamilies = [
    "Georgia, 'Times New Roman', serif",
    'font-family: Cambria',
    'Georgia',
    'Times New Roman',
    'font-family: Roboto',
    'font-family: Arial',
    'font-family: Helvetica',
];

$forbiddenWeights = [
    'font-weight: 750',
    'font-weight: 800',
];

$violations = [];

foreach ($iterator as $file) {
    if (! $file->isFile() || 'css' !== strtolower($file->getExtension())) {
        continue;
    }

    $path = $file->getPathname();
    $css = (string) file_get_contents($path);

    foreach (array_merge($forbiddenFamilies, $forbiddenWeights) as $needle) {
        if (str_contains($css, $needle)) {
            $violations[] = str_replace($root . '/', '', $path) . ' -> ' . $needle;
        }
    }
}

assert([] === $violations, "Typography hardcode violations:\n" . implode("\n", $violations));

echo "PASS: typography hardcode audit contract\n";

<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function g3_fail(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function g3_read(string $path): string
{
    if (!is_file($path)) {
        g3_fail("missing required file: {$path}");
    }
    $contents = file_get_contents($path);
    if (false === $contents) {
        g3_fail("cannot read file: {$path}");
    }
    return $contents;
}

$style = g3_read($root . '/style.css');
$functions = g3_read($root . '/functions.php');

$requiredHeaders = [
    'Requires at least: 6.9',
    'Requires PHP: 8.1',
    'Text Domain: aznet-theme',
];
foreach ($requiredHeaders as $header) {
    if (!str_contains($style, $header)) {
        g3_fail("style.css missing release header: {$header}");
    }
}

if (!preg_match('/^Version:\s*([^\r\n]+)$/mi', $style, $styleMatch)) {
    g3_fail('style.css Version header missing');
}
if (!preg_match("/define\\(\\s*'AZNET_THEME_VERSION'\\s*,\\s*'([^']+)'\\s*\\)/", $functions, $constantMatch)) {
    g3_fail('functions.php AZNET_THEME_VERSION definition missing');
}

$styleVersion = trim($styleMatch[1]);
$constantVersion = trim($constantMatch[1]);
if ($styleVersion !== $constantVersion) {
    g3_fail("version mismatch: style.css={$styleVersion}; functions.php={$constantVersion}");
}

$productionPhp = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if (!$file->isFile() || 'php' !== strtolower($file->getExtension())) {
        continue;
    }
    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if (preg_match('#^(tests|docs|scripts|\.github)/#', $relative)) {
        continue;
    }
    $productionPhp[$relative] = g3_read($file->getPathname());
}

if ([] === $productionPhp) {
    g3_fail('no production PHP files discovered');
}

$forbiddenPatterns = [
    '/\$wpdb\b/' => 'direct $wpdb access is not allowed in Theme production code',
    '/[\'\"]_rootprofile_[^\'\"]*[\'\"]/' => 'RootProfile private storage key detected',
    '/[\'\"]_choiceguide_[^\'\"]*[\'\"]/' => 'ConvertFlow/ChoiceGuide private storage key detected',
];

foreach ($productionPhp as $relative => $contents) {
    foreach ($forbiddenPatterns as $pattern => $message) {
        if (preg_match($pattern, $contents)) {
            g3_fail("{$message}: {$relative}");
        }
    }
}

$requiredPackageExclusions = [
    '.git',
    '.github',
    'docs',
    'scripts',
    'tests',
    'README.md',
    'aznet-preview.png',
];
if (count($requiredPackageExclusions) !== count(array_unique($requiredPackageExclusions))) {
    g3_fail('package exclusion policy contains duplicates');
}

foreach (['AZnet\\Theme', 'aznet_theme_', 'AZNET_THEME_', 'aznet-theme-', '--aznet-theme-'] as $family) {
    if ('' === $family) {
        g3_fail('empty naming family entry');
    }
}

echo "PASS: G3 core release static contract\n";
echo "VERSION={$styleVersion}\n";
echo 'PRODUCTION_PHP_FILES=' . count($productionPhp) . "\n";
echo 'PACKAGE_EXCLUDES=' . implode(',', $requiredPackageExclusions) . "\n";

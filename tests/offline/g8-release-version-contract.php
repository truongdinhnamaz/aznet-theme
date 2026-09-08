<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$stylePath = $root . '/style.css';
$functionsPath = $root . '/functions.php';

function g8_fail(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

$style = file_get_contents($stylePath);
$functions = file_get_contents($functionsPath);
if (false === $style || false === $functions) {
    g8_fail('release metadata files are unreadable');
}

if (!preg_match('/^Version:\s*([^\r\n]+)$/mi', $style, $styleMatch)) {
    g8_fail('style.css Version header missing');
}
if (!preg_match("/define\\(\\s*'AZNET_THEME_VERSION'\\s*,\\s*'([^']+)'\\s*\\)/", $functions, $constantMatch)) {
    g8_fail('functions.php AZNET_THEME_VERSION definition missing');
}

$styleVersion = trim($styleMatch[1]);
$constantVersion = trim($constantMatch[1]);
$expected = '1.1.0';

if ($styleVersion !== $constantVersion) {
    g8_fail("release metadata mismatch: style.css={$styleVersion}; functions.php={$constantVersion}");
}
if ($styleVersion !== $expected) {
    g8_fail("expected final v1.1 release version {$expected}, got {$styleVersion}");
}
if (preg_match('/(?:alpha|beta|rc|dev|snapshot)/i', $styleVersion)) {
    g8_fail("pre-release marker remains in final version: {$styleVersion}");
}

foreach (['Requires at least: 6.9', 'Requires PHP: 8.1', 'Text Domain: aznet-theme'] as $required) {
    if (!str_contains($style, $required)) {
        g8_fail("style.css missing required release header: {$required}");
    }
}

echo "PASS: G8 final release version contract\n";
echo "VERSION={$styleVersion}\n";

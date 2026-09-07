<?php
/**
 * R1 semantic token contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$tokens_file = dirname(__DIR__, 2) . '/assets/css/tokens.css';
$css = file_get_contents($tokens_file);

if (false === $css) {
    fwrite(STDERR, "FAIL: unable to read assets/css/tokens.css\n");
    exit(1);
}

$required = [
    '--aznet-theme-color-primary',
    '--aznet-theme-color-on-primary',
    '--aznet-theme-color-surface-subtle',
    '--aznet-theme-color-success',
    '--aznet-theme-color-warning',
    '--aznet-theme-color-danger',
    '--aznet-theme-font-size-sm',
    '--aznet-theme-font-size-base',
    '--aznet-theme-font-size-lg',
    '--aznet-theme-font-size-xl',
    '--aznet-theme-line-height-body',
    '--aznet-theme-line-height-heading',
    '--aznet-theme-radius-control',
    '--aznet-theme-shadow-card',
    '--aznet-theme-motion-fast',
    '--aznet-theme-motion-base',
];

$legacy = [
    '--aznet-theme-accent',
    '--aznet-theme-text',
    '--aznet-theme-surface',
    '--aznet-theme-muted',
    '--aznet-theme-border',
    '--aznet-theme-focus',
    '--aznet-theme-container-content',
    '--aznet-theme-container-wide',
    '--aznet-theme-container-shell',
    '--aznet-theme-space-1',
    '--aznet-theme-space-2',
    '--aznet-theme-space-3',
    '--aznet-theme-space-4',
    '--aznet-theme-space-6',
];

$missing = [];
foreach (array_merge($required, $legacy) as $token) {
    if (! str_contains($css, $token . ':')) {
        $missing[] = $token;
    }
}

if ([] !== $missing) {
    fwrite(STDERR, 'FAIL: missing tokens: ' . implode(', ', $missing) . "\n");
    exit(1);
}

echo "PASS: R1 semantic token contract\n";
